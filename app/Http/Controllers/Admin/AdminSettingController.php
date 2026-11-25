<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Order;
use App\Models\Product;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\ApiPeruService;
use App\Services\AuditLogger;
use App\Services\SystemMaintenanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminSettingController extends Controller
{
    public function index(ApiPeruService $apiPeru, SystemMaintenanceService $maintenance): View
    {
        $token = $apiPeru->token();
        $hasToken = $token !== '';
        $maskedToken = SystemSetting::maskedToken($token);
        $tokenSource = SystemSetting::hasNonEmptyValue('apiperu_token')
            ? 'base de datos (cifrado)'
            : ($hasToken ? 'archivo .env' : 'no configurado');

        $settings = [
            'apiperu_enabled' => $apiPeru->isEnabled(),
            'apiperu_endpoint' => $apiPeru->endpoint(),
            'whatsapp_number' => SystemSetting::getValue('whatsapp_number', '959199368'),
            'support_email' => SystemSetting::getValue('support_email', 'contacto@jmsperu.com'),
            'manual_updated_at' => SystemSetting::getValue('manual_updated_at', now()->format('d/m/Y')),
        ];

        $systemCounts = [
            'users' => User::count(),
            'institutions' => Institution::count(),
            'products' => Product::count(),
            'orders' => Order::count(),
        ];

        $backups = $maintenance->listBackups();
        $testResult = session('dni_test_result');

        return view('admin.settings.index', compact(
            'settings',
            'hasToken',
            'maskedToken',
            'tokenSource',
            'testResult',
            'systemCounts',
            'backups'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'apiperu_endpoint' => ['required', 'url', 'max:255'],
            'apiperu_token' => ['nullable', 'string', 'max:500'],
            'whatsapp_number' => ['required', 'string', 'max:25'],
            'support_email' => ['nullable', 'email', 'max:150'],
        ]);

        $enabled = $request->boolean('apiperu_enabled');

        SystemSetting::setValue(
            'apiperu_enabled',
            $enabled ? '1' : '0',
            'apiperu',
            false,
            'Habilita la búsqueda y autocompletado por DNI vía apiperu.dev'
        );

        SystemSetting::setValue(
            'apiperu_endpoint',
            trim($validated['apiperu_endpoint']),
            'apiperu',
            false,
            'URL oficial del endpoint de consulta DNI en apiperu.dev'
        );

        if ($request->boolean('clear_token')) {
            SystemSetting::setValue('apiperu_token', '', 'apiperu', false, 'Token Bearer de apiperu.dev');
        } elseif (filled($validated['apiperu_token'] ?? null)) {
            SystemSetting::setValue(
                'apiperu_token',
                trim((string) $validated['apiperu_token']),
                'apiperu',
                true,
                'Token Bearer cifrado para consultas RENIEC en apiperu.dev'
            );
        }

        SystemSetting::setValue(
            'whatsapp_number',
            preg_replace('/\D+/', '', $validated['whatsapp_number']) ?: '959199368',
            'general',
            false,
            'Número oficial de atención WhatsApp'
        );

        if (filled($validated['support_email'] ?? null)) {
            SystemSetting::setValue(
                'support_email',
                trim((string) $validated['support_email']),
                'general',
                false,
                'Correo oficial de soporte'
            );
        }

        AuditLogger::log('settings.updated', 'Configuración del sistema / token apiperu.dev actualizada');

        return back()->with('success', 'Configuración del sistema e integración apiperu.dev guardadas de forma segura.');
    }

    public function testDni(Request $request, ApiPeruService $apiPeru): RedirectResponse
    {
        $validated = $request->validate([
            'test_dni' => ['required', 'digits:8'],
            'override_token' => ['nullable', 'string', 'max:500'],
        ]);

        $override = filled($validated['override_token'] ?? null) ? trim((string) $validated['override_token']) : null;
        $result = $apiPeru->lookupDni($validated['test_dni'], $override, $apiPeru->endpoint(), false);

        return back()->with('dni_test_result', [
            'dni' => $validated['test_dni'],
            ...$result,
        ]);
    }

    public function backup(SystemMaintenanceService $maintenance): BinaryFileResponse
    {
        $archive = $maintenance->createBackupArchive();
        AuditLogger::log('system.backup_created', "Copia de seguridad generada: {$archive['filename']} ({$archive['tables_count']} tablas)");

        return response()->download($archive['path'], $archive['filename'], [
            'Content-Type' => $archive['mime'],
        ]);
    }

    public function downloadSavedBackup(string $filename): BinaryFileResponse
    {
        $safe = basename($filename);
        abort_unless((bool) preg_match('/^mipromo-backup-[0-9\-]+\.(zip|sql)$/', $safe), 404);
        $path = storage_path('app/backups/'.$safe);
        abort_unless(is_file($path), 404);

        return response()->download($path, $safe);
    }

    public function wipe(Request $request, SystemMaintenanceService $maintenance): RedirectResponse
    {
        $request->validate([
            'confirm_phrase' => ['required', 'string', 'in:BLANQUEAR'],
            'current_password' => ['required', 'current_password'],
            'keep_catalog' => ['nullable', 'boolean'],
        ], [
            'confirm_phrase.in' => 'Debes escribir exactamente BLANQUEAR en mayúsculas para confirmar.',
        ]);

        $keepCatalog = $request->boolean('keep_catalog');
        $result = $maintenance->wipeSystem($request->user(), $keepCatalog);

        $scopeLabel = $result['kept_catalog']
            ? 'conservando el catálogo base, colegios y zonas de envío'
            : 'dejando únicamente tu usuario Superadministrador y la configuración del sistema';

        return redirect()->route('admin.settings.index')->with('success', "Sistema blanqueado correctamente ({$result['tables_cleared']} tablas limpiadas, {$scopeLabel}).");
    }

    public function seedDemo(Request $request, SystemMaintenanceService $maintenance): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        $counts = $maintenance->initializeDemoData($request->user());

        return redirect()->route('admin.settings.index')->with(
            'success',
            "Datos de demostración iniciados correctamente ({$counts['institutions']} colegios, {$counts['products']} productos, {$counts['users']} usuarios y {$counts['orders']} pedidos)."
        );
    }
}
