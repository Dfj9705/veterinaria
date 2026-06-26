<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Permisos por módulo
        |--------------------------------------------------------------------------
        */

        $customerPermissions = [
            'view_customers',
            'create_customers',
            'update_customers',
            'delete_customers',
        ];

        $petPermissions = [
            'view_pets',
            'create_pets',
            'update_pets',
            'delete_pets',
        ];

        $servicePermissions = [
            'view_services',
            'create_services',
            'update_services',
            'delete_services',
        ];

        $appointmentPermissions = [
            'view_appointments',
            'create_appointments',
            'update_appointments',
            'delete_appointments',
        ];

        $medicalRecordPermissions = [
            'view_medical_records',
            'create_medical_records',
            'update_medical_records',
            'delete_medical_records',
        ];

        $prescriptionPermissions = [
            'view_prescriptions',
            'create_prescriptions',
            'update_prescriptions',
            'delete_prescriptions',
        ];

        $speciesPermissions = [
            'view_species',
            'create_species',
            'update_species',
            'delete_species',
        ];

        $breedPermissions = [
            'view_breeds',
            'create_breeds',
            'update_breeds',
            'delete_breeds',
        ];

        $quotationPermissions = [
            'view_any_quotation',
            'view_quotation',
            'create_quotation',
            'update_quotation',
            'delete_quotation',
            'delete_any_quotation',
            'print_quotation',
        ];

        $inventoryCategoryPermissions = [
            'view_inventory_categories',
            'create_inventory_categories',
            'update_inventory_categories',
            'delete_inventory_categories',
        ];

        $unitPermissions = [
            'view_units',
            'create_units',
            'update_units',
            'delete_units',
        ];

        $productPermissions = [
            'view_products',
            'create_products',
            'update_products',
            'delete_products',
        ];

        $inventoryMovementPermissions = [
            'view_inventory_movements',
            'create_inventory_movements',
            'update_inventory_movements',
            'delete_inventory_movements',
        ];

        $systemPermissions = [
            'view_reports',
            'manage_users',
            'manage_roles',
            'view_dashboard',
        ];

        $permissions = [
            ...$customerPermissions,
            ...$petPermissions,
            ...$servicePermissions,
            ...$appointmentPermissions,
            ...$medicalRecordPermissions,
            ...$prescriptionPermissions,
            ...$speciesPermissions,
            ...$breedPermissions,
            ...$quotationPermissions,
            ...$inventoryCategoryPermissions,
            ...$unitPermissions,
            ...$productPermissions,
            ...$inventoryMovementPermissions,
            ...$systemPermissions,
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $admin = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'web',
        ]);

        $recepcion = Role::firstOrCreate([
            'name' => 'Recepción',
            'guard_name' => 'web',
        ]);

        $veterinario = Role::firstOrCreate([
            'name' => 'Veterinario',
            'guard_name' => 'web',
        ]);

        $auxiliar = Role::firstOrCreate([
            'name' => 'Auxiliar',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Asignación de permisos
        |--------------------------------------------------------------------------
        */

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

            'view_inventory_categories',
            'view_units',
            'view_products',
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

            'view_inventory_categories',
            'view_units',
            'view_products',

            'view_inventory_movements',
            'create_inventory_movements',
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

            'view_inventory_categories',
            'view_units',
            'view_products',

            'view_inventory_movements',
            'create_inventory_movements',
        ]);

        User::query()->first()?->assignRole('Administrador');
    }
}