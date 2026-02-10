<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditoria';
    protected $primaryKey = 'id_auditoria';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'tabla',
        'usuario',
        'operacion',
        'ip',
        'fecha',
    ];

        protected $casts = [
  
            'fecha' => 'datetime',
        ];

    // Relación con Usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}
