@props(['campaign'])

<article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
    <div class="h-1.5" style="background: linear-gradient(90deg, {{ $campaign->institution->primary_color }}, {{ $campaign->institution->secondary_color }})"></div>
    <div class="p-5 flex-1 flex flex-col justify-between gap-4">
        <div>
            <div class="flex items-center justify-between gap-2">
                <div class="inline-flex items-center gap-2">
                    @if($campaign->institution->logo_path)
                        <img src="{{ asset(ltrim($campaign->institution->logo_path, '/')) }}" alt="{{ $campaign->institution->short_name }}" class="w-6 h-6 object-contain">
                    @endif
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold text-white" style="background-color: {{ $campaign->institution->primary_color }}">
                        {{ $campaign->institution->short_name }}
                    </span>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $campaign->status === 'goal_reached' ? 'bg-emerald-100 text-emerald-800' : 'bg-sky-50 text-[#1D70B8]' }}">
                    {{ $campaign->statusLabel() }}
                </span>
            </div>

            <h3 class="mt-3 text-base font-bold text-[#0F2D4A] leading-snug">
                <a href="{{ route('campaigns.show', $campaign->slug) }}" class="hover:text-[#1D70B8]">{{ $campaign->title }}</a>
            </h3>
            <p class="mt-1.5 text-xs text-slate-600 line-clamp-2">{{ $campaign->description }}</p>
        </div>

        <div class="space-y-3">
            <div>
                <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                    <span class="text-slate-700">{{ $campaign->current_units }} de {{ $campaign->target_units }} unidades</span>
                    <span class="text-[#1D70B8]">{{ $campaign->progressPercent() }}%</span>
                </div>
                <div class="w-full h-2.5 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 {{ $campaign->progressPercent() >= 100 ? 'bg-emerald-500' : 'bg-[#1D70B8]' }}" style="width: {{ $campaign->progressPercent() }}%"></div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                <div>
                    <span class="block text-xs text-slate-500">Precio campaña</span>
                    <span class="text-lg font-extrabold text-[#0F2D4A]">S/ {{ number_format($campaign->unit_price, 2) }}</span>
                    <span class="text-[11px] text-amber-700 font-semibold block">Adelanto {{ $campaign->deposit_percent }}%</span>
                </div>
                <a href="{{ route('campaigns.show', $campaign->slug) }}" class="px-4 py-2.5 rounded-xl text-white text-xs font-bold transition hover:opacity-95" style="background-color: {{ $campaign->institution->primary_color }}">
                    Unirme ahora
                </a>
            </div>
        </div>
    </div>
</article>
