<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

class CartService
{
    private const SESSION_KEY = 'mipromo_cart';

    private const COUPON_KEY = 'mipromo_coupon';

    /**
     * @return array<string, array<string, mixed>>
     */
    public function items(): array
    {
        return session(self::SESSION_KEY, []);
    }

    public function add(
        Product $product,
        ?ProductVariant $variant,
        int $quantity = 1,
        array $customization = [],
        ?User $user = null,
        ?float $overrideUnitPrice = null
    ): void {
        $cart = $this->items();
        $baseUnitPrice = $overrideUnitPrice ?? ($product->effectivePrice($user) + (float) ($variant?->price_adjustment ?? 0));
        $customClean = array_filter($customization, fn ($val) => $val !== null && $val !== '');
        $key = md5($product->id.'|'.($variant?->id ?? 0).'|'.json_encode($customClean));

        $newQty = isset($cart[$key]) ? $cart[$key]['quantity'] + $quantity : $quantity;
        $volumeDiscountPercent = $overrideUnitPrice === null ? $this->volumeDiscountPercent($newQty) : 0;
        $finalUnitPrice = round($baseUnitPrice * (1 - ($volumeDiscountPercent / 100)), 2);

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = $newQty;
            $cart[$key]['unit_price'] = $finalUnitPrice;
            $cart[$key]['volume_discount_percent'] = $volumeDiscountPercent;
        } else {
            $cart[$key] = [
                'key' => $key,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_slug' => $product->slug,
                'institution_id' => $product->institution_id,
                'vendor_id' => $product->vendor_id,
                'variant_id' => $variant?->id,
                'variant_label' => $variant?->label() ?: 'Estándar',
                'image_path' => $product->image_path,
                'base_unit_price' => round($baseUnitPrice, 2),
                'unit_price' => $finalUnitPrice,
                'unit_cost' => round((float) ($product->purchase_price ?? 0), 2),
                'volume_discount_percent' => $volumeDiscountPercent,
                'quantity' => $newQty,
                'customization' => $customClean,
            ];
        }

        session([self::SESSION_KEY => $cart]);
    }

    public function updateQuantity(string $key, int $quantity): void
    {
        $cart = $this->items();
        if (! isset($cart[$key])) {
            return;
        }

        if ($quantity <= 0) {
            unset($cart[$key]);
        } else {
            $cart[$key]['quantity'] = $quantity;
            if (isset($cart[$key]['base_unit_price'])) {
                $volPct = $this->volumeDiscountPercent($quantity);
                $cart[$key]['volume_discount_percent'] = $volPct;
                $cart[$key]['unit_price'] = round(((float) $cart[$key]['base_unit_price']) * (1 - ($volPct / 100)), 2);
            }
        }

        session([self::SESSION_KEY => $cart]);
    }

    public function volumeDiscountPercent(int $quantity): int
    {
        if ($quantity >= 30) {
            return 18;
        }
        if ($quantity >= 12) {
            return 10;
        }

        return 0;
    }

    public function remove(string $key): void
    {
        $cart = $this->items();
        unset($cart[$key]);
        session([self::SESSION_KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget([self::SESSION_KEY, self::COUPON_KEY]);
    }

    public function count(): int
    {
        return collect($this->items())->sum('quantity');
    }

    public function subtotal(): float
    {
        return round((float) collect($this->items())->sum(fn ($item) => $item['unit_price'] * $item['quantity']), 2);
    }

    public function applyCoupon(?string $code): ?Coupon
    {
        if (! $code) {
            session()->forget(self::COUPON_KEY);

            return null;
        }

        $coupon = Coupon::where('code', strtoupper(trim($code)))->first();
        if (! $coupon || ! $coupon->isValidForSubtotal($this->subtotal())) {
            return null;
        }

        session([self::COUPON_KEY => $coupon->code]);

        return $coupon;
    }

    public function coupon(): ?Coupon
    {
        $code = session(self::COUPON_KEY);

        return $code ? Coupon::where('code', $code)->first() : null;
    }

    public function discountAmount(): float
    {
        $coupon = $this->coupon();

        return $coupon ? $coupon->calculateDiscount($this->subtotal()) : 0.0;
    }
}
