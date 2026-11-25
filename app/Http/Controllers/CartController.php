<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\StorefrontService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly StorefrontService $storefront
    ) {}

    public function index(): View
    {
        $items = $this->cart->items();
        $subtotal = $this->cart->subtotal();
        $coupon = $this->cart->coupon();
        $discount = $this->cart->discountAmount();
        $estimatedTotal = max(0, $subtotal - $discount);
        $crossSellProducts = $this->storefront->crossSellProducts(collect($items)->pluck('product_id')->all());

        return view('store.cart', compact('items', 'subtotal', 'coupon', 'discount', 'estimatedTotal', 'crossSellProducts'));
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'custom_institution' => ['nullable', 'string', 'max:120'],
            'custom_year' => ['nullable', 'string', 'max:20'],
            'custom_name' => ['nullable', 'string', 'max:80'],
        ]);

        $product = Product::with('variants')->findOrFail($validated['product_id']);
        $variant = isset($validated['variant_id'])
            ? ProductVariant::where('product_id', $product->id)->findOrFail($validated['variant_id'])
            : $product->variants->first();

        if ($variant && ! $product->is_preorder_only && $variant->stock < $validated['quantity']) {
            return back()->with('error', "Stock insuficiente para la variante seleccionada ({$variant->stock} disponibles).");
        }

        $this->cart->add($product, $variant, (int) $validated['quantity'], [
            'institucion' => $validated['custom_institution'] ?? null,
            'promocion' => $validated['custom_year'] ?? null,
            'nombre_bordado' => $validated['custom_name'] ?? null,
        ], $request->user());

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.index')->with('success', "¡{$product->name} listo! Completa tu envío y pago.");
        }

        return redirect()->route('cart.index')->with('success', "{$product->name} agregado al carrito.");
    }

    public function addPack(Request $request): RedirectResponse
    {
        $code = $request->string('bundle_code')->toString();
        $bundle = collect($this->storefront->suggestedBundles())->firstWhere('code', $code);
        if (! $bundle) {
            return back()->with('error', 'No se encontró el pack promocional seleccionado.');
        }

        foreach ($bundle['products'] as $product) {
            $variant = $product->variants->first();
            $this->cart->add($product, $variant, 1, [], $request->user());
        }

        if ($this->cart->subtotal() >= 120) {
            $this->cart->applyCoupon('EGRESADO20');
        } else {
            $this->cart->applyCoupon('PROMO10');
        }

        return redirect()->route('cart.index')->with('success', "¡{$bundle['title']} agregado al carrito con cupón de descuento aplicado!");
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $this->cart->updateQuantity($key, (int) $validated['quantity']);

        return back()->with('success', 'Carrito actualizado.');
    }

    public function remove(string $key): RedirectResponse
    {
        $this->cart->remove($key);

        return back()->with('success', 'Producto retirado del carrito.');
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $code = trim((string) $request->input('code'));
        if ($code === '') {
            $this->cart->applyCoupon(null);

            return back()->with('success', 'Cupón retirado.');
        }

        $coupon = $this->cart->applyCoupon($code);
        if (! $coupon) {
            return back()->with('error', 'Cupón inválido, vencido o no cumple con el monto mínimo.');
        }

        return back()->with('success', "Cupón {$coupon->code} aplicado correctamente.");
    }
}
