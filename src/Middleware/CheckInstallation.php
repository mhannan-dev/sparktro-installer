<?php

namespace Sparktro\Installer\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckInstallation
{
    // public function handle(Request $request, Closure $next)
    // {
    //     $appSecurity = filter_var(env('APP_SECURITY', false), FILTER_VALIDATE_BOOLEAN);

    //     if ($appSecurity === true && $request->is('install*')) {
    //         return redirect('login');
    //     }

    //     if ($appSecurity !== true && $request->is('install*')) {
    //         return $next($request);
    //     }

    //     return $next($request);
    // }

    public function handle(Request $request, Closure $next)
    {
        $appSecurity = filter_var(env('APP_SECURITY', false), FILTER_VALIDATE_BOOLEAN);

        if (!$appSecurity) {
            if (!$request->is('install*')) {
                return redirect()->route('install.requirements');
            }
        }

        if ($appSecurity && $request->is('install*')) {
            return redirect('/');
        }

        return $next($request);
    }
}
