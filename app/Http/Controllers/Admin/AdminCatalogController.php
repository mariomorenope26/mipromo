<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Institution;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCatalogController extends Controller
{
    /**
     * Preset real product photography available in public/images/products/.
     *
     * @return array<string, string>
     */
    private function imagePresets(): array
    {
        return [
            '/images/products/casaca.jpg' => 'Casaca Varsity Bordada',
            '/images/products/polera.jpg' => 'Polera Hoodie Promoción',
            '/images/products/polo.jpg' => 'Polo Piqué Promocional',
            '/images/products/gorro.jpg' => 'Gorro Drill Bordado',
            '/images/products/anillo.jpg' => 'Anillo de Promoción Oro/Plata',
            '/images/products/cadena.jpg' => 'Cadena con Dije Escudo',
            '/images/products/medalla.jpg' => 'Medalla Conmemorativa Dorada',
            '/images/products/taza.jpg' => 'Taza Cerámica Conmemorativa',
            '/images/products/termo.jpg' => 'Termo Inox + Pin Escudo',
            '/images/products/llavero.jpg' => 'Llavero Metálico + Estuche',
            '/images/products/recordatorio.jpg' => 'Placa Recordatoria Cristal',
            '/images/products/souvenir.jpg' => 'Caja Souvenir Aniversario',
            '/images/products/anuario.jpg' => 'Anuario Tapa Dura & Lapicero',
            '/images/products/mochila.jpg' => 'Mochila Ejecutiva Institucional',
            '/images/products/libreta.jpg' => 'Agenda / Libreta Ejecutiva',
            '/images/products/copa.jpg' => 'Copa Cristal de Brindis',
        ];
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $baseQuery = Product::query()
            ->when($user->isRole('institution_admin') && $user->institution_id, fn ($q) => $q->where('institution_id', $user->institution_id))
            ->when($user->isRole('vendor') && $user->vendor, fn ($q) => $q->where('vendor_id', $user->vendor->id));

        $allScopedProducts = (clone $baseQuery)->with(['vendor', 'variants'])->get();

        $filteredQuery = (clone $baseQuery)
            ->with(['category', 'institution', 'vendor', 'variants'])
            ->withCount('orderItems');

        if ($search = trim((string) $request->string('q'))) {
            $filteredQuery->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku_prefix', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhereHas('vendor', fn ($vq) => $vq->where('business_name', 'like', "%{$search}%"));
            });
        }

        if ($categoryId = $request->integer('category_id')) {
            $filteredQuery->where('category_id', $categoryId);
        }

        if ($instFilter = $request->string('institution_id')->toString()) {
            if ($instFilter === 'multi') {
                $filteredQuery->whereNull('institution_id');
            } elseif (is_numeric($instFilter)) {
                $filteredQuery->where('institution_id', (int) $instFilter);
            }
        }

        if ($vendorFilter = $request->string('vendor_id')->toString()) {
            if ($vendorFilter === 'direct') {
                $filteredQuery->whereNull('vendor_id');
            } elseif (is_numeric($vendorFilter)) {
                $filteredQuery->where('vendor_id', (int) $vendorFilter);
            }
        }

        if ($request->boolean('verified_only')) {
            $filteredQuery->where('show_verified_vendor', true)
                ->whereHas('vendor', fn ($vq) => $vq->where('is_verified', true));
        }

        if ($status = $request->string('estado')->toString()) {
            $filteredQuery->where('is_active', $status === 'active');
        }

        $products = $filteredQuery->orderByDesc('is_active')->latest()->paginate(15)->withQueryString();

        $categories = Category::withCount('products')->orderBy('sort_order')->get();
        $institutions = Institution::where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->orderByDesc('is_verified')->orderBy('business_name')->get();
        $coupons = Coupon::latest()->get();
        $productImages = $this->imagePresets();

        $catalogMetrics = [
            'total_products' => $allScopedProducts->count(),
            'active_products' => $allScopedProducts->where('is_active', true)->count(),
            'verified_vendor_products' => $allScopedProducts->filter(fn (Product $p) => $p->hasVerifiedVendorBadge())->count(),
            'avg_margin_percent' => $allScopedProducts->isNotEmpty()
                ? round($allScopedProducts->avg(fn (Product $p) => $p->marginPercent()), 1)
                : 0.0,
            'low_stock_variants' => $allScopedProducts->sum(fn (Product $p) => $p->variants->filter(fn (ProductVariant $v) => $v->isLowStock())->count()),
        ];

        return view('admin.catalog.index', compact(
            'products',
            'categories',
            'institutions',
            'vendors',
            'coupons',
            'productImages',
            'catalogMetrics'
        ));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $slug = Str::slug($validated['name']);
        if (Category::where('slug', $slug)->exists()) {
            $slug .= '-'.strtolower(Str::random(3));
        }

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'sort_order' => ((int) Category::max('sort_order')) + 1,
        ]);

        AuditLogger::log('category.created', "Categoría creada: {$category->name}", $category);

        return back()->with('success', "Categoría {$category->name} registrada.");
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'category_id' => ['required', 'exists:categories,id'],
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'sku_prefix' => ['required', 'string', 'max:30'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'base_price' => ['required', 'numeric', 'min:1'],
            'member_price' => ['nullable', 'numeric', 'min:1'],
            'short_description' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image_preset' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'variant_size' => ['required', 'string', 'max:20'],
            'variant_color' => ['required', 'string', 'max:50'],
            'variant_material' => ['nullable', 'string', 'max:80'],
            'variant_stock' => ['required', 'integer', 'min:0'],
            'variant_low_stock' => ['nullable', 'integer', 'min:0', 'max:500'],
        ]);

        $slug = Str::slug($validated['name']).'-'.strtolower(Str::random(4));
        $user = $request->user();

        $imagePath = $this->resolveProductImage($request, $validated['image_preset'] ?? '/images/products/polo.jpg');

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'category_id' => $validated['category_id'],
            'institution_id' => $user->isRole('institution_admin') ? $user->institution_id : ($validated['institution_id'] ?? null),
            'vendor_id' => $user->isRole('vendor') ? $user->vendor?->id : ($validated['vendor_id'] ?? null),
            'sku_prefix' => strtoupper($validated['sku_prefix']),
            'short_description' => $validated['short_description'],
            'description' => $validated['description'],
            'purchase_price' => $validated['purchase_price'],
            'base_price' => $validated['base_price'],
            'member_price' => $validated['member_price'] ?? null,
            'is_customizable' => $request->boolean('is_customizable', true),
            'is_preorder_only' => $request->boolean('is_preorder_only'),
            'show_verified_vendor' => $request->boolean('show_verified_vendor', true),
            'image_path' => $imagePath,
            'is_featured' => $request->boolean('is_featured', true),
            'is_active' => $request->boolean('is_active', true),
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => strtoupper($validated['sku_prefix']).'-'.strtoupper(Str::slug($validated['variant_size'])).'-'.strtoupper(Str::random(3)),
            'size' => $validated['variant_size'],
            'color' => $validated['variant_color'],
            'color_hex' => '#1D70B8',
            'material' => ($validated['variant_material'] ?? null) ?: 'Estándar Institucional',
            'stock' => $validated['variant_stock'],
            'low_stock_threshold' => $validated['variant_low_stock'] ?? 5,
        ]);

        AuditLogger::log(
            'product.created',
            "Producto creado: {$product->name} (Compra S/ {$product->purchase_price} · Venta S/ {$product->base_price})",
            $product
        );

        return back()->with('success', "Producto {$product->name} creado con su precio de compra, precio de venta y variante inicial.");
    }

    public function editProduct(Request $request, Product $product): View
    {
        $this->authorizeProductAccess($request, $product);

        $product->load(['category', 'institution', 'vendor', 'variants']);
        $categories = Category::orderBy('sort_order')->get();
        $institutions = Institution::where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::orderByDesc('is_verified')->orderBy('business_name')->get();
        $productImages = $this->imagePresets();

        return view('admin.catalog.edit', compact('product', 'categories', 'institutions', 'vendors', 'productImages'));
    }

    public function updateProduct(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProductAccess($request, $product);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'category_id' => ['required', 'exists:categories,id'],
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'sku_prefix' => ['required', 'string', 'max:30'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'base_price' => ['required', 'numeric', 'min:1'],
            'member_price' => ['nullable', 'numeric', 'min:1'],
            'short_description' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image_preset' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $user = $request->user();
        $imagePath = $this->resolveProductImage($request, $validated['image_preset'] ?? ($product->image_path ?? '/images/products/polo.jpg'));

        $product->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'institution_id' => $user->isRole('institution_admin') ? $user->institution_id : ($validated['institution_id'] ?? null),
            'vendor_id' => $user->isRole('vendor') ? $user->vendor?->id : ($validated['vendor_id'] ?? null),
            'sku_prefix' => strtoupper($validated['sku_prefix']),
            'short_description' => $validated['short_description'],
            'description' => $validated['description'],
            'purchase_price' => $validated['purchase_price'],
            'base_price' => $validated['base_price'],
            'member_price' => $validated['member_price'] ?? null,
            'image_path' => $imagePath,
            'is_customizable' => $request->boolean('is_customizable'),
            'is_preorder_only' => $request->boolean('is_preorder_only'),
            'show_verified_vendor' => $request->boolean('show_verified_vendor'),
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLogger::log(
            'product.updated',
            "Producto {$product->name} actualizado (Compra S/ {$product->purchase_price} · Venta S/ {$product->base_price} · Prov: ".($product->vendor?->business_name ?? 'Directo').')',
            $product
        );

        return redirect()->route('admin.catalog.products.edit', $product->id)
            ->with('success', "Producto {$product->name} actualizado correctamente.");
    }

    public function toggleProductStatus(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProductAccess($request, $product);

        $product->update(['is_active' => ! $product->is_active]);
        $statusLabel = $product->is_active ? 'activado' : 'ocultado del catálogo';
        AuditLogger::log('product.toggled', "Producto {$product->name} {$statusLabel}", $product);

        return back()->with('success', "Producto {$product->name} {$statusLabel}.");
    }

    public function destroyProduct(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProductAccess($request, $product);

        if ($product->orderItems()->exists() || $product->campaigns()->exists()) {
            $product->update(['is_active' => false]);
            AuditLogger::log('product.archived', "Producto {$product->name} archivado (posee historial comercial)", $product);

            return redirect()->route('admin.catalog.index')
                ->with('success', "El producto {$product->name} tiene pedidos o campañas históricas, por lo que fue desactivado para conservar trazabilidad.");
        }

        $name = $product->name;
        $product->variants()->delete();
        $product->delete();
        AuditLogger::log('product.deleted', "Producto eliminado del catálogo: {$name}");

        return redirect()->route('admin.catalog.index')
            ->with('success', "Producto {$name} eliminado del catálogo.");
    }

    public function storeVariant(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProductAccess($request, $product);

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:60', 'unique:product_variants,sku'],
            'size' => ['required', 'string', 'max:20'],
            'color' => ['required', 'string', 'max:50'],
            'material' => ['nullable', 'string', 'max:80'],
            'price_adjustment' => ['nullable', 'numeric', 'min:-500', 'max:1000'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => strtoupper(trim($validated['sku'])),
            'size' => $validated['size'],
            'color' => $validated['color'],
            'color_hex' => '#1D70B8',
            'material' => $validated['material'] ?? null,
            'price_adjustment' => $validated['price_adjustment'] ?? 0,
            'stock' => $validated['stock'],
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? 5,
        ]);

        AuditLogger::log('variant.created', "Variante {$variant->sku} agregada a {$product->name}", $variant);

        return back()->with('success', "Variante {$variant->sku} agregada.");
    }

    public function updateVariant(Request $request, ProductVariant $variant): RedirectResponse
    {
        $this->authorizeProductAccess($request, $variant->product);

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:60', Rule::unique('product_variants', 'sku')->ignore($variant->id)],
            'size' => ['required', 'string', 'max:20'],
            'color' => ['required', 'string', 'max:50'],
            'material' => ['nullable', 'string', 'max:80'],
            'price_adjustment' => ['nullable', 'numeric', 'min:-500', 'max:1000'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ]);

        $variant->update([
            'sku' => strtoupper(trim($validated['sku'])),
            'size' => $validated['size'],
            'color' => $validated['color'],
            'material' => $validated['material'] ?? null,
            'price_adjustment' => $validated['price_adjustment'] ?? 0,
            'stock' => $validated['stock'],
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? $variant->low_stock_threshold,
        ]);

        AuditLogger::log('variant.updated', "Variante {$variant->sku} actualizada ({$variant->stock} uds.)", $variant);

        return back()->with('success', "Variante {$variant->sku} actualizada.");
    }

    public function updateVariantStock(Request $request, ProductVariant $variant): RedirectResponse
    {
        $this->authorizeProductAccess($request, $variant->product);

        $validated = $request->validate([
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);

        $variant->update(['stock' => $validated['stock']]);
        AuditLogger::log('variant.stock_updated', "Stock actualizado para {$variant->sku}: {$validated['stock']} uds.", $variant);

        return back()->with('success', "Stock de {$variant->sku} actualizado a {$validated['stock']}.");
    }

    public function destroyVariant(Request $request, ProductVariant $variant): RedirectResponse
    {
        $this->authorizeProductAccess($request, $variant->product);

        if ($variant->product->variants()->count() <= 1) {
            return back()->with('error', 'Cada producto debe conservar al menos una variante.');
        }

        if (OrderItem::where('variant_id', $variant->id)->exists()) {
            $variant->update(['stock' => 0]);

            return back()->with('success', "La variante {$variant->sku} tiene pedidos previos; su stock fue puesto en 0.");
        }

        $sku = $variant->sku;
        $variant->delete();
        AuditLogger::log('variant.deleted', "Variante eliminada: {$sku}");

        return back()->with('success', "Variante {$sku} eliminada.");
    }

    public function storeCoupon(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:coupons,code'],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:1'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:200'],
        ]);

        $coupon = Coupon::create([
            ...$validated,
            'code' => strtoupper(trim($validated['code'])),
            'is_active' => true,
        ]);

        AuditLogger::log('coupon.created', "Cupón creado: {$coupon->code}", $coupon);

        return back()->with('success', "Cupón {$coupon->code} creado.");
    }

    public function toggleCoupon(Coupon $coupon): RedirectResponse
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);
        AuditLogger::log('coupon.toggled', "Cupón {$coupon->code} ".($coupon->is_active ? 'activado' : 'pausado'), $coupon);

        return back()->with('success', "Cupón {$coupon->code} actualizado.");
    }

    private function authorizeProductAccess(Request $request, Product $product): void
    {
        $user = $request->user();
        if ($user->isRole('super_admin')) {
            return;
        }

        if ($user->isRole('institution_admin') && $user->institution_id === $product->institution_id) {
            return;
        }

        if ($user->isRole('vendor') && $user->vendor && $user->vendor->id === $product->vendor_id) {
            return;
        }

        abort(403, 'No tienes permisos para modificar este producto.');
    }

    private function resolveProductImage(Request $request, string $fallback): string
    {
        if ($request->hasFile('image')) {
            $dir = public_path('images/products/uploads');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $ext = strtolower($request->file('image')->getClientOriginalExtension() ?: 'jpg');
            $filename = 'prod-'.now()->format('YmdHis').'-'.strtolower(Str::random(5)).'.'.$ext;
            $request->file('image')->move($dir, $filename);

            return '/images/products/uploads/'.$filename;
        }

        return $fallback ?: '/images/products/polo.jpg';
    }
}
