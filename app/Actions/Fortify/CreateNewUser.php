<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\MembershipRole;
use App\Enums\Plan;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Stancl\Tenancy\Database\Models\Domain;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user together with their tenant.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $baseDomain = config('tenancy.central_domains')[0];

        $input['subdomain'] = Str::slug((string) ($input['subdomain'] ?? ''));

        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'subdomain' => ['required', 'string', 'max:255', 'alpha_dash'],
        ])->validate();

        $fullDomain = $input['subdomain'].'.'.$baseDomain;

        if (Domain::query()->where('domain', $fullDomain)->exists()) {
            throw ValidationException::withMessages([
                'subdomain' => __('This subdomain is already taken.'),
            ]);
        }

        return DB::transaction(function () use ($input, $fullDomain) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            // The billing entity above the page. Without one the tenant has no
            // plan at all, which quietly exempts it from every limit the plan
            // is supposed to impose -- custom domains, subscriber caps, quota.
            $organization = Organization::create([
                'name' => $input['name'],
                'slug' => $input['subdomain'],
                'plan' => Plan::Free,
                'tenant_quota' => 1,
                'billing_email' => $input['email'],
            ]);

            $tenant = Tenant::create([
                'name' => $input['name'],
                'organization_id' => $organization->id,
            ]);
            $tenant->createDomain($fullDomain);
            $user->tenants()->attach($tenant);

            // Without this the person who just signed up cannot open the page
            // they signed up for: the workspace and the tenant dashboard are
            // both gated on a membership, and the pivot row above carries no
            // role. Granted across the organization rather than the single
            // tenant, so it still holds for pages they add later.
            Membership::create([
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'tenant_id' => null,
                'role' => MembershipRole::Owner,
            ]);

            $protocol = request()->isSecure() ? 'https://' : 'http://';
            session(['registered_tenant_url' => $protocol.$fullDomain.'/dashboard']);

            return $user;
        });
    }
}
