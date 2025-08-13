<?php

namespace Database\Seeders;

use App\Models\Ruta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RutaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ruta::create([
            'nombre' => 'Ruta Norte',
            'proyecto_id' => 1,
            'total_equipos' => 8,
            'distancia_km' => 3.456,
            'tiempo_estimado_minutos' => 30,
            'centro_lat' => -12.04500,
            'centro_lng' => -77.03000,
            'estado' => 'pendiente',
            'asignado_a' => null,
            'fecha_asignacion' => null,
        ]);

        Ruta::create([
            'nombre' => 'Ruta Sur',
            'proyecto_id' => 1,
            'total_equipos' => 15,
            'distancia_km' => 7.892,
            'tiempo_estimado_minutos' => 60,
            'centro_lat' => -12.05000,
            'centro_lng' => -77.03500,
            'estado' => 'pendiente',
            'asignado_a' => null,
            'fecha_asignacion' => null,
        ]);

        Ruta::create([
            'nombre' => 'Ruta Este',
            'proyecto_id' => 1,
            'total_equipos' => 5,
            'distancia_km' => 2.100,
            'tiempo_estimado_minutos' => 20,
            'centro_lat' => -12.04000,
            'centro_lng' => -77.02500,
            'estado' => 'pendiente',
            'asignado_a' => null,
            'fecha_asignacion' => null,
        ]);
    }
}
