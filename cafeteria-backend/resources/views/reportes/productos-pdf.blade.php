<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Productos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; color: #334155; line-height: 1.6; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #f97316; }
        .header h1 { color: #f97316; font-size: 28px; margin-bottom: 5px; }
        .header .subtitle { color: #64748b; font-size: 14px; }
        .meta-info { margin-bottom: 30px; font-size: 12px; color: #64748b; text-align: center; }
        .section { margin-bottom: 30px; }
        .section-title { background: #fff7ed; color: #f97316; padding: 10px 15px; font-size: 16px; font-weight: bold; margin-bottom: 15px; border-left: 4px solid #f97316; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table thead { background: #f1f5f9; }
        table th { padding: 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; border-bottom: 2px solid #cbd5e1; }
        table td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; font-size: 12px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: bold; }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .alert { padding: 15px; background: #fef2f2; border-left: 4px solid #ef4444; color: #7f1d1d; margin-bottom: 15px; }
        .footer { position: fixed; bottom: 20px; left: 20px; right: 20px; text-align: center; font-size: 10px; color: #94a3b8; padding-top: 10px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>☕ Café Artesano</h1>
        <div class="subtitle">Reporte de Productos</div>
    </div>
    
    <div class="meta-info">
        <strong>Período:</strong> {{ ucfirst($periodo) }} | 
        <strong>Generado:</strong> {{ date('d/m/Y H:i') }}
    </div>
    
    <!-- Ranking de Productos -->
    <div class="section">
        <div class="section-title">📈 Top {{ count($ranking) }} Productos Más Vendidos</div>
        <table>
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th class="text-right">Cantidad Vendida</th>
                    <th class="text-right">Total Ventas (S/.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ranking as $index => $producto)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $producto->nombre }}</td>
                    <td>{{ $producto->categoria }}</td>
                    <td class="text-right">{{ $producto->cantidad_vendida }}</td>
                    <td class="text-right">{{ number_format($producto->total_ventas, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Productos con Baja Rotación -->
    <div class="section">
        <div class="section-title">📉 Productos con Baja Rotación</div>
        @if(count($bajaRotacion) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="text-right">Ventas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bajaRotacion as $producto)
                    <tr>
                        <td>{{ $producto->codigo }}</td>
                        <td>{{ $producto->nombre }}</td>
                        <td>{{ $producto->categoria }}</td>
                        <td class="text-right">
                            <span class="badge badge-danger">{{ $producto->cantidad_vendida }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="alert">
                ✅ ¡Excelente! Todos los productos tienen buena rotación en el período analizado.
            </div>
        @endif
    </div>
    
    <div class="footer">
        <div>Café Artesano - Sistema de Gestión</div>
        <div>Página <span class="page-number"></span></div>
    </div>
</body>
</html>