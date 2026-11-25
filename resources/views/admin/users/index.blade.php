@extends('layouts.admin')

@section('heading', 'Superadmin: Gestión de Usuarios y Roles')
@section('subheading', 'Administra cuentas, asigna los 8 roles del sistema, vincula instituciones y controla accesos')

@section('content')
<div class="space-y-8">
    {{-- Resumen por Rol --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-8 gap-3">
        @foreach($roles as $role)
            <a href="{{ route('admin.users.index', ['role' => $role->value]) }}" class="bg-white rounded-2xl border border-slate-200 p-3.5 hover:border-[#1D70B8] transition">
                <p class="text-[11px] font-semibold text-slate-500 truncate">{{ $role->label() }}</p>
                <p class="text-xl font-black text-[#0F2D4A] mt-1">{{ $roleStats[$role->value] ?? 0 }}</p>
            </a>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-12 gap-6">
        {{-- Crear Usuario --}}
        <form action="{{ route('admin.users.store') }}" method="POST"
              x-data="{ dni: '', fullName: '', loadingDni: false, dniMsg: '', dniOk: false }"
              class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            @csrf
            <div class="flex items-center justify-between gap-2">
                <h2 class="font-extrabold text-[#0F2D4A]">Crear Cliente / Ex-Alumno / Usuario por DNI</h2>
                <span class="px-2 py-0.5 rounded-full bg-sky-50 text-[#1D70B8] text-[10px] font-bold">API Perú DNI</span>
            </div>
            <div class="grid sm:grid-cols-2 gap-3 text-sm">
                <div class="sm:col-span-2 flex gap-2">
                    <input type="text" name="dni" x-model="dni" maxlength="8" inputmode="numeric" placeholder="DNI (8 dígitos) para consultar RENIEC" class="flex-1 px-3 py-2 rounded-xl border border-slate-300 font-mono">
                    <button type="button"
                            :disabled="loadingDni"
                            @click="loadingDni = true; dniMsg = ''; const r = await window.miPromoLookupDni(dni, { onSuccess: d => { fullName = d.full_name; } }); loadingDni = false; dniOk = !!r.ok; dniMsg = r.message || '';"
                            class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-[#1D70B8] text-white text-xs font-bold shrink-0">
                        <span x-text="loadingDni ? '...' : 'Consultar DNI'"></span>
                    </button>
                </div>
                <p x-show="dniMsg" x-text="dniMsg" :class="dniOk ? 'text-emerald-700' : 'text-amber-700'" class="sm:col-span-2 text-[11px] font-semibold"></p>
                <input type="text" name="name" x-model="fullName" required placeholder="Nombre completo" class="sm:col-span-2 px-3 py-2 rounded-xl border border-slate-300">
                <input type="email" name="email" required placeholder="correo@colegio.pe" class="px-3 py-2 rounded-xl border border-slate-300">
                <input type="password" name="password" minlength="8" required placeholder="Contraseña fuerte (mín. 8)" class="px-3 py-2 rounded-xl border border-slate-300">
                <select name="role" required class="px-3 py-2 rounded-xl border border-slate-300">
                    @foreach($roles as $role)
                        <option value="{{ $role->value }}">{{ $role->label() }}</option>
                    @endforeach
                </select>
                <select name="institution_id" class="px-3 py-2 rounded-xl border border-slate-300">
                    <option value="">Sin institución fija</option>
                    @foreach($institutions as $inst)
                        <option value="{{ $inst->id }}">{{ $inst->short_name }} ({{ $inst->city }})</option>
                    @endforeach
                </select>
                <input type="text" name="phone" placeholder="Teléfono / WhatsApp" class="px-3 py-2 rounded-xl border border-slate-300">
                <input type="text" name="city" value="Lima" placeholder="Ciudad" class="px-3 py-2 rounded-xl border border-slate-300">
            </div>
            <label class="flex items-center gap-2 text-xs text-slate-600">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-[#1D70B8]">
                <span>Cuenta activa inmediatamente (dada de alta)</span>
            </label>
            <button type="submit" class="w-full py-2.5 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white text-xs font-bold transition">
                Registrar Usuario
            </button>
        </form>

        {{-- Matriz de Permisos por Rol (Plan Sección 3) --}}
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
            <h2 class="font-extrabold text-[#0F2D4A]">Matriz de Actores y Permisos</h2>
            <div class="overflow-x-auto text-xs">
                <table class="w-full">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-left">
                        <tr>
                            <th class="p-2.5">Rol</th>
                            <th class="p-2.5">Alcance Principal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr><td class="p-2.5 font-bold">Super administrador</td><td class="p-2.5 text-slate-600">Control total: usuarios/roles, alta de colegios, comisiones, marketplace, comunicaciones y reportes globales.</td></tr>
                        <tr><td class="p-2.5 font-bold">Admin. institución</td><td class="p-2.5 text-slate-600">Valida egresados de su colegio, gestiona catálogo institucional, promociones y comunidades.</td></tr>
                        <tr><td class="p-2.5 font-bold">Delegado promoción</td><td class="p-2.5 text-slate-600">Administra comunidad de su promoción, encuestas, colectas y campañas grupales.</td></tr>
                        <tr><td class="p-2.5 font-bold">Delegado de aula</td><td class="p-2.5 text-slate-600">Gestiona listado, padrón y muro de su sección/aula.</td></tr>
                        <tr><td class="p-2.5 font-bold">Vendedor / Proveedor</td><td class="p-2.5 text-slate-600">Gestiona sus productos, variantes, stock, pedidos asignados y liquidaciones.</td></tr>
                        <tr><td class="p-2.5 font-bold">Ex-alumno (miembro)</td><td class="p-2.5 text-slate-600">Tarifa preferencial, directorio, comunidades, compras, devoluciones y programa de referidos.</td></tr>
                        <tr><td class="p-2.5 font-bold">Soporte / Moderador</td><td class="p-2.5 text-slate-600">Atiende reportes de comunidad, Libro de Reclamaciones Virtual y auditoría.</td></tr>
                        <tr><td class="p-2.5 font-bold">Visitante / Comprador</td><td class="p-2.5 text-slate-600">Compra productos públicos con comprobante SUNAT y seguimiento nacional.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Filtros y Listado Editable --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-2 items-center justify-between">
            <h2 class="font-extrabold text-[#0F2D4A]">Listado de Usuarios ({{ $users->total() }})</h2>
            <div class="flex flex-wrap gap-2 text-xs">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nombre, correo o DNI..." class="px-3 py-2 rounded-xl border border-slate-300">
                <select name="role" class="px-3 py-2 rounded-xl border border-slate-300">
                    <option value="">Todos los roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
                <select name="institution_id" class="px-3 py-2 rounded-xl border border-slate-300">
                    <option value="">Todas las instituciones</option>
                    @foreach($institutions as $inst)
                        <option value="{{ $inst->id }}" @selected((int) request('institution_id') === $inst->id)>{{ $inst->short_name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold">Filtrar</button>
                <a href="{{ route('admin.users.index') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-600">Limpiar</a>
            </div>
        </form>

        <div class="space-y-3">
            @foreach($users as $u)
                <form action="{{ route('admin.users.update', $u->id) }}" method="POST" class="rounded-xl border border-slate-200 bg-slate-50/70 p-4 grid lg:grid-cols-12 gap-3 items-center text-xs">
                    @csrf
                    @method('PATCH')
                    <div class="lg:col-span-3 space-y-1">
                        <input type="text" name="name" value="{{ $u->name }}" required class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white font-bold">
                        <p class="text-[11px] text-slate-500 truncate">{{ $u->email }} @if($u->dni) · DNI: <span class="font-mono font-semibold text-slate-700">{{ $u->dni }}</span> @endif · Ref: {{ $u->referrals_count }}</p>
                    </div>
                    <div class="lg:col-span-3">
                        <select name="role" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                            @foreach($roles as $role)
                                <option value="{{ $role->value }}" @selected($u->role === $role->value)>{{ $role->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lg:col-span-3">
                        <select name="institution_id" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                            <option value="">Sin institución</option>
                            @foreach($institutions as $inst)
                                <option value="{{ $inst->id }}" @selected($u->institution_id === $inst->id)>{{ $inst->short_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lg:col-span-3 flex flex-wrap items-center justify-end gap-2">
                        <label class="inline-flex items-center gap-1 font-semibold">
                            <input type="checkbox" name="is_active" value="1" @checked($u->is_active) class="rounded border-slate-300 text-[#1D70B8]">
                            <span>{{ $u->is_active ? 'Activo' : 'Suspendido' }}</span>
                        </label>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-[#1D70B8] hover:bg-[#0F2D4A] text-white font-bold">Guardar</button>
                    </div>
                </form>
            @endforeach
        </div>

        <div>{{ $users->links() }}</div>
    </div>
</div>
@endsection
