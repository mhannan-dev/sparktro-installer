<?php

namespace Sparktro\Installer\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;
use PDO;

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

        // Normalize values
        $host = $data['db_host'] ?? '127.0.0.1';
        $port = $data['db_port'] ?? 3306;
        $name = $data['db_name'];
        $user = $data['db_user'] ?? 'root';
        $pass = $data['db_pass'] ?? '';

        // 1) Ensure .env exists and update it
        $this->setEnv([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $host,
            'DB_PORT' => $port,
            'DB_DATABASE' => $name,
            'DB_USERNAME' => $user,
            'DB_PASSWORD' => $pass,
        ]);

        // 2) Update runtime config so Artisan::call('migrate') uses new settings
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $host,
            'database.connections.mysql.port' => $port,
            'database.connections.mysql.database' => $name,
            'database.connections.mysql.username' => $user,
            'database.connections.mysql.password' => $pass,
        ]);

        try {
            // Use PDO to connect to MySQL server (without selecting a DB)
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $quotedDb = str_replace('`', '``', $name); 
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$quotedDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        } catch (Exception $e) {
            return redirect()->back()->withInput()->withErrors([
                'db' => 'Could not connect to the database server: ' . $e->getMessage()
            ]);
        }

        // 4) Purge and reconnect the DB connection so Laravel uses new DB
        try {
            DB::purge('mysql');
            DB::reconnect('mysql');

            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('key:generate', ['--force' => true]);

            // 5) Run migrations
            Artisan::call('migrate', ['--force' => true]);
        } catch (Exception $e) {
            return redirect()->back()->withInput()->withErrors([
                'migrate' => 'Migration failed: ' . $e->getMessage()
            ]);
        }

        return redirect()->route('install.admin.form')->with('success', 'Database configured and migrated successfully.');
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
     *
     * - Keeps existing entries, replaces keys when present, otherwise appends.
     * - Handles empty values (writes without surrounding quotes).
     */
    private function setEnv(array $values)
    {
        $path = base_path('.env');

        if (!File::exists($path)) {
            // copy example if .env not present
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
