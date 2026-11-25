<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class ApiPeruService
{
    public const DEFAULT_ENDPOINT = 'https://api.apiperu.dev/dni';

    public function isEnabled(): bool
    {
        return SystemSetting::getValue('apiperu_enabled', '1') === '1';
    }

    public function endpoint(): string
    {
        $configured = trim((string) SystemSetting::getValue('apiperu_endpoint', self::DEFAULT_ENDPOINT));

        return $configured !== '' ? $configured : self::DEFAULT_ENDPOINT;
    }

    public function token(): string
    {
        $dbToken = trim((string) SystemSetting::getValue('apiperu_token', ''));

        if ($dbToken !== '') {
            return $dbToken;
        }

        return trim((string) config('services.apiperu.token', ''));
    }

    public function isConfigured(): bool
    {
        return $this->isEnabled() && $this->token() !== '';
    }

    /**
     * Consulta un DNI de 8 dígitos en apiperu.dev usando token Bearer.
     *
     * @return array{ok: bool, code: string, message: string, data?: array<string, mixed>, elapsed_ms?: int}
     */
    public function lookupDni(string $rawDni, ?string $overrideToken = null, ?string $overrideEndpoint = null, bool $useCache = true): array
    {
        $dni = preg_replace('/\D+/', '', trim($rawDni)) ?? '';

        if (! preg_match('/^\d{8}$/', $dni)) {
            return [
                'ok' => false,
                'code' => 'invalid_dni',
                'message' => 'Ingresa un número de DNI válido de 8 dígitos.',
            ];
        }

        if ($overrideToken === null && ! $this->isEnabled()) {
            return [
                'ok' => false,
                'code' => 'disabled',
                'message' => 'La consulta automática por DNI está desactivada en Configuración.',
            ];
        }

        $token = $overrideToken !== null ? trim($overrideToken) : $this->token();
        if ($token === '') {
            return [
                'ok' => false,
                'code' => 'missing_token',
                'message' => 'Token de apiperu.dev no configurado. Ingrésalo en Superadmin → Configuración.',
            ];
        }

        $endpoint = $overrideEndpoint !== null && trim($overrideEndpoint) !== ''
            ? trim($overrideEndpoint)
            : $this->endpoint();

        $cacheKey = 'apiperu_dni_'.sha1($endpoint.'|'.$dni);
        if ($useCache && $overrideToken === null && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $started = microtime(true);

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token,
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, ['dni' => $dni]);

            $elapsedMs = (int) round((microtime(true) - $started) * 1000);
            $json = $response->json();

            if (! is_array($json)) {
                return [
                    'ok' => false,
                    'code' => 'invalid_response',
                    'message' => "Respuesta inesperada de apiperu.dev (HTTP {$response->status()}).",
                    'elapsed_ms' => $elapsedMs,
                ];
            }

            if ($response->successful() && ($json['success'] ?? false) === true && is_array($json['data'] ?? null)) {
                $payload = $json['data'];
                $nombres = trim((string) ($payload['nombres'] ?? ''));
                $paterno = trim((string) ($payload['apellido_paterno'] ?? ''));
                $materno = trim((string) ($payload['apellido_materno'] ?? ''));
                $rawFull = trim((string) ($payload['nombre_completo'] ?? trim("{$nombres} {$paterno} {$materno}")));

                // Orden natural "Nombres ApellidoPaterno ApellidoMaterno" con capitalización limpia
                $naturalName = trim("{$nombres} {$paterno} {$materno}");
                $fullName = $this->formatPersonName($naturalName !== '' ? $naturalName : $rawFull);

                $result = [
                    'ok' => true,
                    'code' => (string) ($json['code'] ?? 'found'),
                    'message' => 'Identidad validada vía apiperu.dev (RENIEC).',
                    'elapsed_ms' => $elapsedMs,
                    'data' => [
                        'dni' => (string) ($payload['numero'] ?? $dni),
                        'nombres' => $this->formatPersonName($nombres),
                        'apellido_paterno' => $this->formatPersonName($paterno),
                        'apellido_materno' => $this->formatPersonName($materno),
                        'nombre_completo' => $rawFull,
                        'full_name' => $fullName,
                        'codigo_verificacion' => $payload['codigo_verificacion'] ?? null,
                    ],
                ];

                if ($useCache && $overrideToken === null) {
                    Cache::put($cacheKey, $result, now()->addHours(24));
                }

                return $result;
            }

            $apiMessage = trim((string) ($json['message'] ?? 'No se encontró información para el DNI ingresado.'));

            return [
                'ok' => false,
                'code' => (string) ($json['code'] ?? 'api_error'),
                'message' => $apiMessage,
                'elapsed_ms' => $elapsedMs,
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'code' => 'connection_error',
                'message' => 'No se pudo conectar con apiperu.dev en este momento: '.Str::limit($e->getMessage(), 90),
            ];
        }
    }

    private function formatPersonName(string $text): string
    {
        $clean = preg_replace('/\s+/', ' ', trim($text)) ?? '';
        if ($clean === '') {
            return '';
        }

        return mb_convert_case(mb_strtolower($clean, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
}
