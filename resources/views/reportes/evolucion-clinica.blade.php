<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Evolución Clínica - {{ $internacion->paciente?->nombre ?? 'Paciente' }} {{ $internacion->paciente?->apellidos ?? '' }}</title>
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
    <table style="width: 100%; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-bottom: 20px; border-collapse: collapse;">
        <tr>
            <td style="width: 30%; border: none; padding: 0; vertical-align: middle;">
                @if(isset($logoBase64) && $logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo SSTEPI" style="height: 55px; max-width: 150px; object-fit: contain;">
                @else
                    <span style="font-size: 14pt; font-weight: bold; color: #3498db;">SSTEPI</span>
                @endif
            </td>
            <td style="width: 70%; text-align: right; border: none; padding: 0; vertical-align: middle;">
                <h1 style="font-size: 14pt; color: #2c3e50; margin: 0 0 4px 0; padding: 0; font-weight: bold;">EVOLUCIÓN CLÍNICA COMPLETA</h1>
                <p style="font-size: 8.5pt; color: #555; margin: 0; padding: 0;"><strong>Paciente:</strong> {{ $internacion->paciente?->nombre ?? 'N/A' }} {{ $internacion->paciente?->apellidos ?? '' }} (CI: {{ $internacion->paciente?->ci ?? 'N/A' }})</p>
                <p style="font-size: 8.5pt; color: #555; margin: 0; padding: 0;"><strong>Médico:</strong> Dr(a). {{ $internacion->medico?->nombre ?? 'Sin' }} {{ $internacion->medico?->apellidos ?? 'médico asignado' }}</p>
                <p style="font-size: 8.5pt; color: #7f8c8d; margin: 0; padding: 0;">Generado: {{ $fechaGeneracion }}</p>
            </td>
        </tr>
    </table>

    @if($controlesPorFecha && $controlesPorFecha->count() > 0)
        @foreach($controlesPorFecha as $fecha => $controles)
            <div class="section">
                <div class="section-title">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</div>

                @foreach($controles as $control)
                    <div class="control-card">
                        <div class="control-header">
                            {{ $control->fecha_control ? \Carbon\Carbon::parse($control->fecha_control)->format('H:i') : '--:--' }} -
                            {{ $control->tipo ?? 'Control' }}
                            ({{ $control->user?->nombre ?? 'Sistema' }} {{ $control->user?->apellidos ?? '' }})
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
                                            <td>{{ $valor->signo?->nombre ?? 'N/A' }}</td>
                                            <td>{{ $valor->medida ?? 'N/A' }}</td>
                                            <td>{{ $valor->signo?->unidad ?? '' }}</td>
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
    @else
        <div style="text-align: center; padding: 20px; color: #7f8c8d; border: 1px dashed #ccc; margin-top: 20px;">
            No se registran notas de evolución ni controles clínicos durante la internación.
        </div>
    @endif

    <div class="footer">
        <p>Sistema de Gestión Hospitalaria | Internación ID: {{ $internacion->id }}</p>
    </div>
</body>
</html>
