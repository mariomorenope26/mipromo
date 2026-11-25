<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\MessageTemplate;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCommunicationController extends Controller
{
    public function index(): View
    {
        $templates = MessageTemplate::latest()->get();
        $institutions = Institution::orderBy('name')->get();
        $roles = UserRole::cases();
        $recentNotifications = UserNotification::with(['user', 'institution'])->latest()->take(25)->get();

        return view('admin.communications.index', compact('templates', 'institutions', 'roles', 'recentNotifications'));
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'channel' => ['required', 'in:whatsapp,email,web'],
            'subject' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $template = MessageTemplate::create([
            ...$validated,
            'slug' => Str::slug($validated['name']).'-'.strtolower(Str::random(3)),
            'is_active' => true,
        ]);

        AuditLogger::log('comms.template_created', "Plantilla creada: {$template->name}", $template);

        return back()->with('success', "Plantilla {$template->name} guardada.");
    }

    public function broadcast(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:2000'],
            'channel' => ['required', 'in:web,email,whatsapp'],
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'role' => ['nullable', 'string'],
            'action_url' => ['nullable', 'string', 'max:255'],
        ]);

        $recipients = User::where('is_active', true)
            ->when(! empty($validated['institution_id']), fn ($q) => $q->where('institution_id', $validated['institution_id']))
            ->when(! empty($validated['role']), fn ($q) => $q->where('role', $validated['role']))
            ->get();

        foreach ($recipients as $recipient) {
            UserNotification::create([
                'user_id' => $recipient->id,
                'institution_id' => $validated['institution_id'] ?? null,
                'title' => $validated['title'],
                'body' => $validated['body'],
                'channel' => $validated['channel'],
                'action_url' => $validated['action_url'] ?? null,
            ]);
        }

        AuditLogger::log('comms.broadcast', "Anuncio '{$validated['title']}' enviado vía {$validated['channel']} a {$recipients->count()} usuarios.");

        return back()->with('success', "Comunicado enviado a {$recipients->count()} usuario(s) activos.");
    }
}
