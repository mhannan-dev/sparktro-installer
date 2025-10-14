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

class SystemCheckController extends Controller
{
    public function welcome()
    {
        $this->ensureStorageExists();
        $requirements = [
            'PHP >= 8.2' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'PDO' => extension_loaded('pdo'),
            'Mbstring' => extension_loaded('mbstring'),
            'OpenSSL' => extension_loaded('openssl'),
            'Ctype' => extension_loaded('ctype'),
            'JSON' => extension_loaded('json'),
            'BCMath' => extension_loaded('bcmath'),
            'XML' => extension_loaded('xml'),
            'Tokenizer' => extension_loaded('tokenizer'),
            'Writable storage/' => is_writable(storage_path()),
            'Writable storage/framework/' => is_writable(storage_path('framework')),
            'Writable storage/logs/' => is_writable(storage_path('logs')),
            'Writable bootstrap/cache/' => is_writable(base_path('bootstrap/cache')),
            'Writable .env' => !File::exists(base_path('.env')) || is_writable(base_path('.env')),
        ];

        $allRequirementsMet = !in_array(false, $requirements, true);

        return view('installer::installer.welcome', compact('requirements', 'allRequirementsMet'));
    }

    public function dbForm()
    {

        return view('installer::installer.requirements');
    }

    public function environmentSet(Request $request)
    {

        $data = $request->validate([
            'domain_name' => 'required|string|max:255',
            'codecanyon_username' => 'required|string|max:255',
            'codecanyon_license_key' => 'required|string|max:255',
            'app_name' => 'required|string|max:255',
            'db_host' => 'required|string',
            'db_port' => 'required|numeric',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        $envData = [
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $data['db_host'],
            'DB_PORT' => $data['db_port'],
            'DB_DATABASE' => $data['db_database'],
            'DB_USERNAME' => $data['db_username'],
            'DB_PASSWORD' => $data['db_password'] ?? '',
            'APP_DB' => 'true',
            'APP_NAME' => $data['app_name'],
            'APP_URL' => $data['application_url'],
            'CODECANYON_USERNAME' => $data['codecanyon_username'],
            'CODECANYON_LICENSE' => $data['codecanyon_license_key'],
        ];

        try {
            $this->ensureEnv();
            $this->setEnv($envData);

            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');

            // Test DB connection
            config([
                'database.default' => 'mysql',
                'database.connections.mysql.host' => $envData['DB_HOST'],
                'database.connections.mysql.port' => $envData['DB_PORT'],
                'database.connections.mysql.database' => $envData['DB_DATABASE'],
                'database.connections.mysql.username' => $envData['DB_USERNAME'],
                'database.connections.mysql.password' => $envData['DB_PASSWORD'],
            ]);

            Log::info('Database connection test successful', ['host' => $envData['DB_HOST'], 'database' => $envData['DB_DATABASE']]);

            $request->session()->put('installer_data', $envData);

            return redirect()->route('install.admin.form')->with('success', 'Environment settings saved successfully.');
        } catch (Exception $e) {

            Log::error('Environment setup failed: ' . $e->getMessage());
            return back()->with('error', $e->getMessage())->withInput();
        }

    }

    public function database(Request $request)
    {
        $data = $request->session()->get('installer_data');

        if (empty($data)) {
            Log::warning('No installer data found in session, redirecting to environment');
            return redirect()->route('install.environment');
        }

        return view('installer::installer.database');
    }

    public function migrate()
    {
        try {
            Log::info('Running migrations and seeders');

            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);

            Log::info('Migrations and seeders completed');

            return redirect()->route('install.admin.form')
                ->with('success', 'Migrations and seeders ran successfully.');
        } catch (Exception $e) {
            Log::error('Migration failed: ' . $e->getMessage());
            return back()->with('error', 'Migration failed: ' . $e->getMessage());
        }
    }

    public function importDatabase(Request $request)
    {
        try {
            $sqlPath = base_path('database/factories/application.sql');

            if (!File::exists($sqlPath)) {
                Log::warning('SQL file not found, redirecting to migrate');
                return redirect()->route('install.migrate');
            }

            $this->importSqlFile($sqlPath);
            Log::info('SQL file imported successfully');

            return redirect()->route('install.admin.form')
                ->with('success', 'Database imported successfully.');
        } catch (Exception $e) {
            Log::error('Database import failed: ' . $e->getMessage());
            return back()->with('error', 'Database import failed: ' . $e->getMessage());
        }
    }

    public function adminForm()
    {
        Log::info('Displaying admin creation form');
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
            $user = DB::table('users')->insert([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => 1,
                'can_login' => 1,
                'status' => 1
            ]);

            $this->setEnv([
                'SESSION_DRIVER' => 'database',
                'CACHE_STORE' => 'database',
                'APP_DB_SYNC' => 'true'
            ]);

            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            return redirect()->route('install.finish')->with('success', 'Admin user created successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['admin' => 'Failed to create admin user: ' . $e->getMessage()])->withInput();
        }
    }

    public function finish()
    {
        $this->setEnv(['APP_INSTALLED' => 'true']);
        $appUrl = url('/');
        Log::info("Installation finished. App URL: {$appUrl}");

        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        return view('installer::installer.finish', compact('appUrl'));
    }

    protected function ensureStorageExists()
    {
        $paths = [
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

        foreach ($paths as $path) {
            if (!File::exists($path)) {
                File::makeDirectory($path, 0775, true);
            }
            $gitignore = $path . '/.gitignore';
            if (!File::exists($gitignore)) {
                File::put($gitignore, "*\n!.gitignore\n");
            }
            @chmod($path, 0775);
        }

        $logFile = storage_path('logs/laravel.log');
        if (!File::exists($logFile)) {
            File::put($logFile, '');
        }
        chmod($logFile, 0666);
    }

    private function setEnv(array $values)
    {
        $this->ensureEnv();
        $path = base_path('.env');
        $content = File::get($path);

        foreach ($values as $key => $value) {
            $cleanValue = trim($value, '"\'');
            $needsQuotes = preg_match('/[\s#\']/', $cleanValue);
            $formattedValue = $needsQuotes ? "\"{$cleanValue}\"" : $cleanValue;
            $pattern = "/^{$key}=.*$/m";
            $replacement = "{$key}={$formattedValue}";
            $content = preg_match($pattern, $content)
                ? preg_replace($pattern, $replacement, $content, 1)
                : $content.PHP_EOL.$replacement;
            Log::debug("Updated .env: {$key}={$formattedValue}");
        }

        File::put($path, $content);
        Log::info('.env updated successfully');
    }

    private function importSqlFile(string $filePath)
    {
        if (!File::exists($filePath)) {
            throw new Exception("SQL file not found");
        }

        $handle = fopen($filePath, 'r');
        $sql = '';
        $lineNumber = 0;

        while (($line = fgets($handle)) !== false) {
            $lineNumber++;
            $trimmedLine = trim($line);
            if ($trimmedLine === '' || str_starts_with($trimmedLine, ['--','#','/*'])) {
                continue;
            }

            $sql .= $line;
            if (substr(rtrim($line), -1) === ';') {
                try {
                    DB::statement(rtrim($sql, ';'));
                } catch (Exception $e) {
                    fclose($handle);
                    Log::error("SQL error at line {$lineNumber}: ".$e->getMessage());
                    throw new Exception("SQL failed at line {$lineNumber}: ".$e->getMessage());
                }
                $sql = '';
            }
        }

        fclose($handle);
        Log::info('SQL imported successfully');
    }

    public function ensureEnv()
    {
        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');

        if (!File::exists($envPath)) {
            if (File::exists($envExamplePath)) {
                File::copy($envExamplePath, $envPath);
                Log::info('.env created from .env.example');
            } else {
                Log::error('.env.example not found');
                throw new Exception('.env.example not found, please create one manually.');
            }
        } else {
            Log::info('.env already exists');
        }

        @chmod($envPath, 0664);
    }
}
