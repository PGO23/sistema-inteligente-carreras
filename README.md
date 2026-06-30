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

## Instalación paso a paso

### 1. Clonar el proyecto

```bash
git clone https://github.com/PGO23/sistema-inteligente-carreras.git
cd sistema-inteligente-carreras
```

### 2. Dependencias

```bash
# Laravel (PHP)
composer install

# Microservicio ML (Python)
pip install -r ml/requirements.txt
```

### 3. Configuración

```bash
cp .env.example .env
php artisan key:generate
```

Edita el archivo `.env` con tus datos de MySQL:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=registro_estudiantes
DB_USERNAME=root
DB_PASSWORD=

ML_API_URL=http://127.0.0.1:5001
```

### 4. Base de datos

Crea la base de datos vacía (en phpMyAdmin de XAMPP o por consola):
```sql
CREATE DATABASE registro_estudiantes;
```

Crea las tablas con las migraciones:
```bash
php artisan migrate
```

Crea el enlace para los PDFs de mallas:
```bash
php artisan storage:link
```

### 5. Datos de demostración (recomendado para la primera prueba)

Genera estudiantes y carreras de ejemplo con patrones consistentes:
```bash
php artisan db:seed --class=SolicitudesDemoSeeder
```

### 6. Entrenar los modelos de IA

```bash
python ml/train.py
```
(o desde el panel web `/sistemas/ia` con el botón "Entrenar todos los modelos")

## Uso (cómo ejecutarlo)

Necesitas **2 terminales abiertas al mismo tiempo**:

```bash
# Terminal 1 — Servidor web Laravel
php artisan serve
# Queda en http://127.0.0.1:8000

# Terminal 2 — API de Machine Learning (Python/Flask)
python ml/app.py
# Queda en http://127.0.0.1:5001
```

Luego abre en el navegador:
- `http://127.0.0.1:8000` → sitio público: elige una carrera, llena el formulario y verás las recomendaciones de IA.
- `http://127.0.0.1:8000/sistemas/ia` → panel del sistema inteligente: modelos, precisión, agentes y ética.

### Verificación rápida

- `http://127.0.0.1:5001/health` debe responder `{"ok": true, ...}` → la API de IA está activa.

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
