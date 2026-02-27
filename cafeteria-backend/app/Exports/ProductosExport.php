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

class ProductosExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $ranking;
    protected $bajaRotacion;
    
    public function __construct($ranking, $bajaRotacion)
    {
        $this->ranking = $ranking;
        $this->bajaRotacion = $bajaRotacion;
    }
    
    public function collection()
    {
        $rows = collect();
        
        // Ranking de productos
        $rows->push(['TOP PRODUCTOS MÁS VENDIDOS', '', '', '']);
        $rows->push(['#', 'Producto', 'Cantidad Vendida', 'Total Ventas (S/.)']);
        
        foreach ($this->ranking as $index => $producto) {
            $rows->push([
                $index + 1,
                $producto->nombre,
                $producto->cantidad_vendida,
                number_format($producto->total_ventas, 2),
            ]);
        }
        
        $rows->push(['', '', '', '']); // Espacio
        
        // Productos con baja rotación
        $rows->push(['PRODUCTOS CON BAJA ROTACIÓN', '', '', '']);
        $rows->push(['Código', 'Producto', 'Categoría', 'Ventas']);
        
        if (count($this->bajaRotacion) > 0) {
            foreach ($this->bajaRotacion as $producto) {
                $rows->push([
                    $producto->codigo,
                    $producto->nombre,
                    $producto->categoria,
                    $producto->cantidad_vendida,
                ]);
            }
        } else {
            $rows->push(['', 'Todos los productos tienen buena rotación', '', '']);
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
            2 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ],
        ];
    }
    
    public function title(): string
    {
        return 'Productos';
    }
    
    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 30,
            'C' => 20,
            'D' => 18,
        ];
    }
}