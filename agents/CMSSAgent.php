<?php
/**
 * TyreSense AI — Agente: Mantenimiento & CMMS
 * Tipo: Hybrid
 * Consume eventos del Agente de Visión Artificial.
 * Tools: 5 funcionales (Edge + Cloud)
 * ISO 55000 compliant lifecycle management
 */

require_once __DIR__ . '/../api/IESPublisher.php';
require_once __DIR__ . '/../api/MockDB.php';

class CMSSAgent {

    private IESPublisher $publisher;

    public function __construct() {
        $this->publisher = new IESPublisher();
    }

    // ─────────────────────────────────────────────
    // Tool 1: process_vision_event
    // Consume el evento IES del agente de Visión.
    // Interruptor principal: severity.
    // ─────────────────────────────────────────────

    public function process_vision_event(array $params): array {
        $inspectionId = $params['inspection_id'] ?? throw new \InvalidArgumentException('inspection_id requerido');
        $tireId       = $params['tire_id']       ?? throw new \InvalidArgumentException('tire_id requerido');
        $severity     = $params['severity']       ?? throw new \InvalidArgumentException('severity requerido');
        $confidence   = (float)($params['confidence'] ?? 0.90);
        $unitId       = $params['unit_id']        ?? 'UNIT-001';

        // Lógica CMMS según severity (reglas de negocio exactas)
        if ($severity === 'ok') {
            return ['action' => 'no_ot', 'reason' => 'severity_ok', 'event_published' => false];
        }

        // Bajo confianza → revisión manual
        if ($confidence < 0.80) {
            $wo = MockDB::createWorkOrder([
                'tire_id'      => $tireId,
                'inspection_id'=> $inspectionId,
                'type'         => 'inspection',
                'priority'     => 'medium',
                'origin'       => 'manual',
                'assigned_to'  => 'TEC-001',
                'notes'        => "Confianza IA: {$confidence}. Requiere revisión física por técnico.",
                'status'       => 'open',
            ]);

            $this->publisher->publish([
                'asset_id'   => "TIRE-{$tireId}",
                'asset_type' => 'tire',
                'plant_id'   => "PLT-{$unitId}",
                'area_id'    => 'MAINTENANCE',
            ], [
                'type'     => 'work_order_created_manual_review',
                'category' => 'maintenance',
                'severity' => 'medium',
            ], [
                'work_order_id' => $wo['id'],
                'wo_number'     => $wo['number'],
                'tire_id'       => $tireId,
                'inspection_id' => $inspectionId,
                'origin'        => 'manual',
                'reason'        => 'low_ai_confidence',
                'confidence'    => $confidence,
                'assigned_to'   => 'TEC-001',
            ], ['runtime' => 'cloud']);

            return ['action' => 'manual_review', 'work_order' => $wo, 'event_published' => true];
        }

        // severity = critical → OT alta prioridad + bloquear unidad
        if ($severity === 'critical') {
            $wo = MockDB::createWorkOrder([
                'tire_id'      => $tireId,
                'inspection_id'=> $inspectionId,
                'type'         => 'replacement',
                'priority'     => 'high',
                'origin'       => 'ai',
                'assigned_to'  => 'TEC-001',
                'notes'        => "Generada automáticamente por agente IA. Severidad crítica.",
                'status'       => 'open',
            ]);

            // Bloquear unidad (ISO 55000 — fuera de servicio)
            MockDB::updateUnit($unitId, ['status' => 'out_of_service']);

            $this->publisher->publish([
                'asset_id'   => "UNIT-{$unitId}",
                'asset_type' => 'vehicle',
                'plant_id'   => "PLT-{$unitId}",
                'area_id'    => 'MAINTENANCE',
            ], [
                'type'     => 'unit_blocked_critical_tire',
                'category' => 'maintenance',
                'severity' => 'critical',
            ], [
                'unit_id'        => $unitId,
                'tire_id'        => $tireId,
                'work_order_id'  => $wo['id'],
                'wo_number'      => $wo['number'],
                'new_unit_status'=> 'out_of_service',
                'origin'         => 'ai',
                'iso_55000_ref'  => 'clause_6.2_asset_lifecycle',
                'requires_supply_chain_check' => true,
            ], ['runtime' => 'cloud']);

            return ['action' => 'ot_critical_unit_blocked', 'work_order' => $wo, 'unit_blocked' => true, 'event_published' => true];
        }

        // severity = warning → OT media prioridad, sin bloquear
        $wo = MockDB::createWorkOrder([
            'tire_id'      => $tireId,
            'inspection_id'=> $inspectionId,
            'type'         => 'rotation',
            'priority'     => 'medium',
            'origin'       => 'ai',
            'assigned_to'  => 'TEC-001',
            'notes'        => "Generada automáticamente. Programar en próxima ventana de mantenimiento.",
            'status'       => 'open',
        ]);

        $this->publisher->publish([
            'asset_id'   => "TIRE-{$tireId}",
            'asset_type' => 'tire',
            'plant_id'   => "PLT-{$unitId}",
        ], [
            'type'     => 'work_order_created_warning',
            'category' => 'maintenance',
            'severity' => 'high',
        ], [
            'work_order_id' => $wo['id'],
            'wo_number'     => $wo['number'],
            'tire_id'       => $tireId,
            'type'          => 'rotation',
            'origin'        => 'ai',
            'unit_blocked'  => false,
        ], ['runtime' => 'cloud']);

        return ['action' => 'ot_warning', 'work_order' => $wo, 'unit_blocked' => false, 'event_published' => true];
    }

    // ─────────────────────────────────────────────
    // Tool 2: create_work_order (manual)
    // ─────────────────────────────────────────────

    public function create_work_order(array $params): array {
        $tireId   = $params['tire_id']   ?? throw new \InvalidArgumentException('tire_id requerido');
        $type     = $params['type']      ?? 'inspection';
        $priority = $params['priority']  ?? 'medium';
        $notes    = $params['notes']     ?? '';
        $unitId   = $params['unit_id']   ?? 'UNIT-001';

        $validTypes     = ['replacement','rotation','inspection','repair'];
        $validPriorities= ['high','medium','low'];

        if (!in_array($type, $validTypes))         throw new \InvalidArgumentException("type inválido: {$type}");
        if (!in_array($priority, $validPriorities)) throw new \InvalidArgumentException("priority inválida: {$priority}");

        $wo = MockDB::createWorkOrder([
            'tire_id'    => $tireId,
            'type'       => $type,
            'priority'   => $priority,
            'origin'     => 'manual',
            'assigned_to'=> 'TEC-001',
            'notes'      => $notes,
            'status'     => 'open',
        ]);

        $this->publisher->publish([
            'asset_id'   => "TIRE-{$tireId}",
            'asset_type' => 'tire',
            'plant_id'   => "PLT-{$unitId}",
            'area_id'    => 'MAINTENANCE',
        ], [
            'type'     => 'work_order_created_manual',
            'category' => 'maintenance',
            'severity' => match($priority) { 'high'=>'high','medium'=>'medium',default=>'low' },
        ], [
            'work_order_id' => $wo['id'],
            'wo_number'     => $wo['number'],
            'tire_id'       => $tireId,
            'type'          => $type,
            'priority'      => $priority,
            'origin'        => 'manual',
        ], ['runtime' => 'cloud']);

        return ['status' => 'success', 'tool' => 'create_work_order', 'work_order' => $wo, 'event_published' => true];
    }

    // ─────────────────────────────────────────────
    // Tool 3: close_work_order
    // Cierra OT, activa Supply Chain si type=replacement
    // ─────────────────────────────────────────────

    public function close_work_order(array $params): array {
        $woId     = $params['work_order_id'] ?? throw new \InvalidArgumentException('work_order_id requerido');
        $notes    = $params['notes']         ?? 'Trabajo completado';
        $tireId   = $params['tire_id']       ?? null;

        $workOrders = MockDB::get('work_orders');
        $wo = $workOrders[$woId] ?? null;

        if (!$wo) {
            return ['status' => 'error', 'message' => "OT {$woId} no encontrada"];
        }

        // Calcular tiempo de resolución (KPI #12)
        $createdAt   = new \DateTimeImmutable($wo['created_at']);
        $closedAt    = new \DateTimeImmutable('now');
        $resolutionH = round(($closedAt->getTimestamp() - $createdAt->getTimestamp()) / 3600, 2);

        $closedWo = array_merge($wo, [
            'status'    => 'done',
            'notes'     => $notes,
            'closed_at' => $closedAt->format('Y-m-d\TH:i:s\Z'),
        ]);

        // Publicar cierre
        $this->publisher->publish([
            'asset_id'   => "OT-{$woId}",
            'asset_type' => 'work_order',
            'plant_id'   => 'PLT-JUAREZ-01',
            'area_id'    => 'MAINTENANCE',
        ], [
            'type'     => 'work_order_closed',
            'category' => 'maintenance',
            'severity' => 'low',
        ], [
            'work_order_id'     => $woId,
            'wo_number'         => $wo['number'],
            'type'              => $wo['type'],
            'resolution_hours'  => $resolutionH,
            'tire_id'           => $wo['tire_id'],
            'trigger_supply_chain' => ($wo['type'] === 'replacement'),
        ], ['runtime' => 'cloud']);

        return [
            'status'             => 'success',
            'tool'               => 'close_work_order',
            'work_order'         => $closedWo,
            'resolution_hours'   => $resolutionH,
            'trigger_supply_chain'=> ($wo['type'] === 'replacement'),
            'event_published'    => true,
        ];
    }

    // ─────────────────────────────────────────────
    // Tool 4: schedule_preventive_maintenance
    // Programa rotaciones e inspecciones preventivas (ISO 55000)
    // ─────────────────────────────────────────────

    public function schedule_preventive_maintenance(array $params): array {
        $unitId     = $params['unit_id']      ?? throw new \InvalidArgumentException('unit_id requerido');
        $type       = $params['type']         ?? 'rotation';
        $scheduledAt= $params['scheduled_at'] ?? date('Y-m-d', strtotime('+7 days'));
        $kmThreshold= (int)($params['km_threshold'] ?? 50000);

        $scheduleId = 'SCHED-' . strtoupper(substr(md5($unitId . $scheduledAt), 0, 8));

        $this->publisher->publish([
            'asset_id'   => "UNIT-{$unitId}",
            'asset_type' => 'vehicle',
            'plant_id'   => "PLT-{$unitId}",
            'area_id'    => 'MAINTENANCE',
        ], [
            'type'     => 'preventive_maintenance_scheduled',
            'category' => 'maintenance',
            'severity' => 'low',
        ], [
            'schedule_id'    => $scheduleId,
            'unit_id'        => $unitId,
            'maintenance_type'=> $type,
            'scheduled_date' => $scheduledAt,
            'km_threshold'   => $kmThreshold,
            'iso_ref'        => 'ISO_55000_6.2.2',
            'assigned_to'    => 'TEC-001',
            'estimated_duration_min' => match($type) {
                'rotation' => 90, 'replacement' => 45, default => 30
            },
        ], ['runtime' => 'cloud']);

        return [
            'status'         => 'success',
            'tool'           => 'schedule_preventive_maintenance',
            'schedule_id'    => $scheduleId,
            'unit_id'        => $unitId,
            'scheduled_at'   => $scheduledAt,
            'event_published'=> true,
        ];
    }

    // ─────────────────────────────────────────────
    // Tool 5: get_asset_lifecycle_status
    // Estado de ciclo de vida ISO 55000 para unidad/llanta
    // ─────────────────────────────────────────────

    public function get_asset_lifecycle_status(array $params): array {
        $unitId = $params['unit_id'] ?? throw new \InvalidArgumentException('unit_id requerido');

        $unit  = MockDB::get('units', $unitId);
        $tires = array_filter(MockDB::get('tires'), fn($t) => $t['unit_id'] === $unitId);

        if (!$unit) return ['status' => 'error', 'message' => "Unidad {$unitId} no encontrada"];

        $criticalCount = count(array_filter($tires, fn($t) => $t['status'] === 'critical'));
        $warningCount  = count(array_filter($tires, fn($t) => $t['status'] === 'warning'));
        $avgLife       = count($tires) ? round(array_sum(array_column($tires, 'life_pct')) / count($tires)) : 0;
        $openOTs       = array_filter(MockDB::get('work_orders'), fn($wo) => $wo['status'] === 'open');

        $lifecyclePhase = match(true) {
            $criticalCount > 0         => 'corrective',
            $warningCount > 2          => 'preventive',
            $unit['odometer_km'] > 200000 => 'end_of_life_review',
            default                    => 'operational',
        };

        $this->publisher->publish([
            'asset_id'   => "UNIT-{$unitId}",
            'asset_type' => 'vehicle',
            'plant_id'   => "PLT-{$unitId}",
        ], [
            'type'     => 'asset_lifecycle_status_report',
            'category' => 'maintenance',
            'severity' => $criticalCount > 0 ? 'critical' : ($warningCount > 0 ? 'high' : 'low'),
        ], [
            'unit_id'         => $unitId,
            'plate'           => $unit['plate'],
            'odometer_km'     => $unit['odometer_km'],
            'lifecycle_phase' => $lifecyclePhase,
            'tire_count'      => count($tires),
            'critical_tires'  => $criticalCount,
            'warning_tires'   => $warningCount,
            'avg_life_pct'    => $avgLife,
            'open_work_orders'=> count($openOTs),
            'iso_55000_phase' => $lifecyclePhase,
        ], ['runtime' => 'cloud']);

        return [
            'status'          => 'success',
            'tool'            => 'get_asset_lifecycle_status',
            'unit'            => $unit,
            'tires'           => array_values($tires),
            'lifecycle'       => [
                'phase'          => $lifecyclePhase,
                'critical_tires' => $criticalCount,
                'warning_tires'  => $warningCount,
                'avg_life_pct'   => $avgLife,
                'open_work_orders'=> count($openOTs),
            ],
            'event_published' => true,
        ];
    }
}
