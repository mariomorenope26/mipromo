@extends('layouts.admin')

@section('heading', 'Pedidos, Conciliación de Pagos y SUNAT')
@section('subheading', 'Valida vouchers Yape/Plin, asigna guías courier y emite comprobantes electrónicos')

@section('content')
<div class="space-y-5">
    @if(isset($returnRequests) && $returnRequests->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 space-y-3">
            <h2 class="font-extrabold text-amber-900 text-sm">Solicitudes de Cambio o Devolución Pendientes ({{ $returnRequests->count() }})</h2>
            @foreach($returnRequests as $ret)
                <form action="{{ route('admin.returns.resolve', $ret->id) }}" method="POST" class="bg-white rounded-xl p-3 border border-amber-200 flex flex-wrap items-center justify-between gap-3 text-xs">
                    @csrf
                    @method('PATCH')
                    <div>
                        <strong>{{ $ret->order->order_number }}</strong> ({{ $ret->request_type }}) — {{ $ret->reason }}
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" name="admin_notes" placeholder="Notas..." class="px-2.5 py-1.5 rounded-lg border border-slate-300">
                        <button type="submit" name="status" value="approved" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold">Aprobar</button>
                        <button type="submit" name="status" value="rejected" class="px-3 py-1.5 rounded-lg bg-rose-600 text-white font-bold">Rechazar</button>
                    </div>
                </form>
            @endforeach
        </div>
    @endif

    @foreach($orders as $order)
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-extrabold text-[#0F2D4A]">{{ $order->order_number }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $order->payment_status === 'verified' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $order->paymentStatusLabel() }}</span>
                        <span class="px-2.5 py-0.5 rounded-full bg-sky-50 text-[#1D70B8] text-xs font-bold">{{ $order->orderStatusLabel() }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ $order->customer_name }} ({{ $order->customer_dni_ruc }}) · {{ $order->customer_phone }} · {{ $order->city }}, {{ $order->department }}
                    </p>
                </div>
                <div class="text-right space-y-1">
                    <p class="text-xl font-black text-[#0F2D4A]">S/ {{ number_format($order->total, 2) }}</p>
                    @if($order->invoice)
                        <a href="{{ route('orders.invoice', $order->order_number) }}" class="text-xs font-bold text-emerald-700 hover:underline block">SUNAT: {{ $order->invoice->formattedNumber() }}</a>
                        @if(! $order->invoices()->where('document_type', 'nota_credito')->exists())
                            <form action="{{ route('admin.orders.credit_note', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-[11px] font-bold text-rose-600 hover:underline">Emitir Nota de Crédito SUNAT</button>
                            </form>
                        @else
                            <span class="text-[11px] font-bold text-rose-700">NC emitida</span>
                        @endif
                    @else
                        <span class="text-xs text-slate-400">Comprobante pendiente de pago</span>
                    @endif
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-4">
                {{-- Conciliación de pagos --}}
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-3">
                    <h3 class="text-xs font-bold uppercase text-slate-500">Pagos y Vouchers</h3>
                    @foreach($order->payments as $payment)
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                            <div>
                                <p class="font-bold">{{ $payment->methodLabel() }} — S/ {{ number_format($payment->amount, 2) }}</p>
                                <p class="text-slate-500">Ref: {{ $payment->reference_code ?? 'Sin código' }} · Estado: <strong>{{ $payment->status }}</strong></p>
                            </div>
                            @if($payment->status === 'pending_review')
                                <div class="flex gap-1.5">
                                    <form action="{{ route('admin.payments.verify', $payment->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold">Aprobar y Emitir SUNAT</button>
                                    </form>
                                    <form action="{{ route('admin.payments.verify', $payment->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-600 text-white font-bold">Rechazar</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Estado Logístico --}}
                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-2">
                    @csrf
                    @method('PATCH')
                    <h3 class="text-xs font-bold uppercase text-slate-500">Despacho y Tracking</h3>
                    <div class="grid sm:grid-cols-3 gap-2 text-xs">
                        <select name="order_status" class="px-2.5 py-2 rounded-lg border border-slate-300 bg-white">
                            @foreach(['pending' => 'Recibido', 'confirmed' => 'Confirmado', 'in_production' => 'En producción', 'shipped' => 'Enviado', 'delivered' => 'Entregado', 'cancelled' => 'Cancelado'] as $val => $lbl)
                                <option value="{{ $val }}" @selected($order->order_status === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="courier_name" value="{{ $order->courier_name }}" placeholder="Courier" class="px-2.5 py-2 rounded-lg border border-slate-300 bg-white">
                        <input type="text" name="tracking_code" value="{{ $order->tracking_code }}" placeholder="N.° Guía" class="px-2.5 py-2 rounded-lg border border-slate-300 bg-white">
                    </div>
                    <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-[#0F2D4A] text-white text-xs font-bold">Guardar Despacho</button>
                </form>
            </div>
        </div>
    @endforeach

    <div>{{ $orders->links() }}</div>
</div>
@endsection
