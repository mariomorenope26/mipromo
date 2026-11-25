@extends('layouts.app')

@section('title', 'Directorio Nacional de Ex-Alumnos — MiPromo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-[#1D70B8]">Módulo C · Padrón Verificado</span>
            <h1 class="text-3xl font-extrabold text-[#0F2D4A] mt-1">Directorio de Ex-Alumnos</h1>
            <p class="text-sm text-slate-600 mt-1">Encuentra compañeros por colegio, promoción, ciudad o profesión con privacidad controlada por cada miembro.</p>
        </div>

        <form method="GET" action="{{ route('directory.index') }}" class="flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Nombre, DNI, profesión o empresa..." class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-sm">
            <select name="colegio" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-sm">
                <option value="">Todos los colegios</option>
                @foreach($institutions as $inst)
                    <option value="{{ $inst->slug }}" @selected(request('colegio') === $inst->slug)>{{ $inst->short_name }}</option>
                @endforeach
            </select>
            <select name="promocion" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-sm">
                <option value="">Cualquier año</option>
                @foreach($years as $year)
                    <option value="{{ $year }}" @selected((int) request('promocion') === (int) $year)>Promo {{ $year }}</option>
                @endforeach
            </select>
            <input type="text" name="ciudad" value="{{ request('ciudad') }}" placeholder="Ciudad..." class="w-32 px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-sm">
            <button type="submit" class="px-4 py-2 rounded-xl bg-[#1D70B8] text-white text-sm font-semibold">Buscar</button>
        </form>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($alumni as $person)
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full flex items-center justify-center text-white font-bold text-sm" style="background-color: {{ $person->institution->primary_color }}">
                            {{ strtoupper(substr($person->full_name, 0, 2)) }}
                        </div>
                        <div>
                            <h2 class="font-bold text-slate-900">{{ $person->full_name }}</h2>
                            <p class="text-xs text-[#1D70B8] font-semibold">{{ $person->institution->short_name }} · Promo {{ $person->graduation_year }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Verificado</span>
                </div>

                <div class="text-xs text-slate-600 space-y-1 pt-2 border-t border-slate-100">
                    @if($person->profession)
                        <p><strong>Profesión:</strong> {{ $person->profession }}</p>
                    @endif
                    @if($person->company && $person->allowsField('company'))
                        <p><strong>Empresa:</strong> {{ $person->company }}</p>
                    @endif
                    @if($person->current_city && $person->allowsField('city'))
                        <p><strong>Ciudad actual:</strong> {{ $person->current_city }}</p>
                    @endif
                    @if($person->email && $person->allowsField('email'))
                        <p><strong>Correo:</strong> {{ $person->email }}</p>
                    @endif
                    @if($person->classroom)
                        <p><strong>Aula:</strong> {{ $person->classroom->label() }}</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-10 text-center text-slate-500">
                No se encontraron egresados con los filtros indicados.
            </div>
        @endforelse
    </div>

    <div>{{ $alumni->links() }}</div>

    {{-- Formulario de Registro / Validación en el Padrón --}}
    <section class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm"
             x-data="{
                dni: @js(old('dni', auth()->user()?->dni ?? '')),
                fullName: @js(old('full_name', auth()->user()?->name ?? '')),
                loadingDni: false,
                dniMsg: '',
                dniOk: false
             }">
        <h2 class="text-xl font-extrabold text-[#0F2D4A]">Solicitar Registro o Validación en el Padrón de Egresados</h2>
        <p class="text-xs text-slate-500 mt-1 mb-5">Consulta tu DNI con apiperu.dev para completar tus datos. El sistema detecta duplicados y envía tu ficha a validación por tu delegado.</p>

        <form action="{{ route('directory.store') }}" method="POST" class="grid sm:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Institución Educativa</label>
                <select name="institution_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    @foreach($institutions as $inst)
                        <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">DNI (Búsqueda apiperu.dev) *</label>
                <div class="flex gap-1.5">
                    <input type="text" name="dni" x-model="dni" maxlength="8" inputmode="numeric" required placeholder="8 dígitos" class="flex-1 px-3 py-2.5 rounded-xl border border-slate-300 text-sm font-mono">
                    <button type="button"
                            :disabled="loadingDni"
                            @click="loadingDni = true; dniMsg = ''; const r = await window.miPromoLookupDni(dni, { onSuccess: d => { fullName = d.full_name; } }); loadingDni = false; dniOk = !!r.ok; dniMsg = r.message || '';"
                            class="px-3 py-2 rounded-xl bg-[#0F2D4A] hover:bg-[#1D70B8] text-white text-xs font-bold shrink-0">
                        <span x-text="loadingDni ? '...' : 'Buscar DNI'"></span>
                    </button>
                </div>
                <p x-show="dniMsg" x-text="dniMsg" :class="dniOk ? 'text-emerald-700' : 'text-amber-700'" class="text-[11px] font-semibold mt-1"></p>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nombres y Apellidos *</label>
                <input type="text" name="full_name" x-model="fullName" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Año de Egreso</label>
                <input type="number" name="graduation_year" required value="{{ old('graduation_year', 2006) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Ciudad Actual</label>
                <input type="text" name="current_city" required value="{{ old('current_city', 'Lima') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Profesión</label>
                <input type="text" name="profession" placeholder="Ej. Ingeniero Civil" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
            </div>
            <div class="sm:col-span-3 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#0F2D4A] hover:bg-[#1D70B8] text-white text-sm font-bold transition">
                    Enviar Ficha a Validación
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
