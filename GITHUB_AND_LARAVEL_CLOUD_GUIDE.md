# GitHub and Laravel Cloud Guide

## 1. Create local Laravel project

```bash
composer create-project laravel/laravel providelabs-id-system
cd providelabs-id-system
```

Copy all files from this starter into the project root.

## 2. Configure local environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database.

Add:

```env
DEFAULT_ADMIN_EMAIL=admin@providelabscorp.com
DEFAULT_ADMIN_PASSWORD=ChangeMeNow!
APP_TIMEZONE=Asia/Manila
FILESYSTEM_DISK=public
```

Then run:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

## 3. Push to GitHub

Create a new empty GitHub repository named:

```text
providelabs-id-system
```

Do not initialize it with README, .gitignore, or license if you are pushing existing code.

Then run:

```bash
git init
git add .
git commit -m "Initial ProvideLabs ID system"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/providelabs-id-system.git
git push -u origin main
```

## 4. Deploy to Laravel Cloud

In Laravel Cloud:

1. Create a new application.
2. Connect your GitHub repository.
3. Add database/storage resources.
4. Set production environment variables.
5. Set deploy command:

```bash
php artisan migrate --force
```

6. Point your custom subdomain, recommended:

```text
ids.providelabscorp.com
```

## 5. Squarespace DNS

Keep:

```text
providelabscorp.com
```

on Squarespace.

Point only:

```text
ids.providelabscorp.com
```

to Laravel Cloud using the DNS record Laravel Cloud provides.
