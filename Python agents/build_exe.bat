@echo off
echo ==========================================
echo   Empaquetador de TyreSense AI a .EXE
echo ==========================================
echo.
echo 1. Instalando PyInstaller si no lo tienes...
pip install pyinstaller

echo.
echo 2. Iniciando compilacion (Esto tomara un rato por YOLOv8)...
pyinstaller --noconfirm --onedir --console --add-data "runs;runs" --add-data "yolov8n-cls.pt;." --collect-all ultralytics --collect-all fastapi --collect-all uvicorn --collect-all pydantic demo_orchestrator.py

echo.
echo ======================================================================
echo ¡COMPILACION TERMINADA!
echo.
echo Tu programa esta listo en la carpeta: "dist\demo_orchestrator"
echo.
echo ----------------------------------------------------------------------
echo INSTRUCCIONES PARA LLEVARLO A OTRA PC:
echo 1. Copia la carpeta "demo_orchestrator" que se creo dentro de "dist"
echo 2. Acompañala con tus archivos web (dashboard.html, mobile_app.html, css, js)
echo 3. En la otra PC, solo dales doble clic a "demo_orchestrator.exe"
echo 4. Abre los archivos .html en tu navegador. ¡Y listo!
echo ----------------------------------------------------------------------
pause
