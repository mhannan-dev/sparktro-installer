<?php

use Illuminate\Support\Facades\Route;
use Sparktro\Installer\Http\Controllers\SystemCheckController;

$isInstalled = filter_var(env('APP_INSTALLED', false), FILTER_VALIDATE_BOOLEAN);

if (!$isInstalled) {
    Route::middleware([\Sparktro\Installer\Middleware\CheckInstallation::class, 'web'])
        ->prefix('install')
        ->as('install.')
        ->group(function () {

            Route::match(['get', 'post'], '/', [SystemCheckController::class, 'welcome'])->name('welcome');

            Route::match(['get', 'post'], '/requirements', [SystemCheckController::class, 'requirements'])->name('requirements');

            Route::match(['get', 'post'], '/environment', [SystemCheckController::class, 'environment'])->name('environment');

            Route::match(['get', 'post'], '/database', [SystemCheckController::class, 'database'])->name('database');

            Route::match(['get', 'post'], '/migrate', [SystemCheckController::class, 'migrate'])->name('migrate');

            Route::match(['get', 'post'], '/import/database', [SystemCheckController::class, 'importDatabase'])->name('import.database');

            Route::match(['get', 'post'], '/admin', [SystemCheckController::class, 'adminForm'])->name('admin.form');
            Route::match(['get', 'post'], '/admin/store', [SystemCheckController::class, 'adminStore'])->name('admin.store');

            Route::match(['get', 'post'], '/finish', [SystemCheckController::class, 'finish'])->name('finish');
        });
}
