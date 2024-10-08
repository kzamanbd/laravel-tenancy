<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class RegisteredUser extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $subdomain = '';

    public string $baseDomain = '';

    public function mount()
    {
        $this->baseDomain = request()->getHost();
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'subdomain' => ['required', 'string', 'regex:/^[a-z-]+$/'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ], [
            'subdomain.regex' => 'Subdomain can only contain lowercase letters and hyphens.',
        ]);

        DB::transaction(function () use ($validated) {
            $validated['password'] = Hash::make($validated['password']);

            event(new Registered(($user = User::create($validated))));

            $tenant = Tenant::create([
                'name' => $this->name,
            ]);

            $domain = $this->subdomain . '.' . $this->baseDomain;
            $tenant->createDomain($domain);
            $user->tenants()->attach($tenant);

            Auth::login($user);

            // if user browse with https then redirect to https
            $protocol = request()->isSecure() ? 'https://' : 'http://';
            $this->redirect($protocol . $domain);
        });
    }

    public function render()
    {
        return view('livewire.pages.auth.register');
    }
}