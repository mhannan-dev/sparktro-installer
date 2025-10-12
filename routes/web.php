<?php

use Illuminate\Support\Facades\Route;
use Sparktro\Installer\Http\Controllers\SecurityController;

$isInstalled = filter_var(env('APP_INSTALLED', false), FILTER_VALIDATE_BOOLEAN);

if (!$isInstalled) {
    Route::middleware([\Sparktro\Installer\Middleware\CheckInstallation::class, 'web'])
        ->prefix('install')
        ->as('install.')
        ->group(function () {
            Route::get('/', [SecurityController::class, 'welcome'])->name('welcome');

            Route::get('/requirements', [SecurityController::class, 'requirements'])->name('requirements');

            Route::match(['get', 'post'], '/environment', [SecurityController::class, 'environment'])->name('environment');

            Route::post('/database', [SecurityController::class, 'database'])->name('database');

            Route::get('/migrate', [SecurityController::class, 'migrate'])->name('migrate');
            Route::post('/import/database', [SecurityController::class, 'importDatabase'])->name('import.database');

            Route::get('/admin', [SecurityController::class, 'adminForm'])->name('admin.form');
            Route::post('/admin', [SecurityController::class, 'adminStore'])->name('admin.store');

            Route::get('/finish', [SecurityController::class, 'finish'])->name('finish');
        });
}
