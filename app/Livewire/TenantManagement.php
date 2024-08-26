<?php

namespace App\Livewire;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class TenantManagement extends Component
{

    public string $name = '';
    public string $subdomain = '';

    public function store()
    {
        DB::beginTransaction();
        try {
            $validatedData = $this->validate([
                'name' => 'required|string|max:255',
                'subdomain' => 'required|alpha_dash|unique:domains,domain'
            ]);

            $tenant = Tenant::create($validatedData);

            $domain = $this->subdomain . '.' . config('tenancy.central_domains')[0];
            $tenant->createDomain($domain);
            $this->dispatch('notify', 'success', 'Tenant created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', 'error', 'Failed to create tenant');
        }
    }




    public function getTenantsProperty()
    {
        return Tenant::with('domains')->get();
    }

    public function render()
    {
        return view('livewire.tenants');
    }
}
