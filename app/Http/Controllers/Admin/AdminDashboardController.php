<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniProfile;
use App\Models\AuditLog;
use App\Models\Campaign;
use App\Models\Complaint;
use App\Models\ContentReport;
use App\Models\Institution;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $scopedInstitutionId = $user->isRole('institution_admin', 'promotion_delegate', 'classroom_delegate')
            ? $user->institution_id
            : null;

        $ordersQuery = Order::query()
            ->when($scopedInstitutionId, fn ($q) => $q->where('institution_id', $scopedInstitutionId))
            ->when($user->isRole('vendor') && $user->vendor, fn ($q) => $q->whereHas('items', fn ($iq) => $iq->where('vendor_id', $user->vendor->id)));

        $validOrderIds = (clone $ordersQuery)->where('order_status', '!=', 'cancelled')->pluck('id')->all();

        $totalSales = (float) (clone $ordersQuery)->where('order_status', '!=', 'cancelled')->sum('total');
        $ordersCount = (clone $ordersQuery)->count();

        $scopedItems = OrderItem::with('product')->whereIn('order_id', $validOrderIds)->get();
        $totalItemsCost = (float) $scopedItems->sum(fn (OrderItem $item) => ((float) $item->unit_cost > 0 ? (float) $item->unit_cost : (float) ($item->product?->purchase_price ?? 0)) * $item->quantity);
        $totalItemsRevenue = (float) $scopedItems->sum('total_price');
        $estimatedProfit = round(max(0, $totalItemsRevenue - $totalItemsCost), 2);

        $pendingPayments = Payment::where('status', 'pending_review')
            ->when($scopedInstitutionId, fn ($q) => $q->whereHas('order', fn ($oq) => $oq->where('institution_id', $scopedInstitutionId)))
            ->count();

        $alumniPending = AlumniProfile::where('verification_status', 'pending')
            ->when($scopedInstitutionId, fn ($q) => $q->where('institution_id', $scopedInstitutionId))
            ->count();

        $alumniVerified = AlumniProfile::where('verification_status', 'verified')
            ->when($scopedInstitutionId, fn ($q) => $q->where('institution_id', $scopedInstitutionId))
            ->count();

        $activeCampaigns = Campaign::whereIn('status', ['active', 'goal_reached', 'in_production'])
            ->when($scopedInstitutionId, fn ($q) => $q->where('institution_id', $scopedInstitutionId))
            ->count();

        $lowStockVariants = ProductVariant::with('product')
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->when($scopedInstitutionId, fn ($q) => $q->whereHas('product', fn ($pq) => $pq->where('institution_id', $scopedInstitutionId)))
            ->when($user->isRole('vendor') && $user->vendor, fn ($q) => $q->whereHas('product', fn ($pq) => $pq->where('vendor_id', $user->vendor->id)))
            ->orderBy('stock')
            ->take(6)
            ->get();

        $recentOrders = (clone $ordersQuery)->with(['institution', 'payments', 'invoice'])
            ->latest()
            ->take(8)
            ->get();

        $institutionCommissions = Institution::withSum(
            ['orders as total_sales_sum' => fn ($q) => $q->where('order_status', '!=', 'cancelled')],
            'total'
        )->withSum(
            ['orders as commission_sum' => fn ($q) => $q->where('order_status', '!=', 'cancelled')],
            'institution_commission_amount'
        )->get();

        $vendorStats = Vendor::withSum('orderItems as gross_sales', 'total_price')->get();

        $superadminSummary = [
            'products_count' => Product::count(),
            'verified_vendors' => Vendor::where('is_active', true)->where('is_verified', true)->count(),
            'total_vendors' => Vendor::count(),
            'published_institutions' => Institution::published()->count(),
            'total_institutions' => Institution::count(),
            'estimated_profit' => $estimatedProfit,
            'total_cost' => round($totalItemsCost, 2),
        ];

        $openReports = ContentReport::where('status', 'open')->count();
        $pendingComplaints = Complaint::where('status', 'pending')->count();
        $recentLogs = AuditLog::with('user')->latest()->take(8)->get();

        return view('admin.dashboard', compact(
            'user',
            'totalSales',
            'ordersCount',
            'pendingPayments',
            'alumniPending',
            'alumniVerified',
            'activeCampaigns',
            'lowStockVariants',
            'recentOrders',
            'institutionCommissions',
            'vendorStats',
            'superadminSummary',
            'openReports',
            'pendingComplaints',
            'recentLogs'
        ));
    }
}
