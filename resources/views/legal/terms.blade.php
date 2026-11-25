@extends('layouts.app')

@section('title', 'Términos, Condiciones y Cambios — MiPromo')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-10 space-y-5 shadow-sm">
        <h1 class="text-3xl font-extrabold text-[#0F2D4A]">Términos, Condiciones y Política de Cambios</h1>
        <div class="space-y-4 text-sm text-slate-700 leading-relaxed">
            <p><strong>1. Uso de marcas e insignias institucionales:</strong> Los productos personalizados con escudos y lemas se confeccionan en coordinación con convenios institucionales o delegaciones autorizadas de ex-alumnos.</p>
            <p><strong>2. Campañas grupales y preventas:</strong> Las campañas cuentan con una meta mínima de unidades y plazo límite. Los adelantos reservan la producción consolidada del lote.</p>
            <p><strong>3. Cambios y devoluciones:</strong> Los productos en stock estándar admiten cambios dentro de los 7 días calendario posteriores a la entrega. Las prendas personalizadas con nombre o bordado específico solo admiten cambio o reposición por falla de fabricación o error en la talla solicitada.</p>
            <p><strong>4. Atención al cliente:</strong> Canal directo Grupo JMSPERU por WhatsApp al <a href="https://wa.me/51959199368?text={{ urlencode('Hola Grupo JMSPERU, consulta de atención al cliente MiPromo.') }}" target="_blank" rel="noopener noreferrer" class="font-bold text-emerald-700 underline">959199368 ↗</a> o mediante nuestro <a href="{{ route('legal.complaints') }}" class="text-[#1D70B8] underline font-bold">Libro de Reclamaciones Virtual</a>.</p>
        </div>
    </div>
</div>
@endsection
