@extends('layouts.app')

@section('title', 'Mi Cuenta — MiPromo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 flex flex-wrap items-center justify-between gap-4 shadow-sm">
        <div>
            <span class="px-2.5 py-1 rounded-full bg-sky-50 text-[#1D70B8] text-xs font-bold">{{ $user->roleLabel() }}</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F2D4A] mt-2">{{ $user->name }}</h1>
            <p class="text-sm text-slate-500">{{ $user->email }} @if($user->institution) · {{ $user->institution->name }} @endif</p>
        </div>

        <div class="flex flex-wrap gap-3">
            @if($user->canAccessAdmin())
                <a href="{{ route('admin.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-amber-500 text-white text-xs font-bold hover:bg-amber-600 transition">Ir al Panel Administrativo</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100">Cerrar Sesión</button>
            </form>
        </div>
    </div>

    <div class="grid lg:grid-cols-12 gap-8">
        {{-- Perfil y Privacidad --}}
        <div class="lg:col-span-5 space-y-6">
            <form action="{{ route('account.privacy') }}" method="POST" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                @csrf
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Perfil y Control de Privacidad (Ley 29733)</h2>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Celular</label>
                        <input type="text" name="phone" value="{{ $user->phone }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Ciudad</label>
                        <input type="text" name="city" value="{{ $user->city }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    @if($user->alumniProfile)
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Profesión</label>
                            <input type="text" name="profession" value="{{ $user->alumniProfile->profession }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Empresa</label>
                            <input type="text" name="company" value="{{ $user->alumniProfile->company }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                        </div>
                    @endif
                </div>

                @if($user->alumniProfile)
                    <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-2 text-xs">
                        <p class="font-bold text-slate-700">Visibilidad en Directorio Público (Estado: {{ $user->alumniProfile->verification_status }})</p>
                        <label class="flex items-center gap-2"><input type="checkbox" name="show_city" value="1" @checked($user->alumniProfile->allowsField('city'))> Mostrar ciudad actual</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="show_company" value="1" @checked($user->alumniProfile->allowsField('company'))> Mostrar empresa / centro laboral</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="show_email" value="1" @checked($user->alumniProfile->allowsField('email'))> Mostrar correo en directorio</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="show_phone" value="1" @checked($user->alumniProfile->allowsField('phone'))> Mostrar celular</label>
                    </div>
                @endif

                <button type="submit" class="w-full py-2.5 rounded-xl bg-[#1D70B8] text-white text-xs font-bold">Guardar Preferencias</button>
            </form>

            {{-- Seguridad: Actualizar Contraseña --}}
            <form action="{{ route('account.password') }}" method="POST" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3 text-xs">
                @csrf
                <h2 class="text-base font-extrabold text-[#0F2D4A]">Seguridad: Cambiar Contraseña</h2>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Contraseña actual *</label>
                    <input type="password" name="current_password" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nueva contraseña (mín. 8 caracteres, letras y números) *</label>
                    <input type="password" name="password" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Confirmar nueva contraseña *</label>
                    <input type="password" name="password_confirmation" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                </div>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-[#0F2D4A] text-white font-bold transition">Actualizar Contraseña</button>
            </form>

            {{-- Lista de deseos --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Lista de Deseos ({{ $wishlists->count() }})</h2>
                @forelse($wishlists as $wish)
                    <a href="{{ route('products.show', $wish->product->slug) }}" class="block p-3 rounded-xl bg-slate-50 hover:bg-sky-50 text-sm font-semibold text-slate-800">
                        {{ $wish->product->name }} — S/ {{ number_format($wish->product->base_price, 2) }}
                    </a>
                @empty
                    <p class="text-xs text-slate-500">Aún no guardaste productos favoritos.</p>
                @endforelse
            </div>
        </div>

        {{-- Pedidos, Campañas y Comunidades --}}
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Mis Pedidos ({{ $orders->count() }})</h2>
                <div class="divide-y divide-slate-100 text-sm">
                    @forelse($orders as $order)
                        <div class="py-3 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <a href="{{ route('orders.confirmation', $order->order_number) }}" class="font-bold text-[#1D70B8] hover:underline">{{ $order->order_number }}</a>
                                <p class="text-xs text-slate-500">{{ $order->created_at->format('d/m/Y') }} · {{ $order->orderStatusLabel() }} · {{ $order->paymentStatusLabel() }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-extrabold">S/ {{ number_format($order->total, 2) }}</span>
                                <a href="{{ route('orders.invoice', $order->order_number) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 text-xs font-semibold">SUNAT</a>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500">Aún no registras pedidos.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Mis Participaciones en Campañas ({{ $participations->count() }})</h2>
                @forelse($participations as $part)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                        <div>
                            <a href="{{ route('campaigns.show', $part->campaign->slug) }}" class="font-bold text-[#0F2D4A] hover:underline">{{ $part->campaign->title }}</a>
                            <p class="text-slate-500">{{ $part->quantity }} ud(s) · Pagado S/ {{ number_format($part->amount_paid, 2) }} de S/ {{ number_format($part->total_amount, 2) }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-sky-100 text-[#1D70B8] font-bold">{{ $part->payment_status }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-500">No estás inscrito en campañas grupales todavía.</p>
                @endforelse
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Mis Comunidades ({{ $memberships->count() }})</h2>
                <div class="flex flex-wrap gap-2">
                    @forelse($memberships as $mem)
                        <a href="{{ route('communities.show', $mem->community->slug) }}" class="px-3.5 py-2 rounded-xl bg-sky-50 text-[#1D70B8] text-xs font-bold hover:bg-sky-100">
                            {{ $mem->community->name }}
                        </a>
                    @empty
                        <p class="text-xs text-slate-500">Únete a la comunidad de tu promoción desde el menú Comunidades.</p>
                    @endforelse
                </div>
            </div>

            {{-- Programa de Referidos (Módulo L) + Notificaciones (Módulo K) --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-extrabold text-[#0F2D4A]">Invita a tu Promoción (Referidos)</h2>
                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">{{ $referralsCount }} referido(s)</span>
                </div>
                <p class="text-xs text-slate-600">Comparte tu enlace personal con tus compañeros de colegio para que se registren y formen parte de tu promoción:</p>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono break-all">
                    {{ route('register', ['ref' => $user->referral_code]) }}
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-extrabold text-[#0F2D4A]">Buzón de Notificaciones ({{ $notifications->count() }})</h2>
                    @if($notifications->whereNull('read_at')->isNotEmpty())
                        <form action="{{ route('account.notifications.read') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-[#1D70B8] hover:underline">Marcar leídas</button>
                        </form>
                    @endif
                </div>
                @forelse($notifications as $notif)
                    <div class="p-3 rounded-xl border text-xs {{ $notif->read_at ? 'bg-slate-50 border-slate-100' : 'bg-sky-50 border-sky-200' }}">
                        <p class="font-bold text-slate-900">{{ $notif->title }}</p>
                        <p class="text-slate-600 mt-0.5">{{ $notif->body }}</p>
                    </div>
                @empty
                    <p class="text-xs text-slate-500">No tienes notificaciones pendientes.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
