@extends('layouts.admin')

@section('heading', 'Campañas Grupales y Producción Consolidada')
@section('subheading', 'Crea campañas por promoción, controla metas mínimas y consolida lotes de fabricación')

@section('content')
<div class="space-y-8 min-w-0">
    <form action="{{ route('admin.campaigns.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 space-y-4 min-w-0">
        @csrf
        <h2 class="font-extrabold text-[#0F2D4A]">Lanzar Nueva Campaña Grupal</h2>
        <div class="grid sm:grid-cols-3 gap-3 text-sm">
            <select name="institution_id" required class="w-full min-w-0 px-3 py-2 rounded-xl border border-slate-300">
                @foreach($institutions as $inst)
                    <option value="{{ $inst->id }}">{{ $inst->short_name }}</option>
                @endforeach
            </select>
            <select name="promotion_id" class="w-full min-w-0 px-3 py-2 rounded-xl border border-slate-300">
                <option value="">Promoción (opcional)</option>
                @foreach($promotions as $promo)
                    <option value="{{ $promo->id }}">{{ $promo->institution->short_name }} — {{ $promo->year }}</option>
                @endforeach
            </select>
            <select name="product_id" required class="w-full min-w-0 px-3 py-2 rounded-xl border border-slate-300">
                @foreach($products as $prod)
                    <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                @endforeach
            </select>
            <input type="text" name="title" required placeholder="Título de la campaña" class="w-full min-w-0 sm:col-span-2 px-3 py-2 rounded-xl border border-slate-300">
            <input type="number" step="0.01" name="unit_price" required placeholder="Precio grupal S/" class="w-full min-w-0 px-3 py-2 rounded-xl border border-slate-300">
            <input type="number" name="deposit_percent" value="50" min="10" max="100" required placeholder="% Adelanto" class="w-full min-w-0 px-3 py-2 rounded-xl border border-slate-300">
            <input type="number" name="target_units" value="30" min="5" required placeholder="Meta mínima uds" class="w-full min-w-0 px-3 py-2 rounded-xl border border-slate-300">
            <input type="date" name="ends_at" required value="{{ now()->addDays(20)->format('Y-m-d') }}" class="w-full min-w-0 px-3 py-2 rounded-xl border border-slate-300">
            <select name="delivery_mode" class="w-full min-w-0 px-3 py-2 rounded-xl border border-slate-300">
                <option value="consolidated_delegate">Envío consolidado al delegado</option>
                <option value="individual">Envío individual</option>
            </select>
            <input type="text" name="description" required placeholder="Descripción de campaña..." class="w-full min-w-0 sm:col-span-2 px-3 py-2 rounded-xl border border-slate-300">
        </div>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1D70B8] text-white text-xs font-bold">Publicar Campaña</button>
    </form>

    <div class="space-y-4 min-w-0">
        @foreach($campaigns as $campaign)
            <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 space-y-4 min-w-0">
                <div class="flex flex-wrap items-center justify-between gap-4 min-w-0">
                    <div class="min-w-0">
                        <span class="text-xs font-bold text-[#1D70B8]">{{ $campaign->institution->short_name }} · {{ $campaign->current_units }}/{{ $campaign->target_units }} uds ({{ $campaign->progressPercent() }}%)</span>
                        <h3 class="text-lg font-extrabold text-[#0F2D4A] break-words">{{ $campaign->title }}</h3>
                    </div>

                    <form action="{{ route('admin.campaigns.status', $campaign->id) }}" method="POST" class="flex flex-wrap items-center gap-2 text-xs max-w-full">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="min-w-0 max-w-full px-3 py-2 rounded-xl border border-slate-300">
                            @foreach(['active' => 'Activa', 'goal_reached' => 'Meta alcanzada', 'in_production' => 'En producción', 'closed' => 'Cerrada', 'cancelled' => 'Cancelada'] as $st => $label)
                                <option value="{{ $st }}" @selected($campaign->status === $st)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white font-bold">Actualizar</button>
                    </form>
                </div>

                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 text-xs flex flex-wrap items-center justify-between gap-3 min-w-0">
                    <div class="min-w-0">
                        <strong class="block text-slate-700 mb-2">Resumen Consolidado para Taller de Producción:</strong>
                        <div class="flex flex-wrap gap-2">
                            @foreach($campaign->participants->groupBy(fn($p) => $p->variant?->label() ?? 'Estándar') as $variantLabel => $group)
                                <span class="px-3 py-1 rounded-full bg-white border border-slate-200 font-bold">
                                    {{ $variantLabel }}: {{ $group->sum('quantity') }} uds
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <a href="{{ route('admin.campaigns.production', $campaign->slug) }}" class="px-4 py-2 rounded-xl bg-[#0F2D4A] text-white font-bold text-center whitespace-normal sm:shrink-0">Orden de Producción Consolidada →</a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
