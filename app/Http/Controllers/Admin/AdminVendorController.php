<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSettlement;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminVendorController extends Controller
{
    public function index(Request $request): View
    {
        $query = Vendor::query()
            ->with(['user', 'verifier', 'settlements'])
            ->withCount([
                'products',
                'products as verified_badge_products_count' => fn ($q) => $q->where('show_verified_vendor', true),
            ])
            ->withSum(
                ['orderItems as gross_sales' => fn ($q) => $q->whereHas('order', fn ($oq) => $oq->where('order_status', '!=', 'cancelled'))],
                'total_price'
            )
            ->withSum(
                ['orderItems as total_units_sold' => fn ($q) => $q->whereHas('order', fn ($oq) => $oq->where('order_status', '!=', 'cancelled'))],
                'quantity'
            );

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($q) use ($search): void {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhere('trade_name', 'like', "%{$search}%")
                    ->orWhere('ruc', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('category_specialty', 'like', "%{$search}%");
            });
        }

        if ($request->filled('verificado')) {
            $query->where('is_verified', $request->string('verificado')->toString() === '1');
        }

        if ($request->filled('estado')) {
            $query->where('is_active', $request->string('estado')->toString() === 'active');
        }

        $vendors = $query->orderByDesc('is_verified')->orderByDesc('is_active')->orderBy('business_name')->get();

        // Calcular costos de compra reales en órdenes por proveedor
        $costByVendor = OrderItem::query()
            ->select('vendor_id', DB::raw('SUM(unit_cost * quantity) as purchase_cost_sum'))
            ->whereNotNull('vendor_id')
            ->whereHas('order', fn ($oq) => $oq->where('order_status', '!=', 'cancelled'))
            ->groupBy('vendor_id')
            ->pluck('purchase_cost_sum', 'vendor_id');

        $allVendors = Vendor::with('settlements')->get();
        $summary = [
            'total' => $allVendors->count(),
            'verified' => $allVendors->where('is_verified', true)->count(),
            'unverified' => $allVendors->where('is_verified', false)->count(),
            'active' => $allVendors->where('is_active', true)->count(),
            'gross_sales' => (float) $vendors->sum(fn (Vendor $v) => (float) ($v->gross_sales ?? 0)),
            'purchase_cost' => (float) $costByVendor->sum(),
            'paid_settlements' => (float) VendorSettlement::where('status', 'paid')->sum('net_payout'),
            'pending_settlements' => (float) VendorSettlement::where('status', 'pending')->sum('net_payout'),
        ];

        $settlements = VendorSettlement::with('vendor')->latest()->take(25)->get();

        return view('admin.vendors.index', compact('vendors', 'costByVendor', 'summary', 'settlements'));
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('owner_email')) {
            $request->merge([
                'owner_email' => strtolower(trim((string) $request->input('owner_email'))),
            ]);
        }

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:180'],
            'trade_name' => ['nullable', 'string', 'max:180'],
            'ruc' => ['required', 'string', 'min:11', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:150'],
            'category_specialty' => ['nullable', 'string', 'max:120'],
            'payment_terms' => ['nullable', 'string', 'max:80'],
            'bank_name' => ['nullable', 'string', 'max:80'],
            'bank_account' => ['nullable', 'string', 'max:80'],
            'bank_cci' => ['nullable', 'string', 'max:80'],
            'lead_time_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'commission_rate' => ['required', 'numeric', 'min:1', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'internal_notes' => ['nullable', 'string', 'max:1000'],
            'owner_email' => ['nullable', 'email', 'unique:users,email'],
            'owner_password' => ['nullable', 'string', Password::min(8)->letters()->numbers()],
        ]);

        $userId = null;
        if (! empty($validated['owner_email'])) {
            $ownerPassword = ! empty($validated['owner_password'])
                ? $validated['owner_password']
                : Str::random(16);

            $user = User::create([
                'name' => ($validated['contact_name'] ?? null) ?: $validated['business_name'],
                'email' => $validated['owner_email'],
                'password' => Hash::make($ownerPassword),
                'role' => UserRole::Vendor->value,
                'phone' => $validated['phone'],
                'city' => $validated['city'],
                'referral_code' => strtoupper(Str::random(8)),
                'is_active' => true,
                'privacy_consent_at' => now(),
            ]);
            $userId = $user->id;
        }

        $isVerified = $request->boolean('is_verified', true);
        $slug = Str::slug($validated['business_name']);

        $vendor = Vendor::create([
            'user_id' => $userId,
            'business_name' => $validated['business_name'],
            'trade_name' => $validated['trade_name'] ?? null,
            'ruc' => $validated['ruc'],
            'slug' => Vendor::where('slug', $slug)->exists() ? $slug.'-'.strtolower(Str::random(3)) : $slug,
            'city' => $validated['city'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'],
            'contact_name' => $validated['contact_name'] ?? null,
            'email' => $validated['email'] ?? ($validated['owner_email'] ?? null),
            'category_specialty' => $validated['category_specialty'] ?? null,
            'payment_terms' => $validated['payment_terms'] ?? '50% adelanto / 50% contraentrega',
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account' => $validated['bank_account'] ?? null,
            'bank_cci' => $validated['bank_cci'] ?? null,
            'lead_time_days' => $validated['lead_time_days'] ?? 7,
            'rating' => 5.00,
            'commission_rate' => $validated['commission_rate'],
            'description' => $validated['description'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'is_active' => true,
            'is_verified' => $isVerified,
            'verified_at' => $isVerified ? now() : null,
            'verified_by' => $isVerified ? $request->user()->id : null,
        ]);

        AuditLogger::log(
            'vendor.created',
            "Proveedor registrado: {$vendor->business_name} (RUC {$vendor->ruc}".($isVerified ? ', Verificado' : '').')',
            $vendor
        );

        return back()->with('success', "Proveedor {$vendor->business_name} registrado correctamente en el área de proveedores.");
    }

    public function update(Request $request, Vendor $vendor): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:180'],
            'trade_name' => ['nullable', 'string', 'max:180'],
            'ruc' => ['required', 'string', 'min:11', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:150'],
            'category_specialty' => ['nullable', 'string', 'max:120'],
            'payment_terms' => ['nullable', 'string', 'max:80'],
            'bank_name' => ['nullable', 'string', 'max:80'],
            'bank_account' => ['nullable', 'string', 'max:80'],
            'bank_cci' => ['nullable', 'string', 'max:80'],
            'lead_time_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'rating' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'commission_rate' => ['required', 'numeric', 'min:1', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'internal_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $isVerified = $request->boolean('is_verified');
        $vendor->update([
            ...$validated,
            'is_verified' => $isVerified,
            'verified_at' => $isVerified ? ($vendor->verified_at ?? now()) : null,
            'verified_by' => $isVerified ? ($vendor->verified_by ?? $request->user()->id) : null,
        ]);

        AuditLogger::log('vendor.updated', "Proveedor {$vendor->business_name} actualizado", $vendor);

        return back()->with('success', "Ficha de proveedor {$vendor->business_name} actualizada.");
    }

    public function toggleVerified(Request $request, Vendor $vendor): RedirectResponse
    {
        $newState = ! $vendor->is_verified;
        $vendor->update([
            'is_verified' => $newState,
            'verified_at' => $newState ? now() : null,
            'verified_by' => $newState ? $request->user()->id : null,
        ]);

        AuditLogger::log(
            'vendor.verified_toggled',
            "Proveedor {$vendor->business_name} ".($newState ? 'aprobado como Proveedor Verificado' : 'retirado de Proveedor Verificado'),
            $vendor
        );

        return back()->with(
            'success',
            $newState
                ? "{$vendor->business_name} ahora cuenta con sello de Proveedor Verificado para tienda cliente."
                : "Se retiró el sello de Proveedor Verificado a {$vendor->business_name}."
        );
    }

    public function toggleStatus(Vendor $vendor): RedirectResponse
    {
        $vendor->update(['is_active' => ! $vendor->is_active]);
        AuditLogger::log('vendor.toggled', "Proveedor {$vendor->business_name} ".($vendor->is_active ? 'activado' : 'suspendido'), $vendor);

        return back()->with('success', "Estado operativo de {$vendor->business_name} actualizado.");
    }

    public function destroy(Vendor $vendor): RedirectResponse
    {
        if ($vendor->products()->exists() || $vendor->orderItems()->exists() || $vendor->settlements()->exists()) {
            $vendor->update(['is_active' => false, 'is_verified' => false]);
            AuditLogger::log('vendor.archived', "Proveedor {$vendor->business_name} suspendido por poseer historial", $vendor);

            return back()->with('success', "El proveedor {$vendor->business_name} tiene productos o historial comercial, por lo que fue suspendido para preservar trazabilidad.");
        }

        $name = $vendor->business_name;
        $vendor->delete();
        AuditLogger::log('vendor.deleted', "Proveedor eliminado: {$name}");

        return back()->with('success', "Proveedor {$name} eliminado.");
    }

    public function createSettlement(Request $request, Vendor $vendor): RedirectResponse
    {
        $validated = $request->validate([
            'period_label' => ['required', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:400'],
        ]);

        $gross = (float) $vendor->orderItems()
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'verified'))
            ->sum('total_price');

        $commission = round($gross * ((float) $vendor->commission_rate / 100), 2);
        $net = round(max(0, $gross - $commission), 2);

        $settlement = VendorSettlement::create([
            'vendor_id' => $vendor->id,
            'period_label' => $validated['period_label'],
            'gross_sales' => $gross,
            'platform_commission' => $commission,
            'net_payout' => $net,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLogger::log('vendor.settlement_created', "Liquidación {$settlement->period_label} generada para {$vendor->business_name}: Neto S/ {$net}", $settlement);

        return back()->with('success', "Liquidación generada para {$vendor->business_name} por S/ ".number_format($net, 2));
    }

    public function markSettlementPaid(Request $request, VendorSettlement $settlement): RedirectResponse
    {
        $validated = $request->validate([
            'reference_code' => ['required', 'string', 'max:80'],
        ]);

        $settlement->update([
            'status' => 'paid',
            'paid_at' => now(),
            'reference_code' => $validated['reference_code'],
        ]);

        AuditLogger::log('vendor.settlement_paid', "Liquidación #{$settlement->id} pagada ({$validated['reference_code']})", $settlement);

        return back()->with('success', "Liquidación marcada como pagada ({$validated['reference_code']}).");
    }

    public function exportCsv(): StreamedResponse
    {
        $vendors = Vendor::withCount('products')
            ->withSum(['orderItems as gross_sales' => fn ($q) => $q->whereHas('order', fn ($oq) => $oq->where('order_status', '!=', 'cancelled'))], 'total_price')
            ->orderBy('business_name')
            ->get();

        return response()->streamDownload(function () use ($vendors): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Razon Social', 'Nombre Comercial', 'RUC', 'Verificado', 'Estado', 'Ciudad', 'Contacto', 'Telefono', 'Email', 'Especialidad', 'Banco', 'CCI', 'Comision %', 'Productos', 'Ventas Brutas S/']);

            foreach ($vendors as $vendor) {
                fputcsv($handle, [
                    $vendor->business_name,
                    $vendor->trade_name,
                    $vendor->ruc,
                    $vendor->is_verified ? 'SI' : 'NO',
                    $vendor->is_active ? 'Activo' : 'Suspendido',
                    $vendor->city,
                    $vendor->contact_name,
                    $vendor->phone,
                    $vendor->email,
                    $vendor->category_specialty,
                    $vendor->bank_name,
                    $vendor->bank_cci,
                    $vendor->commission_rate,
                    $vendor->products_count,
                    number_format((float) ($vendor->gross_sales ?? 0), 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, 'directorio-proveedores-mipromo.csv');
    }
}
