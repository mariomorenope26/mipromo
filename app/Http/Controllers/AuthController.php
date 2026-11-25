<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AlumniProfile;
use App\Models\CampaignParticipant;
use App\Models\CommunityMember;
use App\Models\Institution;
use App\Models\Promotion;
use App\Models\User;
use App\Models\Wishlist;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = strtolower(trim($credentials['email']));

        if (! Auth::attempt(['email' => $email, 'password' => $credentials['password']], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Credenciales incorrectas.'])->onlyInput('email');
        }

        $user = $request->user();
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->with('error', 'Tu cuenta se encuentra suspendida. Contáctanos por WhatsApp al 959199368.');
        }

        $request->session()->regenerate();
        AuditLogger::log('auth.login', 'Inicio de sesión de '.$user->email, $user);

        $redirectTo = $this->resolveSafeRedirect($request->input('redirect_to'));

        return $redirectTo
            ? redirect($redirectTo)->with('success', '¡Sesión iniciada! Ya accedes a tarifas de ex-alumno.')
            : redirect()->intended(route('account.index'));
    }

    public function showRegister(Request $request): View
    {
        $institutions = Institution::published()->orderBy('name')->get();
        $ref = trim((string) $request->string('ref'));
        $redirectTo = $this->resolveSafeRedirect($request->string('redirect')->toString());

        return view('auth.register', compact('institutions', 'ref', 'redirectTo'));
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email', ''))),
            'dni' => preg_replace('/\D+/', '', (string) $request->input('dni', '')) ?: null,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
            'password_confirmation' => ['nullable', 'same:password'],
            'dni' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:100'],
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'graduation_year' => ['nullable', 'integer', 'min:1950', 'max:2035'],
            'referral_code_used' => ['nullable', 'string', 'max:24'],
            'privacy_consent' => ['accepted'],
        ]);

        $isAlumni = ! empty($validated['institution_id']) && ! empty($validated['graduation_year']);
        $referrer = ! empty($validated['referral_code_used'])
            ? User::where('referral_code', strtoupper(trim($validated['referral_code_used'])))->first()
            : null;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $isAlumni ? UserRole::Alumni->value : UserRole::Buyer->value,
            'institution_id' => $validated['institution_id'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'dni' => $validated['dni'] ?? null,
            'city' => ($validated['city'] ?? null) ?: 'Lima',
            'referral_code' => strtoupper(Str::random(8)),
            'referred_by_user_id' => $referrer?->id,
            'is_active' => true,
            'privacy_consent_at' => now(),
        ]);

        if ($isAlumni) {
            $promotion = Promotion::where('institution_id', $validated['institution_id'])
                ->where('year', $validated['graduation_year'])
                ->first();

            AlumniProfile::create([
                'user_id' => $user->id,
                'institution_id' => $validated['institution_id'],
                'promotion_id' => $promotion?->id,
                'full_name' => $validated['name'],
                'dni' => $validated['dni'] ?? null,
                'graduation_year' => $validated['graduation_year'],
                'current_city' => ($validated['city'] ?? null) ?: 'Lima',
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'],
                'verification_status' => 'pending',
                'privacy_settings' => [
                    'show_phone' => true,
                    'show_email' => true,
                    'show_company' => true,
                    'show_city' => true,
                ],
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        AuditLogger::log('auth.registered', "Registro rápido de {$user->email}", $user);

        $redirectTo = $this->resolveSafeRedirect($request->input('redirect_to'));

        return redirect($redirectTo ?: route('account.index'))
            ->with('success', '¡Cuenta creada en segundos! Ya puedes comprar con beneficios MiPromo.');
    }

    private function resolveSafeRedirect(?string $target): ?string
    {
        if (! $target) {
            return null;
        }

        $target = trim($target);
        if (str_starts_with($target, '/') && ! str_starts_with($target, '//')) {
            return $target;
        }

        $appUrl = rtrim(url('/'), '/');
        if ($appUrl !== '' && str_starts_with($target, $appUrl)) {
            return $target;
        }

        return null;
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Sesión cerrada correctamente.');
    }

    public function account(Request $request): View
    {
        $user = $request->user()->load(['institution', 'alumniProfile.promotion', 'alumniProfile.classroom']);
        if (! $user->referral_code) {
            $user->update(['referral_code' => strtoupper(Str::random(8))]);
        }
        $orders = $user->orders()->with(['items', 'invoice', 'returnRequests'])->latest()->get();
        $wishlists = Wishlist::with('product.category')->where('user_id', $user->id)->get();
        $participations = CampaignParticipant::with('campaign.institution')->where('user_id', $user->id)->latest()->get();
        $memberships = CommunityMember::with('community.institution')->where('user_id', $user->id)->get();
        $notifications = $user->notifications()->take(10)->get();
        $referralsCount = $user->referrals()->count();

        return view('auth.account', compact('user', 'orders', 'wishlists', 'participations', 'memberships', 'notifications', 'referralsCount'));
    }

    public function markNotificationsRead(Request $request): RedirectResponse
    {
        $request->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'Notificaciones marcadas como leídas.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        AuditLogger::log('auth.password_updated', "Contraseña actualizada por {$user->email}", $user);

        return back()->with('success', 'Contraseña actualizada con éxito.');
    }

    public function updatePrivacy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:100'],
            'profession' => ['nullable', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update([
            'phone' => $validated['phone'] ?? $user->phone,
            'city' => $validated['city'] ?? $user->city,
        ]);

        if ($user->alumniProfile) {
            $user->alumniProfile->update([
                'phone' => $validated['phone'] ?? $user->alumniProfile->phone,
                'current_city' => $validated['city'] ?? $user->alumniProfile->current_city,
                'profession' => $validated['profession'] ?? $user->alumniProfile->profession,
                'company' => $validated['company'] ?? $user->alumniProfile->company,
                'bio' => $validated['bio'] ?? $user->alumniProfile->bio,
                'privacy_settings' => [
                    'show_phone' => $request->boolean('show_phone'),
                    'show_email' => $request->boolean('show_email'),
                    'show_company' => $request->boolean('show_company'),
                    'show_city' => $request->boolean('show_city'),
                ],
            ]);
        }

        return back()->with('success', 'Preferencias de perfil y privacidad (Ley 29733) actualizadas.');
    }
}
