<?php

namespace Sparktro\Installer\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckInstallation
{
    public function handle(Request $request, Closure $next)
    {
        $installed = env('APP_INSTALLED', false);

        if (!$installed && ! $request->is('install*')) {
            return redirect('/install');
        }

        if ($installed && $request->is('install*')) {
            return redirect('/syslogin'); // or your default login page
        }

        return $next($request);
    }
}
