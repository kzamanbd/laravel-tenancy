<?php

declare(strict_types=1);

use App\Enums\Plan;
use App\Models\Domain;
use App\Models\Organization;
use App\Models\Tenant;

/*
|--------------------------------------------------------------------------
| On-demand TLS gate
|--------------------------------------------------------------------------
|
| Caddy asks this endpoint before obtaining a publicly trusted certificate for
| a hostname, and answers 200 as permission. Anything that gets a 200 here
| without proving ownership turns the platform into a phishing host that issues
| its own certificates -- for free, on the free tier.
|
| Every refusal is a 404 on purpose: an unauthenticated caller must not be able
| to enumerate which hostnames we know about.
|
*/

function domainFor(Plan $plan, ?callable $configure = null): Domain
{
    return asSuperAdmin(function () use ($plan, $configure): Domain {
        $organization = Organization::factory()->onPlan($plan)->create(['tenant_quota' => 5]);
        $tenant = Tenant::factory()->forOrganization($organization)->create();

        $domain = Domain::factory()->forTenant($tenant)->custom()->create();

        if ($configure !== null) {
            $configure($domain);
        }

        return $domain->fresh();
    });
}

it('permits issuance for a verified domain on a paid plan', function () {
    $domain = domainFor(Plan::Starter, fn (Domain $domain) => $domain->markVerified());

    $this->get('/internal/tls-ask?domain='.$domain->domain)->assertOk();
});

it('refuses a domain whose ownership has not been proven', function () {
    $domain = domainFor(Plan::Starter);

    $this->get('/internal/tls-ask?domain='.$domain->domain)->assertNotFound();
});

it('refuses a domain whose verification failed', function () {
    $domain = domainFor(Plan::Starter, fn (Domain $domain) => $domain->markVerificationFailed());

    $this->get('/internal/tls-ask?domain='.$domain->domain)->assertNotFound();
});

it('refuses a verified domain on the free plan', function () {
    // The plan risk register calls this out directly: free custom domains with
    // automatic certificates are a phishing vector.
    $domain = domainFor(Plan::Free, fn (Domain $domain) => $domain->markVerified());

    $this->get('/internal/tls-ask?domain='.$domain->domain)->assertNotFound();
});

it('refuses a hostname it has never heard of', function () {
    $this->get('/internal/tls-ask?domain=phish.example.com')->assertNotFound();
});

it('refuses a request with no hostname at all', function () {
    $this->get('/internal/tls-ask')->assertNotFound();
});

it('refuses malformed hostnames without querying', function (string $hostname) {
    $this->get('/internal/tls-ask?domain='.urlencode($hostname))->assertNotFound();
})->with([
    'no dot' => ['localhost'],
    'leading dash' => ['-bad.example.com'],
    'path traversal' => ['../../etc/passwd'],
    'space' => ['evil .com'],
    'wildcard' => ['*.example.com'],
]);

it('permits a platform subdomain that is verified', function () {
    $domain = asSuperAdmin(function (): Domain {
        $organization = Organization::factory()->onPlan(Plan::Starter)->create(['tenant_quota' => 5]);
        $tenant = Tenant::factory()->forOrganization($organization)->create();

        return Domain::factory()->forTenant($tenant)->verified()->create();
    });

    $this->get('/internal/tls-ask?domain='.$domain->domain)->assertOk();
});

it('answers case-insensitively', function () {
    $domain = domainFor(Plan::Growth, fn (Domain $domain) => $domain->markVerified());

    $this->get('/internal/tls-ask?domain='.strtoupper($domain->domain))->assertOk();
});
