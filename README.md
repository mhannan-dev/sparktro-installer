# Sparktro Laravel Installer Package

A complete **Laravel Installer Package** designed for **Codecanyon projects**.  

It provides:

- ✅ System requirements check  
- ✅ Database setup  
- ✅ Admin user creation  
- ✅ Blade-based UI for installation steps  
- ✅ Middleware to prevent access before installation  

---

## Installation

Install the package via Composer:

```bash
composer require sparktro/installer
```

---

## Setup

### Middleware

Add the installer middleware to your `Http\Kernel.php` (if not already applied globally):

```php
protected $middleware = [
    \Sparktro\Installer\Middleware\CheckInstallation::class,
];
```

### Auto-discovery

The package supports **Laravel auto-discovery**, so the `InstallerServiceProvider` is automatically registered.

### Environment

During installation, the package will:

- Automatically update your `.env` file with the database credentials you provide.
- Set `APP_INSTALLED=true` after the installation is completed.

---

## Usage

Open your browser and navigate to:

```
http://your-app.test/install
```

Follow the installation steps:

1. ✅ Check system requirements  
2. ✅ Enter database credentials  
3. ✅ Create an admin account  
4. ✅ Complete installation  

> After installation, accessing `/install` will automatically redirect to `/syslogin`.

---

## Security Note

- For security reasons, **delete the installer folder** after installation.  
- Only use this package in development or controlled environments before going live.

---

## Compatibility

- Laravel 10+  
- PHP 8.1+  

---

## License

**MIT License**

---

## Author

**Muhammad Hannan**  
Email: [mdhannan.info@gmail.com](mailto:mdhannan.info@gmail.com)
