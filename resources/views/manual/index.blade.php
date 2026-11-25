@extends('layouts.app')

@section('title', 'Manual de Uso Oficial en Línea — MiPromo | Grupo JMSPERU')

@section('content')
<div class="bg-slate-50 min-h-screen"
     x-data="{
        search: '',
        audience: 'todos',
        lightboxSrc: null,
        lightboxTitle: '',
        matches(secTitle, secAudience, secSummary) {
            const okAudience = this.audience === 'todos' || this.audience === secAudience;
            const q = this.search.trim().toLowerCase();
            if (!q) return okAudience;
            return okAudience && (secTitle.toLowerCase().includes(q) || secSummary.toLowerCase().includes(q));
        }
     }">

    {{-- Hero Editorial Premium --}}
    <section class="bg-gradient-to-br from-[#0F2D4A] via-[#133B61] to-[#1D70B8] text-white border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-14 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="max-w-3xl space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-xs font-extrabold text-amber-300">
                    <span>Documentación Oficial · Edición Producción 2026</span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
                    Manual de Uso Interactivo <span class="text-sky-300">MiPromo Store</span>
                </h1>
                <p class="text-sm sm:text-base text-slate-200 leading-relaxed max-w-2xl">
                    Guía completa paso a paso con <strong>capturas reales en alta definición</strong> de cada función del sistema: tienda online, registro y búsqueda por DNI con <strong>apiperu.dev</strong>, pagos Yape/Plin + SUNAT, padrón de egresados, proveedores, 10 reportes gerenciales y configuración de producción.
                </p>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <a href="{{ route('manual.pdf') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-amber-400 hover:bg-amber-300 text-[#0F2D4A] font-extrabold text-sm shadow-lg transition">
                        <span>Descargar Manual Completo en PDF ↓</span>
                    </a>
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm border border-white/20 transition print:hidden">
                        Imprimir Vista
                    </button>
                    <a href="https://wa.me/51959199368?text={{ urlencode('Hola Grupo JMSPERU, necesito soporte sobre el uso del sistema MiPromo.') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-sm transition">
                        Soporte WhatsApp 959199368
                    </a>
                </div>
            </div>

            <div class="bg-white/10 backdrop-blur border border-white/20 rounded-3xl p-5 sm:p-6 max-w-sm w-full space-y-3 text-xs">
                <div class="flex items-center gap-3 bg-white rounded-2xl p-3">
                    <img src="{{ asset('images/logo_jmsperu.png') }}" alt="Grupo JMSPERU" class="h-9 w-auto object-contain">
                    <div class="border-l border-slate-200 pl-3">
                        <p class="font-extrabold text-[#0F2D4A] text-sm">Grupo JMSPERU</p>
                        <p class="text-[11px] text-slate-500">{{ count($sections) }} Módulos Documentados</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-center">
                    <div class="rounded-xl bg-white/10 p-2.5">
                        <p class="text-lg font-black text-amber-300">{{ count($sections) }}</p>
                        <p class="text-[11px] text-slate-200">Pantallas Reales</p>
                    </div>
                    <div class="rounded-xl bg-white/10 p-2.5">
                        <p class="text-lg font-black text-sky-300">PDF + Web</p>
                        <p class="text-[11px] text-slate-200">Doble Formato</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Barra de Filtros y Buscador --}}
    <div class="bg-white border-b border-slate-200 sticky top-16 z-30 print:hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold">
                @foreach([
                    'todos' => 'Todo el Sistema',
                    'cliente' => 'Compradores',
                    'egresado' => 'Ex-Alumnos',
                    'admin' => 'Delegados / Admin',
                    'superadmin' => 'Superadmin',
                ] as $key => $label)
                    <button type="button"
                            @click="audience = '{{ $key }}'"
                            :class="audience === '{{ $key }}' ? 'bg-[#0F2D4A] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-3.5 py-2 rounded-xl transition">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <div class="w-full sm:w-72">
                <input type="search" x-model="search" placeholder="Filtrar módulo (ej. DNI, reporte, Yape...)" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-[#1D70B8]/25">
            </div>
        </div>
    </div>

    {{-- Contenido con TOC Lateral Fijo --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid lg:grid-cols-12 gap-8 items-start">
        <aside class="lg:col-span-3 lg:sticky lg:top-36 bg-white rounded-2xl border border-slate-200 p-4 space-y-2 shadow-sm print:hidden">
            <p class="text-xs font-extrabold uppercase tracking-wider text-[#1D70B8] px-2 pb-1 border-b border-slate-100">Índice de Capítulos</p>
            <nav class="space-y-1 max-h-[65vh] overflow-y-auto pr-1 text-xs">
                @foreach($sections as $section)
                    <a href="#cap-{{ $section['id'] }}"
                       class="flex items-start gap-2 px-2.5 py-1.5 rounded-lg text-slate-600 hover:bg-sky-50 hover:text-[#0F2D4A] transition">
                        <span class="font-mono font-bold text-[#1D70B8] shrink-0">{{ $section['number'] }}.</span>
                        <span class="leading-snug">{{ $section['title'] }}</span>
                    </a>
                @endforeach
                <a href="#recomendaciones-finales" class="flex items-start gap-2 px-2.5 py-1.5 rounded-lg font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 transition mt-2">
                    <span>★</span>
                    <span>Recomendaciones y Producción</span>
                </a>
            </nav>
        </aside>

        <div class="lg:col-span-9 space-y-10">
            @foreach($sections as $section)
                <article id="cap-{{ $section['id'] }}"
                         x-show="matches(@js($section['title'].' '.$section['route']), @js($section['audience']), @js($section['summary'].' '.implode(' ', $section['steps'])))"
                         class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm scroll-mt-36">
                    <div class="px-6 py-5 bg-slate-50/80 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold">
                                <span class="px-2.5 py-0.5 rounded-full bg-[#0F2D4A] text-white">Capítulo {{ $section['number'] }}</span>
                                <span class="px-2.5 py-0.5 rounded-full bg-sky-100 text-[#1D70B8]">{{ $section['audience_label'] }}</span>
                                <span class="font-mono text-slate-500">Ruta: {{ $section['route'] }}</span>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-extrabold text-[#0F2D4A]">{{ $section['title'] }}</h2>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <p class="text-sm text-slate-600 leading-relaxed">{{ $section['summary'] }}</p>

                        {{-- Captura Real del Sistema con Ampliación --}}
                        @if(file_exists(public_path($section['image'])))
                            <div class="rounded-2xl border border-slate-200 overflow-hidden bg-slate-100 shadow-inner group relative">
                                <div class="bg-slate-200/80 px-3.5 py-2 flex items-center justify-between text-[11px] text-slate-600 font-mono">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                                        <span class="ml-2">mipromo.pe{{ $section['route'] === '/' ? '' : explode(' ', $section['route'])[0] }}</span>
                                    </div>
                                    <button type="button"
                                            @click="lightboxSrc = @js(asset($section['image'])); lightboxTitle = @js($section['number'].'. '.$section['title'])"
                                            class="font-sans font-bold text-[#1D70B8] hover:underline print:hidden">
                                        Ampliar captura ↗
                                    </button>
                                </div>
                                <img src="{{ asset($section['image']) }}"
                                     alt="Captura real: {{ $section['title'] }}"
                                     loading="lazy"
                                     @click="lightboxSrc = @js(asset($section['image'])); lightboxTitle = @js($section['number'].'. '.$section['title'])"
                                     class="w-full h-auto object-cover cursor-zoom-in border-t border-slate-200">
                            </div>
                        @endif

                        {{-- Pasos Operativos Detallados --}}
                        <div class="space-y-2.5">
                            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Procedimiento Paso a Paso</h3>
                            <ol class="space-y-2">
                                @foreach($section['steps'] as $idx => $step)
                                    <li class="flex items-start gap-3 text-sm text-slate-700 bg-slate-50/70 rounded-xl p-3 border border-slate-100">
                                        <span class="w-6 h-6 rounded-full bg-[#1D70B8] text-white text-xs font-extrabold flex items-center justify-center shrink-0">{{ $idx + 1 }}</span>
                                        <span class="leading-relaxed">{{ $step }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        </div>

                        {{-- Recomendaciones Profesionales --}}
                        <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 space-y-2">
                            <p class="text-xs font-extrabold uppercase tracking-wider text-amber-900">💡 Recomendaciones Profesionales</p>
                            <ul class="space-y-1.5 text-xs sm:text-sm text-amber-950 list-disc pl-5">
                                @foreach($section['recommendations'] as $rec)
                                    <li>{{ $rec }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </article>
            @endforeach

            {{-- Recomendaciones Generales & Producción --}}
            <section id="recomendaciones-finales" class="bg-[#0F2D4A] text-white rounded-3xl p-7 sm:p-8 space-y-5 shadow-lg scroll-mt-36">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/15 pb-4">
                    <div>
                        <span class="text-xs font-extrabold uppercase tracking-wider text-amber-300">Operación y Seguridad</span>
                        <h2 class="text-2xl font-extrabold mt-1">Recomendaciones Clave para Producción y Despliegue</h2>
                    </div>
                    <a href="{{ route('manual.pdf') }}" class="px-4 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-[#0F2D4A] text-xs font-extrabold transition">
                        Descargar en PDF ↓
                    </a>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach($bestPractices as $bp)
                        <div class="rounded-2xl bg-white/10 border border-white/15 p-4 space-y-1.5">
                            <h3 class="text-sm font-extrabold text-sky-200">{{ $bp['title'] }}</h3>
                            <p class="text-xs text-slate-200 leading-relaxed">{{ $bp['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>

    {{-- Lightbox Ampliador de Capturas --}}
    <div x-show="lightboxSrc" x-cloak @keydown.escape.window="lightboxSrc = null"
         class="fixed inset-0 z-50 bg-slate-950/85 backdrop-blur-sm flex items-center justify-center p-4"
         @click.self="lightboxSrc = null">
        <div class="bg-white rounded-2xl max-w-6xl w-full overflow-hidden shadow-2xl">
            <div class="px-5 py-3 border-b border-slate-200 flex items-center justify-between">
                <p class="text-sm font-extrabold text-[#0F2D4A]" x-text="lightboxTitle"></p>
                <button type="button" @click="lightboxSrc = null" class="px-3 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold">Cerrar ✕</button>
            </div>
            <div class="p-2 bg-slate-100 max-h-[82vh] overflow-auto">
                <img :src="lightboxSrc" alt="Vista ampliada" class="w-full h-auto rounded-lg">
            </div>
        </div>
    </div>
</div>
@endsection
