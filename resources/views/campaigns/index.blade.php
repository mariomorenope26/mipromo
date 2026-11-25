@extends('layouts.app')

@section('title', 'Campañas Grupales y Preventas — MiPromo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-amber-600">Módulo B · Pedidos Grupales por Promoción</span>
            <h1 class="text-3xl font-extrabold text-[#0F2D4A] mt-1">Campañas Grupales y Preventas</h1>
            <p class="text-sm text-slate-600 mt-1">Únete a la campaña de tu promoción, separa con adelanto y alcanza la meta mínima para iniciar producción consolidada.</p>
        </div>

        <form method="GET" action="{{ route('campaigns.index') }}" class="flex flex-wrap gap-2">
            <select name="colegio" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-sm">
                <option value="">Todas las instituciones</option>
                @foreach($institutions as $inst)
                    <option value="{{ $inst->slug }}" @selected(request('colegio') === $inst->slug)>{{ $inst->short_name }}</option>
                @endforeach
            </select>
            <select name="estado" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-sm">
                <option value="">Todos los estados</option>
                <option value="active" @selected(request('estado') === 'active')>Activas</option>
                <option value="goal_reached" @selected(request('estado') === 'goal_reached')>Meta alcanzada</option>
                <option value="in_production" @selected(request('estado') === 'in_production')>En producción</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl bg-[#1D70B8] text-white text-sm font-semibold">Filtrar</button>
        </form>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        @forelse($campaigns as $campaign)
            <x-campaign-card :campaign="$campaign" />
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-10 text-center text-slate-500">
                No hay campañas activas con este filtro.
            </div>
        @endforelse
    </div>

    <div>{{ $campaigns->links() }}</div>
</div>
@endsection
