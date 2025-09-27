<?php
use Sparktro\Installer\Http\Controllers\SecurityController;


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

