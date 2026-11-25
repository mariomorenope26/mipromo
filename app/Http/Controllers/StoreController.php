<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\StorefrontService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function __construct(private readonly StorefrontService $storefront) {}

    public function index(Request $request): View
    {
        $products = $this->storefront->filterProducts($request, 12);
        $categories = $this->storefront->categoriesWithCount();
        $institutions = Institution::published()->orderBy('name')->get();
        $bundles = $this->storefront->suggestedBundles();

        return view('store.index', compact('products', 'categories', 'institutions', 'bundles'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'institution', 'vendor', 'variants', 'reviews.user']);

        $relatedProducts = Product::query()
            ->with(['category', 'variants', 'institution'])
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                $q->where('category_id', $product->category_id);
                if ($product->institution_id) {
                    $q->orWhere('institution_id', $product->institution_id);
                }
            })
            ->take(4)
            ->get();

        $inWishlist = auth()->check()
            ? Wishlist::where('user_id', auth()->id())->where('product_id', $product->id)->exists()
            : false;

        return view('store.show', compact('product', 'relatedProducts', 'inWishlist'));
    }

    public function toggleWishlist(Product $product): RedirectResponse
    {
        $existing = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $message = 'Producto eliminado de tu lista de deseos.';
        } else {
            Wishlist::create(['user_id' => auth()->id(), 'product_id' => $product->id]);
            $message = 'Producto guardado en tu lista de deseos.';
        }

        return back()->with('success', $message);
    }

    public function storeReview(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $product->reviews()->create([
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'is_approved' => true,
        ]);

        return back()->with('success', '¡Gracias! Tu reseña ha sido publicada.');
    }
}
