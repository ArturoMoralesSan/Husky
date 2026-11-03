<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\About;
use App\Traits\StoresGalleryImages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    use StoresGalleryImages;

    /**
     * Mostrar el listado de Acerca de nosotros.
     */
    public function index(): Response
    {
        $about = About::query()
            ->with([
                'values',
                'gallery.images',
            ])
            ->first();

        return Inertia::render('admin/about/Index', [
            'about' => $about
                ? $this->formatAbout($about)
                : null,
        ]);
    }

    /**
     * Mostrar el formulario de creación.
     */
    public function create(): Response|RedirectResponse
    {
        if (About::query()->exists()) {
            return to_route('admin.about.edit');
        }

        return Inertia::render('admin/about/Create');
    }

    /**
     * Guardar la información de Acerca de nosotros.
     */
    public function store(Request $request): RedirectResponse
    {
        if (About::query()->exists()) {
            return to_route('admin.about.edit')
                ->with(
                    'error',
                    'La información de Acerca de nosotros ya existe.'
                );
        }

        $this->normalizeGalleryInput($request);

        $validated = $this->validateAbout($request);

        $storedFiles = [];

        DB::transaction(function () use (
            $request,
            $validated,
            &$storedFiles
        ) {
            $about = About::create([
                'who_we_are' => $validated['who_we_are'] ?? null,
                'history' => $validated['history'] ?? null,
                'mission' => $validated['mission'] ?? null,
                'vision' => $validated['vision'] ?? null,
                'objectives' => $validated['objectives'] ?? null,
            ]);

            // Guardar los valores institucionales.
            foreach ($validated['values'] ?? [] as $value) {
                $about->values()->create([
                    'name' => $value['name'],
                    'description' => $value['description'] ?? null,
                    'icon' => $value['icon'] ?? null,
                    'sort_order' => $value['sort_order'],
                ]);
            }

            // Guardar la galería.
            $this->storeGalleryImages(
                $about,
                $request,
                $validated,
                $storedFiles
            );

            // Normalizar la imagen principal.
            $this->normalizePrimaryImage($about);
        });

        return to_route('admin.about.edit')
            ->with(
                'success',
                'La información de Acerca de nosotros se creó correctamente.'
            );
    }

    /**
     * Mostrar el formulario de edición.
     */
    public function edit(): Response|RedirectResponse
    {
        $about = About::query()
            ->with([
                'values',
                'gallery.images' => function ($query) {
                    $query->orderBy('sort_order')
                        ->orderBy('id');
                },
            ])
            ->first();

        if (!$about) {
            return to_route('admin.about.create');
        }

        return Inertia::render('admin/about/Edit', [
            'about' => $this->formatAbout($about),
        ]);
    }

    /**
     * Actualizar la información de Acerca de nosotros.
     */
    public function update(Request $request): RedirectResponse
    {
        $about = About::query()->first();

        if (!$about) {
            return to_route('admin.about.create');
        }

        $this->normalizeGalleryInput($request);

        $validated = $this->validateAbout($request, true);

        $storedFiles = [];

        DB::transaction(function () use (
            $request,
            $about,
            $validated,
            &$storedFiles
        ) {
            $about->update([
                'who_we_are' => $validated['who_we_are'] ?? null,
                'history' => $validated['history'] ?? null,
                'mission' => $validated['mission'] ?? null,
                'vision' => $validated['vision'] ?? null,
                'objectives' => $validated['objectives'] ?? null,
            ]);

            // Reemplazar los valores anteriores por los recibidos.
            $about->values()->delete();

            foreach ($validated['values'] ?? [] as $value) {
                $about->values()->create([
                    'name' => $value['name'],
                    'description' => $value['description'] ?? null,
                    'icon' => $value['icon'] ?? null,
                    'sort_order' => $value['sort_order'],
                ]);
            }

            $gallery = $about->gallery;

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
                $about,
                $request,
                $validated,
                $storedFiles
            );

            // Normalizar la imagen principal.
            $this->normalizePrimaryImage($about);
        });

        return to_route('admin.about.edit')
            ->with(
                'success',
                'La información de Acerca de nosotros se actualizó correctamente.'
            );
    }

    /**
     * Validar los datos del formulario.
     */
    private function validateAbout(
        Request $request,
        bool $updating = false
    ): array {
        $rules = [
            'who_we_are' => ['nullable', 'string'],
            'history' => ['nullable', 'string'],
            'mission' => ['nullable', 'string'],
            'vision' => ['nullable', 'string'],
            'objectives' => ['nullable', 'string'],

            'values' => ['nullable', 'array'],
            'values.*.name' => ['required', 'string', 'max:255'],
            'values.*.description' => ['nullable', 'string'],
            'values.*.icon' => ['nullable', 'string', 'max:100'],
            'values.*.sort_order' => [
                'required',
                'integer',
                'min:0',
            ],

            // Imágenes nuevas.
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            // Metadatos de las imágenes nuevas.
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
        ];

        if ($updating) {
            $rules = array_merge($rules, [
                // Imágenes existentes que se eliminarán.
                'remove_gallery_images' => ['nullable', 'array'],
                'remove_gallery_images.*' => [
                    'integer',
                    'exists:gallery_images,id',
                ],

                // Metadatos de las imágenes existentes.
                'existing_gallery_metadata' => [
                    'nullable',
                    'array',
                ],
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
        }

        return $request->validate($rules, [
            'values.*.name.required' =>
                'El nombre de cada valor es obligatorio.',

            'values.*.name.max' =>
                'El nombre no debe superar los 255 caracteres.',

            'values.*.sort_order.required' =>
                'El orden de cada valor es obligatorio.',

            'values.*.sort_order.integer' =>
                'El orden debe ser un número entero.',

            'values.*.sort_order.min' =>
                'El orden no puede ser negativo.',

            'gallery_images.*.image' =>
                'Cada archivo de la galería debe ser una imagen válida.',

            'gallery_images.*.mimes' =>
                'Las imágenes deben ser JPG, JPEG, PNG o WEBP.',

            'gallery_images.*.max' =>
                'Cada imagen no debe superar los 5 MB.',

            'existing_gallery_metadata.*.id.exists' =>
                'Una de las imágenes seleccionadas ya no existe.',

            'remove_gallery_images.*.exists' =>
                'Una de las imágenes seleccionadas para eliminar ya no existe.',
        ]);
    }

    /**
     * Normalizar los datos de la galería enviados por FormData.
     */
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

    /**
     * Preparar la información para las vistas de Inertia.
     */
    private function formatAbout(About $about): array
    {
        return [
            'id' => $about->id,
            'who_we_are' => $about->who_we_are,
            'history' => $about->history,
            'mission' => $about->mission,
            'vision' => $about->vision,
            'objectives' => $about->objectives,

            'values' => $about->values
                ->map(fn ($value) => [
                    'id' => $value->id,
                    'name' => $value->name,
                    'description' => $value->description,
                    'icon' => $value->icon,
                    'sort_order' => $value->sort_order,
                ])
                ->values(),

            // Galería de Nosotros.
            'gallery_images' => $about->gallery?->images
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
        ];
    }
}
