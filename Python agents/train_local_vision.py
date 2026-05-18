import os
from ultralytics import YOLO

def train_local_model():
    """
    Entrena un modelo YOLOv8 para clasificación de imágenes
    basado en el dataset de Tyre Quality.
    """
    # Usar el modelo base de clasificación de YOLOv8
    model = YOLO('yolov8n-cls.pt')  
    
    # Ruta absoluta al dataset
    dataset_path = r"C:\Users\alexc\Downloads\tyresense_app\{css,js,images}\Tyre Quality.v1i.folder"
    
    if not os.path.exists(dataset_path):
        print(f"Error: No se encontró el dataset en {dataset_path}")
        return

    print("Iniciando entrenamiento del modelo local de visión...")
    print(f"Dataset configurado en: {dataset_path}")
    
    # Entrenar el modelo (configurado a 3 épocas para pruebas/demo rápido)
    # En producción deberías usar 50-100 épocas
    results = model.train(
        data=dataset_path,
        epochs=3,           # Bajo número de épocas para que el demo se ejecute rápido
        imgsz=224,          # Tamaño de imagen estándar para clasificación rápida
        project='tyre_runs',
        name='local_model',
        exist_ok=True       # Sobrescribe entrenamientos anteriores con el mismo nombre
    )
    
    print("\n¡Entrenamiento finalizado!")
    print("El modelo guardado se encuentra en: tyre_runs/local_model/weights/best.pt")

if __name__ == "__main__":
    train_local_model()
