<?php

namespace Database\Seeders;

use App\Models\Link;
use App\Models\Menu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Configuración
        |--------------------------------------------------------------------------
        */

        $configuration = Menu::updateOrCreate(
            ['name' => 'Configuración'],
            [
                'icon' => 'Settings',
                'order' => 1,
                'route' => null,
                'is_submenu' => true,
            ]
        );

        $this->link(
            $configuration,
            'Usuarios',
            'Users',
            1,
            'admin.users.index',
            'users.view'
        );

        $this->link(
            $configuration,
            'Roles',
            'Shield',
            2,
            'admin.roles.index',
            'roles.view'
        );

        $this->link(
            $configuration,
            'Permisos',
            'KeyRound',
            3,
            'admin.permissions.index',
            'permissions.view'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Crear o actualizar un enlace de menú
    |--------------------------------------------------------------------------
    */

    private function link(
        Menu $menu,
        string $name,
        string $icon,
        int $order,
        ?string $route,
        string $permission
    ): void {
        $permissionModel = Permission::where('name', $permission)
            ->where('guard_name', 'web')
            ->first();

        Link::updateOrCreate(
            [
                'menu_id' => $menu->id,
                'name' => $name,
            ],
            [
                'icon' => $icon,
                'order' => $order,
                'route' => $route,
                'permission_id' => $permissionModel?->id,
            ]
        );
    }
}
