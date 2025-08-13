<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $superAdmin = Role::where('name', 'super_admin')->first();
        $lpsAdmin = Role::where('name', 'lps_admin')->first();
        $lpsCampo = Role::where('name', 'lps_campo')->first();

        // Usar updateOrCreate para evitar duplicados
        User::updateOrCreate(
            ['email' => 'superadmin@rutometro.com'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('admin123'),
                'role_id' => $superAdmin->id
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@lps.pe'],
            [
                'name' => 'LPS Admin',
                'password' => Hash::make('lps123'),
                'role_id' => $lpsAdmin->id
            ]
        );

        User::updateOrCreate(
            ['email' => 'juan@lps.pe'],
            [
                'name' => 'Juan Técnico',
                'password' => Hash::make('campo123'),
                'role_id' => $lpsCampo->id
            ]
        );

        User::updateOrCreate(
            ['email' => 'maria@lps.pe'],
            [
                'name' => 'María Campo',
                'password' => Hash::make('campo123'),
                'role_id' => $lpsCampo->id
            ]
        );
    }
}
