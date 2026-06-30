<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarreraController extends Controller
{
    public function index(): View
    {
        $carreras = Carrera::orderByDesc('id')->get();

        return view('sistemas.carreras.index', compact('carreras'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'carrera' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'malla_curricular' => 'required|file|mimes:pdf|max:10240',
        ]);

        $path = $request->file('malla_curricular')->store('mallas', 'public');

        Carrera::create([
            'carrera' => $validated['carrera'],
            'descripcion' => $validated['descripcion'] ?? null,
            'malla_curricular' => $path,
        ]);

        return redirect()
            ->route('sistemas.carreras.index')
            ->with('success', 'Carrera registrada correctamente.');
    }
}
