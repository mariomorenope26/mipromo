@extends('layouts.app')

@section('title', $product->name.' — Tienda MiPromo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">
    <nav class="text-xs text-slate-500 flex items-center gap-2">
        <a href="{{ route('home') }}" class="hover:text-[#1D70B8]">Inicio</a>
        <span>/</span>
        <a href="{{ route('store.index') }}" class="hover:text-[#1D70B8]">Tienda</a>
        <span>/</span>
        <span class="text-slate-900 font-semibold">{{ $product->name }}</span>
    </nav>

    <div class="grid lg:grid-cols-12 gap-10" x-data="{ selectedVariant: {{ $product->variants->first()?->id ?? 'null' }} }">
        <div class="lg:col-span-5">
            <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden aspect-[4/3] shadow-sm">
                <img src="{{ asset(ltrim($product->image_path ?? 'images/products/polo.jpg', '/')) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
            </div>
        </div>

        <div class="lg:col-span-7 space-y-6">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full bg-sky-50 text-[#1D70B8] text-xs font-bold">{{ $product->category->name }}</span>
                    @if($product->institution)
                        <a href="{{ route('institutions.show', $product->institution->slug) }}" class="px-2.5 py-1 rounded-full text-xs font-bold text-white" style="background-color: {{ $product->institution->primary_color }}">
                            {{ $product->institution->short_name }}
                        </a>
                    @endif
                    @if($product->hasVerifiedVendorBadge())
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
                            ✓ Proveedor Verificado — Garantía MiPromo
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                            Producción Oficial MiPromo
                        </span>
                    @endif
                </div>

                <h1 class="mt-3 text-2xl sm:text-3xl font-extrabold text-[#0F2D4A]">{{ $product->name }}</h1>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $product->description }}</p>
            </div>

            @php($savings = $product->member_price && (float) $product->member_price < (float) $product->base_price ? round((float) $product->base_price - (float) $product->member_price, 2) : 0)
            <div class="rounded-2xl bg-slate-100 p-4 space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs text-slate-500">Precio público</p>
                        <p class="text-3xl font-extrabold text-[#0F2D4A]">S/ {{ number_format($product->base_price, 2) }}</p>
                    </div>
                    @if($product->member_price)
                        <div class="rounded-xl bg-emerald-100/80 border border-emerald-200 px-4 py-2">
                            <p class="text-[11px] font-bold uppercase text-emerald-800">Tarifa Especial Ex-Alumno</p>
                            <p class="text-xl font-extrabold text-emerald-700">S/ {{ number_format($product->member_price, 2) }}</p>
                        </div>
                    @endif
                </div>

                @guest
                    @if($savings > 0)
                        <div class="rounded-xl bg-amber-50 border border-amber-200 px-3.5 py-2 flex flex-wrap items-center justify-between gap-2 text-xs">
                            <span class="font-bold text-amber-900">⚡ Ahorra S/ {{ number_format($savings, 2) }} por unidad activando tu cuenta en 15 seg.</span>
                            <button type="button" @click="$dispatch('open-register-modal')" class="px-3 py-1 rounded-lg bg-[#0F2D4A] text-white font-bold">Registrarme Gratis →</button>
                        </div>
                    @endif
                @endguest

                {{-- Precios Escalonados por Volumen --}}
                <div class="grid grid-cols-3 gap-2 text-center text-[11px] pt-2 border-t border-slate-200">
                    <div class="rounded-lg bg-white p-2 border border-slate-200">
                        <p class="text-slate-500">1 – 11 uds</p>
                        <p class="font-extrabold text-[#0F2D4A]">S/ {{ number_format($product->base_price, 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-2 border border-sky-200">
                        <p class="text-[#1D70B8] font-bold">12 – 29 uds (-10%)</p>
                        <p class="font-extrabold text-[#0F2D4A]">S/ {{ number_format($product->base_price * 0.90, 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-emerald-50 p-2 border border-emerald-200">
                        <p class="text-emerald-800 font-bold">30+ uds (-18%)</p>
                        <p class="font-extrabold text-emerald-800">S/ {{ number_format($product->base_price * 0.82, 2) }}</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('cart.add') }}" method="POST" class="space-y-5 bg-white rounded-2xl border border-slate-200 p-6">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Selecciona talla / color / material</label>
                    <div class="grid sm:grid-cols-2 gap-2.5">
                        @foreach($product->variants as $variant)
                            <label class="cursor-pointer rounded-xl border p-3 flex items-center justify-between gap-2 transition"
                                   :class="selectedVariant == {{ $variant->id }} ? 'border-[#1D70B8] bg-sky-50 ring-2 ring-[#1D70B8]/20' : 'border-slate-200 hover:border-slate-300'">
                                <div class="flex items-center gap-2.5">
                                    <input type="radio" name="variant_id" value="{{ $variant->id }}" x-model="selectedVariant" class="text-[#1D70B8]">
                                    <div>
                                        <p class="text-xs font-bold text-slate-900">{{ $variant->label() }}</p>
                                        <p class="text-[11px] text-slate-500">SKU: {{ $variant->sku }}</p>
                                    </div>
                                </div>
                                <span class="text-[11px] font-bold {{ $variant->isLowStock() ? 'text-amber-700' : 'text-emerald-700' }}">
                                    {{ $variant->stock }} uds.
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                @if($product->is_customizable)
                    <div class="grid sm:grid-cols-3 gap-3 pt-2 border-t border-slate-100">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Escudo / Colegio</label>
                            <input type="text" name="custom_institution" value="{{ $product->institution?->short_name }}" placeholder="Ej. Guadalupe" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Año de Promoción</label>
                            <input type="text" name="custom_year" placeholder="Ej. 2006" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre / Apellido bordado</label>
                            <input type="text" name="custom_name" placeholder="Ej. J. MORENO" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                    </div>
                @endif

                <div class="flex flex-wrap items-end gap-3 pt-2">
                    <div class="w-24">
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1">Cantidad</label>
                        <input type="number" name="quantity" value="1" min="1" max="100" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm font-bold">
                    </div>
                    <button type="submit" class="flex-1 py-3 px-5 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white font-bold text-sm shadow-sm transition">
                        Agregar al Carrito
                    </button>
                    <button type="submit" name="buy_now" value="1" class="flex-1 py-3 px-5 rounded-xl bg-amber-400 hover:bg-amber-300 text-[#0F2D4A] font-extrabold text-sm shadow-sm transition">
                        Comprar Ahora →
                    </button>
                </div>
            </form>

            <div class="flex flex-wrap items-center justify-between gap-3">
                @auth
                    <form action="{{ route('products.wishlist', $product->slug) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 text-xs font-bold {{ $inWishlist ? 'text-rose-600' : 'text-slate-600 hover:text-[#1D70B8]' }}">
                            <span>{{ $inWishlist ? '♥ Guardado en tu lista de deseos (quitar)' : '♡ Guardar en lista de deseos' }}</span>
                        </button>
                    </form>
                @endauth
                <a href="https://wa.me/51959199368?text={{ urlencode('Hola Grupo JMSPERU, quiero cotizar o comprar: '.$product->name.' ('.url()->current().')') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-xs font-extrabold text-emerald-700 hover:underline">
                    💬 Cotizar lote / Pedir por WhatsApp (959199368) ↗
                </a>
            </div>
        </div>
    </div>

    {{-- Reseñas --}}
    <section class="bg-white rounded-2xl border border-slate-200 p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-extrabold text-[#0F2D4A]">Reseñas y Calificaciones ({{ $product->reviews->count() }})</h2>
            <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-bold">★ {{ $product->averageRating() }} / 5.0</span>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            @forelse($product->reviews as $review)
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                    <div class="flex items-center justify-between text-xs">
                        <strong>{{ $review->user->name }}</strong>
                        <span class="text-amber-600 font-bold">{{ str_repeat('★', $review->rating) }}</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-600">{{ $review->comment }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">Sé el primero en calificar este producto.</p>
            @endforelse
        </div>

        @auth
            <form action="{{ route('products.review', $product->slug) }}" method="POST" class="pt-4 border-t border-slate-100 grid sm:grid-cols-4 gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Calificación</label>
                    <select name="rating" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        <option value="5">5 estrellas</option>
                        <option value="4">4 estrellas</option>
                        <option value="3">3 estrellas</option>
                        <option value="2">2 estrellas</option>
                        <option value="1">1 estrella</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tu comentario</label>
                    <input type="text" name="comment" required placeholder="¿Qué te pareció la calidad y el acabado?" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                </div>
                <button type="submit" class="py-2.5 px-4 rounded-xl bg-slate-900 text-white text-xs font-bold">Publicar reseña</button>
            </form>
        @endauth
    </section>

    @if($relatedProducts->isNotEmpty())
        <section>
            <h2 class="text-xl font-extrabold text-[#0F2D4A] mb-5">Productos Relacionados</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $rel)
                    <x-product-card :product="$rel" />
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
