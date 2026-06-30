"""
Entrena múltiples modelos de Machine Learning con solicitudes reales de MySQL.

Algoritmos (Unidad III):
  - DecisionTreeClassifier (árboles de decisión)
  - LogisticRegression (regresión logística, clasificación multiclase)
  - MLPClassifier (red neuronal / deep learning básico)
  - KMeans (aprendizaje no supervisado — perfiles de estudiantes)

Ejecutar: python ml/train.py
"""

from __future__ import annotations

import json
from datetime import datetime, timezone
from pathlib import Path

import joblib
import numpy as np
import pandas as pd
from sklearn.cluster import KMeans
from sklearn.linear_model import LogisticRegression
from sklearn.model_selection import train_test_split
from sklearn.neural_network import MLPClassifier
from sklearn.preprocessing import StandardScaler
from sklearn.tree import DecisionTreeClassifier

from recommend import MODEL_PATHS, _conexion_mysql

MODELS_DIR = Path(__file__).resolve().parent / "models"


def _construir_pares(solicitudes: pd.DataFrame) -> tuple[pd.DataFrame, pd.Series]:
    filas: list[dict[str, int]] = []

    for _, grupo in solicitudes.groupby("correo"):
        carreras = grupo["carrera_id"].astype(int).unique().tolist()
        for base in carreras:
            for relacionada in carreras:
                if base != relacionada:
                    filas.append({"carrera_base": base, "carrera_relacionada": relacionada})

    if not filas:
        return pd.DataFrame(columns=["carrera_base"]), pd.Series(dtype=int)

    dataset = pd.DataFrame(filas)
    return dataset[["carrera_base"]], dataset["carrera_relacionada"]


def _construir_matriz_clusters(solicitudes: pd.DataFrame, ids_carreras: list[int]) -> pd.DataFrame:
    """Una fila por estudiante, columnas binarias por carrera (interés sí/no)."""
    filas: list[dict[str, int]] = []

    for correo, grupo in solicitudes.groupby("correo"):
        fila = {"correo": correo}
        carreras_est = set(grupo["carrera_id"].astype(int).tolist())
        for cid in ids_carreras:
            fila[f"c_{cid}"] = 1 if cid in carreras_est else 0
        filas.append(fila)

    return pd.DataFrame(filas)


def _entrenar_clasificador(
    x: pd.DataFrame,
    y: pd.Series,
    modelo,
    nombre_algoritmo: str,
    requiere_escala: bool = False,
) -> tuple[object, object | None, float | None]:
    precision = None
    scaler = None
    x_train = x

    if len(x) >= 8 and y.nunique() >= 2:
        x_train, x_test, y_train, y_test = train_test_split(
            x, y, test_size=0.25, random_state=42
        )
        if requiere_escala:
            scaler = StandardScaler()
            x_train = pd.DataFrame(scaler.fit_transform(x_train), columns=x.columns)
            x_test_scaled = scaler.transform(x_test)
            modelo.fit(x_train, y_train)
            precision = round(float(modelo.score(x_test_scaled, y_test)) * 100, 1)
        else:
            modelo.fit(x_train, y_train)
            precision = round(float(modelo.score(x_test, y_test)) * 100, 1)
    else:
        if requiere_escala:
            scaler = StandardScaler()
            x_train = pd.DataFrame(scaler.fit_transform(x), columns=x.columns)
        modelo.fit(x_train, y)

    return modelo, scaler, precision


def entrenar() -> dict:
    with _conexion_mysql() as conn:
        solicitudes = pd.read_sql("SELECT correo, carrera_id FROM estudiantes", conn)
        carreras = pd.read_sql("SELECT id, carrera FROM carreras", conn)

    solicitudes["correo"] = solicitudes["correo"].astype(str).str.strip().str.lower()
    nombres = carreras.set_index("id")["carrera"].to_dict()
    ids_carreras = sorted(carreras["id"].astype(int).tolist())

    x, y = _construir_pares(solicitudes)

    if len(x) < 4 or y.nunique() < 2:
        raise RuntimeError(
            "No hay suficientes pares para entrenar. "
            "Ejecuta: php artisan db:seed --class=SolicitudesDemoSeeder"
        )

    MODELS_DIR.mkdir(parents=True, exist_ok=True)
    ahora = datetime.now(timezone.utc).isoformat()
    resultados: dict = {"entrenado_en": ahora, "modelos": {}}

    # --- Árbol de decisión ---
    arbol = DecisionTreeClassifier(max_depth=4, min_samples_leaf=1, random_state=42)
    modelo_arbol, _, prec_arbol = _entrenar_clasificador(x, y, arbol, "DecisionTreeClassifier")
    meta_arbol = {
        "entrenado_en": ahora,
        "muestras": int(len(x)),
        "precision_pct": prec_arbol,
        "algoritmo": "DecisionTreeClassifier",
    }
    joblib.dump({"modelo": modelo_arbol, "nombres": nombres, "meta": meta_arbol}, MODEL_PATHS["arbol"])
    resultados["modelos"]["arbol"] = meta_arbol

    # --- Regresión logística ---
    logistico = LogisticRegression(max_iter=500, random_state=42)
    modelo_log, scaler_log, prec_log = _entrenar_clasificador(
        x, y, logistico, "LogisticRegression", requiere_escala=True
    )
    meta_log = {
        "entrenado_en": ahora,
        "muestras": int(len(x)),
        "precision_pct": prec_log,
        "algoritmo": "LogisticRegression",
    }
    joblib.dump(
        {"modelo": modelo_log, "scaler": scaler_log, "nombres": nombres, "meta": meta_log},
        MODEL_PATHS["logistico"],
    )
    resultados["modelos"]["logistico"] = meta_log

    # --- Red neuronal (MLP) ---
    mlp = MLPClassifier(hidden_layer_sizes=(32, 16), max_iter=800, random_state=42)
    modelo_mlp, scaler_mlp, prec_mlp = _entrenar_clasificador(
        x, y, mlp, "MLPClassifier", requiere_escala=True
    )
    meta_mlp = {
        "entrenado_en": ahora,
        "muestras": int(len(x)),
        "precision_pct": prec_mlp,
        "algoritmo": "MLPClassifier",
    }
    joblib.dump(
        {"modelo": modelo_mlp, "scaler": scaler_mlp, "nombres": nombres, "meta": meta_mlp},
        MODEL_PATHS["mlp"],
    )
    resultados["modelos"]["mlp"] = meta_mlp

    # --- K-Means (no supervisado) ---
    if len(solicitudes["correo"].unique()) >= 3 and len(ids_carreras) >= 2:
        matriz = _construir_matriz_clusters(solicitudes, ids_carreras)
        cols = [f"c_{cid}" for cid in ids_carreras]
        features = matriz[cols].values
        n_clusters = min(3, len(matriz))
        kmeans = KMeans(n_clusters=n_clusters, random_state=42, n_init=10)
        etiquetas = kmeans.fit_predict(features)

        meta_kmeans = {
            "entrenado_en": ahora,
            "muestras": int(len(matriz)),
            "precision_pct": None,
            "algoritmo": "KMeans",
            "clusters": int(n_clusters),
        }
        joblib.dump(
            {
                "modelo": kmeans,
                "columnas": cols,
                "ids_carreras": ids_carreras,
                "correos": matriz["correo"].tolist(),
                "etiquetas": etiquetas.tolist(),
                "nombres": nombres,
                "meta": meta_kmeans,
            },
            MODEL_PATHS["kmeans"],
        )
        resultados["modelos"]["kmeans"] = meta_kmeans

    return resultados


if __name__ == "__main__":
    info = entrenar()
    print("Modelos guardados en:", MODELS_DIR)
    print(json.dumps(info, indent=2, ensure_ascii=False))
