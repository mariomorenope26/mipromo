<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Institution;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProductionCatalogSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PDO;
use ZipArchive;

class SystemMaintenanceService
{
    /**
     * Genera una copia de seguridad completa en SQL (+ JSON manifest empaquetado en ZIP cuando ZipArchive está disponible)
     * compatible con MySQL (BanaHosting cPanel) y SQLite sin requerir mysqldump.
     *
     * @return array{path: string, filename: string, size: int, mime: string, tables_count: int}
     */
    public function createBackupArchive(): array
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $timestamp = now()->format('Ymd-His');
        $sqlFilename = "mipromo-backup-{$timestamp}.sql";
        $sqlData = $this->generateSqlDump();

        $manifest = json_encode([
            'app' => config('app.name'),
            'generated_at' => now()->toIso8601String(),
            'db_driver' => DB::getDriverName(),
            'summary' => [
                'users' => User::count(),
                'institutions' => Institution::count(),
                'products' => Product::count(),
                'orders' => Order::count(),
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (class_exists(ZipArchive::class)) {
            $zipFilename = "mipromo-backup-{$timestamp}.zip";
            $zipPath = $dir.'/'.$zipFilename;
            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $zip->addFromString($sqlFilename, $sqlData['sql']);
                $zip->addFromString('manifest.json', (string) $manifest);
                $zip->close();

                return [
                    'path' => $zipPath,
                    'filename' => $zipFilename,
                    'size' => (int) filesize($zipPath),
                    'mime' => 'application/zip',
                    'tables_count' => $sqlData['tables_count'],
                ];
            }
        }

        $sqlPath = $dir.'/'.$sqlFilename;
        file_put_contents($sqlPath, $sqlData['sql']);

        return [
            'path' => $sqlPath,
            'filename' => $sqlFilename,
            'size' => (int) filesize($sqlPath),
            'mime' => 'application/sql',
            'tables_count' => $sqlData['tables_count'],
        ];
    }

    /**
     * Lista las copias de seguridad almacenadas localmente en storage/app/backups.
     *
     * @return array<int, array{name: string, size_kb: float, created_at: string}>
     */
    public function listBackups(): array
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            return [];
        }

        $files = glob($dir.'/*.{zip,sql}', GLOB_BRACE) ?: [];
        usort($files, fn (string $a, string $b) => filemtime($b) <=> filemtime($a));

        return array_map(fn (string $path): array => [
            'name' => basename($path),
            'size_kb' => round(((int) filesize($path)) / 1024, 1),
            'created_at' => date('d/m/Y H:i', (int) filemtime($path)),
        ], array_slice($files, 0, 10));
    }

    /**
     * Blanquea el sistema eliminando datos transaccionales/demo (y opcionalmente catálogo)
     * preservando la cuenta Superadmin actual y la configuración (`system_settings`).
     *
     * @return array{tables_cleared: int, kept_catalog: bool}
     */
    public function wipeSystem(User $actingSuperAdmin, bool $keepBaseCatalog = false): array
    {
        $driver = DB::getDriverName();
        $this->setForeignKeys($driver, false);

        $transactionalTables = [
            'audit_logs',
            'user_notifications',
            'complaints',
            'content_reports',
            'community_album_photos',
            'community_albums',
            'community_collection_contributions',
            'community_collections',
            'community_poll_votes',
            'community_poll_options',
            'community_polls',
            'community_event_rsvps',
            'community_events',
            'community_comments',
            'community_posts',
            'community_members',
            'communities',
            'campaign_participants',
            'campaigns',
            'return_requests',
            'invoices',
            'payments',
            'order_items',
            'orders',
            'vendor_settlements',
            'wishlists',
            'product_reviews',
            'alumni_profiles',
            'classrooms',
            'promotions',
            'blog_posts',
            'message_templates',
            'promotional_banners',
        ];

        $catalogTables = [
            'product_variants',
            'products',
            'categories',
            'vendors',
            'coupons',
            'shipping_zones',
            'institutions',
        ];

        $tablesToClear = $keepBaseCatalog
            ? $transactionalTables
            : array_merge($transactionalTables, $catalogTables);

        $cleared = 0;
        foreach ($tablesToClear as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
                $cleared++;
            }
        }

        User::where('id', '!=', $actingSuperAdmin->id)->delete();

        if (! $keepBaseCatalog) {
            $actingSuperAdmin->update(['institution_id' => null]);
        }

        $this->setForeignKeys($driver, true);
        Cache::flush();

        if ($keepBaseCatalog && ! Product::exists()) {
            Artisan::call('db:seed', ['--class' => ProductionCatalogSeeder::class, '--force' => true]);
        }

        Auth::login($actingSuperAdmin->fresh());

        AuditLog::create([
            'user_id' => $actingSuperAdmin->id,
            'action' => 'system.wiped',
            'description' => $keepBaseCatalog
                ? 'Sistema blanqueado conservando catálogo base y colegios.'
                : 'Sistema blanqueado por completo manteniendo únicamente el Superadministrador y la configuración.',
            'ip_address' => request()?->ip(),
        ]);

        return [
            'tables_cleared' => $cleared,
            'kept_catalog' => $keepBaseCatalog,
        ];
    }

    /**
     * Reinicia el conjunto de datos de demostración completos preservando la configuración del sistema
     * y manteniendo activa la sesión del Superadministrador actual.
     *
     * @return array{users: int, institutions: int, products: int, orders: int}
     */
    public function initializeDemoData(User $actingSuperAdmin): array
    {
        $savedEmail = $actingSuperAdmin->email;
        $savedName = $actingSuperAdmin->name;
        $savedPasswordHash = $actingSuperAdmin->password;
        $savedPhone = $actingSuperAdmin->phone;

        Auth::logout();

        $driver = DB::getDriverName();
        $this->setForeignKeys($driver, false);

        foreach ($this->allApplicationTablesExceptSettings() as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        $this->setForeignKeys($driver, true);
        Cache::flush();

        Artisan::call('db:seed', [
            '--class' => DatabaseSeeder::class,
            '--force' => true,
        ]);

        $restoredUser = User::where('email', $savedEmail)->first();
        if (! $restoredUser) {
            $restoredUser = User::create([
                'name' => $savedName,
                'email' => $savedEmail,
                'password' => $savedPasswordHash,
                'role' => UserRole::SuperAdmin->value,
                'phone' => $savedPhone,
                'city' => 'Lima',
                'referral_code' => 'JMS'.strtoupper(Str::random(5)),
                'is_active' => true,
                'privacy_consent_at' => now(),
            ]);
        }

        Auth::login($restoredUser);

        AuditLog::create([
            'user_id' => $restoredUser->id,
            'action' => 'system.demo_initialized',
            'description' => 'Datos de demostración consistentes reiniciados desde Configuración.',
            'ip_address' => request()?->ip(),
        ]);

        return [
            'users' => User::count(),
            'institutions' => Institution::count(),
            'products' => Product::count(),
            'orders' => Order::count(),
        ];
    }

    /**
     * @return array{sql: string, tables_count: int}
     */
    private function generateSqlDump(): array
    {
        $pdo = DB::connection()->getPdo();
        $driver = DB::getDriverName();
        $excluded = ['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'];

        $lines = [
            '-- Copia de Seguridad MiPromo — Grupo JMSPERU',
            '-- Generado: '.now()->toDateTimeString(),
            '-- Motor DB: '.$driver,
            '',
        ];

        if ($driver === 'mysql') {
            $lines[] = 'SET FOREIGN_KEY_CHECKS=0;';
            $lines[] = 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";';
        } elseif ($driver === 'sqlite') {
            $lines[] = 'PRAGMA foreign_keys=OFF;';
        }

        $tables = $this->listTables($pdo, $driver);
        $exportedCount = 0;

        foreach ($tables as $table) {
            if (in_array($table, $excluded, true)) {
                continue;
            }

            $exportedCount++;
            $lines[] = '';
            $lines[] = "-- Tabla: {$table}";

            if ($driver === 'mysql') {
                $lines[] = "DROP TABLE IF EXISTS `{$table}`;";
                $createRow = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM);
                if (! empty($createRow[1])) {
                    $lines[] = $createRow[1].';';
                }
            } elseif ($driver === 'sqlite') {
                $lines[] = "DROP TABLE IF EXISTS \"{$table}\";";
                $stmt = $pdo->prepare("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ?");
                $stmt->execute([$table]);
                $createSql = $stmt->fetchColumn();
                if ($createSql) {
                    $lines[] = $createSql.';';
                }
            }

            $rows = DB::table($table)->get();
            foreach ($rows as $row) {
                $data = (array) $row;
                $cols = array_map(
                    fn (string $col): string => $driver === 'mysql' ? "`{$col}`" : "\"{$col}\"",
                    array_keys($data)
                );
                $vals = array_map(function ($val) use ($pdo): string {
                    if ($val === null) {
                        return 'NULL';
                    }
                    if (is_bool($val)) {
                        return $val ? '1' : '0';
                    }

                    return $pdo->quote((string) $val);
                }, array_values($data));

                $tblQuoted = $driver === 'mysql' ? "`{$table}`" : "\"{$table}\"";
                $lines[] = "INSERT INTO {$tblQuoted} (".implode(', ', $cols).') VALUES ('.implode(', ', $vals).');';
            }
        }

        $lines[] = '';
        if ($driver === 'mysql') {
            $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';
        } elseif ($driver === 'sqlite') {
            $lines[] = 'PRAGMA foreign_keys=ON;';
        }

        return [
            'sql' => implode("\n", $lines)."\n",
            'tables_count' => $exportedCount,
        ];
    }

    /**
     * @return list<string>
     */
    private function listTables(PDO $pdo, string $driver): array
    {
        if ($driver === 'mysql') {
            return $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) ?: [];
        }

        if ($driver === 'sqlite') {
            $rows = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

            return $rows ?: [];
        }

        return [];
    }

    private function setForeignKeys(string $driver, bool $enabled): void
    {
        if ($driver === 'mysql') {
            DB::statement($enabled ? 'SET FOREIGN_KEY_CHECKS=1' : 'SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'sqlite') {
            DB::statement($enabled ? 'PRAGMA foreign_keys = ON' : 'PRAGMA foreign_keys = OFF');
        }
    }

    /**
     * @return list<string>
     */
    private function allApplicationTablesExceptSettings(): array
    {
        return [
            'audit_logs',
            'user_notifications',
            'complaints',
            'content_reports',
            'community_album_photos',
            'community_albums',
            'community_collection_contributions',
            'community_collections',
            'community_poll_votes',
            'community_poll_options',
            'community_polls',
            'community_event_rsvps',
            'community_events',
            'community_comments',
            'community_posts',
            'community_members',
            'communities',
            'campaign_participants',
            'campaigns',
            'return_requests',
            'invoices',
            'payments',
            'order_items',
            'orders',
            'vendor_settlements',
            'wishlists',
            'product_reviews',
            'product_variants',
            'products',
            'categories',
            'vendors',
            'coupons',
            'shipping_zones',
            'alumni_profiles',
            'classrooms',
            'promotions',
            'blog_posts',
            'message_templates',
            'promotional_banners',
            'institutions',
            'users',
        ];
    }
}
