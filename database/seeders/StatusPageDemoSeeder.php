<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ComponentStatus;
use App\Enums\IncidentImpact;
use App\Enums\IncidentStatus;
use App\Enums\MaintenanceStatus;
use App\Enums\MembershipRole;
use App\Enums\Plan;
use App\Models\Component;
use App\Models\Domain;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use App\Models\Maintenance;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Subscriber;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * A small but realistic dataset: one organization, two status pages, and enough
 * incident history for the dashboard to have something to say.
 *
 * Seeding is inherently cross-tenant, so the whole run sits inside the audited
 * escape hatch rather than initializing tenancy repeatedly.
 */
class StatusPageDemoSeeder extends Seeder
{
    public function run(): void
    {
        app(TenantContext::class)->withoutIsolation(function (): void {
            $user = User::firstOrCreate(
                ['email' => 'demo@example.com'],
                [
                    'name' => 'Demo Operator',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );

            $organization = Organization::firstOrCreate(
                ['slug' => 'acme'],
                [
                    'name' => 'Acme Inc.',
                    'plan' => Plan::Growth,
                    'tenant_quota' => 5,
                    'billing_email' => 'billing@acme.test',
                ],
            );

            $this->createPage($user, $organization, 'Acme Platform', 'acme', healthy: false);
            $this->createPage($user, $organization, 'Acme Payments', 'acme-payments', healthy: true);
        });
    }

    private function createPage(User $user, Organization $organization, string $name, string $slug, bool $healthy): void
    {
        if (Tenant::where('slug', $slug)->exists()) {
            return;
        }

        $tenant = Tenant::create([
            'organization_id' => $organization->id,
            'name' => $name,
            'slug' => $slug,
            'published_at' => now(),
        ]);

        Domain::create([
            'tenant_id' => $tenant->id,
            'domain' => $slug.'.'.config('tenancy.central_domains')[0],
            'is_primary' => true,
        ]);

        $tenant->users()->syncWithoutDetaching([$user->id]);

        Membership::firstOrCreate([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'organization_id' => null,
        ], ['role' => MembershipRole::Owner]);

        $components = collect(['API', 'Dashboard', 'Webhooks', 'Background Jobs'])
            ->map(fn (string $componentName, int $index): Component => Component::create([
                'tenant_id' => $tenant->id,
                'name' => $componentName,
                'description' => "{$componentName} availability",
                'position' => $index,
                'status' => ! $healthy && $componentName === 'Webhooks'
                    ? ComponentStatus::PartialOutage
                    : ComponentStatus::Operational,
            ]));

        foreach (range(1, $healthy ? 8 : 24) as $index) {
            Subscriber::create([
                'tenant_id' => $tenant->id,
                'endpoint' => "subscriber{$index}@{$slug}.test",
                'confirmed_at' => now(),
            ]);
        }

        Maintenance::create([
            'tenant_id' => $tenant->id,
            'title' => 'Database failover rehearsal',
            'slug' => Str::slug($slug.'-failover-rehearsal'),
            'description' => 'Brief connection resets while we exercise the standby.',
            'status' => MaintenanceStatus::Scheduled,
            'is_published' => true,
            'scheduled_start_at' => now()->addDays(3),
            'scheduled_end_at' => now()->addDays(3)->addHours(2),
        ]);

        if ($healthy) {
            return;
        }

        $incident = Incident::create([
            'tenant_id' => $tenant->id,
            'title' => 'Elevated webhook delivery latency',
            'slug' => Str::slug($slug.'-webhook-latency'),
            'status' => IncidentStatus::Monitoring,
            'impact' => IncidentImpact::Major,
            'is_published' => true,
            'started_at' => now()->subHours(3),
        ]);

        $incident->components()->attach($components->firstWhere('name', 'Webhooks')->id, [
            'tenant_id' => $tenant->id,
            'status' => ComponentStatus::PartialOutage->value,
        ]);

        IncidentUpdate::create([
            'tenant_id' => $tenant->id,
            'incident_id' => $incident->id,
            'author_id' => $user->id,
            'status' => IncidentStatus::Investigating,
            'body' => 'We are seeing delayed webhook deliveries and are investigating.',
            'published_at' => now()->subHours(3),
        ]);

        IncidentUpdate::create([
            'tenant_id' => $tenant->id,
            'incident_id' => $incident->id,
            'author_id' => $user->id,
            'status' => IncidentStatus::Monitoring,
            'body' => 'The queue backlog is draining. We are monitoring recovery.',
            'published_at' => now()->subHour(),
        ]);

        // An unpublished draft, which is what the one-click approval queue holds.
        IncidentUpdate::create([
            'tenant_id' => $tenant->id,
            'incident_id' => $incident->id,
            'status' => IncidentStatus::Resolved,
            'body' => 'Webhook delivery has returned to normal for all customers.',
            'is_ai_drafted' => true,
            'published_at' => null,
        ]);
    }
}
