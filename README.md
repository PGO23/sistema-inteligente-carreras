# Sistema Inteligente de Recomendación de Carreras — Universidad PAT

Proyecto del curso de **Inteligencia Artificial / Machine Learning** que implementa un sistema autónomo multi-agente para orientar a estudiantes interesados en carreras universitarias.

## Descripción

La aplicación capta solicitudes de información vía formulario web (Laravel), las almacena en MySQL y utiliza un **microservicio Python (Flask)** con múltiples motores de IA para recomendar carreras afines. El sistema combina razonamiento simbólico, aprendizaje supervisado y no supervisado.

## Arquitectura

```
[Laravel 12]  ←→  HTTP  ←→  [Flask ML API :5001]
     │                              │
  MySQL (estudiantes, carreras)  ←──┘
```

### Agentes inteligentes (Unidad I y II)

| Agente | Función |
|--------|---------|
| Sensor | Capta datos del formulario web |
| Conocimiento | Sistema experto con reglas SI-ENTONCES |
| Patrones | Co-ocurrencias en tiempo real |
| Aprendizaje | Árbol, regresión logística, red neuronal |
| Agrupamiento | K-Means sobre perfiles de estudiantes |
| Decisión | Fusión probabilística ponderada |

## Algoritmos implementados (Unidad III)

- **DecisionTreeClassifier** — Árboles de decisión
- **LogisticRegression** — Regresión logística (clasificación multiclase)
- **MLPClassifier** — Red neuronal (32→16 neuronas)
- **KMeans** — Aprendizaje no supervisado (perfiles de interés)

## Requisitos

- PHP 8.2+, Composer, Laravel 12
- MySQL (XAMPP recomendado)
- Python 3.10+
- Node.js (opcional, para assets)

## Instalación

```bash
# Laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link

# Python ML
pip install -r ml/requirements.txt
```

Configurar en `.env`:
```
DB_DATABASE=registro_estudiantes
ML_API_URL=http://127.0.0.1:5001
```

## Uso

```bash
# Terminal 1 — Laravel
php artisan serve

# Terminal 2 — API de Machine Learning
python ml/app.py

# Datos de demostración (opcional)
php artisan db:seed --class=SolicitudesDemoSeeder

# Entrenar modelos
python ml/train.py
# o desde el panel: /sistemas/ia → "Entrenar todos los modelos"
```

## Rutas principales

| Ruta | Descripción |
|------|-------------|
| `/` | Sitio público — formulario y recomendaciones |
| `/sistemas/carreras` | Panel — gestión de carreras |
| `/sistemas/ia` | Panel — sistema inteligente, modelos, ética |

## API Flask

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/health` | GET | Estado del servicio |
| `/recommend/<carrera_id>` | GET | Recomendaciones |
| `/model/status` | GET | Estado de modelos |
| `/analytics` | GET | Estadísticas del dataset |
| `/agents` | GET | Arquitectura de agentes |
| `/ethics` | GET | Consideraciones éticas |
| `/train` | POST | Entrenar todos los modelos |

## Estructura ML

```
ml/
├── app.py              # API Flask
├── recommend.py        # Motor multi-agente + fusión
├── train.py            # Entrenamiento de modelos
├── expert_system.py    # Sistema experto (reglas)
├── agents.py           # Definición de agentes
├── analytics.py        # Métricas y estadísticas
├── knowledge_base.json # Base de conocimiento
└── models/             # Modelos entrenados (.joblib)
```

## Alineación curricular

El proyecto cubre temas de las tres unidades del sílabo: fundamentos de IA y agentes inteligentes (I), sistemas expertos y razonamiento probabilístico (II), y machine learning con consideraciones éticas (III).

## Licencia

Proyecto académico — Universidad PAT.
