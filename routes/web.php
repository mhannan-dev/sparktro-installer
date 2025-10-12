<?php

use Sparktro\Installer\Middleware\CheckInstallation;
use Sparktro\Installer\Http\Controllers\SecurityController;


$isInstalled = filter_var(env('APP_DB_SYNC', false), FILTER_VALIDATE_BOOLEAN)
    && filter_var(env('APP_SECURITY', false), FILTER_VALIDATE_BOOLEAN);

if (! $isInstalled) {
    Route::middleware([\Sparktro\Installer\Middleware\CheckInstallation::class, 'guest'])
        ->prefix('install')
        ->as('install.')
        ->group(function () {
            Route::get('/', [\Sparktro\Installer\Http\Controllers\SecurityController::class, 'requirements'])->name('requirements');
            Route::post('/database', [\Sparktro\Installer\Http\Controllers\SecurityController::class, 'database'])->name('database');
            Route::get('/admin', [\Sparktro\Installer\Http\Controllers\SecurityController::class, 'adminForm'])->name('admin.form');
            Route::post('/admin', [\Sparktro\Installer\Http\Controllers\SecurityController::class, 'adminStore'])->name('admin.store');
            Route::get('/finish', [\Sparktro\Installer\Http\Controllers\SecurityController::class, 'finish'])->name('finish');
            Route::post('/import/database', [\Sparktro\Installer\Http\Controllers\SecurityController::class, 'importDatabase'])->name('import.database');
        });
}

