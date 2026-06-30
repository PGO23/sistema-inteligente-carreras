<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Estudiante;
use Illuminate\Database\Seeder;

class SolicitudesDemoSeeder extends Seeder
{
    /**
     * Genera solicitudes demo con PATRONES DE CO-OCURRENCIA CONSISTENTES.
     *
     * El objetivo es que los modelos (árbol, regresión, MLP) aprendan relaciones
     * claras carrera_base → carrera_relacionada y obtengan buena precisión.
     *
     * Cada "perfil" representa estudiantes con intereses afines: todos los
     * estudiantes de un perfil solicitan el mismo conjunto de carreras, lo que
     * crea un patrón fuerte y predecible para el entrenamiento.
     */
    private const DEMO_DOMAIN = '@demo.pat.edu';

    public function run(): void
    {
        $this->asegurarCarreras();

        $carreras = Carrera::orderBy('id')->pluck('id')->all();

        if (count($carreras) < 2) {
            $this->command?->warn('Registra al menos 2 carreras antes de ejecutar este seeder.');

            return;
        }

        // Limpia solo los estudiantes demo previos para regenerar patrones limpios.
        Estudiante::where('correo', 'like', '%'.self::DEMO_DOMAIN)->delete();

        // Perfiles de interés: [índices de carreras] => cuántos estudiantes lo siguen.
        // Patrones FUERTES y poco ambiguos para que el árbol aprenda relaciones claras.
        //   0 ↔ 2  (software ↔ ciberseguridad)
        //   1 ↔ 3  (ciencia de datos ↔ estadística)
        //   4 → 2  (redes se asocia con ciberseguridad)
        $perfiles = [
            ['indices' => [0, 2], 'cantidad' => 20, 'etiqueta' => 'software-seguridad'],
            ['indices' => [1, 3], 'cantidad' => 20, 'etiqueta' => 'datos-estadistica'],
            ['indices' => [4, 2], 'cantidad' => 6,  'etiqueta' => 'redes-seguridad'],
        ];

        $totalCarreras = count($carreras);
        $contador = 1;
        $creados = 0;

        foreach ($perfiles as $perfil) {
            // Filtra índices que existan según cuántas carreras hay en la BD.
            $idsPerfil = [];
            foreach ($perfil['indices'] as $idx) {
                if (isset($carreras[$idx])) {
                    $idsPerfil[] = $carreras[$idx];
                }
            }
            $idsPerfil = array_values(array_unique($idsPerfil));

            if (count($idsPerfil) < 2) {
                continue;
            }

            for ($i = 0; $i < $perfil['cantidad']; $i++) {
                $correo = sprintf('demo%03d%s', $contador, self::DEMO_DOMAIN);
                $celular = '519'.str_pad((string) (10000000 + $contador), 8, '0', STR_PAD_LEFT);
                $nombre = 'Estudiante Demo '.$contador;

                foreach ($idsPerfil as $carreraId) {
                    Estudiante::create([
                        'nombre' => $nombre,
                        'correo' => $correo,
                        'celular' => $celular,
                        'carrera_id' => $carreraId,
                        'estado' => Estudiante::ESTADO_ENVIADO,
                        'fecha_solicitud' => now()->subDays(rand(5, 40)),
                        'fecha_envio' => now()->subDays(rand(1, 4)),
                    ]);
                    $creados++;
                }

                $contador++;
            }
        }

        $this->command?->info("Seeder demo: {$creados} solicitudes generadas para ".($contador - 1)." estudiantes.");
        $this->command?->info('Ahora reentrena: python ml/train.py (o el botón "Entrenar todos los modelos").');
    }

    private function asegurarCarreras(): void
    {
        if (Carrera::count() >= 2) {
            return;
        }

        $demoCarreras = [
            ['carrera' => 'ING. DE SOFTWARE', 'descripcion' => 'Desarrollo de aplicaciones y sistemas informáticos.'],
            ['carrera' => 'CIENCIA DE DATOS', 'descripcion' => 'Análisis de datos, estadística y machine learning.'],
            ['carrera' => 'CIBERSEGURIDAD', 'descripcion' => 'Protección de sistemas, redes y análisis forense digital.'],
            ['carrera' => 'ING. ESTADISTICA', 'descripcion' => 'Modelos estadísticos y análisis cuantitativo.'],
            ['carrera' => 'ING. DE REDES Y TELECOM', 'descripcion' => 'Infraestructura de redes y comunicaciones.'],
        ];

        foreach ($demoCarreras as $demo) {
            Carrera::firstOrCreate(['carrera' => $demo['carrera']], $demo);
        }
    }
}
