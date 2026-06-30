@extends('layouts.sistemas')

@section('title', 'Sistema Inteligente - Sistemas PAT')

@section('content')
    <div class="page-header">
        <h1>Sistema Inteligente de Recomendación</h1>
        <p>Arquitectura multi-agente · Machine Learning · Sistema experto · Universidad PAT</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $statsLocales['carreras'] }}</div>
            <div class="stat-label">Carreras registradas</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $statsLocales['solicitudes'] }}</div>
            <div class="stat-label">Solicitudes totales</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $statsLocales['estudiantes_unicos'] }}</div>
            <div class="stat-label">Estudiantes únicos</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $analytics['dataset']['pares_entrenamiento'] ?? 0 }}</div>
            <div class="stat-label">Pares de entrenamiento</div>
        </div>
    </div>

    <div class="card">
        <h2>Modelos de Machine Learning (Unidad III)</h2>
        <p style="color:#666;font-size:0.9rem;margin-bottom:1rem;">
            Algoritmos supervisados y no supervisados entrenados con solicitudes reales de estudiantes.
        </p>

        @php $detalle = $modelos['detalle'] ?? ($analytics['modelos'] ?? []); @endphp

        <table>
            <thead>
                <tr>
                    <th>Modelo</th>
                    <th>Algoritmo</th>
                    <th>Estado</th>
                    <th>Muestras</th>
                    <th>Precisión</th>
                </tr>
            </thead>
            <tbody>
                @foreach([
                    'arbol' => 'Árbol de decisión',
                    'logistico' => 'Regresión logística',
                    'mlp' => 'Red neuronal (MLP)',
                    'kmeans' => 'K-Means (no supervisado)',
                ] as $clave => $etiqueta)
                    @php $m = $detalle[$clave] ?? ['disponible' => false]; @endphp
                    <tr>
                        <td><strong>{{ $etiqueta }}</strong></td>
                        <td>{{ $m['algoritmo'] ?? '—' }}</td>
                        <td>
                            @if($m['disponible'] ?? false)
                                <span class="badge-ok">Entrenado</span>
                            @else
                                <span class="badge-pending">Pendiente</span>
                            @endif
                        </td>
                        <td>{{ $m['muestras'] ?? '—' }}</td>
                        <td>
                            @if(isset($m['precision_pct']))
                                {{ $m['precision_pct'] }}%
                            @elseif(($m['algoritmo'] ?? '') === 'KMeans')
                                {{ $m['clusters'] ?? '—' }} clusters
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <form action="{{ route('sistemas.ia.entrenar') }}" method="POST" style="margin-top:1.25rem;">
            @csrf
            <button type="submit" class="btn btn-red">Entrenar todos los modelos</button>
            <small style="display:block;color:#888;margin-top:8px;">
                Requiere al menos 4 pares de entrenamiento. Usa el seeder demo si no hay datos suficientes.
            </small>
        </form>
    </div>

    <div class="card">
        <h2>Arquitectura de Agentes Inteligentes (Unidad I y II)</h2>
        <p style="color:#666;font-size:0.9rem;margin-bottom:1rem;">
            {{ $arquitectura['paradigma'] ?? 'Sistema multi-agente híbrido' }}
        </p>

        @if(!empty($arquitectura['flujo']))
            <ol class="flow-list">
                @foreach($arquitectura['flujo'] as $paso)
                    <li>{{ $paso }}</li>
                @endforeach
            </ol>
        @endif

        <div class="agents-grid">
            @foreach($arquitectura['agentes'] ?? [] as $agente)
                <article class="agent-card">
                    <h3>{{ $agente['nombre'] }}</h3>
                    <span class="agent-type">{{ $agente['tipo'] }}</span>
                    <p>{{ $agente['rol'] }}</p>
                    <div class="agent-meta">
                        <strong>Sensores:</strong> {{ implode(', ', $agente['sensores'] ?? []) }}<br>
                        <strong>Actuadores:</strong> {{ implode(', ', $agente['actuadores'] ?? []) ?: '—' }}
                    </div>
                    <span class="agent-unit">{{ $agente['unidad_curricular'] }}</span>
                </article>
            @endforeach
        </div>
    </div>

    <div class="card">
        <h2>Distribución del dataset</h2>
        @if(empty($analytics['dataset']['distribucion']))
            <p style="color:#888;">Sin solicitudes registradas aún.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Carrera</th>
                        <th>Solicitudes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analytics['dataset']['distribucion'] as $fila)
                        <tr>
                            <td>{{ $fila['carrera'] }}</td>
                            <td>{{ $fila['solicitudes'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="card">
        <h2>Consideraciones éticas (Unidad III — Semana 15)</h2>
        <ul class="ethics-list">
            @foreach($etica['consideraciones'] ?? [] as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    </div>

    <div class="card">
        <h2>Alineación con la malla curricular</h2>
        <table>
            <thead>
                <tr>
                    <th>Unidad</th>
                    <th>Tema del sílabo</th>
                    <th>Implementación en el proyecto</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>I</td>
                    <td>Agentes inteligentes, sensores/actuadores</td>
                    <td>Arquitectura multi-agente (sensor, decisión, actuadores web)</td>
                </tr>
                <tr>
                    <td>I</td>
                    <td>Toma de decisiones con variables continuas</td>
                    <td>Fusión probabilística ponderada de confianzas</td>
                </tr>
                <tr>
                    <td>II</td>
                    <td>Sistema experto basado en reglas</td>
                    <td><code>ml/knowledge_base.json</code> + motor de inferencia</td>
                </tr>
                <tr>
                    <td>II</td>
                    <td>Razonamiento probabilístico</td>
                    <td><code>predict_proba</code> + co-ocurrencias con porcentaje</td>
                </tr>
                <tr>
                    <td>II</td>
                    <td>Agentes múltiples / comunicación</td>
                    <td>6 agentes especializados coordinados por Agente de Decisión</td>
                </tr>
                <tr>
                    <td>III</td>
                    <td>Árboles de decisión</td>
                    <td><code>DecisionTreeClassifier</code></td>
                </tr>
                <tr>
                    <td>III</td>
                    <td>Regresión logística</td>
                    <td><code>LogisticRegression</code> multiclase</td>
                </tr>
                <tr>
                    <td>III</td>
                    <td>Aprendizaje no supervisado</td>
                    <td><code>KMeans</code> sobre perfiles de intereses</td>
                </tr>
                <tr>
                    <td>III</td>
                    <td>Redes neuronales / Deep learning</td>
                    <td><code>MLPClassifier</code> (32→16 neuronas)</td>
                </tr>
                <tr>
                    <td>III</td>
                    <td>Ética profesional en IA</td>
                    <td>Políticas documentadas en base de conocimiento</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .stat-card {
        background: #fff;
        border-radius: 8px;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
        text-align: center;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 800;
        color: #e30613;
    }
    .stat-label {
        font-size: 0.8rem;
        color: #666;
        margin-top: 4px;
    }
    .badge-ok {
        background: #d4edda;
        color: #155724;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .badge-pending {
        background: #fff3cd;
        color: #856404;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .agents-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    .agent-card {
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 1rem;
        background: #fafafa;
    }
    .agent-card h3 {
        font-size: 0.95rem;
        margin-bottom: 4px;
    }
    .agent-type {
        display: inline-block;
        font-size: 0.72rem;
        background: #111;
        color: #fff;
        padding: 2px 8px;
        border-radius: 4px;
        margin-bottom: 8px;
    }
    .agent-card p {
        font-size: 0.85rem;
        color: #444;
        margin-bottom: 8px;
    }
    .agent-meta {
        font-size: 0.78rem;
        color: #666;
        margin-bottom: 8px;
        line-height: 1.5;
    }
    .agent-unit {
        font-size: 0.72rem;
        color: #e30613;
        font-weight: 600;
    }
    .flow-list {
        margin: 0 0 1rem 1.25rem;
        color: #444;
        font-size: 0.9rem;
        line-height: 1.6;
    }
    .ethics-list {
        margin-left: 1.25rem;
        color: #444;
        line-height: 1.7;
        font-size: 0.9rem;
    }
</style>
@endpush
