# Laravel Tenancy

Multi-tenant Laravel application. Domain/subdomain tenancy via [`stancl/tenancy`](https://tenancyforlaravel.com), a React + Inertia SPA frontend, and [Laravel Fortify](https://laravel.com/docs/fortify) authentication (login, registration, password reset, email verification, two-factor).

Each account registers on its own **subdomain** (`acme.laravel-tenancy.test`); the central domain hosts sign-up and tenant management.

## Tech stack

- **Backend:** Laravel 13, PHP 8.3, Fortify, stancl/tenancy 3
- **Frontend:** React 19, Inertia 3, TypeScript, Tailwind CSS 4, shadcn/ui, Vite
- **Routing bridge:** [Laravel Wayfinder](https://github.com/laravel/wayfinder) (typed routes/actions generated from PHP)
- **Testing:** Pest

## Requirements

- PHP 8.3+
- Composer
- Node 20+ and `pnpm`
- A local domain with wildcard subdomains — [Laravel Herd](https://herd.laravel.com) recommended

## Setup

1. Install dependencies:

    ```bash
    composer install
    pnpm install
    ```

2. Create the environment file and generate a key:

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

3. Set the central domain and DB in `.env`. `SESSION_DOMAIN` must be the leading-dot central domain so the auth session is shared across tenant subdomains:

    ```dotenv
    APP_URL=https://laravel-tenancy.test
    CENTRAL_DOMAIN=laravel-tenancy.test
    SESSION_DOMAIN=.laravel-tenancy.test

    DB_CONNECTION=mysql
    DB_DATABASE=laravel_tenancy
    DB_USERNAME=root
    DB_PASSWORD=
    ```

4. Point the central domain and a wildcard at the project (Herd):

    ```bash
    herd link laravel-tenancy
    herd secure laravel-tenancy
    # ensure *.laravel-tenancy.test resolves (Herd handles this by default)
    ```

5. Migrate:

    ```bash
    php artisan migrate
    ```

## Development

```bash
composer dev
```

Runs the PHP server, queue listener, Pail logs, and Vite together. Or run Vite alone:

```bash
pnpm dev
```

Wayfinder route/action files under `resources/js/{actions,routes,wayfinder}` are generated on build and are git-ignored. Regenerate manually with:

```bash
php artisan wayfinder:generate --with-form
```

## How tenancy works

- **Central domain** (`CENTRAL_DOMAIN`) serves registration and the `/tenants` management page.
- **Registration** creates a `User`, a `Tenant`, and a `Domain` (`{subdomain}.{central}`), then redirects to that tenant's `/dashboard`.
- Shared routes (`/`, `/dashboard`) are **universal** — they resolve on both the central domain and any tenant domain (see `Stancl\Tenancy\Features\UniversalRoutes`). On a tenant domain the tenant is initialized; on the central domain the request passes through without one.
- Users live on the central connection (`User` uses `CentralConnection`); the app runs single-database tenancy by default (database-per-tenant bootstrapper is available but commented out in `config/tenancy.php`).

## Testing

```bash
php artisan test
```

## Build

```bash
pnpm build
```
