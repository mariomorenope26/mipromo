@extends('layouts.admin')

@section('heading', 'Configuración del Sistema e Integración API Perú')
@section('subheading', 'Administra el Token Bearer de apiperu.dev para búsqueda/registro por DNI y parámetros operativos')

@section('content')
<div class="space-y-8">
    {{-- Tarjeta Resumen Estado API --}}
    <div class="grid md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase text-slate-400">API Consulta DNI</p>
                <p class="text-lg font-extrabold text-[#0F2D4A] mt-1">apiperu.dev (RENIEC)</p>
                <p class="text-xs text-slate-500 mt-0.5">Fuente: {{ $tokenSource }}</p>
            </div>
            @if($hasToken && $settings['apiperu_enabled'])
                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-extrabold shrink-0">Activa ✓</span>
            @elseif($hasToken)
                <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-extrabold shrink-0">Pausada</span>
            @else
                <span class="px-3 py-1 rounded-full bg-rose-100 text-rose-800 text-xs font-extrabold shrink-0">Sin Token</span>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-bold uppercase text-slate-400">Token Actual Almacenado</p>
            <p class="text-sm font-mono font-bold text-slate-800 mt-2">
                {{ $hasToken ? $maskedToken : 'No configurado aún' }}
            </p>
            <p class="text-[11px] text-slate-500 mt-1">Guardado con cifrado AES-256 (Laravel Crypt).</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase text-slate-400">Documentación Oficial</p>
                <p class="text-sm font-extrabold text-[#0F2D4A] mt-1">Manual en Línea &amp; PDF</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Incluye capturas reales del sistema</p>
            </div>
            <div class="flex flex-col gap-1.5 shrink-0 text-xs">
                <a href="{{ route('manual.index') }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-[#0F2D4A] text-white font-bold text-center hover:bg-[#1D70B8]">Ver Web</a>
                <a href="{{ route('manual.pdf') }}" class="px-3 py-1.5 rounded-lg bg-amber-400 text-[#0F2D4A] font-extrabold text-center hover:bg-amber-300">PDF ↓</a>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-12 gap-6 items-start">
        {{-- Formulario Principal de Configuración --}}
        <form action="{{ route('admin.settings.update') }}" method="POST"
              x-data="{ showToken: false }"
              class="lg:col-span-7 bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 space-y-5 shadow-sm">
            @csrf
            @method('PUT')

            <div class="border-b border-slate-100 pb-4 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-extrabold text-[#0F2D4A]">Credenciales apiperu.dev &amp; Parámetros Generales</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Permite consultar DNI en Registro de Clientes, Directorio de Egresados, Checkout y Panel Admin.</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-sky-50 text-[#1D70B8] text-[11px] font-bold">POST Bearer JSON</span>
            </div>

            <div class="space-y-4 text-sm">
                <label class="flex items-start gap-3 p-4 rounded-2xl border border-sky-200 bg-sky-50/70 cursor-pointer">
                    <input type="checkbox" name="apiperu_enabled" value="1" @checked($settings['apiperu_enabled']) class="mt-0.5 rounded border-slate-300 text-[#1D70B8]">
                    <div>
                        <span class="block text-sm font-extrabold text-[#0F2D4A]">Activar búsqueda y autocompletado automático por DNI (apiperu.dev)</span>
                        <span class="block text-xs text-slate-600 mt-0.5">Habilita los botones "Consultar DNI" en Registro, Directorio de Egresados, Checkout, Usuarios y Padrón Admin.</span>
                    </div>
                </label>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Token Bearer de apiperu.dev *</label>
                    <div class="flex gap-2">
                        <input :type="showToken ? 'text' : 'password'"
                               name="apiperu_token"
                               autocomplete="new-password"
                               placeholder="{{ $hasToken ? 'Dejar vacío para conservar token actual ('.$maskedToken.')' : 'Pega aquí tu token generado en apiperu.dev' }}"
                               class="flex-1 px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-xs">
                        <button type="button" @click="showToken = !showToken" class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-600 hover:bg-slate-50">
                            <span x-text="showToken ? 'Ocultar' : 'Mostrar'"></span>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Obtén tu token en <span class="font-semibold text-[#1D70B8]">apiperu.dev → Panel de Usuario → Tokens</span>. Nunca compartas este token en repositorios públicos.
                    </p>
                    @if($hasToken)
                        <label class="inline-flex items-center gap-2 mt-2 text-xs text-rose-700 cursor-pointer">
                            <input type="checkbox" name="clear_token" value="1" class="rounded border-rose-300 text-rose-600">
                            <span>Eliminar token almacenado actualmente</span>
                        </label>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">URL del Endpoint DNI apiperu.dev *</label>
                    <input type="url" name="apiperu_endpoint" required value="{{ old('apiperu_endpoint', $settings['apiperu_endpoint']) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-xs">
                    <p class="text-[11px] text-slate-500 mt-1">Recomendado: <code>https://api.apiperu.dev/dni</code> (o legacy <code>https://apiperu.dev/api/dni</code>).</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">WhatsApp Oficial (Enlaces Directos)</label>
                        <input type="text" name="whatsapp_number" required value="{{ old('whatsapp_number', $settings['whatsapp_number']) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Correo de Soporte Técnico</label>
                        <input type="email" name="support_email" value="{{ old('support_email', $settings['support_email']) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white font-extrabold text-sm shadow-sm transition">
                Guardar Configuración Segura
            </button>
        </form>

        {{-- Probador en Vivo de Token API Perú --}}
        <div class="lg:col-span-5 space-y-5">
            <form action="{{ route('admin.settings.test_dni') }}" method="POST" class="bg-white rounded-3xl border border-slate-200 p-6 space-y-4 shadow-sm">
                @csrf
                <h2 class="text-base font-extrabold text-[#0F2D4A]">Probar Conexión con apiperu.dev</h2>
                <p class="text-xs text-slate-500">Ingresa un DNI peruano real de 8 dígitos para verificar que tu token responde correctamente antes de publicar.</p>

                <div class="space-y-3 text-sm">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">DNI de Prueba (8 dígitos)</label>
                        <input type="text" name="test_dni" maxlength="8" required placeholder="Ej. 44556677" value="{{ old('test_dni', $testResult['dni'] ?? '') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Token temporal de prueba (opcional)</label>
                        <input type="password" name="override_token" placeholder="Opcional: probar un token nuevo antes de guardarlo" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-xs">
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-[#1D70B8] text-white font-bold text-xs transition">
                    Ejecutar Prueba de Consulta DNI →
                </button>

                @if($testResult)
                    <div class="rounded-2xl p-4 text-xs border {{ ($testResult['ok'] ?? false) ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900' }} space-y-1.5">
                        <p class="font-extrabold">
                            {{ ($testResult['ok'] ?? false) ? '✓ Consulta Exitosa' : '✕ Error en Consulta' }}
                            @if(!empty($testResult['elapsed_ms']))
                                <span class="font-normal opacity-75">({{ $testResult['elapsed_ms'] }} ms)</span>
                            @endif
                        </p>
                        <p>{{ $testResult['message'] ?? '' }}</p>
                        @if(!empty($testResult['data']))
                            <div class="bg-white/80 rounded-xl p-2.5 font-mono text-[11px] space-y-0.5 text-slate-800 border border-emerald-200">
                                <p><strong>DNI:</strong> {{ $testResult['data']['dni'] }}</p>
                                <p><strong>Nombre Formateado:</strong> {{ $testResult['data']['full_name'] }}</p>
                                <p><strong>RENIEC Completo:</strong> {{ $testResult['data']['nombre_completo'] }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </form>

            <div class="bg-[#0F2D4A] text-white rounded-3xl p-6 space-y-3 text-xs">
                <h3 class="font-extrabold text-sm text-amber-300">Recomendaciones de Uso &amp; Seguridad</h3>
                <ul class="space-y-2 text-slate-200 leading-relaxed list-disc pl-4">
                    <li>El sistema cachea las consultas exitosas de DNI durante <strong>24 horas</strong> para evitar consumir créditos innecesarios de tu plan apiperu.dev.</li>
                    <li>Las consultas públicas están protegidas con <strong>rate limiting (20/min)</strong> para prevenir abuso automático por bots.</li>
                    <li>Incluso si la API externa está sin saldo o caída, clientes y delegados pueden completar su registro manualmente sin perder ventas.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
