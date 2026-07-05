# ProvideLabs ID System Starter

Responsive Laravel starter code for a private ID card registration and generation system.

This starter is designed for:

- Senior Citizen Cards
- Officer IDs
- Volunteer IDs
- Member IDs
- Staff IDs
- Other custom card types

All card types use the same cardholder information. The selected card type changes only the visual design/template.

## What is included

- Login/logout
- Dashboard
- Cardholder registration
- Search and edit records
- Phone/webcam photo capture
- Photo upload
- White placeholder when no photo is available
- Card type selection
- Front/back ID preview
- Browser-based PNG download
- Mark as Generated, Printed, Released, or For Correction
- Basic audit logs
- Seeded admin user
- ProvideLabs orange/aqua theme

## Recommended local installation

Create a fresh Laravel project first:

```bash
composer create-project laravel/laravel providelabs-id-system
cd providelabs-id-system
```

Copy the contents of this starter folder into the Laravel project root and allow files to overwrite.

Then run:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Open `http://127.0.0.1:8000`.

Default seeded admin:

```text
Email: admin@providelabscorp.com
Password: ChangeMeNow!
```

Change this immediately after first login, or set a different value before seeding:

```env
DEFAULT_ADMIN_EMAIL=admin@providelabscorp.com
DEFAULT_ADMIN_PASSWORD=YourSecurePasswordHere
APP_TIMEZONE=Asia/Manila
FILESYSTEM_DISK=public
```

## Laravel Cloud notes

Suggested deploy command:

```bash
php artisan migrate --force
```

Use:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ids.providelabscorp.com
FILESYSTEM_DISK=public
```

## GitHub quick start

```bash
git init
git add .
git commit -m "Initial ProvideLabs ID system"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/providelabs-id-system.git
git push -u origin main
```

## Important privacy note

This system stores personal information, photos, birthdays, addresses, and emergency contact information. Use HTTPS, strong passwords, restricted accounts, backups, and access logs.
