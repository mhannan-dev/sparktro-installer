<?php

namespace Sparktro\Installer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use PDO;
use Exception;

class SecurityController extends Controller
{
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

        // 1) Ensure .env exists
        $this->setEnv([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $host,
            'DB_PORT' => $port,
            'DB_DATABASE' => $name,
            'DB_USERNAME' => $user,
            'DB_PASSWORD' => $pass,
        ]);

        // 2) Runtime config update
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

        // 4) Purge & reconnect
        DB::purge('mysql');
        DB::reconnect('mysql');

        // 5) Clear cache & run migrations
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
