@extends('layouts.admin')

@section('heading', 'Padrón de Ex-Alumnos e Importación Masiva CSV')
@section('subheading', 'Valida solicitudes de egresados y carga listados históricos desde Excel/CSV')

@section('content')
<div class="space-y-6">
    <div class="grid lg:grid-cols-12 gap-6 items-start">
        {{-- Registro Individual de Ex-Alumno con Búsqueda DNI apiperu.dev --}}
        <form action="{{ route('admin.alumni.store') }}" method="POST"
              x-data="{ dni: '', fullName: '', loadingDni: false, dniMsg: '', dniOk: false }"
              class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            @csrf
            <div class="flex items-center justify-between gap-2">
                <h2 class="font-extrabold text-[#0F2D4A]">Registrar Ex-Alumno por DNI (apiperu.dev)</h2>
                <span class="px-2.5 py-0.5 rounded-full bg-sky-50 text-[#1D70B8] text-[10px] font-bold">RENIEC Directo</span>
            </div>

            <div class="grid sm:grid-cols-3 gap-3 text-sm">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Institución Educativa *</label>
                    <select name="institution_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        @foreach($institutions as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Año Promoción *</label>
                    <input type="number" name="graduation_year" required value="2006" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>

                <div class="sm:col-span-1">
                    <label class="block text-xs font-bold text-slate-700 mb-1">DNI *</label>
                    <div class="flex gap-1.5">
                        <input type="text" name="dni" x-model="dni" maxlength="8" inputmode="numeric" required placeholder="8 dígitos" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono">
                        <button type="button"
                                :disabled="loadingDni"
                                @click="loadingDni = true; dniMsg = ''; const r = await window.miPromoLookupDni(dni, { onSuccess: d => { fullName = d.full_name; } }); loadingDni = false; dniOk = !!r.ok; dniMsg = r.message || '';"
                                class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-[#1D70B8] text-white text-xs font-bold shrink-0">
                            <span x-text="loadingDni ? '...' : 'DNI'"></span>
                        </button>
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nombres y Apellidos *</label>
                    <input type="text" name="full_name" x-model="fullName" required placeholder="Se autocompleta con API Perú" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <p x-show="dniMsg" x-text="dniMsg" :class="dniOk ? 'text-emerald-700' : 'text-amber-700'" class="sm:col-span-3 text-[11px] font-semibold"></p>

                <input type="text" name="current_city" required value="Lima" placeholder="Ciudad actual" class="px-3 py-2 rounded-xl border border-slate-300">
                <input type="email" name="email" placeholder="Correo (opcional)" class="px-3 py-2 rounded-xl border border-slate-300">
                <input type="text" name="profession" placeholder="Profesión (opcional)" class="px-3 py-2 rounded-xl border border-slate-300">
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <label class="flex items-center gap-2 text-xs text-slate-600">
                    <input type="checkbox" name="auto_verify" value="1" checked class="rounded border-slate-300 text-[#1D70B8]">
                    <span>Validar en padrón inmediatamente</span>
                </label>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#0F2D4A] hover:bg-[#1D70B8] text-white text-xs font-bold transition">
                    + Guardar en Padrón
                </button>
            </div>
        </form>

        <form action="{{ route('admin.alumni.import') }}" method="POST" enctype="multipart/form-data" class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            @csrf
            <h2 class="font-extrabold text-[#0F2D4A]">Importación Masiva desde CSV</h2>
            <p class="text-xs text-slate-500">Columnas esperadas: <code>nombre_completo,dni,anio_egreso,ciudad,email,profesion</code> (detecta duplicados automáticamente).</p>
            <div class="space-y-3 text-sm">
                <select name="institution_id" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300">
                    @foreach($institutions as $inst)
                        <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                    @endforeach
                </select>
                <input type="file" name="csv_file" required accept=".csv,.txt" class="text-xs block w-full">
                <button type="submit" class="w-full px-5 py-2.5 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white text-xs font-bold transition">Importar Padrón CSV</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4 overflow-x-auto">
        <form method="GET" action="{{ route('admin.alumni.index') }}" class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-extrabold text-[#0F2D4A]">Listado del Padrón ({{ $alumni->total() }})</h2>
            <div class="flex flex-wrap gap-2 text-xs">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por DNI, nombre o correo..." class="px-3.5 py-2 rounded-xl border border-slate-300">
                <select name="institution_id" class="px-3 py-2 rounded-xl border border-slate-300">
                    <option value="">Todos los colegios</option>
                    @foreach($institutions as $inst)
                        <option value="{{ $inst->id }}" @selected((int) request('institution_id') === $inst->id)>{{ $inst->short_name }}</option>
                    @endforeach
                </select>
                <select name="status" class="px-3 py-2 rounded-xl border border-slate-300">
                    <option value="">Todos los estados</option>
                    <option value="verified" @selected(request('status') === 'verified')>Verificados</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pendientes</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rechazados</option>
                </select>
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold">Buscar</button>
                <a href="{{ route('admin.alumni.index') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-600">Limpiar</a>
            </div>
        </form>

        <table class="w-full text-xs">
            <thead class="bg-slate-50 text-left text-slate-500 uppercase">
                <tr>
                    <th class="p-3">Egresado</th>
                    <th class="p-3">DNI</th>
                    <th class="p-3">Institución / Promo</th>
                    <th class="p-3">Ciudad</th>
                    <th class="p-3">Estado</th>
                    <th class="p-3 text-right">Validación</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($alumni as $person)
                    <tr>
                        <td class="p-3 font-bold text-slate-900">{{ $person->full_name }}</td>
                        <td class="p-3">{{ $person->dni }}</td>
                        <td class="p-3">{{ $person->institution->short_name }} · {{ $person->graduation_year }}</td>
                        <td class="p-3">{{ $person->current_city }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded-full font-bold {{ $person->verification_status === 'verified' ? 'bg-emerald-100 text-emerald-800' : ($person->verification_status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ $person->verification_status }}
                            </span>
                        </td>
                        <td class="p-3 text-right">
                            <div class="inline-flex gap-1.5">
                                <form action="{{ route('admin.alumni.verify', $person->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="verified">
                                    <button type="submit" class="px-2.5 py-1 rounded bg-emerald-600 text-white font-bold">Validar</button>
                                </form>
                                <form action="{{ route('admin.alumni.verify', $person->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="px-2.5 py-1 rounded bg-rose-600 text-white font-bold">Rechazar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $alumni->links() }}</div>
    </div>
</div>
@endsection
