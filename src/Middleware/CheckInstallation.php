<?php

namespace Sparktro\Installer\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckInstallation
{
    public function handle(Request $request, Closure $next)
    {
        $isInstalled = filter_var(env('APP_INSTALLED', false), FILTER_VALIDATE_BOOLEAN);

        // If not installed, redirect to installer
        if (!$isInstalled && !$request->is('install*') && !$request->is('_debugbar*')) {
            return redirect()->route('install.welcome');
        }

        // If installed, block installer access
        if ($isInstalled && $request->is('install*')) {
            return redirect('/')->with('info', 'Application is already installed.');
        }

        return $next($request);
    }
}