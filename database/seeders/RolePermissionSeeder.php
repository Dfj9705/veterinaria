<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view_customers',
            'create_customers',
            'update_customers',
            'delete_customers',
            'view_pets',
            'create_pets',
            'update_pets',
            'delete_pets',
            'view_services',
            'create_services',
            'update_services',
            'delete_services',
            'view_appointments',
            'create_appointments',
            'update_appointments',
            'delete_appointments',
            'view_medical_records',
            'create_medical_records',
            'update_medical_records',
            'delete_medical_records',
            'view_prescriptions',
            'create_prescriptions',
            'update_prescriptions',
            'delete_prescriptions',
            'view_reports',
            'manage_users',
            'manage_roles',
            'view_dashboard',
            'view_species',
            'create_species',
            'update_species',
            'delete_species',
            'view_breeds',
            'create_breeds',
            'update_breeds',
            'delete_breeds',
            'view_any_quotation',
            'view_quotation',
            'create_quotation',
            'update_quotation',
            'delete_quotation',
            'delete_any_quotation',
            'print_quotation',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $recepcion = Role::firstOrCreate(['name' => 'Recepción', 'guard_name' => 'web']);
        $veterinario = Role::firstOrCreate(['name' => 'Veterinario', 'guard_name' => 'web']);
        $auxiliar = Role::firstOrCreate(['name' => 'Auxiliar', 'guard_name' => 'web']);

        $admin->syncPermissions($permissions);

        $recepcion->syncPermissions([
            'view_dashboard',

            'view_customers',
            'create_customers',
            'update_customers',
            'view_pets',
            'create_pets',
            'update_pets',

            'view_species',
            'view_breeds',

            'view_services',
            'view_appointments',
            'create_appointments',
            'update_appointments',
            'view_reports',
            'view_any_quotation',
            'view_quotation',
            'create_quotation',
            'update_quotation',
            'delete_quotation',
            'delete_any_quotation',
            'print_quotation',
        ]);

        $veterinario->syncPermissions([
            'view_dashboard',

            'view_customers',
            'view_pets',
            'update_pets',

            'view_species',
            'view_breeds',

            'view_services',
            'view_appointments',
            'update_appointments',

            'view_medical_records',
            'create_medical_records',
            'update_medical_records',
            'view_prescriptions',
            'create_prescriptions',
            'update_prescriptions',

            'view_reports',

            'view_any_quotation',
            'view_quotation',
            'create_quotation',
            'update_quotation',
            'delete_quotation',
            'delete_any_quotation',
            'print_quotation',
        ]);

        $auxiliar->syncPermissions([
            'view_dashboard',

            'view_customers',
            'view_pets',

            'view_species',
            'view_breeds',

            'view_services',
            'view_appointments',
            'view_medical_records',
            'view_prescriptions',
        ]);

        User::query()->first()?->assignRole('Administrador');
    }
}
