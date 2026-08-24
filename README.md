# UpFront

Multi-tenant status pages that stay honest without a human remembering to be honest.

Customers describe their systems as components, report incidents and maintenance against them, and publish a page their own customers read during an outage. The published page is a flat file on object storage, so it survives the outage it is reporting.

Built on Laravel 13 with PostgreSQL Row-Level Security for tenant isolation, domain/subdomain tenancy via [`stancl/tenancy`](https://tenancyforlaravel.com), a React + Inertia SPA admin, and [Laravel Fortify](https://laravel.com/docs/fortify) authentication (login, registration, password reset, email verification, two-factor).

Each account gets its own **subdomain** (`acme.laravel-tenancy.test` locally); the central domain hosts sign-up, the portfolio dashboard, and workspace administration.

> The local development domain stays `laravel-tenancy.test` because Herd binds it to the directory name. Only the display name is `UpFront`; set `CENTRAL_DOMAIN` to the real domain in production.

## Documentation

Architecture and operating docs live in [`docs/`](docs/README.md):

| Doc | Answers |
|---|---|
| [Architecture](docs/01-architecture.md) | The two planes, request paths, and the rules the design obeys |
| [Tenancy & isolation](docs/02-tenancy-and-isolation.md) | How Row-Level Security keeps tenants apart |
| [Data model](docs/03-data-model.md) | Tables, relationships, enums, plan limits |
| [Publish pipeline](docs/04-publish-pipeline.md) | Database change → static page on a CDN |
| [Custom domains & TLS](docs/05-custom-domains-tls.md) | Verification and on-demand certificates |
| [Notifications](docs/06-notifications.md) | Subscriber lifecycle and fan-out |
| [Roles & permissions](docs/07-roles-and-permissions.md) | Who may do what |
| [User guides](docs/08-user-guides.md) | Operating the product at every user level |
| [Operations](docs/09-operations.md) | Setup, deploy topology, runbooks, known gaps |

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
