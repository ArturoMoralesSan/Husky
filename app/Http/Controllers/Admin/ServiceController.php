<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Traits\StoresGalleryImages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    use StoresGalleryImages;

    public function index(Request $request): Response
    {
        $query = Service::query()
            ->with(['gallery.images'])
            ->orderBy('sort_order')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if (
            $request->filled('status') &&
            in_array((string) $request->input('status'), ['0', '1'], true)
        ) {
            $query->where(
                'is_active',
                $request->boolean('status')
            );
        }

        $services = $query
            ->paginate(10)
            ->withQueryString();

        $services->getCollection()->transform(
            function (Service $service) {
                $images = $service->gallery?->images ?? collect();

                $primaryImage = $images->first(
                    fn ($image) => $image->is_primary
                ) ?? $images->first();

                $service->setAttribute(
                    'image_url',
                    $primaryImage
                        ? Storage::disk('public')->url($primaryImage->file)
                        : null
                );

                $service->setAttribute('images_count', $images->count());

                return $service;
            }
        );

        return Inertia::render('admin/services/Index', [
            'services' => $services,
            'filters' => [
                'search' => $request->input('search', ''),
                'category' => $request->input('category', ''),
                'status' => $request->input('status', ''),
            ],
            'categories' => [
                [
                    'value' => Service::CATEGORY_PROJECTS,
                    'label' => 'Proyectos',
                ],
                [
                    'value' => Service::CATEGORY_MAQUILA,
                    'label' => 'Maquila',
                ],
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/services/Create', [
            'categories' => [
                [
                    'value' => Service::CATEGORY_PROJECTS,
                    'label' => 'Proyectos',
                ],
                [
                    'value' => Service::CATEGORY_MAQUILA,
                    'label' => 'Maquila',
                ],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateService($request);
        $storedFiles = [];

        try {
            DB::transaction(function () use (
                $request,
                $validated,
                &$storedFiles
            ) {
                $service = Service::create([
                    'name' => $validated['name'],
                    'slug' => $this->uniqueSlug(
                        $validated['slug'] ?? null,
                        $validated['name']
                    ),
                    'category' => $validated['category'],
                    'short_description' => $validated['short_description'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'is_active' => $validated['is_active'],
                    'sort_order' => $validated['sort_order'],
                ]);

                $this->storeGalleryImages(
                    $service,
                    $request,
                    $validated,
                    $storedFiles
                );
            });
        } catch (\Throwable $exception) {
            $this->deleteStoredFiles($storedFiles);

            throw $exception;
        }

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Servicio creado correctamente.');
    }

    public function edit(Service $service): Response
    {
        $service->load(['gallery.images']);
        $images = $service->gallery?->images ?? collect();

        return Inertia::render('admin/services/Edit', [
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'slug' => $service->slug,
                'category' => $service->category,
                'short_description' => $service->short_description,
                'description' => $service->description,
                'is_active' => $service->is_active,
                'sort_order' => $service->sort_order,
                'gallery_images' => $images->map(
                    fn ($image) => [
                        'id' => $image->id,
                        'file' => $image->file,
                        'url' => Storage::disk('public')->url($image->file),
                        'alt' => $image->alt,
                        'is_primary' => $image->is_primary,
                        'sort_order' => $image->sort_order,
                    ]
                )->values(),
            ],
            'categories' => [
                [
                    'value' => Service::CATEGORY_PROJECTS,
                    'label' => 'Proyectos',
                ],
                [
                    'value' => Service::CATEGORY_MAQUILA,
                    'label' => 'Maquila',
                ],
            ],
        ]);
    }

    public function update(
        Request $request,
        Service $service
    ): RedirectResponse {
        $validated = $this->validateService($request, $service);

        $storedFiles = [];
        $filesToDelete = [];

        try {
            DB::transaction(function () use (
                $request,
                $service,
                $validated,
                &$storedFiles,
                &$filesToDelete
            ) {
                $service->update([
                    'name' => $validated['name'],
                    'slug' => $this->uniqueSlug(
                        $validated['slug'] ?? null,
                        $validated['name'],
                        $service->id
                    ),
                    'category' => $validated['category'],
                    'short_description' => $validated['short_description'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'is_active' => $validated['is_active'],
                    'sort_order' => $validated['sort_order'],
                ]);

                $gallery = $service->gallery()->first();

                if ($gallery) {
                    $gallery->update(['name' => $service->name]);
                }

                $metadata = $validated['gallery_metadata'] ?? [];

                foreach ($metadata as $item) {
                    if (empty($item['id'])) {
                        continue;
                    }

                    $image = $service->gallery
                        ?->images()
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

                $deleteIds = $validated['delete_gallery_images'] ?? [];

                if (! empty($deleteIds)) {
                    $imagesToRemove = $service->gallery
                        ?->images()
                        ->whereIn('id', $deleteIds)
                        ->get() ?? collect();

                    foreach ($imagesToRemove as $image) {
                        $filesToDelete[] = $image->file;
                        $image->delete();
                    }
                }

                $this->storeGalleryImages(
                    $service,
                    $request,
                    $validated,
                    $storedFiles
                );

                $this->normalizePrimaryImage($service);
            });
        } catch (\Throwable $exception) {
            $this->deleteStoredFiles($storedFiles);

            throw $exception;
        }

        $this->deleteStoredFiles($filesToDelete);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $files = $service->gallery
            ?->images()
            ->pluck('file')
            ->all() ?? [];

        DB::transaction(function () use ($service) {
            $gallery = $service->gallery;

            if ($gallery) {
                $gallery->images()->delete();
                $gallery->delete();
            }

            $service->delete();
        });

        $this->deleteStoredFiles($files);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Servicio eliminado correctamente.');
    }

    private function validateService(
        Request $request,
        ?Service $service = null
    ): array {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('services', 'slug')->ignore($service?->id),
            ],

            'category' => [
                'required',
                'string',
                Rule::in(Service::CATEGORIES),
            ],

            'short_description' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],

            'gallery_images' => ['sometimes', 'array'],

            'gallery_images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp,avif',
                'max:5120',
            ],

            'gallery_metadata' => ['sometimes', 'array'],
            'gallery_metadata.*.id' => ['sometimes', 'integer'],
            'gallery_metadata.*.alt' => ['nullable', 'string', 'max:255'],
            'gallery_metadata.*.is_primary' => ['sometimes', 'boolean'],
            'gallery_metadata.*.sort_order' => ['sometimes', 'integer', 'min:0'],

            'delete_gallery_images' => ['sometimes', 'array'],
            'delete_gallery_images.*' => ['integer'],
        ]);
    }

    private function uniqueSlug(
        ?string $slug,
        string $name,
        ?int $ignoreId = null
    ): string {
        $base = Str::slug($slug ?: $name);

        if ($base === '') {
            $base = 'servicio';
        }

        $candidate = $base;
        $counter = 1;

        while (
            Service::query()
                ->where('slug', $candidate)
                ->when(
                    $ignoreId !== null,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $candidate = $base . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }

    private function deleteStoredFiles(array $files): void
    {
        foreach (array_unique($files) as $file) {
            if (
                is_string($file) &&
                $file !== '' &&
                Storage::disk('public')->exists($file)
            ) {
                Storage::disk('public')->delete($file);
            }
        }
    }
}
