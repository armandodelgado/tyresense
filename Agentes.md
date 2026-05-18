# TyreSense AI — Agentes Industriales
## CTRLHACK 2.0 · Expo Programador · Ciudad Juárez

---

## Gate de selección: 11 puntos

| Agente | Tipo | Puntos |
|---|---|---|
| Visión Artificial Industrial | Hybrid | 5 |
| Mantenimiento & CMMS | Hybrid | 3 |
| Cadena de Suministro | Cloud | 3 |
| **Total** | | **11 pts** |

---

## Correr el proyecto en < 5 minutos

### Requisitos
- PHP 8.0+
- Node.js 18+ (para dashboard Next.js)

### Backend (agentes + API)
```bash
cd tyresense/
php -S localhost:8080
```

API disponible en:
- `GET  http://localhost:8080/api/health` — salud del sistema
- `POST http://localhost:8080/api/agent/{agent}/{tool}` — ejecutar tool
- `GET  http://localhost:8080/api/events` — log de eventos IES
- `GET  http://localhost:8080/api/state` — estado de la BD mock
- `POST http://localhost:8080/api/simulate/full_flow` — demo 3 agentes en cadena

### Dashboard Next.js
```bash
cd tyresense/dashboard/
npm install
NEXT_PUBLIC_API_BASE=http://localhost:8080/api npm run dev
```
Abrir http://localhost:3000/agents

### App del chofer (mockup PHP)
```
http://localhost:8080/?screen=home
http://localhost:8080/?screen=scan
http://localhost:8080/?screen=result
http://localhost:8080/?screen=supply
```

---

## Flujo completo de demo (< 3 min)

### Opción A — cURL
```bash
# 1. Health check
curl http://localhost:8080/api/health

# 2. Visión inspecciona llanta crítica
curl -X POST http://localhost:8080/api/agent/vision/inspect_tire_quality \
  -H "Content-Type: application/json" \
  -d '{"tire_id":"TIRE-004","driver_id":"DRV-001","unit_id":"UNIT-001","position":"drive1_inner_left"}'

# 3. CMMS procesa el evento y genera OT
curl -X POST http://localhost:8080/api/agent/cmss/process_vision_event \
  -H "Content-Type: application/json" \
  -d '{"inspection_id":"INS-DEMO","tire_id":"TIRE-004","severity":"critical","confidence":0.92,"unit_id":"UNIT-001"}'

# 4. Supply Chain verifica inventario
curl -X POST http://localhost:8080/api/agent/supply_chain/check_inventory_on_replacement \
  -H "Content-Type: application/json" \
  -d '{"tire_id":"TIRE-004","work_order_id":"OT-DEMO"}'

# 5. Ver todos los eventos IES publicados
curl http://localhost:8080/api/events

# 6. Simulación completa automática (3 agentes en cadena)
curl -X POST http://localhost:8080/api/simulate/full_flow \
  -H "Content-Type: application/json" \
  -d '{"tire_id":"TIRE-004","unit_id":"UNIT-001","driver_id":"DRV-001"}'
```

### Opción B — Dashboard UI
1. Abrir http://localhost:3000/agents
2. Clic **"Ejecutar Flujo Completo"**
3. Ver los 3 eventos IES publicados en tiempo real en la pestaña "Eventos IES"

---

## Tools por agente

### 🔍 Visión Artificial Industrial (8 tools)
| Tool | Runtime | Descripción |
|---|---|---|
| `inspect_tire_quality` | Edge 10-50ms | Análisis CNN de foto de llanta |
| `detect_safety_violations` | Edge | Detección EPP/zonas restringidas |
| `monitor_worker_posture` | Edge | Ergonomía durante inspección |
| `capture_video_stream` | Edge | Captura video para historial |
| `count_items_on_conveyor` | Edge | Conteo llantas en bodega |
| `train_vision_model` | Cloud | Reentrenamiento con nuevos defectos |
| `improve_detection_model` | Cloud | Mejora continua desde feedback |
| `analyze_visual_patterns` | Cloud | Análisis histórico predictivo |

### 🔧 Mantenimiento & CMSS (5 tools)
| Tool | Runtime | Descripción |
|---|---|---|
| `process_vision_event` | Cloud | Consume evento IES de Visión → OT |
| `create_work_order` | Cloud | Crea OT manual |
| `close_work_order` | Cloud | Cierra OT, activa Supply Chain |
| `schedule_preventive_maintenance` | Cloud | ISO 55000 preventivo |
| `get_asset_lifecycle_status` | Cloud | Estado ciclo de vida unidad |

### 📦 Cadena de Suministro (5 tools)
| Tool | Runtime | Descripción |
|---|---|---|
| `check_inventory_on_replacement` | Cloud | Verifica stock al consumir llanta |
| `suggest_purchase_order` | Cloud | Genera sugerencia de compra |
| `calculate_supplier_score` | Cloud | Scorecard proveedores (fórmula real) |
| `calculate_cost_per_km` | Cloud | KPI costo/km real vs prometido |
| `get_supply_chain_dashboard` | Cloud | Vista ejecutiva supply chain |

---

## IES v2.0 — Cumplimiento

Todos los eventos publicados incluyen los 7 campos requeridos:
- `event_id` (UUID generado)
- `timestamp` (ISO 8601 UTC)
- `platform_version: "2.0.0"`
- `module.id` (snake_case)
- `module.version`
- `asset.asset_id`
- `event.type` (snake_case)

Valores válidos usados en `event.category`: `quality`, `maintenance`, `productivity`, `safety`, `system`
Valores válidos usados en `event.severity`: `low`, `medium`, `high`, `critical`

---

## Arquitectura cross-agent

```
App chofer (PHP mockup / React Native)
  │  foto + unit_id + tire_id + driver_id
  ▼
Agente Visión Artificial [HYBRID]
  │  Edge: CNN inference 10-50ms
  │  Cloud: reentrenamiento, análisis histórico
  │  → IES event: tire_defect_critical / tire_defect_warning / tire_inspection_ok
  ▼
Agente CMMS [HYBRID]           ← consume evento IES de Visión
  │  process_vision_event → OT automática (origin=ai)
  │  severity=critical → bloquea unidad (ISO 55000)
  │  confidence<0.80 → escalada manual
  │  → IES event: unit_blocked_critical_tire / work_order_created_*
  ▼
Agente Supply Chain [CLOUD]    ← consume evento IES de CMSS
  │  check_inventory_on_replacement
  │  qty < min_stock → suggest_purchase_order (urgency=high)
  │  recalcula supplier scores mensual
  │  → IES event: inventory_consumed_on_replacement / purchase_order_suggested
```

---

## Código pre-existente declarado
- PHP 8 stdlib
- Sin librerías de terceros en el backend (cero dependencias npm en el backend)
- Dashboard: Next.js 14 + React 18 (framework declarado)

---

## Entregables completados
- [x] Repositorio con estructura de carpetas
- [x] README con instrucciones (demo < 10 min)
- [x] 3+ eventos IES v2.0 válidos por agente
- [x] Interfaz gráfica funcional (PHP mockup + Next.js dashboard)
- [x] 18 tools funcionales en total (mínimo 3 por agente)
- [x] Endpoint REST expuesto (`/api/agent/{agent}/{tool}`)
- [x] Comunicación cross-agent (Visión → CMMS → Supply Chain)