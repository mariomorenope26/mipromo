<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalController extends Controller
{
    public function privacy(): View
    {
        return view('legal.privacy');
    }

    public function terms(): View
    {
        return view('legal.terms');
    }

    public function complaints(Request $request): View
    {
        $lookupCode = trim((string) $request->string('codigo'));
        $complaint = $lookupCode !== ''
            ? Complaint::where('complaint_code', $lookupCode)->first()
            : null;

        return view('legal.complaints', compact('complaint', 'lookupCode'));
    }

    public function storeComplaint(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_number' => ['nullable', 'string', 'max:40'],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_doc_number' => ['required', 'string', 'min:8', 'max:20'],
            'customer_email' => ['required', 'email', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'type' => ['required', 'in:reclamo,queja'],
            'subject' => ['required', 'string', 'max:200'],
            'detail' => ['required', 'string', 'min:10', 'max:3000'],
            'request_detail' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $nextSeq = (int) Complaint::max('id') + 1;
        $code = sprintf('LRV-%s-%04d', now()->format('Y'), $nextSeq);

        $complaint = Complaint::create([
            ...$validated,
            'complaint_code' => $code,
            'status' => 'pending',
        ]);

        AuditLogger::log('complaint.created', "Hoja de reclamación {$code} registrada.", $complaint);

        return redirect()->route('legal.complaints', ['codigo' => $code])
            ->with('success', "Hoja de Reclamación {$code} registrada correctamente.");
    }
}
