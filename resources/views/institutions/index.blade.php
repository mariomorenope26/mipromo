@extends('layouts.app')

@section('title', 'Instituciones Educativas — MiPromo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-8">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-[#1D70B8]">Módulo E · Multi-Tenencia</span>
            <h1 class="text-3xl font-extrabold text-[#0F2D4A] mt-1">Colegios e Instituciones Participantes</h1>
            <p class="text-sm text-slate-600 mt-1">Colegios con más de 10 años de vida institucional dados de alta tras la acreditación de sus delegados.</p>
        </div>

        <form method="GET" action="{{ route('institutions.index') }}" class="flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Nombre o lema..." class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-sm">
            <select name="ciudad" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-sm">
                <option value="">Todas las ciudades</option>
                @foreach($cities as $city)
                    <option value="{{ $city }}" @selected(request('ciudad') === $city)>{{ $city }}</option>
                @endforeach
            </select>
            <select name="region" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-sm">
                <option value="">Todas las regiones</option>
                @foreach($regions as $region)
                    <option value="{{ $region }}" @selected(request('region') === $region)>{{ $region }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl bg-[#1D70B8] text-white text-sm font-semibold">Filtrar</button>
        </form>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($institutions as $inst)
            <x-institution-card :institution="$inst" />
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-10 text-center text-slate-500">
                No se encontraron instituciones con ese criterio.
            </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $institutions->links() }}</div>
</div>
@endsection
