@extends('layouts.app')

@section('title', 'Comprobante Electrónico SUNAT '.$invoice->formattedNumber())

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white rounded-3xl border border-slate-300 p-8 shadow-sm space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-6 border-b border-slate-200 pb-6">
            <div class="space-y-2">
                <img src="{{ asset('images/logo_jmsperu.png') }}" alt="Grupo JMSPERU" class="h-10 w-auto">
                <p class="text-xs text-slate-600 font-semibold">GRUPO JMSPERU SOLUTIONS E.I.R.L. · RUC 20609919368</p>
                <p class="text-xs text-slate-500">Lima, Perú · WhatsApp: <a href="https://wa.me/51959199368?text={{ urlencode('Hola Grupo JMSPERU, consulta sobre comprobante '.$invoice->formattedNumber()) }}" target="_blank" rel="noopener noreferrer" class="font-bold text-emerald-700 hover:underline">959199368 ↗</a></p>
            </div>
            <div class="border-2 border-[#0F2D4A] rounded-2xl p-4 text-center min-w-[210px]">
                <p class="text-xs font-bold uppercase text-[#1D70B8]">{{ $invoice->document_type === 'factura' ? 'Factura Electrónica' : 'Boleta de Venta Electrónica' }}</p>
                <p class="text-xl font-black text-[#0F2D4A] mt-1">{{ $invoice->formattedNumber() }}</p>
                <span class="inline-block mt-1 px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase">SUNAT: {{ $invoice->sunat_status }}</span>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4 text-xs bg-slate-50 p-4 rounded-2xl border border-slate-100">
            <div>
                <p><strong class="text-slate-700">Adquirente:</strong> {{ $invoice->customer_legal_name }}</p>
                <p><strong class="text-slate-700">{{ $invoice->customer_doc_type }}:</strong> {{ $invoice->customer_doc_number }}</p>
            </div>
            <div>
                <p><strong class="text-slate-700">Fecha emisión:</strong> {{ $invoice->issued_at->format('d/m/Y H:i') }}</p>
                <p><strong class="text-slate-700">Pedido ref.:</strong> {{ $order->order_number }}</p>
            </div>
        </div>

        <table class="w-full text-sm border border-slate-200 rounded-xl overflow-hidden">
            <thead class="bg-[#0F2D4A] text-white text-xs">
                <tr>
                    <th class="p-3 text-left">Descripción</th>
                    <th class="p-3 text-center">Cant.</th>
                    <th class="p-3 text-right">P. Unit</th>
                    <th class="p-3 text-right">Importe</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($order->items as $item)
                    <tr>
                        <td class="p-3">{{ $item->product_name }} ({{ $item->variant_label }})</td>
                        <td class="p-3 text-center">{{ $item->quantity }}</td>
                        <td class="p-3 text-right">S/ {{ number_format($item->unit_price, 2) }}</td>
                        <td class="p-3 text-right font-semibold">S/ {{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex flex-col sm:flex-row justify-between items-end gap-4 pt-2">
            <div class="text-[11px] text-slate-500 max-w-md">
                <p class="font-semibold text-slate-700">Representación impresa del Comprobante Electrónico SUNAT</p>
                <p class="break-all">Hash CDR: {{ $invoice->sunat_hash }}</p>
            </div>
            <div class="w-full sm:w-64 space-y-1 text-sm bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div class="flex justify-between"><span>Op. Gravada:</span><span>S/ {{ number_format($invoice->subtotal_gravado, 2) }}</span></div>
                <div class="flex justify-between"><span>IGV (18%):</span><span>S/ {{ number_format($invoice->igv, 2) }}</span></div>
                <div class="flex justify-between font-black text-base text-[#0F2D4A] pt-1 border-t border-slate-200"><span>Importe Total:</span><span>S/ {{ number_format($invoice->total, 2) }}</span></div>
            </div>
        </div>

        <div class="flex justify-between pt-4 border-t border-slate-100">
            <a href="{{ route('orders.confirmation', $order->order_number) }}" class="text-xs font-bold text-[#1D70B8] hover:underline">← Volver al pedido</a>
            <button onclick="window.print()" type="button" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">Imprimir / Guardar PDF</button>
        </div>
    </div>
</div>
@endsection
