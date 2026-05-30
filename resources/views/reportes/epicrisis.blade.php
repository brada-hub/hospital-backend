<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Epicrisis Clínica - {{ $internacion->paciente?->nombre ?? 'Paciente' }} {{ $internacion->paciente?->apellidos ?? '' }}</title>
    <style>
        @page {
            margin: 40px 40px 50px 40px;
        }
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 9pt; 
            line-height: 1.5; 
            color: #1e293b;
            background-color: #ffffff;
        }
        
        /* Header styling */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .header-logo-cell {
            width: 35%;
            vertical-align: middle;
        }
        .header-title-cell {
            width: 65%;
            text-align: right;
            vertical-align: middle;
        }
        .header-logo-text {
            font-size: 18pt;
            font-weight: 900;
            color: #0f766e;
            letter-spacing: -1px;
        }
        .header-subtitle {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 2px;
        }
        .header-title {
            font-size: 15pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Section styling */
        .section {
            margin-bottom: 22px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 9pt;
            font-weight: 800;
            color: #0f766e;
            background-color: #f0fdfa;
            border-left: 3px solid #0f766e;
            padding: 5px 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        /* Discharge Highlight Box */
        .discharge-summary-box {
            background-color: #fdf2f8;
            border: 1px solid #fbcfe8;
            border-left: 4px solid #db2777;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
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
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .discharge-summary-box.alta-medica .discharge-title { color: #15803d; }
        .discharge-summary-box.alta-solicitada .discharge-title { color: #b45309; }
        .discharge-summary-box.traslado .discharge-title { color: #1d4ed8; }
        .discharge-summary-box.fuga .discharge-title { color: #6d28d9; }

        .discharge-details {
            font-size: 8.5pt;
            color: #334155;
            line-height: 1.4;
        }

        /* Two-Column details table */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .details-table td {
            padding: 4px 8px;
            vertical-align: top;
            font-size: 8.5pt;
            border-bottom: 1px solid #f1f5f9;
        }
        .label-cell {
            font-weight: 700;
            color: #475569;
            width: 25%;
            background-color: #f8fafc;
        }
        .value-cell {
            color: #0f172a;
            width: 25%;
        }

        /* Clinical Vitals / Treatment Tables */
        .clinical-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .clinical-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 8pt;
            font-weight: 700;
            padding: 6px 10px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #1e293b;
        }
        .clinical-table td {
            padding: 6px 10px;
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
            border-left-color: #3b82f6;
            background-color: #f0f6ff;
        }
        .timeline-item.enfermeria {
            border-left-color: #0d9488;
            background-color: #f0fdfa;
        }
        .timeline-header {
            font-size: 8pt;
            font-weight: 700;
            color: #475569;
            margin-bottom: 3px;
        }
        .timeline-author {
            color: #0f766e;
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

        /* Signature block */
        .signature-section {
            margin-top: 40px;
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 10px;
        }
        .signature-line {
            width: 70%;
            margin: 0 auto 5px auto;
            border-top: 1px solid #94a3b8;
        }
        .signature-text {
            font-size: 7.5pt;
            color: #475569;
            font-weight: 600;
        }

        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td class="header-logo-cell">
                @if(isset($logoBase64) && $logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo SSTEPI" style="height: 52px; max-width: 160px; object-fit: contain;">
                @else
                    <span class="header-logo-text">SSTEPI</span>
                @endif
                <div class="header-subtitle">Sistema Clínico e Historial Clínico de Internación</div>
            </td>
            <td class="header-title-cell">
                <h1 class="header-title">Epicrisis y Resumen de Alta</h1>
                <div class="header-subtitle" style="font-weight: bold; color: #475569;">
                    Establecimiento: {{ $internacion->medico?->hospital?->nombre ?? 'Clínica General SSTEPI' }}
                </div>
                <div class="header-subtitle">Generado el {{ $fechaGeneracion }}</div>
            </td>
        </tr>
    </table>

    <!-- ESTADO DEL ALTA MÉDICA (Pink/Green Alert Box) -->
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
            ⚠️ Registro Oficial de Egreso Hospitalario: {{ $tipoAlta }}
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

    <!-- DATOS PERSONALES DEL PACIENTE -->
    <div class="section">
        <div class="section-title">Información Demográfica del Paciente</div>
        <table class="details-table">
            <tr>
                <td class="label-cell">Nombre Completo:</td>
                <td class="value-cell" style="font-weight: bold;">{{ $internacion->paciente?->nombre ?? 'N/A' }} {{ $internacion->paciente?->apellidos ?? '' }}</td>
                <td class="label-cell">Cédula de Identidad (CI):</td>
                <td class="value-cell" style="font-weight: bold; font-family: monospace;">{{ $internacion->paciente?->ci ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label-cell">Fecha de Nacimiento:</td>
                <td class="value-cell">
                    @if($internacion->paciente?->fecha_nacimiento)
                        {{ \Carbon\Carbon::parse($internacion->paciente->fecha_nacimiento)->format('d/m/Y') }} 
                        ({{ \Carbon\Carbon::parse($internacion->paciente->fecha_nacimiento)->age }} años)
                    @else
                        N/A
                    @endif
                </td>
                <td class="label-cell">Sexo / Género:</td>
                <td class="value-cell">
                    @php $sexo = $internacion->paciente?->sexo ?? $internacion->paciente?->genero ?? 'N/A'; @endphp
                    {{ $sexo === 'M' ? 'Masculino' : ($sexo === 'F' ? 'Femenino' : $sexo) }}
                </td>
            </tr>
        </table>
    </div>

    <!-- DATOS DE LA HOSPITALIZACIÓN -->
    <div class="section">
        <div class="section-title">Resumen de la Estadía Hospitalaria</div>
        <table class="details-table">
            <tr>
                <td class="label-cell">Fecha de Ingreso:</td>
                <td class="value-cell" style="font-family: monospace;">{{ \Carbon\Carbon::parse($internacion->fecha_ingreso)->format('d/m/Y H:i') }}</td>
                <td class="label-cell">Médico Tratante:</td>
                <td class="value-cell">Dr(a). {{ $internacion->medico?->nombre ?? 'N/A' }} {{ $internacion->medico?->apellidos ?? '' }}</td>
            </tr>
            <tr>
                <td class="label-cell">Fecha de Egreso:</td>
                <td class="value-cell" style="font-family: monospace;">{{ $internacion->fecha_alta ? \Carbon\Carbon::parse($internacion->fecha_alta)->format('d/m/Y H:i') : 'N/A' }}</td>
                <td class="label-cell">Especialidad:</td>
                <td class="value-cell">{{ $ocupacion?->cama?->sala?->especialidad?->nombre ?? 'Medicina General' }}</td>
            </tr>
            <tr>
                <td class="label-cell">Tiempo de Internación:</td>
                <td class="value-cell" style="font-weight: bold; color: #0f766e;">{{ number_format($diasEstancia, 1) }} días transcurridos</td>
                <td class="label-cell">Ubicación Cama:</td>
                <td class="value-cell">{{ $ocupacion?->cama?->sala?->nombre ?? 'N/A' }} — Cama {{ $ocupacion?->cama?->nombre ?? $ocupacion?->cama?->codigo ?? 'S/C' }}</td>
            </tr>
        </table>
    </div>

    <!-- DIAGNÓSTICO & MOTIVO -->
    <div class="section">
        <div class="section-title">Diagnóstico de Ingreso</div>
        <table class="details-table">
            <tr>
                <td class="label-cell" style="width: 20%;">Motivo de Internación:</td>
                <td class="value-cell" style="width: 80%;">{{ $internacion->motivo ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label-cell" style="width: 20%;">Diagnóstico Principal:</td>
                <td class="value-cell" style="width: 80%; font-weight: bold;">{{ $internacion->diagnostico ?? 'N/A' }}</td>
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
                        <td style="font-weight: bold; font-size: 9.5pt;">{{ $internacion->antropometria->peso }} kg</td>
                        <td style="font-weight: bold; font-size: 9.5pt;">{{ $internacion->antropometria->altura }} cm</td>
                        <td style="font-weight: bold; font-size: 9.5pt; color: #0f766e;">
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
                        <th style="width: 15%;">Presión Arterial</th>
                        <th style="width: 12%;">Frec. Cardíaca</th>
                        <th style="width: 12%;">Temp. (°C)</th>
                        <th style="width: 12%;">Sat. O₂</th>
                        <th style="width: 12%;">Frec. Resp.</th>
                        <th style="width: 19%;">Registrado Por</th>
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

    <!-- TRATAMIENTO FARMACOLÓGICO -->
    <div class="section">
        <div class="section-title">Tratamientos Farmacológicos & Adherencia</div>
        @if(count($resumenMedicamentos) > 0)
            <table class="clinical-table">
                <thead>
                    <tr>
                        <th style="width: 30%;">Medicamento</th>
                        <th style="width: 15%;">Dosis</th>
                        <th style="width: 15%;">Vía</th>
                        <th style="width: 20%;">Frecuencia / Duración</th>
                        <th style="width: 20%;">Tasa de Adherencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumenMedicamentos as $med)
                        <tr>
                            <td style="font-weight: bold; color: #0f172a;">{{ $med['medicamento'] }}</td>
                            <td>{{ $med['dosis'] }}</td>
                            <td>{{ $med['via'] }}</td>
                            <td>{{ $med['frecuencia'] }} <br /><span style="font-size: 7.5pt; color: #64748b;">durante {{ $med['duracion'] }}</span></td>
                            <td>
                                @php
                                    $ad = $med['adherencia'];
                                    $bc = 'badge-success';
                                    if ($ad < 50) $bc = 'badge-danger';
                                    elseif ($ad < 80) $bc = 'badge-warning';
                                @endphp
                                <span class="badge {{ $bc }}">{{ $ad }}%</span>
                                <div style="font-size: 7.2pt; color: #64748b; margin-top: 1px;">
                                    Administradas: <strong>{{ $med['dosis_administradas'] }}</strong> de {{ $med['total_dosis'] }}
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
                        <th style="width: 28%;">Tipo de Dieta Prescrita</th>
                        <th style="width: 15%;">Vía</th>
                        <th style="width: 25%;">Período de Aplicación</th>
                        <th style="width: 17%;">Consumo Promedio</th>
                        <th style="width: 15%;">Estado Final</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumenAlimentacion as $alim)
                        <tr>
                            <td style="font-weight: bold; color: #0f172a;">{{ $alim['tipo_dieta'] }}</td>
                            <td>{{ $alim['via'] }}</td>
                            <td style="font-family: monospace;">{{ $alim['fecha_inicio'] }} — {{ $alim['fecha_fin'] }}</td>
                            <td style="font-weight: bold; color: #0f766e;">{{ $alim['consumo_promedio'] }}</td>
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

    <!-- NOTAS DE EVOLUCIÓN CLÍNICA (MÉDICAS) -->
    <div class="section" style="page-break-before: always;">
        <div class="section-title">Bitácora Médica: Notas de Evolución</div>
        @if($evolucionClinica->count() > 0)
            <div style="margin-top: 8px;">
                @foreach($evolucionClinica as $control)
                    <div class="timeline-item medica">
                        <div class="timeline-header">
                            <span class="timeline-author">🩺 Dr(a). {{ $control->user?->nombre ?? 'Médico de Turno' }} {{ $control->user?->apellidos ?? '' }}</span>
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

    <!-- PLANES DE CUIDADOS Y APLICACIONES (NURSING LOGS) -->
    <div class="section">
        <div class="section-title">Planes de Cuidados e Historial de Aplicaciones (Enfermería)</div>
        @if($internacion->cuidados->count() > 0)
            <div style="margin-top: 8px; space-y-4">
                @foreach($internacion->cuidados as $cuidado)
                    <div style="margin-bottom: 12px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; background-color: #fafbfc; page-break-inside: avoid;">
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 4px;">
                            <tr>
                                <td style="border: none; padding: 0; font-weight: bold; font-size: 8.5pt; color: #0f766e;">
                                    📋 {{ $cuidado->tipo ?? 'Cuidado de Enfermería' }} — Frecuencia: {{ $cuidado->frecuencia ?? 'S/F' }}
                                </td>
                                <td style="border: none; padding: 0; text-align: right; font-size: 7.5pt; color: #64748b; font-family: monospace;">
                                    Indicado: {{ \Carbon\Carbon::parse($cuidado->created_at)->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        </table>
                        <p style="font-size: 8.2pt; font-weight: bold; color: #334155; margin-bottom: 6px;">
                            Directriz: {{ $cuidado->descripcion }}
                        </p>

                        @if($cuidado->cuidadosAplicados->count() > 0)
                            <div style="margin-top: 5px; padding-left: 8px; border-left: 2px solid #0d9488;">
                                <div style="font-size: 7.5pt; font-weight: 800; color: #0f766e; text-transform: uppercase; margin-bottom: 3px;">
                                    Registro de Cumplimiento (Aplicaciones por Enfermería):
                                </div>
                                @foreach($cuidado->cuidadosAplicados as $ap)
                                    <div style="font-size: 7.5pt; color: #334155; margin-bottom: 3px;">
                                        <span style="color: #166534; font-weight: bold;">✓ Realizado por:</span>
                                        {{ $ap->user ? $ap->user->nombre . ' ' . $ap->user->apellidos : 'Personal de Enfermería' }} 
                                        el <span style="font-family: monospace; font-weight: bold;">{{ \Carbon\Carbon::parse($ap->fecha_aplicacion)->format('d/m/Y H:i') }}</span>
                                        @if($ap->observaciones)
                                            <div style="font-size: 7.2pt; color: #64748b; font-style: italic; padding-left: 12px; margin-top: 1px;">
                                                Observación: "{{ $ap->observaciones }}"
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p style="font-size: 7.5pt; color: #94a3b8; font-style: italic;">
                                * No se registraron aplicaciones ni ejecuciones específicas para este cuidado por parte del turno.
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p style="padding: 6px 10px; background-color: #f8fafc; font-size: 8pt; color: #64748b; font-style: italic; border: 1px solid #e2e8f0; border-radius: 4px;">
                No se registraron directrices en el Plan de Cuidados e Indicaciones clínicas.
            </p>
        @endif
    </div>

    <!-- SECCIÓN DE FIRMAS Y TIMBRES -->
    <div class="section" style="margin-top: 60px;">
        <table class="signature-section">
            <tr>
                <td class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-text">Médico Tratante / Dr. Responsable</div>
                    <div class="signature-text" style="font-weight: normal; font-size: 7pt; color: #64748b;">Firma, Sello y Especialidad</div>
                </td>
                <td class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-text">Supervisor(a) de Turno Clínico</div>
                    <div class="signature-text" style="font-weight: normal; font-size: 7pt; color: #64748b;">Estación de Enfermería / Validación SSTEPI</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <p>Este informe de epicrisis clínica constituye un registro legal de salud. Generado automáticamente a través de la plataforma SSTEPI.</p>
        <p>Identificador de Internación: {{ $internacion->id }} | Cédula de Identidad Paciente: {{ $internacion->paciente?->ci ?? 'N/A' }} | Página 1 de 1</p>
    </div>
</body>
</html>
