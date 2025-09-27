<?php

namespace Sparktro\Installer\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PDO;
use Exception;

class SecurityController extends Controller
{
    /**
     * Show system requirements / initial installer page
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

        // direct project view, installer folder under resources/views
        return view('installer.requirements', compact('requirements'));
    }

    /**
     * Handle MySQL database configuration and run migrations
     */
    public function database(Request $request)
    {
        $data = $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'nullable|numeric',
            'db_name' => 'required|string',
            'db_user' => 'required|string',
            'db_pass' => 'nullable|string',
        ]);

        $host = $data['db_host'];
        $port = $data['db_port'] ?? 3306;
        $name = $data['db_name'];
        $user = $data['db_user'];
        $pass = $data['db_pass'] ?? '';

        // 1) Ensure .env exists and update MySQL settings
        $this->setEnv([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $host,
            'DB_PORT' => $port,
            'DB_DATABASE' => $name,
            'DB_USERNAME' => $user,
            'DB_PASSWORD' => $pass,
        ]);

        // 2) Update runtime config
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $host,
            'database.connections.mysql.port' => $port,
            'database.connections.mysql.database' => $name,
            'database.connections.mysql.username' => $user,
            'database.connections.mysql.password' => $pass,
        ]);

        // 3) Create database if not exists
        try {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $quotedDb = str_replace('`', '``', $name);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$quotedDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        } catch (Exception $e) {
            return redirect()->back()->withInput()->withErrors([
                'db' => 'Could not connect to MySQL: ' . $e->getMessage()
            ]);
        }

        // 4) Purge & reconnect DB
        DB::purge('mysql');
        DB::reconnect('mysql');

        // 5) Clear config & cache, generate key, run migrations
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('key:generate', ['--force' => true]);
            Artisan::call('migrate', ['--force' => true]);
        } catch (Exception $e) {
            return redirect()->back()->withInput()->withErrors([
                'migrate' => 'Migration failed: ' . $e->getMessage()
            ]);
        }

        return redirect()->route('install.admin.form')->with('success', 'MySQL database configured and migrated successfully.');
    }

    /**
     * Show admin account creation form
     */
    public function adminForm()
    {
        return view('installer.admin'); // resources/views/installer/admin.blade.php
    }

    /**
     * Store admin user credentials
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
     * Mark installation as complete
     */
    public function finish()
    {
        $this->setEnv(['APP_SECURITY' => 'true']);

        $appUrl = url('/syslogin');

        return view('installer.finish', compact('appUrl')); // resources/views/installer/finish.blade.php
    }

    /**
     * Create or update .env file
     */
    private function setEnv(array $values)
    {
        $path = base_path('.env');

        if (!File::exists($path)) {
            $example = base_path('.env.example');
            if (File::exists($example)) {
                File::put($path, File::get($example));
            } else {
                File::put($path, '');
            }
        }

        $content = File::get($path);

        foreach ($values as $key => $value) {
            $strValue = $value === '' ? '' : (string) $value;
            $replacement = $key . '=' . ($strValue === '' ? '' : "\"{$strValue}\"");

            $escapedKey = preg_quote($key, '/');
            $pattern = "/^{$escapedKey}=.*$/m";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                $content .= PHP_EOL . $replacement;
            }
        }

        File::put($path, $content);
    }
}
