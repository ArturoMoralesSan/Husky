<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewQuoteMail;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class QuoteController extends Controller
{
    /**
     * Obtener productos y servicios para el formulario público.
     */
    public function options(): JsonResponse
    {
        return response()->json([
            'products' => Product::query()->get(),
            'services' => Service::query()->get(),
        ]);
    }

    /**
     * Registrar una solicitud de cotización desde la página principal.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],

            'product_id' => [
                'nullable',
                'integer',
                'exists:products,id',
            ],

            'service_id' => [
                'nullable',
                'integer',
                'exists:services,id',
            ],

            'item_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        if (
            !empty($validated['product_id']) &&
            !empty($validated['service_id'])
        ) {
            return back()
                ->withErrors([
                    'product_id' => 'Selecciona un producto o un servicio, no ambos.',
                ])
                ->withInput();
        }

        $quote = Quote::create([
            ...$validated,
            'status' => 'pendiente',
        ]);

        // Cargar las relaciones para disponer de los datos
        // del producto o servicio en el correo.
        $quote->load(['product', 'service']);

        try {
            $recipient = config('mail.quotes_notification_address');

            if ($recipient) {
                Mail::to($recipient)->send(
                    new NewQuoteMail(
                        quote: $quote,
                        quoteUrl: route('admin.quotes.index'),
                    )
                );
            } else {
                Log::warning(
                    'No se envió la notificación de cotización: falta configurar el destinatario.'
                );
            }
        } catch (\Throwable $exception) {
            Log::error('Error al enviar el correo de nueva cotización.', [
                'quote_id' => $quote->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return back()->with(
            'success',
            'Tu solicitud de cotización se envió correctamente. Nos pondremos en contacto contigo.'
        );
    }

    /**
     * Mostrar todas las cotizaciones en el panel administrativo.
     */
    public function index(): Response
    {
        $quotes = Quote::query()
            ->with(['product', 'service'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/quotes/Index', [
            'quotes' => $quotes,
            'statuses' => Quote::STATUSES,
        ]);
    }

    /**
     * Actualizar el estado de una cotización.
     */
    public function updateStatus(
        Request $request,
        Quote $quote
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in(Quote::STATUSES),
            ],
        ]);

        $quote->update([
            'status' => $validated['status'],
        ]);

        return back()->with(
            'success',
            'El estado de la cotización se actualizó correctamente.'
        );
    }
}
