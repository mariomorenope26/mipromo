@extends('layouts.app')

@section('title', 'Finalizar Compra — Checkout MiPromo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10"
     x-data="{
        shippingRate: {{ (float) ($shippingZones->first()?->rate ?? 0) }},
        subtotal: {{ $subtotal }},
        discount: {{ $discount }},
        method: 'yape',
        docType: 'boleta',
        createAccount: true,
        copiedPhone: false,
        customerDni: @js(old('customer_dni_ruc', auth()->user()?->dni ?? '')),
        customerName: @js(old('customer_name', auth()->user()?->name ?? '')),
        loadingDni: false,
        dniMsg: '',
        dniOk: false
     }">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-[#0F2D4A]">Finalizar Pedido y Pago</h1>
            <p class="text-xs text-slate-500 mt-1">Compra protegida con comprobante electrónico SUNAT y garantía Grupo JMSPERU.</p>
        </div>
        <div class="flex items-center gap-2 text-xs font-bold">
            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800">1. Carrito ✓</span>
            <span>→</span>
            <span class="px-3 py-1 rounded-full bg-[#1D70B8] text-white">2. Datos &amp; Envío</span>
            <span>→</span>
            <span class="px-3 py-1 rounded-full bg-slate-200 text-slate-700">3. Pago</span>
        </div>
    </div>

    <form action="{{ route('checkout.store') }}" method="POST" enctype="multipart/form-data" class="grid lg:grid-cols-12 gap-8">
        @csrf
        <div class="lg:col-span-7 space-y-6">
            {{-- Datos del comprador y Comprobante SUNAT --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">1. Datos de Facturación SUNAT</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nombre completo</label>
                        <input type="text" name="customer_name" x-model="customerName" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Correo electrónico</label>
                        <input type="email" name="customer_email" required value="{{ old('customer_email', auth()->user()?->email) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Teléfono / WhatsApp</label>
                        <input type="text" name="customer_phone" required value="{{ old('customer_phone', auth()->user()?->phone) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipo de comprobante</label>
                        <select name="document_type" x-model="docType" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                            <option value="boleta">Boleta de Venta Electrónica (DNI)</option>
                            <option value="factura">Factura Electrónica (RUC)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" x-text="docType === 'factura' ? 'Número de RUC (11 dígitos)' : 'Número de DNI (consulta apiperu.dev)'"></label>
                        <div class="flex gap-2">
                            <input type="text" name="customer_dni_ruc" x-model="customerDni" required class="flex-1 px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono">
                            <button type="button"
                                    x-show="docType === 'boleta'"
                                    :disabled="loadingDni"
                                    @click="loadingDni = true; dniMsg = ''; const r = await window.miPromoLookupDni(customerDni, { onSuccess: d => { customerName = d.full_name; } }); loadingDni = false; dniOk = !!r.ok; dniMsg = r.message || '';"
                                    class="px-3.5 py-2 rounded-xl bg-[#0F2D4A] hover:bg-[#1D70B8] text-white text-xs font-bold shrink-0">
                                <span x-text="loadingDni ? '...' : 'Buscar DNI'"></span>
                            </button>
                        </div>
                        <p x-show="dniMsg && docType === 'boleta'" x-text="dniMsg" :class="dniOk ? 'text-emerald-700' : 'text-amber-700'" class="text-[11px] font-semibold mt-1"></p>
                    </div>
                    <div x-show="docType === 'factura'">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Razón Social</label>
                        <input type="text" name="legal_name" value="{{ old('legal_name') }}" placeholder="Empresa SAC" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                </div>

                @guest
                    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 space-y-2.5 text-xs">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="create_account" value="1" x-model="createAccount" class="mt-0.5 rounded text-emerald-600">
                            <span>
                                <strong class="text-emerald-900">⚡ Crear mi cuenta MiPromo gratis con estos datos (1 clic)</strong>
                                <span class="block text-emerald-800 mt-0.5">Guarda tu historial, descarga tu SUNAT cuando quieras y obtén descuento en próximas compras.</span>
                            </span>
                        </label>
                        <div x-show="createAccount" class="sm:max-w-xs">
                            <label class="block font-bold text-emerald-900 mb-1">Define una contraseña rápida (opcional)</label>
                            <input type="password" name="account_password" placeholder="Mín. 8 caracteres (o clave automática)" class="w-full px-3 py-2 rounded-xl border border-emerald-300 bg-white text-sm">
                        </div>
                    </div>
                @endguest
            </div>

            {{-- Logística y Envío --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">2. Zona de Envío y Courier (Local / Nacional)</h2>
                <div class="space-y-2.5">
                    @foreach($shippingZones as $index => $zone)
                        <label class="cursor-pointer rounded-xl border border-slate-200 p-3.5 flex items-center justify-between gap-3 hover:border-[#1D70B8]">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="shipping_zone_id" value="{{ $zone->id }}" @checked($index === 0) @change="shippingRate = {{ (float) $zone->rate }}" class="mt-1 text-[#1D70B8]">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ $zone->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $zone->courier_name }} · {{ $zone->estimated_days }} — {{ $zone->description }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-extrabold text-[#0F2D4A] shrink-0">
                                {{ $zone->rate > 0 ? 'S/ '.number_format($zone->rate, 2) : 'Gratis' }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="grid sm:grid-cols-3 gap-3 pt-2">
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Dirección de entrega o agencia</label>
                        <input type="text" name="shipping_address" required value="{{ old('shipping_address') }}" placeholder="Av. / Jr. / Referencia o Agencia Shalom/Olva" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Ciudad / Distrito</label>
                        <input type="text" name="city" required value="{{ old('city', auth()->user()?->city ?? 'Lima') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Departamento / Región</label>
                        <input type="text" name="department" required value="{{ old('department', 'Lima') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                </div>
            </div>

            {{-- Método de Pago --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">3. Método de Pago</h2>
                <div class="grid sm:grid-cols-2 gap-3">
                    <label class="cursor-pointer rounded-xl border p-3.5 flex items-center gap-2.5" :class="method === 'yape' ? 'border-[#1D70B8] bg-sky-50' : 'border-slate-200'">
                        <input type="radio" name="payment_method" value="yape" x-model="method">
                        <div>
                            <p class="text-sm font-bold">Yape (959199368)</p>
                            <p class="text-[11px] text-slate-500">Validación de voucher</p>
                        </div>
                    </label>
                    <label class="cursor-pointer rounded-xl border p-3.5 flex items-center gap-2.5" :class="method === 'plin' ? 'border-[#1D70B8] bg-sky-50' : 'border-slate-200'">
                        <input type="radio" name="payment_method" value="plin" x-model="method">
                        <div>
                            <p class="text-sm font-bold">Plin (959199368)</p>
                            <p class="text-[11px] text-slate-500">Validación de voucher</p>
                        </div>
                    </label>
                    <label class="cursor-pointer rounded-xl border p-3.5 flex items-center gap-2.5" :class="method === 'card_culqi' ? 'border-[#1D70B8] bg-sky-50' : 'border-slate-200'">
                        <input type="radio" name="payment_method" value="card_culqi" x-model="method">
                        <div>
                            <p class="text-sm font-bold">Tarjeta Crédito / Débito</p>
                            <p class="text-[11px] text-emerald-700 font-semibold">Aprobación inmediata + SUNAT</p>
                        </div>
                    </label>
                    <label class="cursor-pointer rounded-xl border p-3.5 flex items-center gap-2.5" :class="method === 'bank_transfer' ? 'border-[#1D70B8] bg-sky-50' : 'border-slate-200'">
                        <input type="radio" name="payment_method" value="bank_transfer" x-model="method">
                        <div>
                            <p class="text-sm font-bold">Transferencia BCP / Interbank</p>
                            <p class="text-[11px] text-slate-500">Carga de constancia</p>
                        </div>
                    </label>
                </div>

                <div x-show="method !== 'card_culqi'" class="rounded-xl bg-amber-50 border border-amber-200 p-4 space-y-3 text-xs text-amber-900">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="space-y-1 flex-1 min-w-52">
                            <p class="font-extrabold text-sm text-[#0F2D4A]">Pago rápido Yape / Plin Grupo JMSPERU</p>
                            <p>Escanea o transfiere al número <a href="https://wa.me/51959199368?text={{ urlencode('Hola Grupo JMSPERU, envío constancia de pago Yape/Plin.') }}" target="_blank" rel="noopener noreferrer" class="underline font-extrabold text-emerald-800">959199368 (WhatsApp ↗)</a>, ingresa tu N.° de operación y confirma abajo.</p>
                            <button type="button"
                                    @click="navigator.clipboard?.writeText('959199368'); copiedPhone = true; setTimeout(() => copiedPhone = false, 2000)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#0F2D4A] text-white font-bold">
                                <span x-text="copiedPhone ? '✓ Número 959199368 copiado' : 'Copiar 959199368'"></span>
                            </button>
                        </div>
                        <div class="bg-white rounded-xl p-2 border border-amber-200 text-center shrink-0">
                            <svg viewBox="0 0 64 64" class="w-16 h-16 mx-auto text-[#0F2D4A]" fill="currentColor" aria-label="QR Yape Plin 959199368">
                                <rect x="4" y="4" width="20" height="20" rx="2"/><rect x="8" y="8" width="12" height="12" fill="white"/><rect x="11" y="11" width="6" height="6"/>
                                <rect x="40" y="4" width="20" height="20" rx="2"/><rect x="44" y="8" width="12" height="12" fill="white"/><rect x="47" y="11" width="6" height="6"/>
                                <rect x="4" y="40" width="20" height="20" rx="2"/><rect x="8" y="44" width="12" height="12" fill="white"/><rect x="11" y="47" width="6" height="6"/>
                                <rect x="28" y="8" width="6" height="6"/><rect x="28" y="20" width="8" height="8"/><rect x="8" y="28" width="8" height="6"/>
                                <rect x="22" y="32" width="12" height="6"/><rect x="40" y="28" width="10" height="6"/><rect x="52" y="36" width="8" height="8"/>
                                <rect x="30" y="44" width="8" height="12"/><rect x="44" y="48" width="14" height="10"/>
                            </svg>
                            <span class="block text-[10px] font-extrabold text-[#0F2D4A] mt-1">YAPE / PLIN</span>
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-3 pt-1">
                        <input type="text" name="reference_code" placeholder="N.° de operación / constancia" class="px-3 py-2 rounded-lg border border-amber-300 bg-white text-slate-900">
                        <input type="file" name="voucher" accept=".jpg,.jpeg,.png,.pdf" class="text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Notas adicionales (tallas especiales o indicaciones)</label>
                    <textarea name="notes" rows="2" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
                </div>
            </div>
        </div>

        {{-- Summary --}}
        <div class="lg:col-span-5">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4 sticky top-24">
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Resumen de Compra</h2>
                <div class="divide-y divide-slate-100 max-h-72 overflow-y-auto">
                    @foreach($items as $item)
                        <div class="py-3 flex justify-between gap-3 text-sm">
                            <div>
                                <p class="font-bold text-slate-800">{{ $item['product_name'] }} × {{ $item['quantity'] }}</p>
                                <p class="text-xs text-slate-500">{{ $item['variant_label'] }}</p>
                            </div>
                            <span class="font-bold text-slate-900 shrink-0">S/ {{ number_format($item['unit_price'] * $item['quantity'], 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="pt-4 border-t border-slate-200 space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-600">Subtotal</span><span>S/ {{ number_format($subtotal, 2) }}</span></div>
                    @if($discount > 0)
                        <div class="flex justify-between text-emerald-700"><span>Descuento ({{ $coupon?->code }})</span><span>- S/ {{ number_format($discount, 2) }}</span></div>
                    @endif
                    <div class="flex justify-between"><span class="text-slate-600">Envío</span><span x-text="shippingRate > 0 ? 'S/ ' + shippingRate.toFixed(2) : 'Gratis'"></span></div>
                    <div class="flex justify-between text-xl font-extrabold text-[#0F2D4A] pt-2 border-t border-slate-100">
                        <span>Total a pagar</span>
                        <span x-text="'S/ ' + Math.max(0, subtotal - discount + shippingRate).toFixed(2)"></span>
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white font-extrabold text-sm shadow-md transition">
                    Confirmar y Generar Pedido
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
