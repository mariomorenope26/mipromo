<?php

namespace App\Http\Controllers;

use App\Models\AlumniProfile;
use App\Models\User;
use App\Services\ApiPeruService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DniLookupController extends Controller
{
    public function __invoke(Request $request, ApiPeruService $apiPeru): JsonResponse
    {
        $validated = $request->validate([
            'dni' => ['required', 'string', 'max:20'],
        ]);

        $dni = preg_replace('/\D+/', '', trim($validated['dni'])) ?? '';
        $result = $apiPeru->lookupDni($dni);

        $response = [
            'ok' => $result['ok'],
            'code' => $result['code'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null,
            'elapsed_ms' => $result['elapsed_ms'] ?? null,
        ];

        // Solo expone coincidencias internas de BD a personal administrativo autenticado
        if ($request->user()?->canAccessAdmin() && preg_match('/^\d{8}$/', $dni)) {
            $localUser = User::with('institution')->where('dni', $dni)->first();
            $localAlumni = AlumniProfile::with('institution')->where('dni', $dni)->first();

            $response['local_matches'] = [
                'user' => $localUser ? [
                    'id' => $localUser->id,
                    'name' => $localUser->name,
                    'email' => $localUser->email,
                    'role_label' => $localUser->roleLabel(),
                    'institution' => $localUser->institution?->short_name,
                ] : null,
                'alumni' => $localAlumni ? [
                    'id' => $localAlumni->id,
                    'full_name' => $localAlumni->full_name,
                    'graduation_year' => $localAlumni->graduation_year,
                    'verification_status' => $localAlumni->verification_status,
                    'institution' => $localAlumni->institution?->short_name,
                ] : null,
            ];
        }

        $statusCode = match ($result['code']) {
            'invalid_dni' => 422,
            'missing_token', 'disabled' => 422,
            'connection_error' => 502,
            default => $result['ok'] ? 200 : 404,
        };

        return response()->json($response, $statusCode);
    }
}
