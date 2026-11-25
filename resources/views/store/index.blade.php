@extends('layouts.app')

@section('title', 'Tienda en Línea — MiPromo Egresados Store')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-[#1D70B8]">Catálogo Personalizable + Descuentos por Volumen</span>
            <h1 class="text-3xl font-extrabold text-[#0F2D4A] mt-1">Tienda de Productos Promocionales</h1>
            <p class="text-sm text-slate-600 mt-1">Prendas, recuerdos y accesorios con sello Proveedor Verificado. Usa <strong>PROMO10</strong> (-10%) o <strong>EGRESADO20</strong> (-S/ 20).</p>
        </div>
        @guest
            <button type="button" @click="$dispatch('open-register-modal')" class="px-4 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-[#0F2D4A] text-xs font-extrabold transition">
                Regístrate en 15s y Activa Tarifa Ex-Alumno →
            </button>
        @endguest
    </div>

    <div class="grid lg:grid-cols-4 gap-8">
        {{-- Filters --}}
        <aside class="lg:col-span-1">
            <form method="GET" action="{{ route('store.index') }}" class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4 sticky top-24">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Buscar</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Casaca, polo, taza..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Categoría</label>
                    <select name="categoria" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('categoria') === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Institución</label>
                    <select name="colegio" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        <option value="">Todas las instituciones</option>
                        <option value="multi" @selected(request('colegio') === 'multi')>Personalizable Multi-Colegio</option>
                        @foreach($institutions as $inst)
                            <option value="{{ $inst->slug }}" @selected(request('colegio') === $inst->slug)>{{ $inst->short_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ordenar por</label>
                    <select name="orden" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        <option value="">Destacados</option>
                        <option value="precio_asc" @selected(request('orden') === 'precio_asc')>Menor precio</option>
                        <option value="precio_desc" @selected(request('orden') === 'precio_desc')>Mayor precio</option>
                    </select>
                </div>

                <label class="flex items-center gap-2 text-xs text-slate-700">
                    <input type="checkbox" name="preventa" value="1" @checked(request()->boolean('preventa')) class="rounded border-slate-300 text-[#1D70B8]">
                    <span>Solo productos bajo pedido</span>
                </label>

                <label class="flex items-center gap-2 text-xs font-semibold text-emerald-800">
                    <input type="checkbox" name="verificado" value="1" @checked(request()->boolean('verificado')) class="rounded border-slate-300 text-emerald-600">
                    <span>Solo Proveedor Verificado ✓</span>
                </label>

                <div class="flex gap-2 pt-2">
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white text-xs font-bold transition">Aplicar</button>
                    <a href="{{ route('store.index') }}" class="px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-600">Limpiar</a>
                </div>
            </form>
        </aside>

        {{-- Products Grid --}}
        <div class="lg:col-span-3">
            <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($products as $product)
                    <x-product-card :product="$product" />
                @empty
                    <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-10 text-center text-slate-500">
                        No encontramos productos con los filtros seleccionados.
                    </div>
                @endforelse
            </div>

            <div class="mt-8">{{ $products->links() }}</div>
        </div>
    </div>
</div>
@endsection
