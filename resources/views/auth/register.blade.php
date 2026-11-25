@extends('layouts.app')

@section('title', 'Registro Exprés en 15 Segundos — MiPromo')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid lg:grid-cols-12 gap-8 items-start">
    <div class="lg:col-span-5 bg-gradient-to-br from-[#0F2D4A] to-[#1D70B8] text-white rounded-3xl p-7 space-y-5 shadow-lg">
        <span class="inline-flex px-3 py-1 rounded-full bg-amber-400 text-[#0F2D4A] text-xs font-extrabold uppercase">Registro en 15 Segundos</span>
        <h1 class="text-2xl sm:text-3xl font-extrabold leading-tight">Desbloquea precios de Ex-Alumno y compra al instante</h1>
        <p class="text-sm text-white/85 leading-relaxed">Sin formularios eternos. Regístrate con solo 3 datos y accede hoy mismo a beneficios exclusivos en todo el Perú.</p>

        <ul class="space-y-3 text-xs">
            <li class="flex items-start gap-2.5">
                <span class="px-2 py-0.5 rounded-full bg-emerald-400/20 text-emerald-200 font-bold">✓</span>
                <span><strong>Tarifa Especial Ex-Alumno:</strong> ahorra hasta 20% frente al precio público en casacas, polos y souvenirs.</span>
            </li>
            <li class="flex items-start gap-2.5">
                <span class="px-2 py-0.5 rounded-full bg-emerald-400/20 text-emerald-200 font-bold">✓</span>
                <span><strong>Cupón de bienvenida:</strong> aplica <code class="bg-white/15 px-1.5 py-0.5 rounded font-bold">PROMO10</code> en tu compra desde S/ 50.</span>
            </li>
            <li class="flex items-start gap-2.5">
                <span class="px-2 py-0.5 rounded-full bg-emerald-400/20 text-emerald-200 font-bold">✓</span>
                <span><strong>Pago fácil + SUNAT:</strong> Yape, Plin, Tarjeta o Transferencia con Boleta/Factura y seguimiento Olva/Shalom.</span>
            </li>
        </ul>

        <div class="rounded-2xl bg-white/10 border border-white/20 p-4 text-xs">
            ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="font-extrabold text-amber-300 underline">Inicia sesión aquí →</a>
        </div>
    </div>

    <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200 p-7 sm:p-8 shadow-sm space-y-5"
         x-data="{
            alumniMode: {{ old('institution_id') || request()->filled('colegio') ? 'true' : 'false' }},
            dni: @js(old('dni', '')),
            fullName: @js(old('name', '')),
            loadingDni: false,
            dniMsg: '',
            dniOk: false
         }">
        <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-xl font-extrabold text-[#0F2D4A]">Crear Cuenta Rápida</h2>
                <p class="text-xs text-slate-500 mt-0.5">Ingresa tu DNI para autocompletar con apiperu.dev o escribe tus 3 datos básicos.</p>
            </div>
            <img src="{{ asset('images/logo_jmsperu.png') }}" alt="Grupo JMSPERU" class="h-9 w-auto">
        </div>

        <form action="{{ route('register.store') }}" method="POST" class="space-y-4">
            @csrf
            @if(!empty($redirectTo))
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
            @endif

            <div class="grid sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2 rounded-2xl bg-slate-50 border border-slate-200 p-3.5 space-y-2">
                    <label class="block text-xs font-bold text-slate-700">DNI (consulta automática RENIEC / apiperu.dev)</label>
                    <div class="flex gap-2">
                        <input type="text" name="dni" x-model="dni" maxlength="8" inputmode="numeric" placeholder="8 dígitos (ej. 44556677)" class="flex-1 px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-sm font-mono">
                        <button type="button"
                                :disabled="loadingDni"
                                @click="loadingDni = true; dniMsg = ''; const r = await window.miPromoLookupDni(dni, { onSuccess: d => { fullName = d.full_name; } }); loadingDni = false; dniOk = !!r.ok; dniMsg = r.message || '';"
                                class="px-4 py-2 rounded-xl bg-[#0F2D4A] hover:bg-[#1D70B8] text-white text-xs font-bold transition shrink-0">
                            <span x-text="loadingDni ? 'Consultando...' : 'Buscar DNI →'"></span>
                        </button>
                    </div>
                    <p x-show="dniMsg" x-text="dniMsg" :class="dniOk ? 'text-emerald-700' : 'text-amber-700'" class="text-[11px] font-semibold"></p>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">1. Nombre completo *</label>
                    <input type="text" name="name" x-model="fullName" required placeholder="Ej. Andrea Valdivia" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">2. Correo electrónico *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="tu@correo.pe" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">3. Contraseña (mín. 8 caracteres, letras y números) *</label>
                    <input type="password" name="password" minlength="8" required placeholder="Ej. MiPromo2026" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                </div>
            </div>

            <div class="rounded-2xl bg-sky-50/70 border border-sky-200 p-4 space-y-3">
                <label class="flex items-center justify-between gap-3 cursor-pointer">
                    <span class="text-xs font-extrabold text-[#0F2D4A]">🎓 ¿Eres ex-alumno? Vincula tu colegio y activa descuento (opcional)</span>
                    <input type="checkbox" x-model="alumniMode" class="rounded border-slate-300 text-[#1D70B8]">
                </label>

                <div x-show="alumniMode" x-cloak class="grid sm:grid-cols-2 gap-3 pt-2 border-t border-sky-200">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Colegio de egreso</label>
                        <select name="institution_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm">
                            <option value="">Selecciona tu colegio (opcional)</option>
                            @foreach($institutions as $inst)
                                <option value="{{ $inst->id }}" @selected((int) old('institution_id') === $inst->id || request('colegio') === $inst->slug)>{{ $inst->name }} ({{ $inst->city }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Año de Promoción</label>
                        <input type="number" name="graduation_year" value="{{ old('graduation_year') }}" placeholder="Ej. 2006" class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">WhatsApp (para entregas)</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="999888777" class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Código de referido (si te invitaron)</label>
                        <input type="text" name="referral_code_used" value="{{ old('referral_code_used', $ref ?? '') }}" placeholder="Opcional" class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm uppercase">
                    </div>
                </div>
            </div>

            <label class="flex items-start gap-2.5 text-xs text-slate-600 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                <input type="checkbox" name="privacy_consent" value="1" required checked class="mt-0.5 rounded border-slate-300 text-[#1D70B8]">
                <span>
                    Autorizo el tratamiento de mis datos conforme a la <strong>Ley N.° 29733</strong>, aceptando la
                    <a href="{{ route('legal.privacy') }}" class="text-[#1D70B8] underline font-semibold">Política de Privacidad</a> y
                    <a href="{{ route('legal.terms') }}" class="text-[#1D70B8] underline font-semibold">Términos</a>.
                </span>
            </label>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-[#0F2D4A] font-extrabold text-sm shadow-sm transition">
                Crear Cuenta Gratis y Comprar →
            </button>
        </form>
    </div>
</div>
@endsection
