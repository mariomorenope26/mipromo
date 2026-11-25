@extends('layouts.app')

@section('title', $institution->name.' — Tienda y Comunidad')

@section('content')
    {{-- Branded Institution Header (Multi-tenant Theme with Official Colors & PNG Insignia) --}}
    <section class="relative overflow-hidden text-white" style="background: linear-gradient(135deg, {{ $institution->primary_color }} 0%, #0F2D4A 78%, {{ $institution->secondary_color }}55 100%)">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                    <div class="w-28 h-28 rounded-3xl bg-white/95 backdrop-blur p-3 flex items-center justify-center shadow-2xl border-2 shrink-0"
                         style="border-color: {{ $institution->secondary_color }}">
                        @if($institution->logo_path)
                            <img src="{{ asset(ltrim($institution->logo_path, '/')) }}"
                                 alt="Insignia {{ $institution->name }}"
                                 class="w-full h-full object-contain drop-shadow-sm">
                        @else
                            <span class="text-3xl font-black" style="color: {{ $institution->primary_color }}">
                                {{ strtoupper(substr($institution->short_name, 0, 2)) }}
                            </span>
                        @endif
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-white/90">
                            <span class="px-2.5 py-0.5 rounded-full bg-white/15">{{ ucfirst($institution->type) }}</span>
                            <span>{{ $institution->city }}, {{ $institution->region }}</span>
                            @if($institution->founded_year)
                                <span>• Fundado en {{ $institution->founded_year }} ({{ $institution->yearsOfHistory() }} años de historia)</span>
                            @endif
                            @if($institution->modular_code)
                                <span>• Cód. Modular {{ $institution->modular_code }}</span>
                            @endif
                        </div>
                        <h1 class="text-2xl sm:text-4xl font-extrabold mt-2 leading-tight">{{ $institution->name }}</h1>
                        @if($institution->motto)
                            <p class="mt-1.5 text-sm sm:text-base italic font-medium" style="color: {{ $institution->secondary_color === '#0F2D4A' || $institution->secondary_color === '#18181B' ? '#FDE68A' : '#FEF08A' }}">
                                “{{ $institution->motto }}”
                            </p>
                        @endif

                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/20 border border-white/15">
                                <span class="w-3 h-3 rounded-full border border-white/50" style="background-color: {{ $institution->primary_color }}"></span>
                                <span class="w-3 h-3 rounded-full border border-white/50" style="background-color: {{ $institution->secondary_color }}"></span>
                                <span>Identidad oficial</span>
                            </span>
                            @if($institution->website_url)
                                <a href="{{ $institution->website_url }}" target="_blank" rel="noopener noreferrer" class="underline text-white/90 hover:text-white">Sitio institucional ↗</a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur rounded-2xl p-5 border border-white/20 text-xs space-y-2 shrink-0 max-w-xs">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-bold text-amber-200 uppercase tracking-wider">Estado del Colegio</p>
                        <span class="px-2 py-0.5 rounded-full {{ $institution->is_published ? 'bg-emerald-400/20 text-emerald-200' : 'bg-amber-400/20 text-amber-200' }} font-bold">
                            {{ $institution->is_published ? 'Alta Activa' : 'Vista Previa' }}
                        </span>
                    </div>
                    <p class="text-white/90">{{ number_format($institution->commission_rate, 0) }}% de cada venta retorna a la institución o asociación de egresados.</p>
                    <p class="text-white/80"><strong>{{ $institution->activeDelegates->count() }}</strong> delegado(s) acreditado(s)</p>
                    <a href="{{ route('directory.index', ['colegio' => $institution->slug]) }}" class="inline-block mt-1 underline font-semibold text-white">Ver padrón de egresados →</a>
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">
        {{-- Historia, Himno, Delegados y Promociones --}}
        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 space-y-5">
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Reseña e Identidad Institucional</h2>
                <p class="text-sm text-slate-600 leading-relaxed">{{ $institution->history }}</p>
                @if($institution->anthem_excerpt)
                    <blockquote class="rounded-xl bg-slate-50 border-l-4 p-4 text-sm italic text-slate-700" style="border-color: {{ $institution->primary_color }}">
                        “{{ $institution->anthem_excerpt }}”
                    </blockquote>
                @endif

                <div class="pt-3 border-t border-slate-100">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2.5">Delegados Oficiales Acreditados</h3>
                    <div class="flex flex-wrap gap-2">
                        @forelse($institution->activeDelegates as $delegate)
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="font-bold text-slate-800">{{ $delegate->name }}</span>
                                <span class="text-slate-500">({{ $delegate->roleLabel() }})</span>
                            </div>
                        @empty
                            <span class="text-xs text-amber-700">Sin delegados activos asignados todavía.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <h2 class="text-lg font-extrabold text-[#0F2D4A] mb-3">Promociones Activas</h2>
                <div class="space-y-2.5">
                    @forelse($institution->promotions as $promo)
                        <div class="rounded-xl bg-slate-50 border border-slate-100 p-3 flex items-center justify-between gap-2">
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ $promo->year }} — {{ $promo->name }}</p>
                                <p class="text-xs text-slate-500">{{ $promo->classrooms->count() }} aulas · {{ $promo->alumni_profiles_count }} egresados</p>
                            </div>
                            <a href="{{ route('directory.index', ['colegio' => $institution->slug, 'promocion' => $promo->year]) }}" class="text-xs font-bold hover:underline" style="color: {{ $institution->primary_color }}">Ver</a>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500">Aún no hay promociones registradas.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Campañas de la Institución --}}
        @if($campaigns->isNotEmpty())
            <section>
                <h2 class="text-2xl font-extrabold text-[#0F2D4A] mb-5">Campañas Grupales de {{ $institution->short_name }}</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    @foreach($campaigns as $campaign)
                        <x-campaign-card :campaign="$campaign" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Catálogo de la Institución --}}
        <section>
            <h2 class="text-2xl font-extrabold text-[#0F2D4A] mb-5">Tienda Oficial de {{ $institution->short_name }}</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>

        {{-- Comunidades --}}
        <section>
            <h2 class="text-2xl font-extrabold text-[#0F2D4A] mb-5">Comunidades de {{ $institution->short_name }}</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($institution->communities as $community)
                    <a href="{{ route('communities.show', $community->slug) }}" class="bg-white rounded-2xl border border-slate-200 p-5 hover:border-[#1D70B8] transition">
                        <span class="text-[11px] font-bold uppercase" style="color: {{ $institution->primary_color }}">{{ $community->typeLabel() }}</span>
                        <h3 class="mt-1 font-bold text-slate-900">{{ $community->name }}</h3>
                        <p class="mt-1 text-xs text-slate-500 line-clamp-2">{{ $community->description }}</p>
                        <p class="mt-3 text-xs font-semibold text-slate-700">{{ $community->members_count }} miembros · {{ $community->posts_count }} publicaciones</p>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
@endsection
