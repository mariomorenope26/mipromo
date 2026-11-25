@extends('layouts.app')

@section('title', 'Libro de Reclamaciones Virtual — Grupo JMSPERU')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold">INDECOPI · D.S. N.° 011-2011-PCM</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F2D4A] mt-2">Libro de Reclamaciones Virtual</h1>
                <p class="text-xs text-slate-500">Grupo JMSPERU Solutions · WhatsApp directo: <a href="https://wa.me/51959199368?text={{ urlencode('Hola Grupo JMSPERU, consulta sobre Libro de Reclamaciones.') }}" target="_blank" rel="noopener noreferrer" class="font-bold text-emerald-700 hover:underline">959199368 ↗</a></p>
            </div>
            <img src="{{ asset('images/logo_jmsperu.png') }}" alt="Grupo JMSPERU" class="h-10 w-auto">
        </div>

        {{-- Consultar estado --}}
        <form method="GET" action="{{ route('legal.complaints') }}" class="flex gap-2 bg-slate-50 p-3 rounded-2xl border border-slate-200">
            <input type="text" name="codigo" value="{{ $lookupCode }}" placeholder="Consultar hoja existente (ej. LRV-2026-0001)" class="flex-1 px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm">
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">Consultar</button>
        </form>

        @if($complaint)
            <div class="rounded-2xl bg-sky-50 border border-sky-200 p-5 text-sm space-y-2">
                <div class="flex justify-between">
                    <strong class="text-[#0F2D4A]">Hoja {{ $complaint->complaint_code }} ({{ ucfirst($complaint->type) }})</strong>
                    <span class="px-2.5 py-0.5 rounded-full bg-white text-[#1D70B8] text-xs font-bold">{{ $complaint->status === 'resolved' ? 'Respondida' : 'En atención' }}</span>
                </div>
                <p><strong>Asunto:</strong> {{ $complaint->subject }}</p>
                <p class="text-slate-600">{{ $complaint->detail }}</p>
                @if($complaint->response)
                    <p class="pt-2 border-t border-sky-200 text-emerald-800"><strong>Respuesta del proveedor:</strong> {{ $complaint->response }}</p>
                @endif
            </div>
        @endif

        {{-- Nueva Hoja --}}
        <form action="{{ route('legal.complaints.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nombre completo del consumidor</label>
                    <input type="text" name="customer_name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">DNI / CE</label>
                    <input type="text" name="customer_doc_number" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Correo electrónico</label>
                    <input type="email" name="customer_email" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Teléfono</label>
                    <input type="text" name="customer_phone" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tipo</label>
                    <select name="type" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                        <option value="reclamo">Reclamo (disconformidad con el producto o servicio)</option>
                        <option value="queja">Queja (malestar respecto a la atención)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pedido relacionado (opcional)</label>
                    <input type="text" name="order_number" placeholder="Ej. PED-2026-0001" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Asunto / Bien contratado</label>
                    <input type="text" name="subject" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Detalle de la reclamación</label>
                    <textarea name="detail" required rows="3" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pedido del consumidor</label>
                    <textarea name="request_detail" required rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm"></textarea>
                </div>
            </div>
            <button type="submit" class="px-6 py-3 rounded-xl bg-[#0F2D4A] hover:bg-[#1D70B8] text-white text-sm font-bold transition">
                Registrar Hoja de Reclamación
            </button>
        </form>
    </div>
</div>
@endsection
