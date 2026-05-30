<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Epicrisis Clínica - {{ $internacion->paciente?->nombre ?? 'Paciente' }} {{ $internacion->paciente?->apellidos ?? '' }}</title>
    <style>
        /* Aumentamos significativamente los márgenes físicos de impresión */
        @page {
            margin: 60pt 55pt 60pt 55pt;
        }
        
        /* Reset suave y controlado para evitar estiramientos brutales */
        body, h1, h2, h3, p, table, tr, td, th, ul, li { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 8.5pt; 
            line-height: 1.5; 
            color: #1e293b;
            background-color: #ffffff;
        }
        
        /* Header styling con excelente espaciado y márgenes */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .header-logo-cell {
            width: 50%;
            vertical-align: top;
            padding-bottom: 10px;
        }
        .header-title-cell {
            width: 50%;
            text-align: right;
            vertical-align: top;
            padding-bottom: 10px;
        }
        .header-logo-text {
            font-size: 22pt;
            font-weight: 900;
            color: #581c87; /* UNITEPC Purple */
            letter-spacing: -1px;
            margin-bottom: 5px;
            display: inline-block;
        }
        .header-subtitle {
            font-size: 8pt;
            color: #64748b;
            margin-top: 4px;
            line-height: 1.3;
        }
        .header-title {
            font-size: 15pt;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        /* Section styling */
        .section {
            margin-bottom: 22px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 9pt;
            font-weight: 800;
            color: #581c87; /* UNITEPC Purple */
            background-color: #faf5ff;
            border-left: 4px solid #581c87;
            padding: 5px 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        /* Discharge Highlight Box */
        .discharge-summary-box {
            background-color: #fdf2f8;
            border: 1px solid #fbcfe8;
            border-left: 4px solid #db2777;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 22px;
            page-break-inside: avoid;
        }
        .discharge-summary-box.alta-medica {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #16a34a;
        }
        .discharge-summary-box.alta-solicitada {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #d97706;
        }
        .discharge-summary-box.traslado {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #2563eb;
        }
        .discharge-summary-box.fuga {
            background-color: #faf5ff;
            border: 1px solid #e9d5ff;
            border-left: 4px solid #7c3aed;
        }
        .discharge-title {
            font-size: 10pt;
            font-weight: 800;
            color: #9d174d;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .discharge-summary-box.alta-medica .discharge-title { color: #15803d; }
        .discharge-summary-box.alta-solicitada .discharge-title { color: #b45309; }
        .discharge-summary-box.traslado .discharge-title { color: #1d4ed8; }
        .discharge-summary-box.fuga .discharge-title { color: #6d28d9; }

        .discharge-details {
            font-size: 8.5pt;
            color: #334155;
            line-height: 1.45;
        }

        /* Clinical Vitals / Treatment Tables */
        .clinical-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .clinical-table th {
            background-color: #581c87; /* UNITEPC Purple */
            color: #ffffff;
            font-size: 8pt;
            font-weight: 700;
            padding: 7px 10px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #581c87;
        }
        .clinical-table td {
            padding: 7px 10px;
            border: 1px solid #e2e8f0;
            font-size: 8pt;
            vertical-align: middle;
            color: #334155;
        }
        .clinical-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Custom badge */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5pt;
            font-weight: 800;
            text-transform: uppercase;
        }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-info { background-color: #dbeafe; color: #1e40af; }

        /* Timeline and Logs */
        .timeline-item {
            margin-bottom: 8px;
            padding: 8px 12px;
            background-color: #f8fafc;
            border-left: 3px solid #64748b;
            border-radius: 0 6px 6px 0;
            page-break-inside: avoid;
        }
        .timeline-item.medica {
            border-left-color: #581c87; /* UNITEPC Purple */
            background-color: #faf5ff;
        }
        .timeline-header {
            font-size: 8pt;
            font-weight: 700;
            color: #475569;
            margin-bottom: 3px;
        }
        .timeline-author {
            color: #581c87;
            font-weight: 600;
        }
        .timeline-date {
            font-family: monospace;
            color: #64748b;
            float: right;
        }
        .timeline-content {
            font-size: 8pt;
            color: #0f172a;
            white-space: pre-wrap;
            line-height: 1.4;
        }

        .footer {
            position: fixed;
            bottom: -35px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7.5pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <!-- FIXED FOOTER -->
    <div class="footer">
        Este informe de epicrisis clínica constituye un documento médico-legal. Generado automáticamente a través de la plataforma de salud SSTEPI.
        <br />
        ID Internación: {{ $internacion->id }} &bull; Paciente CI: {{ $internacion->paciente?->ci ?? 'N/A' }} &bull; UNITEPC SSTEPI &copy; 2026
    </div>

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td class="header-logo-cell">
                @if(isset($logoBase64) && $logoBase64)
                    <!-- Incrementamos el tamaño del logotipo y añadimos márgenes -->
                    <img src="{{ $logoBase64 }}" alt="Logo SSTEPI" style="height: 70px; margin-bottom: 6px; object-fit: contain;">
                @else
                    <span class="header-logo-text">UNITEPC</span>
                @endif
                <div class="header-subtitle">Sistema Clínico e Historial Clínico de Internación (SSTEPI)</div>
            </td>
            <td class="header-title-cell">
                <h1 class="header-title">Epicrisis y Resumen de Alta</h1>
                <div class="header-subtitle" style="font-weight: bold; color: #475569;">
                    Establecimiento: {{ $internacion->medico?->hospital?->nombre ?? 'Clínica Universitaria UNITEPC' }}
                </div>
                <div class="header-subtitle">Generado el {{ $fechaGeneracion }}</div>
            </td>
        </tr>
    </table>

    <!-- ESTADO DEL ALTA MÉDICA (Green/Orange/Pink Alert Box) -->
    @php
        $tipoAlta = $internacion->tipo_alta;
        if (!$internacion->fecha_alta) {
            $tipoAlta = 'Hospitalización en Curso (Activo)';
            $styleClass = 'traslado';
        } else {
            $tipoAlta = $tipoAlta ?? 'Alta Médica';
            $styleClass = 'alta-medica';
            if (strpos(strtolower($tipoAlta), 'solicitada') !== false || strpos(strtolower($tipoAlta), 'voluntario') !== false) {
                $styleClass = 'alta-solicitada';
            } elseif (strpos(strtolower($tipoAlta), 'traslado') !== false || strpos(strtolower($tipoAlta), 'deriv') !== false) {
                $styleClass = 'traslado';
            } elseif (strpos(strtolower($tipoAlta), 'fallecimiento') !== false || strpos(strtolower($tipoAlta), 'defunc') !== false) {
                $styleClass = 'alta-fallecimiento';
            } elseif (strpos(strtolower($tipoAlta), 'fuga') !== false || strpos(strtolower($tipoAlta), 'abandono') !== false) {
                $styleClass = 'fuga';
            }
        }
    @endphp
    
    <div class="discharge-summary-box {{ $styleClass }}">
        <div class="discharge-title">
            REGISTRO OFICIAL DE EGRESO HOSPITALARIO: {{ $tipoAlta }}
        </div>
        <div class="discharge-details">
            @if($internacion->fecha_alta)
                <strong>Fecha y Hora de Alta:</strong> 
                {{ \Carbon\Carbon::parse($internacion->fecha_alta)->format('d/m/Y H:i:s') }}
                <br />
                <strong>Detalles Clínicos / Administrativos de Egreso:</strong> 
                {{ $internacion->observaciones_alta ?? 'Egresado sin observaciones particulares de alta.' }}
            @else
                <strong>Estado de Estadía:</strong> El paciente se encuentra actualmente en curso de hospitalización clínica. No se registran observaciones de egreso clínico.
            @endif
        </div>
    </div>

    <!-- BLOQUES DE IDENTIFICACIÓN PARALELOS (Purple vs Turquoise - Sin Divisores Crudos '|' y Sin Emojis rotos '??') -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 22px;">
        <tr>
            <!-- LEFT CARD: DEMOGRAPHICS (Purple theme) -->
            <td style="width: 49%; vertical-align: top; background-color: #faf5ff; border: 1px solid #e9d5ff; border-left: 5px solid #581c87; border-radius: 6px; padding: 12px;">
                <h3 style="color: #581c87; font-size: 9pt; font-weight: 800; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                    PACIENTE - INFORMACIÓN DEMOGRÁFICA
                </h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #6b21a8; width: 35%;">Nombre:</td>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: bold; color: #1e293b;">{{ $internacion->paciente?->nombre ?? 'N/A' }} {{ $internacion->paciente?->apellidos ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #6b21a8;">Cédula (CI):</td>
                        <td style="padding: 4px 0; font-size: 8pt; font-family: monospace; color: #1e293b;">{{ $internacion->paciente?->ci ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #6b21a8;">F. Nacimiento:</td>
                        <td style="padding: 4px 0; font-size: 8pt; color: #1e293b;">
                            @if($internacion->paciente?->fecha_nacimiento)
                                {{ \Carbon\Carbon::parse($internacion->paciente->fecha_nacimiento)->format('d/m/Y') }} 
                                ({{ \Carbon\Carbon::parse($internacion->paciente->fecha_nacimiento)->age }} años)
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #6b21a8;">Género:</td>
                        <td style="padding: 4px 0; font-size: 8pt; color: #1e293b;">
                            @php $sexo = $internacion->paciente?->sexo ?? $internacion->paciente?->genero ?? 'N/A'; @endphp
                            {{ $sexo === 'M' ? 'Masculino' : ($sexo === 'F' ? 'Femenino' : $sexo) }}
                        </td>
                    </tr>
                </table>
            </td>
            <!-- SPACING COLUMN -->
            <td style="width: 2%;"></td>
            <!-- RIGHT CARD: STAY & ADMISSION (Turquoise theme) -->
            <td style="width: 49%; vertical-align: top; background-color: #f0fdfa; border: 1px solid #ccfbf1; border-left: 5px solid #0d9488; border-radius: 6px; padding: 12px;">
                <h3 style="color: #0d9488; font-size: 9pt; font-weight: 800; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                    ESTADÍA E INGRESO CLÍNICO
                </h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #0f766e; width: 35%;">F. Ingreso:</td>
                        <td style="padding: 4px 0; font-size: 8pt; font-family: monospace; color: #1e293b;">{{ \Carbon\Carbon::parse($internacion->fecha_ingreso)->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #0f766e;">F. Egreso:</td>
                        <td style="padding: 4px 0; font-size: 8pt; font-family: monospace; color: #1e293b;">{{ $internacion->fecha_alta ? \Carbon\Carbon::parse($internacion->fecha_alta)->format('d/m/Y H:i') : 'Hospitalización en Curso' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #0f766e;">Médico:</td>
                        <td style="padding: 4px 0; font-size: 8pt; color: #1e293b;">Dr. {{ $internacion->medico?->nombre ?? 'Carlos' }} {{ $internacion->medico?->apellidos ?? 'Vegas' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #0f766e;">Ubicación:</td>
                        <td style="padding: 4px 0; font-size: 8pt; color: #1e293b;">
                            {{ $ocupacion?->cama?->sala?->nombre ?? 'Sala General' }} / Cama {{ $ocupacion?->cama?->nombre ?? 'S/N' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- DIAGNÓSTICO & MOTIVO -->
    <div class="section">
        <div class="section-title">Diagnóstico de Ingreso</div>
        <table style="width: 100%; border-collapse: collapse; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px;">
            <tr>
                <td style="padding: 6px 10px; font-size: 8pt; font-weight: 700; color: #475569; width: 22%;">Motivo de Internación:</td>
                <td style="padding: 6px 10px; font-size: 8pt; color: #1e293b; width: 78%;">{{ $internacion->motivo ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 10px; font-size: 8pt; font-weight: 700; color: #475569; border-top: 1px solid #e2e8f0;">Diagnóstico Principal:</td>
                <td style="padding: 6px 10px; font-size: 8pt; font-weight: bold; color: #1e293b; border-top: 1px solid #e2e8f0;">{{ $internacion->diagnostico ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- ANTROPOMETRÍA -->
    <div class="section">
        <div class="section-title">Evaluación Antropométrica</div>
        @if($internacion->antropometria)
            <table class="clinical-table">
                <thead>
                    <tr>
                        <th style="width: 33.3%;">Peso Registrado</th>
                        <th style="width: 33.3%;">Altura Registrada</th>
                        <th style="width: 33.3%;">Índice de Masa Corporal (IMC)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight: bold; font-size: 8.5pt;">{{ $internacion->antropometria->peso }} kg</td>
                        <td style="font-weight: bold; font-size: 8.5pt;">{{ $internacion->antropometria->altura }} cm</td>
                        <td style="font-weight: bold; font-size: 8.5pt; color: #0d9488;">
                            {{ $internacion->antropometria->imc }}
                            @if($internacion->antropometria->imc)
                                @php
                                    $imc = $internacion->antropometria->imc;
                                    $desc = 'Normal';
                                    $c = 'badge-success';
                                    if ($imc < 18.5) { $desc = 'Bajo Peso'; $c = 'badge-warning'; }
                                    elseif ($imc >= 25 && $imc < 30) { $desc = 'Sobrepeso'; $c = 'badge-warning'; }
                                    elseif ($imc >= 30) { $desc = 'Obesidad'; $c = 'badge-danger'; }
                                @endphp
                                <span class="badge {{ $c }}">{{ $desc }}</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            @if($internacion->antropometria->observaciones)
                <p style="font-size: 7.5pt; color: #64748b; font-style: italic; margin-top: 4px;">
                    * Observación Antropométrica: "{{ $internacion->antropometria->observaciones }}"
                </p>
            @endif
        @else
            <p style="padding: 6px 10px; background-color: #f8fafc; font-size: 8pt; color: #64748b; font-style: italic; border: 1px solid #e2e8f0; border-radius: 4px;">
                No se registraron datos antropométricos específicos para este expediente.
            </p>
        @endif
    </div>

    <!-- HISTORIAL COMPLETO DE SIGNOS VITALES -->
    <div class="section">
        <div class="section-title">Historial Clínico Cronológico de Signos Vitales</div>
        @if(count($historialControles) > 0)
            <table class="clinical-table">
                <thead>
                    <tr>
                        <th style="width: 18%;">Fecha y Hora</th>
                        <th style="width: 16%;">Presión Arterial</th>
                        <th style="width: 12%;">Frec. Cardíaca</th>
                        <th style="width: 11%;">Temp. (°C)</th>
                        <th style="width: 11%;">Sat. O₂</th>
                        <th style="width: 12%;">Frec. Resp.</th>
                        <th style="width: 20%;">Registrado Por</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($historialControles as $control)
                        <tr>
                            <td style="font-family: monospace; font-weight: bold;">
                                {{ \Carbon\Carbon::parse($control->fecha_control)->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                @php 
                                    $pa = $control->valores->first(function($val) {
                                        $nombre = strtolower($val->signo->nombre ?? '');
                                        return strpos($nombre, 'presion') !== false || strpos($nombre, 'arterial') !== false;
                                    });
                                @endphp
                                @if($pa)
                                    {{ $pa->medida }}{{ $pa->medida_baja ? '/' . $pa->medida_baja : '' }} mmHg
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @php 
                                    $fc = $control->valores->first(function($val) {
                                        $nombre = strtolower($val->signo->nombre ?? '');
                                        return strpos($nombre, 'card') !== false || strpos($nombre, 'pulso') !== false;
                                    });
                                @endphp
                                {{ $fc ? $fc->medida . ' lpm' : '—' }}
                            </td>
                            <td>
                                @php 
                                    $temp = $control->valores->first(function($val) {
                                        $nombre = strtolower($val->signo->nombre ?? '');
                                        return strpos($nombre, 'temp') !== false;
                                    });
                                @endphp
                                {{ $temp ? $temp->medida . ' °C' : '—' }}
                            </td>
                            <td>
                                @php 
                                    $sat = $control->valores->first(function($val) {
                                        $nombre = strtolower($val->signo->nombre ?? '');
                                        return strpos($nombre, 'satur') !== false || strpos($nombre, 'o2') !== false;
                                    });
                                @endphp
                                {{ $sat ? $sat->medida . '%' : '—' }}
                            </td>
                            <td>
                                @php 
                                    $fr = $control->valores->first(function($val) {
                                        $nombre = strtolower($val->signo->nombre ?? '');
                                        return strpos($nombre, 'resp') !== false;
                                    });
                                @endphp
                                {{ $fr ? $fr->medida . ' rpm' : '—' }}
                            </td>
                            <td>
                                {{ $control->user ? $control->user->nombre . ' ' . substr($control->user->apellidos, 0, 1) . '.' : 'Turno Clínico' }}
                                @if($control->observaciones)
                                    <div style="font-size: 7pt; color: #64748b; font-style: italic;">
                                        Nota: "{{ $control->observaciones }}"
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="padding: 6px 10px; background-color: #f8fafc; font-size: 8pt; color: #64748b; font-style: italic; border: 1px solid #e2e8f0; border-radius: 4px;">
                No se registraron signos vitales durante el curso clínico de internación.
            </p>
        @endif
    </div>

    <!-- TRATAMIENTO FARMACOLÓGICO & ALERTA DE ADHERENCIA -->
    <div class="section">
        <div class="section-title">Tratamientos Farmacológicos & Adherencia</div>
        @if(count($resumenMedicamentos) > 0)
            <table class="clinical-table">
                <thead>
                    <tr>
                        <th style="width: 32%;">Medicamento</th>
                        <th style="width: 14%;">Dosis</th>
                        <th style="width: 14%;">Vía</th>
                        <th style="width: 20%;">Frecuencia / Duración</th>
                        <th style="width: 20%;">Tasa de Adherencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumenMedicamentos as $med)
                        @php
                            $ad = $med['adherencia'];
                            $isLowAdherencia = $ad < 60;
                            $rowStyle = $isLowAdherencia ? 'background-color: #fffbeb;' : '';
                            $bc = 'badge-success';
                            if ($ad < 50) $bc = 'badge-danger';
                            elseif ($ad < 80) $bc = 'badge-warning';
                        @endphp
                        <tr style="{{ $rowStyle }}">
                            <td style="font-weight: bold; color: #1e293b;">
                                {{ $med['medicamento'] }}
                                @if($isLowAdherencia)
                                    <span style="display: block; font-size: 6.5pt; color: #b45309; font-weight: bold; margin-top: 1px;">Alerta de Baja Adherencia</span>
                                @endif
                            </td>
                            <td style="font-weight: 500;">{{ $med['dosis'] }}</td>
                            <td>{{ $med['via'] }}</td>
                            <td>{{ $med['frecuencia'] }} <br /><span style="font-size: 7pt; color: #64748b;">durante {{ $med['duracion'] }}</span></td>
                            <td>
                                <span class="badge {{ $bc }}">{{ $ad }}%</span>
                                <div style="font-size: 7pt; color: #64748b; margin-top: 1px;">
                                    Dosis: <strong>{{ $med['dosis_administradas'] }}</strong> de {{ $med['total_dosis'] }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="padding: 6px 10px; background-color: #f8fafc; font-size: 8pt; color: #64748b; font-style: italic; border: 1px solid #e2e8f0; border-radius: 4px;">
                No se prescribieron tratamientos farmacológicos específicos en esta internación.
            </p>
        @endif
    </div>

    <!-- PLAN ALIMENTICIO -->
    <div class="section">
        <div class="section-title">Nutrición y Regímenes Dietéticos</div>
        @if(count($resumenAlimentacion) > 0)
            <table class="clinical-table">
                <thead>
                    <tr>
                        <th style="width: 30%;">Tipo de Dieta Prescrita</th>
                        <th style="width: 15%;">Vía</th>
                        <th style="width: 25%;">Período de Aplicación</th>
                        <th style="width: 15%;">Consumo Promedio</th>
                        <th style="width: 15%;">Estado Final</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumenAlimentacion as $alim)
                        <tr>
                            <td style="font-weight: bold; color: #1e293b;">{{ $alim['tipo_dieta'] }}</td>
                            <td>{{ $alim['via'] }}</td>
                            <td style="font-family: monospace;">{{ $alim['fecha_inicio'] }} — {{ $alim['fecha_fin'] }}</td>
                            <td style="font-weight: bold; color: #0d9488;">{{ $alim['consumo_promedio'] }}</td>
                            <td>
                                @php
                                    $est = strtolower($alim['estado']);
                                    $bad = 'badge-info';
                                    if ($est === 'activa') $bad = 'badge-success';
                                    elseif ($est === 'suspendida') $bad = 'badge-danger';
                                @endphp
                                <span class="badge {{ $bad }}">{{ $alim['estado'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="padding: 6px 10px; background-color: #f8fafc; font-size: 8pt; color: #64748b; font-style: italic; border: 1px solid #e2e8f0; border-radius: 4px;">
                No se registraron regímenes de nutrición en esta internación.
            </p>
        @endif
    </div>

    <!-- PLANES DE CUIDADOS Y APLICACIONES (NURSING LOGS - EXECUTIVE CONSOLIDATION) -->
    <div class="section" style="page-break-before: always;">
        <div class="section-title">Resumen Ejecutivo de Cuidados de Enfermería</div>
        @if(count($resumenCuidados) > 0)
            <table class="clinical-table">
                <thead>
                    <tr>
                        <th style="width: 32%;">Directriz de Enfermería</th>
                        <th style="width: 43%;">Indicaciones Clínicas / Frecuencia</th>
                        <th style="width: 25%; text-align: right;">Cumplimiento Operativo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumenCuidados as $cuidado)
                        @php
                            $cum = $cuidado['cumplimiento'];
                            $badgeColor = 'badge-success';
                            if ($cum < 50) $badgeColor = 'badge-danger';
                            elseif ($cum < 80) $badgeColor = 'badge-warning';
                        @endphp
                        <tr>
                            <td style="font-weight: bold; color: #581c87; font-size: 8pt;">
                                {{ $cuidado['directriz'] }}
                            </td>
                            <td>
                                <ul style="margin: 0; padding-left: 12px; font-size: 7.5pt; color: #475569;">
                                    @foreach($cuidado['descripciones'] as $desc)
                                        <li>{{ $desc }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td style="text-align: right; vertical-align: middle;">
                                <span class="badge {{ $badgeColor }}" style="font-size: 7.5pt; padding: 2px 5px;">
                                    {{ $cum }}% de cumplimiento
                                </span>
                                <div style="font-size: 7pt; color: #64748b; margin-top: 2px;">
                                    {{ $cuidado['total_aplicadas'] }} de {{ $cuidado['total_esperadas'] }} ejecuciones
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="padding: 6px 10px; background-color: #f8fafc; font-size: 8pt; color: #64748b; font-style: italic; border: 1px solid #e2e8f0; border-radius: 4px;">
                No se registraron directrices de enfermería en esta internación.
            </p>
        @endif
    </div>

    <!-- NOTAS DE EVOLUCIÓN CLÍNICA (MÉDICAS) -->
    <div class="section">
        <div class="section-title">Bitácora Médica: Notas de Evolución</div>
        @if($evolucionClinica->count() > 0)
            <div style="margin-top: 4px;">
                @foreach($evolucionClinica as $control)
                    <div class="timeline-item medica">
                        <div class="timeline-header">
                            <span class="timeline-author">Dr(a). {{ $control->user?->nombre ?? 'Carlos' }} {{ $control->user?->apellidos ?? 'Vegas' }}</span>
                            <span class="timeline-date">{{ \Carbon\Carbon::parse($control->fecha_control)->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="timeline-content">{{ $control->observaciones ?? 'Sin descripción añadida.' }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <p style="padding: 6px 10px; background-color: #f8fafc; font-size: 8pt; color: #64748b; font-style: italic; border: 1px solid #e2e8f0; border-radius: 4px;">
                No se registraron notas de evolución médica formales en el expediente de internación.
            </p>
        @endif
    </div>

    <!-- SECCIÓN DE FIRMAS Y TIMBRES -->
    <div class="section" style="margin-top: 50px; page-break-inside: avoid;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 45%; text-align: center; vertical-align: bottom; padding: 10px;">
                    <div style="width: 80%; margin: 0 auto 5px auto; border-top: 1px solid #94a3b8;"></div>
                    <div style="font-size: 8.5pt; font-weight: 700; color: #475569;">Dr. Carlos Vegas</div>
                    <div style="font-size: 7.5pt; color: #64748b;">Médico Tratante / Especialista Responsable</div>
                    <div style="font-size: 7pt; color: #94a3b8; font-style: italic;">Firma y Sello Profesional</div>
                </td>
                <td style="width: 10%;"></td>
                <td style="width: 45%; text-align: center; vertical-align: bottom; padding: 10px;">
                    <div style="width: 80%; margin: 0 auto 5px auto; border-top: 1px solid #94a3b8;"></div>
                    <div style="font-size: 8.5pt; font-weight: 700; color: #475569;">Supervisión de Enfermería</div>
                    <div style="font-size: 7.5pt; color: #64748b;">Validación y Control de Turno Clínico</div>
                    <div style="font-size: 7pt; color: #94a3b8; font-style: italic;">UNITEPC - Estación de Enfermería</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
