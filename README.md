# StoreZ

Laravel 13 storefront and admin catalog built with Livewire, Sheaf UI, Tailwind CSS, and Vite.

## Requirements

- PHP 8.3+
- Composer
- Node.js and npm
- PostgreSQL (or another database configured in `.env`)

## Local setup

```powershell
composer install
Copy-Item .env.example .env
```

Set the database and local admin values in `.env`, then run:

```powershell
php artisan key:generate
php artisan migrate --seed --force
npm install
npm run build
```

Start the local application:

```powershell
composer run dev
```

The storefront is available at `http://127.0.0.1:8000`. Use the `ADMIN_EMAIL` and `ADMIN_PASSWORD` values from `.env` to sign in to the admin panel.

## Testing

```powershell
php artisan test --compact
npm run build
```
