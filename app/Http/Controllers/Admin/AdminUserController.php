<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->with(['institution', 'alumniProfile'])->withCount('referrals');

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('dni', 'like', "%{$search}%");
            });
        }

        if ($role = $request->string('role')->toString()) {
            $query->where('role', $role);
        }

        if ($institutionId = $request->integer('institution_id')) {
            $query->where('institution_id', $institutionId);
        }

        if ($request->filled('estado')) {
            $query->where('is_active', $request->string('estado')->toString() === '1');
        }

        $users = $query->orderByDesc('is_active')->orderBy('role')->orderBy('name')->paginate(20)->withQueryString();
        $institutions = Institution::orderBy('name')->get();
        $roles = UserRole::cases();
        $roleStats = collect($roles)->mapWithKeys(fn (UserRole $r) => [
            $r->value => User::where('role', $r->value)->count(),
        ]);

        return view('admin.users.index', compact('users', 'institutions', 'roles', 'roleStats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $roleValues = array_map(fn (UserRole $r) => $r->value, UserRole::cases());

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email', ''))),
            'dni' => preg_replace('/\D+/', '', trim((string) $request->input('dni', ''))) ?: null,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
            'role' => ['required', Rule::in($roleValues)],
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'dni' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'institution_id' => $validated['institution_id'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'dni' => $validated['dni'] ?? null,
            'city' => $validated['city'] ?? 'Lima',
            'referral_code' => strtoupper(Str::random(8)),
            'is_active' => $request->boolean('is_active', true),
            'privacy_consent_at' => now(),
        ]);

        AuditLogger::log('user.created', "Usuario {$user->email} creado con rol {$user->roleLabel()}", $user);

        return back()->with('success', "Usuario {$user->name} creado con rol {$user->roleLabel()}.");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $roleValues = array_map(fn (UserRole $r) => $r->value, UserRole::cases());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'role' => ['required', Rule::in($roleValues)],
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'dni' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', Password::min(8)->letters()->numbers()],
        ]);

        $isActive = $request->boolean('is_active');
        if ($user->id === $request->user()->id && ($validated['role'] !== UserRole::SuperAdmin->value || ! $isActive)) {
            return back()->with('error', 'No puedes quitarte el rol Super administrador ni desactivar tu propia cuenta.');
        }

        $data = [
            'name' => $validated['name'],
            'role' => $validated['role'],
            'institution_id' => $validated['institution_id'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'dni' => $validated['dni'] ?? $user->dni,
            'city' => $validated['city'] ?? $user->city,
            'is_active' => $isActive,
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        // Si se desactivó al último delegado activo de una institución publicada, retirar publicación
        if ($user->institution && ! $isActive && ! $user->institution->canBePublished() && $user->institution->is_published) {
            $user->institution->update(['is_published' => false]);
        }

        AuditLogger::log('user.updated', "Usuario {$user->email} actualizado (rol: {$user->role}, activo: ".($isActive ? 'sí' : 'no').')', $user);

        return back()->with('success', "Usuario {$user->name} actualizado correctamente.");
    }
}
