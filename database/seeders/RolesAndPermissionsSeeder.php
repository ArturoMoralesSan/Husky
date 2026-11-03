<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Roles administrativos
        |--------------------------------------------------------------------------
        */

        $superadmin = Role::firstOrCreate([
            'name' => 'SuperAdmin',
            'guard_name' => 'web',
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Permisos
        |--------------------------------------------------------------------------
        */

        $permissions = [
            // Usuarios
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Roles
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // Permisos
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',

            // Productos
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            // Servicios
            'services.view',
            'services.create',
            'services.edit',
            'services.delete',

            // Proyectos
            'projects.view',
            'projects.create',
            'projects.edit',
            'projects.delete',

            // Clientes
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',

            // Banners
            'banners.view',
            'banners.create',
            'banners.edit',
            'banners.delete',

            // Nosotros
            'about.view',
            'about.create',
            'about.edit',

            // Contacto
            'contact.view',
            'contact.create',
            'contact.edit',

            // Cotizaciones
            'quotes.view',
            'quotes.create',
            'quotes.edit',
            'quotes.delete',
        ];

        /*
        |--------------------------------------------------------------------------
        | Crear permisos
        |--------------------------------------------------------------------------
        */

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SuperAdmin
        |--------------------------------------------------------------------------
        |
        | El acceso total se administra mediante Gate::before().
        | No necesita permisos asignados directamente.
        |
        */

        $superadmin->syncPermissions([]);

        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        |
        | Puede administrar los módulos y registros del sistema.
        |
        */

        $admin->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
