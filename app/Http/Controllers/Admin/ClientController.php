<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    /**
     * Mostrar y filtrar clientes.
     */
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $clients = Client::query()
            ->when(! empty($validated['search']), function ($query) use ($validated) {
                $search = $validated['search'];

                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Client $client) => [
                'id' => $client->id,
                'name' => $client->name,
                'logo' => $client->logo,
                'logo_url' => Storage::disk('public')->url($client->logo),
                'url' => $client->url,
                'created_at' => $client->created_at?->format('d/m/Y'),
            ]);

        return Inertia::render('admin/clients/Index', [
            'clients' => $clients,
            'filters' => [
                'search' => $validated['search'] ?? '',
            ],
        ]);
    }

    /**
     * Mostrar el formulario de creación.
     */
    public function create(): Response
    {
        return Inertia::render('admin/clients/Create');
    }

    /**
     * Guardar un cliente nuevo.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'url' => ['nullable', 'url', 'max:255'],
        ], [
            'name.required' => 'El nombre del cliente es obligatorio.',
            'logo.required' => 'Debes seleccionar el logotipo del cliente.',
            'logo.image' => 'El archivo debe ser una imagen.',
            'logo.mimes' => 'El logotipo debe ser JPG, JPEG, PNG o WEBP.',
            'logo.max' => 'El logotipo no debe superar los 5 MB.',
            'url.url' => 'Ingresa una dirección URL válida.',
        ]);

        $validated['logo'] = $this->storeLogo(
            $request->file('logo')
        );

        Client::create($validated);

        return to_route('admin.clients.index')
            ->with('success', 'Cliente creado correctamente.');
    }

    /**
     * Mostrar el formulario de edición.
     */
    public function edit(Client $client): Response
    {
        return Inertia::render('admin/clients/Edit', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'logo' => $client->logo,
                'logo_url' => Storage::disk('public')->url($client->logo),
                'url' => $client->url,
            ],
        ]);
    }

    /**
     * Actualizar un cliente existente.
     */
    public function update(
        Request $request,
        Client $client
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'url' => ['nullable', 'url', 'max:255'],
        ], [
            'name.required' => 'El nombre del cliente es obligatorio.',
            'logo.image' => 'El archivo debe ser una imagen.',
            'logo.mimes' => 'El logotipo debe ser JPG, JPEG, PNG o WEBP.',
            'logo.max' => 'El logotipo no debe superar los 5 MB.',
            'url.url' => 'Ingresa una dirección URL válida.',
        ]);

        if ($request->hasFile('logo')) {
            $newLogo = $this->storeLogo(
                $request->file('logo')
            );

            $oldLogo = $client->logo;

            $validated['logo'] = $newLogo;

            $client->update($validated);

            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
        } else {
            unset($validated['logo']);

            $client->update($validated);
        }

        return to_route('admin.clients.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Eliminar un cliente y su logotipo.
     */
    public function destroy(Client $client): RedirectResponse
    {
        $logo = $client->logo;

        $client->delete();

        if ($logo) {
            Storage::disk('public')->delete($logo);
        }

        return to_route('admin.clients.index')
            ->with('success', 'Cliente eliminado correctamente.');
    }

    /**
     * Guardar un logotipo utilizando un nombre único.
     */
    private function storeLogo($logo): string
    {
        $directory = storage_path('app/public/clients');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = uniqid('', true)
            . '.'
            . strtolower($logo->getClientOriginalExtension());

        $logo->move($directory, $filename);

        return "clients/{$filename}";
    }
}
