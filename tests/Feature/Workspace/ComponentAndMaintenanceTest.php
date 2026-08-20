<?php

declare(strict_types=1);

use App\Enums\ComponentStatus;
use App\Enums\MaintenanceStatus;
use App\Enums\MembershipRole;
use App\Models\Component;
use App\Models\Maintenance;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    [$this->tenant, $this->user] = asSuperAdmin(function () {
        $organization = Organization::factory()->create(['tenant_quota' => 5]);
        $tenant = Tenant::factory()->forOrganization($organization)->create();
        $user = User::factory()->create();

        Membership::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'organization_id' => null,
            'role' => MembershipRole::Owner,
        ]);

        return [$tenant, $user];
    });

    $this->base = "/workspaces/{$this->tenant->id}";
});

it('creates a component against the resolved tenant without being told which', function () {
    $this->actingAs($this->user)
        ->post("{$this->base}/components", [
            'name' => 'Checkout API',
            'status' => ComponentStatus::Operational->value,
        ])
        ->assertRedirect();

    $component = asSuperAdmin(fn () => Component::query()->first());

    expect($component->name)->toBe('Checkout API')
        ->and($component->tenant_id)->toBe($this->tenant->id);
});

it('appends new components to the end of the ordering', function () {
    foreach (['First', 'Second', 'Third'] as $name) {
        $this->actingAs($this->user)->post("{$this->base}/components", [
            'name' => $name,
            'status' => ComponentStatus::Operational->value,
        ]);
    }

    $positions = asSuperAdmin(
        fn () => Component::query()->orderBy('position')->pluck('name')->all(),
    );

    expect($positions)->toBe(['First', 'Second', 'Third']);
});

it('advances the status timestamp only when the status actually moves', function () {
    $component = asSuperAdmin(fn () => Component::factory()->forTenant($this->tenant)->create([
        'status' => ComponentStatus::Operational,
    ]));

    // Setting the initial status counts as a change, so the stamp exists from
    // creation; what matters is that it tracks subsequent transitions.
    $createdAt = $component->status_changed_at;

    expect($createdAt)->not->toBeNull();

    $this->travel(1)->minutes();

    // Saving without touching the status must leave the stamp alone, otherwise
    // the public page reports a fresh incident every time someone fixes a typo.
    $this->actingAs($this->user)
        ->put("{$this->base}/components/{$component->id}", [
            'name' => 'Renamed only',
            'status' => ComponentStatus::Operational->value,
        ])
        ->assertRedirect();

    expect(asSuperAdmin(fn () => $component->fresh())->status_changed_at->timestamp)
        ->toBe($createdAt->timestamp);

    $this->actingAs($this->user)
        ->put("{$this->base}/components/{$component->id}", [
            'name' => 'Renamed only',
            'status' => ComponentStatus::MajorOutage->value,
        ])
        ->assertRedirect();

    $fresh = asSuperAdmin(fn () => $component->fresh());

    expect($fresh->status)->toBe(ComponentStatus::MajorOutage)
        ->and($fresh->status_changed_at->timestamp)->toBeGreaterThan($createdAt->timestamp);
});

it('rejects an unknown component status', function () {
    $this->actingAs($this->user)
        ->post("{$this->base}/components", ['name' => 'Bad', 'status' => 'on_fire'])
        ->assertSessionHasErrors('status');
});

it('schedules a maintenance window as scheduled regardless of what was posted', function () {
    $this->actingAs($this->user)
        ->post("{$this->base}/maintenance", [
            'title' => 'Database failover rehearsal',
            'scheduled_start_at' => now()->addDay()->toDateTimeString(),
            'scheduled_end_at' => now()->addDay()->addHours(2)->toDateTimeString(),
        ])
        ->assertRedirect();

    $window = asSuperAdmin(fn () => Maintenance::query()->first());

    expect($window->status)->toBe(MaintenanceStatus::Scheduled)
        ->and($window->slug)->toBe('database-failover-rehearsal')
        ->and($window->tenant_id)->toBe($this->tenant->id);
});

it('refuses a maintenance window that ends before it starts', function () {
    $this->actingAs($this->user)
        ->post("{$this->base}/maintenance", [
            'title' => 'Backwards',
            'scheduled_start_at' => now()->addDay()->toDateTimeString(),
            'scheduled_end_at' => now()->toDateTimeString(),
        ])
        ->assertSessionHasErrors('scheduled_end_at');

    expect(asSuperAdmin(fn () => Maintenance::query()->count()))->toBe(0);
});

it('stamps start and completion as a window moves through its states', function () {
    $window = asSuperAdmin(fn () => Maintenance::factory()->forTenant($this->tenant)->create());

    $payload = fn (string $status): array => [
        'title' => $window->title,
        'status' => $status,
        'scheduled_start_at' => $window->scheduled_start_at->toDateTimeString(),
        'scheduled_end_at' => $window->scheduled_end_at->toDateTimeString(),
    ];

    $this->actingAs($this->user)
        ->put("{$this->base}/maintenance/{$window->id}", $payload(MaintenanceStatus::InProgress->value));

    $started = asSuperAdmin(fn () => $window->fresh());

    expect($started->started_at)->not->toBeNull()
        ->and($started->completed_at)->toBeNull();

    $this->actingAs($this->user)
        ->put("{$this->base}/maintenance/{$window->id}", $payload(MaintenanceStatus::Completed->value));

    $completed = asSuperAdmin(fn () => $window->fresh());

    expect($completed->completed_at)->not->toBeNull()
        // Rewinding to scheduled must clear both, or the public page claims a
        // window that has not begun already finished.
        ->and($completed->started_at)->not->toBeNull();

    $this->actingAs($this->user)
        ->put("{$this->base}/maintenance/{$window->id}", $payload(MaintenanceStatus::Scheduled->value));

    $rewound = asSuperAdmin(fn () => $window->fresh());

    expect($rewound->started_at)->toBeNull()
        ->and($rewound->completed_at)->toBeNull();
});

it('links a maintenance window to the components it affects', function () {
    $component = asSuperAdmin(fn () => Component::factory()->forTenant($this->tenant)->create());

    $this->actingAs($this->user)
        ->post("{$this->base}/maintenance", [
            'title' => 'Storage migration',
            'scheduled_start_at' => now()->addDay()->toDateTimeString(),
            'scheduled_end_at' => now()->addDay()->addHour()->toDateTimeString(),
            'component_ids' => [$component->id],
        ])
        ->assertRedirect();

    $window = asSuperAdmin(fn () => Maintenance::query()->with('components')->first());

    expect($window->components)->toHaveCount(1)
        ->and($window->components->first()->id)->toBe($component->id)
        // The pivot carries tenant_id so one RLS policy shape covers it too.
        ->and($window->components->first()->pivot->tenant_id)->toBe($this->tenant->id);
});
