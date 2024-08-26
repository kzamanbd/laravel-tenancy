<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $tenant = Tenant::create([
            'name' => 'Base Tenant',
        ]);

        $tenant->createDomain(config('app.central_domain'));
        $user->tenants()->attach($tenant);

        $tenant = Tenant::create([
            'name' => 'Test Tenant',
        ]);
        $tenant->createDomain('app.' . config('app.central_domain'));
        $user->tenants()->attach($tenant);
    }
}
