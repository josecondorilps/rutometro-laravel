<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipo;
use App\Models\Ruta;

class EquipoSeeder extends Seeder
{
    public function run()
    {
        $ruta = \App\Models\Ruta::find(7);

        if (!$ruta) {
            $this->command->info('La ruta con ID 7 no existe. Saltando EquipoSeeder...');
            return;
        }

        $equipos = [
            ['EQP-001', -12.0464, -77.0428, 'Poste', 'Av. Javier Prado 123', 'Lima Norte', 'operativo', 'Poste eléctrico en buen estado'],
            ['EQP-002', -12.0465, -77.0430, 'Transformador', 'Jr. Las Flores 456', 'Lima Norte', 'mantenimiento', 'Transformador requiere mantenimiento'],
            ['EQP-003', -12.0470, -77.0435, 'Medidor', 'Calle Los Olivos 789', 'Lima Norte', 'operativo', 'Medidor funcionando correctamente'],
            ['EQP-004', -12.0475, -77.0440, 'Poste', 'Av. Universitaria 321', 'Lima Norte', 'dañado', 'Poste con daños estructurales'],
            ['EQP-005', -12.0480, -77.0445, 'Transformador', 'Jr. San Martín 654', 'Lima Norte', 'operativo', 'Transformador en perfecto estado'],
            ['EQP-006', -12.0485, -77.0450, 'Medidor', 'Calle Lima 987', 'Lima Norte', 'operativo', 'Medidor recién instalado'],
            ['EQP-007', -12.0490, -77.0455, 'Poste', 'Av. Brasil 159', 'Lima Norte', 'mantenimiento', 'Poste necesita pintura'],
            ['EQP-008', -12.0495, -77.0460, 'Transformador', 'Jr. Cusco 753', 'Lima Norte', 'operativo', 'Transformador funcionando bien'],
        ];

        foreach ($equipos as $index => $equipo) {
            Equipo::create([
                'identificador' => $equipo[0],
                'latitud' => $equipo[1],
                'longitud' => $equipo[2],
                'tipo' => $equipo[3],
                'direccion' => $equipo[4],
                'area' => $equipo[5],
                'estado' => $equipo[6],
                'observaciones_campo' => $equipo[7],
                'altitud' => rand(100, 500),
                'ruta_id' => $ruta->id,
                'orden_en_ruta' => $index + 1,
            ]);
        }

        $this->command->info('Equipos creados exitosamente y asociados a la ruta ID 7');
    }
}
