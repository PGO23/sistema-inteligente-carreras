"""
Análisis de datos y métricas del sistema — Unidad II y III.
"""

from __future__ import annotations

from pathlib import Path

import joblib
import pandas as pd

from recommend import MODEL_PATHS, _conexion_mysql

MODELS_DIR = Path(__file__).resolve().parent / "models"


def _estado_modelo(ruta: Path, clave_algoritmo: str = "algoritmo") -> dict:
    if not ruta.exists():
        return {"disponible": False, "archivo": ruta.name}

    bundle = joblib.load(ruta)
    meta = bundle.get("meta", {})
    return {
        "disponible": True,
        "archivo": ruta.name,
        "algoritmo": meta.get(clave_algoritmo, meta.get("algoritmo", "—")),
        "entrenado_en": meta.get("entrenado_en"),
        "muestras": meta.get("muestras"),
        "precision_pct": meta.get("precision_pct"),
        "clusters": meta.get("clusters"),
    }


def estado_modelos() -> dict:
    return {
        "arbol": _estado_modelo(MODEL_PATHS["arbol"]),
        "logistico": _estado_modelo(MODEL_PATHS["logistico"]),
        "mlp": _estado_modelo(MODEL_PATHS["mlp"]),
        "kmeans": _estado_modelo(MODEL_PATHS["kmeans"]),
    }


def estadisticas_dataset() -> dict:
    with _conexion_mysql() as conn:
        solicitudes = pd.read_sql("SELECT correo, carrera_id FROM estudiantes", conn)
        carreras = pd.read_sql("SELECT id, carrera FROM carreras", conn)

    if solicitudes.empty:
        return {
            "total_solicitudes": 0,
            "estudiantes_unicos": 0,
            "carreras_registradas": len(carreras),
            "pares_entrenamiento": 0,
            "distribucion": [],
        }

    solicitudes["correo"] = solicitudes["correo"].astype(str).str.strip().str.lower()
    estudiantes_unicos = solicitudes["correo"].nunique()

    pares = 0
    for _, grupo in solicitudes.groupby("correo"):
        ids = grupo["carrera_id"].astype(int).unique().tolist()
        pares += sum(1 for b in ids for r in ids if b != r)

    nombres = carreras.set_index("id")["carrera"].to_dict()
    distribucion = (
        solicitudes.groupby("carrera_id")
        .size()
        .reset_index(name="solicitudes")
        .sort_values("solicitudes", ascending=False)
    )

    dist_lista = [
        {
            "carrera_id": int(row["carrera_id"]),
            "carrera": nombres.get(int(row["carrera_id"]), f"ID {row['carrera_id']}"),
            "solicitudes": int(row["solicitudes"]),
        }
        for _, row in distribucion.iterrows()
    ]

    return {
        "total_solicitudes": int(len(solicitudes)),
        "estudiantes_unicos": int(estudiantes_unicos),
        "carreras_registradas": int(len(carreras)),
        "pares_entrenamiento": int(pares),
        "distribucion": dist_lista,
    }


def resumen_analytics() -> dict:
    return {
        "dataset": estadisticas_dataset(),
        "modelos": estado_modelos(),
    }
