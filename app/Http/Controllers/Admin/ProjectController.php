<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Traits\StoresGalleryImages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    use StoresGalleryImages;

    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $projects = Project::query()
            ->with(['gallery.images'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Project $project) => [
                'id' => $project->id,
                'name' => $project->name,
                'slug' => $project->slug,
                'category' => $project->category,
                'description' => $project->description,
                'location' => $project->location,
                'year' => $project->year,
                'is_active' => $project->is_active,
                'sort_order' => $project->sort_order,
                'created_at' => $project->created_at?->format('d/m/Y'),

                'gallery_images' => $project->gallery?->images
                    ->map(fn ($image) => [
                        'id' => $image->id,
                        'file' => $image->file,
                        'url' => Storage::disk('public')->url($image->file),
                        'alt' => $image->alt,
                        'is_primary' => $image->is_primary,
                        'sort_order' => $image->sort_order,
                    ])
                    ->values()
                    ->all() ?? [],
            ]);

        return Inertia::render('admin/projects/Index', [
            'projects' => $projects,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/projects/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeGalleryInput($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],

            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'gallery_metadata' => ['nullable', 'array'],
            'gallery_metadata.*.alt' => [
                'nullable',
                'string',
                'max:255',
            ],
            'gallery_metadata.*.is_primary' => [
                'nullable',
                'boolean',
            ],
            'gallery_metadata.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        $validated['slug'] = $this->generateSlug(
            $validated['name']
        );

        $storedFiles = [];

        DB::transaction(function () use (
            $request,
            $validated,
            &$storedFiles
        ) {
            $project = Project::create(
                collect($validated)
                    ->except([
                        'gallery_images',
                        'gallery_metadata',
                    ])
                    ->toArray()
            );

            $this->storeGalleryImages(
                $project,
                $request,
                $validated,
                $storedFiles
            );

            $this->normalizePrimaryImage($project);
        });

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Proyecto creado correctamente.');
    }

    public function edit(Project $project): Response
    {
        $project->load([
            'gallery.images' => function ($query) {
                $query->orderBy('sort_order')
                    ->orderBy('id');
            },
        ]);

        return Inertia::render('admin/projects/Edit', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'slug' => $project->slug,
                'category' => $project->category,
                'description' => $project->description,
                'location' => $project->location,
                'year' => $project->year,
                'is_active' => $project->is_active,
                'sort_order' => $project->sort_order,

                'gallery_images' => $project->gallery?->images
                    ->map(fn ($image) => [
                        'id' => $image->id,
                        'file' => $image->file,
                        'url' => Storage::disk('public')->url($image->file),
                        'alt' => $image->alt,
                        'is_primary' => $image->is_primary,
                        'sort_order' => $image->sort_order,
                    ])
                    ->values()
                    ->all() ?? [],
            ],
        ]);
    }

    public function update(
        Request $request,
        Project $project
    ): RedirectResponse {
        $this->normalizeGalleryInput($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],

            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'gallery_metadata' => ['nullable', 'array'],
            'gallery_metadata.*.alt' => [
                'nullable',
                'string',
                'max:255',
            ],
            'gallery_metadata.*.is_primary' => [
                'nullable',
                'boolean',
            ],
            'gallery_metadata.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'remove_gallery_images' => ['nullable', 'array'],
            'remove_gallery_images.*' => [
                'integer',
                'exists:gallery_images,id',
            ],

            'existing_gallery_metadata' => ['nullable', 'array'],
            'existing_gallery_metadata.*.id' => [
                'required',
                'integer',
                'exists:gallery_images,id',
            ],
            'existing_gallery_metadata.*.alt' => [
                'nullable',
                'string',
                'max:255',
            ],
            'existing_gallery_metadata.*.is_primary' => [
                'nullable',
                'boolean',
            ],
            'existing_gallery_metadata.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        $validated['slug'] = $this->generateSlug(
            $validated['name'],
            $project->id
        );

        $storedFiles = [];

        DB::transaction(function () use (
            $request,
            $project,
            $validated,
            &$storedFiles
        ) {
            $project->update(
                collect($validated)
                    ->except([
                        'gallery_images',
                        'gallery_metadata',
                        'remove_gallery_images',
                        'existing_gallery_metadata',
                    ])
                    ->toArray()
            );

            $gallery = $project->gallery;

            // Actualizar los metadatos de las imágenes existentes.
            if (!empty($validated['existing_gallery_metadata'])) {
                foreach ($validated['existing_gallery_metadata'] as $metadata) {
                    if (!$gallery) {
                        continue;
                    }

                    $image = $gallery->images()
                        ->where('id', $metadata['id'])
                        ->first();

                    if (!$image) {
                        continue;
                    }

                    $image->update([
                        'alt' => $metadata['alt'] ?? null,
                        'is_primary' => $metadata['is_primary'] ?? false,
                        'sort_order' => $metadata['sort_order'] ?? 0,
                    ]);
                }
            }

            // Eliminar las imágenes seleccionadas.
            if (
                $gallery &&
                !empty($validated['remove_gallery_images'])
            ) {
                $imagesToDelete = $gallery->images()
                    ->whereIn(
                        'id',
                        $validated['remove_gallery_images']
                    )
                    ->get();

                foreach ($imagesToDelete as $image) {
                    Storage::disk('public')->delete($image->file);
                    $image->delete();
                }
            }

            // Guardar las imágenes nuevas.
            $this->storeGalleryImages(
                $project,
                $request,
                $validated,
                $storedFiles
            );

            // Normalizar la imagen principal.
            $this->normalizePrimaryImage($project);
        });

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Proyecto actualizado correctamente.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->load('gallery.images');

        DB::transaction(function () use ($project) {
            foreach ($project->gallery?->images ?? [] as $image) {
                Storage::disk('public')->delete($image->file);
            }

            $project->gallery?->images()->delete();
            $project->gallery?->delete();

            $project->delete();
        });

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Proyecto eliminado correctamente.');
    }

    private function generateSlug(
        string $name,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Project::query()
                ->where('slug', $slug)
                ->when(
                    $ignoreId,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function normalizeGalleryInput(Request $request): void
    {
        foreach ([
            'gallery_metadata',
            'remove_gallery_images',
            'existing_gallery_metadata',
        ] as $field) {
            $value = $request->input($field);

            if (is_string($value)) {
                $decoded = json_decode($value, true);

                $request->merge([
                    $field => is_array($decoded) ? $decoded : [],
                ]);
            }
        }

        // Compatibilidad con formularios anteriores.
        if (
            !$request->has('remove_gallery_images') &&
            $request->has('delete_gallery_images')
        ) {
            $request->merge([
                'remove_gallery_images' => $request->input(
                    'delete_gallery_images'
                ),
            ]);
        }
    }
}
