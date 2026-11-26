@extends('layouts.admin')

@section('heading', 'Centro de Reportes Profesionales y Gerenciales')
@section('subheading', '10 reportes estratégicos para medir el avance del propósito, rentabilidad, proveedores, stock, SUNAT y comunidades')

@section('content')
<div class="space-y-8">
    {{-- Filtros Globales e Índice de los 10 Reportes --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4 print:hidden">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-extrabold text-[#0F2D4A]">Panel de Control Gerencial — Grupo JMSPERU</h2>
                <p class="text-xs text-slate-500">Filtra por rango de fechas o región y exporta cualquier reporte en CSV o imprime en PDF.</p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs">
                <a href="{{ route('admin.reports.export', ['report' => 'resumen', 'desde' => $from, 'hasta' => $to, 'region' => $region]) }}" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-slate-50 font-bold text-slate-700 hover:bg-slate-100">
                    Exportar Resumen CSV
                </a>
                <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl bg-[#0F2D4A] text-white font-bold">
                    Imprimir / Guardar PDF
                </button>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid sm:grid-cols-4 gap-3 text-xs bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
            <div>
                <label class="block font-bold text-slate-600 mb-1">Desde</label>
                <input type="date" name="desde" value="{{ $from }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white">
            </div>
            <div>
                <label class="block font-bold text-slate-600 mb-1">Hasta</label>
                <input type="date" name="hasta" value="{{ $to }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white">
            </div>
            <div>
                <label class="block font-bold text-slate-600 mb-1">Región</label>
                <select name="region" class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white">
                    <option value="">Todas las regiones</option>
                    @foreach($availableRegions as $reg)
                        <option value="{{ $reg }}" @selected($region === $reg)>{{ $reg }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-[#1D70B8] text-white font-bold">Aplicar Filtros</button>
                <a href="{{ route('admin.reports.index') }}" class="px-3 py-2.5 rounded-xl border border-slate-300 bg-white font-semibold text-slate-600">Limpiar</a>
            </div>
        </form>

        <div class="flex flex-wrap gap-2 text-[11px] font-bold">
            <a href="#rep-1" class="px-3 py-1.5 rounded-full bg-sky-50 text-[#0F2D4A] hover:bg-sky-100">1. Propósito del Sistema</a>
            <a href="#rep-2" class="px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-800 hover:bg-emerald-100">2. Rentabilidad Compra vs Venta</a>
            <a href="#rep-3" class="px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200">3. Colegios & Aportes</a>
            <a href="#rep-4" class="px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200">4. Campañas Grupales</a>
            <a href="#rep-5" class="px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200">5. Proveedores & Liquidaciones</a>
            <a href="#rep-6" class="px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200">6. Salud de Inventario</a>
            <a href="#rep-7" class="px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200">7. Pagos & SUNAT</a>
            <a href="#rep-8" class="px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200">8. Logística & Devoluciones</a>
            <a href="#rep-9" class="px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200">9. Egresados, Colectas & Referidos</a>
            <a href="#rep-10" class="px-3 py-1.5 rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200">10. Legal & Auditoría</a>
        </div>
    </div>

    {{-- REPORTE 1: AVANCE DEL PROPÓSITO DEL SISTEMA Y COBERTURA NACIONAL --}}
    <section id="rep-1" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#1D70B8]">Reporte Gerencial 01</span>
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Avance del Propósito del Sistema y Cobertura Regional</h2>
                <p class="text-xs text-slate-500">Medición integral de la misión MiPromo: colegios históricos activos, delegados acreditados, homologación de talleres y comunidades verificadas.</p>
            </div>
            <div class="rounded-2xl bg-[#0F2D4A] text-white px-5 py-3 text-center">
                <p class="text-[10px] uppercase tracking-wider text-sky-300 font-bold">Índice Global de Avance</p>
                <p class="text-3xl font-black">{{ $purposeProgress['overall_score'] }}%</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div class="space-y-3 min-w-0">
                <h3 class="text-xs font-extrabold uppercase text-slate-500">Hitos Estratégicos del Propósito</h3>
                @foreach($purposeProgress['milestones'] as $m)
                    <div class="space-y-1 text-xs">
                        <div class="flex justify-between font-semibold">
                            <span>{{ $m['label'] }}</span>
                            <span class="text-[#0F2D4A] font-bold">{{ $m['current'] }} / {{ $m['target'] }} ({{ $m['percent'] }}%)</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full {{ $m['percent'] >= 80 ? 'bg-emerald-500' : ($m['percent'] >= 50 ? 'bg-[#1D70B8]' : 'bg-amber-500') }}" style="width: {{ min(100, $m['percent']) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="min-w-0">
                <h3 class="text-xs font-extrabold uppercase text-slate-500 mb-3">Cobertura Regional (>10 años de trayectoria)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-left">
                            <tr>
                                <th class="p-2">Región</th>
                                <th class="p-2">Colegios</th>
                                <th class="p-2">Publicados</th>
                                <th class="p-2">Antigüedad Prom.</th>
                                <th class="p-2">Delegados</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($regionalCoverage as $row)
                                <tr>
                                    <td class="p-2 font-bold text-[#0F2D4A]">{{ $row['region'] }}</td>
                                    <td class="p-2">{{ $row['total'] }}</td>
                                    <td class="p-2 font-bold text-emerald-700">{{ $row['published'] }}</td>
                                    <td class="p-2">{{ $row['avg_years'] }} años</td>
                                    <td class="p-2">{{ $row['delegates'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- REPORTE 2: RENTABILIDAD COMPRA VS VENTA --}}
    <section id="rep-2" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Reporte Gerencial 02</span>
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Rentabilidad Comercial: Precio de Compra vs. Precio de Venta</h2>
                <p class="text-xs text-slate-500">Margen bruto real por producto y proveedor basado en el costo de compra y precio de venta de órdenes activas.</p>
            </div>
            <a href="{{ route('admin.reports.export', ['report' => 'r2-rentabilidad', 'desde' => $from, 'hasta' => $to, 'region' => $region]) }}" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50">CSV Rentabilidad</a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
            <div class="rounded-xl bg-slate-50 p-3.5"><p class="text-slate-500">Ingreso Ítems</p><p class="text-lg font-black text-[#0F2D4A]">S/ {{ number_format($financialSummary['items_revenue'], 2) }}</p></div>
            <div class="rounded-xl bg-amber-50 p-3.5"><p class="text-amber-800">Costo de Compra</p><p class="text-lg font-black text-amber-900">S/ {{ number_format($financialSummary['total_cost'], 2) }}</p></div>
            <div class="rounded-xl bg-emerald-50 p-3.5"><p class="text-emerald-800">Utilidad Bruta</p><p class="text-lg font-black text-emerald-800">S/ {{ number_format($financialSummary['gross_profit'], 2) }}</p></div>
            <div class="rounded-xl bg-sky-50 p-3.5"><p class="text-[#1D70B8]">Margen Bruto</p><p class="text-lg font-black text-[#0F2D4A]">{{ $financialSummary['margin_percent'] }}%</p></div>
            <div class="rounded-xl bg-slate-50 p-3.5"><p class="text-slate-500">Ticket Promedio</p><p class="text-lg font-black text-[#0F2D4A]">S/ {{ number_format($financialSummary['avg_ticket'], 2) }}</p></div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-left">
                    <tr>
                        <th class="p-2.5">Producto</th>
                        <th class="p-2.5">Proveedor (Superadmin)</th>
                        <th class="p-2.5 text-right">Uds</th>
                        <th class="p-2.5 text-right">Compra Unit.</th>
                        <th class="p-2.5 text-right">Venta Unit.</th>
                        <th class="p-2.5 text-right">Costo Total</th>
                        <th class="p-2.5 text-right">Ingreso Total</th>
                        <th class="p-2.5 text-right">Utilidad</th>
                        <th class="p-2.5 text-right">Margen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($productMargins as $pm)
                        <tr>
                            <td class="p-2.5 font-bold text-slate-900">{{ $pm['product_name'] }}</td>
                            <td class="p-2.5">{{ $pm['vendor_name'] }} @if($pm['is_verified']) <span class="text-emerald-700 font-bold">✓</span> @endif</td>
                            <td class="p-2.5 text-right">{{ $pm['units'] }}</td>
                            <td class="p-2.5 text-right text-amber-800">S/ {{ number_format($pm['avg_cost'], 2) }}</td>
                            <td class="p-2.5 text-right font-semibold">S/ {{ number_format($pm['avg_sale'], 2) }}</td>
                            <td class="p-2.5 text-right">S/ {{ number_format($pm['total_cost'], 2) }}</td>
                            <td class="p-2.5 text-right font-bold">S/ {{ number_format($pm['total_revenue'], 2) }}</td>
                            <td class="p-2.5 text-right font-extrabold text-emerald-700">S/ {{ number_format($pm['gross_profit'], 2) }}</td>
                            <td class="p-2.5 text-right font-bold">{{ $pm['margin_percent'] }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="p-4 text-center text-slate-500">Sin ventas en el rango seleccionado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- REPORTE 3: DESEMPEÑO POR COLEGIO Y APORTE SOCIAL --}}
    <section id="rep-3" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#1D70B8]">Reporte Gerencial 03</span>
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Desempeño Comercial por Colegio y Fondo de Aporte Social</h2>
                <p class="text-xs text-slate-500">Facturación, comisión institucional retornada a cada colegio/asociación y tamaño de comunidad verificada.</p>
            </div>
            <a href="{{ route('admin.reports.export', ['report' => 'r3-instituciones', 'desde' => $from, 'hasta' => $to, 'region' => $region]) }}" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50">CSV Colegios</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-left">
                    <tr>
                        <th class="p-2.5">Colegio</th>
                        <th class="p-2.5">Ciudad / Región</th>
                        <th class="p-2.5">Web Principal</th>
                        <th class="p-2.5 text-right">Pedidos</th>
                        <th class="p-2.5 text-right">Ventas</th>
                        <th class="p-2.5 text-right">Aporte %</th>
                        <th class="p-2.5 text-right">Fondo Generado</th>
                        <th class="p-2.5 text-right">Egresados Verif.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($institutionPerformance as $ip)
                        <tr>
                            <td class="p-2.5 font-bold text-[#0F2D4A]">{{ $ip['name'] }}</td>
                            <td class="p-2.5">{{ $ip['city'] }} · {{ $ip['region'] }}</td>
                            <td class="p-2.5"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ip['is_published'] ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $ip['is_published'] ? 'Publicado' : 'Pendiente' }}</span></td>
                            <td class="p-2.5 text-right">{{ $ip['orders_count'] }}</td>
                            <td class="p-2.5 text-right font-bold">S/ {{ number_format($ip['sales_total'], 2) }}</td>
                            <td class="p-2.5 text-right">{{ $ip['commission_rate'] }}%</td>
                            <td class="p-2.5 text-right font-extrabold text-emerald-700">S/ {{ number_format($ip['commission_amount'], 2) }}</td>
                            <td class="p-2.5 text-right">{{ $ip['verified_alumni'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- REPORTE 4: CAMPAÑAS GRUPALES --}}
        <section id="rep-4" class="min-w-0 bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Reporte Gerencial 04</span>
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Cumplimiento de Campañas Grupales</h2>
                <p class="text-xs text-slate-500">Avance hacia la meta mínima de producción, adelantos cobrados y saldos pendientes.</p>
            </div>
            <div class="space-y-3 text-xs">
                @foreach($campaignPerformance as $cp)
                    <div class="rounded-xl bg-slate-50 border border-slate-100 p-3.5 space-y-1.5">
                        <div class="flex justify-between gap-2">
                            <strong class="text-[#0F2D4A]">{{ $cp['title'] }}</strong>
                            <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-bold">{{ $cp['status'] }}</span>
                        </div>
                        <p class="text-slate-500">{{ $cp['institution'] }} · {{ $cp['current_units'] }}/{{ $cp['target_units'] }} uds ({{ $cp['progress'] }}%)</p>
                        <div class="h-2 rounded-full bg-slate-200 overflow-hidden">
                            <div class="h-full bg-[#1D70B8]" style="width: {{ min(100, $cp['progress']) }}%"></div>
                        </div>
                        <div class="flex justify-between text-[11px] pt-1">
                            <span>Comprometido: <strong>S/ {{ number_format($cp['committed'], 2) }}</strong></span>
                            <span class="text-emerald-700">Cobrado: <strong>S/ {{ number_format($cp['paid'], 2) }}</strong></span>
                            <span class="text-amber-700">Saldo: <strong>S/ {{ number_format($cp['pending_balance'], 2) }}</strong></span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- REPORTE 5: RANKING DE PROVEEDORES --}}
        <section id="rep-5" class="min-w-0 bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Reporte Gerencial 05</span>
                    <h2 class="text-lg font-extrabold text-[#0F2D4A]">Proveedores, Verificación y Liquidaciones</h2>
                </div>
                <a href="{{ route('admin.reports.export', ['report' => 'r5-proveedores', 'desde' => $from, 'hasta' => $to, 'region' => $region]) }}" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50">CSV Proveedores</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-left">
                        <tr>
                            <th class="p-2">Proveedor</th>
                            <th class="p-2">Verif.</th>
                            <th class="p-2 text-right">Prod.</th>
                            <th class="p-2 text-right">Costo</th>
                            <th class="p-2 text-right">Ventas</th>
                            <th class="p-2 text-right">Pagado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($vendorPerformance as $vp)
                            <tr>
                                <td class="p-2 font-bold text-[#0F2D4A]">{{ $vp['business_name'] }}<span class="block text-[10px] font-normal text-slate-500">RUC {{ $vp['ruc'] }} · {{ $vp['city'] }}</span></td>
                                <td class="p-2">{!! $vp['is_verified'] ? '<span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">✓ Sí</span>' : '<span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold">No</span>' !!}</td>
                                <td class="p-2 text-right">{{ $vp['products_count'] }}</td>
                                <td class="p-2 text-right text-amber-800">S/ {{ number_format($vp['purchase_cost'], 2) }}</td>
                                <td class="p-2 text-right font-bold">S/ {{ number_format($vp['gross_sales'], 2) }}</td>
                                <td class="p-2 text-right text-emerald-700 font-bold">S/ {{ number_format($vp['paid_settlements'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- REPORTE 6: SALUD DEL INVENTARIO Y VALORIZACIÓN --}}
    <section id="rep-6" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#1D70B8]">Reporte Gerencial 06</span>
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Salud de Inventario, Valorización a Costo/Venta y Quiebres de Stock</h2>
            </div>
            <a href="{{ route('admin.reports.export', ['report' => 'r6-inventario', 'desde' => $from, 'hasta' => $to, 'region' => $region]) }}" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50">CSV Stock Crítico</a>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 text-xs">
            <div class="rounded-xl bg-slate-50 p-3.5"><p class="text-slate-500">SKUs Activos</p><p class="text-lg font-black text-[#0F2D4A]">{{ $inventorySummary['total_skus'] }}</p></div>
            <div class="rounded-xl bg-slate-50 p-3.5"><p class="text-slate-500">Unidades Stock</p><p class="text-lg font-black text-[#0F2D4A]">{{ number_format($inventorySummary['total_units']) }}</p></div>
            <div class="rounded-xl bg-amber-50 p-3.5"><p class="text-amber-800">Valorizado Costo</p><p class="text-lg font-black text-amber-900">S/ {{ number_format($inventorySummary['valuation_cost'], 2) }}</p></div>
            <div class="rounded-xl bg-emerald-50 p-3.5"><p class="text-emerald-800">Valorizado Venta</p><p class="text-lg font-black text-emerald-800">S/ {{ number_format($inventorySummary['valuation_sale'], 2) }}</p></div>
            <div class="rounded-xl bg-rose-50 p-3.5"><p class="text-rose-700">Agotados</p><p class="text-lg font-black text-rose-700">{{ $inventorySummary['out_of_stock'] }}</p></div>
            <div class="rounded-xl bg-amber-50 p-3.5"><p class="text-amber-700">Stock Bajo</p><p class="text-lg font-black text-amber-700">{{ $inventorySummary['low_stock_count'] }}</p></div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6 text-xs">
            <div class="min-w-0">
                <h3 class="font-bold uppercase text-slate-500 mb-2">Stock por Categoría</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-left">
                            <tr><th class="p-2">Categoría</th><th class="p-2 text-right">Productos</th><th class="p-2 text-right">Unidades</th><th class="p-2 text-right">Valor Costo</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($stockByCategory as $sc)
                                <tr>
                                    <td class="p-2 font-bold">{{ $sc['name'] }}</td>
                                    <td class="p-2 text-right">{{ $sc['products'] }}</td>
                                    <td class="p-2 text-right">{{ $sc['units'] }}</td>
                                    <td class="p-2 text-right font-bold">S/ {{ number_format($sc['valuation_cost'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="min-w-0">
                <h3 class="font-bold uppercase text-slate-500 mb-2">Variantes en Riesgo de Reposición</h3>
                <div class="space-y-2">
                    @forelse($criticalStockVariants as $variant)
                        <div class="flex items-center justify-between gap-2 p-2.5 rounded-xl bg-amber-50 border border-amber-200">
                            <div class="min-w-0">
                                <p class="font-bold text-slate-900 truncate">{{ $variant->product?->name }}</p>
                                <p class="text-slate-600 truncate">{{ $variant->sku }} · {{ $variant->product?->vendor?->business_name ?? 'Directo' }}</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-amber-600 text-white font-bold shrink-0">{{ $variant->stock }} uds</span>
                        </div>
                    @empty
                        <p class="text-slate-500">No hay variantes en nivel crítico.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- REPORTE 7: PAGOS Y FACTURACIÓN SUNAT --}}
        <section id="rep-7" class="min-w-0 bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#1D70B8]">Reporte Gerencial 07</span>
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Pagos, Conciliación y Facturación Electrónica SUNAT</h2>
            </div>

            <div class="grid grid-cols-3 gap-2 text-xs text-center">
                <div class="rounded-xl bg-slate-50 p-3"><p class="text-slate-500">Boletas B001</p><p class="text-lg font-black">{{ $sunatSummary['boletas_count'] }}</p></div>
                <div class="rounded-xl bg-slate-50 p-3"><p class="text-slate-500">Facturas F001</p><p class="text-lg font-black">{{ $sunatSummary['facturas_count'] }}</p></div>
                <div class="rounded-xl bg-rose-50 p-3"><p class="text-rose-700">Notas Créd. NC01</p><p class="text-lg font-black text-rose-700">{{ $sunatSummary['credit_notes_count'] }}</p></div>
            </div>
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-xs flex flex-wrap justify-between gap-2">
                <span>Gravado: <strong>S/ {{ number_format($sunatSummary['subtotal_gravado'], 2) }}</strong></span>
                <span>IGV (18%): <strong>S/ {{ number_format($sunatSummary['igv_total'], 2) }}</strong></span>
                <span>Total Facturado: <strong>S/ {{ number_format($sunatSummary['invoiced_total'], 2) }}</strong></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-left">
                        <tr><th class="p-2">Método Pago</th><th class="p-2 text-right">Ops</th><th class="p-2 text-right">Conciliado</th><th class="p-2 text-right">Por Revisar</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($paymentsByMethod as $pm)
                            <tr>
                                <td class="p-2 font-bold">{{ $pm['method'] }}</td>
                                <td class="p-2 text-right">{{ $pm['count'] }}</td>
                                <td class="p-2 text-right text-emerald-700 font-bold">S/ {{ number_format($pm['approved_amount'], 2) }}</td>
                                <td class="p-2 text-right text-amber-700 font-bold">S/ {{ number_format($pm['pending_amount'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- REPORTE 8: LOGÍSTICA NACIONAL Y DEVOLUCIONES --}}
        <section id="rep-8" class="min-w-0 bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#1D70B8]">Reporte Gerencial 08</span>
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Logística Nacional, Zonas de Envío y Devoluciones</h2>
            </div>

            <div class="grid sm:grid-cols-2 gap-3 text-xs">
                <div class="space-y-1.5 min-w-0">
                    <p class="font-bold uppercase text-slate-500">Pedidos por Estado</p>
                    @foreach($logisticsByStatus as $ls)
                        <div class="flex justify-between p-2 rounded-lg bg-slate-50">
                            <span>{{ $ls['status'] }}</span>
                            <strong>{{ $ls['count'] }} (S/ {{ number_format($ls['amount'], 2) }})</strong>
                        </div>
                    @endforeach
                </div>
                <div class="space-y-1.5 min-w-0">
                    <p class="font-bold uppercase text-slate-500">Cambios & Devoluciones</p>
                    <div class="p-3 rounded-xl bg-slate-50 space-y-1">
                        <p class="flex justify-between"><span>Total solicitudes:</span><strong>{{ $returnsSummary['total'] }}</strong></p>
                        <p class="flex justify-between text-amber-700"><span>Pendientes:</span><strong>{{ $returnsSummary['pending'] }}</strong></p>
                        <p class="flex justify-between text-emerald-700"><span>Aprobadas:</span><strong>{{ $returnsSummary['approved'] }}</strong></p>
                        <p class="flex justify-between text-rose-700"><span>Rechazadas:</span><strong>{{ $returnsSummary['rejected'] }}</strong></p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-left">
                        <tr><th class="p-2">Zona / Courier</th><th class="p-2">Operador</th><th class="p-2 text-right">Pedidos</th><th class="p-2 text-right">Fletes</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($shippingZonesReport as $sz)
                            <tr>
                                <td class="p-2 font-bold">{{ $sz['name'] }}</td>
                                <td class="p-2">{{ $sz['courier'] }}</td>
                                <td class="p-2 text-right">{{ $sz['orders_count'] }}</td>
                                <td class="p-2 text-right font-bold">S/ {{ number_format($sz['shipping_revenue'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- REPORTE 9: COMUNIDADES, COLECTAS SOLIDARIAS Y REFERIDOS --}}
        <section id="rep-9" class="min-w-0 bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#1D70B8]">Reporte Gerencial 09</span>
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Comunidades, Colectas Solidarias y Programa de Referidos</h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs text-center">
                <div class="rounded-xl bg-slate-50 p-3"><p class="text-slate-500">Usuarios</p><p class="text-xl font-black">{{ $engagementSummary['total_users'] }}</p></div>
                <div class="rounded-xl bg-sky-50 p-3"><p class="text-[#1D70B8]">Referidos</p><p class="text-xl font-black text-[#0F2D4A]">{{ $engagementSummary['referrals_count'] }}</p></div>
                <div class="rounded-xl bg-slate-50 p-3"><p class="text-slate-500">Comunidades</p><p class="text-xl font-black">{{ $engagementSummary['communities_count'] }}</p></div>
                <div class="rounded-xl bg-slate-50 p-3"><p class="text-slate-500">Posts / Encuestas</p><p class="text-xl font-black">{{ $engagementSummary['posts_count'] }} / {{ $engagementSummary['polls_count'] }}</p></div>
            </div>
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-xs flex flex-wrap justify-between items-center gap-2">
                <div>
                    <p class="font-extrabold text-emerald-900">Colectas Solidarias Activas ({{ $engagementSummary['collections_count'] }})</p>
                    <p class="text-emerald-700">Recaudación acumulada para becas, placas y mejoras institucionales</p>
                </div>
                <p class="text-lg font-black text-emerald-900">S/ {{ number_format($engagementSummary['collections_raised'], 2) }} <span class="text-xs font-normal">/ Meta S/ {{ number_format($engagementSummary['collections_goal'], 2) }}</span></p>
            </div>
        </section>

        {{-- REPORTE 10: CUMPLIMIENTO LEGAL, CALIDAD Y AUDITORÍA --}}
        <section id="rep-10" class="min-w-0 bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Reporte Gerencial 10</span>
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Cumplimiento Legal (INDECOPI), Calidad y Auditoría</h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs text-center">
                <div class="rounded-xl bg-slate-50 p-3"><p class="text-slate-500">Reclamos Total</p><p class="text-xl font-black">{{ $complianceSummary['complaints_total'] }}</p></div>
                <div class="rounded-xl bg-amber-50 p-3"><p class="text-amber-800">Pendientes</p><p class="text-xl font-black text-amber-800">{{ $complianceSummary['complaints_pending'] }}</p></div>
                <div class="rounded-xl bg-emerald-50 p-3"><p class="text-emerald-700">Resueltos</p><p class="text-xl font-black text-emerald-700">{{ $complianceSummary['complaints_resolved'] }}</p></div>
                <div class="rounded-xl bg-sky-50 p-3"><p class="text-[#1D70B8]">Calidad Reseñas</p><p class="text-xl font-black text-[#0F2D4A]">★ {{ $complianceSummary['avg_rating'] }}</p></div>
            </div>
            <div class="divide-y divide-slate-100 text-xs max-h-44 overflow-y-auto">
                @foreach($complianceSummary['recent_logs'] as $log)
                    <div class="py-1.5 flex justify-between gap-2">
                        <span class="truncate"><strong>{{ $log->action }}</strong> — {{ $log->description }}</span>
                        <span class="text-slate-400 shrink-0">{{ $log->user?->name ?? 'Sistema' }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection
