<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';
    protected $primaryKey = 'proveedor_id';

    protected $fillable = [
        'ruc',
        'nombre',
        'telefono',
        'email',
        'direccion',
        'estado',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ══════════════════════════════════════════════════════════
    // RELACIONES
    // ══════════════════════════════════════════════════════════

    /**
     * Un proveedor tiene muchos productos
     */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'proveedor_id', 'proveedor_id');
    }

    // ══════════════════════════════════════════════════════════
    // SCOPES ÚTILES
    // ══════════════════════════════════════════════════════════

    /**
     * Solo proveedores activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    // ══════════════════════════════════════════════════════════
    // ACCESSORS
    // ══════════════════════════════════════════════════════════

    /**
     * Formato bonito del RUC
     */
    public function getRucFormateadoAttribute(): string
    {
        return $this->ruc;
    }
}
