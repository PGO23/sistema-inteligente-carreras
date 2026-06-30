<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Estudiante;
use App\Services\MlInteligenciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InteligenciaController extends Controller
{
    public function __construct(
        private MlInteligenciaService $ml
    ) {}

    public function index(): View
    {
        $analytics = $this->ml->analytics();
        $modelos = $this->ml->estadoModelos();
        $arquitectura = $this->ml->arquitectura();
        $etica = $this->ml->etica();

        $statsLocales = [
            'carreras' => Carrera::count(),
            'solicitudes' => Estudiante::count(),
            'estudiantes_unicos' => Estudiante::distinct('correo')->count('correo'),
        ];

        return view('sistemas.ia.index', compact(
            'analytics',
            'modelos',
            'arquitectura',
            'etica',
            'statsLocales',
        ));
    }

    public function entrenar(): RedirectResponse
    {
        $resultado = $this->ml->entrenar();

        if (! ($resultado['ok'] ?? false)) {
            return redirect()
                ->route('sistemas.ia.index')
                ->with('error', $resultado['error'] ?? 'No se pudieron entrenar los modelos.');
        }

        return redirect()
            ->route('sistemas.ia.index')
            ->with('success', 'Modelos entrenados correctamente (árbol, regresión logística, red neuronal y K-Means).');
    }
}
