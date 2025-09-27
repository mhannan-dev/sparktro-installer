<?php

namespace Sparktro\Installer\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckInstallation
{
    public function handle(Request $request, Closure $next)
    {
        $appSecurity = env('APP_SECURITY', 'false');

        if ($appSecurity === true && $request->is('install*')) {
            return redirect('syslogin');
        }

        if ($appSecurity !== true && $request->is('install*')) {
            return $next($request);
        }

        return $next($request);
    }
}
