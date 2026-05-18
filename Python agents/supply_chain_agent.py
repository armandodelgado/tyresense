import sys
import json
import random

# ── Supply Chain KPIs & Thresholds ──
TARGET_QUALITY_PCT = 95.0
TARGET_DELIVERY_PCT = 90.0
MAX_COST_PER_KM = 2.50

class SupplyChainAgent:
    def __init__(self):
        self.suppliers = [
            {"name": "Bridgestone", "rating": 92, "quality": 98.5, "onTime": 96, "claimsAccepted": 90, "costPerTire": 8500, "costPerKm": 1.95},
            {"name": "Michelin", "rating": 95, "quality": 99.1, "onTime": 98, "claimsAccepted": 93, "costPerTire": 9200, "costPerKm": 1.82},
            {"name": "Continental", "rating": 87, "quality": 97.2, "onTime": 92, "claimsAccepted": 85, "costPerTire": 7800, "costPerKm": 2.10},
            {"name": "Goodyear", "rating": 83, "quality": 96.8, "onTime": 89, "claimsAccepted": 82, "costPerTire": 8100, "costPerKm": 2.25},
            {"name": "Hankook", "rating": 72, "quality": 93.5, "onTime": 80, "claimsAccepted": 75, "costPerTire": 6200, "costPerKm": 2.80},
        ]
        
    def evaluate_supplier_scorecard(self):
        """
        Calculates and updates supplier ratings based on quality, delivery, and cost metrics.
        Assigns grades A, B, C, D based on final rating.
        """
        scorecard = []
        for s in self.suppliers:
            # Weighted rating formula
            # Quality (50%), Delivery (30%), Cost Efficiency (20%)
            cost_efficiency = min(100, max(0, (MAX_COST_PER_KM / s["costPerKm"]) * 100)) if s["costPerKm"] else 0
            
            calculated_rating = (s["quality"] * 0.5) + (s["onTime"] * 0.3) + (cost_efficiency * 0.2)
            
            if calculated_rating >= 90:
                grade = "A"
            elif calculated_rating >= 80:
                grade = "B"
            elif calculated_rating >= 70:
                grade = "C"
            else:
                grade = "D"
                
            scorecard.append({
                "supplier": s["name"],
                "calculated_rating": round(calculated_rating, 1),
                "grade": grade,
                "metrics": {
                    "quality": s["quality"],
                    "onTime": s["onTime"],
                    "costPerKm": s["costPerKm"]
                }
            })
            
        return scorecard

    def process_automated_claim(self, vision_result, truck_id, supplier="Hankook", lot="UNKNOWN"):
        """
        Takes output from vision_model.py and automatically generates an IES claim
        if the defect is critical.
        """
        if vision_result.get("severity") == "critical":
            claim_id = f"IES-{random.randint(1000, 9999)}"
            defect_reasons = []
            if vision_result.get("sidewall_crack"):
                defect_reasons.append("Grieta lateral detectada")
            if vision_result.get("embedded"):
                defect_reasons.append("Objeto incrustado detectado")
            if not defect_reasons:
                defect_reasons.append("Defecto crítico detectado por IA")
                
            return {
                "status": "claim_generated",
                "claim": {
                    "id": claim_id,
                    "truck_id": truck_id,
                    "pos": vision_result.get("pos", "Unknown"),
                    "supplier": supplier,
                    "lot": lot,
                    "type": "auto",
                    "severity": "critical",
                    "description": f"{', '.join(defect_reasons)} (Confianza: {vision_result.get('confidence', 0)}%)",
                }
            }
        return {"status": "no_claim_needed"}

    def run_full_analysis(self):
        """
        Executes all supply chain evaluations and returns the overall dashboard state.
        """
        return {
            "scorecard": self.evaluate_supplier_scorecard(),
            "recommendations": self.generate_recommendations()
        }
        
    def generate_recommendations(self):
        """
        Generates actionable insights based on supplier metrics.
        """
        recommendations = []
        for s in self.evaluate_supplier_scorecard():
            if s["grade"] in ["C", "D"]:
                recommendations.append(f"ALERTA: Proveedor {s['supplier']} en grado {s['grade']}. Revisar calidad ({s['metrics']['quality']}%) y tiempos de entrega.")
            if s["metrics"]["costPerKm"] > MAX_COST_PER_KM:
                recommendations.append(f"COSTO: El costo por KM de {s['supplier']} (${s['metrics']['costPerKm']}) excede el límite de ${MAX_COST_PER_KM}.")
        return recommendations

if __name__ == "__main__":
    agent = SupplyChainAgent()
    
    if len(sys.argv) > 1 and sys.argv[1] == "claim":
        # Simulate passing a vision result JSON string
        mock_vision = {
            "pos": "T2Ii", "severity": "critical", "sidewall_crack": True, "confidence": 94.5
        }
        try:
            vision_input = json.loads(sys.argv[2]) if len(sys.argv) > 2 else mock_vision
        except:
            vision_input = mock_vision
            
        truck_id = sys.argv[3] if len(sys.argv) > 3 else "9901"
        supplier = sys.argv[4] if len(sys.argv) > 4 else "Hankook"
        lot = sys.argv[5] if len(sys.argv) > 5 else "LOT-2026-0087"
        
        print(json.dumps(agent.process_automated_claim(vision_input, truck_id, supplier, lot), ensure_ascii=False, indent=2))
    else:
        print(json.dumps(agent.run_full_analysis(), ensure_ascii=False, indent=2))
