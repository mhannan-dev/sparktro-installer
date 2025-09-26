<?php

namespace Sparktro\Installer;

use Illuminate\Support\ServiceProvider;

class SparkTroInstallerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Load package routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Load package views with proper namespace
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'installer');
    }

    public function register()
    {
        //
    }
}
