# TyreSense AI — App del Chofer (PHP Mockup)

Mockup interactivo de la app móvil para choferes del sistema **TyreSense AI**.
Desarrollado para el hackathon CTRLHACK 2.0 / Expo Programador.

## Estructura

```
tyresense/
├── index.php              # Enrutador principal + datos mock
├── includes/
│   ├── header.php         # HTML head + frame del teléfono
│   └── footer.php         # Nav inferior + cierre HTML
├── screens/
│   ├── home.php           # Pantalla inicio — mapa de llantas
│   ├── scan.php           # Pantalla de captura de foto
│   ├── result.php         # Resultado del análisis IA
│   ├── history.php        # Historial de inspecciones
│   └── supply.php         # KPIs supply chain (nuevo)
├── css/
│   └── app.css            # Sistema de diseño completo
├── js/
│   └── app.js             # JS mínimo para UX
└── README.md
```

## Cómo correr el proyecto

### Opción 1 — PHP built-in server (recomendado)
```bash
cd tyresense
php -S localhost:8080
```
Abre http://localhost:8080 en el navegador.

### Opción 2 — XAMPP / Laragon / MAMP
Copia la carpeta `tyresense/` dentro de `htdocs/` (XAMPP) o `www/` (Laragon).
Abre http://localhost/tyresense/

## Navegación

| URL                        | Pantalla              |
|----------------------------|-----------------------|
| `?screen=home`             | Inicio (default)      |
| `?screen=scan`             | Escanear llanta       |
| `?screen=result`           | Resultado IA          |
| `?screen=history`          | Historial             |
| `?screen=supply`           | Supply chain KPIs     |

## Personalizar datos mock

Edita el bloque `// Mock data` en `index.php` para cambiar:
- `$unit` — datos de la unidad del chofer
- `$tires` — posiciones y estado de cada llanta
- `$result` — diagnóstico de la IA (severity, life_pct, etc.)
- `$history` — historial de inspecciones

## Agentes utilizados (Expo Programador)

- **Visión Artificial Industrial** — análisis de fotos, detección de defectos
- **Mantenimiento & CMMS** — generación de OTs, ciclo de vida ISO 55000
- **Cadena de Suministro** — inventario, scorecard de proveedores

## Requisitos

- PHP 8.0 o superior
- Navegador moderno (Chrome, Firefox, Safari, Edge)
- Sin dependencias externas — todo es CSS y PHP puro
