<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Epicrisis - {{ $internacion->paciente->nombre }} {{ $internacion->paciente->apellidos }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.4; color: #333; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #2c3e50; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 18pt; color: #2c3e50; margin-bottom: 5px; }
        .header p { font-size: 10pt; color: #7f8c8d; }
        .section { margin-bottom: 20px; }
        .section-title { background-color: #26a69a; color: white; padding: 8px 12px; font-size: 12pt; font-weight: bold; margin-bottom: 10px; }
        .info-grid { display: table; width: 100%; margin-bottom: 10px; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; font-weight: bold; width: 30%; padding: 5px; background-color: #ecf0f1; }
        .info-value { display: table-cell; padding: 5px; border-bottom: 1px solid #bdc3c7; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th { background-color: #00897b; color: white; padding: 8px; text-align: left; font-size: 10pt; }
        table td { padding: 6px; border-bottom: 1px solid #ddd; font-size: 10pt; }
        .footer { margin-top: 30px; text-align: center; font-size: 9pt; color: #7f8c8d; border-top: 1px solid #bdc3c7; padding-top: 10px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 9pt; font-weight: bold; }
        .badge-success { background-color: #27ae60; color: white; }
        .badge-warning { background-color: #f39c12; color: white; }
        .badge-danger { background-color: #e74c3c; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h1>EPICRISIS - INFORME DE ALTA MÉDICA</h1>
        <p>Hospital: {{ $internacion->medico->hospital->nombre ?? 'N/A' }}</p>
        <p>Fecha de generación: {{ $fechaGeneracion }}</p>
    </div>

    <!-- DATOS DEL PACIENTE -->
    <div class="section">
        <div class="section-title">DATOS DEL PACIENTE</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nombre Completo:</div>
                <div class="info-value">{{ $internacion->paciente->nombre }} {{ $internacion->paciente->apellidos }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">CI:</div>
                <div class="info-value">{{ $internacion->paciente->ci }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha de Nacimiento:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($internacion->paciente->fecha_nacimiento)->format('d/m/Y') }} ({{ \Carbon\Carbon::parse($internacion->paciente->fecha_nacimiento)->age }} años)</div>
            </div>
            <div class="info-row">
                <div class="info-label">Sexo:</div>
                <div class="info-value">{{ $internacion->paciente->genero }}</div>
            </div>
        </div>
    </div>

    <!-- DATOS DE LA INTERNACIÓN -->
    <div class="section">
        <div class="section-title">DATOS DE LA INTERNACIÓN</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Fecha de Ingreso:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($internacion->fecha_ingreso)->format('d/m/Y H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha de Alta:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($internacion->fecha_alta)->format('d/m/Y H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Días de Estancia:</div>
                <div class="info-value"><strong>{{ number_format($diasEstancia, 1) }} días</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Médico Tratante:</div>
                <div class="info-value">Dr(a). {{ $internacion->medico->nombre }} {{ $internacion->medico->apellidos }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Especialidad:</div>
                <div class="info-value">{{ $ocupacion->cama->sala->especialidad->nombre ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Sala/Cama:</div>
                <div class="info-value">{{ $ocupacion->cama->sala->nombre ?? 'N/A' }} - Cama {{ $ocupacion->cama->nombre ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- MOTIVO Y DIAGNÓSTICO -->
    <div class="section">
        <div class="section-title">MOTIVO DE INGRESO Y DIAGNÓSTICO</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Motivo de Ingreso:</div>
                <div class="info-value">{{ $internacion->motivo }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Diagnóstico:</div>
                <div class="info-value">{{ $internacion->diagnostico }}</div>
            </div>
        </div>
    </div>

    <!-- ANTROPOMETRÍA -->
    <div class="section">
        <div class="section-title">ANTROPOMETRÍA</div>
        <table>
            <thead>
                <tr>
                    <th>Medida</th>
                    <th>Al Ingreso</th>
                    <th>Al Egreso</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $antropometriaKeys = ['Peso', 'Talla', 'Altura', 'IMC'];
                    $signosAntro = collect($signosIngreso)->pluck('signo')->merge(collect($signosEgreso)->pluck('signo'))->unique()->filter(function($s) use ($antropometriaKeys) {
                        return in_array($s, $antropometriaKeys);
                    });
                @endphp
                @if($signosAntro->isEmpty())
                    <tr><td colspan="3" style="text-align:center;">No se registraron datos antropométricos</td></tr>
                @else
                    @foreach($signosAntro as $nombreSigno)
                        <tr>
                            <td>{{ $nombreSigno }}</td>
                            <td>
                                @php $sIn = collect($signosIngreso)->firstWhere('signo', $nombreSigno); @endphp
                                {{ $sIn ? $sIn['medida'] . ' ' . $sIn['unidad'] : 'N/R' }}
                            </td>
                            <td>
                                @php $sOut = collect($signosEgreso)->firstWhere('signo', $nombreSigno); @endphp
                                {{ $sOut ? $sOut['medida'] . ' ' . $sOut['unidad'] : 'N/R' }}
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- SIGNOS VITALES -->
    <div class="section">
        <div class="section-title">SIGNOS VITALES</div>
        <table>
            <thead>
                <tr>
                    <th>Signo Vital</th>
                    <th>Al Ingreso</th>
                    <th>Al Egreso</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $signosUnicos = collect($signosIngreso)->pluck('signo')->merge(collect($signosEgreso)->pluck('signo'))->unique();
                @endphp
                @foreach($signosUnicos as $nombreSigno)
                    @if(in_array($nombreSigno, ['Peso', 'Talla', 'Altura', 'IMC'])) @continue @endif
                    <tr>
                        <td>{{ $nombreSigno }}</td>
                        <td>
                            @php
                                $signoIngreso = collect($signosIngreso)->firstWhere('signo', $nombreSigno);
                            @endphp
                            {{ $signoIngreso ? $signoIngreso['medida'] . ' ' . $signoIngreso['unidad'] : 'N/R' }}
                        </td>
                        <td>
                            @php
                                $signoEgreso = collect($signosEgreso)->firstWhere('signo', $nombreSigno);
                            @endphp
                            {{ $signoEgreso ? $signoEgreso['medida'] . ' ' . $signoEgreso['unidad'] : 'N/R' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- TRATAMIENTO FARMACOLÓGICO -->
    <div class="section">
        <div class="section-title">TRATAMIENTO FARMACOLÓGICO</div>
        @if(count($resumenMedicamentos) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Medicamento</th>
                        <th>Dosis</th>
                        <th>Vía</th>
                        <th>Frecuencia</th>
                        <th>Adherencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumenMedicamentos as $med)
                        <tr>
                            <td>{{ $med['medicamento'] }}</td>
                            <td>{{ $med['dosis'] }}</td>
                            <td>{{ $med['via'] }}</td>
                            <td>{{ $med['frecuencia'] }}</td>
                            <td>
                                <span class="badge {{ $med['adherencia'] >= 80 ? 'badge-success' : ($med['adherencia'] >= 50 ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $med['adherencia'] }}%
                                </span>
                                ({{ $med['dosis_administradas'] }}/{{ $med['total_dosis'] }})
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="padding: 10px; background-color: #ecf0f1;">No se registraron medicamentos durante la internación.</p>
        @endif
    </div>

    <!-- ALIMENTACIÓN -->
    <div class="section">
        <div class="section-title">PLAN DE ALIMENTACIÓN</div>
        @if(count($resumenAlimentacion) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Tipo de Dieta</th>
                        <th>Vía</th>
                        <th>Período</th>
                        <th>Consumo Promedio</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumenAlimentacion as $alim)
                        <tr>
                            <td>{{ $alim['tipo_dieta'] }}</td>
                            <td>{{ $alim['via'] }}</td>
                            <td>{{ $alim['fecha_inicio'] }} - {{ $alim['fecha_fin'] }}</td>
                            <td>{{ $alim['consumo_promedio'] }}</td>
                            <td>{{ $alim['estado'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="padding: 10px; background-color: #ecf0f1;">No se registró plan de alimentación.</p>
        @endif
    </div>

    <!-- EVOLUCIÓN CLÍNICA -->
    <div class="section">
        <div class="section-title">EVOLUCIÓN CLÍNICA</div>
        @if($evolucionClinica->count() > 0)
            @foreach($evolucionClinica as $control)
                <div style="margin-bottom: 15px; padding: 10px; background-color: #f8f9fa; border-left: 4px solid #3498db;">
                    <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($control->fecha_control)->format('d/m/Y H:i') }}</p>
                    <p><strong>Registrado por:</strong> {{ $control->user->nombre }} {{ $control->user->apellidos }}</p>
                    <p><strong>Observaciones:</strong> {{ $control->observaciones ?? 'Sin observaciones' }}</p>
                </div>
            @endforeach
        @else
            <p style="padding: 10px; background-color: #ecf0f1;">No se registraron evoluciones médicas.</p>
        @endif
    </div>

    <!-- OBSERVACIONES FINALES -->
    @if($internacion->observaciones)
        <div class="section">
            <div class="section-title">OBSERVACIONES FINALES</div>
            <p style="padding: 10px; background-color: #fff3cd; border-left: 4px solid #ffc107;">
                {{ $internacion->observaciones }}
            </p>
        </div>
    @endif

    <div class="footer">
        <p>Este documento fue generado automáticamente por el Sistema de Gestión Hospitalaria</p>
        <p>Internación ID: {{ $internacion->id }} | Generado: {{ $fechaGeneracion }}</p>
    </div>
</body>
</html>
