@extends('layouts.app')

@section('title', 'Carrito de Compras — MiPromo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-[#1D70B8]">Paso 1 de 3 · Carrito</span>
            <h1 class="text-3xl font-extrabold text-[#0F2D4A]">Tu Carrito de Compras</h1>
        </div>
        <a href="{{ route('home') }}#tienda-catalogo" class="text-xs font-bold text-[#1D70B8] hover:underline">← Seguir agregando productos</a>
    </div>

    @if(empty($items))
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center space-y-4">
            <p class="text-lg font-bold text-slate-800">Tu carrito está vacío</p>
            <p class="text-sm text-slate-500">Explora productos personalizables de tu colegio o únete a una campaña grupal.</p>
            <div class="flex justify-center gap-3">
                <a href="{{ route('home') }}#tienda-catalogo" class="px-5 py-2.5 rounded-xl bg-[#1D70B8] text-white text-sm font-bold">Ir a la Tienda</a>
                <a href="{{ route('campaigns.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold">Ver Campañas</a>
            </div>
        </div>
    @else
        <div class="grid lg:grid-cols-12 gap-8">
            <div class="lg:col-span-8 space-y-4">
                @foreach($items as $item)
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <img src="{{ asset(ltrim($item['image_path'] ?? 'images/products/polo.jpg', '/')) }}" alt="{{ $item['product_name'] }}" class="w-20 h-20 rounded-xl bg-slate-50 object-cover border border-slate-100 shrink-0">
                            <div>
                                <a href="{{ route('products.show', $item['product_slug']) }}" class="font-bold text-slate-900 hover:text-[#1D70B8]">{{ $item['product_name'] }}</a>
                                <p class="text-xs text-slate-500 mt-0.5">Variante: {{ $item['variant_label'] }}</p>
                                @if(!empty($item['customization']))
                                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                                        @foreach($item['customization'] as $ck => $cv)
                                            <span class="text-[11px] px-2 py-0.5 rounded bg-sky-50 text-[#1D70B8] font-medium">{{ ucfirst(str_replace('_', ' ', $ck)) }}: {{ $cv }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                    <p class="text-sm font-bold text-[#0F2D4A]">S/ {{ number_format($item['unit_price'], 2) }} c/u</p>
                                    @if(!empty($item['volume_discount_percent']))
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold">-{{ $item['volume_discount_percent'] }}% por volumen</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-3">
                            <form action="{{ route('cart.update', $item['key']) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="0" max="100" class="w-16 px-2.5 py-1.5 rounded-lg border border-slate-300 text-sm font-bold text-center">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-semibold">Actualizar</button>
                            </form>

                            <form action="{{ route('cart.remove', $item['key']) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-lg text-rose-600 hover:bg-rose-50 text-xs font-semibold">Quitar</button>
                            </form>
                        </div>
                    </div>
                @endforeach

                {{-- Cross-Sell: Completa tu recuerdo en 1 clic --}}
                @if(isset($crossSellProducts) && $crossSellProducts->isNotEmpty())
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4">
                        <div>
                            <h2 class="text-sm font-extrabold text-[#0F2D4A]">Completa tu pedido antes de pagar (1 clic)</h2>
                            <p class="text-xs text-slate-500">Llaveros, tazas y accesorios favoritos para alcanzar el cupón <strong>EGRESADO20 (-S/ 20)</strong>.</p>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-3">
                            @foreach($crossSellProducts as $cs)
                                @php($csVar = $cs->variants->first())
                                <div class="rounded-xl bg-slate-50 border border-slate-200 p-3 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img src="{{ asset(ltrim($cs->image_path ?? 'images/products/llavero.jpg', '/')) }}" alt="{{ $cs->name }}" class="w-12 h-12 rounded-lg object-cover border border-slate-200 shrink-0">
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-slate-900 truncate">{{ $cs->name }}</p>
                                            <p class="text-xs font-extrabold text-[#1D70B8]">S/ {{ number_format($cs->base_price, 2) }}</p>
                                        </div>
                                    </div>
                                    @if($csVar)
                                        <form action="{{ route('cart.add') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $cs->id }}">
                                            <input type="hidden" name="variant_id" value="{{ $csVar->id }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#0F2D4A] hover:bg-[#1D70B8] text-white text-xs font-bold shrink-0">+ Agregar</button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="lg:col-span-4 space-y-5">
                <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4 sticky top-24">
                    <h2 class="text-lg font-extrabold text-[#0F2D4A]">Resumen del Pedido</h2>

                    {{-- Barra de incentivo de cupón --}}
                    @if($subtotal < 120)
                        <div class="rounded-xl bg-amber-50 border border-amber-200 p-3 text-xs text-amber-900">
                            Agrega <strong>S/ {{ number_format(120 - $subtotal, 2) }}</strong> más para activar el cupón <strong>EGRESADO20</strong> (-S/ 20.00).
                        </div>
                    @elseif(! $coupon)
                        <form action="{{ route('cart.coupon') }}" method="POST" class="rounded-xl bg-emerald-50 border border-emerald-200 p-3 flex items-center justify-between gap-2 text-xs">
                            @csrf
                            <input type="hidden" name="code" value="EGRESADO20">
                            <span class="font-bold text-emerald-900">🎁 ¡Calificas para -S/ 20.00!</span>
                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-700 text-white font-bold">Aplicar EGRESADO20</button>
                        </form>
                    @endif

                    <form action="{{ route('cart.coupon') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="code" value="{{ $coupon?->code }}" placeholder="Cupón (ej. PROMO10)" class="flex-1 px-3 py-2 rounded-xl border border-slate-300 text-sm uppercase">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">Aplicar</button>
                    </form>
                    <p class="text-[11px] text-slate-500">Cupones activos: <code>PROMO10</code>, <code>EGRESADO20</code>, <code>BODASPLATA</code></p>

                    <div class="space-y-2 pt-3 border-t border-slate-100 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-600">Subtotal</span>
                            <span class="font-semibold">S/ {{ number_format($subtotal, 2) }}</span>
                        </div>
                        @if($discount > 0)
                            <div class="flex justify-between text-emerald-700">
                                <span>Descuento ({{ $coupon->code }})</span>
                                <span class="font-bold">- S/ {{ number_format($discount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-base font-extrabold text-[#0F2D4A] pt-2 border-t border-slate-100">
                            <span>Total estimado</span>
                            <span>S/ {{ number_format($estimatedTotal, 2) }}</span>
                        </div>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="block w-full text-center py-3.5 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white font-extrabold text-sm transition shadow-sm">
                        Continuar al Checkout →
                    </a>

                    @guest
                        <button type="button" @click="$dispatch('open-register-modal')" class="w-full py-2.5 rounded-xl border border-[#1D70B8] text-[#1D70B8] hover:bg-sky-50 text-xs font-extrabold transition">
                            ¿Eres Ex-Alumno? Regístrate en 15s y baja el precio
                        </button>
                    @endguest
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
