<?php

declare(strict_types=1);

use App\Enums\ComponentStatus;
use App\Jobs\PublishStatusPage;
use App\Models\Component;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use App\Models\Maintenance;
use App\Models\Organization;
use App\Models\Tenant;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Queue;

/*
|--------------------------------------------------------------------------
| Republish triggers
|--------------------------------------------------------------------------
|
| The wedge is a page that stays honest without anyone remembering to update
| it. That only holds if every change the page reports on queues a republish by
| itself, so these assert the hooks rather than trusting them.
|
*/

beforeEach(function () {
    $this->tenant = asSuperAdmin(fn () => Tenant::factory()
        ->forOrganization(Organization::factory()->create())
        ->create());

    Queue::fake();
});

it('queues a republish when a component changes status', function () {
    $component = asSuperAdmin(fn () => Component::factory()->forTenant($this->tenant)->create());

    Queue::assertPushed(PublishStatusPage::class);
    Queue::fake();

    $component->update(['status' => ComponentStatus::MajorOutage]);

    Queue::assertPushed(
        PublishStatusPage::class,
        fn (PublishStatusPage $job): bool => $job->tenant->is($this->tenant),
    );
});

it('queues a republish when an incident is opened', function () {
    asSuperAdmin(fn () => Incident::factory()->forTenant($this->tenant)->create());

    Queue::assertPushed(PublishStatusPage::class);
});

it('queues a republish when an incident update is posted', function () {
    $incident = asSuperAdmin(fn () => Incident::factory()->forTenant($this->tenant)->create());

    Queue::fake();

    asSuperAdmin(fn () => IncidentUpdate::factory()->forTenant($this->tenant)->create([
        'incident_id' => $incident->id,
    ]));

    Queue::assertPushed(PublishStatusPage::class);
});

it('queues a republish when maintenance is scheduled', function () {
    asSuperAdmin(fn () => Maintenance::factory()->forTenant($this->tenant)->create());

    Queue::assertPushed(PublishStatusPage::class);
});

it('queues a republish when a record is deleted', function () {
    $component = asSuperAdmin(fn () => Component::factory()->forTenant($this->tenant)->create());

    Queue::fake();

    asSuperAdmin(fn () => $component->delete());

    Queue::assertPushed(PublishStatusPage::class);
});

it('collapses concurrent republishes for one page into a single job', function () {
    $component = asSuperAdmin(fn () => Component::factory()->forTenant($this->tenant)->create());

    // An incident touches several records at once and each asks to republish.
    // ShouldBeUnique is what stops a busy incident queueing a render per write,
    // precisely when the system can least afford it.
    expect($component)->toBeInstanceOf(Component::class);

    $job = new PublishStatusPage($this->tenant);

    expect($job)->toBeInstanceOf(ShouldBeUnique::class)
        ->and($job->uniqueId())->toBe((string) $this->tenant->id);
});

it('gives each page its own uniqueness key', function () {
    $other = asSuperAdmin(fn () => Tenant::factory()
        ->forOrganization(Organization::factory()->create())
        ->create());

    expect((new PublishStatusPage($this->tenant))->uniqueId())
        ->not->toBe((new PublishStatusPage($other))->uniqueId());
});
