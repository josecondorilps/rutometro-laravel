<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            ClienteSeeder::class,
            ProyectoSeeder::class,
            UserSeeder::class,
            EquipoSeeder::class,
        ]);
    }
}
