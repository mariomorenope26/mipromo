@extends('layouts.admin')

@section('heading', 'Moderación, Libro de Reclamaciones y Auditoría')
@section('subheading', 'Atención de reportes de comunidad, hojas de reclamación y bitácora del sistema')

@section('content')
<div class="space-y-8">
    {{-- Reportes de Contenido --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
        <h2 class="font-extrabold text-[#0F2D4A]">Reportes de Comunidad</h2>
        @forelse($reports as $report)
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                <div>
                    <p class="font-bold">{{ $report->reason }} ({{ $report->status }})</p>
                    <p class="text-slate-500">Reportado por {{ $report->reporter->name }} · {{ $report->created_at->diffForHumans() }}</p>
                </div>
                @if($report->status === 'open')
                    <div class="flex gap-2">
                        <form action="{{ route('admin.moderation.reports.resolve', $report->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="hide_post">
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-600 text-white font-bold">Ocultar publicación</button>
                        </form>
                        <form action="{{ route('admin.moderation.reports.resolve', $report->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="dismiss">
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-700 text-white font-bold">Desestimar</button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-xs text-slate-500">Sin reportes registrados.</p>
        @endforelse
    </div>

    {{-- Libro de Reclamaciones --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
        <h2 class="font-extrabold text-[#0F2D4A]">Hojas del Libro de Reclamaciones Virtual</h2>
        @foreach($complaints as $complaint)
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                <div class="flex justify-between">
                    <strong>{{ $complaint->complaint_code }} — {{ $complaint->customer_name }} ({{ ucfirst($complaint->type) }})</strong>
                    <span class="px-2 py-0.5 rounded bg-white font-bold">{{ $complaint->status }}</span>
                </div>
                <p><strong>Asunto:</strong> {{ $complaint->subject }}</p>
                <p class="text-slate-600">{{ $complaint->detail }}</p>
                @if($complaint->status === 'pending')
                    <form action="{{ route('admin.moderation.complaints.respond', $complaint->id) }}" method="POST" class="flex gap-2 pt-2">
                        @csrf
                        @method('PATCH')
                        <input type="text" name="response" required placeholder="Respuesta formal al consumidor..." class="flex-1 px-3 py-1.5 rounded-lg border border-slate-300 bg-white">
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-[#1D70B8] text-white font-bold">Responder</button>
                    </form>
                @else
                    <p class="text-emerald-700"><strong>Respuesta:</strong> {{ $complaint->response }}</p>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Audit Log --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
        <h2 class="font-extrabold text-[#0F2D4A]">Registro de Auditoría</h2>
        <div class="divide-y divide-slate-100 text-xs">
            @foreach($logs as $log)
                <div class="py-2 flex justify-between gap-2">
                    <span><strong>{{ $log->action }}</strong> — {{ $log->description }}</span>
                    <span class="text-slate-400">{{ $log->user?->name ?? 'Sistema' }} · {{ $log->created_at->format('d/m/Y H:i') }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
