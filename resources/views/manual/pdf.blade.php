<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Manual Oficial de Uso — MiPromo Store (Grupo JMSPERU)</title>
    <style>
        @page {
            margin: 18mm 15mm 18mm 15mm;
        }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 10pt;
            line-height: 1.45;
            margin: 0;
        }
        .header-bar {
            width: 100%;
            border-bottom: 2px solid #0F2D4A;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .header-bar table { width: 100%; border-collapse: collapse; }
        .cover-box {
            background-color: #0F2D4A;
            color: #ffffff;
            border-radius: 10px;
            padding: 28px 26px;
            margin-bottom: 18px;
        }
        .cover-badge {
            display: inline-block;
            background-color: #facc15;
            color: #0F2D4A;
            font-weight: bold;
            font-size: 8.5pt;
            padding: 4px 10px;
            border-radius: 12px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        .cover-title {
            font-size: 22pt;
            font-weight: bold;
            margin: 0 0 8px 0;
            line-height: 1.2;
        }
        .cover-subtitle {
            font-size: 10.5pt;
            color: #cbd5e1;
            margin: 0 0 16px 0;
        }
        .logo-card {
            display: inline-block;
            background-color: #ffffff;
            padding: 8px 14px;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        .logo-card img { height: 34px; width: auto; }
        .meta-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .meta-table td {
            background-color: #163d61;
            color: #e2e8f0;
            padding: 8px 10px;
            font-size: 8.5pt;
            border: 1px solid #265178;
        }
        .toc-box {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 14px 16px;
            background-color: #f8fafc;
            margin-bottom: 16px;
        }
        .toc-title {
            font-size: 12pt;
            font-weight: bold;
            color: #0F2D4A;
            margin: 0 0 8px 0;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }
        .toc-table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
        .toc-table td { padding: 3px 4px; border-bottom: 1px dotted #e2e8f0; }
        .page-break { page-break-after: always; }
        .chapter {
            margin-bottom: 18px;
            page-break-inside: avoid;
        }
        .chapter-head {
            background-color: #f1f5f9;
            border-left: 4px solid #1D70B8;
            padding: 8px 12px;
            margin-bottom: 10px;
        }
        .chapter-meta {
            font-size: 8pt;
            font-weight: bold;
            color: #1D70B8;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .chapter-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0F2D4A;
            margin: 0;
        }
        .chapter-summary {
            font-size: 9.5pt;
            color: #334155;
            margin: 0 0 10px 0;
        }
        .shot-frame {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 4px;
            background-color: #ffffff;
            margin-bottom: 10px;
            text-align: center;
        }
        .shot-frame img {
            width: 100%;
            max-height: 255px;
            height: auto;
            display: block;
        }
        .shot-caption {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 3px;
            text-align: center;
        }
        .steps-box {
            margin-bottom: 8px;
        }
        .steps-heading {
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #475569;
            margin: 0 0 5px 0;
        }
        .step-row {
            margin-bottom: 4px;
            font-size: 9pt;
            color: #1e293b;
        }
        .step-num {
            display: inline-block;
            background-color: #1D70B8;
            color: #ffffff;
            font-weight: bold;
            font-size: 7.5pt;
            padding: 1px 5px;
            border-radius: 9px;
            margin-right: 5px;
        }
        .rec-box {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #f59e0b;
            border-radius: 5px;
            padding: 8px 11px;
            font-size: 8.5pt;
            color: #78350f;
        }
        .rec-title {
            font-weight: bold;
            color: #92400e;
            margin: 0 0 4px 0;
            font-size: 8.5pt;
            text-transform: uppercase;
        }
        .rec-box ul { margin: 0; padding-left: 16px; }
        .rec-box li { margin-bottom: 2px; }
        .bp-card {
            border: 1px solid #cbd5e1;
            border-left: 4px solid #0F2D4A;
            background-color: #f8fafc;
            border-radius: 5px;
            padding: 9px 12px;
            margin-bottom: 8px;
        }
        .bp-card h4 { margin: 0 0 3px 0; font-size: 9.5pt; color: #0F2D4A; }
        .bp-card p { margin: 0; font-size: 8.5pt; color: #475569; }
        .footer-note {
            margin-top: 16px;
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
            text-align: center;
            font-size: 8pt;
            color: #475569;
        }
    </style>
</head>
<body>
    {{-- PORTADA + ÍNDICE --}}
    <div class="cover-box">
        @if($logoDataUri)
            <div class="logo-card">
                <img src="{{ $logoDataUri }}" alt="Grupo JMSPERU">
            </div>
        @endif
        <div><span class="cover-badge">Manual Oficial del Sistema · Versión PDF</span></div>
        <h1 class="cover-title">MiPromo Egresados Store &amp; Gestión Multi-Colegio</h1>
        <p class="cover-subtitle">
            Documentación integral con capturas reales del sistema, operación de tienda, integración de búsqueda/registro por DNI (apiperu.dev), gestión de colegios/egresados, proveedores, 10 reportes gerenciales y despliegue en producción.
        </p>
        <table class="meta-table">
            <tr>
                <td><strong>Empresa:</strong> Grupo JMSPERU</td>
                <td><strong>Contacto / WhatsApp:</strong> +51 959 199 368</td>
                <td><strong>Emisión:</strong> {{ $generatedAt }}</td>
            </tr>
        </table>
    </div>

    <div class="toc-box">
        <h2 class="toc-title">Tabla de Contenidos ({{ count($sections) }} Capítulos)</h2>
        <table class="toc-table">
            @foreach($sections as $section)
                <tr>
                    <td style="width: 34px; font-weight: bold; color: #1D70B8;">Cap. {{ $section['number'] }}</td>
                    <td style="font-weight: bold; color: #0F2D4A;">{{ $section['title'] }}</td>
                    <td style="width: 135px; color: #64748b;">{{ $section['audience_label'] }}</td>
                    <td style="width: 120px; font-family: monospace; font-size: 7.5pt; color: #475569;">{{ $section['route'] }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="page-break"></div>

    {{-- CAPÍTULOS CON CAPTURAS REALES --}}
    @foreach($sections as $idx => $section)
        <div class="chapter">
            <div class="chapter-head">
                <div class="chapter-meta">Capítulo {{ $section['number'] }} · {{ $section['audience_label'] }} · Ruta: {{ $section['route'] }}</div>
                <h2 class="chapter-title">{{ $section['title'] }}</h2>
            </div>

            <p class="chapter-summary">{{ $section['summary'] }}</p>

            @if(!empty($section['image_data_uri']))
                <div class="shot-frame">
                    <img src="{{ $section['image_data_uri'] }}" alt="{{ $section['title'] }}">
                    <div class="shot-caption">Captura real en pantalla — {{ $section['title'] }} ({{ $section['route'] }})</div>
                </div>
            @endif

            <div class="steps-box">
                <p class="steps-heading">Instrucciones Paso a Paso</p>
                @foreach($section['steps'] as $sIdx => $step)
                    <div class="step-row">
                        <span class="step-num">{{ $sIdx + 1 }}</span>{{ $step }}
                    </div>
                @endforeach
            </div>

            <div class="rec-box">
                <p class="rec-title">Recomendaciones Profesionales</p>
                <ul>
                    @foreach($section['recommendations'] as $rec)
                        <li>{{ $rec }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        @if(($idx + 1) % 2 === 0 && ! $loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <div class="page-break"></div>

    {{-- SECCIÓN FINAL DE BUENAS PRÁCTICAS Y SEGURIDAD --}}
    <div class="header-bar">
        <table>
            <tr>
                <td style="font-size: 13pt; font-weight: bold; color: #0F2D4A;">Recomendaciones Finales de Operación y Producción</td>
                <td style="text-align: right; font-size: 8.5pt; color: #64748b;">Grupo JMSPERU :: Contacto 959199368</td>
            </tr>
        </table>
    </div>

    @foreach($bestPractices as $bp)
        <div class="bp-card">
            <h4>{{ $bp['title'] }}</h4>
            <p>{{ $bp['body'] }}</p>
        </div>
    @endforeach

    <div class="footer-note">
        <strong>Todos los derechos reservados son del Grupo JMSPERU :: Contacto 959199368</strong><br>
        Manual en línea disponible permanentemente en /manual · Versión PDF generada el {{ $generatedAt }}.
    </div>
</body>
</html>
