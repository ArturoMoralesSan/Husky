<?php

namespace App\Traits;

use App\Models\About;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use RuntimeException;

trait StoresGalleryImages
{
    private function storeGalleryImages(
        Model $model,
        Request $request,
        array $validated,
        array &$storedFiles
    ): void {
        $files = $request->file('gallery_images', []);

        if (! is_array($files) || count($files) === 0) {
            return;
        }

        $gallery = $model->gallery()->first();

        $galleryName = $model instanceof About
            ? 'Nosotros'
            : $model->name;

        if (! $gallery) {
            $gallery = $model->gallery()->create([
                'name' => $galleryName,
            ]);
        } else {
            $gallery->update([
                'name' => $galleryName,
            ]);
        }

        $metadata = $validated['gallery_metadata'] ?? [];

        foreach (array_values($files) as $index => $file) {
            $directory = match (true) {
                $model instanceof Product => 'products',
                $model instanceof Service => 'services',
                $model instanceof Project => 'projects',
                $model instanceof About => 'about',

                default => throw new RuntimeException(
                    'El modelo no está habilitado para guardar galerías.'
                ),
            };

            $path = $this->storeMedia($file, $directory);

            $storedFiles[] = $path;

            $itemMetadata = $metadata[$index] ?? [];

            $gallery->images()->create([
                'file' => $path,
                'alt' => $itemMetadata['alt'] ?? null,
                'is_primary' => (bool) (
                    $itemMetadata['is_primary'] ?? false
                ),
                'sort_order' => (int) (
                    $itemMetadata['sort_order'] ?? $index
                ),
            ]);
        }

        $this->normalizePrimaryImage($model);
    }

    private function normalizePrimaryImage(Model $model): void
    {
        $images = $model->gallery?->images()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if (! $images || $images->isEmpty()) {
            return;
        }

        $primary = $images->first(
            fn ($image) => $image->is_primary
        ) ?? $images->first();

        foreach ($images as $image) {
            $shouldBePrimary = $image->id === $primary->id;

            if ($image->is_primary !== $shouldBePrimary) {
                $image->update([
                    'is_primary' => $shouldBePrimary,
                ]);
            }
        }
    }

    private function storeMedia($media, string $directoryName): string
    {
        $directory = storage_path("app/public/{$directoryName}");

        if (
            ! is_dir($directory)
            && ! mkdir($directory, 0755, true)
            && ! is_dir($directory)
        ) {
            throw new RuntimeException(
                'No fue posible crear el directorio de imágenes.'
            );
        }

        $filename = uniqid('', true)
            . '.'
            . strtolower($media->getClientOriginalExtension());

        $media->move($directory, $filename);

        return "{$directoryName}/{$filename}";
    }
}
