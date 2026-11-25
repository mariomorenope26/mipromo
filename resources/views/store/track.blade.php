@extends('layouts.app')

@section('title', 'Seguimiento de Pedido — MiPromo')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
    <div class="text-center space-y-2">
        <span class="text-xs font-bold uppercase tracking-wider text-[#1D70B8]">Módulo G · Logística Nacional</span>
        <h1 class="text-3xl font-extrabold text-[#0F2D4A]">Seguimiento de Pedido y Guía Courier</h1>
        <p class="text-sm text-slate-600">Ingresa tu código de pedido (ej. <code>PED-2026-0001</code>) o número de guía.</p>
    </div>

    <form method="GET" action="{{ route('orders.track') }}" class="flex gap-2 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm">
        <input type="text" name="codigo" value="{{ $orderNumber }}" placeholder="Ej. PED-2026-0001 o MPR-882314PE" class="flex-1 px-4 py-2.5 rounded-xl text-sm focus:outline-none">
        <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#1D70B8] text-white text-sm font-bold">Rastrear</button>
    </form>

    @if($orderNumber !== '' && ! $order)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center text-sm text-amber-800">
            No encontramos un pedido con el código <strong>{{ $orderNumber }}</strong>.
        </div>
    @elseif($order)
        @php
            $steps = ['pending' => 1, 'confirmed' => 2, 'in_production' => 3, 'shipped' => 4, 'delivered' => 5];
            $currentStep = $steps[$order->order_status] ?? 1;
        @endphp
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-6 shadow-sm">
            <div class="flex flex-wrap justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-xl font-extrabold text-[#0F2D4A]">{{ $order->order_number }}</h2>
                    <p class="text-xs text-slate-500">Courier: {{ $order->courier_name }} · Guía: {{ $order->tracking_code ?? 'Por asignar' }}</p>
                </div>
                <span class="px-3 py-1 rounded-full bg-sky-50 text-[#1D70B8] text-xs font-bold h-fit">{{ $order->orderStatusLabel() }}</span>
            </div>

            <div class="grid grid-cols-5 gap-2 text-center text-[11px] font-semibold">
                @foreach(['Recibido', 'Confirmado', 'Producción', 'En Camino', 'Entregado'] as $idx => $label)
                    <div class="p-2 rounded-xl {{ ($idx + 1) <= $currentStep ? 'bg-[#1D70B8] text-white' : 'bg-slate-100 text-slate-400' }}">
                        {{ $label }}
                    </div>
                @endforeach
            </div>

            <div class="flex justify-between items-center text-sm pt-2">
                <span class="text-slate-600">Destino: <strong>{{ $order->city }}, {{ $order->department }}</strong></span>
                <a href="{{ route('orders.confirmation', $order->order_number) }}" class="text-[#1D70B8] font-bold hover:underline">Ver detalle completo →</a>
            </div>
        </div>
    @endif
</div>
@endsection
