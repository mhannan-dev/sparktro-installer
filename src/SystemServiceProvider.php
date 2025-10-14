<?php

namespace Sparktro\Installer;

use Illuminate\Support\ServiceProvider;

class SystemServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'installer');
    }

    public function register()
    {
        //
    }
}
