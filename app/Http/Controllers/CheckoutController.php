<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Institution;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\ShippingZone;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\AuditLogger;
use App\Services\CartService;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index(): View|RedirectResponse
    {
        $items = $this->cart->items();
        if ($items === []) {
            return redirect()->route('store.index')->with('error', 'Tu carrito está vacío. Agrega productos antes de continuar.');
        }

        $subtotal = $this->cart->subtotal();
        $discount = $this->cart->discountAmount();
        $coupon = $this->cart->coupon();
        $shippingZones = ShippingZone::where('is_active', true)->orderBy('rate')->get();

        return view('store.checkout', compact('items', 'subtotal', 'discount', 'coupon', 'shippingZones'));
    }

    public function store(Request $request): RedirectResponse
    {
        $items = $this->cart->items();
        if ($items === []) {
            return redirect()->route('store.index')->with('error', 'Tu carrito está vacío.');
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_email' => ['required', 'email', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_dni_ruc' => ['required', 'string', 'min:8', 'max:15'],
            'document_type' => ['required', 'in:boleta,factura'],
            'legal_name' => ['nullable', 'string', 'max:180'],
            'shipping_zone_id' => ['required', 'exists:shipping_zones,id'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'department' => ['required', 'string', 'max:100'],
            'payment_method' => ['required', 'in:yape,plin,bank_transfer,card_culqi,cash_on_delivery'],
            'reference_code' => ['nullable', 'string', 'max:80'],
            'voucher' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $voucherPath = null;
        if ($request->hasFile('voucher')) {
            $voucherPath = $request->file('voucher')->store('vouchers', 'public');
        }

        $order = DB::transaction(function () use ($request, $validated, $items, $voucherPath): Order {
            $shippingZone = ShippingZone::findOrFail($validated['shipping_zone_id']);
            $subtotal = $this->cart->subtotal();
            $discount = $this->cart->discountAmount();
            $coupon = $this->cart->coupon();
            $shippingCost = (float) $shippingZone->rate;
            $total = max(0, round($subtotal - $discount + $shippingCost, 2));

            $institutionId = collect($items)->pluck('institution_id')->filter()->first();
            $commissionAmount = 0.0;
            if ($institutionId && ($institution = Institution::find($institutionId))) {
                $commissionAmount = round($total * ((float) $institution->commission_rate / 100), 2);
            }

            $orderNumber = Order::generateUniqueOrderNumber('PED');

            $isCardAutoApproved = $validated['payment_method'] === 'card_culqi';

            $userId = $request->user()?->id;

            if (! $userId && $request->boolean('create_account')) {
                $normalizedEmail = strtolower(trim($validated['customer_email']));
                $existingUser = User::where('email', $normalizedEmail)->first();

                if (! $existingUser) {
                    $rawPassword = trim((string) $request->input('account_password'));
                    $passwordToHash = strlen($rawPassword) >= 8 ? $rawPassword : Str::random(16);

                    $newUser = User::create([
                        'name' => $validated['customer_name'],
                        'email' => $normalizedEmail,
                        'password' => Hash::make($passwordToHash),
                        'role' => UserRole::Buyer->value,
                        'institution_id' => $institutionId,
                        'phone' => $validated['customer_phone'],
                        'dni' => $validated['customer_dni_ruc'],
                        'city' => $validated['city'],
                        'referral_code' => strtoupper(Str::random(8)),
                        'is_active' => true,
                        'privacy_consent_at' => now(),
                    ]);

                    UserNotification::create([
                        'user_id' => $newUser->id,
                        'institution_id' => $institutionId,
                        'title' => '¡Bienvenido a MiPromo!',
                        'body' => "Tu cuenta quedó lista junto a tu pedido {$orderNumber}. Comparte tu código {$newUser->referral_code} con tu promoción.",
                        'channel' => 'web',
                        'action_url' => route('orders.confirmation', $orderNumber),
                    ]);

                    $userId = $newUser->id;
                    Auth::login($newUser);
                }
            }

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'institution_id' => $institutionId,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'customer_dni_ruc' => $validated['customer_dni_ruc'],
                'shipping_zone_id' => $shippingZone->id,
                'shipping_address' => $validated['shipping_address'],
                'city' => $validated['city'],
                'department' => $validated['department'],
                'courier_name' => $shippingZone->courier_name,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'institution_commission_amount' => $commissionAmount,
                'coupon_code' => $coupon?->code,
                'payment_status' => $isCardAutoApproved ? 'verified' : 'pending',
                'order_status' => $isCardAutoApproved ? 'confirmed' : 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'vendor_id' => $item['vendor_id'],
                    'product_name' => $item['product_name'],
                    'variant_label' => $item['variant_label'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'unit_cost' => $item['unit_cost'] ?? 0,
                    'total_price' => round($item['unit_price'] * $item['quantity'], 2),
                    'customization_data' => $item['customization'] ?: null,
                ]);

                if ($item['variant_id'] && ($variant = ProductVariant::find($item['variant_id']))) {
                    $variant->decrement('stock', min($variant->stock, (int) $item['quantity']));
                }
            }

            if ($coupon) {
                $coupon->increment('used_count');
            }

            Payment::create([
                'order_id' => $order->id,
                'method' => $validated['payment_method'],
                'amount' => $total,
                'reference_code' => ($validated['reference_code'] ?? null) ?: ($isCardAutoApproved ? 'CULQI-'.strtoupper(substr(md5($orderNumber), 0, 8)) : null),
                'voucher_path' => $voucherPath,
                'status' => $isCardAutoApproved ? 'approved' : 'pending_review',
                'verified_at' => $isCardAutoApproved ? now() : null,
                'notes' => $isCardAutoApproved ? 'Pago autorizado en línea.' : 'Pendiente de conciliación por administración.',
            ]);

            if ($isCardAutoApproved) {
                InvoiceService::issueForOrder($order, $validated['document_type'], ($validated['legal_name'] ?? null) ?: $validated['customer_name']);
            }

            AuditLogger::log('order.created', "Pedido {$order->order_number} generado por S/ {$total}", $order);

            return $order;
        });

        $this->cart->clear();

        return redirect()->route('orders.confirmation', $order->order_number)
            ->with('success', "¡Pedido {$order->order_number} registrado con éxito!");
    }

    public function confirmation(string $orderNumber): View
    {
        $order = Order::with(['items', 'payments', 'shippingZone', 'invoice', 'institution'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return view('store.order-confirmation', compact('order'));
    }

    public function track(Request $request): View
    {
        $orderNumber = trim((string) $request->string('codigo'));
        $order = $orderNumber !== ''
            ? Order::with(['items', 'payments', 'shippingZone', 'invoice', 'institution'])
                ->where('order_number', $orderNumber)
                ->orWhere('tracking_code', $orderNumber)
                ->first()
            : null;

        return view('store.track', compact('order', 'orderNumber'));
    }

    public function invoice(string $orderNumber): View
    {
        $order = Order::with(['items', 'invoice', 'institution'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $invoice = $order->invoice ?? InvoiceService::issueForOrder($order, strlen((string) $order->customer_dni_ruc) === 11 ? 'factura' : 'boleta');

        return view('store.invoice', compact('order', 'invoice'));
    }

    public function storeReturn(Request $request, string $orderNumber): RedirectResponse
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        $validated = $request->validate([
            'request_type' => ['required', 'in:cambio_talla,devolucion,garantia'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        ReturnRequest::create([
            'order_id' => $order->id,
            'user_id' => $request->user()?->id,
            'request_type' => $validated['request_type'],
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        AuditLogger::log('order.return_requested', "Solicitud {$validated['request_type']} en {$order->order_number}", $order);

        return back()->with('success', 'Solicitud de cambio/devolución registrada. Nuestro equipo responderá pronto.');
    }
}
