<?php

use Sparktro\Installer\Http\Controllers\InstallerController;


Route::prefix('install')->group(function () {
    Route::get('/', [InstallerController::class, 'requirements'])->name('install.requirements');
    Route::post('/database', [InstallerController::class, 'database'])->name('install.database');
    Route::get('/admin', [InstallerController::class, 'adminForm'])->name('install.admin.form');
    Route::post('/admin', [InstallerController::class, 'adminStore'])->name('install.admin.store');
    Route::get('/finish', [InstallerController::class, 'finish'])->name('install.finish');
});
