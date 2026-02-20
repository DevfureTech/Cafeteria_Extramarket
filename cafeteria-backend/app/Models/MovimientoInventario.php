<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';
    protected $primaryKey = 'mov_inv_id';

    public $timestamps = false; 

    protected $fillable = [
        'producto_id',
        'proveedor_id',
        'tipo',
        'cantidad',
        'precio_unitario',
        'motivo',
        'justificacion',
        'usuario_id',
        'fecha_movimiento',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'fecha_movimiento' => 'datetime',
    ];

    // 🔗 Relaciones

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'producto_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id', 'proveedor_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id_usuario');
    }
}
