<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $products = Product::query()
            ->withCount('variants')
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('material', 'like', "%{$search}%")
                        ->orWhere('treatment', 'like', "%{$search}%");
                });
            })
            ->when(
                isset($validated['status']) && $validated['status'] !== '',
                fn ($query) => $query->where(
                    'is_active',
                    (bool) $validated['status']
                )
            )
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/products/Index', [
            'products' => $products,
            'filters' => [
                'search' => $validated['search'] ?? '',
                'status' => $validated['status'] ?? '',
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/products/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        $variants = $validated['variants'] ?? [];
        unset($validated['variants']);

        $validated['slug'] = $this->uniqueSlug(
            $validated['slug'] ?: $validated['name']
        );

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('products', 'public');
        }

        DB::transaction(function () use ($validated, $variants) {
            $product = Product::create($validated);

            foreach ($variants as $index => $variant) {
                $product->variants()->create([
                    ...$variant,
                    'is_active' => $variant['is_active'] ?? true,
                    'sort_order' => $variant['sort_order'] ?? $index,
                ]);
            }
        });

        return to_route('admin.products.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function show(Product $product): RedirectResponse
    {
        return to_route('admin.products.edit', $product);
    }

    public function edit(Product $product): Response
    {
        $product->load([
            'variants' => fn ($query) => $query
                ->orderBy('sort_order')
                ->orderBy('id'),
        ]);

        return Inertia::render('admin/products/Edit', [
            'product' => $product,
        ]);
    }

    public function update(
        Request $request,
        Product $product
    ): RedirectResponse {
        $validated = $this->validateProduct($request, $product);

        $variants = $validated['variants'] ?? [];
        unset($validated['variants']);

        $validated['slug'] = $this->uniqueSlug(
            $validated['slug'] ?: $validated['name'],
            $product->id
        );

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $newImage = $request->file('image')
                ->store('products', 'public');

            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $validated['image'] = $newImage;
        } else {
            unset($validated['image']);
        }

        DB::transaction(function () use ($product, $validated, $variants) {
            $product->update($validated);

            $existingIds = [];

            foreach ($variants as $index => $variant) {
                $variantId = $variant['id'] ?? null;
                unset($variant['id']);

                $data = [
                    ...$variant,
                    'is_active' => $variant['is_active'] ?? true,
                    'sort_order' => $variant['sort_order'] ?? $index,
                ];

                if ($variantId) {
                    $existing = $product->variants()
                        ->whereKey($variantId)
                        ->firstOrFail();

                    $existing->update($data);
                    $existingIds[] = $existing->id;
                } else {
                    $created = $product->variants()->create($data);
                    $existingIds[] = $created->id;
                }
            }

            $product->variants()
                ->whereNotIn('id', $existingIds ?: [0])
                ->delete();
        });

        return to_route('admin.products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $image = $product->image;

        DB::transaction(fn () => $product->delete());

        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return to_route('admin.products.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    private function validateProduct(
        Request $request,
        ?Product $product = null
    ): array {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($product?->id),
            ],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'material' => ['nullable', 'string', 'max:255'],
            'treatment' => ['nullable', 'string', 'max:255'],
            'retention' => ['nullable', 'numeric', 'min:0'],
            'retention_unit' => ['nullable', 'string', 'max:50'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,avif',
                'max:5120',
            ],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],

            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['nullable', 'string', 'max:255'],
            'variants.*.length' => ['nullable', 'numeric', 'min:0'],
            'variants.*.length_unit' => ['nullable', 'string', 'max:30'],
            'variants.*.diameter' => ['nullable', 'numeric', 'min:0'],
            'variants.*.diameter_unit' => ['nullable', 'string', 'max:30'],
            'variants.*.width' => ['nullable', 'numeric', 'min:0'],
            'variants.*.width_unit' => ['nullable', 'string', 'max:30'],
            'variants.*.thickness' => ['nullable', 'numeric', 'min:0'],
            'variants.*.thickness_unit' => ['nullable', 'string', 'max:30'],
            'variants.*.specifications' => ['nullable', 'array'],
            'variants.*.is_active' => ['sometimes', 'boolean'],
            'variants.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'producto';
        $slug = $base;
        $counter = 2;

        while (
            Product::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
