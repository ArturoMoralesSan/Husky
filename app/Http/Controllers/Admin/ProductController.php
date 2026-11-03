<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;

use App\Models\Product;

use App\Models\ProductVariant;
use App\Traits\StoresGalleryImages;

use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;

use Illuminate\Validation\ValidationException;

use Inertia\Inertia;

use Inertia\Response;

use Throwable;


class ProductController extends Controller

{
    use StoresGalleryImages;

    public function index(): Response

    {

        $products = Product::query()

            ->with(['gallery.images'])

            ->withCount('variants')

            ->orderBy('sort_order')

            ->orderByDesc('id')

            ->paginate(10)

            ->withQueryString();


        $products->getCollection()->transform(function (Product $product) {

            $images = $product->gallery?->images ?? collect();


            $primaryImage = $images->first(

                fn ($image) => $image->is_primary

            ) ?? $images->first();


            $product->setAttribute(

                'image_url',

                $primaryImage

                    ? Storage::disk('public')->url($primaryImage->file)

                    : null

            );


            return $product;

        });


        return Inertia::render('admin/products/Index', [

            'products' => $products,

        ]);

    }


    public function create(): Response

    {

        return Inertia::render('admin/products/Create');

    }


    public function store(Request $request): RedirectResponse

    {

        $validated = $this->validateProduct($request);


        $storedFiles = [];


        try {

            DB::transaction(function () use (

                $validated,

                $request,

                &$storedFiles

            ) {

                $product = Product::create([

                    'name' => $validated['name'],

                    'slug' => $this->uniqueSlug(

                        $validated['slug'] ?? null,

                        $validated['name']

                    ),

                    'short_description' => $validated['short_description'] ?? null,

                    'description' => $validated['description'] ?? null,

                    'material' => $validated['material'] ?? null,

                    'treatment' => $validated['treatment'] ?? null,

                    'retention' => $validated['retention'] ?? null,

                    'retention_unit' => $validated['retention_unit'] ?? null,

                    'is_active' => $validated['is_active'] ?? false,

                    'sort_order' => $validated['sort_order'] ?? 0,

                ]);


                $this->storeGalleryImages(

                    $product,

                    $request,

                    $validated,

                    $storedFiles

                );


                $this->storeVariants($product, $validated);

            });


        } catch (Throwable $exception) {

            $this->deleteStoredFiles($storedFiles);


            throw $exception;

        }


        return to_route('admin.products.index')

            ->with('success', 'Producto creado correctamente.');

    }


    public function edit(Product $product): Response

    {

        $product->load([

            'variants',

            'gallery.images',

        ]);


        $galleryImages = ($product->gallery?->images ?? collect())

            ->map(fn ($image) => [

                'id' => $image->id,

                'file' => $image->file,

                'url' => Storage::disk('public')->url($image->file),

                'alt' => $image->alt,

                'is_primary' => $image->is_primary,

                'sort_order' => $image->sort_order,

            ])

            ->values();


        return Inertia::render('admin/products/Edit', [

            'product' => $product,

            'gallery_images' => $galleryImages,

        ]);

    }


    public function update(

        Request $request,

        Product $product

    ): RedirectResponse {

        $validated = $this->validateProduct($request, $product);


        $storedFiles = [];

        $filesToDelete = [];


        try {

            DB::transaction(function () use (

                $request,

                $product,

                $validated,

                &$storedFiles,

                &$filesToDelete

            ) {

                $product->update([

                    'name' => $validated['name'],

                    'slug' => $this->uniqueSlug(

                        $validated['slug'] ?? null,

                        $validated['name'],

                        $product->id

                    ),

                    'short_description' => $validated['short_description'] ?? null,

                    'description' => $validated['description'] ?? null,

                    'material' => $validated['material'] ?? null,

                    'treatment' => $validated['treatment'] ?? null,

                    'retention' => $validated['retention'] ?? null,

                    'retention_unit' => $validated['retention_unit'] ?? null,

                    'is_active' => $validated['is_active'] ?? false,

                    'sort_order' => $validated['sort_order'] ?? 0,

                ]);


                $gallery = $product->gallery()->first();


                $metadata = $request->input(

                    'existing_gallery_metadata',

                    []

                );


                if (! is_array($metadata)) {

                    $metadata = [];

                }


                if ($gallery) {

                    foreach ($metadata as $item) {

                        if (! is_array($item) || empty($item['id'])) {

                            continue;

                        }


                        $image = $gallery->images()

                            ->whereKey($item['id'])

                            ->first();


                        if (! $image) {

                            continue;

                        }


                        $image->update([

                            'alt' => $item['alt'] ?? null,

                            'is_primary' => (bool) ($item['is_primary'] ?? false),

                            'sort_order' => (int) ($item['sort_order'] ?? 0),

                        ]);

                    }

                }


                $removeIds = $validated['remove_gallery_images'] ?? [];


                if ($gallery && $removeIds) {

                    $imagesToRemove = $gallery->images()

                        ->whereIn('id', $removeIds)

                        ->get();


                    foreach ($imagesToRemove as $image) {

                        $filesToDelete[] = $image->file;

                        $image->delete();

                    }

                }


                $this->storeGalleryImages(

                    $product,

                    $request,

                    $validated,

                    $storedFiles

                );


                $this->normalizePrimaryImage($product);


                

                if (array_key_exists('variants', $validated)) {

                    $this->syncVariants($product, $validated['variants']);

                }

            });


        } catch (Throwable $exception) {

            $this->deleteStoredFiles($storedFiles);


            throw $exception;

        }


        $this->deleteStoredFiles($filesToDelete);


        return to_route('admin.products.index')

            ->with('success', 'Producto actualizado correctamente.');

    }


    public function destroy(Product $product): RedirectResponse

    {

        $product->load(['gallery.images']);


        $filesToDelete = ($product->gallery?->images ?? collect())

            ->pluck('file')

            ->all();


        DB::transaction(function () use ($product) {

            $product->variants()->delete();


            if ($product->gallery) {

                $product->gallery->images()->delete();

                $product->gallery->delete();

            }


            $product->delete();

        });


        $this->deleteStoredFiles($filesToDelete);


        return to_route('admin.products.index')

            ->with('success', 'Producto eliminado correctamente.');

    }


    private function validateProduct(

        Request $request,

        ?Product $product = null

    ): array {

        $validated = $request->validate([

            'name' => ['required', 'string', 'max:255'],

            'slug' => ['nullable', 'string', 'max:255'],

            'short_description' => ['nullable', 'string', 'max:1000'],

            'description' => ['nullable', 'string'],

            'material' => ['nullable', 'string', 'max:255'],

            'treatment' => ['nullable', 'string', 'max:255'],

            'retention' => ['nullable', 'numeric', 'min:0'],

            'retention_unit' => ['nullable', 'string', 'max:50'],

            'is_active' => ['sometimes', 'boolean'],

            'sort_order' => ['sometimes', 'integer', 'min:0'],


            'gallery_images' => ['sometimes', 'array', 'max:20'],

            'gallery_images.*' => [

                'image',

                'mimes:jpg,jpeg,png,webp,avif',

                'max:5120',

            ],


            'gallery_metadata' => ['sometimes', 'array'],

            'gallery_metadata.*.alt' => ['nullable', 'string', 'max:255'],

            'gallery_metadata.*.is_primary' => ['sometimes', 'boolean'],

            'gallery_metadata.*.sort_order' => ['sometimes', 'integer', 'min:0'],


            'existing_gallery_metadata' => ['sometimes', 'array'],

            'existing_gallery_metadata.*.id' => ['required', 'integer'],

            'existing_gallery_metadata.*.alt' => ['nullable', 'string', 'max:255'],

            'existing_gallery_metadata.*.is_primary' => ['sometimes', 'boolean'],

            'existing_gallery_metadata.*.sort_order' => ['sometimes', 'integer', 'min:0'],


            'remove_gallery_images' => ['sometimes', 'array'],

            'remove_gallery_images.*' => ['integer', 'distinct'],


            'variants' => ['sometimes', 'array'],

            'variants.*.id' => ['sometimes', 'integer'],

            'variants.*.name' => ['nullable', 'string', 'max:255'],

            'variants.*.length' => ['nullable', 'numeric', 'min:0'],

            'variants.*.length_unit' => ['nullable', 'string', 'max:20'],

            'variants.*.diameter' => ['nullable', 'numeric', 'min:0'],

            'variants.*.diameter_unit' => ['nullable', 'string', 'max:20'],

            'variants.*.width' => ['nullable', 'numeric', 'min:0'],

            'variants.*.width_unit' => ['nullable', 'string', 'max:20'],

            'variants.*.thickness' => ['nullable', 'numeric', 'min:0'],

            'variants.*.thickness_unit' => ['nullable', 'string', 'max:20'],

            'variants.*.specifications' => ['sometimes', 'array'],

            'variants.*.is_active' => ['sometimes', 'boolean'],

            'variants.*.sort_order' => ['sometimes', 'integer', 'min:0'],

        ]);


        if ($product) {

            $gallery = $product->gallery;


            $validImageIds = $gallery

                ? $gallery->images()->pluck('id')->map(fn ($id) => (int) $id)->all()

                : [];


            foreach (

                $validated['existing_gallery_metadata'] ?? []

                as $item

            ) {

                if (! in_array((int) $item['id'], $validImageIds, true)) {

                    throw ValidationException::withMessages([

                        'existing_gallery_metadata' =>

                            'Una de las imágenes seleccionadas no pertenece a este producto.',

                    ]);

                }

            }


            foreach (

                $validated['remove_gallery_images'] ?? []

                as $id

            ) {

                if (! in_array((int) $id, $validImageIds, true)) {

                    throw ValidationException::withMessages([

                        'remove_gallery_images' =>

                            'Una de las imágenes que intentas eliminar no pertenece a este producto.',

                    ]);

                }

            }


            $validVariantIds = $product->variants()

                ->pluck('id')

                ->map(fn ($id) => (int) $id)

                ->all();


            foreach ($validated['variants'] ?? [] as $variant) {

                if (

                    isset($variant['id']) &&

                    ! in_array((int) $variant['id'], $validVariantIds, true)

                ) {

                    throw ValidationException::withMessages([

                        'variants' =>

                            'Una de las variantes seleccionadas no pertenece a este producto.',

                    ]);

                }

            }

        }


        return $validated;

    }


    private function storeVariants(Product $product, array $validated): void

    {

        foreach ($validated['variants'] ?? [] as $index => $variant) {

            $product->variants()->create([

                'name' => filled($variant['name'] ?? null)

                    ? $variant['name']

                    : 'Variante ' . ($index + 1),

                'length' => $variant['length'] ?? null,

                'length_unit' => $variant['length_unit'] ?? 'm',

                'diameter' => $variant['diameter'] ?? null,

                'diameter_unit' => $variant['diameter_unit'] ?? 'cm',

                'width' => $variant['width'] ?? null,

                'width_unit' => $variant['width_unit'] ?? 'cm',

                'thickness' => $variant['thickness'] ?? null,

                'thickness_unit' => $variant['thickness_unit'] ?? 'cm',

                'specifications' => $variant['specifications'] ?? [],

                'is_active' => $variant['is_active'] ?? true,

                'sort_order' => $variant['sort_order'] ?? $index,

            ]);

        }

    }


    private function syncVariants(Product $product, array $variants): void

    {

        $submittedIds = [];


        foreach (array_values($variants) as $index => $variant) {

            $data = [

                'name' => filled($variant['name'] ?? null)

                    ? $variant['name']

                    : 'Variante ' . ($index + 1),

                'length' => $variant['length'] ?? null,

                'length_unit' => $variant['length_unit'] ?? 'm',

                'diameter' => $variant['diameter'] ?? null,

                'diameter_unit' => $variant['diameter_unit'] ?? 'cm',

                'width' => $variant['width'] ?? null,

                'width_unit' => $variant['width_unit'] ?? 'cm',

                'thickness' => $variant['thickness'] ?? null,

                'thickness_unit' => $variant['thickness_unit'] ?? 'cm',

                'specifications' => $variant['specifications'] ?? [],

                'is_active' => $variant['is_active'] ?? true,

                'sort_order' => $index,

            ];


            if (! empty($variant['id'])) {

                $existing = $product->variants()

                    ->whereKey($variant['id'])

                    ->first();


                if ($existing) {

                    $existing->update($data);

                    $submittedIds[] = $existing->id;

                }

            } else {

                $created = $product->variants()->create($data);

                $submittedIds[] = $created->id;

            }

        }


        $product->variants()

            ->when(

                count($submittedIds) > 0,

                fn ($query) => $query->whereNotIn('id', $submittedIds)

            )

            ->when(

                count($submittedIds) === 0,

                fn ($query) => $query

            )

            ->delete();

    }


    private function deleteStoredFiles(array $paths): void

    {

        foreach (array_unique($paths) as $path) {

            if ($path && Storage::disk('public')->exists($path)) {

                Storage::disk('public')->delete($path);

            }

        }

    }


    private function uniqueSlug(

        ?string $slug,

        string $name,

        ?int $ignoreId = null

    ): string {

        $base = Str::slug($slug ?: $name);


        if ($base === '') {

            $base = 'producto';

        }


        $candidate = $base;

        $counter = 1;


        while (

            Product::query()

                ->where('slug', $candidate)

                ->when(

                    $ignoreId,

                    fn ($query) => $query->where('id', '!=', $ignoreId)

                )

                ->exists()

        ) {

            $candidate = $base . '-' . $counter;

            $counter++;

        }


        return $candidate;

    }

}
