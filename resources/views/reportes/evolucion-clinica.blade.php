<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Evolución Clínica - {{ $internacion->paciente->nombre }} {{ $internacion->paciente->apellidos }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10pt; line-height: 1.3; color: #333; padding: 15px; }
        .header { text-align: center; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 16pt; color: #2c3e50; }
        .section { margin-bottom: 15px; page-break-inside: avoid; }
        .section-title { background-color: #3498db; color: white; padding: 6px 10px; font-size: 11pt; font-weight: bold; }
        .control-card { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; background-color: #f9f9f9; }
        .control-header { font-weight: bold; color: #2c3e50; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        table th { background-color: #34495e; color: white; padding: 5px; text-align: left; font-size: 9pt; }
        table td { padding: 4px; border-bottom: 1px solid #ddd; font-size: 9pt; }
        .footer { margin-top: 20px; text-align: center; font-size: 8pt; color: #7f8c8d; border-top: 1px solid #bdc3c7; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 EVOLUCIÓN CLÍNICA COMPLETA</h1>
        <p>Paciente: {{ $internacion->paciente->nombre }} {{ $internacion->paciente->apellidos }} (CI: {{ $internacion->paciente->ci }})</p>
        <p>Médico: Dr(a). {{ $internacion->medico->nombre }} {{ $internacion->medico->apellidos }}</p>
        <p>Generado: {{ $fechaGeneracion }}</p>
    </div>

    @foreach($controlesPorFecha as $fecha => $controles)
        <div class="section">
            <div class="section-title">📅 {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</div>

            @foreach($controles as $control)
                <div class="control-card">
                    <div class="control-header">
                        🕐 {{ \Carbon\Carbon::parse($control->fecha_control)->format('H:i') }} -
                        {{ $control->tipo }}
                        ({{ $control->user->nombre }} {{ $control->user->apellidos }})
                    </div>

                    @if($control->valores && $control->valores->count() > 0)
                        <table>
                            <thead>
                                <tr>
                                    <th>Signo Vital</th>
                                    <th>Valor</th>
                                    <th>Unidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($control->valores as $valor)
                                    <tr>
                                        <td>{{ $valor->signo->nombre }}</td>
                                        <td>{{ $valor->medida }}</td>
                                        <td>{{ $valor->signo->unidad }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if($control->observaciones)
                        <p style="margin-top: 8px; padding: 6px; background-color: #fff3cd; border-left: 3px solid #ffc107;">
                            <strong>Observaciones:</strong> {{ $control->observaciones }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach

    <div class="footer">
        <p>Sistema de Gestión Hospitalaria | Internación ID: {{ $internacion->id }}</p>
    </div>
</body>
</html>
