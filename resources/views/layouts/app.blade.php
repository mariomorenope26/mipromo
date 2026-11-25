<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Tienda peruana en línea de prendas, casacas, polos Pima y souvenirs personalizados por colegio y promoción. Paga con Yape, Plin o Tarjeta con comprobante SUNAT — Grupo JMSPERU.')">
    <meta property="og:title" content="@yield('title', 'MiPromo | Tienda Online de Egresados — Grupo JMSPERU')">
    <meta property="og:description" content="@yield('meta_description', 'Compra online productos personalizados de tu colegio, aprovecha tarifa ex-alumno y envíos nacionales con SUNAT.')">
    <title>@yield('title', 'MiPromo | Tienda Online de Egresados — Grupo JMSPERU')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-900"
      x-data="{ quickRegister: false, copiedCoupon: false }"
      @open-register-modal.window="quickRegister = true">

    {{-- Top Commercial Announcement Bar --}}
    <div class="bg-[#0F2D4A] text-white text-xs py-2 px-4 print:hidden">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2 py-0.5 rounded bg-amber-400 text-[#0F2D4A] font-black text-[10px] uppercase">OFERTA HOY</span>
                <span class="font-medium">10% DTO. con <strong class="text-amber-300">PROMO10</strong> + Tarifa Especial Ex-Alumno · Envíos Nacionales + SUNAT</span>
                <button type="button"
                        @click="navigator.clipboard?.writeText('PROMO10'); copiedCoupon = true; setTimeout(() => copiedCoupon = false, 2000)"
                        class="px-2 py-0.5 rounded bg-white/15 hover:bg-white/25 text-[11px] font-bold text-sky-200 transition">
                    <span x-text="copiedCoupon ? '✓ PROMO10 copiado' : 'Copiar PROMO10'"></span>
                </button>
            </div>
            <div class="flex items-center gap-4">
                @guest
                    <button type="button" @click="quickRegister = true" class="font-bold text-amber-300 hover:underline">
                        Regístrate en 15s →
                    </button>
                @endguest
                <a href="{{ route('orders.track') }}" class="hover:text-sky-300 underline-offset-2 hover:underline">Seguimiento de pedidos</a>
                <a href="https://wa.me/51959199368?text={{ urlencode('Hola Grupo JMSPERU / MiPromo, deseo atención en línea.') }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-sky-300 hover:underline">WhatsApp: 959199368</a>
            </div>
        </div>
    </div>

    {{-- Navbar --}}
    <header x-data="{ open: false }" class="bg-white/95 backdrop-blur border-b border-slate-200 sticky top-0 z-40 print:hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18 py-2.5 gap-3">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0">
                    <img src="{{ asset('images/logo_jmsperu.png') }}" alt="Grupo JMSPERU" class="h-9 sm:h-10 w-auto object-contain">
                    <div class="hidden 2xl:block border-l border-slate-200 pl-3">
                        <span class="block text-sm font-bold text-[#0F2D4A] leading-tight">Egresados Store</span>
                        <span class="block text-[11px] font-medium text-[#1D70B8]">Tienda MiPromo Perú</span>
                    </div>
                </a>

                <nav class="hidden lg:flex items-center gap-0.5 text-[13px] font-medium text-slate-700 whitespace-nowrap">
                    <a href="{{ route('home') }}" class="px-2 py-1.5 rounded-lg hover:bg-slate-100 {{ request()->routeIs('home', 'store.*', 'products.*') ? 'text-[#1D70B8] bg-sky-50 font-semibold' : '' }}">Tienda</a>
                    <a href="{{ route('info.landing') }}" class="px-2 py-1.5 rounded-lg hover:bg-slate-100 {{ request()->routeIs('info.landing') ? 'text-[#1D70B8] bg-sky-50 font-semibold' : '' }}">Cómo Comprar</a>
                    <a href="{{ route('institutions.index') }}" class="px-2 py-1.5 rounded-lg hover:bg-slate-100 {{ request()->routeIs('institutions.*') ? 'text-[#1D70B8] bg-sky-50 font-semibold' : '' }}">Colegios</a>
                    <a href="{{ route('campaigns.index') }}" class="px-2 py-1.5 rounded-lg hover:bg-slate-100 {{ request()->routeIs('campaigns.*') ? 'text-[#1D70B8] bg-sky-50 font-semibold' : '' }}">Campañas</a>
                    <a href="{{ route('communities.index') }}" class="px-2 py-1.5 rounded-lg hover:bg-slate-100 {{ request()->routeIs('communities.*') ? 'text-[#1D70B8] bg-sky-50 font-semibold' : '' }}">Comunidades</a>
                    <a href="{{ route('directory.index') }}" class="px-2 py-1.5 rounded-lg hover:bg-slate-100 {{ request()->routeIs('directory.*') ? 'text-[#1D70B8] bg-sky-50 font-semibold' : '' }}">Directorio</a>
                    <a href="{{ route('blog.index') }}" class="px-2 py-1.5 rounded-lg hover:bg-slate-100 {{ request()->routeIs('blog.*') ? 'text-[#1D70B8] bg-sky-50 font-semibold' : '' }}">Noticias</a>
                    <a href="{{ route('manual.index') }}" class="px-2 py-1.5 rounded-lg hover:bg-slate-100 {{ request()->routeIs('manual.*') ? 'text-[#1D70B8] bg-sky-50 font-semibold' : '' }}">Manual</a>
                </nav>

                <div class="flex items-center gap-2 shrink-0 whitespace-nowrap">
                    @php($cartCount = app(\App\Services\CartService::class)->count())
                    <a href="{{ route('cart.index') }}" class="relative inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-sm font-semibold text-slate-700 transition">
                        <svg class="w-4 h-4 text-[#1D70B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span class="hidden sm:inline">Carrito</span>
                        <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold rounded-full bg-[#1D70B8] text-white">{{ $cartCount }}</span>
                    </a>

                    @auth
                        @if(auth()->user()->canAccessAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline-flex items-center px-3 py-2 rounded-xl bg-amber-500/15 text-amber-800 hover:bg-amber-500/25 text-xs font-bold border border-amber-300 transition">
                                Panel Admin
                            </a>
                        @endif
                        <a href="{{ route('account.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-[#0F2D4A] text-white hover:bg-[#1D70B8] text-sm font-semibold transition">
                            <span class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            <span class="hidden md:inline max-w-[110px] truncate">{{ auth()->user()->name }}</span>
                            @php($unreadCount = auth()->user()->notifications()->whereNull('read_at')->count())
                            @if($unreadCount > 0)
                                <span class="px-1.5 py-0.5 rounded-full bg-amber-400 text-[#0F2D4A] text-[10px] font-black">{{ $unreadCount }}</span>
                            @endif
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 rounded-xl text-[13px] font-semibold text-slate-700 hover:bg-slate-100">Ingresar</a>
                        <button type="button" @click="quickRegister = true" class="hidden sm:inline-flex px-3.5 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-[#0F2D4A] text-[13px] font-extrabold shadow-sm transition">
                            Regístrate en 15s
                        </button>
                    @endauth

                    <button @click="open = !open" type="button" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100" aria-label="Menú">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>

            {{-- Mobile Nav --}}
            <div x-show="open" x-cloak class="lg:hidden border-t border-slate-100 py-3 space-y-1 text-sm font-medium">
                <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Tienda en Línea</a>
                <a href="{{ route('info.landing') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Cómo Comprar Fácil</a>
                <a href="{{ route('institutions.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Instituciones Educativas</a>
                <a href="{{ route('campaigns.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Campañas Grupales</a>
                <a href="{{ route('communities.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Comunidades</a>
                <a href="{{ route('directory.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Directorio de Egresados</a>
                <a href="{{ route('blog.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Noticias y Guías</a>
                <a href="{{ route('manual.index') }}" class="block px-3 py-2 rounded-lg hover:bg-slate-100">Manual de Uso (Web &amp; PDF)</a>
                @guest
                    <a href="{{ route('register') }}" class="block px-3 py-2 rounded-lg bg-amber-50 text-[#0F2D4A] font-bold">Regístrate Gratis en 15s →</a>
                @endguest
                @auth
                    @if(auth()->user()->canAccessAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg bg-amber-50 text-amber-800 font-semibold">Panel Administración</a>
                    @endif
                @endauth
            </div>
        </div>
    </header>

    {{-- Modal de Registro Exprés en 15s (disponible en toda la tienda) --}}
    @guest
        <div x-show="quickRegister" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div @click.away="quickRegister = false" class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 text-[10px] font-extrabold uppercase">Desbloquea Tarifa Ex-Alumno</span>
                        <h3 class="text-xl font-extrabold text-[#0F2D4A] mt-1">Regístrate en 15 segundos</h3>
                        <p class="text-xs text-slate-500">Compra con precios preferenciales y usa el cupón <strong>PROMO10</strong>.</p>
                    </div>
                    <button type="button" @click="quickRegister = false" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100">✕</button>
                </div>

                <form action="{{ route('register.store') }}" method="POST"
                      x-data="{ dni: '', name: '', loadingDni: false, dniMsg: '', dniOk: false }"
                      class="space-y-3 text-xs">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                    <input type="hidden" name="privacy_consent" value="1">

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">DNI (consulta rápida apiperu.dev)</label>
                        <div class="flex gap-2">
                            <input type="text" name="dni" x-model="dni" maxlength="8" inputmode="numeric" placeholder="8 dígitos (opcional)" class="flex-1 px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-mono">
                            <button type="button"
                                    :disabled="loadingDni"
                                    @click="loadingDni = true; dniMsg = ''; const r = await window.miPromoLookupDni(dni, { onSuccess: d => { name = d.full_name; } }); loadingDni = false; dniOk = !!r.ok; dniMsg = r.message || '';"
                                    class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-[#1D70B8] text-white font-bold shrink-0 transition">
                                <span x-text="loadingDni ? 'Consultando...' : 'Buscar DNI'"></span>
                            </button>
                        </div>
                        <p x-show="dniMsg" x-text="dniMsg" :class="dniOk ? 'text-emerald-700' : 'text-amber-700'" class="text-[11px] font-semibold mt-1"></p>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nombre completo *</label>
                        <input type="text" name="name" x-model="name" required placeholder="Tu nombre y apellido" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Correo electrónico *</label>
                        <input type="email" name="email" required placeholder="tu@correo.pe" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Contraseña (mín. 8 caracteres, letras y números) *</label>
                        <input type="password" name="password" minlength="8" required placeholder="Ej. MiPromo2026" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">WhatsApp (opcional para seguimiento)</label>
                        <input type="text" name="phone" placeholder="999888777" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <button type="submit" class="w-full py-3 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white font-extrabold text-sm transition">
                        Activar mi Cuenta Ahora →
                    </button>
                </form>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                    <a href="{{ route('register', ['redirect' => url()->full()]) }}" class="font-bold text-[#1D70B8] hover:underline">Vincular mi colegio y promoción →</a>
                    <a href="{{ route('login') }}" class="text-slate-500 hover:text-slate-800 font-semibold">Ya tengo cuenta</a>
                </div>
            </div>
        </div>
    @endguest

    {{-- Flash Notifications --}}
    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Botón Flotante Asistido WhatsApp --}}
    <a href="https://wa.me/51959199368?text={{ urlencode('Hola Grupo JMSPERU / MiPromo, deseo ayuda para comprar o cotizar prendas personalizadas de mi promoción.') }}"
       target="_blank"
       rel="noopener noreferrer"
       class="fixed bottom-5 right-5 z-40 inline-flex items-center gap-2 px-4 py-3 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-xl transition print:hidden">
        <span>💬 Comprar / Cotizar por WhatsApp</span>
    </a>

    @include('components.footer')
    @include('components.dni-helper')
</body>
</html>
