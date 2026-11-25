@props(['institution'])

<article class="group bg-white rounded-3xl border border-slate-200/90 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between relative">
    {{-- Barra bicolor institucional --}}
    <div class="h-2 w-full" style="background: linear-gradient(90deg, {{ $institution->primary_color }} 0%, {{ $institution->secondary_color }} 100%)"></div>

    <div class="p-6 flex-1 flex flex-col justify-between gap-5">
        <div>
            {{-- Cabecera con insignia PNG transparente sin fondo y badge de ciudad/historia --}}
            <div class="flex items-start justify-between gap-4">
                <a href="{{ route('institutions.show', $institution->slug) }}"
                   class="w-20 h-20 rounded-2xl flex items-center justify-center p-2 shrink-0 border transition group-hover:scale-105 duration-300"
                   style="background: radial-gradient(circle at 30% 30%, {{ $institution->secondary_color }}18, {{ $institution->primary_color }}10); border-color: {{ $institution->primary_color }}28;">
                    @if($institution->logo_path)
                        <img src="{{ asset(ltrim($institution->logo_path, '/')) }}"
                             alt="Insignia {{ $institution->short_name }}"
                             class="w-full h-full object-contain drop-shadow-sm">
                    @else
                        <span class="text-xl font-black" style="color: {{ $institution->primary_color }}">
                            {{ strtoupper(substr($institution->short_name, 0, 2)) }}
                        </span>
                    @endif
                </a>

                <div class="text-right space-y-1.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold"
                          style="background-color: {{ $institution->primary_color }}14; color: {{ $institution->primary_color }}">
                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $institution->primary_color }}"></span>
                        {{ $institution->city }}
                    </span>
                    @if($institution->founded_year)
                        <p class="text-[11px] font-semibold text-slate-500">
                            Fund. {{ $institution->founded_year }} · <span class="text-slate-700 font-bold">{{ $institution->yearsOfHistory() }} años</span>
                        </p>
                    @endif
                </div>
            </div>

            {{-- Nombre y Lema --}}
            <div class="mt-4">
                <h3 class="text-base font-extrabold text-slate-900 leading-snug group-hover:underline decoration-2 underline-offset-4"
                    style="text-decoration-color: {{ $institution->primary_color }}">
                    <a href="{{ route('institutions.show', $institution->slug) }}">{{ $institution->name }}</a>
                </h3>
                @if($institution->motto)
                    <p class="mt-1.5 text-xs italic text-slate-500 line-clamp-2">“{{ $institution->motto }}”</p>
                @endif
            </div>
        </div>

        {{-- Paleta institucional y CTA con colores del colegio --}}
        <div class="pt-4 border-t border-slate-100 space-y-3">
            <div class="flex items-center justify-between text-[11px] text-slate-500">
                <div class="flex items-center gap-2">
                    <span class="flex items-center gap-1" title="Colores oficiales del colegio">
                        <span class="w-3.5 h-3.5 rounded-full border border-black/10 shadow-xs" style="background-color: {{ $institution->primary_color }}"></span>
                        <span class="w-3.5 h-3.5 rounded-full border border-black/10 shadow-xs" style="background-color: {{ $institution->secondary_color }}"></span>
                    </span>
                    <span>Colores oficiales</span>
                </div>
                <span class="font-semibold text-slate-600">
                    {{ $institution->active_delegates_count ?? $institution->activeDelegates()->count() }} delegados · {{ $institution->promotions_count ?? $institution->promotions()->count() }} promos
                </span>
            </div>

            <a href="{{ route('institutions.show', $institution->slug) }}"
               class="flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-xl text-white text-xs font-bold shadow-xs hover:opacity-95 transition"
               style="background: linear-gradient(90deg, {{ $institution->primary_color }} 0%, {{ $institution->primary_color }}ee 100%)">
                <span>Visitar Perfil y Tienda</span>
                <span>→</span>
            </a>
        </div>
    </div>
</article>
