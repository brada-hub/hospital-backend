<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas Hospitalarias</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10pt; line-height: 1.3; color: #333; padding: 15px; }
        .header { text-align: center; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 18pt; color: #2c3e50; }
        .kpi-grid { display: table; width: 100%; margin-bottom: 20px; }
        .kpi-row { display: table-row; }
        .kpi-cell { display: table-cell; width: 25%; padding: 10px; text-align: center; border: 1px solid #ddd; background-color: #ecf0f1; }
        .kpi-value { font-size: 20pt; font-weight: bold; color: #3498db; }
        .kpi-label { font-size: 9pt; color: #7f8c8d; margin-top: 5px; }
        .section { margin-bottom: 20px; page-break-inside: avoid; }
        .section-title { background-color: #3498db; color: white; padding: 8px 12px; font-size: 12pt; font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th { background-color: #34495e; color: white; padding: 6px; text-align: left; font-size: 10pt; }
        table td { padding: 5px; border-bottom: 1px solid #ddd; font-size: 10pt; }
        .footer { margin-top: 20px; text-align: center; font-size: 9pt; color: #7f8c8d; border-top: 1px solid #bdc3c7; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📈 ESTADÍSTICAS HOSPITALARIAS</h1>
        <p>Período: {{ $periodo['inicio'] }} - {{ $periodo['fin'] }}</p>
        <p>Generado: {{ $fecha_generacion }}</p>
    </div>

    <!-- KPIs PRINCIPALES -->
    <div class="kpi-grid">
        <div class="kpi-row">
            <div class="kpi-cell">
                <div class="kpi-value">{{ $estancia_media_dias }}</div>
                <div class="kpi-label">Estancia Media (días)</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-value">{{ $total_internaciones }}</div>
                <div class="kpi-label">Total Internaciones</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-value">{{ $internaciones_activas }}</div>
                <div class="kpi-label">Internaciones Activas</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-value">{{ $efectividad_tratamientos['porcentaje_efectividad'] }}%</div>
                <div class="kpi-label">Efectividad Tratamientos</div>
            </div>
        </div>
    </div>

    <!-- MEDICAMENTOS MÁS USADOS -->
    <div class="section">
        <div class="section-title">💊 TOP 10 MEDICAMENTOS MÁS PRESCRITOS</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Medicamento</th>
                    <th>Total Prescripciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($medicamentos_mas_usados as $index => $med)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $med->nombre }}</td>
                        <td>{{ $med->total_prescripciones }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- DIAGNÓSTICOS FRECUENTES -->
    <div class="section">
        <div class="section-title">🩺 DIAGNÓSTICOS MÁS FRECUENTES</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Diagnóstico</th>
                    <th>Casos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($diagnosticos_frecuentes as $index => $diag)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $diag->diagnostico }}</td>
                        <td>{{ $diag->total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- OCUPACIÓN DE SALAS -->
    <div class="section">
        <div class="section-title">🏥 OCUPACIÓN POR SALA</div>
        <table>
            <thead>
                <tr>
                    <th>Sala</th>
                    <th>Total Ocupaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ocupacion_salas as $sala)
                    <tr>
                        <td>{{ $sala->nombre }}</td>
                        <td>{{ $sala->total_ocupaciones }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- EFECTIVIDAD DE TRATAMIENTOS -->
    <div class="section">
        <div class="section-title">✅ ESTADO DE TRATAMIENTOS</div>
        <table>
            <thead>
                <tr>
                    <th>Estado</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>✅ Finalizados</td>
                    <td>{{ $efectividad_tratamientos['finalizados'] }}</td>
                </tr>
                <tr>
                    <td>⏸️ Suspendidos</td>
                    <td>{{ $efectividad_tratamientos['suspendidos'] }}</td>
                </tr>
                <tr>
                    <td>🔄 Activos</td>
                    <td>{{ $efectividad_tratamientos['activos'] }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Sistema de Gestión Hospitalaria - Reportes Estadísticos</p>
    </div>
</body>
</html>
