@extends('layouts.admin')

@section('heading', 'Editar Producto: '.$product->name)
@section('subheading', 'Actualiza costos de compra, precios de venta, proveedor asignado, imagen y todas sus variantes SKU')

@section('content')
<div class="space-y-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.catalog.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50">
            ← Volver al Maestro de Catálogo
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('products.show', $product->slug) }}" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">
                Ver ficha en Tienda Cliente ↗
            </a>
            <form action="{{ route('admin.catalog.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar o archivar este producto?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold">
                    Eliminar Producto
                </button>
            </form>
        </div>
    </div>

    <div class="grid lg:grid-cols-12 gap-6">
        {{-- Formulario de Edición Completa del Producto --}}
        <form action="{{ route('admin.catalog.products.update', $product->id) }}" method="POST" enctype="multipart/form-data"
              x-data="{ purchase: {{ (float) $product->purchase_price }}, sale: {{ (float) $product->base_price }} }"
              class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <h2 class="font-extrabold text-[#0F2D4A]">Datos Generales, Costos y Proveedor</h2>
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-3 py-1.5 text-xs">
                    <span class="text-emerald-800 font-semibold">Margen bruto:</span>
                    <strong class="text-emerald-900" x-text="'S/ ' + Math.max(0, (Number(sale || 0) - Number(purchase || 0))).toFixed(2) + ' (' + (Number(sale) > 0 ? (((Number(sale) - Number(purchase)) / Number(sale)) * 100).toFixed(1) : '0.0') + '%)'"></strong>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-3 text-xs">
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1">Nombre del producto *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Prefijo SKU *</label>
                    <input type="text" name="sku_prefix" value="{{ old('sku_prefix', $product->sku_prefix) }}" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm uppercase">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Categoría *</label>
                    <select name="category_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($product->category_id === $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Colegio / Institución</label>
                    <select name="institution_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                        <option value="">Multi-Colegio (General)</option>
                        @foreach($institutions as $inst)
                            <option value="{{ $inst->id }}" @selected($product->institution_id === $inst->id)>{{ $inst->short_name }} ({{ $inst->city }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nombre del Proveedor (solo visible en Superadmin)</label>
                    <select name="vendor_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                        <option value="">Producción Directa JMSPERU</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected($product->vendor_id === $vendor->id)>
                                {{ $vendor->business_name }} {{ $vendor->is_verified ? '✓ Verificado' : '(Pendiente verificar)' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-3 text-xs">
                <div class="rounded-xl bg-amber-50/70 border border-amber-200 p-3">
                    <label class="block font-bold text-amber-900 mb-1">Precio de Compra (Costo S/) *</label>
                    <input type="number" step="0.01" min="0" name="purchase_price" x-model="purchase" value="{{ old('purchase_price', $product->purchase_price) }}" required class="w-full px-3 py-2 rounded-lg border border-amber-300 bg-white text-sm font-bold">
                </div>
                <div class="rounded-xl bg-sky-50/80 border border-sky-200 p-3">
                    <label class="block font-bold text-[#0F2D4A] mb-1">Precio de Venta Público S/ *</label>
                    <input type="number" step="0.01" min="1" name="base_price" x-model="sale" value="{{ old('base_price', $product->base_price) }}" required class="w-full px-3 py-2 rounded-lg border border-sky-300 bg-white text-sm font-bold">
                </div>
                <div class="rounded-xl bg-emerald-50/70 border border-emerald-200 p-3">
                    <label class="block font-bold text-emerald-900 mb-1">Precio Venta Ex-Alumno S/</label>
                    <input type="number" step="0.01" min="1" name="member_price" value="{{ old('member_price', $product->member_price) }}" class="w-full px-3 py-2 rounded-lg border border-emerald-300 bg-white text-sm font-bold">
                </div>
            </div>

            <div class="grid sm:grid-cols-12 gap-4 items-center text-xs bg-slate-50 rounded-2xl p-4 border border-slate-200">
                <div class="sm:col-span-3">
                    <img src="{{ asset(ltrim($product->image_path ?? 'images/products/polo.jpg', '/')) }}" alt="{{ $product->name }}" class="w-full aspect-[4/3] object-cover rounded-xl border border-slate-200">
                </div>
                <div class="sm:col-span-9 space-y-2.5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Seleccionar fotografía de galería</label>
                        <select name="image_preset" class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm">
                            @foreach($productImages as $path => $label)
                                <option value="{{ $path }}" @selected($product->image_path === $path)>{{ $label }} ({{ basename($path) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">O subir imagen personalizada (JPG/PNG/WEBP)</label>
                        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/*" class="text-xs">
                    </div>
                </div>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Resumen corto *</label>
                    <input type="text" name="short_description" value="{{ old('short_description', $product->short_description) }}" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Descripción completa *</label>
                    <textarea name="description" required rows="3" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>

            <div class="rounded-2xl bg-emerald-50/70 border border-emerald-200 p-4 space-y-2 text-xs">
                <p class="font-extrabold text-emerald-900">Visibilidad de Proveedor (Cliente vs. Superadmin)</p>
                <p class="text-emerald-800">En la tienda cliente no se expone el nombre comercial ni RUC del taller ({{ $product->vendor?->business_name ?? 'Sin proveedor externo' }}); si activas la casilla inferior y el proveedor está verificado, el cliente verá el sello <strong>“✓ Proveedor Verificado”</strong>.</p>
                <div class="flex flex-wrap gap-5 pt-1">
                    <label class="flex items-center gap-1.5 font-bold text-emerald-900">
                        <input type="checkbox" name="show_verified_vendor" value="1" @checked($product->show_verified_vendor) class="rounded text-emerald-600">
                        <span>Mostrar opción "Proveedor Verificado" en tienda cliente</span>
                    </label>
                    <label class="flex items-center gap-1.5 font-semibold text-slate-700">
                        <input type="checkbox" name="is_active" value="1" @checked($product->is_active) class="rounded text-[#1D70B8]">
                        <span>Producto activo</span>
                    </label>
                    <label class="flex items-center gap-1.5 font-semibold text-slate-700">
                        <input type="checkbox" name="is_featured" value="1" @checked($product->is_featured) class="rounded text-[#1D70B8]">
                        <span>Destacado</span>
                    </label>
                    <label class="flex items-center gap-1.5 font-semibold text-slate-700">
                        <input type="checkbox" name="is_customizable" value="1" @checked($product->is_customizable) class="rounded text-[#1D70B8]">
                        <span>Personalizable</span>
                    </label>
                    <label class="flex items-center gap-1.5 font-semibold text-slate-700">
                        <input type="checkbox" name="is_preorder_only" value="1" @checked($product->is_preorder_only) class="rounded text-[#1D70B8]">
                        <span>Solo bajo pedido</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white text-sm font-bold shadow-sm transition">
                Guardar Cambios del Producto
            </button>
        </form>

        {{-- CRUD Completo de Variantes --}}
        <div class="lg:col-span-5 space-y-6">
            <form action="{{ route('admin.catalog.variants.store', $product->id) }}" method="POST" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3 text-xs">
                @csrf
                <h2 class="font-extrabold text-sm text-[#0F2D4A]">Agregar Nueva Variante / Talla</h2>
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">SKU único *</label>
                        <input type="text" name="sku" required value="{{ $product->sku_prefix }}-NEW" class="w-full px-3 py-2 rounded-xl border border-slate-300 uppercase">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Talla / Medida *</label>
                        <input type="text" name="size" required placeholder="XL, L, M..." class="w-full px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Color *</label>
                        <input type="text" name="color" required placeholder="Azul Marino" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Material</label>
                        <input type="text" name="material" placeholder="Algodón Pima" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ajuste precio S/</label>
                        <input type="number" step="0.01" name="price_adjustment" value="0" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                    <div class="grid grid-cols-2 gap-1.5">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Stock *</label>
                            <input type="number" name="stock" required min="0" value="20" class="w-full px-2.5 py-2 rounded-xl border border-slate-300 font-bold">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Mín.</label>
                            <input type="number" name="low_stock_threshold" min="0" value="5" class="w-full px-2.5 py-2 rounded-xl border border-slate-300">
                        </div>
                    </div>
                </div>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-[#1D70B8] text-white font-bold transition">
                    + Agregar Variante
                </button>
            </form>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
                <h2 class="font-extrabold text-sm text-[#0F2D4A]">Variantes Existentes ({{ $product->variants->count() }})</h2>
                <div class="space-y-3">
                    @foreach($product->variants as $variant)
                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5 space-y-2.5 text-xs">
                            <form action="{{ route('admin.catalog.variants.update', $variant->id) }}" method="POST" class="space-y-2">
                                @csrf
                                @method('PATCH')
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500">SKU</label>
                                        <input type="text" name="sku" value="{{ $variant->sku }}" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white font-bold uppercase">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500">Talla</label>
                                        <input type="text" name="size" value="{{ $variant->size }}" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500">Color</label>
                                        <input type="text" name="color" value="{{ $variant->color }}" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500">Material</label>
                                        <input type="text" name="material" value="{{ $variant->material }}" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500">Ajuste S/</label>
                                        <input type="number" step="0.01" name="price_adjustment" value="{{ $variant->price_adjustment }}" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                                    </div>
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500">Stock</label>
                                            <input type="number" name="stock" value="{{ $variant->stock }}" min="0" required class="w-full px-2 py-1.5 rounded-lg border border-slate-300 bg-white font-bold">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500">Mín.</label>
                                            <input type="number" name="low_stock_threshold" value="{{ $variant->low_stock_threshold }}" min="0" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 bg-white">
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-1">
                                    <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-[#1D70B8] text-white font-bold">Actualizar Variante</button>
                                </div>
                            </form>
                            <form action="{{ route('admin.catalog.variants.destroy', $variant->id) }}" method="POST" class="flex justify-end">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[11px] font-bold text-rose-600 hover:underline">Eliminar variante</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
