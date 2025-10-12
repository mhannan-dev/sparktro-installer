<?php

namespace Sparktro\Installer\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckInstallation
{
    public function handle(Request $request, Closure $next)
    {
        $isDbSynced = filter_var(env('APP_DB_SYNC', false), FILTER_VALIDATE_BOOLEAN);
        $isSecured  = filter_var(env('APP_SECURITY', false), FILTER_VALIDATE_BOOLEAN);

        if ($isDbSynced && $isSecured && $request->is('install*')) {
            return abort(404);

        }

        if (! $isDbSynced || ! $isSecured) {
            return $next($request);
        }

        return $next($request);
    }
}
