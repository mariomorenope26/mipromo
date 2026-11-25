<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AlumniProfile;
use App\Models\Classroom;
use App\Models\Community;
use App\Models\Institution;
use App\Models\Promotion;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminAcademicController extends Controller
{
    public function institutions(Request $request): View
    {
        $user = $request->user();
        $institutions = Institution::query()
            ->with(['promotions.classrooms', 'delegates', 'approver'])
            ->withCount(['alumniProfiles', 'campaigns', 'communities', 'activeDelegates'])
            ->when($user->isRole('institution_admin', 'promotion_delegate') && $user->institution_id, fn ($q) => $q->where('id', $user->institution_id))
            ->orderBy('is_published')
            ->orderBy('city')
            ->orderBy('name')
            ->get();

        return view('admin.academic.institutions', compact('institutions'));
    }

    public function storeInstitution(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isRole(UserRole::SuperAdmin->value), 403);

        $maxYear = (int) now()->year - 11;
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'short_name' => ['required', 'string', 'max:60'],
            'type' => ['required', 'in:colegio,instituto,universidad'],
            'modular_code' => ['nullable', 'string', 'max:30'],
            'city' => ['required', 'string', 'max:100'],
            'region' => ['required', 'string', 'max:100'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'founded_year' => ['required', 'integer', 'min:1700', "max:{$maxYear}"],
            'motto' => ['nullable', 'string', 'max:200'],
            'primary_color' => ['required', 'string', 'max:20'],
            'secondary_color' => ['required', 'string', 'max:20'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:50'],
            'logo' => ['nullable', 'image', 'mimes:png', 'max:2048'],
        ], [
            'founded_year.max' => 'El colegio debe contar con más de 10 años de vida institucional.',
            'logo.mimes' => 'La insignia debe estar en formato PNG limpio.',
        ]);

        $baseSlug = Str::slug($validated['short_name'].'-'.$validated['city']);
        $slug = Institution::where('slug', $baseSlug)->exists() ? $baseSlug.'-'.strtolower(Str::random(3)) : $baseSlug;

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $dir = public_path('images/insignias');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $request->file('logo')->move($dir, "{$slug}.png");
            $logoPath = "/images/insignias/{$slug}.png";
        }

        $institution = Institution::create([
            'name' => $validated['name'],
            'short_name' => $validated['short_name'],
            'slug' => $slug,
            'type' => $validated['type'],
            'modular_code' => $validated['modular_code'] ?? null,
            'city' => $validated['city'],
            'region' => $validated['region'],
            'website_url' => $validated['website_url'] ?? null,
            'founded_year' => $validated['founded_year'],
            'motto' => $validated['motto'] ?? null,
            'primary_color' => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'],
            'logo_path' => $logoPath,
            'commission_rate' => $validated['commission_rate'],
            'is_featured' => false,
            'is_published' => false,
            'is_active' => true,
        ]);

        Community::create([
            'institution_id' => $institution->id,
            'type' => 'institution',
            'name' => "Comunidad Oficial — {$institution->short_name}",
            'slug' => "comunidad-{$institution->slug}",
            'description' => "Espacio oficial de ex-alumnos de {$institution->name}.",
            'created_by' => $request->user()->id,
        ]);

        AuditLogger::log('institution.created', "Institución creada (pendiente de delegado y alta): {$institution->name}", $institution);

        return back()->with('success', "Colegio {$institution->name} registrado. Da de alta al menos a un delegado para poder publicarlo en la web principal.");
    }

    public function storeDelegate(Request $request, Institution $institution): RedirectResponse
    {
        abort_unless(
            $request->user()->isRole(UserRole::SuperAdmin->value) ||
            ($request->user()->isRole(UserRole::InstitutionAdmin->value) && $request->user()->institution_id === $institution->id),
            403
        );

        $allowedRoles = $request->user()->isRole(UserRole::SuperAdmin->value)
            ? 'in:institution_admin,promotion_delegate,classroom_delegate'
            : 'in:promotion_delegate,classroom_delegate';

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'role' => ['required', $allowedRoles],
            'phone' => ['nullable', 'string', 'max:30'],
            'dni' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $delegate = User::create([
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make(($validated['password'] ?? null) ?: Str::random(16)),
            'role' => $validated['role'],
            'institution_id' => $institution->id,
            'phone' => $validated['phone'] ?? null,
            'dni' => $validated['dni'] ?? null,
            'city' => $institution->city,
            'referral_code' => strtoupper(Str::random(8)),
            'is_active' => true,
            'privacy_consent_at' => now(),
        ]);

        AuditLogger::log('delegate.activated', "Delegado {$delegate->name} dado de alta para {$institution->short_name}", $institution);

        return back()->with('success', "Delegado {$delegate->name} dado de alta para {$institution->short_name}. Ya puedes publicar el colegio en la web principal.");
    }

    public function toggleDelegateStatus(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isRole(UserRole::SuperAdmin->value), 403);

        $newState = ! $user->is_active;
        $user->update(['is_active' => $newState]);

        $institution = $user->institution;
        if ($institution && ! $newState && ! $institution->canBePublished() && $institution->is_published) {
            $institution->update(['is_published' => false]);
        }

        $actionLabel = $newState ? 'dado de alta' : 'desactivado';
        AuditLogger::log('delegate.toggled', "Delegado {$user->name} {$actionLabel}", $user);

        return back()->with('success', "Delegado {$user->name} {$actionLabel}.");
    }

    public function togglePublication(Request $request, Institution $institution): RedirectResponse
    {
        abort_unless($request->user()->isRole(UserRole::SuperAdmin->value), 403);

        $publish = $request->boolean('publish');

        if ($publish && ! $institution->canBePublished()) {
            return back()->with('error', "No se puede dar de alta a {$institution->short_name} en la web principal sin antes dar de alta al menos a un delegado del colegio.");
        }

        $institution->update([
            'is_published' => $publish,
            'is_featured' => $publish,
            'approved_by' => $publish ? $request->user()->id : $institution->approved_by,
            'approved_at' => $publish ? now() : $institution->approved_at,
        ]);

        AuditLogger::log(
            $publish ? 'institution.published' : 'institution.unpublished',
            $publish ? "Colegio {$institution->name} dado de alta en la web principal." : "Colegio {$institution->name} retirado de la web principal.",
            $institution
        );

        return back()->with(
            'success',
            $publish
                ? "¡{$institution->name} ha sido dado de alta y ya se muestra en la web principal!"
                : "{$institution->name} fue retirado de la web principal."
        );
    }

    public function updateAppearance(Request $request, Institution $institution): RedirectResponse
    {
        abort_unless(
            $request->user()->isRole(UserRole::SuperAdmin->value) ||
            ($request->user()->isRole(UserRole::InstitutionAdmin->value) && $request->user()->institution_id === $institution->id),
            403
        );

        $validated = $request->validate([
            'primary_color' => ['required', 'string', 'max:20'],
            'secondary_color' => ['required', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'mimes:png', 'max:2048'],
        ]);

        $data = [
            'primary_color' => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'],
        ];

        if ($request->hasFile('logo')) {
            $dir = public_path('images/insignias');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $request->file('logo')->move($dir, "{$institution->slug}.png");
            $data['logo_path'] = "/images/insignias/{$institution->slug}.png";
        }

        $institution->update($data);
        AuditLogger::log('institution.appearance_updated', "Colores/insignia actualizados para {$institution->short_name}", $institution);

        return back()->with('success', "Identidad visual de {$institution->short_name} actualizada.");
    }

    public function storePromotion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institution_id' => ['required', 'exists:institutions,id'],
            'year' => ['required', 'integer', 'min:1950', 'max:2035'],
            'name' => ['required', 'string', 'max:160'],
            'motto' => ['nullable', 'string', 'max:200'],
        ]);

        $institution = Institution::findOrFail($validated['institution_id']);
        $slug = "promo-{$validated['year']}-".strtolower(Str::random(3));

        $promotion = Promotion::create([
            ...$validated,
            'slug' => $slug,
            'delegate_user_id' => $request->user()->id,
        ]);

        Community::create([
            'institution_id' => $institution->id,
            'promotion_id' => $promotion->id,
            'type' => 'promotion',
            'name' => "{$institution->short_name} — {$promotion->name}",
            'slug' => Str::slug("{$institution->slug}-{$promotion->year}-".Str::random(3)),
            'description' => "Comunidad oficial de la promoción {$promotion->year}.",
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', "Promoción {$promotion->year} y su comunidad automática creadas.");
    }

    public function storeClassroom(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'promotion_id' => ['required', 'exists:promotions,id'],
            'grade' => ['required', 'string', 'max:60'],
            'section' => ['required', 'string', 'max:40'],
        ]);

        $promotion = Promotion::with('institution')->findOrFail($validated['promotion_id']);

        $classroom = Classroom::firstOrCreate([
            'promotion_id' => $promotion->id,
            'grade' => $validated['grade'],
            'section' => $validated['section'],
        ], ['delegate_user_id' => $request->user()->id]);

        Community::firstOrCreate(
            ['classroom_id' => $classroom->id, 'type' => 'classroom'],
            [
                'institution_id' => $promotion->institution_id,
                'promotion_id' => $promotion->id,
                'name' => "Aula {$classroom->label()} — {$promotion->institution->short_name} {$promotion->year}",
                'slug' => Str::slug("{$promotion->institution->slug}-{$promotion->year}-{$classroom->section}-".Str::random(3)),
                'description' => "Grupo de sección {$classroom->label()}.",
                'created_by' => $request->user()->id,
            ]
        );

        return back()->with('success', "Aula {$classroom->label()} registrada correctamente.");
    }

    public function alumni(Request $request): View
    {
        $user = $request->user();
        $query = AlumniProfile::query()
            ->with(['institution', 'promotion', 'classroom'])
            ->when($user->isRole('institution_admin', 'promotion_delegate', 'classroom_delegate') && $user->institution_id, fn ($q) => $q->where('institution_id', $user->institution_id));

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($q) use ($search): void {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('dni', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('profession', 'like', "%{$search}%");
            });
        }

        if ($institutionId = $request->integer('institution_id')) {
            $query->where('institution_id', $institutionId);
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('verification_status', $status);
        }

        $alumni = $query->orderByRaw("CASE WHEN verification_status = 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $institutions = Institution::where('is_active', true)->orderBy('name')->get();

        return view('admin.academic.alumni', compact('alumni', 'institutions'));
    }

    public function storeAlumni(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'institution_id' => ['required', 'exists:institutions,id'],
            'dni' => ['required', 'digits:8'],
            'full_name' => ['required', 'string', 'max:150'],
            'graduation_year' => ['required', 'integer', 'min:1950', 'max:2035'],
            'current_city' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'profession' => ['nullable', 'string', 'max:120'],
            'auto_verify' => ['nullable', 'boolean'],
        ]);

        if ($user->isRole('institution_admin', 'promotion_delegate', 'classroom_delegate') && $user->institution_id) {
            abort_unless((int) $validated['institution_id'] === $user->institution_id, 403);
        }

        $exists = AlumniProfile::where('institution_id', $validated['institution_id'])
            ->where('dni', $validated['dni'])
            ->first();

        if ($exists) {
            return back()->with('error', "El DNI {$validated['dni']} ya está registrado en el padrón de esta institución ({$exists->full_name}).");
        }

        $promotion = Promotion::where('institution_id', $validated['institution_id'])
            ->where('year', $validated['graduation_year'])
            ->first();

        $matchedUsers = User::query()
            ->where(function ($q) use ($validated): void {
                $q->where('dni', $validated['dni']);
                if (! empty($validated['email'])) {
                    $q->orWhere('email', strtolower(trim((string) $validated['email'])));
                }
            })
            ->get();

        $matchedUser = $matchedUsers->first();
        $isVerified = $request->boolean('auto_verify', true);

        $profile = AlumniProfile::create([
            'user_id' => $matchedUser?->id,
            'institution_id' => $validated['institution_id'],
            'promotion_id' => $promotion?->id,
            'full_name' => $validated['full_name'],
            'dni' => $validated['dni'],
            'graduation_year' => $validated['graduation_year'],
            'current_city' => $validated['current_city'],
            'email' => $validated['email'] ?? $matchedUser?->email,
            'phone' => $validated['phone'] ?? $matchedUser?->phone,
            'profession' => $validated['profession'] ?? null,
            'verification_status' => $isVerified ? 'verified' : 'pending',
            'verified_by' => $isVerified ? $user->id : null,
            'verified_at' => $isVerified ? now() : null,
            'privacy_settings' => [
                'show_phone' => true,
                'show_email' => true,
                'show_company' => true,
                'show_city' => true,
            ],
        ]);

        if ($isVerified) {
            foreach ($matchedUsers as $candidate) {
                if ($candidate->role === UserRole::Buyer->value) {
                    $candidate->update([
                        'role' => UserRole::Alumni->value,
                        'institution_id' => $validated['institution_id'],
                    ]);
                }
            }
        }

        AuditLogger::log('alumni.admin_created', "Egresado {$profile->full_name} (DNI {$profile->dni}) registrado en padrón.", $profile);

        return back()->with('success', "Ex-alumno {$profile->full_name} registrado".($isVerified ? ' y validado' : '').' correctamente.');
    }

    public function verifyAlumni(Request $request, AlumniProfile $profile): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:verified,rejected'],
        ]);

        $profile->update([
            'verification_status' => $validated['status'],
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        if ($validated['status'] === 'verified' && $profile->user && $profile->user->role === 'buyer') {
            $profile->user->update(['role' => 'alumni']);
        }

        AuditLogger::log('alumni.verification', "Egresado {$profile->full_name} marcado como {$validated['status']}", $profile);

        return back()->with('success', "Estado de {$profile->full_name} actualizado.");
    }

    public function importAlumniCsv(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institution_id' => ['required', 'exists:institutions,id'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:4096'],
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        $batch = 'IMP-'.now()->format('YmdHis');
        $imported = 0;
        $duplicates = 0;

        foreach ($rows as $index => $row) {
            if ($index === 0 && isset($row[0]) && str_contains(strtolower($row[0]), 'nombre')) {
                continue;
            }
            if (count($row) < 3 || trim((string) $row[0]) === '') {
                continue;
            }

            $fullName = trim((string) $row[0]);
            $dni = trim((string) ($row[1] ?? ''));
            $gradYear = (int) ($row[2] ?? now()->year);
            $city = trim((string) ($row[3] ?? 'Lima'));
            $email = trim((string) ($row[4] ?? ''));
            $profession = trim((string) ($row[5] ?? ''));

            $exists = AlumniProfile::where('institution_id', $validated['institution_id'])
                ->where(function ($q) use ($dni, $fullName, $gradYear) {
                    if ($dni !== '') {
                        $q->where('dni', $dni);
                    } else {
                        $q->where('full_name', $fullName)->where('graduation_year', $gradYear);
                    }
                })->exists();

            if ($exists) {
                $duplicates++;

                continue;
            }

            $promotion = Promotion::where('institution_id', $validated['institution_id'])
                ->where('year', $gradYear)
                ->first();

            AlumniProfile::create([
                'institution_id' => $validated['institution_id'],
                'promotion_id' => $promotion?->id,
                'full_name' => $fullName,
                'dni' => $dni ?: null,
                'graduation_year' => $gradYear,
                'current_city' => $city ?: 'Lima',
                'email' => $email ?: null,
                'profession' => $profession ?: null,
                'verification_status' => 'verified',
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'privacy_settings' => [
                    'show_phone' => true,
                    'show_email' => true,
                    'show_company' => true,
                    'show_city' => true,
                ],
                'import_batch' => $batch,
            ]);
            $imported++;
        }

        AuditLogger::log('alumni.csv_imported', "Importación masiva {$batch}: {$imported} egresados importados, {$duplicates} duplicados omitidos.");

        return back()->with('success', "Importación completada: {$imported} egresados registrados, {$duplicates} duplicados omitidos.");
    }
}
