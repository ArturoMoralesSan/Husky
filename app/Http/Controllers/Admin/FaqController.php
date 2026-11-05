<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class FaqController extends Controller
{
    /**
     * Listado de preguntas frecuentes.
     */
    public function index(Request $request): Response
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $faqs = Faq::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');

                $query->where(function ($q) use ($search) {
                    $q->where('question', 'like', "%{$search}%")
                        ->orWhere('answer', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category', $request->input('category'));
            })
            ->when($request->input('status') === 'active', function ($query) {
                $query->where('is_active', true);
            })
            ->when($request->input('status') === 'inactive', function ($query) {
                $query->where('is_active', false);
            })
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        $categories = Faq::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return Inertia::render('admin/faqs/Index', [
            'faqs' => $faqs,
            'categories' => $categories,
            'filters' => [
                'search' => $request->input('search', ''),
                'category' => $request->input('category', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    /**
     * Formulario de creación.
     */
    public function create(): Response
    {
        return Inertia::render('admin/faqs/Create');
    }

    /**
     * Guardar una pregunta frecuente.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:20000'],
            'category' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        Faq::create($validated);

        return to_route('admin.faqs.index')
            ->with('success', 'La pregunta frecuente se creó correctamente.');
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(Faq $faq): Response
    {
        return Inertia::render('admin/faqs/Edit', [
            'faq' => $faq,
        ]);
    }

    /**
     * Actualizar una pregunta frecuente.
     */
    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:20000'],
            'category' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $faq->update($validated);

        return to_route('admin.faqs.index')
            ->with('success', 'La pregunta frecuente se actualizó correctamente.');
    }

    /**
     * Eliminar una pregunta frecuente.
     */
    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return to_route('admin.faqs.index')
            ->with('success', 'La pregunta frecuente se eliminó correctamente.');
    }
}
