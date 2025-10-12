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
    public function welcome()
    {
        $this->ensureStorageExists();
        return view('installer::installer.welcome');
    }

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
            'Ctype' => extension_loaded('ctype'),
            'JSON' => extension_loaded('json'),
            'BCMath' => extension_loaded('bcmath'),
            'XML' => extension_loaded('xml'),
            'Tokenizer' => extension_loaded('tokenizer'),

            // Writable directories
            'Writable storage/' => is_writable(storage_path()),
            'Writable storage/framework/' => is_writable(storage_path('framework')),
            'Writable storage/logs/' => is_writable(storage_path('logs')),
            'Writable bootstrap/cache/' => is_writable(base_path('bootstrap/cache')),
            'Writable .env' => !file_exists(base_path('.env')) || is_writable(base_path('.env')),
        ];

        $allRequirementsMet = !in_array(false, $requirements, true);
        
        return view('installer::installer.requirements', compact('requirements', 'allRequirementsMet'));
    }

    public function environment(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'app_name' => 'required|string|max:255',
                'app_url' => 'required|url',
                'db_host' => 'required|string',
                'db_port' => 'required|numeric',
                'db_name' => 'required|string',
                'db_user' => 'required|string',
                'db_pass' => 'nullable|string',
            ]);

            try {
                $this->ensureEnv();
                
                // Set basic app configuration
                $this->setEnv([
                    'APP_NAME' => '"' . $data['app_name'] . '"',
                    'APP_URL' => $data['app_url'],
                    'APP_ENV' => 'production',
                    'APP_DEBUG' => 'false'
                ]);

                return redirect()->route('install.database')->with('data', $data);
            } catch (Exception $e) {
                return redirect()->back()->with('error', $e->getMessage())->withInput();
            }
        }

        return view('installer::installer.environment');
    }

    public function database(Request $request)
    {
        // Get data from session or request
        $data = $request->session()->get('data') ?? $request->all();
        
        if (empty($data)) {
            return redirect()->route('install.environment');
        }

        try {
            // 🔧 Increase limits ONLY for installer
            ini_set('max_execution_time', 300);
            ini_set('memory_limit', '512M');

            // 2️⃣ Generate APP_KEY
            Artisan::call('key:generate', ['--force' => true]);

            // Update .env with database credentials
            Log::info("Installer: Updating .env with DB credentials");
            $this->setEnv([
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => $data['db_host'],
                'DB_PORT' => $data['db_port'] ?? 3306,
                'DB_DATABASE' => $data['db_name'],
                'DB_USERNAME' => $data['db_user'],
                'DB_PASSWORD' => $data['db_pass'] ?? ''
            ]);

            // Update runtime config
            config([
                'database.default' => 'mysql',
                'database.connections.mysql.host' => $data['db_host'],
                'database.connections.mysql.port' => $data['db_port'] ?? 3306,
                'database.connections.mysql.database' => $data['db_name'],
                'database.connections.mysql.username' => $data['db_user'],
                'database.connections.mysql.password' => $data['db_pass'] ?? '',
            ]);

            // 🔥 Critical: Refresh DB connection
            DB::purge('mysql');
            DB::reconnect('mysql');

            // Test database connection
            DB::connection()->getPdo();

            // Clear caches
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            Log::info('Installer: Database setup completed successfully.');
            
            // Check if we should import SQL or run migrations
            $sqlPath = base_path('database/factories/application.sql');
            if (File::exists($sqlPath)) {
                return redirect()->route('install.import.database');
            } else {
                return redirect()->route('install.migrate');
            }
            
        } catch (Exception $e) {
            Log::error('Database setup failed: ' . $e->getMessage());
            return redirect()->route('install.environment')
                ->with('error', 'Database connection failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function migrate()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
            
            return redirect()->route('install.admin.form')
                ->with('success', 'Migrations and seeders ran successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Migration failed: ' . $e->getMessage());
        }
    }

    public function importDatabase()
    {
        try {
            $sqlPath = base_path('database/factories/application.sql');
            if (!File::exists($sqlPath)) {
                return redirect()->route('install.migrate');
            }

            $this->importSqlFile($sqlPath);
            
            return redirect()->route('install.admin.form')
                ->with('success', 'Database imported successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Database import failed: ' . $e->getMessage());
        }
    }

    public function adminForm()
    {
        Log::info('Installer: Displaying admin user creation form.');
        return view('installer::installer.admin');
    }

    public function adminStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        try {
            // Update existing user or create new
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'role_id' => 1,
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                ]
            );

            $this->setEnv([
                'SESSION_DRIVER' => 'database',
                'CACHE_STORE' => 'database',
                'APP_DB_SYNC' => 'true'
            ]);

            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            
            return redirect()->route('install.finish')->with('success', 'Admin user created successfully.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['admin' => 'Failed to create admin user: ' . $e->getMessage()])->withInput();
        }
    }

    public function finish()
    {
        // Lock installer
        $this->setEnv(['APP_INSTALLED' => 'true']);

        $appUrl = url('/');
        Log::info("Installer: Installation complete. Login URL: {$appUrl}");

        // Final optimizations
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        return view('installer::installer.finish', compact('appUrl'));
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
            public_path('storage'),
            public_path('storage/uploads'),
        ];

        foreach ($storagePaths as $path) {
            if (!File::exists($path)) {
                File::makeDirectory($path, 0775, true);
            }

            // Add .gitignore so empty folders can exist in zip
            $gitignore = $path . '/.gitignore';
            if (!File::exists($gitignore)) {
                File::put($gitignore, "*\n!.gitignore\n");
            }

            // Make sure folder is writable
            @chmod($path, 0775);
        }

        // Ensure laravel.log exists
        $logFile = storage_path('logs/laravel.log');
        if (!File::exists($logFile)) {
            File::put($logFile, '');
        }
        chmod($logFile, 0666);
    }

    private function setEnv(array $values)
    {
        $path = base_path('.env');
        if (!File::exists($path)) {
            $this->ensureEnv();
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
        @chmod($envPath, 0664);
    }
}