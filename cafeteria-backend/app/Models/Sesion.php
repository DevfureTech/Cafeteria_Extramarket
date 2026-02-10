<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Usuario;

class Sesion extends Model
{
    use HasFactory;

    protected $table = 'sesion';
       protected $primaryKey = 'id_sesion'; 
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'token',
        'fecha_inicio',
        'ip',
        'user_agent',
        'fecha_expiracion',
        'activo',
        'fecha_fin'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}
