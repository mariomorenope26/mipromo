@extends('layouts.admin')

@section('heading', 'Instituciones, Delegados y Alta en Web Principal')
@section('subheading', 'El Superadministrador da de alta a los colegios en la web principal tras acreditar a sus delegados')

@section('content')
<div class="space-y-8">
    {{-- Resumen de Alta --}}
    <div class="grid sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500">Publicados en Web Principal</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $institutions->where('is_published', true)->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500">Con Delegado (Listos para Alta)</p>
            <p class="text-2xl font-black text-[#1D70B8] mt-1">{{ $institutions->where('is_published', false)->where('active_delegates_count', '>', 0)->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500">Pendientes de Delegado</p>
            <p class="text-2xl font-black text-amber-600 mt-1">{{ $institutions->where('active_delegates_count', 0)->count() }}</p>
        </div>
    </div>

    {{-- Formularios de Creación --}}
    <div class="grid lg:grid-cols-2 gap-6">
        @if(auth()->user()->isRole('super_admin'))
            <form action="{{ route('admin.institutions.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
                @csrf
                <h2 class="font-extrabold text-[#0F2D4A]">Registrar Nuevo Colegio (&gt;10 años de vida institucional)</h2>
                <div class="grid sm:grid-cols-2 gap-3 text-sm">
                    <input type="text" name="name" required placeholder="Nombre completo del colegio" class="sm:col-span-2 px-3 py-2 rounded-xl border border-slate-300">
                    <input type="text" name="short_name" required placeholder="Nombre corto" class="px-3 py-2 rounded-xl border border-slate-300">
                    <select name="type" class="px-3 py-2 rounded-xl border border-slate-300">
                        <option value="colegio">Colegio</option>
                        <option value="instituto">Instituto</option>
                        <option value="universidad">Universidad</option>
                    </select>
                    <input type="text" name="city" required placeholder="Ciudad (ej. Piura, Chiclayo, Trujillo, Lima)" class="px-3 py-2 rounded-xl border border-slate-300">
                    <input type="text" name="region" required placeholder="Región" class="px-3 py-2 rounded-xl border border-slate-300">
                    <input type="number" name="founded_year" required max="{{ now()->year - 11 }}" placeholder="Año de fundación (mín. >10 años)" class="px-3 py-2 rounded-xl border border-slate-300">
                    <input type="number" step="0.01" name="commission_rate" value="10" required placeholder="% Aporte" class="px-3 py-2 rounded-xl border border-slate-300">
                    <input type="url" name="website_url" placeholder="Web oficial (https://...)" class="px-3 py-2 rounded-xl border border-slate-300">
                    <input type="text" name="modular_code" placeholder="Cód. modular" class="px-3 py-2 rounded-xl border border-slate-300">
                    <input type="text" name="motto" placeholder="Lema institucional" class="sm:col-span-2 px-3 py-2 rounded-xl border border-slate-300">
                    <div class="flex items-center gap-2 text-xs"><span>Color primario:</span><input type="color" name="primary_color" value="#1D70B8"></div>
                    <div class="flex items-center gap-2 text-xs"><span>Color secundario:</span><input type="color" name="secondary_color" value="#F59E0B"></div>
                    <div class="sm:col-span-2 text-xs">
                        <label class="block font-bold text-slate-600 mb-1">Insignia PNG sin fondo</label>
                        <input type="file" name="logo" accept=".png,image/png">
                    </div>
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1D70B8] text-white text-xs font-bold">Registrar Colegio (Pendiente de Alta)</button>
            </form>
        @endif

        <div class="space-y-6">
            <form action="{{ route('admin.promotions.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
                @csrf
                <h2 class="font-extrabold text-[#0F2D4A]">Crear Promoción</h2>
                <div class="grid sm:grid-cols-2 gap-3 text-sm">
                    <select name="institution_id" required class="px-3 py-2 rounded-xl border border-slate-300">
                        @foreach($institutions as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->short_name }} ({{ $inst->city }})</option>
                        @endforeach
                    </select>
                    <input type="number" name="year" required value="2026" class="px-3 py-2 rounded-xl border border-slate-300">
                    <input type="text" name="name" required placeholder="Nombre (ej. Bodas de Plata)" class="sm:col-span-2 px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">Agregar Promoción</button>
            </form>

            <form action="{{ route('admin.classrooms.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
                @csrf
                <h2 class="font-extrabold text-[#0F2D4A]">Crear Aula / Sección</h2>
                <div class="grid sm:grid-cols-3 gap-3 text-sm">
                    <select name="promotion_id" required class="px-3 py-2 rounded-xl border border-slate-300">
                        @foreach($institutions as $inst)
                            @foreach($inst->promotions as $promo)
                                <option value="{{ $promo->id }}">{{ $inst->short_name }} {{ $promo->year }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    <input type="text" name="grade" required value="5to Secundaria" class="px-3 py-2 rounded-xl border border-slate-300">
                    <input type="text" name="section" required placeholder="Sección (A, B...)" class="px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">Agregar Aula</button>
            </form>
        </div>
    </div>

    {{-- Gestión de Colegios, Delegados y Publicación en Web Principal --}}
    <div class="space-y-5">
        @foreach($institutions as $inst)
            <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="h-2" style="background: linear-gradient(90deg, {{ $inst->primary_color }}, {{ $inst->secondary_color }})"></div>
                <div class="p-6 space-y-5">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-2xl p-2 flex items-center justify-center border border-slate-200 bg-slate-50 shrink-0">
                                @if($inst->logo_path)
                                    <img src="{{ asset(ltrim($inst->logo_path, '/')) }}" alt="{{ $inst->short_name }}" class="w-full h-full object-contain">
                                @else
                                    <span class="font-black text-lg" style="color: {{ $inst->primary_color }}">{{ strtoupper(substr($inst->short_name, 0, 2)) }}</span>
                                @endif
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-extrabold text-[#0F2D4A]">{{ $inst->name }}</h3>
                                    @if($inst->is_published)
                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">Publicado en Web Principal</span>
                                    @elseif($inst->active_delegates_count > 0)
                                        <span class="px-2.5 py-0.5 rounded-full bg-sky-100 text-[#1D70B8] text-[11px] font-bold">Delegado listo — Pendiente de Alta</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold">Requiere dar de alta a un delegado</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    {{ $inst->city }}, {{ $inst->region }} · Fundado en <strong>{{ $inst->founded_year }}</strong> ({{ $inst->yearsOfHistory() }} años) · {{ $inst->alumni_profiles_count }} egresados
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2.5">
                            <a href="{{ route('institutions.show', $inst->slug) }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50">Vista Previa / Perfil</a>

                            @if(auth()->user()->isRole('super_admin'))
                                <form action="{{ route('admin.institutions.publication', $inst->slug) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    @if($inst->is_published)
                                        <input type="hidden" name="publish" value="0">
                                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold">
                                            Retirar de Web Principal
                                        </button>
                                    @else
                                        <input type="hidden" name="publish" value="1">
                                        <button type="submit"
                                                @disabled($inst->active_delegates_count === 0)
                                                class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $inst->active_delegates_count > 0 ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm' : 'bg-slate-200 text-slate-400 cursor-not-allowed' }}">
                                            Dar de Alta en Web Principal
                                        </button>
                                    @endif
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="grid lg:grid-cols-12 gap-5">
                        {{-- Delegados del Colegio --}}
                        <div class="lg:col-span-7 bg-slate-50 rounded-2xl p-4 border border-slate-100 space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600">Delegados del Colegio ({{ $inst->active_delegates_count }} activos)</h4>
                            <div class="space-y-2">
                                @forelse($inst->delegates as $delegate)
                                    <div class="flex flex-wrap items-center justify-between gap-2 bg-white rounded-xl px-3.5 py-2.5 border border-slate-200 text-xs">
                                        <div>
                                            <span class="font-bold text-slate-900">{{ $delegate->name }}</span>
                                            <span class="text-slate-500">· {{ $delegate->email }} · {{ $delegate->roleLabel() }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded-full font-bold {{ $delegate->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $delegate->is_active ? 'Activo' : 'Pendiente' }}
                                            </span>
                                            @if(auth()->user()->isRole('super_admin'))
                                                <form action="{{ route('admin.delegates.toggle', $delegate->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-slate-900 text-white font-bold">
                                                        {{ $delegate->is_active ? 'Desactivar' : 'Dar de alta' }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-amber-700">No hay delegados asignados aún. Registra uno para habilitar el alta del colegio.</p>
                                @endforelse
                            </div>

                            {{-- Alta rápida de delegado --}}
                            <form action="{{ route('admin.institutions.delegates.store', $inst->slug) }}" method="POST"
                                  x-data="{ dni: '', fullName: '', loadingDni: false }"
                                  class="grid sm:grid-cols-5 gap-2 pt-2 border-t border-slate-200 text-xs">
                                @csrf
                                <div class="flex gap-1">
                                    <input type="text" name="dni" x-model="dni" maxlength="8" placeholder="DNI (opcional)" class="w-full px-2.5 py-2 rounded-lg border border-slate-300 bg-white font-mono">
                                    <button type="button" :disabled="loadingDni"
                                            @click="loadingDni = true; await window.miPromoLookupDni(dni, { onSuccess: d => { fullName = d.full_name; } }); loadingDni = false;"
                                            class="px-2 py-2 rounded-lg bg-slate-900 text-white font-bold shrink-0">DNI</button>
                                </div>
                                <input type="text" name="name" x-model="fullName" required placeholder="Nombre delegado" class="px-2.5 py-2 rounded-lg border border-slate-300 bg-white">
                                <input type="email" name="email" required placeholder="correo@colegio.pe" class="px-2.5 py-2 rounded-lg border border-slate-300 bg-white">
                                <select name="role" class="px-2.5 py-2 rounded-lg border border-slate-300 bg-white">
                                    <option value="promotion_delegate">Delegado Promoción</option>
                                    <option value="institution_admin">Admin Institución</option>
                                    <option value="classroom_delegate">Delegado Aula</option>
                                </select>
                                <button type="submit" class="px-3 py-2 rounded-lg bg-[#1D70B8] text-white font-bold">+ Dar de alta Delegado</button>
                            </form>
                        </div>

                        {{-- Apariencia e Insignia --}}
                        <form action="{{ route('admin.institutions.appearance', $inst->slug) }}" method="POST" enctype="multipart/form-data" class="lg:col-span-5 bg-slate-50 rounded-2xl p-4 border border-slate-100 space-y-3 text-xs">
                            @csrf
                            @method('PATCH')
                            <h4 class="font-bold uppercase tracking-wider text-slate-600">Colores e Insignia PNG</h4>
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-1.5"><span>Primario</span><input type="color" name="primary_color" value="{{ $inst->primary_color }}"></label>
                                <label class="flex items-center gap-1.5"><span>Secundario</span><input type="color" name="secondary_color" value="{{ $inst->secondary_color }}"></label>
                            </div>
                            <input type="file" name="logo" accept=".png,image/png" class="block w-full">
                            <button type="submit" class="px-4 py-2 rounded-lg bg-slate-900 text-white font-bold">Actualizar Identidad</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
