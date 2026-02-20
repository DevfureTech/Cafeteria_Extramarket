<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class MigrarData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrar:data'; 

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar datos de MySQL a PostgreSQL';

    /**
     * Execute the console command.
     */
   

    public function handle()
    { 
        $this->info('Migrando roles...');
    $roles = DB::connection('mysql')->table('rol')->get();
    foreach ($roles as $rol) {
        DB::connection('pgsql')->table('rol')->insert((array) $rol);
    }

    $this->info('Migrando usuarios...');
    $usuarios = DB::connection('mysql')->table('usuario')->get();
    foreach ($usuarios as $usuario) {
        DB::connection('pgsql')->table('usuario')->insert((array) $usuario);
    }

    $this->info('Migrando categorias...'); 
    $categorias = DB::connection('mysql')->table('categorias')->get();
    foreach ($categorias as $categoria) {
        DB::connection('pgsql')->table('categorias')->insert((array) $categoria);
    }

    $this->info('Migrando proveedores...');
    $proveedores = DB::connection('mysql')->table('proveedores')->get();
    foreach ($proveedores as $proveedor) {
        DB::connection('pgsql')->table('proveedores')->insert((array) $proveedor);
    }

    $this->info('Migrando productos...');
    $productos = DB::connection('mysql')->table('productos')->get();
    foreach ($productos as $producto) {
        DB::connection('pgsql')->table('productos')->insert((array) $producto);
    }
    
    $this->info('Migrando movimientos de inventario...');
    $movimientos = DB::connection('mysql')->table('movimientos_inventario')->get();
    foreach ($movimientos as $movimiento) {
        DB::connection('pgsql')->table('movimientos_inventario')->insert((array) $movimiento);
    }

    $this->info('Migrando alertas...'); 
    $alertas = DB::connection('mysql')->table('alertas')->get();
    foreach ($alertas as $alerta) {
        DB::connection('pgsql')->table('alertas')->insert((array) $alerta);
    }

    $this->info('Migración completa.');
    }

}