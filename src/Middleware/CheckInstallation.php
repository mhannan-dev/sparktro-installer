<?php

namespace Sparktro\Ignite\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class CheckInstallation
{
    // যদি true থাকে তাহলে local-এও middleware enforce হবে
    protected $forceInstallOnLocal = false;

    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $ip = $request->ip();

        $localHosts = ['localhost', '127.0.0.1'];
        $localIps = ['127.0.0.1', '::1'];

        $isLocalAccess = in_array($host, $localHosts)
            || in_array($ip, $localIps)
            || str_starts_with($ip, '192.168.')
            || str_starts_with($ip, '10.');

        // Skip middleware for local access only if not forced
        if (env('APP_ENV') === 'local' && $isLocalAccess && !$this->forceInstallOnLocal) {
            return $next($request);
        }

        // Installation & activation checks
        $isInstalled = filter_var(env('APP_INSTALLED', false), FILTER_VALIDATE_BOOLEAN);
        $activationPath = storage_path('activation.lock');
        $isActivated = false;
        $licenseKey = null;

        if (File::exists($activationPath)) {
            try {
                $data = json_decode(Crypt::decryptString(File::get($activationPath)), true);
                $licenseKey = $data['license_key'] ?? null;

                if (isset($data['domain']) && $data['domain'] === $host) {
                    $isActivated = true;
                }
            } catch (\Throwable $e) {
                $isActivated = false;
            }
        }

        // Remote license check
        if ($isActivated && $licenseKey) {
            try {
                $response = Http::timeout(5)->post('https://sparktro.com', [
                    'license_key' => $licenseKey,
                    'domain' => $host,
                ]);

                if ($response->failed() || !$response->json('valid', false)) {
                    $isActivated = false;
                }
            } catch (\Throwable $e) {
                $isActivated = true;
            }
        }

        // Redirect if not installed or activated
        if ((!$isInstalled || !$isActivated) && !$request->is('install*') && !$request->is('_debugbar*')) {
            return redirect()->route('install.welcome')
                ->with('info', 'Application not installed or activation required.');
        }

        // Block installer if already installed & activated
        if ($isInstalled && $isActivated && $request->is('install*')) {
            return redirect('/')->with('info', 'Application is already installed and activated.');
        }

        return $next($request);
    }
}
