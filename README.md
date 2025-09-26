# `README.md`

````markdown
# Sparktro Laravel Installer Package

A complete **Laravel Installer Package** for Codecanyon projects.  
It provides:

- System requirements check
- Database setup
- Admin user creation
- Blade UI for installation steps
- Middleware to prevent access before installation

---

## Installation

Install the package via Composer:

```bash
composer require sparktro/installer
````

---

## Setup

1. **Middleware**
   Add the installer middleware in `app/Http/Kernel.php`:

```php
protected $middleware = [
    \Sparktro\Installer\Middleware\CheckInstallation::class,
];
```

2. **Auto-discovery**
   The package uses Laravel auto-discovery, so the `InstallerServiceProvider` is automatically registered.

3. **Environment**
   The installer will automatically update your `.env` file with database credentials and set `APP_INSTALLED=true` when the installation is completed.

---

## Usage

Open your browser and navigate to:

```
http://your-app.test/install
```

Follow the steps:

1. Check system requirements
2. Enter database credentials
3. Create an admin account
4. Finish installation

After installation, accessing `/install` will automatically redirect to `/syslogin`.

---

## Folder Structure

```
sparktro-installer/
├── composer.json
├── LICENSE
├── src/
│   ├── Http/Controllers/InstallerController.php
│   ├── Middleware/CheckInstallation.php
│   └── InstallerServiceProvider.php
├── routes/web.php
└── resources/views/installer/
    ├── layout.blade.php
    ├── requirements.blade.php
    ├── admin.blade.php
    └── finish.blade.php
```

---

## Notes

* For security, delete the installer folder after installation.
* Compatible with **Laravel 10** and **PHP 8.1+**.
* License: **MIT**

---

## Author

**Muhammad Hannan**
Email: [mdhannan.info@gmail.com](mailto:mdhannan.info@gmail.com)

````
