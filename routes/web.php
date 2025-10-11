<?php

use Sparktro\Installer\Middleware\CheckInstallation;
use Sparktro\Installer\Http\Controllers\SecurityController;

Route::middleware([CheckInstallation::class])->group(function () {
    Route::middleware(['guest'])->prefix('install')->group(function () {
        Route::get('/', [SecurityController::class, 'requirements'])->name('install.requirements');
        Route::post('/database', [SecurityController::class, 'database'])->name('install.database');
        Route::get('/admin', [SecurityController::class, 'adminForm'])->name('install.admin.form');
        Route::post('/admin', [SecurityController::class, 'adminStore'])->name('install.admin.store');
        Route::get('/finish', [SecurityController::class, 'finish'])->name('install.finish');
        Route::post('/import/database', [SecurityController::class, 'importDatabase'])->name('install.import.database');
    });
});
