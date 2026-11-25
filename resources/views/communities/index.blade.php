@extends('layouts.app')

@section('title', 'Comunidades de Ex-Alumnos — MiPromo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-[#1D70B8]">Módulo D · Jerarquía Institución → Promoción → Aula</span>
            <h1 class="text-3xl font-extrabold text-[#0F2D4A] mt-1">Comunidades y Grupos de Ex-Alumnos</h1>
            <p class="text-sm text-slate-600 mt-1">Comparte recuerdos, organiza reencuentros, participa en votaciones de diseño y coordina colectas.</p>
        </div>

        <form method="GET" action="{{ route('communities.index') }}" class="flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar comunidad..." class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-sm">
            <select name="tipo" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-sm">
                <option value="">Todos los niveles</option>
                <option value="institution" @selected(request('tipo') === 'institution')>Por Institución</option>
                <option value="promotion" @selected(request('tipo') === 'promotion')>Por Promoción</option>
                <option value="classroom" @selected(request('tipo') === 'classroom')>Por Aula / Sección</option>
                <option value="interest" @selected(request('tipo') === 'interest')>Grupos de Interés</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl bg-[#1D70B8] text-white text-sm font-semibold">Filtrar</button>
        </form>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($communities as $community)
            <article class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between gap-4">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="px-2.5 py-1 rounded-full bg-sky-50 text-[#1D70B8] text-[11px] font-bold">{{ $community->typeLabel() }}</span>
                        @if($community->institution)
                            <span class="text-[11px] font-bold text-slate-500">{{ $community->institution->short_name }}</span>
                        @endif
                    </div>
                    <h2 class="mt-3 text-lg font-extrabold text-[#0F2D4A]">
                        <a href="{{ route('communities.show', $community->slug) }}" class="hover:text-[#1D70B8]">{{ $community->name }}</a>
                    </h2>
                    <p class="mt-1.5 text-xs text-slate-600 line-clamp-2">{{ $community->description }}</p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-600"><strong>{{ $community->members_count }}</strong> miembros · <strong>{{ $community->posts_count }}</strong> posts · <strong>{{ $community->events_count }}</strong> eventos</span>
                    <a href="{{ route('communities.show', $community->slug) }}" class="font-bold text-[#1D70B8] hover:underline">Entrar →</a>
                </div>
            </article>
        @endforeach
    </div>

    <div>{{ $communities->links() }}</div>
</div>
@endsection
