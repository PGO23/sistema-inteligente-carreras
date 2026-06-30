"""
Recomendador de carreras — Sistema inteligente multi-agente híbrido.

Motores de recomendación (malla curricular):
  1. Sistema experto (reglas SI-ENTONCES)          — Unidad II
  2. Patrones de co-ocurrencia (tiempo real)       — Unidad II
  3. Árbol de decisión                             — Unidad III
  4. Regresión logística                           — Unidad III
  5. Red neuronal (MLP)                            — Unidad III
  6. K-Means (perfiles de estudiantes)             — Unidad III
  7. Fusión probabilística (Agente de Decisión)    — Unidad I/II
"""

from __future__ import annotations

import os
from pathlib import Path

import joblib
import pandas as pd
import pymysql
from dotenv import load_dotenv

from expert_system import recomendar_experto

load_dotenv(Path(__file__).resolve().parent.parent / ".env")

MODELS_DIR = Path(__file__).resolve().parent / "models"
MODEL_PATHS = {
    "arbol": MODELS_DIR / "career_tree.joblib",
    "logistico": MODELS_DIR / "career_logistic.joblib",
    "mlp": MODELS_DIR / "career_mlp.joblib",
    "kmeans": MODELS_DIR / "student_clusters.joblib",
}

PESOS_AGENTES = {
    "patrones": 0.30,
    "experto": 0.15,
    "arbol": 0.20,
    "logistico": 0.15,
    "mlp": 0.12,
    "kmeans": 0.08,
}


def _conexion_mysql():
    return pymysql.connect(
        host=os.getenv("DB_HOST", "127.0.0.1"),
        port=int(os.getenv("DB_PORT", "3306")),
        user=os.getenv("DB_USERNAME", "root"),
        password=os.getenv("DB_PASSWORD", ""),
        database=os.getenv("DB_DATABASE", "registro_estudiantes"),
        charset="utf8mb4",
    )


def estado_modelo() -> dict:
    from analytics import estado_modelos

    modelos = estado_modelos()
    disponibles = sum(1 for m in modelos.values() if m.get("disponible"))
    return {
        "disponible": disponibles > 0,
        "modelos_entrenados": disponibles,
        "detalle": modelos,
    }


def _recomendar_por_patrones(
    solicitudes: pd.DataFrame,
    carreras: pd.DataFrame,
    carrera_id: int,
    limite: int,
    correo: str | None,
) -> list[dict]:
    correos_base = solicitudes.loc[
        solicitudes["carrera_id"] == carrera_id, "correo"
    ].dropna().unique()

    if len(correos_base) < 1:
        return []

    correo_actual = correo.strip().lower() if correo else None
    carreras_usuario: set[int] = set()
    if correo_actual:
        carreras_usuario = set(
            solicitudes.loc[solicitudes["correo"] == correo_actual, "carrera_id"].astype(int)
        )

    otros_correos = [c for c in correos_base if not correo_actual or c != correo_actual]
    correos_analisis = otros_correos if otros_correos else list(correos_base)
    total_base = len(correos_analisis)

    relacionadas = solicitudes[
        solicitudes["correo"].isin(correos_analisis)
        & (solicitudes["carrera_id"] != carrera_id)
        & (~solicitudes["carrera_id"].isin(carreras_usuario))
    ]

    if relacionadas.empty:
        return []

    conteo = (
        relacionadas.groupby("carrera_id")["correo"]
        .nunique()
        .reset_index(name="correos_relacionados")
        .sort_values("correos_relacionados", ascending=False)
        .head(limite)
    )

    nombres = carreras.set_index("id")["carrera"].to_dict()
    resultados: list[dict] = []

    for _, fila in conteo.iterrows():
        cid = int(fila["carrera_id"])
        nombre = nombres.get(cid)
        if not nombre:
            continue
        relacionados = int(fila["correos_relacionados"])
        resultados.append(
            {
                "carrera_id": cid,
                "carrera": nombre,
                "correos_relacionados": relacionados,
                "porcentaje": int(round((relacionados / total_base) * 100)),
                "metodo": "patrones",
                "confianza_ml": int(round((relacionados / total_base) * 100)),
                "agentes": ["patrones"],
            }
        )

    return resultados


def _predicciones_clasificador(
    ruta: Path,
    carrera_id: int,
    limite: int,
    carreras_usuario: set[int],
    nombres: dict[int, str],
    metodo: str,
) -> list[dict]:
    if not ruta.exists():
        return []

    bundle = joblib.load(ruta)
    modelo = bundle["modelo"]
    scaler = bundle.get("scaler")

    if carrera_id not in modelo.classes_:
        return []

    entrada = pd.DataFrame([[carrera_id]], columns=["carrera_base"])
    if scaler is not None:
        entrada = pd.DataFrame(scaler.transform(entrada), columns=["carrera_base"])

    probabilidades = modelo.predict_proba(entrada)[0]
    clases = modelo.classes_

    candidatos: list[tuple[int, float]] = []
    for clase, prob in zip(clases, probabilidades, strict=True):
        cid = int(clase)
        if cid == carrera_id or cid in carreras_usuario or cid not in nombres:
            continue
        candidatos.append((cid, float(prob)))

    candidatos.sort(key=lambda item: item[1], reverse=True)
    resultados: list[dict] = []
    for cid, prob in candidatos[:limite]:
        resultados.append(
            {
                "carrera_id": cid,
                "carrera": nombres[cid],
                "confianza_ml": int(round(prob * 100)),
                "metodo": metodo,
                "agentes": [metodo],
            }
        )

    return resultados


def _predicciones_kmeans(
    correo: str | None,
    carrera_id: int,
    limite: int,
    carreras_usuario: set[int],
    nombres: dict[int, str],
    solicitudes: pd.DataFrame,
    ids_carreras: list[int],
) -> list[dict]:
    if not MODEL_PATHS["kmeans"].exists() or not correo:
        return []

    bundle = joblib.load(MODEL_PATHS["kmeans"])
    columnas = bundle["columnas"]
    ids = bundle["ids_carreras"]
    correos_entrenados = bundle.get("correos", [])
    etiquetas = bundle.get("etiquetas", [])

    if not correos_entrenados or not etiquetas:
        return []

    correo_norm = correo.strip().lower()
    cluster_usuario = None

    if correo_norm in correos_entrenados:
        idx = correos_entrenados.index(correo_norm)
        cluster_usuario = etiquetas[idx]
    else:
        fila = {f"c_{cid}": 0 for cid in ids}
        fila[f"c_{carrera_id}"] = 1
        usuario_df = solicitudes.loc[solicitudes["correo"] == correo_norm]
        for cid in usuario_df["carrera_id"].astype(int).tolist():
            if f"c_{cid}" in fila:
                fila[f"c_{cid}"] = 1
        vector = [[fila[col] for col in columnas]]
        cluster_usuario = int(bundle["modelo"].predict(vector)[0])

    miembros = [
        correos_entrenados[i]
        for i, etiqueta in enumerate(etiquetas)
        if etiqueta == cluster_usuario
    ]

    if not miembros:
        return []

    del_cluster = solicitudes[
        solicitudes["correo"].isin(miembros)
        & (solicitudes["carrera_id"] != carrera_id)
        & (~solicitudes["carrera_id"].isin(carreras_usuario))
    ]

    if del_cluster.empty:
        return []

    conteo = (
        del_cluster.groupby("carrera_id")["correo"]
        .nunique()
        .reset_index(name="frecuencia")
        .sort_values("frecuencia", ascending=False)
        .head(limite)
    )

    total = max(len(miembros), 1)
    resultados: list[dict] = []
    for _, fila in conteo.iterrows():
        cid = int(fila["carrera_id"])
        if cid not in nombres:
            continue
        freq = int(fila["frecuencia"])
        resultados.append(
            {
                "carrera_id": cid,
                "carrera": nombres[cid],
                "confianza_ml": int(round((freq / total) * 100)),
                "metodo": "kmeans",
                "agentes": ["kmeans"],
            }
        )

    return resultados


def _fusionar_probabilistico(
    fuentes: dict[str, list[dict]],
    nombres: dict[int, str],
    limite: int,
) -> list[dict]:
    """
    Agente de Decisión: combina señales con razonamiento probabilístico ponderado.
    """
    scores: dict[int, dict] = {}

    for agente, items in fuentes.items():
        peso = PESOS_AGENTES.get(agente, 0.1)
        for item in items:
            cid = item["carrera_id"]
            confianza = item.get("confianza_ml") or item.get("porcentaje") or 0
            puntaje = (confianza / 100.0) * peso

            if cid not in scores:
                scores[cid] = {
                    "carrera_id": cid,
                    "carrera": item.get("carrera") or nombres.get(cid, ""),
                    "puntaje": 0.0,
                    "agentes": set(),
                    "correos_relacionados": item.get("correos_relacionados", 0),
                }

            scores[cid]["puntaje"] += puntaje
            scores[cid]["agentes"].update(item.get("agentes", [agente]))
            if item.get("correos_relacionados", 0) > scores[cid]["correos_relacionados"]:
                scores[cid]["correos_relacionados"] = item["correos_relacionados"]

    if not scores:
        return []

    ordenados = sorted(scores.values(), key=lambda x: x["puntaje"], reverse=True)
    resultados: list[dict] = []

    for item in ordenados[:limite]:
        agentes = sorted(item["agentes"])
        porcentaje = int(round(item["puntaje"] * 100))
        porcentaje = max(porcentaje, 1)

        if len(agentes) >= 3:
            metodo = "multi_agente"
        elif len(agentes) == 2:
            metodo = "hibrido"
        else:
            metodo = agentes[0] if agentes else "desconocido"

        resultados.append(
            {
                "carrera_id": item["carrera_id"],
                "carrera": item["carrera"],
                "correos_relacionados": item["correos_relacionados"],
                "porcentaje": porcentaje,
                "confianza_ml": porcentaje,
                "metodo": metodo,
                "agentes": agentes,
            }
        )

    return resultados


def recomendar(carrera_id: int, limite: int = 3, correo: str | None = None) -> list[dict]:
    with _conexion_mysql() as conn:
        solicitudes = pd.read_sql("SELECT correo, carrera_id FROM estudiantes", conn)
        carreras = pd.read_sql("SELECT id, carrera FROM carreras", conn)

    if solicitudes.empty and carreras.empty:
        return []

    solicitudes = solicitudes.copy()
    solicitudes["correo"] = solicitudes["correo"].astype(str).str.strip().str.lower()

    correo_actual = correo.strip().lower() if correo else None
    carreras_usuario: set[int] = set()
    if correo_actual:
        carreras_usuario = set(
            solicitudes.loc[solicitudes["correo"] == correo_actual, "carrera_id"].astype(int)
        )

    nombres = carreras.set_index("id")["carrera"].to_dict()
    ids_carreras = sorted(carreras["id"].astype(int).tolist())

    fuentes = {
        "patrones": _recomendar_por_patrones(solicitudes, carreras, carrera_id, limite, correo),
        "experto": [
            {**r, "metodo": "experto", "agentes": ["experto"]}
            for r in recomendar_experto(carrera_id, nombres, carreras_usuario, limite)
        ],
        "arbol": _predicciones_clasificador(
            MODEL_PATHS["arbol"], carrera_id, limite, carreras_usuario, nombres, "arbol"
        ),
        "logistico": _predicciones_clasificador(
            MODEL_PATHS["logistico"], carrera_id, limite, carreras_usuario, nombres, "logistico"
        ),
        "mlp": _predicciones_clasificador(
            MODEL_PATHS["mlp"], carrera_id, limite, carreras_usuario, nombres, "mlp"
        ),
        "kmeans": _predicciones_kmeans(
            correo, carrera_id, limite, carreras_usuario, nombres, solicitudes, ids_carreras
        ),
    }

    return _fusionar_probabilistico(fuentes, nombres, limite)
