<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class VentasExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $datos;
    protected $periodo;
    
    public function __construct($datos, $periodo)
    {
        $this->datos = $datos;
        $this->periodo = $periodo;
    }
    
    /**
     * Retornar colección de datos
     */
    public function collection()
    {
        $rows = collect();
        
        // Fila de resumen
        $rows->push([
            'RESUMEN DEL PERÍODO',
            '',
            '',
            '',
        ]);
        $rows->push([
            'Total Ventas:',
            'S/. ' . number_format($this->datos['resumen']['total_ventas'], 2),
            '',
            '',
        ]);
        $rows->push([
            'Cantidad de Ventas:',
            $this->datos['resumen']['cantidad_ventas'],
            '',
            '',
        ]);
        $rows->push([
            'Ticket Promedio:',
            'S/. ' . number_format($this->datos['resumen']['ticket_promedio'], 2),
            '',
            '',
        ]);
        $rows->push(['', '', '', '']); // Espacio
        
        // Detalle por día
        $rows->push(['DETALLE POR DÍA', '', '', '']);
        $rows->push(['Fecha', 'Total (S/.)', 'Cantidad', 'Promedio']);
        
        foreach ($this->datos['ventas_por_dia'] as $venta) {
            $promedio = $venta->cantidad > 0 ? $venta->total / $venta->cantidad : 0;
            $rows->push([
                $venta->fecha,
                number_format($venta->total, 2),
                $venta->cantidad,
                number_format($promedio, 2),
            ]);
        }
        
        $rows->push(['', '', '', '']); // Espacio
        
        // Distribución por método de pago
        $rows->push(['DISTRIBUCIÓN POR MÉTODO DE PAGO', '', '', '']);
        $rows->push(['Método', 'Total (S/.)', 'Porcentaje', '']);
        
        $totalGeneral = collect($this->datos['distribucion_pago'])->sum('total');
        
        foreach ($this->datos['distribucion_pago'] as $pago) {
            $porcentaje = $totalGeneral > 0 ? ($pago->total / $totalGeneral) * 100 : 0;
            $rows->push([
                $pago->metodo_pago,
                number_format($pago->total, 2),
                number_format($porcentaje, 2) . '%',
                '',
            ]);
        }
        
        return $rows;
    }
    
    /**
     * Encabezados principales (no se usan aquí porque manejamos manualmente)
     */
    public function headings(): array
    {
        return [];
    }
    
    /**
     * Aplicar estilos
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Título principal
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F97316'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            
            // Secciones
            6 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF7ED'],
                ],
            ],
            
            // Headers de tablas
            7 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }
    
    /**
     * Título de la hoja
     */
    public function title(): string
    {
        return 'Ventas ' . ucfirst($this->periodo);
    }
    
    /**
     * Anchos de columnas
     */
    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 15,
            'C' => 15,
            'D' => 15,
        ];
    }
}