<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProductionCatalogSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Instalador Web Oficial MiPromo v2.1 — Grupo JMSPERU Solutions
 * Optimizado para hosting compartido cPanel / BanaHosting (PHP 8.2+ y MySQL/MariaDB).
 * Incluye chequeo y preparación automática de directorios, escapes seguros en .env ($ y comillas),
 * recarga limpia de variables de entorno, token apiperu.dev cifrado y selector de modo de datos.
 */
@set_time_limit(300);
@ini_set('memory_limit', '512M');

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$baseDir = dirname(__DIR__);
$lockFile = $baseDir.'/storage/installed.lock';
$envFile = $baseDir.'/.env';

$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$appBaseHref = str_ends_with($scriptDir, '/public') ? substr($scriptDir, 0, -7) : $scriptDir;
$storeUrl = ($appBaseHref === '' ? '' : $appBaseHref).'/';
$loginUrl = ($appBaseHref === '' ? '' : $appBaseHref).'/login';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function envEscape(string $value): string
{
    if ($value === '') {
        return '';
    }
    if (preg_match('/[\s#"$\\\\]/', $value)) {
        return '"'.str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value).'"';
    }

    return $value;
}

function clearPreloadedEnv(array $keys): void
{
    foreach ($keys as $key) {
        unset($_ENV[$key], $_SERVER[$key]);
        if (function_exists('putenv')) {
            @putenv($key);
        }
    }
}

// Auto-eliminación segura tras completar instalación
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'self_destruct') {
    $token = (string) ($_POST['cleanup_token'] ?? '');
    if (
        isset($_SESSION['install_cleanup_token']) &&
        hash_equals((string) $_SESSION['install_cleanup_token'], $token)
    ) {
        @unlink(__FILE__);
        header('Location: '.$storeUrl);
        exit;
    }
}

// Bloqueo de seguridad si ya fue instalado
if (file_exists($lockFile) && empty($_SESSION['install_just_completed'])) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <title>Instalador Bloqueado — MiPromo</title>
        <style>
            body{font-family:system-ui,-apple-system,sans-serif;background:#f1f5f9;color:#0f2d4a;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px}
            .card{max-width:540px;background:#fff;border:1px solid #cbd5e1;border-radius:20px;padding:32px;box-shadow:0 10px 25px rgba(15,45,74,.08)}
            .badge{display:inline-block;background:#fee2e2;color:#991b1b;font-weight:700;font-size:12px;padding:4px 10px;border-radius:999px}
        </style>
    </head>
    <body>
        <div class="card">
            <span class="badge">Sistema Ya Instalado</span>
            <h1>El instalador está bloqueado por seguridad</h1>
            <p>MiPromo ya se encuentra instalado en este servidor (<code>storage/installed.lock</code>).</p>
            <p>Por seguridad en producción, elimina el archivo <strong>public/install.php</strong> desde el Administrador de Archivos de cPanel (BanaHosting).</p>
            <p><a href="<?= h($storeUrl) ?>" style="color:#1d70b8;font-weight:700">Ir a la Tienda MiPromo →</a></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Preparar subcarpetas requeridas de Laravel si un ZIP las omitió
foreach ([
    $baseDir.'/storage/app',
    $baseDir.'/storage/app/public',
    $baseDir.'/storage/app/backups',
    $baseDir.'/storage/framework/cache/data',
    $baseDir.'/storage/framework/sessions',
    $baseDir.'/storage/framework/views',
    $baseDir.'/storage/logs',
    $baseDir.'/bootstrap/cache',
] as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

$scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$detectedUrl = rtrim($scheme.'://'.$host.($appBaseHref === '' ? '' : $appBaseHref), '/');

$requiredPhpVersion = '8.3.0';
$platformCheckPath = $baseDir.'/vendor/composer/platform_check.php';
if (is_file($platformCheckPath)) {
    $platformCheckBody = (string) @file_get_contents($platformCheckPath);
    if (preg_match('/PHP_VERSION_ID\s*>=\s*(\d{5})/', $platformCheckBody, $m)) {
        $id = (int) $m[1];
        $requiredPhpVersion = sprintf('%d.%d.%d', intdiv($id, 10000), intdiv($id % 10000, 100), $id % 100);
    }
}

$checks = [
    'php_version' => [
        'label' => "Versión de PHP >= {$requiredPhpVersion} (actual: ".PHP_VERSION.')',
        'ok' => version_compare(PHP_VERSION, $requiredPhpVersion, '>='),
    ],
    'vendor' => [
        'label' => 'Dependencias PHP (vendor/autoload.php)',
        'ok' => file_exists($baseDir.'/vendor/autoload.php'),
    ],
    'assets' => [
        'label' => 'Assets compilados (public/build/manifest.json)',
        'ok' => file_exists($baseDir.'/public/build/manifest.json'),
    ],
    'storage_writable' => [
        'label' => 'Permisos de escritura en storage/, framework/, logs/ y bootstrap/cache/',
        'ok' => is_writable($baseDir.'/storage')
            && is_writable($baseDir.'/storage/app')
            && is_writable($baseDir.'/storage/framework/views')
            && is_writable($baseDir.'/storage/framework/sessions')
            && is_writable($baseDir.'/storage/logs')
            && is_writable($baseDir.'/bootstrap/cache'),
    ],
    'root_writable' => [
        'label' => 'Permisos para escribir archivo .env en raíz',
        'ok' => is_writable($baseDir) || (file_exists($envFile) && is_writable($envFile)),
    ],
];

foreach (['pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'dom', 'ctype', 'json', 'bcmath', 'fileinfo', 'gd', 'curl', 'zip'] as $ext) {
    $checks['ext_'.$ext] = [
        'label' => "Extensión PHP: {$ext}",
        'ok' => extension_loaded($ext),
    ];
}

$allChecksPassed = ! in_array(false, array_column($checks, 'ok'), true);

$errors = [];
$dbTestMessage = null;
$warnings = [];
$installedSuccess = false;
$cleanupToken = $_SESSION['install_cleanup_token'] ?? bin2hex(random_bytes(16));
$_SESSION['install_cleanup_token'] = $cleanupToken;

$form = [
    'app_name' => (string) ($_POST['app_name'] ?? 'MiPromo Egresados Store'),
    'app_url' => (string) ($_POST['app_url'] ?? $detectedUrl),
    'db_host' => (string) ($_POST['db_host'] ?? 'localhost'),
    'db_port' => (string) ($_POST['db_port'] ?? '3306'),
    'db_database' => (string) ($_POST['db_database'] ?? ''),
    'db_username' => (string) ($_POST['db_username'] ?? ''),
    'db_password' => (string) ($_POST['db_password'] ?? ''),
    'mail_host' => (string) ($_POST['mail_host'] ?? 'localhost'),
    'mail_port' => (string) ($_POST['mail_port'] ?? '465'),
    'mail_username' => (string) ($_POST['mail_username'] ?? ''),
    'mail_password' => (string) ($_POST['mail_password'] ?? ''),
    'mail_from_address' => (string) ($_POST['mail_from_address'] ?? ''),
    'admin_name' => (string) ($_POST['admin_name'] ?? 'Julio Moreno (Grupo JMSPERU)'),
    'admin_email' => (string) ($_POST['admin_email'] ?? 'admin@jmsperu.pe'),
    'admin_phone' => (string) ($_POST['admin_phone'] ?? '959199368'),
    'apiperu_token' => (string) ($_POST['apiperu_token'] ?? ''),
    'apiperu_endpoint' => (string) ($_POST['apiperu_endpoint'] ?? 'https://api.apiperu.dev/dni'),
    'seed_mode' => (string) ($_POST['seed_mode'] ?? 'production'),
];

if (! in_array($form['seed_mode'], ['production', 'demo', 'blank'], true)) {
    $form['seed_mode'] = 'production';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = (string) ($_POST['action'] ?? 'install');

    if ($action === 'test_db') {
        try {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $form['db_host'], $form['db_port'], $form['db_database']);
            $pdo = new PDO($dsn, $form['db_username'], $form['db_password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
            ]);
            $serverVer = (string) $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
            $dbTestMessage = "Conexión MySQL exitosa ({$serverVer}) con la base de datos {$form['db_database']}.";
        } catch (Throwable $e) {
            $errors[] = 'No se pudo conectar a MySQL: '.$e->getMessage();
        }
    }

    if ($action === 'install') {
        if (! $allChecksPassed) {
            $errors[] = 'Corrige las verificaciones de requisitos antes de instalar.';
        }

        if ($form['db_database'] === '' || $form['db_username'] === '') {
            $errors[] = 'Debes ingresar el nombre de la base de datos y usuario MySQL creado en cPanel.';
        }

        $adminPass = (string) ($_POST['admin_password'] ?? '');
        $adminPassConf = (string) ($_POST['admin_password_confirmation'] ?? '');

        if (strlen($adminPass) < 8 || ! preg_match('/[A-Za-z]/', $adminPass) || ! preg_match('/[0-9]/', $adminPass)) {
            $errors[] = 'La contraseña del Superadmin debe tener al menos 8 caracteres combinando letras y números.';
        } elseif ($adminPass !== $adminPassConf) {
            $errors[] = 'Las contraseñas del Superadmin no coinciden.';
        }

        if (! filter_var($form['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo del Superadmin no es válido.';
        }

        if (! filter_var($form['apiperu_endpoint'], FILTER_VALIDATE_URL)) {
            $errors[] = 'El endpoint de apiperu.dev debe ser una URL válida (ej. https://api.apiperu.dev/dni).';
        }

        if ($errors === []) {
            try {
                $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $form['db_host'], $form['db_port'], $form['db_database']);
                new PDO($dsn, $form['db_username'], $form['db_password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
                ]);
            } catch (Throwable $e) {
                $errors[] = 'Error de conexión MySQL: '.$e->getMessage();
            }
        }

        if ($errors === []) {
            $appKey = 'base64:'.base64_encode(random_bytes(32));
            $normalizedUrl = rtrim(trim($form['app_url']), '/');
            $isHttps = str_starts_with(strtolower($normalizedUrl), 'https://');
            $mailer = trim($form['mail_username']) !== '' ? 'smtp' : 'log';
            $fromEmail = trim($form['mail_from_address']) !== '' ? $form['mail_from_address'] : $form['admin_email'];
            $cleanEndpoint = trim($form['apiperu_endpoint']) ?: 'https://api.apiperu.dev/dni';

            $envContent = implode("\n", [
                'APP_NAME='.envEscape($form['app_name']),
                'APP_ENV=production',
                'APP_KEY='.$appKey,
                'APP_DEBUG=false',
                'APP_URL='.envEscape($normalizedUrl),
                '',
                'APP_LOCALE=es',
                'APP_FALLBACK_LOCALE=es',
                'APP_FAKER_LOCALE=es_PE',
                'APP_TIMEZONE=America/Lima',
                '',
                'BCRYPT_ROUNDS=12',
                'LOG_CHANNEL=stack',
                'LOG_STACK=single',
                'LOG_LEVEL=warning',
                '',
                'DB_CONNECTION=mysql',
                'DB_HOST='.envEscape($form['db_host']),
                'DB_PORT='.envEscape($form['db_port']),
                'DB_DATABASE='.envEscape($form['db_database']),
                'DB_USERNAME='.envEscape($form['db_username']),
                'DB_PASSWORD='.envEscape($form['db_password']),
                '',
                'SESSION_DRIVER=database',
                'SESSION_LIFETIME=120',
                'SESSION_ENCRYPT=true',
                'SESSION_SAME_SITE=lax',
                'SESSION_SECURE_COOKIE='.($isHttps ? 'true' : 'false'),
                'QUEUE_CONNECTION=database',
                'CACHE_STORE=database',
                'FILESYSTEM_DISK=local',
                '',
                'MAIL_MAILER='.$mailer,
                'MAIL_HOST='.envEscape($form['mail_host']),
                'MAIL_PORT='.envEscape($form['mail_port']),
                'MAIL_USERNAME='.envEscape($form['mail_username']),
                'MAIL_PASSWORD='.envEscape($form['mail_password']),
                'MAIL_FROM_ADDRESS='.envEscape($fromEmail),
                'MAIL_FROM_NAME="${APP_NAME}"',
                '',
                'APIPERU_ENDPOINT='.envEscape($cleanEndpoint),
                'APIPERU_TOKEN=',
                '',
                'VITE_APP_NAME="${APP_NAME}"',
                '',
            ]);

            if (file_put_contents($envFile, $envContent) === false) {
                $errors[] = 'No se pudo escribir el archivo .env.';
            }
        }

        if ($errors === []) {
            try {
                // Limpiar caché previa, archivo public/hot y symlinks rotos de desarrollo
                if (file_exists($baseDir.'/public/hot')) {
                    @unlink($baseDir.'/public/hot');
                }
                foreach (glob($baseDir.'/bootstrap/cache/*.php') ?: [] as $cacheFile) {
                    if (! str_ends_with($cacheFile, '.gitignore')) {
                        @unlink($cacheFile);
                    }
                }

                $publicStorage = $baseDir.'/public/storage';
                if (is_link($publicStorage)) {
                    @unlink($publicStorage);
                }

                clearPreloadedEnv([
                    'APP_NAME', 'APP_ENV', 'APP_KEY', 'APP_DEBUG', 'APP_URL',
                    'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD',
                    'CACHE_STORE', 'SESSION_DRIVER', 'SESSION_ENCRYPT', 'SESSION_SECURE_COOKIE',
                    'APIPERU_ENDPOINT', 'APIPERU_TOKEN',
                ]);

                $obLevel = ob_get_level();
                ob_start();
                require $baseDir.'/vendor/autoload.php';
                while (ob_get_level() > $obLevel) {
                    @ob_end_clean();
                }

                $app = require $baseDir.'/bootstrap/app.php';
                $kernel = $app->make(Kernel::class);
                $kernel->bootstrap();

                Artisan::call('migrate:fresh', ['--force' => true]);
                Cache::flush();

                $adminEmail = strtolower(trim($form['admin_email']));
                $adminPhone = preg_replace('/\D+/', '', trim($form['admin_phone'])) ?: '959199368';

                if ($form['seed_mode'] === 'demo') {
                    Artisan::call('db:seed', [
                        '--class' => DatabaseSeeder::class,
                        '--force' => true,
                    ]);

                    $superAdmin = User::updateOrCreate(
                        ['email' => $adminEmail],
                        [
                            'name' => trim($form['admin_name']),
                            'password' => Hash::make($adminPass),
                            'role' => UserRole::SuperAdmin->value,
                            'phone' => $adminPhone,
                            'city' => 'Lima',
                            'referral_code' => 'JMS'.strtoupper(Str::random(5)),
                            'is_active' => true,
                            'privacy_consent_at' => now(),
                        ]
                    );
                } else {
                    $superAdmin = User::create([
                        'name' => trim($form['admin_name']),
                        'email' => $adminEmail,
                        'password' => Hash::make($adminPass),
                        'role' => UserRole::SuperAdmin->value,
                        'phone' => $adminPhone,
                        'city' => 'Lima',
                        'referral_code' => 'JMS'.strtoupper(Str::random(5)),
                        'is_active' => true,
                        'privacy_consent_at' => now(),
                    ]);

                    if ($form['seed_mode'] === 'production') {
                        Artisan::call('db:seed', [
                            '--class' => ProductionCatalogSeeder::class,
                            '--force' => true,
                        ]);
                    }
                }

                // Configurar parámetros iniciales (caché de arrays planos, nunca objetos) y cifrar token apiperu.dev
                SystemSetting::setValue('apiperu_enabled', '1', 'apiperu', false, 'Activa consulta DNI apiperu.dev');
                SystemSetting::setValue('apiperu_endpoint', $cleanEndpoint, 'apiperu', false, 'Endpoint oficial apiperu.dev');
                SystemSetting::setValue('whatsapp_number', $adminPhone, 'general', false, 'WhatsApp oficial');
                SystemSetting::setValue('support_email', $adminEmail, 'general', false, 'Correo oficial de soporte');

                if (trim($form['apiperu_token']) !== '') {
                    SystemSetting::setValue('apiperu_token', trim($form['apiperu_token']), 'apiperu', true, 'Token Bearer cifrado de apiperu.dev');
                }

                try {
                    if (! file_exists($publicStorage)) {
                        Artisan::call('storage:link');
                    }
                } catch (Throwable) {
                    if (! is_dir($publicStorage) && ! @symlink($baseDir.'/storage/app/public', $publicStorage)) {
                        $warnings[] = 'Tu plan de hosting bloquea symlink(): crea un enlace simbólico o carpeta compartida de storage/app/public a public/storage si subes comprobantes o fotos de álbum.';
                    }
                }

                Cache::flush();

                try {
                    Artisan::call('config:cache');
                    Artisan::call('route:cache');
                    Artisan::call('view:cache');
                } catch (Throwable $cacheEx) {
                    $warnings[] = 'No se pudo precompilar la caché de configuración/rutas (la app funcionará normalmente sin cachear): '.$cacheEx->getMessage();
                }

                file_put_contents($lockFile, json_encode([
                    'installed_at' => date('c'),
                    'app_url' => $normalizedUrl,
                    'mode' => $form['seed_mode'],
                    'super_admin' => $superAdmin->email,
                ], JSON_PRETTY_PRINT));

                $_SESSION['install_just_completed'] = true;
                $installedSuccess = true;
            } catch (Throwable $e) {
                if (isset($obLevel)) {
                    while (ob_get_level() > $obLevel) {
                        @ob_end_clean();
                    }
                }
                $errors[] = 'Error ejecutando migración / instalación Laravel: '.$e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instalador MiPromo v2.2 — Hosting BanaHosting cPanel</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:#f1f5f9;color:#0f2d4a;line-height:1.5}
        .wrap{max-width:980px;margin:32px auto;padding:0 16px}
        .header{background:linear-gradient(135deg,#0f2d4a,#1d70b8);color:#fff;border-radius:24px;padding:28px 32px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px}
        .card{background:#fff;border:1px solid #cbd5e1;border-radius:20px;padding:24px;margin-top:20px;box-shadow:0 4px 16px rgba(15,45,74,.05)}
        h1{margin:0;font-size:24px}
        h2{margin:0 0 12px;font-size:17px;color:#0f2d4a}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px}
        label{display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px}
        input[type=text],input[type=email],input[type=password],input[type=url]{width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;font-size:14px}
        .check{display:flex;justify-content:space-between;align-items:center;padding:8px 12px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;font-size:13px}
        .ok{color:#047857;font-weight:700}
        .fail{color:#b91c1c;font-weight:700}
        .alert{padding:14px 16px;border-radius:12px;margin-top:16px;font-size:13px}
        .alert-err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
        .alert-ok{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
        .alert-warn{background:#fffbeb;border:1px solid #fde68a;color:#92400e}
        .btn{border:0;border-radius:12px;padding:12px 20px;font-size:13px;font-weight:800;cursor:pointer}
        .btn-primary{background:#1d70b8;color:#fff}
        .btn-dark{background:#0f2d4a;color:#fff}
        .btn-warn{background:#dc2626;color:#fff}
        .mode-card{display:flex;gap:10px;align-items:flex-start;padding:12px;border:1px solid #cbd5e1;border-radius:12px;background:#f8fafc;cursor:pointer;font-size:13px}
        .footer{text-align:center;font-size:12px;color:#64748b;margin:28px 0}
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <div>
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#fde047">Despliegue cPanel BanaHosting · v2.2</div>
            <h1>Instalador Web MiPromo Egresados Store</h1>
            <p style="margin:6px 0 0;font-size:13px;opacity:.9">Configura MySQL, SMTP, Endpoint &amp; Token API Perú (DNI), modo de datos y Superadmin real en minutos.</p>
        </div>
        <div style="background:#fff;padding:8px 14px;border-radius:14px">
            <strong style="color:#0f2d4a;font-size:14px">Grupo JMSPERU</strong>
        </div>
    </div>

    <?php if ($errors !== []) { ?>
        <div class="alert alert-err">
            <strong>Revisa los siguientes puntos:</strong>
            <ul style="margin:6px 0 0 18px;padding:0">
                <?php foreach ($errors as $err) { ?>
                    <li><?= h($err) ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <?php if ($warnings !== []) { ?>
        <div class="alert alert-warn">
            <strong>Avisos de hosting:</strong>
            <ul style="margin:6px 0 0 18px;padding:0">
                <?php foreach ($warnings as $warn) { ?>
                    <li><?= h($warn) ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <?php if ($dbTestMessage !== null) { ?>
        <div class="alert alert-ok">✓ <?= h($dbTestMessage) ?></div>
    <?php } ?>

    <?php if ($installedSuccess) { ?>
        <div class="card">
            <h2 style="color:#047857;font-size:22px">✓ ¡MiPromo quedó instalado correctamente!</h2>
            <p>Tu base de datos MySQL fue migrada en modo <strong><?= h($form['seed_mode']) ?></strong>, las cachés quedaron listas y tu cuenta <strong>Superadministrador (<?= h($form['admin_email']) ?>)</strong> ya puede ingresar.</p>
            <p><strong>Paso de seguridad obligatorio:</strong> elimina <code>public/install.php</code> ahora mismo haciendo clic en el botón rojo o desde cPanel.</p>
            <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:16px">
                <form method="POST">
                    <input type="hidden" name="action" value="self_destruct">
                    <input type="hidden" name="cleanup_token" value="<?= h($cleanupToken) ?>">
                    <button type="submit" class="btn btn-warn">Eliminar install.php e ir a la Tienda →</button>
                </form>
                <a href="<?= h($loginUrl) ?>" class="btn btn-dark" style="text-decoration:none">Iniciar Sesión como Superadmin →</a>
            </div>
        </div>
    <?php } else { ?>
        <div class="card">
            <h2>1. Verificación del Servidor BanaHosting (PHP, PDF, ZIP, API DNI)</h2>
            <div class="grid">
                <?php foreach ($checks as $item) { ?>
                    <div class="check">
                        <span><?= h($item['label']) ?></span>
                        <span class="<?= $item['ok'] ? 'ok' : 'fail' ?>"><?= $item['ok'] ? 'OK' : 'Falta' ?></span>
                    </div>
                <?php } ?>
            </div>
        </div>

        <form method="POST" class="card" style="display:flex;flex-direction:column;gap:22px" onsubmit="const btn=this.querySelector('[data-install-btn]'); if(btn && event.submitter===btn){ btn.disabled=true; btn.textContent='Instalando y migrando base de datos... por favor espera'; }">
            <div>
                <h2>2. Dominio y Base de Datos MySQL (cPanel)</h2>
                <div class="grid">
                    <div>
                        <label>Nombre de la Tienda</label>
                        <input type="text" name="app_name" required value="<?= h($form['app_name']) ?>">
                    </div>
                    <div>
                        <label>URL de Producción (con https://)</label>
                        <input type="url" name="app_url" required value="<?= h($form['app_url']) ?>">
                    </div>
                    <div>
                        <label>Host MySQL (en BanaHosting suele ser localhost)</label>
                        <input type="text" name="db_host" required value="<?= h($form['db_host']) ?>">
                    </div>
                    <div>
                        <label>Puerto MySQL</label>
                        <input type="text" name="db_port" required value="<?= h($form['db_port']) ?>">
                    </div>
                    <div>
                        <label>Nombre Base de Datos (ej. usuario_mipromo)</label>
                        <input type="text" name="db_database" required value="<?= h($form['db_database']) ?>">
                    </div>
                    <div>
                        <label>Usuario MySQL (ej. usuario_admin)</label>
                        <input type="text" name="db_username" required value="<?= h($form['db_username']) ?>">
                    </div>
                    <div>
                        <label>Contraseña MySQL</label>
                        <input type="password" name="db_password" value="<?= h($form['db_password']) ?>">
                    </div>
                </div>
                <div style="margin-top:12px">
                    <button type="submit" name="action" value="test_db" class="btn btn-dark" formnovalidate>Probar Conexión MySQL</button>
                </div>
            </div>

            <div>
                <h2>3. Correo SMTP y Token API Perú (apiperu.dev)</h2>
                <div class="grid">
                    <div>
                        <label>Servidor SMTP (cPanel Email)</label>
                        <input type="text" name="mail_host" value="<?= h($form['mail_host']) ?>">
                    </div>
                    <div>
                        <label>Puerto SMTP (465 SSL / 587 TLS)</label>
                        <input type="text" name="mail_port" value="<?= h($form['mail_port']) ?>">
                    </div>
                    <div>
                        <label>Usuario SMTP</label>
                        <input type="text" name="mail_username" value="<?= h($form['mail_username']) ?>" placeholder="noreply@tudominio.com">
                    </div>
                    <div>
                        <label>Contraseña SMTP</label>
                        <input type="password" name="mail_password">
                    </div>
                    <div>
                        <label>Correo remitente</label>
                        <input type="email" name="mail_from_address" value="<?= h($form['mail_from_address']) ?>" placeholder="noreply@tudominio.com">
                    </div>
                    <div>
                        <label>URL Endpoint DNI apiperu.dev</label>
                        <input type="url" name="apiperu_endpoint" value="<?= h($form['apiperu_endpoint']) ?>">
                    </div>
                    <div>
                        <label>Token Bearer apiperu.dev (opcional — configurable luego)</label>
                        <input type="password" name="apiperu_token" value="<?= h($form['apiperu_token']) ?>" placeholder="Se cifrará automáticamente en BD">
                    </div>
                </div>
            </div>

            <div>
                <h2>4. Cuenta Superadministrador Real</h2>
                <div class="grid">
                    <div>
                        <label>Nombre Completo *</label>
                        <input type="text" name="admin_name" required value="<?= h($form['admin_name']) ?>">
                    </div>
                    <div>
                        <label>Correo de Acceso *</label>
                        <input type="email" name="admin_email" required value="<?= h($form['admin_email']) ?>">
                    </div>
                    <div>
                        <label>WhatsApp Oficial</label>
                        <input type="text" name="admin_phone" value="<?= h($form['admin_phone']) ?>">
                    </div>
                    <div>
                        <label>Contraseña Segura (mín. 8 caracteres, letras y números) *</label>
                        <input type="password" name="admin_password" required>
                    </div>
                    <div>
                        <label>Confirmar Contraseña *</label>
                        <input type="password" name="admin_password_confirmation" required>
                    </div>
                </div>
            </div>

            <div>
                <h2>5. Modo de Inicialización de Datos</h2>
                <div class="grid">
                    <label class="mode-card">
                        <input type="radio" name="seed_mode" value="production" <?= $form['seed_mode'] === 'production' ? 'checked' : '' ?> style="margin-top:3px">
                        <span>
                            <strong>Producción Limpia (Recomendado)</strong><br>
                            <small>Carga catálogo ampliado (casacas, polos, anillos, cadenas, medallas, mochilas, libretas, copas), 15 colegios históricos y zonas de envío <u>sin usuarios de prueba</u>.</small>
                        </span>
                    </label>
                    <label class="mode-card">
                        <input type="radio" name="seed_mode" value="demo" <?= $form['seed_mode'] === 'demo' ? 'checked' : '' ?> style="margin-top:3px">
                        <span>
                            <strong>Demostración Completa (Pruebas QA)</strong><br>
                            <small>Carga todos los módulos completos: 8 roles demo, pedidos SUNAT, devoluciones, egresados, campañas, comunidades y liquidaciones (luego puedes blanquear en Configuración).</small>
                        </span>
                    </label>
                    <label class="mode-card">
                        <input type="radio" name="seed_mode" value="blank" <?= $form['seed_mode'] === 'blank' ? 'checked' : '' ?> style="margin-top:3px">
                        <span>
                            <strong>Sistema en Blanco</strong><br>
                            <small>Crea únicamente las tablas y tu cuenta Superadministrador sin datos iniciales.</small>
                        </span>
                    </label>
                </div>
            </div>

            <div>
                <button type="submit" name="action" value="install" data-install-btn class="btn btn-primary" style="width:100%;font-size:15px;padding:14px">
                    Instalar MiPromo en Producción Ahora →
                </button>
            </div>
        </form>
    <?php } ?>

    <p class="footer">Todos los derechos reservados son del Grupo JMSPERU :: Contacto 959199368</p>
</div>
</body>
</html>
