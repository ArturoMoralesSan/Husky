<?php

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\AboutController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Página pública
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/nosotros', [HomeController::class, 'nosotros'])->name('nosotros');

Route::get('/productos', [HomeController::class, 'products'])->name('products');

// Detalle individual de producto
Route::get('/productos/{product:slug}', [HomeController::class, 'showProduct'])->name('products.show');

Route::get('/servicios', [HomeController::class, 'services'])->name('services.index');

Route::get('/servicios/{slug}', [HomeController::class, 'showService'])->name('services.show');


Route::get('/proyectos', [HomeController::class, 'projects'])->name('projects.index');

Route::get('/proyectos/{slug}', [HomeController::class, 'showProject'])->name('projects.show');


Route::get('/quotes/options', [QuoteController::class, 'options'])->name('quotes.options');


Route::post('/quotes', [QuoteController::class, 'store'])
    ->name('quotes.store');

Route::get('/preguntas-frecuentes', [HomeController::class, 'faqs'])
    ->name('faqs.index');
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

        Route::resource('services', ServiceController::class)
            ->middleware([
                'index' => 'permission:services.view',
                'create' => 'permission:services.create',
                'store' => 'permission:services.create',
                'edit' => 'permission:services.edit',
                'update' => 'permission:services.edit',
                'destroy' => 'permission:services.delete',
            ]);
        Route::resource('clients', ClientController::class)
            ->middleware([
                'index' => 'permission:clients.view',
                'create' => 'permission:clients.create',
                'store' => 'permission:clients.create',
                'edit' => 'permission:clients.edit',
                'update' => 'permission:clients.edit',
                'destroy' => 'permission:clients.delete',
            ]);
        
        Route::resource('projects', ProjectController::class)
            ->middleware([
                'index' => 'permission:projects.view',
                'create' => 'permission:projects.create',
                'store' => 'permission:projects.create',
                'edit' => 'permission:projects.edit',
                'update' => 'permission:projects.edit',
                'destroy' => 'permission:projects.delete',
            ]);

        Route::get('about', [AboutController::class, 'index'])
            ->middleware('permission:about.view')
            ->name('about.index');

        Route::get('about/create', [AboutController::class, 'create'])
            ->middleware('permission:about.create')
            ->name('about.create');

        Route::post('about', [AboutController::class, 'store'])
            ->middleware('permission:about.create')
            ->name('about.store');

        Route::get('about/edit', [AboutController::class, 'edit'])
            ->middleware('permission:about.edit')
            ->name('about.edit');

        Route::put('about', [AboutController::class, 'update'])
            ->middleware('permission:about.edit')
            ->name('about.update');

        
        /*
        |--------------------------------------------------------------------------
        | Contacto
        |--------------------------------------------------------------------------
        */

        Route::get('contact', [ContactController::class, 'index'])
            ->middleware('permission:contact.view')
            ->name('contact.index');

        Route::get('contact/create', [ContactController::class, 'create'])
            ->middleware('permission:contact.create')
            ->name('contact.create');

        Route::post('contact', [ContactController::class, 'store'])
            ->middleware('permission:contact.create')
            ->name('contact.store');

        Route::get('contact/edit', [ContactController::class, 'edit'])
            ->middleware('permission:contact.edit')
            ->name('contact.edit');

        Route::put('contact', [ContactController::class, 'update'])
            ->middleware('permission:contact.edit')
            ->name('contact.update');

        
        Route::get('quotes', [QuoteController::class, 'index'])
            ->middleware('permission:quotes.view')
            ->name('quotes.index');

        Route::patch('quotes/{quote}/status', [QuoteController::class, 'updateStatus'])
            ->middleware('permission:quotes.edit')
            ->name('quotes.update-status');

           Route::resource('faqs', FaqController::class)
            ->middleware([
                'index' => 'permission:faqs.view',
                'create' => 'permission:faqs.create',
                'store' => 'permission:faqs.create',
                'edit' => 'permission:faqs.edit',
                'update' => 'permission:faqs.edit',
                'destroy' => 'permission:faqs.delete',
            ]);
     
    });



require __DIR__.'/settings.php';
