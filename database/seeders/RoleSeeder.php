<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'Administrador']);
        Role::firstOrCreate(['name' => 'Recepcion']);
        Role::firstOrCreate(['name' => 'Veterinario']);
        Role::firstOrCreate(['name' => 'Auxiliar']);

        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@veterinaria.test',
            'password' => bcrypt('Asdf123#'),
        ]);

        $user->assignRole('Administrador');
    }
}
