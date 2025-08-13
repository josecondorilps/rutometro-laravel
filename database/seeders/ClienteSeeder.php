<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cliente;
class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Cliente::create([
            'nombre' => 'ELECTROPERU S.A.',
            'email' => 'contacto@electroperu.pe',
            'telefono' => '+51 1 234-5678',
            'activo' => true
        ]);

        Cliente::create([
            'nombre' => 'ENEL DISTRIBUCIÓN PERÚ',
            'email' => 'proyectos@enel.pe',
            'telefono' => '+51 1 567-8900',
            'activo' => true
        ]);

        Cliente::create([
            'nombre' => 'LUZ DEL SUR S.A.A.',
            'email' => 'operaciones@luzdelsur.pe',
            'telefono' => '+51 1 345-6789',
            'activo' => true
        ]);
    }
}
