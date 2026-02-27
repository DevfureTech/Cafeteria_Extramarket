<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            color: #334155;
            line-height: 1.6;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f97316;
        }
        
        .header h1 {
            color: #f97316;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            color: #64748b;
            font-size: 14px;
        }
        
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            font-size: 12px;
            color: #64748b;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            background: #fff7ed;
            color: #f97316;
            padding: 10px 15px;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            border-left: 4px solid #f97316;
        }
        
        .kpi-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .kpi-item {
            display: table-cell;
            width: 33.33%;
            padding: 15px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        
        .kpi-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .kpi-value {
            font-size: 24px;
            font-weight: bold;
            color: #334155;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table thead {
            background: #f1f5f9;
        }
        
        table th {
            padding: 12px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            border-bottom: 2px solid #cbd5e1;
        }
        
        table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
        }
        
        table tbody tr:hover {
            background: #f8fafc;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .font-bold {
            font-weight: bold;
        }
        
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }
        
        .page-number:after {
            content: counter(page);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>☕ Café Artesano</h1>
        <div class="subtitle">Reporte de Ventas</div>
    </div>
    
    <!-- Meta Info -->
    <div class="meta-info">
        <div>
            <strong>Período:</strong> {{ ucfirst($periodo) }}
        </div>
        <div>
            <strong>Rango:</strong> {{ date('d/m/Y', strtotime($rangoFechas['inicio'])) }} - {{ date('d/m/Y', strtotime($rangoFechas['fin'])) }}
        </div>
        <div>
            <strong>Generado:</strong> {{ date('d/m/Y H:i') }}
        </div>
    </div>
    
    <!-- KPIs -->
    <div class="section">
        <div class="section-title">Resumen Ejecutivo</div>
        <div class="kpi-grid">
            <div class="kpi-item">
                <div class="kpi-label">Total Ventas</div>
                <div class="kpi-value">S/. {{ number_format($resumen['total_ventas'], 2) }}</div>
            </div>
            <div class="kpi-item">
                <div class="kpi-label">Cantidad de Ventas</div>
                <div class="kpi-value">{{ $resumen['cantidad_ventas'] }}</div>
            </div>
            <div class="kpi-item">
                <div class="kpi-label">Ticket Promedio</div>
                <div class="kpi-value">S/. {{ number_format($resumen['ticket_promedio'], 2) }}</div>
            </div>
        </div>
    </div>
    
    <!-- Ventas por Día -->
    <div class="section">
        <div class="section-title">Detalle por Día</div>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th class="text-right">Total (S/.)</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-right">Promedio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventasPorDia as $venta)
                <tr>
                    <td>{{ date('d/m/Y', strtotime($venta->fecha)) }}</td>
                    <td class="text-right">{{ number_format($venta->total, 2) }}</td>
                    <td class="text-center">{{ $venta->cantidad }}</td>
                    <td class="text-right">{{ number_format($venta->cantidad > 0 ? $venta->total / $venta->cantidad : 0, 2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <td class="font-bold">TOTAL</td>
                    <td class="text-right font-bold">S/. {{ number_format($resumen['total_ventas'], 2) }}</td>
                    <td class="text-center font-bold">{{ $resumen['cantidad_ventas'] }}</td>
                    <td class="text-right font-bold">S/. {{ number_format($resumen['ticket_promedio'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Distribución por Método de Pago -->
    <div class="section">
        <div class="section-title">Distribución por Método de Pago</div>
        <table>
            <thead>
                <tr>
                    <th>Método</th>
                    <th class="text-right">Total (S/.)</th>
                    <th class="text-right">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalGeneral = collect($distribucionPago)->sum('total');
                @endphp
                @foreach($distribucionPago as $pago)
                <tr>
                    <td>{{ $pago->metodo_pago }}</td>
                    <td class="text-right">{{ number_format($pago->total, 2) }}</td>
                    <td class="text-right">{{ number_format($totalGeneral > 0 ? ($pago->total / $totalGeneral) * 100 : 0, 2) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <div>Café Artesano - Sistema de Gestión</div>
        <div>Página <span class="page-number"></span></div>
    </div>
</body>
</html>