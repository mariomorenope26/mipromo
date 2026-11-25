@extends('layouts.admin')

@section('heading', 'Catálogo Completo, Precios, Stock y Proveedores')
@section('subheading', 'CRUD integral de productos: precio de compra vs. precio de venta, margen, proveedor interno y sello verificado para clientes')

@section('content')
<div class="space-y-8">
    {{-- Resumen Ejecutivo de Catálogo --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-[11px] font-bold uppercase text-slate-500">Total Productos</p>
            <p class="text-2xl font-black text-[#0F2D4A] mt-1">{{ $catalogMetrics['total_products'] }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">{{ $catalogMetrics['active_products'] }} visibles en tienda</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-[11px] font-bold uppercase text-slate-500">Con Proveedor Verificado</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $catalogMetrics['verified_vendor_products'] }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Sello visible al cliente</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-[11px] font-bold uppercase text-slate-500">Margen Bruto Promedio</p>
            <p class="text-2xl font-black text-[#1D70B8] mt-1">{{ $catalogMetrics['avg_margin_percent'] }}%</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Venta vs. precio compra</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-[11px] font-bold uppercase text-slate-500">Categorías</p>
            <p class="text-2xl font-black text-[#0F2D4A] mt-1">{{ $categories->count() }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Líneas promocionales</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-[11px] font-bold uppercase text-slate-500">Alertas Stock Bajo</p>
            <p class="text-2xl font-black {{ $catalogMetrics['low_stock_variants'] > 0 ? 'text-amber-600' : 'text-emerald-600' }} mt-1">{{ $catalogMetrics['low_stock_variants'] }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Variantes por reponer</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-12 gap-6">
        {{-- Registrar Nuevo Producto con Compra / Venta y Proveedor --}}
        <form action="{{ route('admin.catalog.products.store') }}" method="POST" enctype="multipart/form-data"
              x-data="{ purchase: 60, sale: 120 }"
              class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 p-6 space-y-5">
            @csrf
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="font-extrabold text-[#0F2D4A]">Registrar Nuevo Producto en Catálogo</h2>
                    <p class="text-xs text-slate-500">Define costos de compra, precios de venta al público/ex-alumno y vincula el proveedor asignado.</p>
                </div>
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-3 py-1.5 text-xs">
                    <span class="text-emerald-800 font-semibold">Margen estimado:</span>
                    <strong class="text-emerald-900" x-text="'S/ ' + Math.max(0, (Number(sale || 0) - Number(purchase || 0))).toFixed(2) + ' (' + (Number(sale) > 0 ? (((Number(sale) - Number(purchase)) / Number(sale)) * 100).toFixed(1) : '0.0') + '%)'"></strong>
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-3 text-xs">
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1">Nombre del producto *</label>
                    <input type="text" name="name" required placeholder="Ej. Casaca Varsity Bordada — Edición Especial" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Prefijo SKU *</label>
                    <input type="text" name="sku_prefix" required placeholder="Ej. CAS-GUA" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm uppercase">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Categoría *</label>
                    <select name="category_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Colegio / Alcance</label>
                    <select name="institution_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                        <option value="">Multi-Colegio (General)</option>
                        @foreach($institutions as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->short_name }} ({{ $inst->city }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nombre del Proveedor (Superadmin)</label>
                    <select name="vendor_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                        <option value="">Producción Directa JMSPERU</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}">
                                {{ $vendor->business_name }} {{ $vendor->is_verified ? '✓ Verificado' : '(Por verificar)' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="rounded-xl bg-amber-50/70 border border-amber-200 p-2.5">
                    <label class="block font-bold text-amber-900 mb-1">Precio de Compra / Costo S/ *</label>
                    <input type="number" step="0.01" min="0" name="purchase_price" x-model="purchase" required placeholder="0.00" class="w-full px-3 py-2 rounded-lg border border-amber-300 bg-white text-sm font-bold text-slate-900">
                </div>
                <div class="rounded-xl bg-sky-50/80 border border-sky-200 p-2.5">
                    <label class="block font-bold text-[#0F2D4A] mb-1">Precio de Venta Público S/ *</label>
                    <input type="number" step="0.01" min="1" name="base_price" x-model="sale" required placeholder="0.00" class="w-full px-3 py-2 rounded-lg border border-sky-300 bg-white text-sm font-bold text-[#0F2D4A]">
                </div>
                <div class="rounded-xl bg-emerald-50/70 border border-emerald-200 p-2.5">
                    <label class="block font-bold text-emerald-900 mb-1">Precio Venta Ex-Alumno S/</label>
                    <input type="number" step="0.01" min="1" name="member_price" placeholder="Opcional (ej. 99.00)" class="w-full px-3 py-2 rounded-lg border border-emerald-300 bg-white text-sm font-bold text-emerald-800">
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1">Foto de galería real</label>
                    <select name="image_preset" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                        @foreach($productImages as $path => $label)
                            <option value="{{ $path }}">{{ $label }} ({{ basename($path) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">O subir foto nueva</label>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/*" class="w-full text-xs pt-1">
                </div>

                <div class="sm:col-span-3">
                    <label class="block font-bold text-slate-700 mb-1">Resumen corto *</label>
                    <input type="text" name="short_description" required placeholder="Resumen atractivo visible en tarjeta de tienda..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Talla / Medida inicial *</label>
                    <input type="text" name="variant_size" required value="M" placeholder="Ej. M, 11 oz, A4" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Color inicial *</label>
                    <input type="text" name="variant_color" required value="Azul Institucional" placeholder="Ej. Azul / Dorado" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Stock inicial *</label>
                        <input type="number" name="variant_stock" required min="0" value="25" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mín. alerta</label>
                        <input type="number" name="variant_low_stock" min="0" value="5" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <label class="block font-bold text-slate-700 mb-1">Material / Acabado inicial</label>
                    <input type="text" name="variant_material" placeholder="Ej. Paño 380g + Cuero ecológico" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Descripción completa del producto *</label>
                <textarea name="description" required rows="2" placeholder="Detalles técnicos de fabricación, bordado o personalización..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
            </div>

            <div class="flex flex-wrap items-center gap-5 text-xs bg-slate-50 rounded-xl p-3 border border-slate-200">
                <label class="flex items-center gap-1.5 font-semibold"><input type="checkbox" name="is_customizable" value="1" checked class="rounded text-[#1D70B8]"> Personalizable</label>
                <label class="flex items-center gap-1.5 font-semibold"><input type="checkbox" name="is_preorder_only" value="1" class="rounded text-[#1D70B8]"> Solo bajo pedido</label>
                <label class="flex items-center gap-1.5 font-semibold"><input type="checkbox" name="is_featured" value="1" checked class="rounded text-[#1D70B8]"> Destacado</label>
                <label class="flex items-center gap-1.5 font-bold text-emerald-800"><input type="checkbox" name="show_verified_vendor" value="1" checked class="rounded text-emerald-600"> Mostrar opción "Proveedor Verificado" en lado cliente</label>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white text-xs font-bold shadow-sm transition">
                    + Crear Producto en Catálogo
                </button>
            </div>
        </form>

        {{-- Panel Lateral: Cupones y Categorías --}}
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                <h2 class="font-extrabold text-[#0F2D4A]">Cupones Promocionales</h2>
                <form action="{{ route('admin.catalog.coupons.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Código</label>
                            <input type="text" name="code" required placeholder="PROMO25" class="w-full px-3 py-2 rounded-xl border border-slate-300 uppercase">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Tipo</label>
                            <select name="type" class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white">
                                <option value="percent">% Porcentaje</option>
                                <option value="fixed">S/ Monto fijo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Valor</label>
                            <input type="number" step="0.01" name="value" required placeholder="10" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Compra mínima S/</label>
                            <input type="number" step="0.01" name="min_order_amount" required value="50" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        </div>
                    </div>
                    <input type="text" name="description" placeholder="Descripción del cupón" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                    <button type="submit" class="w-full px-4 py-2 rounded-xl bg-slate-900 text-white font-bold">Crear Cupón</button>
                </form>

                <div class="divide-y divide-slate-100 text-xs pt-2 max-h-48 overflow-y-auto">
                    @foreach($coupons as $coupon)
                        <div class="py-2 flex items-center justify-between gap-2">
                            <div>
                                <strong>{{ $coupon->code }}</strong> ({{ $coupon->type === 'percent' ? $coupon->value.'%' : 'S/ '.$coupon->value }})
                                <p class="text-[11px] text-slate-400">Usos: {{ $coupon->used_count }}</p>
                            </div>
                            <form action="{{ route('admin.catalog.coupons.toggle', $coupon->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-2.5 py-1 rounded-full font-bold {{ $coupon->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $coupon->is_active ? 'Activo' : 'Pausado' }}
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
                <h2 class="font-extrabold text-[#0F2D4A]">Categorías ({{ $categories->count() }})</h2>
                <form action="{{ route('admin.catalog.categories.store') }}" method="POST" class="flex gap-2 text-xs">
                    @csrf
                    <input type="text" name="name" required placeholder="Nueva categoría..." class="flex-1 px-3 py-2 rounded-xl border border-slate-300">
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#1D70B8] text-white font-bold">Agregar</button>
                </form>
                <div class="flex flex-wrap gap-1.5 pt-1">
                    @foreach($categories as $cat)
                        <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px] font-semibold">
                            {{ $cat->name }} ({{ $cat->products_count }})
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Buscador y CRUD Completo de Productos --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h2 class="font-extrabold text-[#0F2D4A]">Maestro Completo de Productos ({{ $products->total() }} encontrados)</h2>
                <p class="text-xs text-slate-500">Vista Superadmin: muestra proveedor real asignado, costo de compra, precio de venta, margen, sello al cliente y acciones CRUD.</p>
            </div>
            @if(auth()->user()->isRole('super_admin'))
                <a href="{{ route('admin.vendors.index') }}" class="px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold hover:bg-emerald-100">
                    Gestionar Proveedores →
                </a>
            @endif
        </div>

        <form method="GET" action="{{ route('admin.catalog.index') }}" class="grid sm:grid-cols-2 lg:grid-cols-6 gap-2.5 text-xs bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar producto, SKU o proveedor..." class="lg:col-span-2 px-3 py-2 rounded-xl border border-slate-300 bg-white">
            <select name="category_id" class="px-3 py-2 rounded-xl border border-slate-300 bg-white">
                <option value="">Todas las categorías</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((int) request('category_id') === $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="institution_id" class="px-3 py-2 rounded-xl border border-slate-300 bg-white">
                <option value="">Todos los colegios</option>
                <option value="multi" @selected(request('institution_id') === 'multi')>Multi-Colegio</option>
                @foreach($institutions as $inst)
                    <option value="{{ $inst->id }}" @selected(request('institution_id') === (string) $inst->id)>{{ $inst->short_name }}</option>
                @endforeach
            </select>
            <select name="vendor_id" class="px-3 py-2 rounded-xl border border-slate-300 bg-white">
                <option value="">Todos los proveedores</option>
                <option value="direct" @selected(request('vendor_id') === 'direct')>Producción Directa</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}" @selected(request('vendor_id') === (string) $vendor->id)>{{ $vendor->business_name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-3 py-2 rounded-xl bg-slate-900 text-white font-bold">Filtrar</button>
                <a href="{{ route('admin.catalog.index') }}" class="px-3 py-2 rounded-xl border border-slate-300 bg-white text-slate-600 font-semibold">Limpiar</a>
            </div>
            <label class="lg:col-span-3 flex items-center gap-2 font-semibold text-slate-700 pt-1">
                <input type="checkbox" name="verified_only" value="1" @checked(request()->boolean('verified_only')) class="rounded text-emerald-600">
                <span>Solo productos con sello "Proveedor Verificado" al cliente</span>
            </label>
            <div class="lg:col-span-3 flex items-center gap-2 justify-end">
                <span class="text-slate-500 font-semibold">Estado:</span>
                <select name="estado" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                    <option value="">Todos</option>
                    <option value="active" @selected(request('estado') === 'active')>Activos</option>
                    <option value="inactive" @selected(request('estado') === 'inactive')>Ocultos</option>
                </select>
            </div>
        </form>

        <div class="space-y-4">
            @forelse($products as $product)
                @php
                    $profit = $product->profitAmount();
                    $margin = $product->marginPercent();
                @endphp
                <div class="rounded-2xl border {{ $product->is_active ? 'border-slate-200 bg-white' : 'border-rose-200 bg-rose-50/30' }} p-4 space-y-3">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <img src="{{ asset(ltrim($product->image_path ?? 'images/products/polo.jpg', '/')) }}" alt="{{ $product->name }}" class="w-16 h-16 rounded-xl object-cover border border-slate-200 shrink-0">
                            <div>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-bold">{{ $product->sku_prefix }}</span>
                                    <h3 class="font-extrabold text-slate-900">{{ $product->name }}</h3>
                                    @if(! $product->is_active)
                                        <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[10px] font-bold">Oculto en tienda</span>
                                    @endif
                                    @if($product->hasVerifiedVendorBadge())
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">✓ Cliente ve: Proveedor Verificado</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-semibold">Cliente sin sello verificado</span>
                                    @endif
                                </div>

                                <p class="text-xs text-slate-500 mt-1">
                                    <strong>{{ $product->category->name }}</strong>
                                    · {{ $product->institution?->short_name ?? 'Multi-Colegio' }}
                                    · <strong class="text-[#0F2D4A]">Proveedor (Superadmin): {{ $product->vendor?->business_name ?? 'Producción Directa JMSPERU' }}</strong>
                                    @if($product->vendor)
                                        <span class="text-[11px] {{ $product->vendor->is_verified ? 'text-emerald-700 font-bold' : 'text-amber-700' }}">
                                            ({{ $product->vendor->is_verified ? 'RUC Verificado ✓' : 'Pendiente verificación' }})
                                        </span>
                                    @endif
                                </p>

                                <div class="flex flex-wrap items-center gap-2 mt-2 text-xs">
                                    <span class="px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 font-bold">Compra: S/ {{ number_format((float) $product->purchase_price, 2) }}</span>
                                    <span class="px-2.5 py-1 rounded-lg bg-sky-50 border border-sky-200 text-[#0F2D4A] font-bold">Venta Público: S/ {{ number_format((float) $product->base_price, 2) }}</span>
                                    @if($product->member_price)
                                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold">Ex-Alumno: S/ {{ number_format((float) $product->member_price, 2) }}</span>
                                    @endif
                                    <span class="px-2.5 py-1 rounded-lg font-extrabold {{ $profit > 0 ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-700' }}">
                                        Utilidad: S/ {{ number_format($profit, 2) }} ({{ $margin }}%)
                                    </span>
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold">
                                        Stock Total: {{ $product->totalStock() }} uds
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 shrink-0 text-xs">
                            <a href="{{ route('admin.catalog.products.edit', $product->id) }}" class="px-3.5 py-2 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white font-bold">
                                Editar Producto / Variantes
                            </a>
                            <form action="{{ route('admin.catalog.products.toggle', $product->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-3 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 font-bold text-slate-700">
                                    {{ $product->is_active ? 'Ocultar' : 'Activar' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.catalog.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('¿Eliminar o archivar este producto del catálogo?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Stock rápido por variante --}}
                    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-2 pt-2 border-t border-slate-100">
                        @foreach($product->variants as $variant)
                            <form action="{{ route('admin.catalog.variants.stock', $variant->id) }}" method="POST" class="flex items-center justify-between gap-2 bg-slate-50 rounded-lg p-2.5 text-xs border {{ $variant->isLowStock() ? 'border-amber-300 bg-amber-50/40' : 'border-slate-100' }}">
                                @csrf
                                @method('PATCH')
                                <div class="min-w-0">
                                    <p class="font-bold truncate">{{ $variant->label() }}</p>
                                    <p class="text-[10px] text-slate-500">{{ $variant->sku }}</p>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <input type="number" name="stock" value="{{ $variant->stock }}" min="0" class="w-14 px-1.5 py-1 rounded border border-slate-300 bg-white text-center font-bold">
                                    <button type="submit" class="px-2 py-1 rounded bg-[#1D70B8] text-white font-bold">OK</button>
                                </div>
                            </form>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-slate-200 p-8 text-center text-sm text-slate-500">
                    No se encontraron productos con los filtros actuales.
                </div>
            @endforelse
        </div>

        <div>{{ $products->links() }}</div>
    </div>
</div>
@endsection
