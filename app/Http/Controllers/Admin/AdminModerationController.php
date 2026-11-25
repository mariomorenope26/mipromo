<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CommunityPost;
use App\Models\Complaint;
use App\Models\ContentReport;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminModerationController extends Controller
{
    public function index(): View
    {
        $reports = ContentReport::with(['reporter', 'resolver'])->latest()->get();
        $complaints = Complaint::latest()->get();
        $logs = AuditLog::with('user')->latest()->take(30)->get();

        return view('admin.moderation.index', compact('reports', 'complaints', 'logs'));
    }

    public function resolveReport(Request $request, ContentReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:dismiss,hide_post'],
        ]);

        if ($validated['action'] === 'hide_post' && $report->reportable_type === CommunityPost::class) {
            CommunityPost::where('id', $report->reportable_id)->update(['status' => 'hidden']);
        }

        $report->update([
            'status' => 'resolved',
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        AuditLogger::log('moderation.report_resolved', "Reporte #{$report->id} resuelto ({$validated['action']})", $report);

        return back()->with('success', 'Reporte de moderación resuelto.');
    }

    public function respondComplaint(Request $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validate([
            'response' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $complaint->update([
            'status' => 'resolved',
            'response' => $validated['response'],
            'responded_at' => now(),
        ]);

        AuditLogger::log('complaint.responded', "Reclamación {$complaint->complaint_code} respondida.", $complaint);

        return back()->with('success', "Respuesta registrada para {$complaint->complaint_code}.");
    }
}
