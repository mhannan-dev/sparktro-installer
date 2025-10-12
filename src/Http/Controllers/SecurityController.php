<?php

namespace Sparktro\Installer\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class SecurityController extends Controller
{
    public function requirements()
    {

         $this->ensureStorageExists();
        $requirements = [
            // PHP version
            'PHP >= 8.2' => version_compare(PHP_VERSION, '8.2.0', '>='),

            // PHP extensions
            'PDO' => extension_loaded('pdo'),
            'Mbstring' => extension_loaded('mbstring'),
            'OpenSSL' => extension_loaded('openssl'),

            // Writable directories
            'Writable storage/' => is_writable(storage_path()),
            'Writable storage/framework/' => is_writable(storage_path('framework')),
            'Writable storage/logs/' => is_writable(storage_path('logs')),
            'Writable bootstrap/cache/' => is_writable(base_path('bootstrap/cache')),
            'Writable .env' => is_writable(base_path('.env')),
        ];
        return view('installer::installer.requirements', compact('requirements'));
    }

     protected function ensureStorageExists()
    {
        $storagePaths = [
            storage_path('app/public/uploads'),
            storage_path('app/private'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('framework/testing'),
            storage_path('logs'),
            storage_path('pail'),
            base_path('bootstrap/cache'),
            public_path('storage'), // public/storage
            public_path('storage/uploads'), // optional
        ];

        foreach ($storagePaths as $path) {
            if (!File::exists($path)) {
                File::makeDirectory($path, 0775, true);
            }

            // .gitignore create
            $gitignore = $path . '/.gitignore';
            if (!File::exists($gitignore)) {
                File::put($gitignore, "*\n!.gitignore\n");
            }
        }

        // Laravel log file নিশ্চিত করা
        $logFile = storage_path('logs/laravel.log');
        if (!File::exists($logFile)) {
            File::put($logFile, '');
        }
        chmod($logFile, 0666);
    }



    public function database(Request $request)
    {
        // 🔧 Increase limits ONLY for installer
        ini_set('max_execution_time', 300); // 5 minutes
        ini_set('memory_limit', '512M');

        $this->ensureEnv();

        // 2️⃣ Generate APP_KEY
        Artisan::call('key:generate');

        // 3️⃣ Optional: clear caches
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Log::info("Installer: Env created");
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

        // Update .env
        Log::info("Installer: Updating .env with DB credentials");
        $this->setEnv([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $host,
            'DB_PORT' => $port,
            'DB_DATABASE' => $name,
            'DB_USERNAME' => $user,
            'DB_PASSWORD' => $pass
        ]);

        // Update runtime config
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $host,
            'database.connections.mysql.port' => $port,
            'database.connections.mysql.database' => $name,
            'database.connections.mysql.username' => $user,
            'database.connections.mysql.password' => $pass,
        ]);

        // 🔥 Critical: Refresh DB connection
        DB::purge('mysql');
        DB::reconnect('mysql');

        // Clear caches
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        Log::info('Installer: Database setup completed successfully.');
        return redirect()->route('install.admin.form')
            ->with('success', 'Database configured and initial data imported successfully.');
    }

    public function adminForm()
    {
        Log::info('Installer: Displaying admin user creation form.');
        return view('installer::installer.admin');
    }

    public function adminStore(Request $request)
    {
        // Import SQL if exists
        $sqlPath = base_path('database/factories/application.sql');
        $this->importSqlFile($sqlPath);

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        try {
            // Update existing user or create new
            $updateData = [
                'name' => $data['name'],
                'role_id' => 1,
                'password' => Hash::make($data['password']),
            ];

            User::updateOrCreate(
                ['email' => $data['email']],
                $updateData
            );

            // Enable foreign key checks
            // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            Artisan::call('migrate', ['--force' => true]);

            // Run seeders
            Artisan::call('db:seed', ['--force' => true]);

            $this->setEnv([
                'SESSION_DRIVER' => 'database',
                'CACHE_STORE' => 'database',
            ]);


            // Mark DB sync in .env
            $this->setEnv(['APP_DB_SYNC' => 'true']);

            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            return redirect()->route('install.finish')->with('success', 'Admin user setup completed.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['admin' => 'Failed to create admin user.']);
        }
    }

    public function finish()
    {
        // Lock installer
        $this->setEnv(['APP_SECURITY' => 'true']);

        $appUrl = url('/');
        Log::info("Installer: Installation complete. Login URL: {$appUrl}");

        return view('installer::installer.finish', compact('appUrl'));
    }

    private function setEnv(array $values)
    {
        $path = base_path('.env');
        if (!File::exists($path)) {
            $example = base_path('.env.example');
            File::put($path, File::exists($example) ? File::get($example) : '');
            Log::info('Installer: Created .env file.');
        }

        $content = File::get($path);
        foreach ($values as $key => $value) {
            $strValue = $value === '' ? '' : (string) $value;
            $replacement = $key . '=' . ($strValue === '' ? '' : "\"{$strValue}\"");
            $pattern = "/^" . preg_quote($key, '/') . "=.*$/m";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
                Log::debug("Installer: Updated .env key: {$key}");
            } else {
                $content .= PHP_EOL . $replacement;
                Log::debug("Installer: Added .env key: {$key}");
            }
        }

        File::put($path, $content);
        Log::debug('Installer: .env updated.');
    }

    /**
     * Import SQL file line-by-line (memory efficient, no hang)
     */
    private function importSqlFile(string $filePath)
    {
        if (!File::exists($filePath)) {
            throw new Exception("SQL file not found: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Cannot open SQL file: {$filePath}");
        }

        $sql = '';
        $lineNumber = 0;

        while (($line = fgets($handle)) !== false) {
            $lineNumber++;
            $trimmedLine = trim($line);

            // Skip comments and empty lines
            if (
                $trimmedLine === '' ||
                strpos($trimmedLine, '--') === 0 ||
                strpos($trimmedLine, '#') === 0 ||
                strpos($trimmedLine, '/*') === 0
            ) {
                continue;
            }

            $sql .= $line;

            // Execute when statement ends with ;
            if (substr(rtrim($line), -1) === ';') {
                $statement = rtrim($sql, " \t\n\r\0\x0B;");
                if (!empty($statement)) {
                    try {
                        DB::statement($statement);
                    } catch (Exception $e) {
                        fclose($handle);
                        Log::error("SQL error at line {$lineNumber}: " . $e->getMessage());
                        throw new Exception("SQL failed at line {$lineNumber}: " . $e->getMessage());
                    }
                }
                $sql = ''; // Reset buffer
            }
        }

        fclose($handle);
        Log::info('Installer: SQL file imported successfully (streamed).');
    }


    public function importDatabase()
    {
        try {
            DB::beginTransaction();

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $tables = array_map(fn($t) => array_values((array)$t)[0], $tables);

            $excluded = ['users'];

            foreach ($tables as $table) {
                if (!in_array($table, $excluded)) {
                    DB::table($table)->truncate();
                }
            }

            // Enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            Artisan::call('migrate', ['--force' => true]);

            // Run seeders
            Artisan::call('db:seed', ['--force' => true]);

            // Mark DB sync in .env
            $this->setEnv(['APP_DB_SYNC' => 'true']);

            // Clear config & cache
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            DB::commit();

            return redirect()->back()->with('success', 'DB imported successfully!');
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    public function ensureEnv()
    {
        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');

        // 1️⃣ Check if .env exists
        if (!File::exists($envPath)) {
            if (File::exists($envExamplePath)) {
                // Copy .env.example to .env
                File::copy($envExamplePath, $envPath);
                Log::info("Installer: .env file created from .env.example");
            } else {
                Log::error("Installer: .env.example not found. Cannot create .env.");
                throw new \Exception(".env.example not found. Please create one manually.");
            }
        } else {
            Log::info("Installer: .env already exists");
        }

        // 2️⃣ Set correct permissions
        $webUser = 'www-data'; // adjust if your web server user is different
        @chmod($envPath, 0664); // writable by owner & group
        @chown($envPath, $webUser);
    }
}
