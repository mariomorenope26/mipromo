@extends('layouts.admin')

@section('heading', 'Dashboard Ejecutivo MiPromo')
@section('subheading', 'Indicadores clave (KPIs), rentabilidad Compra/Venta, proveedores, alertas de stock y control de propósito')

@section('content')
<div class="space-y-6">
    @if(auth()->user()->isRole('super_admin'))
        {{-- Accesos Ejecutivos Superadmin --}}
        <div class="bg-gradient-to-r from-[#0F2D4A] to-[#1D70B8] rounded-2xl p-5 text-white flex flex-wrap items-center justify-between gap-4 shadow-sm">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-sky-200">Centro de Control Superadmin</p>
                <h2 class="text-lg font-extrabold mt-0.5">Catálogo Integral, Proveedores Homologados y 10 Reportes Gerenciales</h2>
                <p class="text-xs text-white/80 mt-1">
                    {{ $superadminSummary['published_institutions'] }}/{{ $superadminSummary['total_institutions'] }} colegios en web principal ·
                    {{ $superadminSummary['products_count'] }} productos con compra/venta ·
                    {{ $superadminSummary['verified_vendors'] }}/{{ $superadminSummary['total_vendors'] }} proveedores verificados ·
                    Utilidad bruta órdenes: S/ {{ number_format($superadminSummary['estimated_profit'], 2) }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs">
                <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-[#0F2D4A] font-extrabold transition">Ver 10 Reportes Gerenciales →</a>
                <a href="{{ route('admin.catalog.index') }}" class="px-4 py-2 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold transition">CRUD Catálogo</a>
                <a href="{{ route('admin.vendors.index') }}" class="px-4 py-2 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold transition">Área Proveedores</a>
            </div>
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="grid sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500">Ventas Totales</p>
            <p class="text-2xl font-black text-[#0F2D4A] mt-1">S/ {{ number_format($totalSales, 2) }}</p>
            <p class="text-[11px] text-slate-400 mt-1">{{ $ordersCount }} pedidos · Utilidad S/ {{ number_format($superadminSummary['estimated_profit'], 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500">Pagos por Conciliar</p>
            <p class="text-2xl font-black text-amber-600 mt-1">{{ $pendingPayments }}</p>
            <a href="{{ route('admin.orders.index') }}" class="text-[11px] font-bold text-[#1D70B8] hover:underline mt-1 inline-block">Revisar vouchers →</a>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500">Campañas Activas</p>
            <p class="text-2xl font-black text-[#1D70B8] mt-1">{{ $activeCampaigns }}</p>
            <a href="{{ route('admin.campaigns.index') }}" class="text-[11px] font-bold text-[#1D70B8] hover:underline mt-1 inline-block">Gestionar campañas →</a>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500">Egresados Verificados</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $alumniVerified }}</p>
            <p class="text-[11px] text-amber-700 font-semibold mt-1">{{ $alumniPending }} pendientes de validar</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500">Moderación &amp; Quejas</p>
            <p class="text-2xl font-black text-rose-600 mt-1">{{ $openReports + $pendingComplaints }}</p>
            <a href="{{ route('admin.moderation.index') }}" class="text-[11px] font-bold text-[#1D70B8] hover:underline mt-1 inline-block">Atender alertas →</a>
        </div>
    </div>

    <div class="grid lg:grid-cols-12 gap-6">
        {{-- Pedidos recientes --}}
        <div class="lg:col-span-7 min-w-0 bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-extrabold text-[#0F2D4A]">Últimos Pedidos</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-[#1D70B8] hover:underline">Ver todos →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-left">
                        <tr>
                            <th class="p-2.5">Pedido</th>
                            <th class="p-2.5">Cliente</th>
                            <th class="p-2.5">Institución</th>
                            <th class="p-2.5">Total</th>
                            <th class="p-2.5">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentOrders as $order)
                            <tr>
                                <td class="p-2.5 font-bold text-[#1D70B8]">{{ $order->order_number }}</td>
                                <td class="p-2.5">{{ $order->customer_name }}</td>
                                <td class="p-2.5">{{ $order->institution?->short_name ?? 'General' }}</td>
                                <td class="p-2.5 font-bold">S/ {{ number_format($order->total, 2) }}</td>
                                <td class="p-2.5"><span class="px-2 py-0.5 rounded bg-slate-100 font-semibold">{{ $order->orderStatusLabel() }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Alertas de stock bajo + Comisiones --}}
        <div class="lg:col-span-5 min-w-0 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
                <h2 class="font-extrabold text-[#0F2D4A]">Alertas de Stock Bajo</h2>
                @forelse($lowStockVariants as $variant)
                    <div class="flex items-center justify-between text-xs p-3 rounded-xl bg-amber-50 border border-amber-200">
                        <div>
                            <p class="font-bold text-slate-900">{{ $variant->product->name }}</p>
                            <p class="text-slate-600">{{ $variant->sku }} ({{ $variant->label() }})</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-amber-600 text-white font-bold">{{ $variant->stock }} uds</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-500">No hay variantes con stock crítico.</p>
                @endforelse
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
                <h2 class="font-extrabold text-[#0F2D4A]">Comisiones por Institución y Proveedores</h2>
                <div class="space-y-2 text-xs">
                    @foreach($institutionCommissions as $inst)
                        @if((float) $inst->commission_sum > 0 || $inst->is_featured)
                            <div class="flex justify-between p-2.5 rounded-xl bg-slate-50">
                                <span><strong>{{ $inst->short_name }}</strong> ({{ number_format($inst->commission_rate, 0) }}%)</span>
                                <span class="font-bold text-emerald-700">Aporte: S/ {{ number_format((float) $inst->commission_sum, 2) }}</span>
                            </div>
                        @endif
                    @endforeach
                    @foreach($vendorStats as $vendor)
                        <div class="flex justify-between p-2.5 rounded-xl bg-sky-50">
                            <span>
                                <strong>{{ $vendor->business_name }}</strong>
                                @if($vendor->is_verified)
                                    <span class="text-emerald-700 font-bold">✓</span>
                                @endif
                            </span>
                            <span class="font-bold text-[#0F2D4A]">Bruto: S/ {{ number_format((float) $vendor->gross_sales, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Auditoría --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
        <h2 class="font-extrabold text-[#0F2D4A]">Auditoría Reciente de Acciones Críticas</h2>
        <div class="divide-y divide-slate-100 text-xs">
            @foreach($recentLogs as $log)
                <div class="py-2 flex flex-wrap justify-between gap-2">
                    <span><strong class="text-[#1D70B8]">{{ $log->action }}</strong> — {{ $log->description }}</span>
                    <span class="text-slate-400">{{ $log->user?->name ?? 'Sistema' }} · {{ $log->created_at->diffForHumans() }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
