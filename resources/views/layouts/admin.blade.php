<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Administrativo — Grupo JMSPERU')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-slate-100 text-slate-900">
    <div class="flex-1 flex flex-col lg:flex-row">
        {{-- Sidebar --}}
        <aside class="w-full lg:w-68 bg-[#0F2D4A] text-white shrink-0 flex flex-col">
            <div class="p-5 border-b border-white/10 flex items-center justify-between lg:block">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 bg-white rounded-xl p-2.5">
                    <img src="{{ asset('images/logo_jmsperu.png') }}" alt="Grupo JMSPERU" class="h-9 w-auto object-contain">
                </a>
                <div class="mt-3 hidden lg:block">
                    <p class="text-xs uppercase tracking-wider text-sky-300 font-semibold">Gestión Multi-Rol</p>
                    <p class="text-sm font-bold truncate">{{ auth()->user()->name }}</p>
                    <span class="inline-block mt-1 text-[11px] px-2 py-0.5 rounded-full bg-white/15 text-sky-200">{{ auth()->user()->roleLabel() }}</span>
                </div>
            </div>

            <nav class="p-4 space-y-1 text-sm flex-1 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-[#1D70B8] text-white font-semibold' : 'text-slate-300 hover:bg-white/10' }}">
                    <span>Dashboard y KPIs</span>
                </a>
                <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.orders.*') ? 'bg-[#1D70B8] text-white font-semibold' : 'text-slate-300 hover:bg-white/10' }}">
                    <span>Pedidos, Pagos y SUNAT</span>
                </a>
                <a href="{{ route('admin.campaigns.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.campaigns.*') ? 'bg-[#1D70B8] text-white font-semibold' : 'text-slate-300 hover:bg-white/10' }}">
                    <span>Campañas Grupales</span>
                </a>
                <a href="{{ route('admin.catalog.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.catalog.*') ? 'bg-[#1D70B8] text-white font-semibold' : 'text-slate-300 hover:bg-white/10' }}">
                    <span>Catálogo, Precios y Stock</span>
                </a>
                <a href="{{ route('admin.institutions.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.institutions.*') ? 'bg-[#1D70B8] text-white font-semibold' : 'text-slate-300 hover:bg-white/10' }}">
                    <span>Instituciones y Aulas</span>
                </a>
                <a href="{{ route('admin.alumni.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.alumni.*') ? 'bg-[#1D70B8] text-white font-semibold' : 'text-slate-300 hover:bg-white/10' }}">
                    <span>Padrón e Importación CSV</span>
                </a>
                <a href="{{ route('admin.moderation.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.moderation.*') ? 'bg-[#1D70B8] text-white font-semibold' : 'text-slate-300 hover:bg-white/10' }}">
                    <span>Moderación, Quejas y Auditoría</span>
                </a>
                <a href="{{ route('admin.communications.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.communications.*') ? 'bg-[#1D70B8] text-white font-semibold' : 'text-slate-300 hover:bg-white/10' }}">
                    <span>Comunicaciones y Plantillas</span>
                </a>
                <a href="{{ route('admin.content.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.content.*') ? 'bg-[#1D70B8] text-white font-semibold' : 'text-slate-300 hover:bg-white/10' }}">
                    <span>Noticias y Banners</span>
                </a>

                @if(auth()->user()->isRole('super_admin'))
                    <div class="pt-3 mt-3 border-t border-white/10 space-y-1">
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-sky-300">Superadmin</p>
                        <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.reports.*') ? 'bg-[#1D70B8] text-white font-semibold' : 'text-slate-300 hover:bg-white/10' }}">
                            <span>Reportes Gerenciales (10)</span>
                        </a>
                        <a href="{{ route('admin.vendors.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.vendors.*') || request()->routeIs('admin.providers.*') ? 'bg-[#1D70B8] text-white font-semibold' : 'text-slate-300 hover:bg-white/10' }}">
                            <span>Área de Proveedores</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.users.*') ? 'bg-[#1D70B8] text-white font-semibold' : 'text-slate-300 hover:bg-white/10' }}">
                            <span>Usuarios y Roles</span>
                        </a>
                        <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.settings.*') ? 'bg-[#1D70B8] text-white font-semibold' : 'text-slate-300 hover:bg-white/10' }}">
                            <span>Configuración y Token DNI</span>
                        </a>
                    </div>
                @endif
                <div class="pt-3 mt-3 border-t border-white/10 space-y-1">
                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-sky-300">Documentación</p>
                    <a href="{{ route('manual.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-slate-300 hover:bg-white/10">
                        <span>Manual de Uso en Línea</span>
                    </a>
                    <a href="{{ route('manual.pdf') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-amber-300 hover:bg-white/10 font-semibold text-xs">
                        <span>Descargar Manual PDF ↓</span>
                    </a>
                </div>
            </nav>

            <div class="p-4 border-t border-white/10 space-y-2">
                <a href="{{ route('home') }}" class="block text-center w-full py-2 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-semibold">Volver a la Tienda</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full py-2 rounded-xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-200 text-xs font-semibold">Cerrar Sesión</button>
                </form>
            </div>
        </aside>

        {{-- Content --}}
        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-white border-b border-slate-200 px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-[#0F2D4A]">@yield('heading', 'Administración MiPromo')</h1>
                    <p class="text-xs text-slate-500">@yield('subheading', 'Operación multi-institución y control de tienda')</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    @if(auth()->user()->isRole('super_admin'))
                        <a href="{{ route('admin.settings.index') }}" class="px-3 py-2 rounded-lg border border-sky-200 bg-sky-50 hover:bg-sky-100 text-[#0F2D4A] font-semibold">Configuración API DNI</a>
                        <a href="{{ route('admin.reports.index') }}" class="px-3 py-2 rounded-lg bg-[#0F2D4A] hover:bg-[#1D70B8] text-white font-semibold">10 Reportes Gerenciales</a>
                    @endif
                    <a href="{{ route('manual.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 font-semibold text-slate-700">Manual</a>
                    <a href="{{ route('admin.orders.export') }}" class="px-3 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 font-semibold text-slate-700">Exportar Pedidos CSV</a>
                    <a href="{{ route('account.index') }}" class="px-3 py-2 rounded-lg bg-slate-100 text-slate-700 font-semibold">Mi Cuenta</a>
                </div>
            </header>

            <div class="px-6 pt-4">
                @if(session('success'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <main class="flex-1 p-6">
                @yield('content')
            </main>

            @include('components.footer')
        </div>
    </div>
    @include('components.dni-helper')
</body>
</html>
