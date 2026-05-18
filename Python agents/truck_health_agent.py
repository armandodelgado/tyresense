import sys
import json

# ── Constantes de seguridad ──────────────────────────────
CRITICAL_MIN_PSI    = 80       # PSI mínima absoluta (por debajo = NO-GO)
WARNING_MIN_PSI     = 90       # PSI baja (advertencia)
SAFETY_LIMIT_PCT    = 75       # % de desgaste máximo permitido para viaje
NORMAL_TEMP_RISE    = 15       # °C de incremento normal de temperatura
CRITICAL_LIFE_PCT   = 30       # % de vida útil mínima para circular

# ── Datos simulados de sensores ──────────────────────────
def get_sensor_data(truck_id):
    """
    Simula la lectura de sensores IoT del camión.
    En producción, esto se conectaría a una API de telemetría real.
    """
    return {
        "truck_id": truck_id,
        "tires": [
            {"pos": "D-I",  "label": "Delantera Izq",   "pressure": 110, "temp_rise": 8,  "wear_pct": 18, "life_pct": 82, "status": "ok"},
            {"pos": "D-D",  "label": "Delantera Der",   "pressure": 108, "temp_rise": 9,  "wear_pct": 22, "life_pct": 78, "status": "ok"},
            {"pos": "T1I",  "label": "Tractivo 1 Izq",  "pressure": 102, "temp_rise": 11, "wear_pct": 35, "life_pct": 65, "status": "ok"},
            {"pos": "T1Ii", "label": "Tractivo 1 IzqI", "pressure": 95,  "temp_rise": 13, "wear_pct": 49, "life_pct": 51, "status": "warning"},
            {"pos": "T1Di", "label": "Tractivo 1 DerI", "pressure": 92,  "temp_rise": 14, "wear_pct": 52, "life_pct": 48, "status": "warning"},
            {"pos": "T1D",  "label": "Tractivo 1 Der",  "pressure": 105, "temp_rise": 10, "wear_pct": 30, "life_pct": 70, "status": "ok"},
            {"pos": "T2I",  "label": "Trasero 2 Izq",   "pressure": 104, "temp_rise": 9,  "wear_pct": 26, "life_pct": 74, "status": "ok"},
            {"pos": "T2Ii", "label": "Trasero 2 IzqI",  "pressure": 75,  "temp_rise": 22, "wear_pct": 72, "life_pct": 28, "status": "critical"},
            {"pos": "T2Di", "label": "Trasero 2 DerI",  "pressure": 103, "temp_rise": 10, "wear_pct": 34, "life_pct": 66, "status": "ok"},
            {"pos": "T2D",  "label": "Trasero 2 Der",   "pressure": 106, "temp_rise": 9,  "wear_pct": 28, "life_pct": 72, "status": "ok"},
        ]
    }


class TruckHealthAgent:
    def predict_wear(self, tire_health, distance_km):
        """
        Predice el desgaste adicional basado en la distancia del viaje.
        Fórmula simplificada: cada 1000 km añade ~1.5% de desgaste.
        """
        wear_rate_per_1000km = 1.5
        max_projected = 0
        for t in tire_health:
            projected = t['wear_pct'] + (distance_km / 1000.0) * wear_rate_per_1000km
            if projected > max_projected:
                max_projected = projected
        return max_projected

    def evaluate_trip(self, truck_id, trip_details):
        """
        Evalúa si el camión puede salir a ruta.
        Retorna un diccionario con el veredicto y los detalles.
        """
        telemetry = get_sensor_data(truck_id)
        tire_health = telemetry['tires']

        verdict = "GO"
        reasons = []
        warnings = []
        flagged_tires = []

        # ── 1. Verificar presión crítica (NO-GO inmediato) ──
        for t in tire_health:
            if t['pressure'] < CRITICAL_MIN_PSI:
                verdict = "NO-GO"
                reasons.append(f"Presión crítica en {t['pos']}: {t['pressure']} PSI (mín: {CRITICAL_MIN_PSI})")
                flagged_tires.append(t['pos'])

        # ── 2. Verificar vida útil mínima ──
        for t in tire_health:
            if t['life_pct'] < CRITICAL_LIFE_PCT:
                verdict = "NO-GO"
                reasons.append(f"Vida útil crítica en {t['pos']}: {t['life_pct']}% (mín: {CRITICAL_LIFE_PCT}%)")
                if t['pos'] not in flagged_tires:
                    flagged_tires.append(t['pos'])

        # ── 3. Análisis predictivo (desgaste proyectado) ──
        distance = trip_details.get('distance', 0)
        if distance > 0:
            estimated_wear = self.predict_wear(tire_health, distance)
            if estimated_wear > SAFETY_LIMIT_PCT:
                verdict = "NO-GO"
                reasons.append(f"Desgaste proyectado {estimated_wear:.1f}% excede límite de {SAFETY_LIMIT_PCT}% para {distance} km")

        # ── 4. Decisión contextual (carga pesada + temperatura) ──
        load = trip_details.get('load', 'normal')
        if load == 'heavy':
            for t in tire_health:
                if t['temp_rise'] > NORMAL_TEMP_RISE:
                    verdict = "NO-GO"
                    reasons.append(f"Riesgo térmico en {t['pos']}: +{t['temp_rise']}°C con carga pesada")
                    if t['pos'] not in flagged_tires:
                        flagged_tires.append(t['pos'])

        # ── 5. Advertencias (no bloquean, pero informan) ──
        for t in tire_health:
            if t['pressure'] < WARNING_MIN_PSI and t['pressure'] >= CRITICAL_MIN_PSI:
                warnings.append(f"Presión baja en {t['pos']}: {t['pressure']} PSI")
            if t['status'] == 'warning':
                warnings.append(f"Llanta {t['pos']} en estado de advertencia")

        # ── Calcular resumen de salud ──
        ok_count = sum(1 for t in tire_health if t['status'] == 'ok')
        warn_count = sum(1 for t in tire_health if t['status'] == 'warning')
        crit_count = sum(1 for t in tire_health if t['status'] == 'critical')
        avg_life = sum(t['life_pct'] for t in tire_health) / len(tire_health)

        if verdict == "GO" and warnings:
            verdict = "GO-CAUTION"

        return {
            "verdict": verdict,
            "truck_id": truck_id,
            "reasons": reasons,
            "warnings": warnings,
            "flagged_tires": flagged_tires,
            "summary": {
                "total_tires": len(tire_health),
                "ok": ok_count,
                "warning": warn_count,
                "critical": crit_count,
                "avg_life_pct": round(avg_life, 1),
            },
            "trip": trip_details
        }


if __name__ == "__main__":
    # Leer argumentos: truck_id, distance (km), load (normal/heavy)
    truck_id = sys.argv[1] if len(sys.argv) > 1 else "4821"
    distance = int(sys.argv[2]) if len(sys.argv) > 2 else 500
    load = sys.argv[3] if len(sys.argv) > 3 else "normal"

    trip_details = {
        "distance": distance,
        "load": load,
        "route": "Monterrey - Saltillo"
    }

    agent = TruckHealthAgent()
    result = agent.evaluate_trip(truck_id, trip_details)
    print(json.dumps(result, ensure_ascii=False))
