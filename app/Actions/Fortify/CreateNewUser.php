<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
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

            $tenant = Tenant::create(['name' => $input['name']]);
            $tenant->createDomain($fullDomain);
            $user->tenants()->attach($tenant);

            $protocol = request()->isSecure() ? 'https://' : 'http://';
            session(['registered_tenant_url' => $protocol.$fullDomain.'/dashboard']);

            return $user;
        });
    }
}
