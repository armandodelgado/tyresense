import sys
import json
import os
import random
import shutil
from datetime import datetime
from fastapi import FastAPI, HTTPException, Request, UploadFile, File, Form
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel

# Import agents
# Assuming they are in the same folder
try:
    from vision_model import process_image
    from truck_health_agent import TruckHealthAgent
    from supply_chain_agent import SupplyChainAgent
except ImportError as e:
    print(f"Error importing agents: {e}")
    sys.exit(1)

app = FastAPI(title="TyreSense API", description="Orchestrator for Local Demo")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

class InspectionRequest(BaseModel):
    image_path: str = None
    truck_id: str = "9901"

@app.post("/api/inspect_tire")
async def inspect_tire(req: InspectionRequest):
    """
    1. Vision Model: Processes image to detect defects
    2. Truck Health: Evaluates if truck can travel
    3. Supply Chain: Generates IES claim and updates supplier metrics
    """
    
    image_path = req.image_path
    
    # If no specific image provided, pick a random one from the test dataset
    if not image_path:
        dataset_path = r"C:\Users\alexc\Downloads\tyresense_app\{css,js,images}\Tyre Quality.v1i.folder\test"
        if os.path.exists(dataset_path):
            classes = ["defective", "perfect"]
            random_class = random.choice(classes)
            class_dir = os.path.join(dataset_path, random_class)
            
            if os.path.exists(class_dir):
                images = [f for f in os.listdir(class_dir) if f.endswith(('.jpg', '.png', '.jpeg'))]
                if images:
                    random_image = random.choice(images)
                    image_path = os.path.join(class_dir, random_image)
    
    if not image_path or not os.path.exists(image_path):
        # We will use the fallback mock internally inside process_image if file doesn't exist
        image_path = "mock.jpg"
        
    print(f"Inspecting image: {image_path}")
        
    # --- 1. Vision ---
    vision_result = process_image(image_path, use_local=True)
    
    # --- 2. Truck Health ---
    health_agent = TruckHealthAgent()
    trip_details = {"distance": 500, "load": "heavy"}
    
    # We update the truck's mock telemetry with the vision result
    # In a real scenario, this updates a database
    health_evaluation = health_agent.evaluate_trip(req.truck_id, trip_details)
    
    # --- 3. Supply Chain ---
    supply_agent = SupplyChainAgent()
    # Assume Hankook for this specific tyre (T2Ii)
    claim_result = supply_agent.process_automated_claim(vision_result, req.truck_id, supplier="Hankook", lot="LOT-2026-0095")
    scorecard = supply_agent.evaluate_supplier_scorecard()
    
    return {
        "status": "success",
        "vision": vision_result,
        "truck_health": {
            "verdict": health_evaluation["verdict"],
            "reasons": health_evaluation["reasons"],
            "flagged_tires": health_evaluation["flagged_tires"]
        },
        "supply_chain": {
            "claim_action": claim_result,
            "scorecard_summary": [
                {"supplier": s["supplier"], "grade": s["grade"], "rating": s["calculated_rating"]}
                for s in scorecard
            ]
        }
    }

@app.post("/api/upload_and_inspect")
async def upload_and_inspect(
    file: UploadFile = File(...),
    truck_id: str = Form("9901")
):
    try:
        # Define destination directory
        dest_dir = r"C:\Users\alexc\Downloads\tyresense_app\{css,js,images}\Tyre Quality.v1i.folder\test"
        os.makedirs(dest_dir, exist_ok=True)
        
        # Create a unique filename
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        safe_filename = file.filename.replace(" ", "_")
        filename = f"upload_{timestamp}_{safe_filename}"
        dest_path = os.path.join(dest_dir, filename)
        
        # Save file
        with open(dest_path, "wb") as buffer:
            shutil.copyfileobj(file.file, buffer)
            
        print(f"Image saved to: {dest_path}")
        
        # Run vision process
        vision_result = process_image(dest_path, use_local=True)
        
        # Run health check
        health_agent = TruckHealthAgent()
        trip_details = {"distance": 500, "load": "heavy"}
        health_evaluation = health_agent.evaluate_trip(truck_id, trip_details)
        
        # Run supply chain logic
        supply_agent = SupplyChainAgent()
        claim_result = supply_agent.process_automated_claim(vision_result, truck_id, supplier="Hankook", lot="LOT-2026-0095")
        scorecard = supply_agent.evaluate_supplier_scorecard()
        
        return {
            "status": "success",
            "vision": vision_result,
            "truck_health": {
                "verdict": health_evaluation["verdict"],
                "reasons": health_evaluation["reasons"],
                "flagged_tires": health_evaluation["flagged_tires"]
            },
            "supply_chain": {
                "claim_action": claim_result,
                "scorecard_summary": [
                    {"supplier": s["supplier"], "grade": s["grade"], "rating": s["calculated_rating"]}
                    for s in scorecard
                ]
            },
            "saved_path": dest_path
        }
    except Exception as e:
        print(f"Error processing upload: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/")
def root():
    return {"message": "TyreSense Local Demo Orchestrator is running"}

if __name__ == "__main__":
    import uvicorn
    print("Iniciando Orquestador en http://localhost:8000")
    uvicorn.run("demo_orchestrator:app", host="0.0.0.0", port=8000, reload=True)
