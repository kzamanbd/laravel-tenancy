<?php

declare(strict_types=1);

use App\Enums\DomainVerificationStatus;
use App\Enums\MembershipRole;
use App\Enums\Plan;
use App\Models\Domain;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Dns\DnsResolver;
use App\Services\StatusPagePublisher;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\Support\FakeDnsResolver;

beforeEach(function () {
    Storage::fake('status_pages');

    $this->dns = new FakeDnsResolver;
    $this->app->instance(DnsResolver::class, $this->dns);

    [$this->tenant, $this->owner, $this->viewer] = asSuperAdmin(function () {
        $organization = Organization::factory()->onPlan(Plan::Growth)->create(['tenant_quota' => 5]);
        $tenant = Tenant::factory()->forOrganization($organization)->create();

        $owner = User::factory()->create();
        Membership::factory()->role(MembershipRole::Owner)->create([
            'user_id' => $owner->id,
            'tenant_id' => $tenant->id,
        ]);

        $viewer = User::factory()->create();
        Membership::factory()->role(MembershipRole::Viewer)->create([
            'user_id' => $viewer->id,
            'tenant_id' => $tenant->id,
        ]);

        return [$tenant, $owner, $viewer];
    });

    $this->base = workspaceUrl($this->tenant);
});

it('adds a domain as pending rather than verified', function () {
    $this->actingAs($this->owner)
        ->post("{$this->base}/domains", ['domain' => 'status.acme-corp.test'])
        ->assertRedirect();

    $domain = asSuperAdmin(fn () => Domain::query()->where('domain', 'status.acme-corp.test')->firstOrFail());

    expect($domain->verification_status)->toBe(DomainVerificationStatus::Pending)
        ->and($domain->mayIssueCertificate())->toBeFalse()
        ->and($domain->verification_token)->toStartWith('status-verify-');
});

it('normalises a hostname typed with scheme or case', function () {
    $this->actingAs($this->owner)
        ->post("{$this->base}/domains", ['domain' => 'https://Status.Acme.test/'])
        ->assertSessionHasErrors('domain');
});

it('rejects a wildcard hostname', function () {
    // A wildcard would ask for a certificate covering names nobody proved.
    $this->actingAs($this->owner)
        ->post("{$this->base}/domains", ['domain' => '*.acme.test'])
        ->assertSessionHasErrors('domain');
});

it('rejects a hostname already connected to another page', function () {
    $other = asSuperAdmin(fn () => Tenant::factory()
        ->forOrganization(Organization::factory()->create())
        ->create());

    asSuperAdmin(fn () => Domain::factory()->forTenant($other)->create(['domain' => 'taken.acme.test']));

    $this->actingAs($this->owner)
        ->post("{$this->base}/domains", ['domain' => 'taken.acme.test'])
        ->assertSessionHasErrors('domain');
});

it('refuses to add a custom domain on the free plan', function () {
    asSuperAdmin(fn () => $this->tenant->organization->forceFill(['plan' => Plan::Free])->save());

    $this->actingAs($this->owner)
        ->post("{$this->base}/domains", ['domain' => 'free.acme.test'])
        ->assertSessionHasErrors('domain');

    expect(asSuperAdmin(fn () => Domain::query()->where('domain', 'free.acme.test')->exists()))->toBeFalse();
});

it('refuses domain changes from a viewer', function () {
    $this->actingAs($this->viewer)
        ->post("{$this->base}/domains", ['domain' => 'nope.acme.test'])
        ->assertForbidden();
});

it('verifies a domain once DNS agrees, and publishes it', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->custom()
        ->create(['domain' => 'status.acme-corp.test']));

    $this->dns->setCname($domain->domain, $domain->expectedCnameTarget());

    $this->actingAs($this->owner)
        ->post("{$this->base}/domains/{$domain->id}/verify")
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(asSuperAdmin(fn () => $domain->fresh())->isVerified())->toBeTrue();

    // The hostname must now resolve to a page without a database lookup.
    Storage::disk('status_pages')->assertExists(StatusPagePublisher::hostPointerFor($domain->domain));
});

it('reports a helpful error when DNS has not propagated', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->custom()->create());

    $this->actingAs($this->owner)
        ->post("{$this->base}/domains/{$domain->id}/verify")
        ->assertSessionHasErrors('domain');

    expect(asSuperAdmin(fn () => $domain->fresh())->isVerified())->toBeFalse();
});

it('rate limits repeated verification attempts', function () {
    RateLimiter::clear('verify-domain:1');

    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->custom()->create());

    // Each attempt is an outbound DNS lookup, and customers waiting on
    // propagation press this repeatedly.
    foreach (range(1, 10) as $ignored) {
        $this->actingAs($this->owner)->post("{$this->base}/domains/{$domain->id}/verify");
    }

    $this->actingAs($this->owner)
        ->post("{$this->base}/domains/{$domain->id}/verify")
        ->assertSessionHasErrors('domain');

    expect(RateLimiter::tooManyAttempts('verify-domain:'.$domain->id, 10))->toBeTrue();
});

it('cannot touch a domain belonging to another page', function () {
    $other = asSuperAdmin(fn () => Tenant::factory()
        ->forOrganization(Organization::factory()->create())
        ->create());

    $foreign = asSuperAdmin(fn () => Domain::factory()->forTenant($other)->custom()->create());

    // `domains` is central, so Row-Level Security does not cover it -- the
    // controller has to check ownership itself.
    $this->actingAs($this->owner)
        ->post("{$this->base}/domains/{$foreign->id}/verify")
        ->assertNotFound();

    $this->actingAs($this->owner)
        ->delete("{$this->base}/domains/{$foreign->id}")
        ->assertNotFound();

    expect(asSuperAdmin(fn () => Domain::query()->find($foreign->id)))->not->toBeNull();
});

it('will not make an unverified domain primary', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->custom()->create());

    $this->actingAs($this->owner)
        ->post("{$this->base}/domains/{$domain->id}/primary")
        ->assertStatus(422);
});

it('moves the primary flag to exactly one domain', function () {
    [$first, $second] = asSuperAdmin(fn () => [
        Domain::factory()->forTenant($this->tenant)->custom()->verified()->create(['is_primary' => true]),
        Domain::factory()->forTenant($this->tenant)->custom()->verified()->create(),
    ]);

    $this->actingAs($this->owner)
        ->post("{$this->base}/domains/{$second->id}/primary")
        ->assertRedirect();

    expect(asSuperAdmin(fn () => $first->fresh())->is_primary)->toBeFalse()
        ->and(asSuperAdmin(fn () => $second->fresh())->is_primary)->toBeTrue();
});

it('stops a removed hostname from resolving', function () {
    $domain = asSuperAdmin(fn () => Domain::factory()->forTenant($this->tenant)->custom()->verified()->create());

    app(StatusPagePublisher::class)->publish($this->tenant);
    Storage::disk('status_pages')->assertExists(StatusPagePublisher::hostPointerFor($domain->domain));

    $this->actingAs($this->owner)
        ->delete("{$this->base}/domains/{$domain->id}")
        ->assertRedirect();

    Storage::disk('status_pages')->assertMissing(StatusPagePublisher::hostPointerFor($domain->domain));
});
