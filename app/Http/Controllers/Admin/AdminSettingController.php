<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\ApiPeruService;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingController extends Controller
{
    public function index(ApiPeruService $apiPeru): View
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

        $testResult = session('dni_test_result');

        return view('admin.settings.index', compact(
            'settings',
            'hasToken',
            'maskedToken',
            'tokenSource',
            'testResult'
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
}
