<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Proyecto;
use App\Models\Cliente;
class ProyectoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $electroperu = Cliente::where('nombre', 'ELECTROPERU S.A.')->first();
        $enel = Cliente::where('nombre', 'ENEL DISTRIBUCIÓN PERÚ')->first();

        Proyecto::create([
            'nombre' => 'Inspección Redes Lima Norte',
            'descripcion' => 'Inspección y mantenimiento de equipos eléctricos en Lima Norte',
            'cliente_id' => $electroperu->id,
            'fecha_inicio' => '2025-01-15',
            'estado' => 'activo'
        ]);

        Proyecto::create([
            'nombre' => 'Mantenimiento Callao 2025',
            'descripcion' => 'Ruteo y mantenimiento preventivo zona Callao',
            'cliente_id' => $enel->id,
            'fecha_inicio' => '2025-02-01',
            'estado' => 'activo'
        ]);

        Proyecto::create([
            'nombre' => 'Inspección San Isidro',
            'descripcion' => 'Proyecto piloto distrito San Isidro',
            'cliente_id' => $electroperu->id,
            'fecha_inicio' => '2025-01-20',
            'estado' => 'activo'
        ]);
    }
}
