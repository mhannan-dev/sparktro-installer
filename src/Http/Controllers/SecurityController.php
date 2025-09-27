<?php

namespace Sparktro\Installer\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class SecurityController extends Controller
{
    /**
     * Check and display system requirements.
     */
    public function requirements()
    {
        $requirements = [
            'PHP >= 8.2' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'PDO' => extension_loaded('pdo'),
            'Mbstring' => extension_loaded('mbstring'),
            'OpenSSL' => extension_loaded('openssl'),
            'Writable storage/' => is_writable(storage_path()),
            'Writable bootstrap/cache/' => is_writable(base_path('bootstrap/cache')),
        ];

        return view('installer::installer.requirements', compact('requirements'));
    }

    /**
     * Handle database configuration and run migrations.
     */
    public function database(Request $request)
    {
        $data = $request->validate([
            'db_host' => 'nullable|string',
            'db_port' => 'nullable|numeric',
            'db_name' => 'required|string',
            'db_user' => 'nullable|string',
            'db_pass' => 'nullable|string',
        ]);

        $this->setEnv([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $data['db_host'] ?? '127.0.0.1',
            'DB_PORT' => $data['db_port'] ?? 3306,
            'DB_DATABASE' => $data['db_name'],
            'DB_USERNAME' => $data['db_user'] ?? 'root',
            'DB_PASSWORD' => $data['db_pass'] ?? '',
        ]);

        // Refresh configuration & run migrations
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('key:generate', ['--force' => true]);
        Artisan::call('migrate', ['--force' => true]);

        return redirect()->route('install.admin.form');
    }

    /**
     * Show admin account creation form.
     */
    public function adminForm()
    {
        return view('installer::installer.admin');
    }

    /**
     * Store admin user credentials.
     */
    public function adminStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => 1,
        ]);

        return redirect()->route('install.finish');
    }

    /**
     * Mark installation as complete.
     */
    public function finish()
    {
        $this->setEnv(['APP_SECURITY' => 'true']);

        $appUrl = url('/syslogin');

        return view('installer::installer.finish', compact('appUrl'));
    }

    /**
     * Update .env file with given values.
     */
    private function setEnv(array $values)
    {
        $path = base_path('.env');

        if (!File::exists($path)) {
            File::put($path, File::get(base_path('.env.example')));
        }

        $content = File::get($path);

        foreach ($values as $key => $value) {
            $escapedValue = $value === '' ? '' : "\"{$value}\"";

            $pattern = "/^{$key}=.*$/m";
            $replacement = "{$key}={$escapedValue}";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                $content .= "\n{$replacement}";
            }
        }

        File::put($path, $content);
    }
}
