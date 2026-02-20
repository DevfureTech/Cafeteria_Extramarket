<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $primaryKey = 'producto_id';

    protected $fillable = [
        'codigo',
        'nombre',
        'categoria_id',
        'proveedor_id', // ✅ CORREGIDO (antes proveedor)
        'cantidad_actual',
        'unidad_medida',
        'precio_compra',
        'stock_minimo',
        'fecha_vencimiento',
    ];

    protected $casts = [
        'cantidad_actual'   => 'decimal:2',
        'precio_compra'     => 'decimal:2',
        'stock_minimo'      => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    // ══════════════════════════════════════════════════════════
    // RELACIONES
    // ══════════════════════════════════════════════════════════

    /**
     * Categoría del producto
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id', 'categoria_id');
    }

    /**
     * ✅ NUEVA — proveedor del producto
     */
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id', 'proveedor_id');
    }

    /**
     * Movimientos de inventario
     */
    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id', 'producto_id');
    }

    /**
     * Alertas del producto
     */
    public function alertas()
    {
        return $this->hasMany(Alerta::class, 'producto_id', 'producto_id');
    }

    // ══════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════

    public function scopeStockBajo($query)
    {
        return $query->whereRaw('cantidad_actual <= stock_minimo');
    }

    public function scopeProximosVencer($query, $dias = 7)
    {
        return $query
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<=', now()->addDays($dias))
            ->whereDate('fecha_vencimiento', '>=', now());
    }

    // ══════════════════════════════════════════════════════════
    // ACCESSORS
    // ══════════════════════════════════════════════════════════

    public function getStockBajoAttribute(): bool
    {
        return $this->cantidad_actual <= $this->stock_minimo;
    }

    public function getDiasParaVencerAttribute(): ?int
    {
        if (!$this->fecha_vencimiento) return null;
        return (int) now()->diffInDays($this->fecha_vencimiento, false);
    }

    public function getCategoriaNombreAttribute(): ?string
    {
        return $this->categoria?->nombre;
    }

    /**
     * ✅ NUEVO accessor útil
     */
    public function getProveedorNombreAttribute(): ?string
    {
        return $this->proveedor?->nombre;
    }
}
