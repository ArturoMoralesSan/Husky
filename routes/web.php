<?php

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Página pública
|--------------------------------------------------------------------------
*/

Route::inertia('/', 'Welcome')->name('home');

/*
|--------------------------------------------------------------------------
| Administración
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        |
        | Página de bienvenida de Industrias Husky.
        |
        */

        Route::inertia('dashboard', 'Dashboard')
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Usuarios
        |--------------------------------------------------------------------------
        */

        Route::resource('users', UserController::class)
            ->middleware([
                'index' => 'permission:users.view',
                'create' => 'permission:users.create',
                'store' => 'permission:users.create',
                'edit' => 'permission:users.edit',
                'update' => 'permission:users.edit',
                'destroy' => 'permission:users.delete',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        Route::resource('roles', RoleController::class)
            ->middleware([
                'index' => 'permission:roles.view',
                'create' => 'permission:roles.create',
                'store' => 'permission:roles.create',
                'edit' => 'permission:roles.edit',
                'update' => 'permission:roles.edit',
                'destroy' => 'permission:roles.delete',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Permisos
        |--------------------------------------------------------------------------
        */

        Route::resource('permissions', PermissionController::class)
            ->middleware([
                'index' => 'permission:permissions.view',
                'create' => 'permission:permissions.create',
                'store' => 'permission:permissions.create',
                'edit' => 'permission:permissions.edit',
                'update' => 'permission:permissions.edit',
                'destroy' => 'permission:permissions.delete',
            ]);
        
        Route::resource('banners', BannerController::class)
            ->middleware([
                'index' => 'permission:banners.view',
                'create' => 'permission:banners.create',
                'store' => 'permission:banners.create',
                'edit' => 'permission:banners.edit',
                'update' => 'permission:banners.edit',
                'destroy' => 'permission:banners.delete',
            ]);

        Route::resource('products', ProductController::class)
            ->middleware([
                'index' => 'permission:products.view',
                'create' => 'permission:products.create',
                'store' => 'permission:products.create',
                'edit' => 'permission:products.edit',
                'update' => 'permission:products.edit',
                'destroy' => 'permission:products.delete',
            ]);
    });

require __DIR__.'/settings.php';
