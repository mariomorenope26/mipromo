<?php

namespace App\Http\Controllers;

use App\Models\AlumniProfile;
use App\Models\Institution;
use App\Models\Promotion;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlumniDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = AlumniProfile::query()
            ->with(['institution', 'promotion', 'classroom'])
            ->where('verification_status', 'verified');

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('dni', 'like', "%{$search}%")
                    ->orWhere('profession', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('current_city', 'like', "%{$search}%");
            });
        }

        if ($dniFilter = preg_replace('/\D+/', '', (string) $request->string('dni'))) {
            $query->where('dni', 'like', "%{$dniFilter}%");
        }

        if ($institutionSlug = $request->string('colegio')->toString()) {
            $query->whereHas('institution', fn ($q) => $q->where('slug', $institutionSlug));
        }

        if ($year = $request->integer('promocion')) {
            $query->where('graduation_year', $year);
        }

        if ($city = trim((string) $request->string('ciudad'))) {
            $query->where('current_city', 'like', "%{$city}%");
        }

        $alumni = $query->orderBy('full_name')->paginate(12)->withQueryString();
        $institutions = Institution::where('is_active', true)->orderBy('name')->get();
        $years = AlumniProfile::where('verification_status', 'verified')
            ->distinct()
            ->orderByDesc('graduation_year')
            ->pluck('graduation_year');

        return view('directory.index', compact('alumni', 'institutions', 'years'));
    }

    public function store(Request $request): RedirectResponse
    {
        $cleanDni = preg_replace('/\D+/', '', trim((string) $request->input('dni', '')));
        if ($cleanDni !== '') {
            $request->merge(['dni' => $cleanDni]);
        }

        $validated = $request->validate([
            'institution_id' => ['required', 'exists:institutions,id'],
            'full_name' => ['required', 'string', 'max:150'],
            'dni' => ['required', 'string', 'min:8', 'max:20'],
            'entry_year' => ['nullable', 'integer', 'min:1950', 'max:2035'],
            'graduation_year' => ['required', 'integer', 'min:1950', 'max:2035'],
            'current_city' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'profession' => ['nullable', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:120'],
        ]);

        // Detección de duplicados por DNI o nombre + institución + año de egreso
        $duplicate = AlumniProfile::where('institution_id', $validated['institution_id'])
            ->where(function ($q) use ($validated) {
                $q->where('dni', $validated['dni'])
                    ->orWhere(function ($q2) use ($validated) {
                        $q2->where('full_name', $validated['full_name'])
                            ->where('graduation_year', $validated['graduation_year']);
                    });
            })
            ->first();

        if ($duplicate) {
            if ($request->user() && ! $duplicate->user_id) {
                $duplicate->update(['user_id' => $request->user()->id]);

                return back()->with('success', 'Encontramos tu registro histórico en el padrón y lo vinculamos a tu cuenta.');
            }

            return back()->with('error', 'Ya existe un registro con ese DNI o nombre en la misma institución y promoción.');
        }

        $promotion = Promotion::where('institution_id', $validated['institution_id'])
            ->where('year', $validated['graduation_year'])
            ->first();

        $profile = AlumniProfile::create([
            'user_id' => $request->user()?->id,
            'institution_id' => $validated['institution_id'],
            'promotion_id' => $promotion?->id,
            'full_name' => $validated['full_name'],
            'dni' => $validated['dni'],
            'entry_year' => $validated['entry_year'] ?? null,
            'graduation_year' => $validated['graduation_year'],
            'current_city' => $validated['current_city'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? $request->user()?->email,
            'profession' => $validated['profession'] ?? null,
            'company' => $validated['company'] ?? null,
            'verification_status' => 'pending',
            'privacy_settings' => [
                'show_phone' => true,
                'show_email' => true,
                'show_company' => true,
                'show_city' => true,
            ],
        ]);

        AuditLogger::log('alumni.registered', "Solicitud de registro de egresado: {$profile->full_name}", $profile);

        return back()->with('success', 'Tu ficha de egresado fue registrada y está pendiente de validación por tu delegado o institución.');
    }
}
