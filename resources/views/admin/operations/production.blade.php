@extends('layouts.admin')

@section('heading', 'Orden de Producción Consolidada')
@section('subheading', $campaign->title.' — '.$campaign->institution->name)

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <span class="px-3 py-1 rounded-full text-xs font-bold text-white" style="background-color: {{ $campaign->institution->primary_color }}">{{ $campaign->institution->short_name }}</span>
                <h2 class="text-2xl font-extrabold text-[#0F2D4A] mt-2">{{ $campaign->title }}</h2>
                <p class="text-xs text-slate-500">
                    Total unidades reservadas: <strong>{{ $campaign->current_units }}</strong> / Meta: {{ $campaign->target_units }} · Entrega: {{ $campaign->delivery_mode === 'consolidated_delegate' ? 'Consolidada al delegado' : 'Individual' }}
                    @if(auth()->user()->isRole('super_admin'))
                        · <strong>Proveedor:</strong> {{ $campaign->product->vendor?->business_name ?? 'Producción Directa JMSPERU' }}
                    @endif
                </p>
            </div>
            <button onclick="window.print()" type="button" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">Imprimir Orden de Taller</button>
        </div>

        <h3 class="font-bold text-sm text-[#0F2D4A]">Resumen por Talla / Variante</h3>
        <div class="grid sm:grid-cols-3 gap-3 text-xs">
            @foreach($summaryByVariant as $label => $qty)
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex justify-between">
                    <span class="font-semibold">{{ $label }}</span>
                    <span class="font-black text-[#1D70B8]">{{ $qty }} uds</span>
                </div>
            @endforeach
        </div>

        <h3 class="font-bold text-sm text-[#0F2D4A] pt-2">Detalle de Personalización (Bordados / Tallas)</h3>
        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 text-left text-slate-500 uppercase">
                    <tr>
                        <th class="p-3">Participante</th>
                        <th class="p-3">Variante</th>
                        <th class="p-3 text-center">Cant.</th>
                        <th class="p-3">Nombre Bordado</th>
                        <th class="p-3">Año</th>
                        <th class="p-3">Pago</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($campaign->participants as $p)
                        <tr>
                            <td class="p-3 font-bold">{{ $p->participant_name }}</td>
                            <td class="p-3">{{ $p->variant?->label() ?? 'Estándar' }}</td>
                            <td class="p-3 text-center font-bold">{{ $p->quantity }}</td>
                            <td class="p-3 font-mono">{{ $p->custom_name ?? '—' }}</td>
                            <td class="p-3">{{ $p->custom_year ?? '—' }}</td>
                            <td class="p-3">{{ $p->payment_status }} (S/ {{ number_format($p->amount_paid, 2) }})</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
