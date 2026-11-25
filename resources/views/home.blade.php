@extends('layouts.app')

@section('title', 'MiPromo | Tienda Online de Egresados y Promociones — Grupo JMSPERU')

@section('content')
    {{-- 1. HERO COMERCIAL STORE-FIRST (Carga directamente en la tienda de productos) --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-[#0F2D4A] via-[#133B61] to-[#1D70B8] text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-9 lg:py-12 grid lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-7 space-y-4">
                <div class="inline-flex flex-wrap items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-xs font-bold text-amber-300">
                    <span>⚡ Compra Online en 2 Minutos · Piura · Chiclayo · Trujillo · Lima · Todo el Perú</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
                    Tu Casaca, Polo y Recuerdo Oficial<br>
                    <span class="text-sky-300">personalizado y directo a tu puerta.</span>
                </h1>

                <p class="text-sm sm:text-base text-slate-200 max-w-2xl leading-relaxed">
                    Elige tu colegio o producto multi-colegio, personaliza talla y bordado al instante y paga seguro con <strong class="text-white">Yape, Plin, Tarjeta o Transferencia</strong> con comprobante SUNAT.
                </p>

                <form action="{{ route('home') }}#tienda-catalogo" method="GET" class="flex flex-col sm:flex-row gap-2 max-w-xl bg-white/10 p-2 rounded-2xl backdrop-blur border border-white/15">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="¿Qué buscas hoy? Casaca varsity, polo pima, taza, Guadalupe, Piura..." class="flex-1 px-4 py-3 rounded-xl bg-white text-slate-900 text-sm focus:outline-none">
                    <button type="submit" class="px-6 py-3 rounded-xl bg-amber-400 hover:bg-amber-300 text-[#0F2D4A] font-extrabold text-sm transition shrink-0">
                        Buscar Productos
                    </button>
                </form>

                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <a href="#tienda-catalogo" class="px-5 py-2.5 rounded-xl bg-white text-[#0F2D4A] font-extrabold text-xs sm:text-sm hover:bg-sky-50 transition">
                        Comprar en Tienda ↓
                    </a>
                    @guest
                        <button type="button" @click="$dispatch('open-register-modal')" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-extrabold text-xs sm:text-sm transition shadow-sm">
                            Regístrate en 15s y Ahorra hasta 20% →
                        </button>
                    @endguest
                    <a href="{{ route('info.landing') }}" class="px-4 py-2.5 rounded-xl border border-white/30 text-white font-semibold text-xs sm:text-sm hover:bg-white/10 transition">
                        ¿Cómo comprar seguro?
                    </a>
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="bg-white text-slate-900 rounded-3xl p-5 sm:p-6 shadow-2xl space-y-4 border border-white/20">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <img src="{{ asset('images/logo_jmsperu.png') }}" alt="Grupo JMSPERU" class="h-9 w-auto">
                        <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-extrabold">✓ Proveedores Verificados</span>
                    </div>

                    <div class="rounded-2xl bg-amber-50 border border-amber-200 p-3.5 text-xs space-y-1">
                        <p class="font-extrabold text-[#0F2D4A]">🎁 Cupón Activo Hoy: <span class="px-2 py-0.5 rounded bg-amber-400 text-[#0F2D4A]">PROMO10</span> (-10%) o <span class="px-2 py-0.5 rounded bg-[#0F2D4A] text-white">EGRESADO20</span> (-S/ 20)</p>
                        <p class="text-slate-600">Además: <strong>-10%</strong> llevando 12+ uds y <strong>-18%</strong> en lotes de 30+ uds para promociones.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 text-center">
                        <div class="rounded-xl bg-slate-50 p-3 border border-slate-100">
                            <p class="text-xl font-black text-[#0F2D4A]">{{ $stats['institutions'] }}</p>
                            <p class="text-[11px] text-slate-500 font-semibold">Colegios Oficiales</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3 border border-slate-100">
                            <p class="text-xl font-black text-[#1D70B8]">{{ $stats['alumni_verified'] }}</p>
                            <p class="text-[11px] text-slate-500 font-semibold">Egresados Verificados</p>
                        </div>
                    </div>

                    @guest
                        <a href="{{ route('register') }}" class="block text-center w-full py-2.5 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white text-xs font-extrabold transition">
                            Crear Cuenta Gratis en 15s (Tarifa Ex-Alumno) →
                        </a>
                    @else
                        <a href="{{ route('account.index') }}" class="block text-center w-full py-2.5 rounded-xl bg-[#0F2D4A] text-white text-xs font-extrabold">
                            Mi Cuenta · Tarifa Ex-Alumno Activa ✓
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    {{-- 2. TIENDA PRINCIPAL EN LA RAÍZ (/) — SELECTOR RÁPIDO + CATÁLOGO COMPLETO --}}
    <section id="tienda-catalogo" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        {{-- Pills de Colegios con Insignia PNG para filtrar la tienda al instante --}}
        <div class="space-y-2.5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-xl sm:text-2xl font-extrabold text-[#0F2D4A]">Catálogo Oficial Personalizable</h2>
                <a href="{{ route('store.index') }}" class="text-xs font-bold text-[#1D70B8] hover:underline">Vista avanzada de tienda ({{ $products->total() }} productos) →</a>
            </div>
            <div class="flex items-center gap-2 overflow-x-auto pb-2">
                <a href="{{ route('home') }}#tienda-catalogo"
                   class="shrink-0 px-3.5 py-2 rounded-full text-xs font-bold border transition {{ !request('colegio') ? 'bg-[#0F2D4A] text-white border-[#0F2D4A]' : 'bg-white text-slate-700 border-slate-200 hover:border-[#1D70B8]' }}">
                    Todos los colegios
                </a>
                <a href="{{ route('home', ['colegio' => 'multi']) }}#tienda-catalogo"
                   class="shrink-0 px-3.5 py-2 rounded-full text-xs font-bold border transition {{ request('colegio') === 'multi' ? 'bg-[#1D70B8] text-white border-[#1D70B8]' : 'bg-white text-slate-700 border-slate-200 hover:border-[#1D70B8]' }}">
                    Multi-Colegio
                </a>
                @foreach($institutions as $inst)
                    <a href="{{ route('home', ['colegio' => $inst->slug]) }}#tienda-catalogo"
                       class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border transition {{ request('colegio') === $inst->slug ? 'text-white shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:border-[#1D70B8]' }}"
                       @if(request('colegio') === $inst->slug) style="background-color: {{ $inst->primary_color }}; border-color: {{ $inst->primary_color }}" @endif>
                        @if($inst->logo_path)
                            <img src="{{ asset(ltrim($inst->logo_path, '/')) }}" alt="{{ $inst->short_name }}" class="w-4 h-4 object-contain bg-white rounded-full">
                        @endif
                        <span>{{ $inst->short_name }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Chips de Categorías --}}
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('home', request()->except('categoria', 'page')) }}#tienda-catalogo"
               class="px-3.5 py-1.5 rounded-full text-xs font-bold border transition {{ !request('categoria') ? 'bg-[#1D70B8] text-white border-[#1D70B8]' : 'bg-white border-slate-200 text-slate-700 hover:border-[#1D70B8]' }}">
                Todas las categorías
            </a>
            @foreach($categories as $category)
                <a href="{{ route('home', array_merge(request()->except('page'), ['categoria' => $category->slug])) }}#tienda-catalogo"
                   class="px-3.5 py-1.5 rounded-full text-xs font-bold border transition {{ request('categoria') === $category->slug ? 'bg-[#1D70B8] text-white border-[#1D70B8]' : 'bg-white border-slate-200 text-slate-700 hover:border-[#1D70B8]' }}">
                    {{ $category->name }} ({{ $category->products_count }})
                </a>
            @endforeach
        </div>

        {{-- Sidebar Filtros + Grid de Productos --}}
        <div class="grid lg:grid-cols-4 gap-7">
            <aside class="lg:col-span-1">
                <form method="GET" action="{{ route('home') }}#tienda-catalogo" class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4 sticky top-24">
                    <h3 class="text-sm font-extrabold text-[#0F2D4A]">Filtrar Productos</h3>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Palabra clave</label>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Casaca, polo, llavero..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
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
                        <label class="block text-xs font-bold text-slate-700 mb-1">Colegio</label>
                        <select name="colegio" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                            <option value="">Todos los colegios</option>
                            <option value="multi" @selected(request('colegio') === 'multi')>Multi-Colegio</option>
                            @foreach($institutions as $inst)
                                <option value="{{ $inst->slug }}" @selected(request('colegio') === $inst->slug)>{{ $inst->short_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Ordenar por</label>
                        <select name="orden" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                            <option value="">Más vendidos / Destacados</option>
                            <option value="precio_asc" @selected(request('orden') === 'precio_asc')>Menor precio</option>
                            <option value="precio_desc" @selected(request('orden') === 'precio_desc')>Mayor precio</option>
                        </select>
                    </div>

                    <label class="flex items-center gap-2 text-xs font-semibold text-emerald-800">
                        <input type="checkbox" name="verificado" value="1" @checked(request()->boolean('verificado')) class="rounded border-slate-300 text-emerald-600">
                        <span>Solo Proveedor Verificado ✓</span>
                    </label>

                    <label class="flex items-center gap-2 text-xs text-slate-700">
                        <input type="checkbox" name="preventa" value="1" @checked(request()->boolean('preventa')) class="rounded border-slate-300 text-[#1D70B8]">
                        <span>Solo bajo pedido</span>
                    </label>

                    <div class="flex gap-2 pt-1">
                        <button type="submit" class="flex-1 py-2.5 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white text-xs font-bold transition">Aplicar</button>
                        <a href="{{ route('home') }}#tienda-catalogo" class="px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-600">Limpiar</a>
                    </div>
                </form>
            </aside>

            <div class="lg:col-span-3 space-y-6">
                <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @forelse($products as $product)
                        <x-product-card :product="$product" />
                    @empty
                        <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-10 text-center text-slate-500">
                            No encontramos productos con esos filtros. <a href="{{ route('home') }}" class="text-[#1D70B8] font-bold underline">Ver todo el catálogo</a>.
                        </div>
                    @endforelse
                </div>

                <div>{{ $products->links() }}</div>
            </div>
        </div>
    </section>

    {{-- 3. PACKS / COMBOS EN 1 CLIC --}}
    @if(!empty($bundles))
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">
            <div class="rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 space-y-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-600">Ahorra Comprando en Combo</span>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-[#0F2D4A]">Packs Listos para tu Promoción</h2>
                    </div>
                    <a href="{{ route('info.landing') }}" class="text-xs font-bold text-[#1D70B8] hover:underline">Ver ventajas de comprar online →</a>
                </div>

                <div class="grid md:grid-cols-2 gap-5">
                    @foreach($bundles as $bundle)
                        <div class="rounded-2xl bg-slate-50 border border-slate-200 p-5 flex flex-col justify-between gap-4">
                            <div>
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">{{ $bundle['savings_label'] }}</span>
                                <h3 class="mt-2 font-extrabold text-[#0F2D4A]">{{ $bundle['title'] }}</h3>
                                <p class="text-xs text-slate-500 mt-1">{{ $bundle['subtitle'] }}</p>
                                <div class="flex flex-wrap gap-2 mt-3">
                                    @foreach($bundle['products'] as $bp)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-semibold">
                                            <img src="{{ asset(ltrim($bp->image_path ?? 'images/products/polo.jpg', '/')) }}" alt="{{ $bp->name }}" class="w-4 h-4 rounded object-cover">
                                            {{ $bp->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-3 pt-3 border-t border-slate-200">
                                <div>
                                    <span class="text-[11px] text-slate-500 block">Valor por separado</span>
                                    <span class="text-lg font-black text-[#0F2D4A]">S/ {{ number_format($bundle['regular_total'], 2) }}</span>
                                </div>
                                <form action="{{ route('cart.add_pack') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="bundle_code" value="{{ $bundle['code'] }}">
                                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white text-xs font-extrabold transition">
                                        Agregar Pack + Cupón →
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 4. BANDA INFORMATIVA AGRESIVA DE COMPRA Y REGISTRO RÁPIDO --}}
    <section class="bg-[#0F2D4A] text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-300">Compra Sin Complicaciones</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold mt-1">¿Por qué comprar y registrarte hoy en MiPromo?</h2>
                </div>
                <div class="flex flex-wrap gap-3">
                    @guest
                        <button type="button" @click="$dispatch('open-register-modal')" class="px-5 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-[#0F2D4A] font-extrabold text-xs">
                            Registrarme Gratis en 15s
                        </button>
                    @endguest
                    <a href="{{ route('info.landing') }}" class="px-5 py-2.5 rounded-xl border border-white/25 text-white font-bold text-xs hover:bg-white/10">
                        Ver Guía Completa de Compra Segura →
                    </a>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div class="rounded-2xl bg-white/10 border border-white/15 p-5 space-y-1.5">
                    <p class="text-base font-extrabold text-amber-300">1. Regístrate en 15 seg</p>
                    <p class="text-white/80">Solo nombre, correo y clave. Activas al instante precios exclusivos de Ex-Alumno (hasta 20% menos).</p>
                </div>
                <div class="rounded-2xl bg-white/10 border border-white/15 p-5 space-y-1.5">
                    <p class="text-base font-extrabold text-sky-300">2. Pago Rápido Peruano</p>
                    <p class="text-white/80">Yape/Plin (<a href="https://wa.me/51959199368?text={{ urlencode('Hola Grupo JMSPERU, consulta sobre pago Yape/Plin.') }}" target="_blank" rel="noopener noreferrer" class="underline font-bold text-emerald-300">959199368</a>), Tarjeta con aprobación inmediata o Transferencia bancaria.</p>
                </div>
                <div class="rounded-2xl bg-white/10 border border-white/15 p-5 space-y-1.5">
                    <p class="text-base font-extrabold text-emerald-300">3. Garantía + SUNAT</p>
                    <p class="text-white/80">Boleta o Factura electrónica real, sello Proveedor Verificado y gestión de cambio de talla.</p>
                </div>
                <div class="rounded-2xl bg-white/10 border border-white/15 p-5 space-y-1.5">
                    <p class="text-base font-extrabold text-white">4. Envíos a Todo el Perú</p>
                    <p class="text-white/80">Olva Courier, Shalom, delivery Lima o entrega consolidada gratuita con tu delegado escolar.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Banners de Temporada --}}
    @if(isset($banners) && $banners->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid md:grid-cols-2 gap-6">
                @foreach($banners as $banner)
                    <div class="rounded-3xl p-7 text-white flex flex-col justify-between gap-4 shadow-sm" style="background-color: {{ $banner->bg_color }}">
                        <div>
                            @if($banner->badge_text)
                                <span class="px-2.5 py-1 rounded-full bg-white/15 text-[11px] font-bold uppercase">{{ $banner->badge_text }}</span>
                            @endif
                            <h3 class="mt-3 text-xl font-extrabold">{{ $banner->title }}</h3>
                            <p class="mt-1 text-sm text-white/85">{{ $banner->subtitle }}</p>
                        </div>
                        <div>
                            <a href="{{ $banner->cta_url }}" class="inline-flex px-4 py-2 rounded-xl bg-white text-[#0F2D4A] text-xs font-bold hover:bg-sky-50 transition">{{ $banner->cta_label }} →</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 5. INSTITUCIONES OFICIALES (Colores e Insignias PNG) --}}
    <section id="colegios-activos" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="{ selectedCity: 'all' }">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-[#1D70B8]">Colegios Oficialmente Dados de Alta</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-[#0F2D4A] mt-1">Tiendas Oficiales por Institución</h2>
                <p class="text-sm text-slate-600 mt-1">Instituciones históricas con más de 10 años de trayectoria, delegados activos e identidad visual propia.</p>
            </div>
            <a href="{{ route('institutions.index') }}" class="text-sm font-bold text-[#1D70B8] hover:underline shrink-0">Directorio completo de colegios →</a>
        </div>

        <div class="flex flex-wrap items-center gap-2 mb-6">
            <button type="button"
                    @click="selectedCity = 'all'"
                    :class="selectedCity === 'all' ? 'bg-[#0F2D4A] text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:border-[#1D70B8]'"
                    class="px-4 py-2 rounded-full text-xs font-bold transition">
                Todas las ciudades ({{ $institutions->count() }})
            </button>
            @foreach($cities as $city)
                <button type="button"
                        @click="selectedCity = '{{ $city }}'"
                        :class="selectedCity === '{{ $city }}' ? 'bg-[#1D70B8] text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:border-[#1D70B8]'"
                        class="px-4 py-2 rounded-full text-xs font-bold transition">
                    {{ $city }} ({{ $institutions->where('city', $city)->count() }})
                </button>
            @endforeach
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($institutions as $inst)
                <div x-show="selectedCity === 'all' || selectedCity === '{{ $inst->city }}'" x-transition.opacity>
                    <x-institution-card :institution="$inst" />
                </div>
            @endforeach
        </div>
    </section>

    {{-- Campañas Grupales --}}
    <section class="bg-slate-100/80 border-y border-slate-200 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-600">Preventas Colaborativas</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#0F2D4A] mt-1">Campañas Grupales Activas (Reserva con 50%)</h2>
                </div>
                <a href="{{ route('campaigns.index') }}" class="text-sm font-bold text-[#1D70B8] hover:underline">Explorar todas las campañas →</a>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                @foreach($campaigns as $campaign)
                    <x-campaign-card :campaign="$campaign" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- Noticias y Guías --}}
    @if(isset($blogPosts) && $blogPosts->isNotEmpty())
        <section class="bg-white border-t border-slate-200 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#1D70B8]">Comunidad e Historias</span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-[#0F2D4A] mt-1">Noticias y Guías MiPromo</h2>
                    </div>
                    <a href="{{ route('blog.index') }}" class="text-sm font-bold text-[#1D70B8] hover:underline">Ver todas las noticias →</a>
                </div>
                <div class="grid md:grid-cols-3 gap-6">
                    @foreach($blogPosts as $post)
                        <a href="{{ route('blog.show', $post->slug) }}" class="group rounded-2xl border border-slate-200 overflow-hidden hover:shadow-md transition flex flex-col bg-slate-50/40">
                            <img src="{{ asset(ltrim($post->image_path ?? 'images/products/anuario.jpg', '/')) }}" alt="{{ $post->title }}" class="h-40 w-full object-cover">
                            <div class="p-5 flex-1 flex flex-col justify-between gap-3">
                                <div>
                                    <span class="text-[11px] font-bold uppercase text-[#1D70B8]">{{ $post->category }}</span>
                                    <h3 class="mt-1 font-bold text-slate-900 group-hover:text-[#1D70B8]">{{ $post->title }}</h3>
                                    <p class="mt-1 text-xs text-slate-500 line-clamp-2">{{ $post->excerpt }}</p>
                                </div>
                                <span class="text-xs font-bold text-[#0F2D4A]">Leer más →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
