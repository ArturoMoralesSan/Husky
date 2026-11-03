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
        | Catálogo
        |--------------------------------------------------------------------------
        */

        $catalog = Menu::updateOrCreate(
            ['name' => 'Catálogo'],
            [
                'icon' => 'Boxes',
                'order' => 1,
                'route' => null,
                'is_submenu' => true,
            ]
        );

        $this->link(
            $catalog,
            'Productos',
            'Package',
            1,
            'admin.products.index',
            'products.view'
        );

        $this->link(
            $catalog,
            'Servicios',
            'Wrench',
            2,
            'admin.services.index',
            'services.view'
        );

        $this->link(
            $catalog,
            'Proyectos',
            'FolderKanban',
            3,
            'admin.projects.index',
            'projects.view'
        );

        /*
        |--------------------------------------------------------------------------
        | Contenido
        |--------------------------------------------------------------------------
        */

        $content = Menu::updateOrCreate(
            ['name' => 'Contenido'],
            [
                'icon' => 'FileText',
                'order' => 2,
                'route' => null,
                'is_submenu' => true,
            ]
        );

        $this->link(
            $content,
            'Nosotros',
            'Building',
            1,
            'admin.about.index',
            'about.view'
        );

        $this->link(
            $content,
            'Banners',
            'PanelsTopLeft',
            2,
            'admin.banners.index',
            'banners.view'
        );

        $this->link(
            $content,
            'Contacto',
            'Contact',
            3,
            'admin.contact.index',
            'contact.view'
        );

        /*
        |--------------------------------------------------------------------------
        | Comercial
        |--------------------------------------------------------------------------
        */

        $commercial = Menu::updateOrCreate(
            ['name' => 'Comercial'],
            [
                'icon' => 'BriefcaseBusiness',
                'order' => 3,
                'route' => null,
                'is_submenu' => true,
            ]
        );

        $this->link(
            $commercial,
            'Clientes',
            'Users',
            1,
            'admin.clients.index',
            'clients.view'
        );

        $this->link(
            $commercial,
            'Cotizaciones',
            'ClipboardList',
            2,
            'admin.quotes.index',
            'quotes.view'
        );

        /*
        |--------------------------------------------------------------------------
        | Configuración
        |--------------------------------------------------------------------------
        */

        $configuration = Menu::updateOrCreate(
            ['name' => 'Configuración'],
            [
                'icon' => 'Settings',
                'order' => 4,
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
