<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;
    protected $table = 'rol';
    protected $primaryKey = 'id_rol';
    public $timestamps = false;
    
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class);
    }

    public function permisos()
{
    return $this->belongsToMany(
        Permiso::class,
        'rol_permiso',
        'id_rol',
        'id_permiso'
    );
}



    public function permisosAgrupados()
    {
        $permisos = [];
        
        foreach($this->permisos as $permiso) {
            $permisos[$permiso->modulo] = [
                'crear' => $permiso->puede_crear,
                'leer' => $permiso->puede_leer,
                'actualizar' => $permiso->puede_actualizar,
                'eliminar' => $permiso->puede_eliminar,
            ];
        }
        
        return $permisos;
    }
}