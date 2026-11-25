@extends('layouts.app')

@section('title', 'Pedido '.$order->order_number.' — MiPromo')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-6">
            <div>
                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">Pedido Registrado</span>
                <h1 class="text-3xl font-extrabold text-[#0F2D4A] mt-2">{{ $order->order_number }}</h1>
                <p class="text-sm text-slate-500">Cliente: {{ $order->customer_name }} · {{ $order->customer_email }}</p>
            </div>
            <div class="text-right">
                <span class="block text-xs text-slate-500">Estado logístico</span>
                <span class="inline-block mt-1 px-3 py-1 rounded-xl bg-sky-50 text-[#1D70B8] text-xs font-bold">{{ $order->orderStatusLabel() }}</span>
                <span class="block mt-2 text-xs text-slate-500">Pago: <strong>{{ $order->paymentStatusLabel() }}</strong></span>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-6 text-sm">
            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-100 space-y-1">
                <h2 class="font-bold text-[#0F2D4A]">Entrega y Courier</h2>
                <p>{{ $order->shipping_address }}</p>
                <p class="text-slate-500">{{ $order->city }}, {{ $order->department }}</p>
                <p class="text-xs text-[#1D70B8] font-semibold">Courier: {{ $order->courier_name }} @if($order->tracking_code) · Guía: {{ $order->tracking_code }} @endif</p>
            </div>

            <div class="rounded-2xl bg-slate-50 p-4 border border-slate-100 space-y-1">
                <h2 class="font-bold text-[#0F2D4A]">Pago y Comprobante</h2>
                @foreach($order->payments as $payment)
                    <p>{{ $payment->methodLabel() }} — <strong>S/ {{ number_format($payment->amount, 2) }}</strong> ({{ $payment->status }})</p>
                    @if($payment->reference_code)
                        <p class="text-xs text-slate-500">Referencia: {{ $payment->reference_code }}</p>
                    @endif
                @endforeach
                <p class="text-xs text-slate-500 pt-1">Consultas Yape/Plin por WhatsApp: <a href="https://wa.me/51959199368?text={{ urlencode('Hola Grupo JMSPERU, consulta sobre mi pedido '.$order->order_number) }}" target="_blank" rel="noopener noreferrer" class="font-bold text-emerald-700 hover:underline">Grupo JMSPERU 959199368 ↗</a></p>
            </div>
        </div>

        <div>
            <h2 class="font-bold text-[#0F2D4A] mb-3">Detalle de Productos</h2>
            <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="p-3">Producto</th>
                            <th class="p-3">Variante</th>
                            <th class="p-3 text-center">Cant.</th>
                            <th class="p-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="p-3 font-semibold">{{ $item->product_name }}</td>
                                <td class="p-3 text-slate-600">{{ $item->variant_label }}</td>
                                <td class="p-3 text-center">{{ $item->quantity }}</td>
                                <td class="p-3 text-right font-bold">S/ {{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-100">
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('orders.invoice', $order->order_number) }}" class="px-4 py-2.5 rounded-xl bg-[#0F2D4A] text-white text-xs font-bold hover:bg-[#1D70B8] transition">Ver Comprobante SUNAT</a>
                <a href="{{ route('orders.track', ['codigo' => $order->order_number]) }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold">Seguimiento en Vivo</a>
            </div>
            <div class="text-right">
                <p class="text-xs text-slate-500">Total del Pedido</p>
                <p class="text-2xl font-extrabold text-[#0F2D4A]">S/ {{ number_format($order->total, 2) }}</p>
            </div>
        </div>

        {{-- Gestión de Devoluciones / Cambios (Módulo G) --}}
        <form action="{{ route('orders.return.store', $order->order_number) }}" method="POST" class="pt-4 border-t border-slate-100 grid sm:grid-cols-4 gap-3 items-end text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Cambio o Devolución</label>
                <select name="request_type" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                    <option value="cambio_talla">Cambio de talla</option>
                    <option value="devolucion">Devolución / Nota de crédito</option>
                    <option value="garantia">Garantía de confección</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block font-bold text-slate-700 mb-1">Motivo de solicitud</label>
                <input type="text" name="reason" required placeholder="Indica tu solicitud..." class="w-full px-3 py-2 rounded-xl border border-slate-300">
            </div>
            <button type="submit" class="py-2 px-4 rounded-xl border border-slate-300 hover:bg-slate-100 font-bold">Solicitar Cambio</button>
        </form>
    </div>
</div>
@endsection
