<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MlInteligenciaService
{
    private function baseUrl(): ?string
    {
        return config('services.ml_recommend.url');
    }

    public function analytics(): array
    {
        return $this->get('/analytics', []);
    }

    public function arquitectura(): array
    {
        return $this->get('/agents', []);
    }

    public function etica(): array
    {
        return $this->get('/ethics', ['consideraciones' => []]);
    }

    public function estadoModelos(): array
    {
        return $this->get('/model/status', ['disponible' => false, 'detalle' => []]);
    }

    public function entrenar(): array
    {
        $baseUrl = $this->baseUrl();

        if (! $baseUrl) {
            return ['ok' => false, 'error' => 'ML_API_URL no configurada'];
        }

        try {
            $response = Http::timeout(30)->post(rtrim($baseUrl, '/').'/train');

            if (! $response->successful()) {
                return ['ok' => false, 'error' => $response->json('error') ?? 'Error al entrenar'];
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::warning('No se pudo entrenar modelos ML: '.$e->getMessage());

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function get(string $path, array $default): array
    {
        $baseUrl = $this->baseUrl();

        if (! $baseUrl) {
            return $default;
        }

        try {
            $response = Http::timeout(5)->get(rtrim($baseUrl, '/').$path);

            if (! $response->successful()) {
                return $default;
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::warning("ML API no disponible ({$path}): ".$e->getMessage());

            return $default;
        }
    }
}
