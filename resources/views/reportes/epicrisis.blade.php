<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Epicrisis Clínica - {{ $internacion->paciente?->nombre ?? 'Paciente' }} {{ $internacion->paciente?->apellidos ?? '' }}</title>
    <style>
        /* Diseño estrictamente minimalista, corporativo y monocromático */
        @page {
            margin: 55pt 50pt 60pt 50pt;
        }
        
        body, h1, h2, h3, p, table, tr, td, th, ul, li { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 8.5pt; 
            line-height: 1.5; 
            color: #0f172a; /* Carbón oscuro puro */
            background-color: #ffffff;
        }
        
        /* Header minimalista sin fondo */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            border-bottom: 1.5px solid #0f172a;
            padding-bottom: 12px;
        }
        .header-logo-cell {
            width: 50%;
            vertical-align: top;
            padding-bottom: 8px;
        }
        .header-title-cell {
            width: 50%;
            text-align: right;
            vertical-align: top;
            padding-bottom: 8px;
        }
        .header-logo-text {
            font-size: 20pt;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }
        .header-subtitle {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 3px;
            line-height: 1.3;
        }
        .header-title {
            font-size: 14pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Sección y Títulos sobrios */
        .section {
            margin-bottom: 22px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 9pt;
            font-weight: 800;
            color: #0f172a;
            border-bottom: 1px solid #0f172a;
            padding-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        /* Caja de Alta estrictamente formal, en B&W */
        .discharge-summary-box {
            background-color: #ffffff;
            border: 1px solid #0f172a;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 22px;
            page-break-inside: avoid;
        }
        .discharge-title {
            font-size: 9.5pt;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .discharge-details {
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.45;
        }

        /* Tablas Clínicas ultra-limpias, B&W */
        .clinical-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .clinical-table th {
            background-color: #ffffff;
            color: #0f172a;
            font-size: 8pt;
            font-weight: 700;
            padding: 6px 8px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-top: 1.5px solid #0f172a;
            border-bottom: 1.5px solid #0f172a;
        }
        .clinical-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 8pt;
            vertical-align: top;
            color: #1e293b;
        }
        .clinical-table tr.low-adherencia-row td {
            background-color: #f8fafc;
            border-left: 2.5px solid #0f172a;
        }

        /* Bloque de Notas */
        .timeline-item {
            margin-bottom: 10px;
            padding: 8px 12px;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #0f172a;
            border-radius: 4px;
            page-break-inside: avoid;
        }
        .timeline-header {
            font-size: 8pt;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .timeline-author {
            color: #0f172a;
            font-weight: 700;
        }
        .timeline-date {
            font-family: monospace;
            color: #475569;
            float: right;
            font-weight: normal;
        }
        .timeline-content {
            font-size: 8pt;
            color: #334155;
            white-space: pre-wrap;
            line-height: 1.4;
        }

        /* Footer minimalista fijo */
        .footer {
            position: fixed;
            bottom: -35px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7.5pt;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <!-- FIXED FOOTER -->
    <div class="footer">
        DOCUMENTO CLÍNICO DE CARÁCTER LEGAL &bull; HISTORIAL EXPEDIENTE SSTEPI &bull; VALIDEZ INSTITUCIONAL UNITEPC
        <br />
        ID Internación: {{ $internacion->id }} &bull; Paciente CI: {{ $internacion->paciente?->ci ?? 'N/A' }} &bull; SSTEPI &copy; 2026
    </div>

    <!-- HEADER MINIMALISTA -->
    <table class="header-table">
        <tr>
            <td class="header-logo-cell">
                @if(isset($logoBase64) && $logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo SSTEPI" style="height: 60px; margin-bottom: 4px; object-fit: contain; filter: grayscale(100%);">
                @else
                    <span class="header-logo-text">UNITEPC</span>
                @endif
                <div class="header-subtitle">Sistema Clínico e Historial Clínico de Internación (SSTEPI)</div>
            </td>
            <td class="header-title-cell">
                <h1 class="header-title">Epicrisis y Resumen de Alta</h1>
                <div class="header-subtitle" style="font-weight: bold; color: #0f172a;">
                    Establecimiento: {{ $internacion->medico?->hospital?->nombre ?? 'Clínica Universitaria UNITEPC' }}
                </div>
                <div class="header-subtitle">Fecha de Emisión: {{ $fechaGeneracion }}</div>
            </td>
        </tr>
    </table>

    <!-- ESTADO DEL ALTA MÉDICA (Monocromático, sobrio) -->
    @php
        $tipoAlta = $internacion->tipo_alta;
        if (!$internacion->fecha_alta) {
            $tipoAlta = 'Hospitalización en Curso (Activo)';
        } else {
            $tipoAlta = $tipoAlta ?? 'Alta Médica';
        }
    @endphp
    
    <div class="discharge-summary-box">
        <div class="discharge-title">
            DOCUMENTO CLÍNICO DE EGRESO HOSPITALARIO: {{ $tipoAlta }}
        </div>
        <div class="discharge-details">
            @if($internacion->fecha_alta)
                <strong>Fecha y Hora Oficial de Alta:</strong> 
                {{ \Carbon\Carbon::parse($internacion->fecha_alta)->format('d/m/Y H:i:s') }}
                <br />
                <strong>Observaciones Médicas de Alta:</strong> 
                {{ $internacion->observaciones_alta ?? 'Egresado sin observaciones particulares de alta.' }}
            @else
                <strong>Estado Actual de Estadía:</strong> El paciente se encuentra bajo hospitalización activa y control clínico continuo. No se registra egreso clínico formal a la fecha de emisión.
            @endif
        </div>
    </div>

    <!-- EVITAR DUPLICADO "DR. DR." -->
    @php
        $medicoNombre = $internacion->medico ? ($internacion->medico->nombre . ' ' . $internacion->medico->apellidos) : 'Carlos Vegas';
        $medicoNombreClean = preg_replace('/^(Dr\.|Dra\.|Dr|Dra)\s+/i', '', $medicoNombre);
    @endphp

    <!-- BLOQUES DE IDENTIFICACIÓN PARALELOS (Tablas limpias sin fondo, sin '|', con bordes mínimos) -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 22px;">
        <tr>
            <!-- COLUMNA IZQUIERDA: DATOS PACIENTE -->
            <td style="width: 48%; vertical-align: top; border: 1px solid #cbd5e1; border-top: 3px solid #0f172a; padding: 12px; border-radius: 4px;">
                <h3 style="color: #0f172a; font-size: 8.5pt; font-weight: 800; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Información Demográfica del Paciente
                </h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #475569; width: 35%;">Nombre Completo:</td>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: bold; color: #0f172a;">{{ $internacion->paciente?->nombre ?? 'N/A' }} {{ $internacion->paciente?->apellidos ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #475569;">Documento (CI):</td>
                        <td style="padding: 4px 0; font-size: 8pt; font-family: monospace; color: #0f172a;">{{ $internacion->paciente?->ci ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #475569;">F. Nacimiento:</td>
                        <td style="padding: 4px 0; font-size: 8pt; color: #0f172a;">
                            @if($internacion->paciente?->fecha_nacimiento)
                                {{ \Carbon\Carbon::parse($internacion->paciente->fecha_nacimiento)->format('d/m/Y') }} 
                                ({{ \Carbon\Carbon::parse($internacion->paciente->fecha_nacimiento)->age }} años)
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #475569;">Sexo / Género:</td>
                        <td style="padding: 4px 0; font-size: 8pt; color: #0f172a;">
                            @php $sexo = $internacion->paciente?->sexo ?? $internacion->paciente?->genero ?? 'N/A'; @endphp
                            {{ $sexo === 'M' ? 'Masculino' : ($sexo === 'F' ? 'Femenino' : $sexo) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #475569;">Teléfono:</td>
                        <td style="padding: 4px 0; font-size: 8pt; color: #0f172a;">{{ $internacion->paciente?->telefono ?? 'No registrado' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #475569; vertical-align: top;">Dirección:</td>
                        <td style="padding: 4px 0; font-size: 8pt; color: #0f172a;">{{ $internacion->paciente?->direccion ?? 'No registrada' }}</td>
                    </tr>
                </table>
            </td>
            <!-- ESPACIO -->
            <td style="width: 4%;"></td>
            <!-- COLUMNA DERECHA: DATOS INTERNACIÓN -->
            <td style="width: 48%; vertical-align: top; border: 1px solid #cbd5e1; border-top: 3px solid #0f172a; padding: 12px; border-radius: 4px;">
                <h3 style="color: #0f172a; font-size: 8.5pt; font-weight: 800; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Resumen de Estadía Hospitalaria
                </h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #475569; width: 35%;">Fecha Ingreso:</td>
                        <td style="padding: 4px 0; font-size: 8pt; font-family: monospace; color: #0f172a;">{{ \Carbon\Carbon::parse($internacion->fecha_ingreso)->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #475569;">Fecha Egreso:</td>
                        <td style="padding: 4px 0; font-size: 8pt; font-family: monospace; color: #0f172a;">{{ $internacion->fecha_alta ? \Carbon\Carbon::parse($internacion->fecha_alta)->format('d/m/Y H:i') : 'N/A (Activo)' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #475569;">Estancia Total:</td>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: bold; color: #0f172a;">{{ number_format($diasEstancia, 1) }} días transcurridos</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #475569;">Médico Tratante:</td>
                        <td style="padding: 4px 0; font-size: 8pt; color: #0f172a;">Dr. {{ $medicoNombreClean }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #475569;">Ubicación:</td>
                        <td style="padding: 4px 0; font-size: 8pt; color: #0f172a;">
                            {{ $ocupacion?->cama?->sala?->nombre ?? 'N/A' }} &mdash; Cama {{ $ocupacion?->cama?->nombre ?? $ocupacion?->cama?->codigo ?? 'S/C' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 8pt; font-weight: 700; color: #475569;">Especialidad:</td>
                        <td style="padding: 4px 0; font-size: 8pt; color: #0f172a;">{{ $ocupacion?->cama?->sala?->especialidad?->nombre ?? 'Medicina General' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- METER TODA LA INFORMACIÓN POSIBLE: CONTACTO DE EMERGENCIA -->
    <div class="section">
        <div class="section-title">Contacto de Emergencia / Familiar Responsable</div>
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px;">
            <tr>
                <td style="padding: 5px 8px; font-size: 8pt; font-weight: 700; color: #475569; width: 25%;">Familiar de Contacto:</td>
                <td style="padding: 5px 8px; font-size: 8pt; color: #0f172a; width: 25%;">{{ $internacion->paciente?->nombre_referencia ?? 'No' }} {{ $internacion->paciente?->apellidos_referencia ?? 'registrado' }}</td>
                <td style="padding: 5px 8px; font-size: 8pt; font-weight: 700; color: #475569; width: 25%;">Teléfono de Contacto:</td>
                <td style="padding: 5px 8px; font-size: 8pt; color: #0f172a; width: 25%;">{{ $internacion->paciente?->celular_referencia ?? 'No registrado' }}</td>
            </tr>
        </table>
    </div>

    <!-- DIAGNÓSTICO & MOTIVO -->
    <div class="section">
        <div class="section-title">Diagnóstico y Justificación Médica de Ingreso</div>
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e1; border-radius: 4px; padding: 10px;">
            <tr>
                <td style="padding: 6px 10px; font-size: 8pt; font-weight: 700; color: #475569; width: 20%; vertical-align: top;">Motivo Clínico:</td>
                <td style="padding: 6px 10px; font-size: 8pt; color: #0f172a; width: 80%;">{{ $internacion->motivo ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 10px; font-size: 8pt; font-weight: 700; color: #475569; border-top: 1px solid #e2e8f0; vertical-align: top;">Diagnóstico Principal:</td>
                <td style="padding: 6px 10px; font-size: 8pt; font-weight: bold; color: #0f172a; border-top: 1px solid #e2e8f0;">{{ $internacion->diagnostico ?? 'N/A' }}</td>
            </tr>
            @if($internacion->observaciones)
                <tr>
                    <td style="padding: 6px 10px; font-size: 8pt; font-weight: 700; color: #475569; border-top: 1px solid #e2e8f0; vertical-align: top;">Notas de Admisión:</td>
                    <td style="padding: 6px 10px; font-size: 8pt; color: #334155; border-top: 1px solid #e2e8f0; font-style: italic;">{{ $internacion->observaciones }}</td>
                </tr>
            @endif
        </table>
    </div>

    <!-- ANTROPOMETRÍA -->
    <div class="section">
        <div class="section-title">Evaluación de Biometría y Antropometría</div>
        @if($internacion->antropometria)
            <table class="clinical-table">
                <thead>
                    <tr>
                        <th style="width: 33.3%;">Peso Corporal</th>
                        <th style="width: 33.3%;">Talla / Altura</th>
                        <th style="width: 33.3%;">Índice de Masa Corporal (IMC)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight: bold;">{{ $internacion->antropometria->peso }} kg</td>
                        <td style="font-weight: bold;">{{ $internacion->antropometria->altura }} cm</td>
                        <td style="font-weight: bold;">
                            {{ $internacion->antropometria->imc }}
                            @if($internacion->antropometria->imc)
                                @php
                                    $imc = $internacion->antropometria->imc;
                                    $desc = 'Normal';
                                    if ($imc < 18.5) { $desc = 'Bajo Peso'; }
                                    elseif ($imc >= 25 && $imc < 30) { $desc = 'Sobrepeso'; }
                                    elseif ($imc >= 30) { $desc = 'Obesidad'; }
                                @endphp
                                &mdash; [{{ $desc }}]
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            @if($internacion->antropometria->observaciones)
                <p style="font-size: 7.5pt; color: #475569; font-style: italic; margin-top: 4px;">
                    Nota Antropométrica: "{{ $internacion->antropometria->observaciones }}"
                </p>
            @endif
        @else
            <p style="padding: 6px 10px; background-color: #ffffff; font-size: 8pt; color: #64748b; font-style: italic; border: 1px solid #cbd5e1; border-radius: 4px;">
                No se registraron datos antropométricos específicos para esta internación.
            </p>
        @endif
    </div>

    <!-- HISTORIAL COMPLETO DE SIGNOS VITALES -->
    <div class="section">
        <div class="section-title">Historial Cronológico de Control de Signos Vitales</div>
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
                                    <div style="font-size: 7pt; color: #475569; font-style: italic;">
                                        Nota: "{{ $control->observaciones }}"
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="padding: 6px 10px; background-color: #ffffff; font-size: 8pt; color: #64748b; font-style: italic; border: 1px solid #cbd5e1; border-radius: 4px;">
                No se registraron signos vitales durante el curso clínico de internación.
            </p>
        @endif
    </div>

    <!-- TRATAMIENTO FARMACOLÓGICO - MINIMALISTA, B&W CON ALERTA SOBRIA -->
    <div class="section">
        <div class="section-title">Tratamientos Farmacológicos e Historial de Adherencia</div>
        @if(count($resumenMedicamentos) > 0)
            <table class="clinical-table">
                <thead>
                    <tr>
                        <th style="width: 32%;">Medicamento</th>
                        <th style="width: 14%;">Dosis</th>
                        <th style="width: 14%;">Vía Adm.</th>
                        <th style="width: 20%;">Frecuencia / Duración</th>
                        <th style="width: 20%;">Adherencia al Tratamiento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumenMedicamentos as $med)
                        @php
                            $ad = $med['adherencia'];
                            $isLowAdherencia = $ad < 60;
                            $rowStyle = $isLowAdherencia ? 'background-color: #f8fafc;' : '';
                        @endphp
                        <tr style="{{ $rowStyle }}">
                            <td style="font-weight: bold;">
                                {{ $med['medicamento'] }}
                                @if($isLowAdherencia)
                                    <span style="display: block; font-size: 7pt; font-weight: bold; color: #0f172a; margin-top: 1px;">[ALERTA: BAJA ADHERENCIA]</span>
                                @endif
                            </td>
                            <td>{{ $med['dosis'] }}</td>
                            <td>{{ $med['via'] }}</td>
                            <td>{{ $med['frecuencia'] }} <br /><span style="font-size: 7pt; color: #475569;">durante {{ $med['duracion'] }}</span></td>
                            <td>
                                <strong>{{ $ad }}%</strong>
                                <div style="font-size: 7pt; color: #475569; margin-top: 1px;">
                                    {{ $med['dosis_administradas'] }} de {{ $med['total_dosis'] }} aplicadas
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="padding: 6px 10px; background-color: #ffffff; font-size: 8pt; color: #64748b; font-style: italic; border: 1px solid #cbd5e1; border-radius: 4px;">
                No se prescribieron tratamientos farmacológicos específicos en esta internación.
            </p>
        @endif
    </div>

    <!-- PLAN ALIMENTICIO -->
    <div class="section">
        <div class="section-title">Regímenes Dietéticos y Soporte Nutricional</div>
        @if(count($resumenAlimentacion) > 0)
            <table class="clinical-table">
                <thead>
                    <tr>
                        <th style="width: 32%;">Tipo de Dieta Prescrita</th>
                        <th style="width: 15%;">Vía</th>
                        <th style="width: 23%;">Período de Aplicación</th>
                        <th style="width: 15%;">Consumo Promedio</th>
                        <th style="width: 15%;">Estado Final</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumenAlimentacion as $alim)
                        <tr>
                            <td style="font-weight: bold;">{{ $alim['tipo_dieta'] }}</td>
                            <td>{{ $alim['via'] }}</td>
                            <td style="font-family: monospace;">{{ $alim['fecha_inicio'] }} &mdash; {{ $alim['fecha_fin'] }}</td>
                            <td><strong>{{ $alim['consumo_promedio'] }}</strong></td>
                            <td><strong>[{{ $alim['estado'] }}]</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="padding: 6px 10px; background-color: #ffffff; font-size: 8pt; color: #64748b; font-style: italic; border: 1px solid #cbd5e1; border-radius: 4px;">
                No se registraron regímenes de nutrición en esta internación.
            </p>
        @endif
    </div>

    <!-- PLANES DE CUIDADOS Y APLICACIONES (NURSING LOGS - EXECUTIVE CONSOLIDATION B&W) -->
    <div class="section" style="page-break-before: always;">
        <div class="section-title">Resumen Ejecutivo de Ejecución de Cuidados de Enfermería</div>
        @if(count($resumenCuidados) > 0)
            <table class="clinical-table">
                <thead>
                    <tr>
                        <th style="width: 30%;">Directriz de Enfermería</th>
                        <th style="width: 45%;">Detalles de la Indicación / Frecuencia</th>
                        <th style="width: 25%; text-align: right;">Cumplimiento Operativo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumenCuidados as $cuidado)
                        <tr>
                            <td style="font-weight: bold; color: #0f172a;">
                                {{ $cuidado['directriz'] }}
                            </td>
                            <td>
                                <ul style="margin: 0; padding-left: 12px; font-size: 7.5pt; color: #334155;">
                                    @foreach($cuidado['descripciones'] as $desc)
                                        <li>{{ $desc }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td style="text-align: right; vertical-align: middle;">
                                <strong>[{{ $cuidado['cumplimiento'] }}% de cumplimiento]</strong>
                                <div style="font-size: 7pt; color: #475569; margin-top: 2px;">
                                    {{ $cuidado['total_aplicadas'] }} de {{ $cuidado['total_esperadas'] }} ejecuciones
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="padding: 6px 10px; background-color: #ffffff; font-size: 8pt; color: #64748b; font-style: italic; border: 1px solid #cbd5e1; border-radius: 4px;">
                No se registraron directrices de enfermería en esta internación.
            </p>
        @endif
    </div>

    <!-- NOTAS DE EVOLUCIÓN CLÍNICA (MÉDICAS) -->
    <div class="section">
        <div class="section-title">Bitácora Médica de Evolución Clínica</div>
        @if($evolucionClinica->count() > 0)
            <div style="margin-top: 4px;">
                @foreach($evolucionClinica as $control)
                    <div class="timeline-item medica">
                        <div class="timeline-header">
                            <span class="timeline-author">Dr. {{ preg_replace('/^(Dr\.|Dra\.|Dr|Dra)\s+/i', '', ($control->user ? ($control->user->nombre . ' ' . $control->user->apellidos) : 'Carlos Vegas')) }}</span>
                            <span class="timeline-date">{{ \Carbon\Carbon::parse($control->fecha_control)->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="timeline-content">{{ $control->observaciones ?? 'Sin descripción añadida.' }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <p style="padding: 6px 10px; background-color: #ffffff; font-size: 8pt; color: #64748b; font-style: italic; border: 1px solid #cbd5e1; border-radius: 4px;">
                No se registraron notas de evolución médica formales en el expediente de internación.
            </p>
        @endif
    </div>

    <!-- SECCIÓN DE FIRMAS Y VALIDACIÓN CLÍNICO-LEGAL -->
    <div class="section" style="margin-top: 60px; page-break-inside: avoid;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 45%; text-align: center; vertical-align: bottom; padding: 10px;">
                    <div style="width: 80%; margin: 0 auto 5px auto; border-top: 1px solid #0f172a;"></div>
                    <div style="font-size: 8.5pt; font-weight: 700; color: #0f172a;">Dr. Carlos Vegas</div>
                    <div style="font-size: 7.5pt; color: #475569;">Médico Tratante / Especialista Responsable</div>
                    <div style="font-size: 7pt; color: #94a3b8; font-style: italic;">Firma y Sello Profesional</div>
                </td>
                <td style="width: 10%;"></td>
                <td style="width: 45%; text-align: center; vertical-align: bottom; padding: 10px;">
                    <div style="width: 80%; margin: 0 auto 5px auto; border-top: 1px solid #0f172a;"></div>
                    <div style="font-size: 8.5pt; font-weight: 700; color: #0f172a;">Supervisión de Enfermería</div>
                    <div style="font-size: 7.5pt; color: #475569;">Validación y Control de Turno Clínico</div>
                    <div style="font-size: 7pt; color: #94a3b8; font-style: italic;">Estación de Enfermería - UNITEPC</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
