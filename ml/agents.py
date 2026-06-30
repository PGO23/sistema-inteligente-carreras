"""
Arquitectura de agentes inteligentes — Unidad I y II.

Define los agentes del sistema autónomo de recomendación de carreras:
  - AgenteSensor: percibe entradas del entorno (formulario web).
  - AgenteConocimiento: razona con reglas (sistema experto).
  - AgenteAprendizaje: predice con modelos ML entrenados.
  - AgentePatrones: analiza co-ocurrencias en tiempo real.
  - AgenteDecision: fusiona señales y selecciona recomendaciones.
"""

from __future__ import annotations

AGENTES = [
    {
        "id": "sensor",
        "nombre": "Agente Sensor",
        "tipo": "Percepción",
        "rol": "Capta datos del entorno: nombre, correo, celular y carrera solicitada.",
        "sensores": ["Formulario web (Laravel)", "Base de datos MySQL"],
        "actuadores": [],
        "unidad_curricular": "I — Sensores y actuadores en agentes inteligentes",
    },
    {
        "id": "conocimiento",
        "nombre": "Agente de Conocimiento",
        "tipo": "Razonamiento simbólico",
        "rol": "Aplica reglas SI-ENTONCES de la base de conocimiento (sistema experto).",
        "sensores": ["knowledge_base.json", "Catálogo de carreras"],
        "actuadores": ["Lista de carreras afines con confianza"],
        "unidad_curricular": "II — Agentes basados en conocimiento, lógica proposicional",
    },
    {
        "id": "patrones",
        "nombre": "Agente de Patrones",
        "tipo": "Análisis en tiempo real",
        "rol": "Detecta co-ocurrencias entre solicitudes de estudiantes con intereses similares.",
        "sensores": ["Tabla estudiantes (MySQL)"],
        "actuadores": ["Ranking por frecuencia de co-solicitud"],
        "unidad_curricular": "II — Búsqueda informada, proyecciones probabilísticas",
    },
    {
        "id": "aprendizaje",
        "nombre": "Agente de Aprendizaje",
        "tipo": "Machine Learning",
        "rol": "Predice carreras relacionadas con árbol de decisión, regresión logística y red neuronal.",
        "sensores": ["Dataset de pares carrera_base → carrera_relacionada"],
        "actuadores": ["Probabilidades de clasificación (predict_proba)"],
        "unidad_curricular": "III — Árboles, regresión logística, redes neuronales",
    },
    {
        "id": "clustering",
        "nombre": "Agente de Agrupamiento",
        "tipo": "Aprendizaje no supervisado",
        "rol": "Agrupa perfiles de estudiantes con K-Means y sugiere carreras del mismo cluster.",
        "sensores": ["Matriz de intereses por estudiante"],
        "actuadores": ["Recomendaciones por perfil de cluster"],
        "unidad_curricular": "III — Algoritmos de aprendizaje no supervisado",
    },
    {
        "id": "decision",
        "nombre": "Agente de Decisión",
        "tipo": "Coordinación multi-agente",
        "rol": "Fusiona señales de todos los agentes con razonamiento probabilístico ponderado.",
        "sensores": ["Salidas de agentes especializados"],
        "actuadores": ["Recomendaciones finales al estudiante"],
        "unidad_curricular": "I — Toma de decisiones · II — Comunicación entre agentes",
    },
]


def arquitectura() -> dict:
    return {
        "sistema": "Recomendador Inteligente de Carreras — Universidad PAT",
        "paradigma": "Sistema multi-agente con fusión híbrida (reglas + ML supervisado + no supervisado)",
        "agentes": AGENTES,
        "flujo": [
            "1. Agente Sensor recibe la solicitud del estudiante.",
            "2. Agentes especializados procesan en paralelo (conocimiento, patrones, ML, clustering).",
            "3. Agente de Decisión fusiona resultados con pesos probabilísticos.",
            "4. Laravel presenta las recomendaciones al estudiante.",
        ],
    }
