"""
Sistema experto basado en reglas — Unidad II (sistemas expertos, representación del conocimiento).

Motor de inferencia proposicional: SI el nombre de la carrera base contiene ciertos términos
ENTONCES sugerir carreras cuyo nombre contiene términos afines, con un nivel de confianza fijo.
"""

from __future__ import annotations

import json
from pathlib import Path

KNOWLEDGE_PATH = Path(__file__).resolve().parent / "knowledge_base.json"


def _cargar_reglas() -> list[dict]:
    if not KNOWLEDGE_PATH.exists():
        return []
    with KNOWLEDGE_PATH.open(encoding="utf-8") as f:
        data = json.load(f)
    return data.get("reglas", [])


def _normalizar(texto: str) -> str:
    return texto.lower().strip()


def _contiene_termino(nombre: str, terminos: list[str]) -> bool:
    nombre_norm = _normalizar(nombre)
    return any(term in nombre_norm for term in terminos)


def recomendar_experto(
    carrera_id: int,
    nombres: dict[int, str],
    carreras_usuario: set[int],
    limite: int,
) -> list[dict]:
    """
    Aplica reglas SI-ENTONCES de la base de conocimiento sobre nombres de carreras.
    """
    nombre_base = nombres.get(carrera_id)
    if not nombre_base:
        return []

    reglas = _cargar_reglas()
    candidatos: dict[int, dict] = {}

    for regla in reglas:
        if not _contiene_termino(nombre_base, regla.get("si_contiene", [])):
            continue

        for cid, nombre in nombres.items():
            if cid == carrera_id or cid in carreras_usuario:
                continue
            if not _contiene_termino(nombre, regla.get("sugiere_contiene", [])):
                continue

            confianza = int(regla.get("confianza", 70))
            existente = candidatos.get(cid)
            if existente is None or confianza > existente["confianza_ml"]:
                candidatos[cid] = {
                    "carrera_id": cid,
                    "carrera": nombre,
                    "confianza_ml": confianza,
                    "regla_id": regla.get("id"),
                    "justificacion": regla.get("justificacion", ""),
                }

    ordenados = sorted(candidatos.values(), key=lambda x: x["confianza_ml"], reverse=True)
    return ordenados[:limite]


def consideraciones_eticas() -> list[str]:
    if not KNOWLEDGE_PATH.exists():
        return []
    with KNOWLEDGE_PATH.open(encoding="utf-8") as f:
        data = json.load(f)
    return data.get("consideraciones_eticas", [])
