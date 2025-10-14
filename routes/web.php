<?php

use Illuminate\Support\Facades\Route;
use Sparktro\Installer\Http\Controllers\SystemCheckController;

$isInstalled = filter_var(env('APP_INSTALLED', false), FILTER_VALIDATE_BOOLEAN);

if (!$isInstalled) {
    Route::middleware([\Sparktro\Installer\Middleware\CheckInstallation::class, 'web'])
        ->prefix('install')
        ->as('install.')
        ->group(function () {

            Route::match(['get'], '/', [SystemCheckController::class, 'welcome'])->name('welcome');

            Route::match(['get'], '/requirements', [SystemCheckController::class, 'dbForm'])->name('requirements');

            Route::match(['post'], '/environment', [SystemCheckController::class, 'environmentSet'])->name('environment.set');

            Route::match(['get'], '/admin', [SystemCheckController::class, 'adminForm'])->name('admin.form');

            Route::match(['post'], '/admin/store', [SystemCheckController::class, 'adminStore'])->name('admin.store');

            Route::match(['get', 'post'], '/migrate', [SystemCheckController::class, 'migrate'])->name('migrate');

            Route::match(['get', 'post'], '/import/database', [SystemCheckController::class, 'importDatabase'])->name('import.database');


            Route::match(['get', 'post'], '/finish', [SystemCheckController::class, 'finish'])->name('finish');
        });
}
