<?php

declare(strict_types=1);

use App\Enums\DomainVerificationStatus;
use App\Models\Domain;
use App\Models\Organization;
use App\Models\Tenant;
use App\Services\Dns\DnsResolver;
use App\Services\Dns\DomainVerifier;
use Tests\Support\FakeDnsResolver;

beforeEach(function () {
    $this->dns = new FakeDnsResolver;
    $this->app->instance(DnsResolver::class, $this->dns);

    $this->tenant = asSuperAdmin(fn () => Tenant::factory()
        ->forOrganization(Organization::factory()->create())
        ->create());

    $this->verifier = app(DomainVerifier::class);
});

function customDomain(Tenant $tenant, string $hostname = 'status.acme-corp.test'): Domain
{
    return asSuperAdmin(fn () => Domain::factory()
        ->forTenant($tenant)
        ->custom()
        ->create(['domain' => $hostname]));
}

it('verifies a domain pointed at us by CNAME', function () {
    $domain = customDomain($this->tenant);

    $this->dns->setCname($domain->domain, $domain->expectedCnameTarget());

    expect($this->verifier->verify($domain))->toBeTrue()
        ->and($domain->fresh()->verification_status)->toBe(DomainVerificationStatus::Verified)
        ->and($domain->fresh()->verified_at)->not->toBeNull();
});

it('verifies a domain proven by TXT record', function () {
    $domain = customDomain($this->tenant);

    // The escape hatch for apex domains, which cannot carry a CNAME.
    $this->dns->setTxt('_status-verify.'.$domain->domain, $domain->verification_token);

    expect($this->verifier->verify($domain))->toBeTrue()
        ->and($domain->fresh()->isVerified())->toBeTrue();
});

it('refuses a domain with no matching record', function () {
    $domain = customDomain($this->tenant);

    expect($this->verifier->verify($domain))->toBeFalse()
        ->and($domain->fresh()->verification_status)->toBe(DomainVerificationStatus::Failed)
        ->and($domain->fresh()->verified_at)->toBeNull();
});

it('refuses a CNAME pointing somewhere else', function () {
    $domain = customDomain($this->tenant);

    $this->dns->setCname($domain->domain, 'cname.someone-else.test');

    expect($this->verifier->verify($domain))->toBeFalse();
});

it('refuses a TXT record carrying the wrong token', function () {
    $domain = customDomain($this->tenant);

    $this->dns->setTxt('_status-verify.'.$domain->domain, 'status-verify-not-the-right-token');

    expect($this->verifier->verify($domain))->toBeFalse();
});

it('will not accept another domain\'s token', function () {
    $mine = customDomain($this->tenant, 'status.mine.test');
    $theirs = customDomain($this->tenant, 'status.theirs.test');

    // Tokens are per-domain, so publishing one you found elsewhere proves
    // nothing about the hostname being claimed.
    $this->dns->setTxt('_status-verify.'.$mine->domain, $theirs->verification_token);

    expect($this->verifier->verify($mine))->toBeFalse();
});

it('treats a platform subdomain as already proven', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->create());

    expect($domain->isCustom())->toBeFalse()
        ->and($this->verifier->hasProof($domain))->toBeTrue();
});

it('matches DNS answers case-insensitively', function () {
    $domain = customDomain($this->tenant);

    $this->dns->setCname($domain->domain, strtoupper($domain->expectedCnameTarget()));

    expect($this->verifier->verify($domain))->toBeTrue();
});

it('hands back the exact records a customer must add', function () {
    $domain = customDomain($this->tenant);

    $instructions = $this->verifier->instructionsFor($domain);

    expect($instructions['cname']['name'])->toBe($domain->domain)
        ->and($instructions['cname']['value'])->toBe($domain->expectedCnameTarget())
        ->and($instructions['txt']['name'])->toBe('_status-verify.'.$domain->domain)
        ->and($instructions['txt']['value'])->toBe($domain->verification_token);
});

it('does not permit issuance until verification succeeds', function () {
    $domain = customDomain($this->tenant);

    expect($domain->mayIssueCertificate())->toBeFalse();

    $this->dns->setCname($domain->domain, $domain->expectedCnameTarget());
    $this->verifier->verify($domain);

    expect($domain->fresh()->mayIssueCertificate())->toBeTrue();
});

it('marks a platform subdomain verified the moment it is created', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->create());

    // We control this DNS. Leaving it pending would make the TLS gate refuse a
    // certificate for the platform's own hostname.
    expect($domain->isCustom())->toBeFalse()
        ->and($domain->verification_status)->toBe(DomainVerificationStatus::Verified)
        ->and($domain->verified_at)->not->toBeNull()
        ->and($domain->mayIssueCertificate())->toBeTrue()
        ->and($domain->verification_token)->toBeNull();
});

it('leaves a custom domain pending until proven', function () {
    $domain = customDomain($this->tenant);

    expect($domain->verification_status)->toBe(DomainVerificationStatus::Pending)
        ->and($domain->mayIssueCertificate())->toBeFalse();
});

it('answers the certificate gate on a domain that has not been reloaded', function () {
    // Column defaults are not applied until after the insert, so an in-memory
    // model held a null status and the gate threw rather than refusing.
    $domain = asSuperAdmin(function (): Domain {
        $domain = new Domain(['domain' => 'fresh.acme-corp.test']);
        $domain->tenant_id = $this->tenant->id;
        $domain->save();

        return $domain;
    });

    expect($domain->verification_status)->toBe(DomainVerificationStatus::Pending)
        ->and($domain->mayIssueCertificate())->toBeFalse();
});
