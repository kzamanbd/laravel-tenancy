<?php

declare(strict_types=1);

use App\Enums\IncidentStatus;
use App\Enums\MembershipRole;
use App\Enums\SubscriberChannel;
use App\Jobs\DeliverStatusNotification;
use App\Jobs\FanOutStatusNotification;
use App\Mail\StatusUpdateMail;
use App\Models\Component;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Subscriber;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Cache\RateLimiter;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

/*
|--------------------------------------------------------------------------
| Notification fan-out
|--------------------------------------------------------------------------
|
| An incident at a page with 40,000 subscribers must not starve delivery for
| every other tenant -- who are, by definition, also mid-incident, because that
| is when notifications happen. Hence one job per subscriber, throttled per
| tenant rather than globally.
|
*/

beforeEach(function () {
    [$this->tenant, $this->owner] = asSuperAdmin(function () {
        $organization = Organization::factory()->create(['tenant_quota' => 5]);
        $tenant = Tenant::factory()->forOrganization($organization)->create(['name' => 'Acme Platform']);

        $owner = User::factory()->create();
        Membership::factory()->role(MembershipRole::Owner)->create([
            'user_id' => $owner->id,
            'tenant_id' => $tenant->id,
        ]);

        return [$tenant, $owner];
    });

    $this->base = workspaceUrl($this->tenant);
});

it('queues a fan-out when an update is published', function () {
    Queue::fake();

    $incident = asSuperAdmin(fn () => Incident::factory()->forTenant($this->tenant)->create());

    $this->actingAs($this->owner)->post("{$this->base}/incidents/{$incident->id}/updates", [
        'status' => IncidentStatus::Monitoring->value,
        'body' => 'We are monitoring recovery.',
        'publish' => true,
    ])->assertRedirect();

    Queue::assertPushed(
        FanOutStatusNotification::class,
        fn (FanOutStatusNotification $job): bool => $job->tenantId === $this->tenant->id,
    );
});

it('does not notify anyone about an unpublished draft', function () {
    Queue::fake();

    $incident = asSuperAdmin(fn () => Incident::factory()->forTenant($this->tenant)->create());

    // A draft exists precisely so somebody can read it before thousands do.
    $this->actingAs($this->owner)->post("{$this->base}/incidents/{$incident->id}/updates", [
        'status' => IncidentStatus::Resolved->value,
        'body' => 'Draft, not yet approved.',
        'publish' => false,
    ])->assertRedirect();

    Queue::assertNotPushed(FanOutStatusNotification::class);
});

it('notifies when a held draft is approved', function () {
    $incident = asSuperAdmin(fn () => Incident::factory()->forTenant($this->tenant)->create());
    $draft = asSuperAdmin(fn () => IncidentUpdate::factory()->forTenant($this->tenant)->aiDraft()
        ->create(['incident_id' => $incident->id]));

    Queue::fake();

    $this->actingAs($this->owner)
        ->post("{$this->base}/incidents/{$incident->id}/updates/{$draft->id}/publish")
        ->assertRedirect();

    Queue::assertPushed(FanOutStatusNotification::class);
});

it('dispatches one delivery per deliverable subscriber', function () {
    asSuperAdmin(function () {
        Subscriber::factory()->count(3)->forTenant($this->tenant)->create();
        Subscriber::factory()->forTenant($this->tenant)->unconfirmed()->create();
        Subscriber::factory()->forTenant($this->tenant)->unsubscribed()->create();
    });

    Queue::fake();

    (new FanOutStatusNotification($this->tenant->id, [
        'heading' => 'Outage', 'body' => 'Investigating.', 'impact' => 'Major',
        'status' => 'Investigating', 'componentNames' => [], 'componentIds' => [],
        'incidentId' => 1, 'maintenanceId' => null,
    ]))->handle(app(TenantContext::class));

    // Unconfirmed and unsubscribed addresses are skipped: sending to either is
    // what costs a shared sending reputation.
    Queue::assertPushed(DeliverStatusNotification::class, 3);
});

it('never fans out to another page\'s subscribers', function () {
    $other = asSuperAdmin(fn () => Tenant::factory()
        ->forOrganization(Organization::factory()->create())->create());

    asSuperAdmin(function () use ($other) {
        Subscriber::factory()->count(2)->forTenant($this->tenant)->create();
        Subscriber::factory()->count(5)->forTenant($other)->create();
    });

    Queue::fake();

    (new FanOutStatusNotification($this->tenant->id, [
        'heading' => 'Outage', 'body' => 'x', 'impact' => null, 'status' => 'Investigating',
        'componentNames' => [], 'componentIds' => [], 'incidentId' => 1, 'maintenanceId' => null,
    ]))->handle(app(TenantContext::class));

    Queue::assertPushed(DeliverStatusNotification::class, 2);
});

it('skips subscribers who follow other components only', function () {
    [$watched, $ignored] = asSuperAdmin(fn () => [
        Component::factory()->forTenant($this->tenant)->create(),
        Component::factory()->forTenant($this->tenant)->create(),
    ]);

    asSuperAdmin(function () use ($watched, $ignored) {
        Subscriber::factory()->forTenant($this->tenant)->create(['component_ids' => [$watched->id]]);
        Subscriber::factory()->forTenant($this->tenant)->create(['component_ids' => [$ignored->id]]);
        // Null means the whole page.
        Subscriber::factory()->forTenant($this->tenant)->create(['component_ids' => null]);
    });

    Queue::fake();

    (new FanOutStatusNotification($this->tenant->id, [
        'heading' => 'Outage', 'body' => 'x', 'impact' => null, 'status' => 'Investigating',
        'componentNames' => [], 'componentIds' => [$watched->id], 'incidentId' => 1, 'maintenanceId' => null,
    ]))->handle(app(TenantContext::class));

    Queue::assertPushed(DeliverStatusNotification::class, 2);
});

it('throttles delivery per tenant rather than globally', function () {
    $job = new DeliverStatusNotification(1, $this->tenant->id, []);

    $middleware = $job->middleware();

    expect($middleware)->toHaveCount(1)
        ->and($middleware[0])->toBeInstanceOf(RateLimited::class);

    // Keyed by tenant: a global limiter would let one large fan-out delay
    // every other page's incident notifications.
    $limiter = app(RateLimiter::class)->limiter('status-notifications');
    $limit = $limiter($job);

    expect($limit->key)->toBe((string) $this->tenant->id);
});

it('gives each tenant its own throttle bucket', function () {
    $limiter = app(RateLimiter::class)->limiter('status-notifications');

    $mine = $limiter(new DeliverStatusNotification(1, 1, []));
    $theirs = $limiter(new DeliverStatusNotification(2, 2, []));

    expect($mine->key)->not->toBe($theirs->key);
});

it('emails a subscriber with a one-click unsubscribe header', function () {
    Mail::fake();

    $subscriber = asSuperAdmin(fn () => Subscriber::factory()->forTenant($this->tenant)->create());

    (new DeliverStatusNotification($subscriber->id, $this->tenant->id, [
        'heading' => 'Elevated errors', 'body' => 'Investigating.', 'impact' => 'Major',
        'status' => 'Investigating', 'componentNames' => ['API'], 'componentIds' => [],
        'incidentId' => 1, 'maintenanceId' => null,
    ]))->handle(app(TenantContext::class));

    Mail::assertSent(StatusUpdateMail::class, function (StatusUpdateMail $mail) {
        // Mail clients turn this into a one-click button. Without it, an
        // annoyed reader reaches for "report spam" instead, and every tenant
        // on the shared reputation pays.
        return $mail->headers()->text['List-Unsubscribe-Post'] === 'List-Unsubscribe=One-Click';
    });
});

it('posts to a slack webhook subscriber', function () {
    Http::fake();

    $subscriber = asSuperAdmin(fn () => Subscriber::factory()->forTenant($this->tenant)
        ->onChannel(SubscriberChannel::Slack)->create(['endpoint' => 'https://hooks.slack.test/abc']));

    (new DeliverStatusNotification($subscriber->id, $this->tenant->id, [
        'heading' => 'Elevated errors', 'body' => 'Investigating.', 'impact' => 'Major',
        'status' => 'Investigating', 'componentNames' => ['API'], 'componentIds' => [],
        'incidentId' => 1, 'maintenanceId' => null,
    ]))->handle(app(TenantContext::class));

    Http::assertSent(fn ($request): bool => $request->url() === 'https://hooks.slack.test/abc'
        && str_contains($request->body(), 'Elevated errors'));
});

it('does not deliver to someone who unsubscribed mid-fanout', function () {
    Mail::fake();

    $subscriber = asSuperAdmin(fn () => Subscriber::factory()->forTenant($this->tenant)->unsubscribed()->create());

    // Deliverability is re-checked at send time, not just at fan-out: a large
    // fan-out can take minutes to drain.
    (new DeliverStatusNotification($subscriber->id, $this->tenant->id, [
        'heading' => 'x', 'body' => 'x', 'impact' => null, 'status' => 'Investigating',
        'componentNames' => [], 'componentIds' => [], 'incidentId' => 1, 'maintenanceId' => null,
    ]))->handle(app(TenantContext::class));

    Mail::assertNothingSent();
});

it('unsubscribes an endpoint that keeps failing', function () {
    Http::fake(fn () => Http::response('gone', 410));

    $subscriber = asSuperAdmin(fn () => Subscriber::factory()->forTenant($this->tenant)
        ->onChannel(SubscriberChannel::Webhook)->create(['bounce_count' => 4]));

    $payload = [
        'heading' => 'x', 'body' => 'x', 'impact' => null, 'status' => 'Investigating',
        'componentNames' => [], 'componentIds' => [], 'incidentId' => 1, 'maintenanceId' => null,
    ];

    // Sending forever at a dead endpoint is what turns a shared sending
    // reputation into a blocked one.
    try {
        (new DeliverStatusNotification($subscriber->id, $this->tenant->id, $payload))
            ->handle(app(TenantContext::class));
    } catch (Throwable) {
        // Rethrown so the queue records the failure and retries.
    }

    expect(asSuperAdmin(fn () => $subscriber->fresh())->isDeliverable())->toBeFalse();
});
