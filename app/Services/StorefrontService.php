<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class StorefrontService
{
    public function filterProducts(Request $request, int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['category', 'institution', 'variants', 'vendor'])
            ->where('is_active', true);

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('sku_prefix', 'like', "%{$search}%")
                    ->orWhereHas('institution', fn ($iq) => $iq->where('name', 'like', "%{$search}%")->orWhere('short_name', 'like', "%{$search}%"));
            });
        }

        if ($categorySlug = $request->string('categoria')->toString()) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        if ($institutionSlug = $request->string('colegio')->toString()) {
            if ($institutionSlug === 'multi') {
                $query->whereNull('institution_id');
            } else {
                $query->whereHas('institution', fn ($q) => $q->where('slug', $institutionSlug));
            }
        }

        if ($request->boolean('preventa')) {
            $query->where('is_preorder_only', true);
        }

        if ($request->boolean('verificado')) {
            $query->where('show_verified_vendor', true)
                ->whereHas('vendor', fn ($vq) => $vq->where('is_verified', true)->where('is_active', true));
        }

        match ($request->string('orden')->toString()) {
            'precio_asc' => $query->orderBy('base_price'),
            'precio_desc' => $query->orderByDesc('base_price'),
            default => $query->orderByDesc('is_featured')->latest(),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @return Collection<int, Category>
     */
    public function categoriesWithCount(): Collection
    {
        return Category::query()
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return Collection<int, Product>
     */
    public function crossSellProducts(array $excludeProductIds = [], int $limit = 4): Collection
    {
        return Product::query()
            ->with(['category', 'variants', 'vendor', 'institution'])
            ->where('is_active', true)
            ->whereNotIn('id', $excludeProductIds)
            ->where('base_price', '<=', 75)
            ->orderByDesc('is_featured')
            ->take($limit)
            ->get();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function suggestedBundles(): array
    {
        $gorro = Product::with('variants')->where('slug', 'gorro-drill-bordado-personalizable')->where('is_active', true)->first();
        $llavero = Product::with('variants')->where('slug', 'llavero-metalico-escudo-relieve')->where('is_active', true)->first();
        $termo = Product::with('variants')->where('slug', 'termo-inox-pin-solapa')->where('is_active', true)->first();
        $polera = Product::with('variants')->where('slug', 'polera-hoodie-promocional')->where('is_active', true)->first();
        $casaca = Product::with('variants')->where('slug', 'casaca-varsity-guadalupe')->where('is_active', true)->first();
        $anillo = Product::with('variants')->where('slug', 'anillo-promocion-oro-plata')->where('is_active', true)->first();
        $medalla = Product::with('variants')->where('slug', 'medalla-honor-dorada-cinta')->where('is_active', true)->first();

        $bundles = [];

        if ($casaca && $gorro && $llavero) {
            $bundles[] = [
                'code' => 'kit-reencuentro',
                'title' => 'Pack Reencuentro Total (Casaca + Gorro + Llavero)',
                'subtitle' => 'El combo más vendido para celebraciones de promoción',
                'savings_label' => 'Usa PROMO10 al final (-10%)',
                'products' => [$casaca, $gorro, $llavero],
                'regular_total' => (float) $casaca->base_price + (float) $gorro->base_price + (float) $llavero->base_price,
            ];
        }

        if ($polera && $termo && $llavero) {
            $bundles[] = [
                'code' => 'kit-promo',
                'title' => 'Pack Promoción Activa (Polera + Termo + Llavero)',
                'subtitle' => 'Personalizable para cualquier colegio o promoción del Perú',
                'savings_label' => 'Supera S/ 120: activa EGRESADO20 (-S/ 20)',
                'products' => [$polera, $termo, $llavero],
                'regular_total' => (float) $polera->base_price + (float) $termo->base_price + (float) $llavero->base_price,
            ];
        }

        if ($anillo && $medalla && $llavero) {
            $bundles[] = [
                'code' => 'kit-gala',
                'title' => 'Pack Ceremonia de Gala (Anillo + Medalla + Llavero)',
                'subtitle' => 'Ideal para graduaciones, Bodas de Plata y ceremonias de honor',
                'savings_label' => 'Descuento EGRESADO20 aplicado (-S/ 20)',
                'products' => [$anillo, $medalla, $llavero],
                'regular_total' => (float) $anillo->base_price + (float) $medalla->base_price + (float) $llavero->base_price,
            ];
        }

        return $bundles;
    }
}
