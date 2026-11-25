@extends('layouts.app')

@section('title', 'Cómo Comprar en Línea Fácil, Rápido y Seguro — MiPromo')

@section('content')
<div class="space-y-14 pb-16">
    {{-- Hero de Conversión Persuasiva --}}
    <section class="bg-gradient-to-br from-[#0F2D4A] via-[#133B61] to-[#1D70B8] text-white py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-10 items-center">
            <div class="lg:col-span-7 space-y-5">
                <span class="px-3 py-1 rounded-full bg-amber-400 text-[#0F2D4A] text-xs font-extrabold uppercase">Compra 100% Protegida y Sin Esperas</span>
                <h1 class="text-3xl sm:text-5xl font-extrabold leading-tight">
                    Compra tus Prendas de Promoción en <span class="text-amber-300">2 Minutos</span> y con Descuento Real
                </h1>
                <p class="text-base text-slate-200 leading-relaxed">
                    Olvídate de coordinar en chats desordenados o transferir sin comprobante. En <strong>MiPromo (Grupo JMSPERU)</strong> compras online con <strong>Proveedores Verificados</strong>, Boleta o Factura electrónica SUNAT y envío directo a Piura, Chiclayo, Trujillo, Lima y todo el Perú.
                </p>
                <div class="flex flex-wrap gap-3 pt-1">
                    <a href="{{ route('home') }}#tienda-catalogo" class="px-6 py-3 rounded-xl bg-amber-400 hover:bg-amber-300 text-[#0F2D4A] font-extrabold text-sm transition">
                        Ir a Comprar en la Tienda →
                    </a>
                    @guest
                        <button type="button" @click="$dispatch('open-register-modal')" class="px-6 py-3 rounded-xl bg-white text-[#0F2D4A] font-extrabold text-sm hover:bg-sky-50 transition">
                            Registrarme Gratis en 15s
                        </button>
                    @endguest
                </div>
            </div>

            <div class="lg:col-span-5 bg-white text-slate-900 rounded-3xl p-6 shadow-2xl space-y-4">
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Lo que desbloqueas al registrarte hoy (Gratis):</h2>
                <ul class="space-y-3 text-xs">
                    <li class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 font-bold text-emerald-900">✓ Tarifa Ex-Alumno inmediata (hasta S/ 20 menos por prenda)</li>
                    <li class="p-3 rounded-xl bg-amber-50 border border-amber-200 font-bold text-amber-900">✓ Cupón PROMO10 (-10%) o EGRESADO20 (-S/ 20 en compras S/ 120+)</li>
                    <li class="p-3 rounded-xl bg-sky-50 border border-sky-200 font-bold text-[#0F2D4A]">✓ Seguimiento en vivo de tu envío + Boleta / Factura SUNAT</li>
                    <li class="p-3 rounded-xl bg-slate-50 border border-slate-200 font-bold text-slate-800">✓ Tu código de referidos y acceso a votaciones de tu promoción</li>
                </ul>
                <a href="{{ route('register') }}" class="block text-center w-full py-3 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white font-extrabold text-sm transition">
                    Crear mi Cuenta Ahora en 15 Segundos →
                </a>
            </div>
        </div>
    </section>

    {{-- 3 Pasos Claros --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="text-center max-w-2xl mx-auto">
            <span class="text-xs font-bold uppercase tracking-wider text-[#1D70B8]">Sin Fricción · Desde tu Celular</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#0F2D4A] mt-1">Así de rápido compras en MiPromo</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-3">
                <span class="w-10 h-10 rounded-2xl bg-[#1D70B8] text-white font-black flex items-center justify-center">1</span>
                <h3 class="text-lg font-extrabold text-[#0F2D4A]">Elige tu Colegio o Producto</h3>
                <p class="text-sm text-slate-600">Busca tu institución educativa, escoge talla, color y personaliza tu año de promoción y nombre bordado.</p>
            </div>
            <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-3">
                <span class="w-10 h-10 rounded-2xl bg-amber-400 text-[#0F2D4A] font-black flex items-center justify-center">2</span>
                <h3 class="text-lg font-extrabold text-[#0F2D4A]">Regístrate en 15s (o Directo en Checkout)</h3>
                <p class="text-sm text-slate-600">Solo nombre, correo y clave. También puedes crear tu cuenta automáticamente en 1 clic al finalizar tu compra.</p>
            </div>
            <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-3">
                <span class="w-10 h-10 rounded-2xl bg-emerald-600 text-white font-black flex items-center justify-center">3</span>
                <h3 class="text-lg font-extrabold text-[#0F2D4A]">Paga con Yape/Plin/Tarjeta y Recibe</h3>
                <p class="text-sm text-slate-600">Paga seguro, descarga tu comprobante SUNAT al instante y sigue tu envío por Olva, Shalom o delegado.</p>
            </div>
        </div>
    </section>

    {{-- Comparativa: Visitante vs Ex-Alumno Registrado --}}
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-6 bg-slate-50 border-b border-slate-200">
                <h2 class="text-xl font-extrabold text-[#0F2D4A]">¿Por qué conviene registrarte antes de pagar?</h2>
            </div>
            <table class="w-full text-xs sm:text-sm">
                <thead class="bg-slate-100 text-slate-600 uppercase text-left">
                    <tr>
                        <th class="p-4">Beneficio</th>
                        <th class="p-4">Comprador Sin Cuenta</th>
                        <th class="p-4 text-emerald-800 bg-emerald-50">Cuenta Registrada / Ex-Alumno</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr><td class="p-4 font-bold">Precio en productos</td><td class="p-4">Tarifa pública estándar</td><td class="p-4 font-extrabold text-emerald-700 bg-emerald-50/40">Tarifa Especial Ex-Alumno (hasta 20% dto.)</td></tr>
                    <tr><td class="p-4 font-bold">Descuento por volumen</td><td class="p-4">Desde 12 uds (-10%)</td><td class="p-4 font-bold text-emerald-700 bg-emerald-50/40">-10% (12+) / -18% (30+) acumulable sobre tarifa miembro</td></tr>
                    <tr><td class="p-4 font-bold">Seguimiento y Boleta SUNAT</td><td class="p-4">Con código de pedido</td><td class="p-4 font-bold text-emerald-700 bg-emerald-50/40">Historial guardado + descargas + notificaciones</td></tr>
                    <tr><td class="p-4 font-bold">Comunidad, Votaciones y Colectas</td><td class="p-4 text-slate-400">Solo lectura</td><td class="p-4 font-bold text-emerald-700 bg-emerald-50/40">Acceso completo con tu promoción</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    {{-- Packs + CTA Final --}}
    @if(!empty($bundles))
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
            <h2 class="text-2xl font-extrabold text-[#0F2D4A]">Empieza Ahora con un Pack Listo</h2>
            <div class="grid md:grid-cols-2 gap-6">
                @foreach($bundles as $bundle)
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">{{ $bundle['savings_label'] }}</span>
                            <h3 class="mt-2 font-extrabold text-[#0F2D4A]">{{ $bundle['title'] }}</h3>
                            <p class="text-xs text-slate-500 mt-1">{{ $bundle['subtitle'] }} · S/ {{ number_format($bundle['regular_total'], 2) }}</p>
                        </div>
                        <form action="{{ route('cart.add_pack') }}" method="POST">
                            @csrf
                            <input type="hidden" name="bundle_code" value="{{ $bundle['code'] }}">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white text-xs font-extrabold transition">
                                Llevar Pack →
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
