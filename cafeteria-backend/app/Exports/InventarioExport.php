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

class InventarioExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $datos;
    
    public function __construct($datos)
    {
        $this->datos = $datos;
    }
    
    public function collection()
    {
        $rows = collect();
        
        // Resumen
        $rows->push(['RESUMEN DE INVENTARIO', '', '', '', '', '']);
        $rows->push([
            'Valor Total:',
            'S/. ' . number_format($this->datos['resumen']['valor_total'], 2),
            '',
            '',
            '',
            '',
        ]);
        $rows->push([
            'Total Productos:',
            $this->datos['resumen']['total_productos'],
            '',
            '',
            '',
            '',
        ]);
        $rows->push([
            'Alertas de Stock:',
            $this->datos['resumen']['alertas_stock'],
            '',
            '',
            '',
            '',
        ]);
        $rows->push(['', '', '', '', '', '']); // Espacio
        
        // Inventario detallado
        $rows->push(['STOCK DISPONIBLE', '', '', '', '', '']);
        $rows->push(['Código', 'Producto', 'Categoría', 'Stock Actual', 'Precio Compra', 'Valor Total']);
        
        foreach ($this->datos['inventario'] as $item) {
            $rows->push([
                $item->codigo,
                $item->nombre,
                $item->categoria,
                $item->stock_actual . ' ' . $item->unidad_medida,
                number_format($item->precio_compra, 2),
                number_format($item->valor_total, 2),
            ]);
        }
        
        $rows->push(['', '', '', '', '', '']); // Espacio
        
        // Valor por categoría
        $rows->push(['VALOR POR CATEGORÍA', '', '', '', '', '']);
        $rows->push(['Categoría', 'Valor (S/.)', '', '', '', '']);
        
        foreach ($this->datos['valor_por_categoria'] as $cat) {
            $rows->push([
                $cat->categoria,
                number_format($cat->valor, 2),
                '',
                '',
                '',
                '',
            ]);
        }
        
        return $rows;
    }
    
    public function headings(): array
    {
        return [];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F97316']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            6 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF7ED']],
            ],
            7 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ],
        ];
    }
    
    public function title(): string
    {
        return 'Inventario';
    }
    
    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 30,
            'C' => 20,
            'D' => 15,
            'E' => 15,
            'F' => 15,
        ];
    }
}