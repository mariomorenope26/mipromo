<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Institution;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\ReturnRequest;
use App\Services\AuditLogger;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminOperationsController extends Controller
{
    public function orders(Request $request): View
    {
        $user = $request->user();
        $scopedInstitutionId = $user->isRole('institution_admin', 'promotion_delegate', 'classroom_delegate') ? $user->institution_id : null;
        $scopedVendorId = ($user->isRole('vendor') && $user->vendor) ? $user->vendor->id : null;

        $query = $this->scopedOrdersQuery($request)
            ->with(['institution', 'payments', 'invoice', 'items', 'shippingZone', 'returnRequests']);

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($q) use ($search): void {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_dni_ruc', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        if ($orderStatus = $request->string('estado')->toString()) {
            $query->where('order_status', $orderStatus);
        }

        if ($paymentStatus = $request->string('pago')->toString()) {
            $query->where('payment_status', $paymentStatus);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        $returnRequests = ReturnRequest::with(['order', 'user'])
            ->where('status', 'pending')
            ->when($scopedInstitutionId, fn ($q) => $q->whereHas('order', fn ($oq) => $oq->where('institution_id', $scopedInstitutionId)))
            ->when($scopedVendorId, fn ($q) => $q->whereHas('order.items', fn ($iq) => $iq->where('vendor_id', $scopedVendorId)))
            ->latest()
            ->get();

        return view('admin.operations.orders', compact('orders', 'returnRequests'));
    }

    private function scopedOrdersQuery(Request $request)
    {
        $user = $request->user();

        return Order::query()
            ->when($user->isRole('institution_admin', 'promotion_delegate', 'classroom_delegate') && $user->institution_id, fn ($q) => $q->where('institution_id', $user->institution_id))
            ->when($user->isRole('vendor') && $user->vendor, fn ($q) => $q->whereHas('items', fn ($iq) => $iq->where('vendor_id', $user->vendor->id)));
    }

    public function verifyPayment(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $order = $payment->order;

        if ($validated['action'] === 'approve') {
            $payment->update([
                'status' => 'approved',
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'notes' => $validated['notes'] ?? $payment->notes,
            ]);

            $order->update([
                'payment_status' => $payment->is_partial_deposit ? 'partial' : 'verified',
                'order_status' => $order->order_status === 'pending' ? 'confirmed' : $order->order_status,
            ]);

            if (! $payment->is_partial_deposit && ! $order->invoice) {
                $docType = strlen(preg_replace('/\D/', '', (string) $order->customer_dni_ruc)) === 11 ? 'factura' : 'boleta';
                InvoiceService::issueForOrder($order, $docType, $order->customer_name);
            }

            AuditLogger::log('payment.approved', "Pago #{$payment->id} conciliado para pedido {$order->order_number}", $order);

            return back()->with('success', "Pago del pedido {$order->order_number} conciliado y comprobante SUNAT emitido.");
        }

        $payment->update([
            'status' => 'rejected',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'notes' => $validated['notes'] ?? 'Voucher no validado.',
        ]);

        $order->update(['payment_status' => 'rejected']);
        AuditLogger::log('payment.rejected', "Pago #{$payment->id} rechazado para pedido {$order->order_number}", $order);

        return back()->with('error', "Pago del pedido {$order->order_number} marcado como rechazado.");
    }

    public function updateOrderStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'order_status' => ['required', 'in:pending,confirmed,in_production,shipped,delivered,cancelled'],
            'courier_name' => ['nullable', 'string', 'max:80'],
            'tracking_code' => ['nullable', 'string', 'max:80'],
        ]);

        $order->update($validated);
        AuditLogger::log('order.status_updated', "Pedido {$order->order_number} actualizado a {$validated['order_status']}", $order);

        return back()->with('success', "Estado logístico de {$order->order_number} actualizado.");
    }

    public function exportOrdersCsv(Request $request): StreamedResponse
    {
        $orders = $this->scopedOrdersQuery($request)->with(['institution', 'invoice'])->latest()->get();

        return response()->streamDownload(function () use ($orders): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Pedido', 'Fecha', 'Cliente', 'DNI/RUC', 'Institución', 'Total S/', 'Comisión Inst. S/', 'Estado Pago', 'Estado Pedido', 'Comprobante SUNAT']);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->created_at?->format('Y-m-d H:i'),
                    $order->customer_name,
                    $order->customer_dni_ruc,
                    $order->institution?->short_name ?? 'General',
                    $order->total,
                    $order->institution_commission_amount,
                    $order->payment_status,
                    $order->order_status,
                    $order->invoice?->formattedNumber() ?? 'Pendiente',
                ]);
            }

            fclose($handle);
        }, 'reporte-pedidos-mipromo.csv');
    }

    public function campaigns(Request $request): View
    {
        $user = $request->user();
        $campaigns = Campaign::query()
            ->with(['institution', 'promotion', 'product', 'participants.variant'])
            ->when($user->isRole('institution_admin', 'promotion_delegate') && $user->institution_id, fn ($q) => $q->where('institution_id', $user->institution_id))
            ->latest()
            ->get();

        $institutions = Institution::where('is_active', true)->orderBy('name')->get();
        $promotions = Promotion::with('institution')->orderByDesc('year')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('admin.operations.campaigns', compact('campaigns', 'institutions', 'promotions', 'products'));
    }

    public function storeCampaign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institution_id' => ['required', 'exists:institutions,id'],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'product_id' => ['required', 'exists:products,id'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string'],
            'unit_price' => ['required', 'numeric', 'min:1'],
            'deposit_percent' => ['required', 'integer', 'min:10', 'max:100'],
            'target_units' => ['required', 'integer', 'min:5'],
            'ends_at' => ['required', 'date'],
            'delivery_mode' => ['required', 'in:consolidated_delegate,individual'],
        ]);

        $campaign = Campaign::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'slug' => Str::slug($validated['title']).'-'.strtolower(Str::random(4)),
            'starts_at' => now(),
            'current_units' => 0,
            'status' => 'active',
        ]);

        AuditLogger::log('campaign.created', "Campaña grupal creada: {$campaign->title}", $campaign);

        return back()->with('success', "Campaña {$campaign->title} lanzada correctamente.");
    }

    public function updateCampaignStatus(Request $request, Campaign $campaign): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,goal_reached,in_production,closed,cancelled'],
        ]);

        $campaign->update(['status' => $validated['status']]);
        AuditLogger::log('campaign.status_updated', "Campaña {$campaign->title} cambió a {$validated['status']}", $campaign);

        return back()->with('success', "Estado de campaña actualizado a {$campaign->statusLabel()}.");
    }

    public function campaignProductionSheet(Campaign $campaign): View
    {
        $campaign->load(['institution', 'promotion', 'product.vendor', 'participants.variant']);

        $summaryByVariant = $campaign->participants
            ->groupBy(fn ($p) => $p->variant?->label() ?? 'Estándar')
            ->map(fn ($group) => $group->sum('quantity'));

        return view('admin.operations.production', compact('campaign', 'summaryByVariant'));
    }

    public function resolveReturn(Request $request, ReturnRequest $returnRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected,completed'],
            'admin_notes' => ['nullable', 'string', 'max:400'],
        ]);

        $returnRequest->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        if ($validated['status'] === 'approved' && $returnRequest->request_type === 'devolucion' && $returnRequest->order->invoice) {
            InvoiceService::issueCreditNote($returnRequest->order, $returnRequest->reason);
        }

        AuditLogger::log('order.return_resolved', "Solicitud #{$returnRequest->id} de {$returnRequest->order->order_number} -> {$validated['status']}", $returnRequest);

        return back()->with('success', "Solicitud de cambio/devolución actualizada ({$validated['status']}).");
    }

    public function issueCreditNote(Request $request, Order $order): RedirectResponse
    {
        $reason = trim((string) $request->input('reason')) ?: 'Anulación o devolución de pedido';
        $note = InvoiceService::issueCreditNote($order, $reason);

        return back()->with('success', "Nota de Crédito SUNAT {$note->formattedNumber()} emitida.");
    }
}
