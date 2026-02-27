<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; color: #334155; line-height: 1.6; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #f97316; }
        .header h1 { color: #f97316; font-size: 28px; margin-bottom: 5px; }
        .header .subtitle { color: #64748b; font-size: 14px; }
        .meta-info { margin-bottom: 30px; font-size: 12px; color: #64748b; text-align: center; }
        .section { margin-bottom: 30px; }
        .section-title { background: #fff7ed; color: #f97316; padding: 10px 15px; font-size: 16px; font-weight: bold; margin-bottom: 15px; border-left: 4px solid #f97316; }
        .kpi-box { background: #fff7ed; padding: 20px; text-align: center; border-radius: 8px; margin-bottom: 20px; border: 2px solid #f97316; }
        .kpi-box .value { font-size: 32px; font-weight: bold; color: #f97316; }
        .kpi-box .label { font-size: 14px; color: #64748b; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
        table thead { background: #f1f5f9; }
        table th { padding: 10px 8px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; border-bottom: 2px solid #cbd5e1; }
        table td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .stock-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: bold; }
        .stock-badge-ok { background: #dcfce7; color: #15803d; }
        .stock-badge-bajo { background: #fee2e2; color: #b91c1c; }
        .row-alerta { background: #fef2f2; }
        .footer { position: fixed; bottom: 20px; left: 20px; right: 20px; text-align: center; font-size: 10px; color: #94a3b8; padding-top: 10px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>☕ Café Artesano</h1>
        <div class="subtitle">Reporte de Inventario</div>
    </div>
    
    <div class="meta-info">
        <strong>Generado:</strong> {{ date('d/m/Y H:i') }} | 
        <strong>Total Productos:</strong> {{ $resumen['total_productos'] }} | 
        <strong>Alertas de Stock:</strong> {{ $resumen['alertas_stock'] }}
    </div>
    
    <!-- Valor Total -->
    <div class="kpi-box">
        <div class="value">S/. {{ number_format($resumen['valor_total'], 2) }}</div>
        <div class="label">Valor Total del Inventario</div>
    </div>
    
    <!-- Stock Disponible -->
    <div class="section">
        <div class="section-title">Stock Disponible</div>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th class="text-center">Stock Actual</th>
                    <th class="text-right">Precio</th>
                    <th class="text-right">Valor Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inventario as $item)
                <tr class="{{ $item->stock_actual <= $item->stock_minimo ? 'row-alerta' : '' }}">
                    <td>{{ $item->codigo }}</td>
                    <td>{{ $item->nombre }}</td>
                    <td>{{ $item->categoria }}</td>
                    <td class="text-center">
                        <span class="stock-badge {{ $item->stock_actual <= $item->stock_minimo ? 'stock-badge-bajo' : 'stock-badge-ok' }}">
                            {{ $item->stock_actual }} {{ $item->unidad_medida }}
                        </span>
                    </td>
                    <td class="text-right">{{ number_format($item->precio_compra, 2) }}</td>
                    <td class="text-right">{{ number_format($item->valor_total, 2) }}</td>
                </tr>
                @endforeach
                <tr style="border-top: 2px solid #334155;">
                    <td colspan="5" class="text-right" style="font-weight: bold;">TOTAL</td>
                    <td class="text-right" style="font-weight: bold;">S/. {{ number_format($resumen['valor_total'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Valor por Categoría -->
    <div class="section">
        <div class="section-title">Valor por Categoría</div>
        <table>
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th class="text-right">Valor (S/.)</th>
                    <th class="text-right">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach($valorPorCategoria as $cat)
                <tr>
                    <td>{{ $cat->categoria }}</td>
                    <td class="text-right">{{ number_format($cat->valor, 2) }}</td>
                    <td class="text-right">{{ number_format($resumen['valor_total'] > 0 ? ($cat->valor / $resumen['valor_total']) * 100 : 0, 2) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="footer">
        <div>Café Artesano - Sistema de Gestión</div>
        <div>Página <span class="page-number"></span></div>
    </div>
</body>
</html>