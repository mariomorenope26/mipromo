<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniProfile;
use App\Models\AuditLog;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Community;
use App\Models\CommunityCollection;
use App\Models\CommunityPoll;
use App\Models\CommunityPost;
use App\Models\Complaint;
use App\Models\ContentReport;
use App\Models\Institution;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\ShippingZone;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSettlement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->filled('desde') ? Carbon::parse($request->string('desde')->toString())->startOfDay() : null;
        $to = $request->filled('hasta') ? Carbon::parse($request->string('hasta')->toString())->endOfDay() : null;
        $region = trim((string) $request->string('region'));

        $data = $this->buildReportPayload($from, $to, $region);

        return view('admin.reports.index', [
            ...$data,
            'from' => $from?->format('Y-m-d'),
            'to' => $to?->format('Y-m-d'),
            'region' => $region,
        ]);
    }

    public function export(Request $request, string $report): StreamedResponse
    {
        $from = $request->filled('desde') ? Carbon::parse($request->string('desde')->toString())->startOfDay() : null;
        $to = $request->filled('hasta') ? Carbon::parse($request->string('hasta')->toString())->endOfDay() : null;
        $region = trim((string) $request->string('region'));

        $data = $this->buildReportPayload($from, $to, $region);

        return response()->streamDownload(function () use ($report, $data): void {
            $handle = fopen('php://output', 'w');

            match ($report) {
                'r2-rentabilidad' => (function () use ($handle, $data): void {
                    fputcsv($handle, ['Producto', 'Proveedor', 'Unidades', 'Precio Compra Prom S/', 'Precio Venta Prom S/', 'Costo Total S/', 'Ingreso Total S/', 'Utilidad Bruta S/', 'Margen %']);
                    foreach ($data['productMargins'] as $row) {
                        fputcsv($handle, [
                            $row['product_name'],
                            $row['vendor_name'],
                            $row['units'],
                            number_format($row['avg_cost'], 2, '.', ''),
                            number_format($row['avg_sale'], 2, '.', ''),
                            number_format($row['total_cost'], 2, '.', ''),
                            number_format($row['total_revenue'], 2, '.', ''),
                            number_format($row['gross_profit'], 2, '.', ''),
                            $row['margin_percent'],
                        ]);
                    }
                })(),
                'r3-instituciones' => (function () use ($handle, $data): void {
                    fputcsv($handle, ['Institucion', 'Ciudad', 'Region', 'Publicado', 'Pedidos', 'Ventas S/', 'Comision %', 'Aporte Generado S/', 'Egresados Verificados']);
                    foreach ($data['institutionPerformance'] as $row) {
                        fputcsv($handle, [
                            $row['name'],
                            $row['city'],
                            $row['region'],
                            $row['is_published'] ? 'SI' : 'NO',
                            $row['orders_count'],
                            number_format($row['sales_total'], 2, '.', ''),
                            $row['commission_rate'],
                            number_format($row['commission_amount'], 2, '.', ''),
                            $row['verified_alumni'],
                        ]);
                    }
                })(),
                'r5-proveedores' => (function () use ($handle, $data): void {
                    fputcsv($handle, ['Proveedor', 'RUC', 'Verificado', 'Productos', 'Unidades Vendidas', 'Costo Compra S/', 'Ventas Brutas S/', 'Comision Plataforma S/', 'Neto Pagado S/']);
                    foreach ($data['vendorPerformance'] as $row) {
                        fputcsv($handle, [
                            $row['business_name'],
                            $row['ruc'],
                            $row['is_verified'] ? 'SI' : 'NO',
                            $row['products_count'],
                            $row['units'],
                            number_format($row['purchase_cost'], 2, '.', ''),
                            number_format($row['gross_sales'], 2, '.', ''),
                            number_format($row['commission'], 2, '.', ''),
                            number_format($row['paid_settlements'], 2, '.', ''),
                        ]);
                    }
                })(),
                'r6-inventario' => (function () use ($handle, $data): void {
                    fputcsv($handle, ['SKU', 'Producto', 'Proveedor', 'Variante', 'Stock', 'Minimo Alerta', 'Costo Unit S/', 'Venta Unit S/', 'Valorizado Costo S/']);
                    foreach ($data['criticalStockVariants'] as $v) {
                        fputcsv($handle, [
                            $v->sku,
                            $v->product?->name,
                            $v->product?->vendor?->business_name ?? 'Directo',
                            $v->label(),
                            $v->stock,
                            $v->low_stock_threshold,
                            $v->product?->purchase_price ?? 0,
                            $v->product?->base_price ?? 0,
                            number_format(((float) ($v->product?->purchase_price ?? 0)) * $v->stock, 2, '.', ''),
                        ]);
                    }
                })(),
                default => (function () use ($handle, $data): void {
                    fputcsv($handle, ['Indicador Gerencial', 'Valor']);
                    fputcsv($handle, ['Avance Global del Proposito %', $data['purposeProgress']['overall_score'].'%']);
                    fputcsv($handle, ['Colegios Publicados', $data['purposeProgress']['published_institutions'].' / '.$data['purposeProgress']['total_institutions']]);
                    fputcsv($handle, ['Egresados Verificados', $data['purposeProgress']['verified_alumni']]);
                    fputcsv($handle, ['Campanas Activas o Cumplidas', $data['purposeProgress']['campaigns_active_or_goal']]);
                    fputcsv($handle, ['Ventas Totales S/', number_format($data['financialSummary']['gross_revenue'], 2, '.', '')]);
                    fputcsv($handle, ['Costo de Compra Total S/', number_format($data['financialSummary']['total_cost'], 2, '.', '')]);
                    fputcsv($handle, ['Utilidad Bruta S/', number_format($data['financialSummary']['gross_profit'], 2, '.', '')]);
                    fputcsv($handle, ['Margen Bruto %', $data['financialSummary']['margin_percent'].'%']);
                })(),
            };

            fclose($handle);
        }, "reporte-gerencial-{$report}-mipromo.csv");
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReportPayload(?Carbon $from, ?Carbon $to, string $region): array
    {
        $institutionsQuery = Institution::query()
            ->withCount(['activeDelegates', 'campaigns', 'communities'])
            ->when($region !== '', fn ($q) => $q->where('region', $region));

        $institutions = $institutionsQuery->orderBy('city')->orderBy('name')->get();
        $institutionIds = $institutions->pluck('id')->all();

        $ordersQuery = Order::query()
            ->with(['institution', 'shippingZone', 'payments', 'invoice', 'items.product.vendor'])
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->when($region !== '', fn ($q) => $q->whereIn('institution_id', $institutionIds));

        $orders = $ordersQuery->get();
        $validOrders = $orders->where('order_status', '!=', 'cancelled');
        $validOrderIds = $validOrders->pluck('id')->all();

        // 1. Avance del propósito del sistema
        $publishedCount = $institutions->where('is_published', true)->count();
        $totalInstCount = max(1, $institutions->count());
        $withDelegatesCount = $institutions->where('active_delegates_count', '>', 0)->count();
        $verifiedAlumniCount = AlumniProfile::where('verification_status', 'verified')
            ->when($region !== '', fn ($q) => $q->whereIn('institution_id', $institutionIds))
            ->count();
        $pendingAlumniCount = AlumniProfile::where('verification_status', 'pending')
            ->when($region !== '', fn ($q) => $q->whereIn('institution_id', $institutionIds))
            ->count();

        $allCampaigns = Campaign::with(['institution', 'product', 'participants'])
            ->when($region !== '', fn ($q) => $q->whereIn('institution_id', $institutionIds))
            ->get();
        $campaignsGoalOrProd = $allCampaigns->whereIn('status', ['goal_reached', 'in_production', 'closed'])->count();
        $campaignsTotal = max(1, $allCampaigns->count());
        $verifiedVendorsCount = Vendor::where('is_active', true)->where('is_verified', true)->count();
        $totalVendorsCount = max(1, Vendor::where('is_active', true)->count());

        $milestones = [
            ['label' => 'Colegios publicados en web principal', 'current' => $publishedCount, 'target' => $institutions->count(), 'percent' => round(($publishedCount / $totalInstCount) * 100, 1)],
            ['label' => 'Colegios con delegado acreditado', 'current' => $withDelegatesCount, 'target' => $institutions->count(), 'percent' => round(($withDelegatesCount / $totalInstCount) * 100, 1)],
            ['label' => 'Proveedores homologados (Verificados)', 'current' => $verifiedVendorsCount, 'target' => Vendor::where('is_active', true)->count(), 'percent' => round(($verifiedVendorsCount / $totalVendorsCount) * 100, 1)],
            ['label' => 'Campañas grupales con meta cumplida / prod.', 'current' => $campaignsGoalOrProd, 'target' => $allCampaigns->count(), 'percent' => round(($campaignsGoalOrProd / $campaignsTotal) * 100, 1)],
            ['label' => 'Egresados validados en padrón', 'current' => $verifiedAlumniCount, 'target' => max(1, $verifiedAlumniCount + $pendingAlumniCount), 'percent' => round(($verifiedAlumniCount / max(1, $verifiedAlumniCount + $pendingAlumniCount)) * 100, 1)],
        ];

        $overallScore = round(collect($milestones)->avg('percent'), 1);

        $regionalCoverage = $institutions->groupBy('region')->map(function ($group, $reg) {
            return [
                'region' => $reg,
                'total' => $group->count(),
                'published' => $group->where('is_published', true)->count(),
                'avg_years' => round($group->avg(fn (Institution $i) => $i->yearsOfHistory()), 0),
                'delegates' => $group->sum('active_delegates_count'),
            ];
        })->values();

        $purposeProgress = [
            'overall_score' => $overallScore,
            'published_institutions' => $publishedCount,
            'total_institutions' => $institutions->count(),
            'verified_alumni' => $verifiedAlumniCount,
            'pending_alumni' => $pendingAlumniCount,
            'campaigns_active_or_goal' => $allCampaigns->whereIn('status', ['active', 'goal_reached', 'in_production'])->count(),
            'verified_vendors' => $verifiedVendorsCount,
            'milestones' => $milestones,
        ];

        // 2. Rentabilidad: Precio de Compra vs Venta
        $orderItems = OrderItem::with(['product.vendor', 'vendor', 'order'])
            ->whereIn('order_id', $validOrderIds)
            ->get();

        $productMargins = $orderItems->groupBy('product_id')->map(function ($items) {
            $first = $items->first();
            $product = $first->product;
            $units = (int) $items->sum('quantity');
            $revenue = (float) $items->sum('total_price');
            $cost = (float) $items->sum(function (OrderItem $item) {
                $unitCost = (float) $item->unit_cost > 0 ? (float) $item->unit_cost : (float) ($item->product?->purchase_price ?? 0);

                return $unitCost * $item->quantity;
            });
            $profit = round($revenue - $cost, 2);

            return [
                'product_id' => $first->product_id,
                'product_name' => $product?->name ?? $first->product_name,
                'vendor_name' => $first->vendor?->business_name ?? $product?->vendor?->business_name ?? 'Producción Directa JMSPERU',
                'is_verified' => (bool) ($product?->vendor?->is_verified ?? false),
                'units' => $units,
                'avg_cost' => $units > 0 ? round($cost / $units, 2) : (float) ($product?->purchase_price ?? 0),
                'avg_sale' => $units > 0 ? round($revenue / $units, 2) : (float) ($product?->base_price ?? 0),
                'total_cost' => round($cost, 2),
                'total_revenue' => round($revenue, 2),
                'gross_profit' => $profit,
                'margin_percent' => $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0.0,
            ];
        })->sortByDesc('gross_profit')->values();

        $itemsRevenue = (float) $productMargins->sum('total_revenue');
        $itemsCost = (float) $productMargins->sum('total_cost');
        $grossRevenue = (float) $validOrders->sum('total');
        $grossProfit = round($itemsRevenue - $itemsCost, 2);
        $catalogProducts = Product::with('vendor')->where('is_active', true)->get();
        $productsMissingCost = $catalogProducts->where('purchase_price', '<=', 0)->count();

        $financialSummary = [
            'gross_revenue' => $grossRevenue,
            'items_revenue' => $itemsRevenue,
            'total_cost' => $itemsCost,
            'gross_profit' => $grossProfit,
            'margin_percent' => $itemsRevenue > 0 ? round(($grossProfit / $itemsRevenue) * 100, 1) : 0.0,
            'avg_ticket' => $validOrders->count() > 0 ? round($grossRevenue / $validOrders->count(), 2) : 0.0,
            'discounts_total' => (float) $validOrders->sum('discount_amount'),
            'shipping_total' => (float) $validOrders->sum('shipping_cost'),
            'commissions_institutions' => (float) $validOrders->sum('institution_commission_amount'),
            'products_missing_cost' => $productsMissingCost,
        ];

        // 3. Desempeño por Institución y Aporte Social
        $verifiedAlumniByInst = AlumniProfile::where('verification_status', 'verified')
            ->selectRaw('institution_id, COUNT(*) as total')
            ->groupBy('institution_id')
            ->pluck('total', 'institution_id');

        $institutionPerformance = $institutions->map(function (Institution $inst) use ($validOrders, $verifiedAlumniByInst) {
            $instOrders = $validOrders->where('institution_id', $inst->id);

            return [
                'name' => $inst->short_name,
                'full_name' => $inst->name,
                'city' => $inst->city,
                'region' => $inst->region,
                'is_published' => $inst->is_published,
                'orders_count' => $instOrders->count(),
                'sales_total' => round((float) $instOrders->sum('total'), 2),
                'commission_rate' => (float) $inst->commission_rate,
                'commission_amount' => round((float) $instOrders->sum('institution_commission_amount'), 2),
                'verified_alumni' => (int) ($verifiedAlumniByInst[$inst->id] ?? 0),
                'campaigns_count' => $inst->campaigns_count,
            ];
        })->sortByDesc('sales_total')->values();

        // 4. Campañas Grupales y Metas
        $campaignPerformance = $allCampaigns->map(function (Campaign $c) {
            $committed = round((float) $c->participants->sum('total_amount'), 2);
            $paid = round((float) $c->participants->sum('amount_paid'), 2);

            return [
                'title' => $c->title,
                'institution' => $c->institution?->short_name,
                'status' => $c->statusLabel(),
                'raw_status' => $c->status,
                'current_units' => $c->current_units,
                'target_units' => $c->target_units,
                'progress' => $c->progressPercent(),
                'committed' => $committed,
                'paid' => $paid,
                'pending_balance' => round(max(0, $committed - $paid), 2),
            ];
        })->sortByDesc('progress')->values();

        // 5. Ranking de Proveedores, Verificación y Liquidaciones
        $settlementsAll = VendorSettlement::all();
        $vendorPerformance = Vendor::withCount('products')->get()->map(function (Vendor $v) use ($orderItems, $settlementsAll) {
            $vItems = $orderItems->where('vendor_id', $v->id);
            $gross = round((float) $vItems->sum('total_price'), 2);
            $cost = round((float) $vItems->sum(fn (OrderItem $i) => ((float) $i->unit_cost > 0 ? (float) $i->unit_cost : (float) ($i->product?->purchase_price ?? 0)) * $i->quantity), 2);
            $commission = round($gross * ((float) $v->commission_rate / 100), 2);
            $paidSettlements = round((float) $settlementsAll->where('vendor_id', $v->id)->where('status', 'paid')->sum('net_payout'), 2);
            $pendingSettlements = round((float) $settlementsAll->where('vendor_id', $v->id)->where('status', 'pending')->sum('net_payout'), 2);

            return [
                'id' => $v->id,
                'business_name' => $v->business_name,
                'ruc' => $v->ruc,
                'city' => $v->city,
                'is_verified' => $v->is_verified,
                'is_active' => $v->is_active,
                'products_count' => $v->products_count,
                'units' => (int) $vItems->sum('quantity'),
                'purchase_cost' => $cost,
                'gross_sales' => $gross,
                'commission' => $commission,
                'paid_settlements' => $paidSettlements,
                'pending_settlements' => $pendingSettlements,
            ];
        })->sortByDesc('gross_sales')->values();

        // 6. Salud del Inventario y Valorización
        $allVariants = ProductVariant::with('product.vendor', 'product.category')->get();
        $inventorySummary = [
            'total_skus' => $allVariants->count(),
            'total_units' => (int) $allVariants->sum('stock'),
            'valuation_cost' => round((float) $allVariants->sum(fn (ProductVariant $v) => ((float) ($v->product?->purchase_price ?? 0)) * $v->stock), 2),
            'valuation_sale' => round((float) $allVariants->sum(fn (ProductVariant $v) => ((float) ($v->product?->base_price ?? 0)) * $v->stock), 2),
            'out_of_stock' => $allVariants->where('stock', '<=', 0)->count(),
            'low_stock_count' => $allVariants->filter(fn (ProductVariant $v) => $v->isLowStock())->count(),
        ];

        $criticalStockVariants = $allVariants
            ->filter(fn (ProductVariant $v) => $v->isLowStock())
            ->sortBy('stock')
            ->take(15)
            ->values();

        $stockByCategory = Category::with('products.variants')->orderBy('sort_order')->get()->map(fn (Category $cat) => [
            'name' => $cat->name,
            'products' => $cat->products->count(),
            'units' => (int) $cat->products->sum(fn (Product $p) => $p->totalStock()),
            'valuation_cost' => round((float) $cat->products->sum(fn (Product $p) => (float) $p->purchase_price * $p->totalStock()), 2),
        ]);

        // 7. Pagos y Facturación SUNAT
        $payments = Payment::whereIn('order_id', $orders->pluck('id'))->get();
        $paymentsByMethod = $payments->groupBy('method')->map(fn ($group, $method) => [
            'method' => $group->first()->methodLabel(),
            'count' => $group->count(),
            'approved_amount' => round((float) $group->where('status', 'approved')->sum('amount'), 2),
            'pending_amount' => round((float) $group->where('status', 'pending_review')->sum('amount'), 2),
        ])->values();

        $invoices = Invoice::whereIn('order_id', $orders->pluck('id'))->get();
        $sunatSummary = [
            'boletas_count' => $invoices->where('document_type', 'boleta')->count(),
            'facturas_count' => $invoices->where('document_type', 'factura')->count(),
            'credit_notes_count' => $invoices->where('document_type', 'nota_credito')->count(),
            'subtotal_gravado' => round((float) $invoices->where('document_type', '!=', 'nota_credito')->sum('subtotal_gravado'), 2),
            'igv_total' => round((float) $invoices->where('document_type', '!=', 'nota_credito')->sum('igv'), 2),
            'invoiced_total' => round((float) $invoices->where('document_type', '!=', 'nota_credito')->sum('total'), 2),
        ];

        // 8. Logística, Envíos y Devoluciones
        $logisticsByStatus = $orders->groupBy('order_status')->map(fn ($group, $status) => [
            'status' => $group->first()->orderStatusLabel(),
            'raw' => $status,
            'count' => $group->count(),
            'amount' => round((float) $group->sum('total'), 2),
        ])->values();

        $shippingZonesReport = ShippingZone::withCount(['orders' => fn ($q) => $q->whereIn('id', $validOrderIds)])->get()->map(function (ShippingZone $zone) use ($validOrders) {
            $zOrders = $validOrders->where('shipping_zone_id', $zone->id);

            return [
                'name' => $zone->name,
                'courier' => $zone->courier_name,
                'orders_count' => $zOrders->count(),
                'shipping_revenue' => round((float) $zOrders->sum('shipping_cost'), 2),
            ];
        });

        $returnsSummary = [
            'total' => ReturnRequest::count(),
            'pending' => ReturnRequest::where('status', 'pending')->count(),
            'approved' => ReturnRequest::whereIn('status', ['approved', 'completed'])->count(),
            'rejected' => ReturnRequest::where('status', 'rejected')->count(),
        ];

        // 9. Comunidades, Colectas Solidarias y Referidos
        $collections = CommunityCollection::all();
        $engagementSummary = [
            'total_users' => User::count(),
            'referrals_count' => User::whereNotNull('referred_by_user_id')->count(),
            'communities_count' => Community::count(),
            'posts_count' => CommunityPost::count(),
            'polls_count' => CommunityPoll::count(),
            'collections_count' => $collections->count(),
            'collections_goal' => round((float) $collections->sum('goal_amount'), 2),
            'collections_raised' => round((float) $collections->sum('current_amount'), 2),
        ];

        // 10. Cumplimiento Legal, Calidad y Auditoría
        $complianceSummary = [
            'complaints_total' => Complaint::count(),
            'complaints_pending' => Complaint::where('status', 'pending')->count(),
            'complaints_resolved' => Complaint::where('status', 'resolved')->count(),
            'reports_open' => ContentReport::where('status', 'open')->count(),
            'reports_resolved' => ContentReport::where('status', '!=', 'open')->count(),
            'reviews_count' => ProductReview::count(),
            'avg_rating' => round((float) (ProductReview::avg('rating') ?? 5.0), 2),
            'recent_logs' => AuditLog::with('user')->latest()->take(10)->get(),
        ];

        $availableRegions = Institution::distinct()->pluck('region')->sort()->values();

        return compact(
            'availableRegions',
            'purposeProgress',
            'regionalCoverage',
            'financialSummary',
            'productMargins',
            'institutionPerformance',
            'campaignPerformance',
            'vendorPerformance',
            'inventorySummary',
            'criticalStockVariants',
            'stockByCategory',
            'paymentsByMethod',
            'sunatSummary',
            'logisticsByStatus',
            'shippingZonesReport',
            'returnsSummary',
            'engagementSummary',
            'complianceSummary'
        );
    }
}
