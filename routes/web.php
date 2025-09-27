<?php
use Sparktro\Installer\Http\Controllers\SecurityController;

Route::prefix('install')->group(function () {
    Route::get('/', [SecurityController::class, 'requirements'])->name('install.requirements');
    Route::post('/database', [SecurityController::class, 'database'])->name('install.database');
    Route::get('/admin', [SecurityController::class, 'adminForm'])->name('install.admin.form');
    Route::post('/admin', [SecurityController::class, 'adminStore'])->name('install.admin.store');
    Route::get('/finish', [SecurityController::class, 'finish'])->name('install.finish');
});


Route::prefix('install')
    ->name('install.')
    ->controller(SecurityController::class)
    ->group(function () {
        Route::get('/', 'requirements')->name('requirements');
        Route::post('/database', 'database')->name('database');
        Route::get('/admin', 'adminForm')->name('admin.form');
        Route::post('/admin', 'adminStore')->name('admin.store');
        Route::get('/finish', 'finish')->name('finish');
    });

