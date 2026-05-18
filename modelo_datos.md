# TyreSense AI — Modelo de Datos

> Versión 1.0 · PostgreSQL · 7 entidades · Generado para el Proyecto Claude

---

## Diagrama de relaciones (ERD textual)

```
UNITS ||--o{ TIRES           : "tiene llantas"
TIRES ||--o{ INSPECTIONS     : "genera inspecciones"
TIRES ||--o{ WORK_ORDERS     : "dispara OTs"
DRIVERS ||--o{ INSPECTIONS   : "realiza inspecciones"
DRIVERS }o--|| UNITS         : "asignado a unidad"
WORK_ORDERS }o--|| DRIVERS   : "asignado a técnico"
INVENTORY }o--|| SUPPLIERS   : "suministrado por"
```

---

## Entidades

---

### 1. `units` — Vehículos / Unidades de flotilla

| Campo          | Tipo         | Restricciones        | Descripción                              |
|----------------|--------------|----------------------|------------------------------------------|
| `id`           | UUID         | PK, NOT NULL         | Identificador único de la unidad         |
| `plate`        | VARCHAR(20)  | UNIQUE, NOT NULL     | Placa / número económico                 |
| `model`        | VARCHAR(100) | NOT NULL             | Marca y modelo del vehículo              |
| `axle_count`   | INT          | NOT NULL, DEFAULT 3  | Número de ejes (define posiciones válidas)|
| `status`       | VARCHAR(30)  | NOT NULL             | `active` / `out_of_service` / `maintenance` |
| `odometer_km`  | INT          | NOT NULL, DEFAULT 0  | Km totales acumulados del vehículo       |
| `created_at`   | TIMESTAMPTZ  | NOT NULL             | Fecha de alta en el sistema              |
| `updated_at`   | TIMESTAMPTZ  | NOT NULL             | Última actualización                     |

**Índices:** `plate` (UNIQUE), `status`

**Notas:**
- `status = out_of_service` es seteado automáticamente por el agente CMMS cuando una llanta tiene `severity = critical`
- `axle_count` determina cuántas posiciones de llanta son válidas (2 ejes = 6 llantas, 3 ejes = 10 llantas)

---

### 2. `tires` — Llantas / Neumáticos

| Campo            | Tipo         | Restricciones    | Descripción                                      |
|------------------|--------------|------------------|--------------------------------------------------|
| `id`             | UUID         | PK, NOT NULL     | Identificador único de la llanta                 |
| `unit_id`        | UUID         | FK → units.id    | Unidad a la que pertenece actualmente            |
| `position`       | VARCHAR(20)  | NOT NULL         | Posición en el vehículo (ver catálogo abajo)     |
| `brand`          | VARCHAR(50)  | NOT NULL         | Marca del neumático (Bridgestone, Michelin, etc.)|
| `model`          | VARCHAR(50)  |                  | Modelo específico (R295, XZE2+, etc.)            |
| `sku`            | VARCHAR(30)  | NOT NULL         | Código de referencia de inventario               |
| `serial_number`  | VARCHAR(50)  | UNIQUE           | Número de serie grabado en la llanta             |
| `life_pct`       | INT          | NOT NULL         | % de vida útil restante (0–100)                  |
| `status`         | VARCHAR(20)  | NOT NULL         | `ok` / `warning` / `critical` / `replaced`      |
| `km_accumulated` | INT          | NOT NULL, DEFAULT 0 | Km recorridos por esta llanta                 |
| `installed_at`   | TIMESTAMPTZ  | NOT NULL         | Fecha de instalación en la unidad actual         |
| `replaced_at`    | TIMESTAMPTZ  |                  | Fecha de reemplazo (NULL si activa)              |
| `supplier_id`    | UUID         | FK → suppliers.id| Proveedor de esta llanta                         |
| `created_at`     | TIMESTAMPTZ  | NOT NULL         | Fecha de alta en el sistema                      |
| `updated_at`     | TIMESTAMPTZ  | NOT NULL         | Última actualización (actualizado por CMMS)      |

**Índices:** `unit_id`, `status`, `sku`, `serial_number` (UNIQUE)

**Catálogo de posiciones (`position`):**
```
Eje delantero:   front_left (D-I)   · front_right (D-D)
Eje tractivo 1:  drive1_outer_left  · drive1_inner_left
                 drive1_inner_right · drive1_outer_right
Eje trasero 2:   drive2_outer_left  · drive2_inner_left
                 drive2_inner_right · drive2_outer_right
```

**Notas:**
- `life_pct` es actualizado por el agente de Visión Artificial en cada inspección
- `status` es actualizado por el agente CMMS basándose en `inspections.severity`
- Cuando `status = replaced`, la llanta queda en historial y se crea un nuevo registro activo

---

### 3. `inspections` — Inspecciones de IA

> **Tabla de solo escritura.** Ningún registro se modifica ni elimina. Es el registro inmutable de cada análisis del agente de Visión Artificial.

| Campo             | Tipo         | Restricciones    | Descripción                                          |
|-------------------|--------------|------------------|------------------------------------------------------|
| `id`              | UUID         | PK, NOT NULL     | Identificador único de la inspección                 |
| `tire_id`         | UUID         | FK → tires.id    | Llanta inspeccionada                                 |
| `driver_id`       | UUID         | FK → drivers.id  | Chofer que realizó la captura                        |
| `inspected_at`    | TIMESTAMPTZ  | NOT NULL         | Timestamp exacto del análisis                        |
| `tread_wear_pct`  | INT          | NOT NULL         | % de desgaste en banda de rodadura (0–100)           |
| `life_remaining_pct` | INT       | NOT NULL         | % de vida útil estimada restante (0–100)             |
| `severity`        | VARCHAR(10)  | NOT NULL         | `ok` / `warning` / `critical` — interruptor principal|
| `confidence`      | DECIMAL(4,2) | NOT NULL         | Confianza del modelo IA (0.00–1.00)                  |
| `findings`        | JSONB        | NOT NULL         | Diagnóstico detallado por zona (ver estructura abajo)|
| `image_urls`      | TEXT[]       | NOT NULL         | URLs de las fotos en S3/storage                      |
| `agent_version`   | VARCHAR(20)  |                  | Versión del agente de Visión Artificial usado        |
| `created_at`      | TIMESTAMPTZ  | NOT NULL         | Timestamp de escritura en BD                         |

**Índices:** `tire_id`, `driver_id`, `severity`, `inspected_at`

**Estructura del campo `findings` (JSONB):**
```json
{
  "sidewall_crack":     true,
  "embedded_object":    true,
  "low_pressure_visual": true,
  "wear_pattern":       "center_wear",
  "zones": {
    "tread_center":  { "wear_pct": 72, "status": "critical" },
    "sidewall_left": { "crack": true,  "status": "warning"  },
    "sidewall_right":{ "crack": false, "status": "ok"       }
  },
  "recommendations": ["replace_immediately", "check_alignment"]
}
```

**Notas:**
- `severity` es el campo que consume el agente CMMS para decidir la prioridad de la OT
- Si `confidence < 0.80`, el agente CMMS escala a revisión manual en lugar de crear OT automáticamente
- Los `image_urls` apuntan a almacenamiento en S3 o similar; nunca se almacenan imágenes en BD

---

### 4. `work_orders` — Órdenes de Trabajo (OTs)

| Campo          | Tipo         | Restricciones    | Descripción                                         |
|----------------|--------------|------------------|-----------------------------------------------------|
| `id`           | UUID         | PK, NOT NULL     | Identificador único de la OT                        |
| `number`       | SERIAL       | UNIQUE, NOT NULL | Número secuencial legible (#7731)                   |
| `tire_id`      | UUID         | FK → tires.id    | Llanta que origina la OT                            |
| `inspection_id`| UUID         | FK → inspections.id | Inspección que disparó la OT (NULL si manual)    |
| `type`         | VARCHAR(20)  | NOT NULL         | `replacement` / `rotation` / `inspection` / `repair`|
| `priority`     | VARCHAR(10)  | NOT NULL         | `high` / `medium` / `low`                          |
| `status`       | VARCHAR(20)  | NOT NULL         | `open` / `in_progress` / `done` / `cancelled`      |
| `origin`       | VARCHAR(10)  | NOT NULL         | `ai` / `manual` — origen de creación               |
| `assigned_to`  | UUID         | FK → drivers.id  | Técnico asignado                                    |
| `notes`        | TEXT         |                  | Observaciones del taller                            |
| `created_at`   | TIMESTAMPTZ  | NOT NULL         | Timestamp de creación (automática o manual)         |
| `started_at`   | TIMESTAMPTZ  |                  | Cuando el técnico inició el trabajo                 |
| `closed_at`    | TIMESTAMPTZ  |                  | Cuando se cerró la OT                               |

**Índices:** `tire_id`, `status`, `priority`, `type`, `origin`, `created_at`

**Reglas de negocio (ejecutadas por el agente CMMS):**
```
severity = critical  →  priority = high   + units.status = out_of_service
severity = warning   →  priority = medium + no bloqueo de unidad
severity = ok        →  no genera OT automática
confidence < 0.80    →  origin = manual   + escalada a revisión humana
```

**Notas:**
- `origin = ai` indica OT generada automáticamente por la cadena de agentes
- El tiempo de resolución `closed_at - created_at` es el KPI #12 (tiempo de cierre de OT)
- Al cerrar una OT de tipo `replacement`, el agente Supply Chain verifica inventario

---

### 5. `drivers` — Choferes y Técnicos

| Campo         | Tipo         | Restricciones        | Descripción                              |
|---------------|--------------|----------------------|------------------------------------------|
| `id`          | UUID         | PK, NOT NULL         | Identificador único                      |
| `name`        | VARCHAR(100) | NOT NULL             | Nombre completo                          |
| `employee_id` | VARCHAR(30)  | UNIQUE               | Número de empleado                       |
| `role`        | VARCHAR(20)  | NOT NULL             | `driver` / `technician` / `supervisor`  |
| `unit_id`     | UUID         | FK → units.id        | Unidad asignada (NULL si técnico)        |
| `shift`       | VARCHAR(20)  |                      | `morning` / `afternoon` / `night`       |
| `active`      | BOOLEAN      | NOT NULL, DEFAULT TRUE | Estado activo en el sistema            |
| `created_at`  | TIMESTAMPTZ  | NOT NULL             | Fecha de alta                            |

**Índices:** `role`, `unit_id`, `active`

**Notas:**
- Los choferes (`role = driver`) realizan inspecciones en `inspections.driver_id`
- Los técnicos (`role = technician`) son asignados en `work_orders.assigned_to`
- Un mismo empleado puede tener ambos roles en flotas pequeñas

---

### 6. `inventory` — Inventario de Llantas

| Campo         | Tipo         | Restricciones    | Descripción                                      |
|---------------|--------------|------------------|--------------------------------------------------|
| `id`          | UUID         | PK, NOT NULL     | Identificador único                              |
| `sku`         | VARCHAR(30)  | NOT NULL         | Código de referencia (295/80R22.5)               |
| `brand`       | VARCHAR(50)  | NOT NULL         | Marca del neumático                              |
| `model`       | VARCHAR(50)  |                  | Modelo específico                                |
| `supplier_id` | UUID         | FK → suppliers.id| Proveedor principal de este SKU                  |
| `qty`         | INT          | NOT NULL, DEFAULT 0 | Cantidad disponible en bodega                 |
| `min_stock`   | INT          | NOT NULL         | Stock mínimo — bajo este valor se sugiere compra |
| `max_stock`   | INT          | NOT NULL         | Stock máximo — límite de reposición              |
| `unit_cost`   | DECIMAL(10,2)|                  | Costo unitario de compra (USD)                   |
| `location`    | VARCHAR(50)  |                  | Ubicación en bodega o patio                      |
| `updated_at`  | TIMESTAMPTZ  | NOT NULL         | Última actualización de cantidad                 |

**Índices:** `sku`, `supplier_id`, `qty`

**Regla del agente Supply Chain:**
```
qty < min_stock  →  generar orden de compra sugerida con urgencia = high
qty < min_stock * 1.5  →  urgencia = medium
qty >= min_stock →  ok, sin acción
```

**Notas:**
- `qty` se decrementa automáticamente cuando se cierra una OT de tipo `replacement`
- El agente Supply Chain recalcula `min_stock` mensualmente basándose en la demanda histórica
- El campo `unit_cost` combinado con `tires.km_accumulated` produce el KPI costo/km real

---

### 7. `suppliers` — Proveedores de Llantas

| Campo            | Tipo          | Restricciones    | Descripción                                         |
|------------------|---------------|------------------|-----------------------------------------------------|
| `id`             | UUID          | PK, NOT NULL     | Identificador único                                 |
| `name`           | VARCHAR(100)  | NOT NULL         | Nombre del proveedor                                |
| `country`        | VARCHAR(50)   |                  | País de operación                                   |
| `contact_email`  | VARCHAR(100)  |                  | Contacto comercial                                  |
| `score`          | INT           |                  | Score 0–100 calculado por el agente Supply Chain    |
| `avg_life_km`    | DECIMAL(10,2) |                  | Km promedio de vida real observada en campo         |
| `promised_life_km`| DECIMAL(10,2)|                  | Km prometidos por el fabricante                     |
| `failure_rate`   | DECIMAL(5,4)  |                  | % de llantas con OT crítica (0.0000–1.0000)        |
| `delivery_grade` | CHAR(1)       |                  | Grado de cumplimiento de entrega: A / B / C         |
| `avg_lead_time_days`| DECIMAL(4,1)|                 | Días promedio entre OC y entrega                    |
| `active`         | BOOLEAN       | NOT NULL, DEFAULT TRUE | Proveedor activo                              |
| `score_updated_at`| TIMESTAMPTZ  |                  | Última vez que el agente recalculó el score         |
| `created_at`     | TIMESTAMPTZ   | NOT NULL         | Fecha de alta                                       |

**Índices:** `score`, `active`

**Fórmula del score (calculada por el agente Supply Chain):**
```
score = (
  (avg_life_km / promised_life_km * 40)   -- 40% peso: vida útil real vs prometida
  + ((1 - failure_rate) * 30)              -- 30% peso: tasa de NO fallas
  + (delivery_grade_num * 20)              -- 20% peso: cumplimiento de entrega (A=1, B=0.66, C=0.33)
  + (price_competitiveness * 10)           -- 10% peso: precio relativo vs mercado
)
```

**Notas:**
- `score` se recalcula mensualmente con datos reales de `tires.km_accumulated` y `work_orders`
- La diferencia `avg_life_km vs promised_life_km` produce el KPI #27 (desviación costo/km)
- Un proveedor con `score < 70` aparece con acción "Revisar" en el dashboard de Supply Chain

---

## Relaciones completas

| Tabla origen     | Campo FK          | Tabla destino | Cardinalidad | Descripción                          |
|------------------|-------------------|---------------|--------------|--------------------------------------|
| `tires`          | `unit_id`         | `units`       | N → 1        | Muchas llantas por unidad            |
| `inspections`    | `tire_id`         | `tires`       | N → 1        | Muchas inspecciones por llanta       |
| `inspections`    | `driver_id`       | `drivers`     | N → 1        | Muchas inspecciones por chofer       |
| `work_orders`    | `tire_id`         | `tires`       | N → 1        | Muchas OTs por llanta                |
| `work_orders`    | `inspection_id`   | `inspections` | N → 1        | OT vinculada a su inspección origen  |
| `work_orders`    | `assigned_to`     | `drivers`     | N → 1        | Muchas OTs por técnico               |
| `drivers`        | `unit_id`         | `units`       | N → 1        | Muchos choferes por unidad           |
| `inventory`      | `supplier_id`     | `suppliers`   | N → 1        | Muchos SKUs por proveedor            |
| `tires`          | `supplier_id`     | `suppliers`   | N → 1        | Muchas llantas por proveedor         |

---

## Flujo de datos entre agentes

```
App chofer
  │  foto + unit_id + position + driver_id
  ▼
Agente Visión Artificial
  │  escribe → inspections (severity, tread_wear_pct, life_pct, findings, image_urls)
  │  actualiza → tires.life_pct, tires.status
  ▼
Agente CMMS                          [si severity = critical o warning]
  │  lee → inspections.severity, inspections.confidence
  │  crea → work_orders (type, priority, assigned_to)
  │  actualiza → units.status = out_of_service (si critical)
  ▼
Agente Supply Chain                  [si work_orders.type = replacement]
  │  lee → tires.sku, inventory.qty, inventory.min_stock
  │  actualiza → inventory.qty (al cerrar OT)
  │  genera → purchase_order sugerida (si qty < min_stock)
  │  recalcula → suppliers.score (mensual)
```

---

## Convenciones de nomenclatura

| Elemento          | Convención                        | Ejemplo                        |
|-------------------|-----------------------------------|--------------------------------|
| Tablas            | `snake_case` plural               | `work_orders`, `suppliers`     |
| Campos            | `snake_case` singular             | `tire_id`, `created_at`        |
| PKs               | `id` UUID en todas las tablas     | `id UUID PRIMARY KEY`          |
| FKs               | `{tabla_singular}_id`             | `unit_id`, `driver_id`         |
| Timestamps        | Siempre `TIMESTAMPTZ`             | `created_at`, `closed_at`      |
| Enums como texto  | `VARCHAR` con valores documentados| `severity: ok/warning/critical`|
| Booleanos         | `BOOLEAN DEFAULT TRUE/FALSE`      | `active BOOLEAN DEFAULT TRUE`  |

---

## Versión y cambios

| Versión | Fecha       | Cambio                                      |
|---------|-------------|---------------------------------------------|
| 1.0     | Mayo 2026   | Modelo inicial — hackathon CTRLHACK 2.0     |