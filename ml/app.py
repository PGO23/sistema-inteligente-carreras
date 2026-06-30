"""
API Flask — Sistema Inteligente de Recomendación de Carreras.
Ejecutar: python ml/app.py
"""

import os
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))

from flask import Flask, jsonify, request

from agents import arquitectura
from analytics import resumen_analytics
from expert_system import consideraciones_eticas
from recommend import estado_modelo, recomendar
from train import entrenar

app = Flask(__name__)


@app.get("/health")
def health():
    return jsonify({"ok": True, "servicio": "sistema-inteligente-pat"})


@app.get("/model/status")
def model_status():
    return jsonify(estado_modelo())


@app.get("/analytics")
def analytics():
    return jsonify(resumen_analytics())


@app.get("/agents")
def agents():
    return jsonify(arquitectura())


@app.get("/ethics")
def ethics():
    return jsonify({"consideraciones": consideraciones_eticas()})


@app.post("/train")
def train_models():
    try:
        resultado = entrenar()
        return jsonify({"ok": True, "resultado": resultado})
    except RuntimeError as e:
        return jsonify({"ok": False, "error": str(e)}), 400
    except Exception as e:
        return jsonify({"ok": False, "error": str(e)}), 500


@app.get("/recommend/<int:carrera_id>")
def recommend(carrera_id: int):
    limite = request.args.get("limite", default=3, type=int)
    limite = max(1, min(limite, 10))
    correo = request.args.get("correo", default=None, type=str)

    return jsonify(recomendar(carrera_id, limite, correo))


if __name__ == "__main__":
    port = int(os.getenv("ML_API_PORT", "5001"))
    app.run(host="127.0.0.1", port=port, debug=False)
