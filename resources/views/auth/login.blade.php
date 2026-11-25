@extends('layouts.app')

@section('title', 'Iniciar Sesión — MiPromo Grupo JMSPERU')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid lg:grid-cols-12 gap-8 items-start">
    <div class="lg:col-span-6 bg-white rounded-3xl border border-slate-200 p-8 shadow-sm space-y-6">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/logo_jmsperu.png') }}" alt="Grupo JMSPERU" class="h-10 w-auto">
        </div>
        <div>
            <h1 class="text-2xl font-extrabold text-[#0F2D4A]">Ingresar a MiPromo</h1>
            <p class="text-sm text-slate-500 mt-1">Accede a tu comunidad, pedidos, campañas y panel de gestión.</p>
        </div>

        <form action="{{ route('login.attempt') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Correo electrónico</label>
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Contraseña</label>
                <input type="password" name="password" required autocomplete="current-password" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
            </div>
            <label class="flex items-center gap-2 text-xs text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-[#1D70B8]">
                <span>Mantener sesión iniciada</span>
            </label>
            <button type="submit" class="w-full py-3 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white font-bold text-sm transition">
                Iniciar Sesión
            </button>
        </form>

        <p class="text-xs text-slate-600 text-center">
            ¿Aún no tienes cuenta? <a href="{{ route('register') }}" class="font-bold text-[#1D70B8] hover:underline">Regístrate gratis en 15 segundos →</a>
        </p>
    </div>

    <div class="lg:col-span-6 bg-gradient-to-br from-[#0F2D4A] to-[#1D70B8] text-white rounded-3xl p-8 space-y-5 shadow-lg">
        <div>
            <span class="px-2.5 py-1 rounded-full bg-amber-400 text-[#0F2D4A] text-[11px] font-extrabold uppercase">Acceso Protegido Grupo JMSPERU</span>
            <h2 class="text-2xl font-extrabold mt-3 leading-tight">Tu cuenta de ex-alumno o comprador en un solo lugar</h2>
            <p class="text-sm text-white/80 mt-2">Ingresa con tus credenciales registradas para acceder a tarifas especiales, seguimiento SUNAT, campañas de tu promoción y comunidades verificadas.</p>
        </div>

        <ul class="space-y-2.5 text-xs">
            <li class="flex items-center gap-2 bg-white/10 rounded-xl px-4 py-2.5">
                <span class="text-emerald-300 font-bold">✓</span>
                <span>Tarifa Ex-Alumno y descuentos por volumen acumulados</span>
            </li>
            <li class="flex items-center gap-2 bg-white/10 rounded-xl px-4 py-2.5">
                <span class="text-emerald-300 font-bold">✓</span>
                <span>Descarga directa de Boletas, Facturas y Notas de Crédito SUNAT</span>
            </li>
            <li class="flex items-center gap-2 bg-white/10 rounded-xl px-4 py-2.5">
                <span class="text-emerald-300 font-bold">✓</span>
                <span>Soporte de delegados e instituciones educativas en todo el Perú</span>
            </li>
        </ul>

        <div class="pt-2 flex flex-wrap gap-3">
            <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-[#0F2D4A] font-extrabold text-xs transition">
                Crear Cuenta Gratis →
            </a>
            <a href="https://wa.me/51959199368?text={{ urlencode('Hola Grupo JMSPERU, necesito asistencia para acceder a mi cuenta MiPromo.') }}"
               target="_blank" rel="noopener noreferrer"
               class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition">
                💬 Ayuda por WhatsApp (959199368)
            </a>
        </div>
    </div>
</div>
@endsection
