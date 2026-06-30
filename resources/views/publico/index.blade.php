@extends('layouts.pat')

@section('title', 'Carreras - Universidad PAT')

@php
    $imagenes = [
        'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&q=80',
        'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=600&q=80',
        'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=600&q=80',
        'https://images.unsplash.com/photo-1531489874583-08fa5edf9a67?w=600&q=80',
        'https://images.unsplash.com/photo-1552664730-d307ca884978?w=600&q=80',
        'https://images.unsplash.com/photo-1571260899304-425eee4c76ec?w=600&q=80',
    ];
@endphp

@section('content')
    <section class="hero">
        <div class="hero-content">
            <div class="hero-slogan">SER SIEMPRE EMPLEABLE</div>
            <div class="hero-sub">Formación profesional de calidad para el mercado laboral actual</div>
        </div>
    </section>

    <section class="section">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        @if(session('recomendaciones') && count(session('recomendaciones')) > 0)
            <div class="ml-recommendations">
                <div class="ml-rec-header">
                    <div class="ml-rec-icon">✦</div>
                    <div>
                        <div class="ml-rec-title">Carreras que también te pueden interesar</div>
                        <div class="ml-rec-subtitle">
                            Sistema inteligente multi-agente: reglas expertas, patrones históricos,
                            árbol de decisión, regresión logística, red neuronal y K-Means.
                        </div>
                    </div>
                </div>

                <div class="ml-rec-grid">
                    @foreach(session('recomendaciones') as $rec)
                        <article class="ml-rec-card">
                            <div class="ml-rec-card-top">
                                <h4>{{ $rec['carrera'] }}</h4>
                                <span class="ml-rec-badge">{{ $rec['porcentaje'] }}%</span>
                            </div>
                            <div class="ml-rec-bar-wrap" aria-hidden="true">
                                <div class="ml-rec-bar" style="width: {{ $rec['porcentaje'] }}%;"></div>
                            </div>
                            <p class="ml-rec-meta">
                                @if(($rec['correos_relacionados'] ?? 0) > 0)
                                    {{ $rec['correos_relacionados'] }} estudiante(s) con intereses similares también la solicitaron
                                @else
                                    Sugerida por el modelo de árbol de decisión
                                @endif
                            </p>
                            @if(!empty($rec['metodo']))
                                <span class="ml-rec-metodo ml-rec-metodo--{{ $rec['metodo'] }}">
                                    @switch($rec['metodo'])
                                        @case('multi_agente')
                                            Consenso multi-agente
                                            @break
                                        @case('hibrido')
                                            Múltiples agentes
                                            @break
                                        @case('arbol')
                                            Árbol de decisión
                                            @break
                                        @case('logistico')
                                            Regresión logística
                                            @break
                                        @case('mlp')
                                            Red neuronal
                                            @break
                                        @case('kmeans')
                                            Perfil K-Means
                                            @break
                                        @case('experto')
                                            Sistema experto
                                            @break
                                        @case('patrones')
                                            Patrones históricos
                                            @break
                                        @default
                                            {{ ucfirst($rec['metodo']) }}
                                    @endswitch
                                    @if(!empty($rec['confianza_ml']))
                                        · {{ $rec['confianza_ml'] }}%
                                    @endif
                                </span>
                            @endif
                            <button
                                type="button"
                                class="ml-rec-btn"
                                data-carrera-id="{{ $rec['carrera_id'] }}"
                                data-carrera-nombre="{{ $rec['carrera'] }}"
                                onclick="openModal(this.dataset.carreraId, this.dataset.carreraNombre)"
                            >
                                Solicitar información
                            </button>
                        </article>
                    @endforeach
                </div>

                <p class="ml-rec-footer">Sistema Inteligente PAT · Multi-agente · scikit-learn · Flask + Laravel</p>
            </div>
        @endif

        <h2 class="section-title">Nuestras <span>Carreras</span></h2>
        <p class="section-sub">Elige la carrera que mejor se adapte a tus objetivos profesionales</p>

        @if($carreras->isEmpty())
            <div class="empty-state">
                <h3>Próximamente nuevas carreras</h3>
                <p>Estamos preparando nuestra oferta académica. Vuelve pronto.</p>
            </div>
        @else
            <div class="carreras-grid">
                @foreach($carreras as $index => $carrera)
                    <article class="carrera-card">
                        <div class="carrera-img" style="background-image: url('{{ $imagenes[$index % count($imagenes)] }}')"></div>
                        <div class="carrera-body">
                            <h3>{{ $carrera->carrera }}</h3>
                            <p>{{ $carrera->descripcion ?? 'Descubre todo lo que esta carrera tiene para ofrecerte.' }}</p>
                            <div class="carrera-actions">
                                <button
                                    class="btn btn-red"
                                    data-carrera-id="{{ $carrera->id }}"
                                    data-carrera-nombre="{{ $carrera->carrera }}"
                                    onclick="openModal(this.dataset.carreraId, this.dataset.carreraNombre)"
                                >
                                    Recibir información
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <div class="modal-header">
                <h2>Recibir información</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form action="{{ route('estudiantes.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="carrera_id" id="carrera_id" value="{{ old('carrera_id') }}">

                    <div class="form-group">
                        <label for="carrera_nombre">Carrera seleccionada</label>
                        <input type="text" id="carrera_nombre" readonly value="{{ old('carrera_id') ? ($carreras->firstWhere('id', old('carrera_id'))?->carrera ?? '') : '' }}">
                    </div>

                    <div class="form-group">
                        <label for="nombre">Nombre completo *</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Juan Pérez">
                        @error('nombre') <div class="alert alert-error" style="margin-top:6px;padding:6px 10px;">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="correo">Correo electrónico *</label>
                        <input type="email" name="correo" id="correo" value="{{ old('correo') }}" required placeholder="Ej: juan@correo.com">
                        @error('correo') <div class="alert alert-error" style="margin-top:6px;padding:6px 10px;">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="celular">Celular (WhatsApp) *</label>
                        <input type="tel" name="celular" id="celular" value="{{ old('celular') }}" required placeholder="Ej: 987654321" pattern="9[0-9]{8}" maxlength="9">
                        <small style="color:#666;font-size:12px;">9 dígitos sin código de país. Ej: 987654321</small>
                        @error('celular') <div class="alert alert-error" style="margin-top:6px;padding:6px 10px;">{{ $message }}</div> @enderror
                    </div>

                    @error('carrera_id') <div class="alert alert-error">{{ $message }}</div> @enderror

                    <button type="submit" class="btn btn-red" style="width:100%;justify-content:center;padding:12px;">
                        Enviar solicitud
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openModal(carreraId, carreraNombre) {
        document.getElementById('modalOverlay').classList.add('active');
        if (carreraId) {
            document.getElementById('carrera_id').value = carreraId;
            document.getElementById('carrera_nombre').value = carreraNombre;
        }
    }

    function closeModal() {
        document.getElementById('modalOverlay').classList.remove('active');
    }

    document.getElementById('modalOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    @if($errors->any() && old('carrera_id'))
        openModal({{ old('carrera_id') }}, '{{ addslashes($carreras->firstWhere('id', old('carrera_id'))?->carrera ?? '') }}');
    @endif
</script>
@endpush
