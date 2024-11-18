# Laravel Tenancy

## Config Local Env(Server)

### Setup Laravel `Herd` for server environment

1. Create a new file named `.env` in the root directory of the project.
2. Copy the contents of the `.env.example` file and paste it into the `.env` file.
3. Update the database connection settings in the `.env` file.
4. Run the following command to generate a new application key:

```bash
php artisan key:generate
```

[x]. Run the following command to create the database tables:

```bash
php artisan migrate
```

[x]. Run the following command to seed the database tables:

```bash
php artisan db:seed
```
