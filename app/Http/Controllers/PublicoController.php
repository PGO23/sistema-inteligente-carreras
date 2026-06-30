<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Estudiante;
use App\Services\RecomendadorCarrerasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PublicoController extends Controller
{
    public function __construct(
        private RecomendadorCarrerasService $recomendador
    ) {}

    public function index(): View
    {
        $carreras = Carrera::orderBy('carrera')->get();

        return view('publico.index', compact('carreras'));
    }

    public function registrarInteres(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'correo' => 'required|email|max:100',
            'celular' => ['required', 'regex:/^9\d{8}$/'],
            'carrera_id' => 'required|exists:carreras,id',
        ]);

        $validated['celular'] = '51' . $validated['celular'];

        $solicitudExistente = Estudiante::where('correo', $validated['correo'])
            ->where('carrera_id', $validated['carrera_id'])
            ->where('estado', Estudiante::ESTADO_ENVIADO)
            ->orderByDesc('fecha_envio')
            ->first();

        $limiteRecomendaciones = max(Carrera::count() - 1, 1);

        if ($solicitudExistente) {
            $recomendaciones = $this->recomendador->recomendar(
                (int) $validated['carrera_id'],
                $limiteRecomendaciones,
                $validated['correo'],
            );

            $this->notificarN8n([
                'estudiante_id' => $solicitudExistente->id,
                'nombre' => $validated['nombre'],
                'correo' => $validated['correo'],
                'celular' => $validated['celular'],
                'carrera_id' => $validated['carrera_id'],
                'duplicado' => true,
                'fecha_envio' => $solicitudExistente->fecha_envio?->format('d/m/Y H:i'),
            ], $recomendaciones);

            $fecha = $solicitudExistente->fecha_envio?->format('d/m/Y') ?? 'una fecha anterior';

            return redirect()
                ->route('publico.index')
                ->with('warning', "Ya solicitaste información de esta carrera. Te enviamos un recordatorio a tu correo (enviado el {$fecha}).")
                ->with('recomendaciones', $recomendaciones->all());
        }

        $estudiante = Estudiante::create([
            ...$validated,
            'estado' => Estudiante::ESTADO_PENDIENTE,
            'fecha_solicitud' => now(),
        ]);

        $recomendaciones = $this->recomendador->recomendar(
            (int) $validated['carrera_id'],
            $limiteRecomendaciones,
            $validated['correo'],
        );

        $this->notificarN8n([
            'estudiante_id' => $estudiante->id,
            'nombre' => $estudiante->nombre,
            'correo' => $estudiante->correo,
            'celular' => $estudiante->celular,
            'carrera_id' => $estudiante->carrera_id,
            'duplicado' => false,
        ], $recomendaciones);

        return redirect()
            ->route('publico.index')
            ->with('success', '¡Gracias! Revisa tu correo, te enviaremos la información de la carrera.')
            ->with('recomendaciones', $recomendaciones->all());
    }

    private function notificarN8n(array $payload, $recomendaciones = null): void
    {
        $webhookUrl = config('services.n8n.webhook_url');

        if (! $webhookUrl) {
            return;
        }

        $recomendaciones = collect($recomendaciones ?? []);

        if ($recomendaciones->isNotEmpty()) {
            $payload['carreras_recomendadas'] = $recomendaciones
                ->map(fn (array $item) => $item['carrera'])
                ->values()
                ->all();
        }

        try {
            $response = Http::timeout(5)->post($webhookUrl, $payload);
            Log::info('n8n webhook respondió', [
                'status' => $response->status(),
                'body' => $response->body(),
                'correo' => $payload['correo'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar a n8n: ' . $e->getMessage(), [
                'correo' => $payload['correo'] ?? null,
            ]);
        }
    }
}
