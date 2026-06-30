<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecomendadorCarrerasService
{
    /**
     * Obtiene recomendaciones del servicio Python (Machine Learning).
     *
     * El modelo vive en ml/recommend.py (pandas + patrones por correo).
     */
    public function recomendar(int $carreraId, int $limite = 3, ?string $correo = null): Collection
    {
        $baseUrl = config('services.ml_recommend.url');

        if (! $baseUrl) {
            return collect();
        }

        $query = ['limite' => $limite];

        if ($correo) {
            $query['correo'] = $correo;
        }

        try {
            $response = Http::timeout(5)->get(
                rtrim($baseUrl, '/')."/recommend/{$carreraId}",
                $query,
            );

            if (! $response->successful()) {
                Log::warning('ML API respondió con error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return collect();
            }

            return collect($response->json());
        } catch (\Throwable $e) {
            Log::warning('ML API no disponible: '.$e->getMessage());

            return collect();
        }
    }
}
