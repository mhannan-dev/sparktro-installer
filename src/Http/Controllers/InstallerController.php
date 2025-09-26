<?php

namespace Sparktro\Installer\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class InstallerController extends Controller
{
    public function requirements()
    {
        $requirements = [
            'PHP >= 8.1' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'PDO' => extension_loaded('pdo'),
            'Mbstring' => extension_loaded('mbstring'),
            'OpenSSL' => extension_loaded('openssl'),
            'Writable storage/' => is_writable(storage_path()),
            'Writable bootstrap/cache/' => is_writable(base_path('bootstrap/cache')),
        ];
        return view('installer::installer.requirements', compact('requirements'));

    }

    public function database(Request $request)
    {
        $data = $request->validate([
            'db_host' => 'nullable|string',
            'db_port' => 'nullable|numeric',
            'db_name' => 'required|string',
            'db_user' => 'nullable|string',
            'db_pass' => 'nullable|string',
        ]);

        $dbHost = $data['db_host'] ?? "127.0.0.1";
        $dbPort = $data['db_port'] ?? 3306;
        $dbName = $data['db_name'];
        $dbUser = $data['db_user'] ?? "root";
        $dbPass = $data['db_pass'] ?? '';

        $this->setEnv([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $dbHost,
            'DB_PORT' => $dbPort,
            'DB_DATABASE' => $dbName,
            'DB_USERNAME' => $dbUser,
            'DB_PASSWORD' => $dbPass,
        ]);

        // Config refresh এবং migration
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('key:generate', ['--force' => true]);
        Artisan::call('migrate', ['--force' => true]);

        return redirect()->route('install.admin.form');
    }


    public function adminForm()
    {
        return view('installer::installer.admin');
    }

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

    public function finish()
    {
        $this->setEnv(['APP_INSTALLED' => 'true']);
        $appUrl = url('/syslogin');

        return view('installer::installer.finish', compact('appUrl'));
    }

    private function setEnv(array $values)
    {
        $path = base_path('.env');

        if (!File::exists($path)) {
            File::put($path, File::get(base_path('.env.example')));
        }

        $content = File::get($path);

        foreach ($values as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            $replacement = $key.'="'.$value.'"';
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                $content .= "\n$replacement";
            }
        }
        File::put($path, $content);
    }
}
