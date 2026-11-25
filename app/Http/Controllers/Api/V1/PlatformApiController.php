<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Institution;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class PlatformApiController extends Controller
{
    public function institutions(): JsonResponse
    {
        return response()->json([
            'data' => Institution::published()
                ->withCount(['promotions', 'alumniProfiles', 'campaigns', 'activeDelegates'])
                ->get(),
        ]);
    }

    public function products(): JsonResponse
    {
        return response()->json([
            'data' => Product::where('is_active', true)
                ->with(['category:id,name,slug', 'institution:id,name,slug', 'variants'])
                ->paginate(20),
        ]);
    }

    public function campaigns(): JsonResponse
    {
        return response()->json([
            'data' => Campaign::with(['institution:id,name,slug', 'promotion:id,year,name', 'product:id,name,slug'])
                ->where('status', '!=', 'cancelled')
                ->get(),
        ]);
    }

    public function trackOrder(string $orderNumber): JsonResponse
    {
        $order = Order::with(['items', 'shippingZone', 'invoice'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return response()->json([
            'order_number' => $order->order_number,
            'order_status' => $order->order_status,
            'payment_status' => $order->payment_status,
            'tracking_code' => $order->tracking_code,
            'courier_name' => $order->courier_name,
            'total' => $order->total,
            'invoice' => $order->invoice?->formattedNumber(),
        ]);
    }
}
