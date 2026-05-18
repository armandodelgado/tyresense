import requests
import sys
import json
import os

try:
    from ultralytics import YOLO
    HAS_YOLO = True
except ImportError:
    HAS_YOLO = False

def process_image(image_path, use_local=True):
    """
    Process image using local YOLOv8 model if available, 
    otherwise falls back to Roboflow API, or mock data.
    """
    # Mock data in case both fail
    mock_result = {
        "pos": "T2Ii",
        "life_pct": 28,
        "severity": "critical",
        "tread_wear": 72,
        "sidewall_crack": True,
        "embedded": True,
        "low_pressure": True,
        "ot_number": "7731",
        "confidence": 94,
        "source": "mock"
    }
    
    if not os.path.exists(image_path):
        return {"error": f"Image not found at {image_path}", "fallback": mock_result}

    # ── 1. Try Local YOLO Model ──
    if getattr(sys, 'frozen', False):
        base_path = sys._MEIPASS
    else:
        base_path = os.path.dirname(__file__)
        
    local_model_path = os.path.join(base_path, "runs", "classify", "tyre_runs", "local_model", "weights", "best.pt")
    
    if use_local and HAS_YOLO and os.path.exists(local_model_path):
        try:
            model = YOLO(local_model_path)
            # Run inference
            results = model(image_path, verbose=False)
            result_obj = results[0]
            
            # For classification, probs contains the probabilities
            probs = result_obj.probs
            top1_index = probs.top1
            top1_conf = float(probs.top1conf) * 100
            class_name = result_obj.names[top1_index].lower()
            
            vision_output = {
                "pos": "T2Ii",
                "source": "local_yolo",
                "class_detected": class_name,
                "confidence": round(top1_conf, 1),
                "severity": "critical" if "defective" in class_name else "ok",
                "tread_wear": 10,
                "sidewall_crack": False,
                "embedded": False,
                "low_pressure": False,
                "ot_number": "7731",
                "life_pct": 90
            }
            
            if vision_output["severity"] == "critical":
                vision_output["life_pct"] = 25
                vision_output["tread_wear"] = 75
                vision_output["sidewall_crack"] = True
            
            return vision_output
            
        except Exception as e:
            print(f"Error running local model: {e}", file=sys.stderr)
            # Fall through to API

    # ── 2. Try Roboflow API ──
    workflow_id = os.environ.get("ROBOFLOW_WORKFLOW_ID", "tyresense-inspection-v1")
    api_key = os.environ.get("ROBOFLOW_API_KEY", "mock_key")

    url = f"https://serverless.roboflow.com/workflow/{workflow_id}?api_key={api_key}"
    
    try:
        with open(image_path, "rb") as img_file:
            response = requests.post(
                url, 
                files={"image": img_file}, 
                data={"workspace_name": "alejandro-castro", "parameters": '{"classes":"Good, Defected"}'}, 
                timeout=5
            )
            
            if response.status_code == 200:
                api_json = response.json()
                
                result = {
                    "pos": "T2Ii",
                    "source": "roboflow_api",
                    "api_response": api_json,
                    "confidence": 0,
                    "severity": "ok",
                    "tread_wear": 10,
                    "sidewall_crack": False,
                    "embedded": False,
                    "low_pressure": False,
                    "ot_number": "7731",
                    "life_pct": 90
                }
                
                predictions = api_json.get("predictions", [])
                if isinstance(predictions, list):
                    for p in predictions:
                        c_name = p.get("class", "").lower()
                        conf = p.get("confidence", 0) * 100
                        if conf > result["confidence"]:
                            result["confidence"] = round(conf, 1)
                        if "crack" in c_name or "grieta" in c_name or "defective" in c_name:
                            result["sidewall_crack"] = True
                            result["severity"] = "critical"
                        elif "embedded" in c_name or "incrustado" in c_name:
                            result["embedded"] = True
                            result["severity"] = "critical"
                
                if result["severity"] == "critical":
                    result["life_pct"] = 25
                    result["tread_wear"] = 75
                elif result["severity"] == "warning":
                    result["life_pct"] = 50
                    result["tread_wear"] = 50
                    
                return result
            else:
                return mock_result
    except Exception as e:
        mock_result['error_details'] = str(e)
        return mock_result

if __name__ == "__main__":
    image_path = sys.argv[1] if len(sys.argv) > 1 else "YOUR_IMAGE.jpg"
    result = process_image(image_path)
    print(json.dumps(result, ensure_ascii=False))
