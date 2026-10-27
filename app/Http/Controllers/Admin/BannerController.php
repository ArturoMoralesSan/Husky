<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BannerController extends Controller
{
    /**
     * Mostrar y filtrar banners.
     */
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:0,1'],
        ]);

        $banners = Banner::query()
            ->when(! empty($validated['search']), function ($query) use ($validated) {
                $search = $validated['search'];

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%");
                });
            })
            ->when(! empty($validated['position']), function ($query) use ($validated) {
                $query->where('position', $validated['position']);
            })
            ->when(
                isset($validated['status']) && $validated['status'] !== '',
                function ($query) use ($validated) {
                    $query->where('is_active', (bool) $validated['status']);
                }
            )
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/banners/Index', [
            'banners' => $banners,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'position' => $validated['position'] ?? '',
                'status' => $validated['status'] ?? '',
            ],
            'positions' => Banner::query()
                ->select('position')
                ->distinct()
                ->orderBy('position')
                ->pluck('position'),
        ]);
    }

    /**
     * Mostrar el formulario de creación.
     */
    public function create(): Response
    {
        return Inertia::render('admin/banners/Create', [
            'positions' => $this->positions(),
        ]);
    }

    /**
     * Guardar un banner nuevo.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'image' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,avif,mp4,webm,mov',
                'max:51200',
            ],

            'alt_text' => ['nullable', 'string', 'max:255'],

            'link_url' => ['nullable', 'string', 'max:2048'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'open_in_new_tab' => ['sometimes', 'boolean'],

            'position' => ['required', 'string', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0'],

            'is_active' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => [
                'nullable',
                'date',
                'after_or_equal:starts_at',
            ],

            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['image'] = $this->storeMedia(
            $request->file('image')
        );

        $validated['open_in_new_tab'] =
            $request->boolean('open_in_new_tab');

        $validated['is_active'] =
            $request->boolean('is_active');

        Banner::create($validated);

        return to_route('admin.banners.index')
            ->with('success', 'Banner creado correctamente.');
    }

    /**
     * Mostrar un banner.
     */
    public function show(Banner $banner): RedirectResponse
    {
        return to_route('admin.banners.edit', $banner);
    }

    /**
     * Mostrar el formulario de edición.
     */
    public function edit(Banner $banner): Response
    {
        return Inertia::render('admin/banners/Edit', [
            'banner' => $banner,
            'positions' => $this->positions($banner->position),
        ]);
    }

    /**
     * Actualizar un banner existente.
     */
    public function update(
        Request $request,
        Banner $banner
    ): RedirectResponse {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'image' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,avif,mp4,webm,mov',
                'max:51200',
            ],

            'alt_text' => ['nullable', 'string', 'max:255'],

            'link_url' => ['nullable', 'string', 'max:2048'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'open_in_new_tab' => ['sometimes', 'boolean'],

            'position' => ['required', 'string', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0'],

            'is_active' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => [
                'nullable',
                'date',
                'after_or_equal:starts_at',
            ],

            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
        ]);

        if ($request->hasFile('image')) {
            $newMedia = $this->storeMedia(
                $request->file('image')
            );

            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }

            $validated['image'] = $newMedia;
        } else {
            unset($validated['image']);
        }

        $validated['open_in_new_tab'] =
            $request->boolean('open_in_new_tab');

        $validated['is_active'] =
            $request->boolean('is_active');

        $banner->update($validated);

        return to_route('admin.banners.index')
            ->with('success', 'Banner actualizado correctamente.');
    }

    /**
     * Eliminar un banner y su archivo multimedia.
     */
    public function destroy(Banner $banner): RedirectResponse
    {
        $media = $banner->image;

        $banner->delete();

        if ($media) {
            Storage::disk('public')->delete($media);
        }

        return to_route('admin.banners.index')
            ->with('success', 'Banner eliminado correctamente.');
    }

    /**
     * Guardar una imagen o un video usando un nombre único.
     */
    private function storeMedia($media): string
    {
        $directory = storage_path('app/public/banners');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = uniqid('', true)
            . '.'
            . strtolower($media->getClientOriginalExtension());

        $media->move($directory, $filename);

        return "banners/{$filename}";
    }

    /**
     * Posiciones disponibles para los formularios.
     */
    private function positions(?string $current = null): array
    {
        $positions = [
            'home' => 'Inicio',
            'products' => 'Productos',
            'services' => 'Servicios',
        ];

        if ($current && ! array_key_exists($current, $positions)) {
            $positions[$current] = ucfirst($current);
        }

        return $positions;
    }
}
