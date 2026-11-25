@extends('layouts.admin')

@section('heading', 'Comunicaciones, Notificaciones y Plantillas')
@section('subheading', 'Módulo K: Anuncios segmentados por colegio/rol, enlaces a WhatsApp y plantillas reutilizables')

@section('content')
<div class="space-y-8">
    <div class="grid lg:grid-cols-12 gap-6">
        {{-- Enviar Comunicado Masivo --}}
        <form action="{{ route('admin.communications.broadcast') }}" method="POST" class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            @csrf
            <h2 class="font-extrabold text-[#0F2D4A]">Enviar Anuncio / Notificación Segmentada</h2>
            <div class="grid sm:grid-cols-3 gap-3 text-sm">
                <select name="channel" class="px-3 py-2 rounded-xl border border-slate-300">
                    <option value="web">Notificación Web + Buzón</option>
                    <option value="email">Correo Transaccional / Masivo</option>
                    <option value="whatsapp">Alerta WhatsApp</option>
                </select>
                <select name="institution_id" class="px-3 py-2 rounded-xl border border-slate-300">
                    <option value="">Todas las instituciones</option>
                    @foreach($institutions as $inst)
                        <option value="{{ $inst->id }}">{{ $inst->short_name }}</option>
                    @endforeach
                </select>
                <select name="role" class="px-3 py-2 rounded-xl border border-slate-300">
                    <option value="">Todos los roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->value }}">{{ $role->label() }}</option>
                    @endforeach
                </select>
                <input type="text" name="title" required placeholder="Asunto o título del anuncio" class="sm:col-span-2 px-3 py-2 rounded-xl border border-slate-300">
                <input type="text" name="action_url" placeholder="Enlace (ej. /campanas)" class="px-3 py-2 rounded-xl border border-slate-300">
            </div>
            <textarea name="body" required rows="3" placeholder="Redacta el mensaje para los ex-alumnos o delegados..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="https://wa.me/51959199368?text={{ urlencode('Hola Grupo JMSPERU / MiPromo, consulta de comunicación institucional:') }}" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-emerald-700 hover:underline">
                    Abrir canal directo WhatsApp 959199368 ↗
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1D70B8] text-white text-xs font-bold">Enviar Comunicado</button>
            </div>
        </form>

        {{-- Crear Plantilla --}}
        <form action="{{ route('admin.communications.templates.store') }}" method="POST" class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
            @csrf
            <h2 class="font-extrabold text-[#0F2D4A]">Nueva Plantilla de Mensaje</h2>
            <div class="grid grid-cols-2 gap-2 text-sm">
                <input type="text" name="name" required placeholder="Nombre (ej. Cierre campaña)" class="px-3 py-2 rounded-xl border border-slate-300">
                <select name="channel" class="px-3 py-2 rounded-xl border border-slate-300">
                    <option value="whatsapp">WhatsApp</option>
                    <option value="email">Correo</option>
                    <option value="web">Web</option>
                </select>
                <input type="text" name="subject" required placeholder="Asunto" class="col-span-2 px-3 py-2 rounded-xl border border-slate-300">
            </div>
            <textarea name="body" required rows="3" placeholder="Contenido con variables ej. {nombre}, {colegio}, {campana}..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">Guardar Plantilla</button>
        </form>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
            <h2 class="font-extrabold text-[#0F2D4A]">Plantillas Configuradas ({{ $templates->count() }})</h2>
            @foreach($templates as $tpl)
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1">
                    <div class="flex justify-between">
                        <strong>{{ $tpl->name }}</strong>
                        <span class="px-2 py-0.5 rounded bg-sky-100 text-[#1D70B8] font-bold uppercase">{{ $tpl->channel }}</span>
                    </div>
                    <p class="text-slate-600">{{ $tpl->body }}</p>
                </div>
            @endforeach
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
            <h2 class="font-extrabold text-[#0F2D4A]">Últimas Notificaciones Entregadas</h2>
            <div class="divide-y divide-slate-100 text-xs max-h-80 overflow-y-auto">
                @foreach($recentNotifications as $notif)
                    <div class="py-2.5 flex justify-between gap-2">
                        <div>
                            <p class="font-bold">{{ $notif->title }} <span class="text-slate-400 font-normal">→ {{ $notif->user->name }}</span></p>
                            <p class="text-slate-500">{{ $notif->body }}</p>
                        </div>
                        <span class="text-[10px] uppercase font-bold text-[#1D70B8] shrink-0">{{ $notif->channel }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
