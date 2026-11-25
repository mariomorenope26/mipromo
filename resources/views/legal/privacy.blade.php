@extends('layouts.app')

@section('title', 'Política de Privacidad — Ley N.° 29733')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-10 space-y-5 shadow-sm">
        <span class="px-3 py-1 rounded-full bg-sky-50 text-[#1D70B8] text-xs font-bold">Protección de Datos Personales · Perú</span>
        <h1 class="text-3xl font-extrabold text-[#0F2D4A]">Política de Privacidad (Ley N.° 29733)</h1>
        <p class="text-sm text-slate-600 leading-relaxed">
            <strong>Grupo JMSPERU</strong> garantiza el cumplimiento estricto de la Ley N.° 29733, Ley de Protección de Datos Personales, y su Reglamento (D.S. N.° 003-2013-JUS) en la plataforma <strong>MiPromo Egresados Store</strong>.
        </p>
        <div class="space-y-3 text-sm text-slate-700 leading-relaxed">
            <h2 class="font-bold text-[#0F2D4A]">1. Finalidad del tratamiento</h2>
            <p>Los datos proporcionados por ex-alumnos y compradores se utilizan exclusivamente para validar su pertenencia a su institución/promoción, gestionar comunidades cerradas, procesar pedidos personalizados, emitir comprobantes electrónicos SUNAT y coordinar entregas nacionales.</p>
            <h2 class="font-bold text-[#0F2D4A]">2. Público objetivo adulto</h2>
            <p>El registro de cuentas y participación en comunidades está dirigido exclusivamente a egresados adultos mayores de 18 años.</p>
            <h2 class="font-bold text-[#0F2D4A]">3. Control de visibilidad y Derechos ARCO</h2>
            <p>Cada egresado controla desde su perfil qué datos son visibles en el directorio (ciudad, correo, empresa, teléfono) y puede ejercer sus derechos de Acceso, Rectificación, Cancelación y Oposición escribiendo directamente vía WhatsApp al <a href="https://wa.me/51959199368?text={{ urlencode('Hola Grupo JMSPERU, consulta sobre derechos ARCO Ley 29733.') }}" target="_blank" rel="noopener noreferrer" class="font-bold text-emerald-700 underline">959199368 ↗</a>.</p>
        </div>
    </div>
</div>
@endsection
