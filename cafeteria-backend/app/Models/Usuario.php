<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
   use hasApiTokens, HasFactory, Notifiable; 
   protected $table = 'usuario'; 
   protected $primaryKey = 'id_usuario'; 
   public $timestamps = false;
   
   protected $fillable = [
    'id_rol', 
    'nombre_completo', 
    'nombre_usuario', 
    'contraseña_administrador', 
    'pin_usuario', 
    'activo'
   ]; 

   protected $hidden = [
    'contraseña_administrador', 
    'pin_usuario', 
    'remember_token'
   ]; 

   protected $casts = [
    'activo' => 'boolean', 
   ]; 

   public function rol(){
<<<<<<< HEAD
    return $this->belongsTo(Rol::class, 'id_rol'); 
=======
    return $this->belongsTo(Rol::class, 'id_rol', 'id_rol'); 
>>>>>>> respaldo-local
   }

   public function sesion(){
    return $this->hasMany(Sesion::class); 
   }

   public function auditoria(){
    return $this->hasMany(Auditoria::class); 
   }

   public function venta(){
    return $this->hasMany(Venta::class);
   }

    public function getNombreCompleto($value){
        return strtoupper($value);
    }

    public function getIniciales(){
        $palabras = explode(' ', $this->nombre_completo);
        $iniciales = '';
        
        foreach($palabras as $palabra) {
            $iniciales .= substr($palabra, 0, 1);
        }
        
        return strtoupper($iniciales);
    }

    public function setPassword($value)
    {
        if (!empty($value)) {
            $this->attributes['contraseña_admninistrador'] = bcrypt($value);
        }
    }

    public function setUsername($value){
        $this->attributes['nombre_usuario'] = strtolower(trim($value));
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorRol($query, $rol){
        if (is_numeric($rol)) {
            return $query->where('id_rol', $rol);
        }
        
        return $query->whereHas('rol', function($q) use ($rol) {
            $q->where('nombre', $rol);
        });
    }
    public function esAdmin()
    {
        return $this->rol && $this->rol->nombre === 'Administrador';
    }

    public function tienePermiso($modulo, $accion)
    {
        $permiso = $this->rol->permisos()
            ->where('modulo', $modulo)
            ->first();
        
        if (!$permiso) {
            return false;
        }
        
        switch($accion) {
            case 'crear':
                return $permiso->puede_crear;
            case 'leer':
                return $permiso->puede_leer;
            case 'actualizar':
                return $permiso->puede_actualizar;
            case 'eliminar':
                return $permiso->puede_eliminar;
            default:
                return false;
        }
    }

     public function ultimaSesion()
    {
        return $this->sesiones()
            ->where('activo', true)
            ->latest()
            ->first();
    }



}
