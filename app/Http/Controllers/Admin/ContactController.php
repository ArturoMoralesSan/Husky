<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    /**
     * Mostrar la información de contacto.
     */
    public function index(): Response
    {
        $contact = Contact::query()->first();

        return Inertia::render('admin/contact/Index', [
            'contact' => $contact
                ? $this->formatContact($contact)
                : null,
        ]);
    }

    /**
     * Mostrar el formulario de creación.
     */
    public function create(): Response|RedirectResponse
    {
        if (Contact::query()->exists()) {
            return redirect()->route('admin.contact.edit');
        }

        return Inertia::render('admin/contact/Create');
    }

    /**
     * Guardar la información de contacto.
     */
    public function store(Request $request): RedirectResponse
    {
        if (Contact::query()->exists()) {
            return redirect()
                ->route('admin.contact.edit')
                ->with('error', 'La información de contacto ya existe.');
        }

        $data = $this->validateContact($request);

        Contact::query()->create($data);

        return redirect()
            ->route('admin.contact.index')
            ->with('success', 'La información de contacto se creó correctamente.');
    }

    /**
     * Mostrar el formulario de edición.
     */
    public function edit(): Response|RedirectResponse
    {
        $contact = Contact::query()->first();

        if (!$contact) {
            return redirect()->route('admin.contact.create');
        }

        return Inertia::render('admin/contact/Edit', [
            'contact' => $this->formatContact($contact),
        ]);
    }

    /**
     * Actualizar la información de contacto.
     */
    public function update(Request $request): RedirectResponse
    {
        $contact = Contact::query()->first();

        if (!$contact) {
            return redirect()->route('admin.contact.create');
        }

        $data = $this->validateContact($request);

        DB::transaction(function () use ($contact, $data) {
            $contact->update($data);
        });

        return redirect()
            ->route('admin.contact.index')
            ->with('success', 'La información de contacto se actualizó correctamente.');
    }

    /**
     * Validar los datos del formulario.
     */
    private function validateContact(Request $request): array
    {
        return $request->validate([
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'phone_primary' => [
                'nullable',
                'string',
                'max:50',
            ],

            'phone_secondary' => [
                'nullable',
                'string',
                'max:50',
            ],

            'phone_tertiary' => [
                'nullable',
                'string',
                'max:50',
            ],

            'whatsapp' => [
                'nullable',
                'string',
                'max:50',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'schedule_weekdays' => [
                'nullable',
                'string',
                'max:255',
            ],

            'schedule_saturday' => [
                'nullable',
                'string',
                'max:255',
            ],

            'facebook_url' => [
                'nullable',
                'url',
                'max:255',
            ],

            'instagram_url' => [
                'nullable',
                'url',
                'max:255',
            ],
        ], [
            'email.email' => 'Ingresa un correo electrónico válido.',

            'facebook_url.url' => 'Ingresa una dirección válida para Facebook.',

            'instagram_url.url' => 'Ingresa una dirección válida para Instagram.',
        ]);
    }

    /**
     * Formatear la información para Inertia.
     */
    private function formatContact(Contact $contact): array
    {
        return [
            'id' => $contact->id,

            'email' => $contact->email,

            'phone_primary' => $contact->phone_primary,
            'phone_secondary' => $contact->phone_secondary,
            'phone_tertiary' => $contact->phone_tertiary,

            'whatsapp' => $contact->whatsapp,

            'address' => $contact->address,

            'schedule_weekdays' => $contact->schedule_weekdays,
            'schedule_saturday' => $contact->schedule_saturday,

            'facebook_url' => $contact->facebook_url,
            'instagram_url' => $contact->instagram_url,
        ];
    }
}
