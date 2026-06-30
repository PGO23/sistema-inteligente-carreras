@extends('layouts.sistemas')

@section('title', 'Gestión de Carreras - Sistemas PAT')

@section('content')
    <div class="page-header">
        <h1>Gestión de Carreras</h1>
        <p>Registra nuevas carreras y administra la oferta académica de la universidad</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <h2>Registrar nueva carrera</h2>
        <form action="{{ route('sistemas.carreras.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="carrera">Nombre de la carrera *</label>
                <input type="text" name="carrera" id="carrera" value="{{ old('carrera') }}" required placeholder="Ej: Ingeniería de Sistemas">
                @error('carrera') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" id="descripcion" placeholder="Describe brevemente la carrera...">{{ old('descripcion') }}</textarea>
                @error('descripcion') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label>Malla curricular (PDF) *</label>
                <div class="file-input">
                    <input type="file" name="malla_curricular" id="malla_curricular" accept=".pdf" required onchange="updateFileName(this)">
                    <label for="malla_curricular">
                        <strong>Adjuntar PDF</strong><br>
                        <span id="file-name">Haz clic para seleccionar el archivo de malla curricular</span>
                    </label>
                </div>
                @error('malla_curricular') <div class="error">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-red">Guardar carrera</button>
        </form>
    </div>

    <div class="card">
        <h2>Carreras registradas ({{ $carreras->count() }})</h2>

        @if($carreras->isEmpty())
            <p style="color:#888;">No hay carreras registradas aún.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Carrera</th>
                        <th>Descripción</th>
                        <th>Malla</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($carreras as $carrera)
                        <tr>
                            <td>{{ $carrera->id }}</td>
                            <td><strong>{{ $carrera->carrera }}</strong></td>
                            <td>{{ Str::limit($carrera->descripcion, 80) }}</td>
                            <td>
                                @if($carrera->malla_curricular)
                                    <span class="badge-pdf">
                                        <a href="{{ asset('storage/' . $carrera->malla_curricular) }}" target="_blank">PDF</a>
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    function updateFileName(input) {
        const span = document.getElementById('file-name');
        span.textContent = input.files.length ? input.files[0].name : 'Haz clic para seleccionar el archivo de malla curricular';
    }
</script>
@endpush
