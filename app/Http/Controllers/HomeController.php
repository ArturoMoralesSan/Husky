<?php

namespace App\Http\Controllers;

use App\Models\About;
use App\Models\Banner;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Banner principal
        |--------------------------------------------------------------------------
        */

        $banner = Banner::query()
            ->where('position', 'home')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if ($banner) {
            $banner->image_url = Storage::disk('public')
                ->url($banner->image);
        }

        /*
        |--------------------------------------------------------------------------
        | Productos
        |--------------------------------------------------------------------------
        */

        $products = Product::query()
            ->with(['gallery.images'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(5)
            ->get([
                'id',
                'name',
                'slug',
                'short_description',
            ])
            ->map(function (Product $product) {
                $images = $product->gallery?->images ?? collect();

                $image = $images->firstWhere('is_primary', true);

                if (!$image) {
                    $image = $images->first();
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'short_description' => $product->short_description,
                    'image_url' => $image
                        ? Storage::disk('public')->url($image->file)
                        : null,
                    'image_alt' => $image?->alt ?: $product->name,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Servicios
        |--------------------------------------------------------------------------
        */

        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Proyectos
        |--------------------------------------------------------------------------
        */

        $projects = Project::query()
            ->with(['gallery.images'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(5)
            ->get([
                'id',
                'name',
                'slug',
                'description',
            ])
            ->map(function (Project $project) {
                $images = $project->gallery?->images ?? collect();

                $image = $images->firstWhere('is_primary', true);

                if (!$image) {
                    $image = $images->first();
                }

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'slug' => $project->slug,
                    'short_description' => $project->description,
                    'image_url' => $image
                        ? Storage::disk('public')->url($image->file)
                        : null,
                    'image_alt' => $image?->alt ?: $project->name,
                ];
            })
            ->values();

            /*
            |--------------------------------------------------------------------------
            | Acerca de nosotros
            |--------------------------------------------------------------------------
            */

            $about = About::query()
                ->with(['gallery.images'])
                ->first();

            if ($about) {
                $images = $about->gallery?->images ?? collect();

                $about = [
                    'id' => $about->id,
                    'history' => $about->history,
                    'gallery_images' => $images
                        ->map(function ($image) {
                            return [
                                'id' => $image->id,
                                'file' => $image->file,
                                'url' => Storage::disk('public')->url($image->file),
                                'alt' => $image->alt,
                                'is_primary' => $image->is_primary,
                                'sort_order' => $image->sort_order,
                            ];
                        })
                        ->values(),
                ];
            }
        /*
        |--------------------------------------------------------------------------
        | Clientes
        |--------------------------------------------------------------------------
        */

        $clients = Client::query()
            ->orderBy('id')
            ->get()
            ->map(function (Client $client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'logo_url' => $client->logo
                        ? Storage::disk('public')->url($client->logo)
                        : null,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Contacto
        |--------------------------------------------------------------------------
        */

        $contact = Contact::query()->first();

        /*
        |--------------------------------------------------------------------------
        | Respuesta Inertia
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Welcome', [
            'banner' => $banner,
            'products' => $products,
            'services' => $services,
            'projects' => $projects,
            'clients' => $clients,
            'contact' => $contact,
            'about' => $about,
        ]);
    }

    public function nosotros(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Banner de Nosotros
        |--------------------------------------------------------------------------
        */

        $banner = Banner::query()
            ->where('position', 'about')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if ($banner) {
            $banner->image_url = Storage::disk('public')
                ->url($banner->image);
        }

        /*
        |--------------------------------------------------------------------------
        | Información de Nosotros
        |--------------------------------------------------------------------------
        */

        $about = About::query()
            ->with([
                'values',
                'gallery.images',
            ])
            ->first();

        if ($about) {
            $about->setAttribute(
                'gallery_images',
                $about->gallery?->images
                    ->map(function ($image) {
                        return [
                            'id' => $image->id,
                            'file' => $image->file,
                            'url' => Storage::disk('public')
                                ->url($image->file),
                            'alt' => $image->alt,
                            'is_primary' => $image->is_primary,
                            'sort_order' => $image->sort_order,
                        ];
                    })
                    ->values()
                    ->all() ?? []
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Respuesta Inertia
        |--------------------------------------------------------------------------
        */

        return Inertia::render('About', [
            'banner' => $banner,
            'about' => $about,
        ]);
    }

    public function products(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Banner de Productos
        |--------------------------------------------------------------------------
        */

        $banner = Banner::query()
            ->where('position', 'products')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if ($banner) {
            $banner->image_url = Storage::disk('public')
                ->url($banner->image);
        }

        /*
        |--------------------------------------------------------------------------
        | Catálogo de Productos
        |--------------------------------------------------------------------------
        */

        $products = Product::query()
            ->with(['gallery.images'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'name',
                'slug',
                'short_description',
            ])
            ->map(function (Product $product) {
                $images = $product->gallery?->images ?? collect();

                $image = $images->firstWhere('is_primary', true);

                if (!$image) {
                    $image = $images->first();
                }

                return [
                    'id' => $product->id,
                    'slug' => $product->slug,
                    'title' => $product->name,
                    'description' => $product->short_description,
                    'image' => $image
                        ? Storage::disk('public')->url($image->file)
                        : null,
                    'imageAlt' => $image?->alt ?: $product->name,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Respuesta Inertia
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Products', [
            'banner' => $banner,
            'products' => $products,
        ]);
    }

    public function services(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Banner de Servicios
        |--------------------------------------------------------------------------
        */

        $banner = Banner::query()
            ->where('position', 'services')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if ($banner) {
            $banner->image_url = Storage::disk('public')
                ->url($banner->image);
        }

        /*
        |--------------------------------------------------------------------------
        | Catálogo de Servicios
        |--------------------------------------------------------------------------
        */

        $services = Service::query()
            ->with(['gallery.images'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'name',
                'slug',
                'description',
            ])
            ->map(function (Service $service) {
                $images = $service->gallery?->images ?? collect();

                $image = $images->firstWhere('is_primary', true);

                if (!$image) {
                    $image = $images->first();
                }

                return [
                    'id' => $service->id,
                    'slug' => $service->slug,
                    'title' => $service->name,
                    'description' => $service->description,
                    'image' => $image
                        ? Storage::disk('public')->url($image->file)
                        : null,
                    'imageAlt' => $image?->alt ?: $service->name,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Respuesta Inertia
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Services', [
            'banner' => $banner,
            'services' => $services,
        ]);
    }

    public function projects(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Banner de Proyectos
        |--------------------------------------------------------------------------
        */

        $banner = Banner::query()
            ->where('position', 'projects')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if ($banner) {
            $banner->image_url = Storage::disk('public')
                ->url($banner->image);
        }

        /*
        |--------------------------------------------------------------------------
        | Catálogo de Proyectos
        |--------------------------------------------------------------------------
        */

        $projects = Project::query()
            ->with(['gallery.images'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get([
                'id',
                'name',
                'slug',
                'description',
            ])
            ->map(function (Project $project) {
                $images = $project->gallery?->images ?? collect();

                $image = $images->firstWhere('is_primary', true);

                if (!$image) {
                    $image = $images->first();
                }

                return [
                    'id' => $project->id,
                    'slug' => $project->slug,
                    'title' => $project->name,
                    'description' => $project->description,
                    'image' => $image
                        ? Storage::disk('public')->url($image->file)
                        : null,
                    'imageAlt' => $image?->alt ?: $project->name,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Respuesta Inertia
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Projects', [
            'banner' => $banner,
            'projects' => $projects,
        ]);
    }

    public function showProduct(string $slug): Response
    {
        $product = Product::query()
            ->with([
                'variants' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
                'gallery.images',
            ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $images = $product->gallery?->images ?? collect();

        $gallery = $images->map(function ($image) use ($product) {
            return [
                'id' => $image->id,
                'url' => Storage::disk('public')->url($image->file),
                'alt' => $image->alt ?: $product->name,
                'is_primary' => $image->is_primary,
            ];
        })->values();

        $primaryImage = $gallery->firstWhere('is_primary', true);

        $variants = $product->variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'name' => $variant->name,
                'length' => $variant->length,
                'length_unit' => $variant->length_unit,
                'diameter' => $variant->diameter,
                'diameter_unit' => $variant->diameter_unit,
                'width' => $variant->width,
                'width_unit' => $variant->width_unit,
                'thickness' => $variant->thickness,
                'thickness_unit' => $variant->thickness_unit,
                'specifications' => $variant->specifications ?? [],
            ];
        })->values();

        return Inertia::render('ProductShow', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'short_description' => $product->short_description,
                'description' => $product->description,
                'material' => $product->material,
                'treatment' => $product->treatment,
                'retention' => $product->retention,
                'retention_unit' => $product->retention_unit,
                'gallery' => $gallery,
                'main_image' => $primaryImage['url'] ?? $gallery->first()['url'] ?? null,
                'variants' => $variants,
            ],
        ]);
    }

    public function showService(string $slug): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Servicio
        |--------------------------------------------------------------------------
        */

        $service = Service::query()
            ->with([
                'gallery.images',
            ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Galería de imágenes
        |--------------------------------------------------------------------------
        */

        $images = $service->gallery?->images ?? collect();

        $gallery = $images->map(function ($image) use ($service) {
            return [
                'id' => $image->id,
                'url' => Storage::disk('public')->url($image->file),
                'alt' => $image->alt ?: $service->name,
                'is_primary' => $image->is_primary,
            ];
        })->values();

        /*
        |--------------------------------------------------------------------------
        | Imagen principal
        |--------------------------------------------------------------------------
        */

        $primaryImage = $gallery->firstWhere('is_primary', true);

        /*
        |--------------------------------------------------------------------------
        | Respuesta Inertia
        |--------------------------------------------------------------------------
        */

        return Inertia::render('ServiceShow', [
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'slug' => $service->slug,
                'description' => $service->description,
                'gallery' => $gallery,
                'main_image' => $primaryImage['url'] ?? $gallery->first()['url'] ?? null,
            ],
        ]);
    }

    public function showProject(string $slug): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Proyecto
        |--------------------------------------------------------------------------
        */

        $project = Project::query()
            ->with([
                'gallery.images',
            ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Galería de imágenes
        |--------------------------------------------------------------------------
        */

        $images = $project->gallery?->images ?? collect();

        $gallery = $images->map(function ($image) use ($project) {
            return [
                'id' => $image->id,
                'url' => Storage::disk('public')->url($image->file),
                'alt' => $image->alt ?: $project->name,
                'is_primary' => $image->is_primary,
            ];
        })->values();

        /*
        |--------------------------------------------------------------------------
        | Imagen principal
        |--------------------------------------------------------------------------
        */

        $primaryImage = $gallery->firstWhere('is_primary', true);

        /*
        |--------------------------------------------------------------------------
        | Respuesta Inertia
        |--------------------------------------------------------------------------
        */

        return Inertia::render('ProjectShow', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'slug' => $project->slug,
                'description' => $project->description,
                'gallery' => $gallery,
                'main_image' => $primaryImage['url'] ?? $gallery->first()['url'] ?? null,
            ],
        ]);
    }
}
