@extends('layouts.admin')

@section('heading', 'Área Integral de Proveedores, Homologación y Liquidaciones')
@section('subheading', 'Gestión profesional de talleres, verificación para sello cliente, datos bancarios, costos y liquidaciones periódicas')

@section('content')
<div class="space-y-8">
    {{-- KPIs del Área de Proveedores --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-[11px] font-bold uppercase text-slate-500">Proveedores</p>
            <p class="text-2xl font-black text-[#0F2D4A] mt-1">{{ $summary['total'] }}</p>
            <p class="text-[11px] text-slate-500">{{ $summary['active'] }} activos</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-[11px] font-bold uppercase text-slate-500">Verificados ✓</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $summary['verified'] }}</p>
            <p class="text-[11px] text-slate-500">{{ $summary['unverified'] }} pendientes</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-[11px] font-bold uppercase text-slate-500">Ventas Brutas</p>
            <p class="text-xl font-black text-[#0F2D4A] mt-1">S/ {{ number_format($summary['gross_sales'], 2) }}</p>
            <p class="text-[11px] text-slate-500">Facturación asociada</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-[11px] font-bold uppercase text-slate-500">Costo Compra</p>
            <p class="text-xl font-black text-amber-700 mt-1">S/ {{ number_format($summary['purchase_cost'], 2) }}</p>
            <p class="text-[11px] text-slate-500">Valorizado órdenes</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-[11px] font-bold uppercase text-slate-500">Liquidado Pagado</p>
            <p class="text-xl font-black text-emerald-700 mt-1">S/ {{ number_format($summary['paid_settlements'], 2) }}</p>
            <p class="text-[11px] text-slate-500">Transferido a taller</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-[11px] font-bold uppercase text-slate-500">Por Liquidar</p>
            <p class="text-xl font-black text-[#1D70B8] mt-1">S/ {{ number_format($summary['pending_settlements'], 2) }}</p>
            <p class="text-[11px] text-slate-500">Pendiente transferencia</p>
        </div>
    </div>

    {{-- Registrar Nuevo Proveedor Completo --}}
    <form action="{{ route('admin.vendors.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
        @csrf
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
                <h2 class="font-extrabold text-[#0F2D4A]">Registrar Nuevo Proveedor / Taller Homologado</h2>
                <p class="text-xs text-slate-500">Los proveedores marcados como Verificados otorgan el sello “Proveedor Verificado” en la tienda cliente sin exponer sus datos comerciales internos.</p>
            </div>
            <a href="{{ route('admin.vendors.export') }}" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-slate-50 text-xs font-bold text-slate-700 hover:bg-slate-100">
                Exportar Directorio CSV
            </a>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Razón Social / Taller *</label>
                <input type="text" name="business_name" required placeholder="Ej. Bordados Industriales Perú SAC" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nombre Comercial</label>
                <input type="text" name="trade_name" placeholder="Ej. Bordados Perú" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">RUC (11 dígitos) *</label>
                <input type="text" name="ruc" required placeholder="2060..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Especialidad</label>
                <input type="text" name="category_specialty" placeholder="Textil Varsity, Grabado Láser..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Persona de Contacto</label>
                <input type="text" name="contact_name" placeholder="Nombre del representante" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Teléfono / WhatsApp *</label>
                <input type="text" name="phone" required placeholder="999888777" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Correo de Órdenes</label>
                <input type="email" name="email" placeholder="ordenes@taller.pe" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Correo acceso Panel (opcional)</label>
                <input type="email" name="owner_email" placeholder="acceso@taller.pe" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Clave Panel (opcional, mín. 8)</label>
                <input type="password" name="owner_password" minlength="8" placeholder="O aleatoria segura de 16 chars" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Ciudad *</label>
                <input type="text" name="city" required placeholder="Lima, Piura, Trujillo..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block font-bold text-slate-700 mb-1">Dirección del Taller</label>
                <input type="text" name="address" placeholder="Av. / Jr. / Calle..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">% Comisión *</label>
                    <input type="number" step="0.01" name="commission_rate" value="12" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Días entrega</label>
                    <input type="number" name="lead_time_days" value="7" min="1" max="90" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Banco</label>
                <input type="text" name="bank_name" placeholder="BCP, BBVA, Interbank..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">N.° Cuenta</label>
                <input type="text" name="bank_account" placeholder="Cuenta corriente/ahorros" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">CCI</label>
                <input type="text" name="bank_cci" placeholder="20 dígitos" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Condición de Pago</label>
                <input type="text" name="payment_terms" value="50% adelanto / 50% contraentrega" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            </div>

            <div class="sm:col-span-2">
                <label class="block font-bold text-slate-700 mb-1">Descripción / Capacidad productiva</label>
                <input type="text" name="description" placeholder="Ej. Talleres de confección hasta 1,500 prendas/mes" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block font-bold text-slate-700 mb-1">Notas internas (solo Superadmin)</label>
                <input type="text" name="internal_notes" placeholder="Acuerdos de precios, muestras aprobadas..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            <label class="flex items-center gap-2 text-xs font-bold text-emerald-800">
                <input type="checkbox" name="is_verified" value="1" checked class="rounded text-emerald-600">
                <span>Homologar inmediatamente como Proveedor Verificado ✓</span>
            </label>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white text-xs font-bold shadow-sm transition">
                + Registrar Proveedor
            </button>
        </div>
    </form>

    {{-- Filtros del Directorio de Proveedores --}}
    <form method="GET" action="{{ route('admin.vendors.index') }}" class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex flex-wrap gap-2 flex-1">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por razón social, RUC, ciudad o especialidad..." class="flex-1 min-w-52 px-3 py-2 rounded-xl border border-slate-300">
            <select name="verificado" class="px-3 py-2 rounded-xl border border-slate-300 bg-white">
                <option value="">Todos (Verificación)</option>
                <option value="1" @selected(request('verificado') === '1')>Solo Verificados ✓</option>
                <option value="0" @selected(request('verificado') === '0')>Por verificar</option>
            </select>
            <select name="estado" class="px-3 py-2 rounded-xl border border-slate-300 bg-white">
                <option value="">Todos (Estado)</option>
                <option value="active" @selected(request('estado') === 'active')>Activos</option>
                <option value="suspended" @selected(request('estado') === 'suspended')>Suspendidos</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold">Filtrar</button>
            <a href="{{ route('admin.vendors.index') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-600">Limpiar</a>
        </div>
    </form>

    {{-- Tarjetas Profesionales de Proveedores con Edición y Liquidación --}}
    <div class="grid xl:grid-cols-2 gap-6">
        @foreach($vendors as $vendor)
            @php
                $gross = (float) ($vendor->gross_sales ?? 0);
                $purchaseCost = (float) ($costByVendor[$vendor->id] ?? 0);
                $comm = round($gross * ((float) $vendor->commission_rate / 100), 2);
                $net = round(max(0, $gross - $comm), 2);
            @endphp
            <div x-data="{ editing: false }" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-base font-extrabold text-[#0F2D4A]">{{ $vendor->business_name }}</h3>
                            @if($vendor->is_verified)
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">✓ Proveedor Verificado</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold">Pendiente Verificación</span>
                            @endif
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[11px] font-bold">★ {{ number_format((float) $vendor->rating, 1) }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            RUC: <strong>{{ $vendor->ruc }}</strong> · {{ $vendor->city }} · {{ $vendor->category_specialty ?? 'Multi-rubro' }} · Plazo: {{ $vendor->lead_time_days }} días · Comisión: {{ number_format($vendor->commission_rate, 0) }}%
                        </p>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Contacto: {{ $vendor->contact_name ?? 'Por asignar' }} · Tel: {{ $vendor->phone }} · {{ $vendor->email ?? 'Sin correo' }}
                            @if($vendor->bank_name)
                                · Banco: {{ $vendor->bank_name }} ({{ $vendor->bank_account ?? $vendor->bank_cci }})
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                        <form action="{{ route('admin.vendors.verify', $vendor->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-3 py-1.5 rounded-lg font-bold {{ $vendor->is_verified ? 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                                {{ $vendor->is_verified ? 'Revocar Sello' : '✓ Verificar' }}
                            </button>
                        </form>
                        <form action="{{ route('admin.vendors.toggle', $vendor->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-3 py-1.5 rounded-lg font-bold {{ $vendor->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $vendor->is_active ? 'Activo' : 'Suspendido' }}
                            </button>
                        </form>
                        <button type="button" @click="editing = !editing" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white font-bold">
                            <span x-text="editing ? 'Cerrar' : 'Editar'"></span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs text-center">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-slate-500">Productos</p>
                        <p class="text-base font-black text-[#0F2D4A]">{{ $vendor->products_count }}</p>
                        <a href="{{ route('admin.catalog.index', ['vendor_id' => $vendor->id]) }}" class="text-[10px] font-bold text-[#1D70B8] hover:underline">Ver catálogo →</a>
                    </div>
                    <div class="rounded-xl bg-amber-50 p-3">
                        <p class="text-amber-800">Costo Órdenes</p>
                        <p class="text-base font-black text-amber-900">S/ {{ number_format($purchaseCost, 2) }}</p>
                        <p class="text-[10px] text-amber-700">{{ (int) ($vendor->total_units_sold ?? 0) }} uds</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-slate-500">Ventas Brutas</p>
                        <p class="text-base font-black text-[#0F2D4A]">S/ {{ number_format($gross, 2) }}</p>
                        <p class="text-[10px] text-slate-400">Com. S/ {{ number_format($comm, 2) }}</p>
                    </div>
                    <div class="rounded-xl bg-emerald-50 p-3">
                        <p class="text-emerald-700">Neto a Liquidar</p>
                        <p class="text-base font-black text-emerald-800">S/ {{ number_format($net, 2) }}</p>
                        <p class="text-[10px] text-emerald-700">{{ $vendor->payment_terms }}</p>
                    </div>
                </div>

                {{-- Panel colapsable de edición completa del proveedor --}}
                <form x-show="editing" x-cloak action="{{ route('admin.vendors.update', $vendor->id) }}" method="POST" class="rounded-xl bg-slate-50 border border-slate-200 p-4 space-y-3 text-xs">
                    @csrf
                    @method('PATCH')
                    <p class="font-extrabold text-[#0F2D4A]">Editar Ficha Completa del Proveedor</p>
                    <div class="grid sm:grid-cols-3 gap-2">
                        <input type="text" name="business_name" value="{{ $vendor->business_name }}" required placeholder="Razón social" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" name="trade_name" value="{{ $vendor->trade_name }}" placeholder="Nombre comercial" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" name="ruc" value="{{ $vendor->ruc }}" required placeholder="RUC" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" name="contact_name" value="{{ $vendor->contact_name }}" placeholder="Contacto" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" name="phone" value="{{ $vendor->phone }}" required placeholder="Teléfono" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="email" name="email" value="{{ $vendor->email }}" placeholder="Email" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" name="city" value="{{ $vendor->city }}" required placeholder="Ciudad" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" name="address" value="{{ $vendor->address }}" placeholder="Dirección" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" name="category_specialty" value="{{ $vendor->category_specialty }}" placeholder="Especialidad" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" name="bank_name" value="{{ $vendor->bank_name }}" placeholder="Banco" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" name="bank_account" value="{{ $vendor->bank_account }}" placeholder="Cuenta" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" name="bank_cci" value="{{ $vendor->bank_cci }}" placeholder="CCI" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="number" step="0.01" name="commission_rate" value="{{ $vendor->commission_rate }}" required placeholder="% Com." class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="number" name="lead_time_days" value="{{ $vendor->lead_time_days }}" min="1" max="90" placeholder="Días entrega" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="number" step="0.1" name="rating" value="{{ $vendor->rating }}" min="1" max="5" placeholder="Rating 1-5" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" name="payment_terms" value="{{ $vendor->payment_terms }}" placeholder="Condición pago" class="sm:col-span-3 px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <input type="text" name="internal_notes" value="{{ $vendor->internal_notes }}" placeholder="Notas internas" class="sm:col-span-3 px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                        <label class="flex items-center gap-1.5 font-bold text-emerald-800">
                            <input type="checkbox" name="is_verified" value="1" @checked($vendor->is_verified) class="rounded text-emerald-600">
                            <span>Proveedor Verificado ✓</span>
                        </label>
                        <button type="submit" class="px-4 py-1.5 rounded-lg bg-[#1D70B8] text-white font-bold">Guardar Ficha</button>
                    </div>
                </form>

                <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100">
                    <form action="{{ route('admin.vendors.settlements.store', $vendor->id) }}" method="POST" class="flex-1 flex flex-wrap gap-2 text-xs">
                        @csrf
                        <input type="text" name="period_label" required value="{{ now()->format('Y-m') }}" placeholder="Periodo" class="w-28 px-3 py-2 rounded-lg border border-slate-300">
                        <input type="text" name="notes" placeholder="Notas de corte..." class="flex-1 min-w-32 px-3 py-2 rounded-lg border border-slate-300">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-slate-900 text-white font-bold">Generar Liquidación</button>
                    </form>
                    <form action="{{ route('admin.vendors.destroy', $vendor->id) }}" method="POST" onsubmit="return confirm('¿Eliminar o suspender proveedor?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold">Eliminar</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Historial de Liquidaciones --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
        <h2 class="font-extrabold text-[#0F2D4A]">Historial de Liquidaciones a Proveedores</h2>
        <div class="space-y-2.5 text-xs">
            @forelse($settlements as $settlement)
                <div class="flex flex-wrap items-center justify-between gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                    <div>
                        <strong>{{ $settlement->vendor->business_name }}</strong> · Periodo {{ $settlement->period_label }}
                        <span class="text-slate-500">· Bruto S/ {{ number_format($settlement->gross_sales, 2) }} · Comisión S/ {{ number_format($settlement->platform_commission, 2) }} · <strong>Neto S/ {{ number_format($settlement->net_payout, 2) }}</strong></span>
                    </div>
                    @if($settlement->status === 'pending')
                        <form action="{{ route('admin.settlements.pay', $settlement->id) }}" method="POST" class="flex gap-2">
                            @csrf
                            @method('PATCH')
                            <input type="text" name="reference_code" required placeholder="N.° transferencia" class="px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold">Marcar Pagada</button>
                        </form>
                    @else
                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold">Pagada ({{ $settlement->reference_code }})</span>
                    @endif
                </div>
            @empty
                <p class="text-xs text-slate-500">Aún no se han generado cortes de liquidación.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
