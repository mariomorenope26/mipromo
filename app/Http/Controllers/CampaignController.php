<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignParticipant;
use App\Models\Institution;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Services\AuditLogger;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function index(Request $request): View
    {
        $query = Campaign::query()
            ->with(['institution', 'promotion', 'product'])
            ->where('status', '!=', 'cancelled');

        if ($institutionSlug = $request->string('colegio')->toString()) {
            $query->whereHas('institution', fn ($q) => $q->where('slug', $institutionSlug));
        }

        if ($status = $request->string('estado')->toString()) {
            $query->where('status', $status);
        }

        $campaigns = $query->orderByRaw("CASE WHEN status = 'active' THEN 0 WHEN status = 'goal_reached' THEN 1 ELSE 2 END")
            ->orderBy('ends_at')
            ->paginate(9)
            ->withQueryString();

        $institutions = Institution::where('is_active', true)->orderBy('name')->get();

        return view('campaigns.index', compact('campaigns', 'institutions'));
    }

    public function show(Campaign $campaign): View
    {
        $campaign->load([
            'institution',
            'promotion',
            'classroom',
            'product.variants',
            'creator',
            'participants.variant',
        ]);

        return view('campaigns.show', compact('campaign'));
    }

    public function join(Request $request, Campaign $campaign): RedirectResponse
    {
        if (! in_array($campaign->status, ['active', 'goal_reached'], true)) {
            return back()->with('error', 'Esta campaña ya cerró su etapa de reservas.');
        }

        $validated = $request->validate([
            'participant_name' => ['required', 'string', 'max:150'],
            'participant_email' => ['required', 'email', 'max:150'],
            'participant_phone' => ['required', 'string', 'max:30'],
            'customer_dni_ruc' => ['nullable', 'string', 'max:20'],
            'variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:30'],
            'custom_name' => ['nullable', 'string', 'max:80'],
            'custom_year' => ['nullable', 'string', 'max:20'],
            'payment_plan' => ['required', 'in:deposit,full'],
            'payment_method' => ['required', 'in:yape,plin,bank_transfer,card_culqi'],
            'reference_code' => ['nullable', 'string', 'max:80'],
        ]);

        $order = DB::transaction(function () use ($request, $campaign, $validated): Order {
            $variant = ProductVariant::where('product_id', $campaign->product_id)
                ->findOrFail($validated['variant_id']);

            $qty = (int) $validated['quantity'];
            $totalAmount = round((float) $campaign->unit_price * $qty, 2);
            $amountPaid = $validated['payment_plan'] === 'deposit'
                ? round($totalAmount * ($campaign->deposit_percent / 100), 2)
                : $totalAmount;

            $isCard = $validated['payment_method'] === 'card_culqi';
            $isPartial = $amountPaid < $totalAmount;

            $nextSeq = (int) Order::max('id') + 1;
            $orderNumber = sprintf('CAM-%s-%04d', now()->format('Y'), $nextSeq);

            $commission = round($totalAmount * ((float) $campaign->institution->commission_rate / 100), 2);

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $request->user()?->id,
                'institution_id' => $campaign->institution_id,
                'campaign_id' => $campaign->id,
                'customer_name' => $validated['participant_name'],
                'customer_email' => $validated['participant_email'],
                'customer_phone' => $validated['participant_phone'],
                'customer_dni_ruc' => $validated['customer_dni_ruc'] ?? null,
                'shipping_address' => $campaign->delivery_mode === 'consolidated_delegate'
                    ? 'Entrega consolidada vía Delegado de Promoción'
                    : 'Coordinación de envío',
                'city' => $campaign->institution->city,
                'department' => $campaign->institution->region,
                'courier_name' => $campaign->delivery_mode === 'consolidated_delegate' ? 'Consolidado Delegación' : 'Por definir',
                'subtotal' => $totalAmount,
                'discount_amount' => 0,
                'shipping_cost' => 0,
                'total' => $totalAmount,
                'institution_commission_amount' => $commission,
                'payment_status' => $isCard ? ($isPartial ? 'partial' : 'verified') : 'pending',
                'order_status' => $isCard ? 'confirmed' : 'pending',
                'notes' => "Reserva en campaña grupal: {$campaign->title}",
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $campaign->product_id,
                'variant_id' => $variant->id,
                'vendor_id' => $campaign->product->vendor_id,
                'product_name' => $campaign->product->name,
                'variant_label' => $variant->label(),
                'quantity' => $qty,
                'unit_price' => $campaign->unit_price,
                'unit_cost' => (float) ($campaign->product->purchase_price ?? 0),
                'total_price' => $totalAmount,
                'customization_data' => [
                    'campana' => $campaign->title,
                    'promocion' => ($validated['custom_year'] ?? null) ?: $campaign->promotion?->year,
                    'nombre_bordado' => $validated['custom_name'] ?? null,
                ],
            ]);

            Payment::create([
                'order_id' => $order->id,
                'method' => $validated['payment_method'],
                'amount' => $amountPaid,
                'is_partial_deposit' => $isPartial,
                'reference_code' => ($validated['reference_code'] ?? null) ?: ($isCard ? 'CULQI-CAM-'.$order->id : null),
                'status' => $isCard ? 'approved' : 'pending_review',
                'verified_at' => $isCard ? now() : null,
                'notes' => $isPartial ? "Adelanto del {$campaign->deposit_percent}% de campaña" : 'Pago completo de campaña',
            ]);

            CampaignParticipant::create([
                'campaign_id' => $campaign->id,
                'user_id' => $request->user()?->id,
                'variant_id' => $variant->id,
                'order_id' => $order->id,
                'participant_name' => $validated['participant_name'],
                'participant_phone' => $validated['participant_phone'],
                'quantity' => $qty,
                'custom_name' => $validated['custom_name'] ?? null,
                'custom_year' => ($validated['custom_year'] ?? null) ?: (string) ($campaign->promotion?->year ?? ''),
                'amount_paid' => $isCard ? $amountPaid : 0,
                'total_amount' => $totalAmount,
                'payment_status' => $isCard ? ($isPartial ? 'partial' : 'paid') : 'pending',
            ]);

            $campaign->increment('current_units', $qty);
            $campaign->refresh();

            if ($campaign->current_units >= $campaign->target_units && $campaign->status === 'active') {
                $campaign->update(['status' => 'goal_reached']);
            }

            if ($isCard && ! $isPartial) {
                InvoiceService::issueForOrder($order, 'boleta', $validated['participant_name']);
            }

            AuditLogger::log('campaign.joined', "{$validated['participant_name']} reservó {$qty} uds en {$campaign->title}", $campaign);

            return $order;
        });

        return redirect()->route('orders.confirmation', $order->order_number)
            ->with('success', "¡Reserva confirmada en la campaña {$campaign->title}!");
    }
}
