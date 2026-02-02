<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sesion extends Model
{
    use HasFactory;

    protected $table = 'sesion'; 
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'token',
        'fecha_inicio'
    ];
}
