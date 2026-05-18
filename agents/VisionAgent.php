<?php
/**
 * TyreSense AI — Agente: Visión Artificial Industrial
 * Tipo: Hybrid (Edge + Cloud)
 * Tools: 5 (Edge) + 3 (Cloud)
 * IES v2.0 compliant
 */

require_once __DIR__ . '/../api/IESPublisher.php';
require_once __DIR__ . '/../api/MockDB.php';

class VisionAgent {

    private IESPublisher $publisher;
    private string $moduleId = 'vision_artificial_industrial';
    private string $moduleVersion = '1.2.0';

    public function __construct() {
        $this->publisher = new IESPublisher();
    }

    // ─────────────────────────────────────────────
    // EDGE TOOLS (latencia 10–50ms)
    // ─────────────────────────────────────────────

    /**
     * Tool 1 (Edge): inspect_tire_quality
     * Analiza foto de llanta, detecta desgaste, grietas, objetos, presión visual.
     */
    public function inspect_tire_quality(array $params): array {
        $tireId    = $params['tire_id'] ?? throw new \InvalidArgumentException('tire_id requerido');
        $driverId  = $params['driver_id'] ?? throw new \InvalidArgumentException('driver_id requerido');
        $unitId    = $params['unit_id'] ?? throw new \InvalidArgumentException('unit_id requerido');
        $position  = $params['position'] ?? 'front_left';
        $imageB64  = $params['image_base64'] ?? null; // En prod: imagen real

        // Simulación CNN inference (Edge, 10–50ms)
        $startMs = microtime(true) * 1000;
        $result  = $this->runCNNInference($tireId, $position);
        $latencyMs = round((microtime(true) * 1000) - $startMs + mt_rand(10, 50));

        $severity   = $result['severity'];
        $confidence = $result['confidence'];

        // Guardar inspección en BD mock
        $inspection = MockDB::createInspection([
            'tire_id'           => $tireId,
            'driver_id'         => $driverId,
            'tread_wear_pct'    => $result['tread_wear_pct'],
            'life_remaining_pct'=> $result['life_remaining_pct'],
            'severity'          => $severity,
            'confidence'        => $confidence,
            'findings'          => $result['findings'],
            'image_urls'        => ["s3://tyresense-prod/inspections/{$tireId}/" . date('Ymd-His') . ".jpg"],
            'agent_version'     => $this->moduleVersion,
        ]);

        // Actualizar llanta en BD
        MockDB::updateTire($tireId, [
            'life_pct' => $result['life_remaining_pct'],
            'status'   => $severity === 'critical' ? 'critical'
                        : ($severity === 'warning' ? 'warning' : 'ok'),
        ]);

        // Publicar evento IES v2.0
        $eventType = match($severity) {
            'critical' => 'tire_defect_critical',
            'warning'  => 'tire_defect_warning',
            default    => 'tire_inspection_ok',
        };

        $iesSeverity = match($severity) {
            'critical' => 'critical',
            'warning'  => 'high',
            default    => 'low',
        };

        $this->publisher->publish([
            'asset_id'   => "TIRE-{$tireId}",
            'asset_type' => 'tire',
            'plant_id'   => "PLT-{$unitId}",
            'area_id'    => 'FLOTILLA',
            'line_id'    => "UNIT-{$unitId}",
            'location'   => $position,
        ], [
            'type'     => $eventType,
            'category' => 'quality',
            'severity' => $iesSeverity,
        ], [
            'tire_id'              => $tireId,
            'position'             => $position,
            'tread_wear_pct'       => $result['tread_wear_pct'],
            'life_remaining_pct'   => $result['life_remaining_pct'],
            'severity'             => $severity,
            'confidence'           => $confidence,
            'inference_latency_ms' => $latencyMs,
            'findings'             => $result['findings'],
            'inspection_id'        => $inspection['id'],
            'requires_work_order'  => in_array($severity, ['critical', 'warning']) && $confidence >= 0.80,
            'requires_human_review'=> $confidence < 0.80,
        ], [
            'driver_id'      => $driverId,
            'runtime'        => 'edge',
            'inference_mode' => 'cnn_embedded',
        ]);

        return [
            'status'      => 'success',
            'tool'        => 'inspect_tire_quality',
            'runtime'     => 'edge',
            'inspection'  => $inspection,
            'result'      => $result,
            'latency_ms'  => $latencyMs,
            'event_published' => true,
        ];
    }

    /**
     * Tool 2 (Edge): detect_safety_violations
     * Detecta EPP faltante, postura de riesgo, zonas restringidas.
     */
    public function detect_safety_violations(array $params): array {
        $unitId   = $params['unit_id'] ?? throw new \InvalidArgumentException('unit_id requerido');
        $driverId = $params['driver_id'] ?? throw new \InvalidArgumentException('driver_id requerido');
        $location = $params['location'] ?? 'patio_inspection';

        $violations = $this->detectSafetyFrame($unitId, $location);
        $hasViolation = !empty($violations['detected']);

        $this->publisher->publish([
            'asset_id'   => "UNIT-{$unitId}",
            'asset_type' => 'vehicle',
            'plant_id'   => 'PLT-JUAREZ-01',
            'area_id'    => 'SAFETY',
            'location'   => $location,
        ], [
            'type'     => $hasViolation ? 'safety_violation_detected' : 'safety_check_passed',
            'category' => 'safety',
            'severity' => $hasViolation ? 'high' : 'low',
        ], [
            'unit_id'          => $unitId,
            'driver_id'        => $driverId,
            'violations'       => $violations['detected'],
            'violation_count'  => count($violations['detected']),
            'ppe_compliant'    => $violations['ppe_ok'],
            'posture_risk'     => $violations['posture_risk'],
            'blocked_zone'     => $violations['blocked_zone'],
            'confidence'       => $violations['confidence'],
        ], [
            'driver_id' => $driverId,
            'runtime'   => 'edge',
        ]);

        return [
            'status'           => 'success',
            'tool'             => 'detect_safety_violations',
            'runtime'          => 'edge',
            'has_violation'    => $hasViolation,
            'violations'       => $violations,
            'event_published'  => true,
        ];
    }

    /**
     * Tool 3 (Edge): monitor_worker_posture
     * Ergonomía durante inspección física de llantas.
     */
    public function monitor_worker_posture(array $params): array {
        $driverId = $params['driver_id'] ?? throw new \InvalidArgumentException('driver_id requerido');
        $unitId   = $params['unit_id'] ?? 'unknown';

        $posture = $this->analyzePosture($driverId);

        if ($posture['risk_level'] !== 'ok') {
            $this->publisher->publish([
                'asset_id'   => "DRIVER-{$driverId}",
                'asset_type' => 'worker',
                'plant_id'   => 'PLT-JUAREZ-01',
                'area_id'    => 'ERGONOMICS',
            ], [
                'type'     => 'ergonomic_risk_detected',
                'category' => 'safety',
                'severity' => $posture['risk_level'] === 'high' ? 'high' : 'medium',
            ], [
                'driver_id'         => $driverId,
                'spine_angle_deg'   => $posture['spine_angle'],
                'knee_flex_deg'     => $posture['knee_flex'],
                'risk_level'        => $posture['risk_level'],
                'recommendation'    => $posture['recommendation'],
                'duration_seconds'  => $posture['duration_seconds'],
            ], ['driver_id' => $driverId, 'runtime' => 'edge']);
        }

        return [
            'status'          => 'success',
            'tool'            => 'monitor_worker_posture',
            'runtime'         => 'edge',
            'posture'         => $posture,
            'event_published' => $posture['risk_level'] !== 'ok',
        ];
    }

    /**
     * Tool 4 (Edge): capture_video_stream
     * Activa captura de video de llanta para análisis histórico.
     */
    public function capture_video_stream(array $params): array {
        $tireId   = $params['tire_id'] ?? throw new \InvalidArgumentException('tire_id requerido');
        $unitId   = $params['unit_id'] ?? 'unknown';
        $duration = min((int)($params['duration_seconds'] ?? 10), 30);

        $streamId = 'STREAM-' . strtoupper(substr(md5($tireId . time()), 0, 8));
        $s3Url    = "s3://tyresense-prod/streams/{$tireId}/{$streamId}.mp4";

        $this->publisher->publish([
            'asset_id'   => "TIRE-{$tireId}",
            'asset_type' => 'tire',
            'plant_id'   => "PLT-{$unitId}",
        ], [
            'type'     => 'video_stream_captured',
            'category' => 'quality',
            'severity' => 'low',
        ], [
            'tire_id'          => $tireId,
            'stream_id'        => $streamId,
            'duration_seconds' => $duration,
            'storage_url'      => $s3Url,
            'fps'              => 30,
            'resolution'       => '1920x1080',
            'queued_for_cloud' => true,
        ], ['runtime' => 'edge']);

        return [
            'status'          => 'success',
            'tool'            => 'capture_video_stream',
            'runtime'         => 'edge',
            'stream_id'       => $streamId,
            'storage_url'     => $s3Url,
            'duration'        => $duration,
            'event_published' => true,
        ];
    }

    /**
     * Tool 5 (Edge): count_items_on_conveyor
     * Conteo de llantas en bodega / área de rotación.
     */
    public function count_items_on_conveyor(array $params): array {
        $areaId   = $params['area_id'] ?? 'bodega_llantas';
        $skuFilter = $params['sku'] ?? null;

        $count = $this->countTiresInArea($areaId, $skuFilter);

        $this->publisher->publish([
            'asset_id'   => "AREA-{$areaId}",
            'asset_type' => 'storage_area',
            'plant_id'   => 'PLT-JUAREZ-01',
            'area_id'    => $areaId,
        ], [
            'type'     => 'inventory_count_completed',
            'category' => 'productivity',
            'severity' => 'low',
        ], [
            'area_id'       => $areaId,
            'sku_filter'    => $skuFilter,
            'count_total'   => $count['total'],
            'count_by_sku'  => $count['by_sku'],
            'discrepancy'   => $count['discrepancy'],
            'count_time_ms' => $count['time_ms'],
        ], ['runtime' => 'edge']);

        return [
            'status'          => 'success',
            'tool'            => 'count_items_on_conveyor',
            'runtime'         => 'edge',
            'count'           => $count,
            'event_published' => true,
        ];
    }

    // ─────────────────────────────────────────────
    // CLOUD TOOLS (reentrenamiento y análisis histórico)
    // ─────────────────────────────────────────────

    /**
     * Tool 6 (Cloud): train_vision_model
     * Entrena modelo CNN con nuevos tipos de defecto.
     */
    public function train_vision_model(array $params): array {
        $defectTypes  = $params['defect_types'] ?? ['sidewall_crack', 'center_wear'];
        $sampleCount  = (int)($params['sample_count'] ?? 500);

        $jobId = 'TRAIN-' . strtoupper(substr(md5(implode('', $defectTypes) . time()), 0, 8));

        $this->publisher->publish([
            'asset_id'   => 'MODEL-VISION-CNN-01',
            'asset_type' => 'ml_model',
            'plant_id'   => 'PLT-CLOUD',
        ], [
            'type'     => 'model_training_started',
            'category' => 'system',
            'severity' => 'low',
        ], [
            'job_id'              => $jobId,
            'defect_types'        => $defectTypes,
            'sample_count'        => $sampleCount,
            'base_model_version'  => $this->moduleVersion,
            'estimated_duration_min' => (int)($sampleCount / 50),
            'target_accuracy'     => 0.95,
        ], ['runtime' => 'cloud']);

        return [
            'status'          => 'success',
            'tool'            => 'train_vision_model',
            'runtime'         => 'cloud',
            'job_id'          => $jobId,
            'defect_types'    => $defectTypes,
            'event_published' => true,
        ];
    }

    /**
     * Tool 7 (Cloud): improve_detection_model
     * Mejora continua basada en feedback de revisiones manuales.
     */
    public function improve_detection_model(array $params): array {
        $modelVersion = $params['model_version'] ?? $this->moduleVersion;
        $feedbackIds  = $params['feedback_inspection_ids'] ?? [];

        $improvementId = 'IMPROVE-' . strtoupper(substr(md5($modelVersion . time()), 0, 8));

        $this->publisher->publish([
            'asset_id'   => 'MODEL-VISION-CNN-01',
            'asset_type' => 'ml_model',
            'plant_id'   => 'PLT-CLOUD',
        ], [
            'type'     => 'model_improvement_triggered',
            'category' => 'system',
            'severity' => 'low',
        ], [
            'improvement_id'      => $improvementId,
            'base_version'        => $modelVersion,
            'feedback_samples'    => count($feedbackIds),
            'feedback_ids'        => $feedbackIds,
            'delta_accuracy_pct'  => round(mt_rand(1, 5) / 10, 2),
            'new_version'         => $this->bumpVersion($modelVersion),
            'deploy_to_edge'      => true,
        ], ['runtime' => 'cloud']);

        return [
            'status'          => 'success',
            'tool'            => 'improve_detection_model',
            'runtime'         => 'cloud',
            'improvement_id'  => $improvementId,
            'new_version'     => $this->bumpVersion($modelVersion),
            'event_published' => true,
        ];
    }

    /**
     * Tool 8 (Cloud): analyze_visual_patterns
     * Análisis histórico de patrones de desgaste para mantenimiento predictivo.
     */
    public function analyze_visual_patterns(array $params): array {
        $unitIds     = $params['unit_ids'] ?? [];
        $periodDays  = (int)($params['period_days'] ?? 30);

        $analysis = $this->runHistoricalAnalysis($unitIds, $periodDays);

        $this->publisher->publish([
            'asset_id'   => 'ANALYTICS-VISION-01',
            'asset_type' => 'analytics_service',
            'plant_id'   => 'PLT-CLOUD',
        ], [
            'type'     => 'predictive_pattern_analysis_completed',
            'category' => 'maintenance',
            'severity' => $analysis['units_at_risk'] > 0 ? 'high' : 'low',
        ], [
            'period_days'          => $periodDays,
            'units_analyzed'       => count($unitIds) ?: $analysis['total_units'],
            'inspections_reviewed' => $analysis['inspections_count'],
            'units_at_risk'        => $analysis['units_at_risk'],
            'dominant_wear_pattern'=> $analysis['dominant_pattern'],
            'avg_life_remaining'   => $analysis['avg_life'],
            'predicted_replacements_30d' => $analysis['replacements_30d'],
            'recommendations'      => $analysis['recommendations'],
        ], ['runtime' => 'cloud']);

        return [
            'status'          => 'success',
            'tool'            => 'analyze_visual_patterns',
            'runtime'         => 'cloud',
            'analysis'        => $analysis,
            'event_published' => true,
        ];
    }

    // ─────────────────────────────────────────────
    // Simuladores internos (mock de CNN / sensores)
    // ─────────────────────────────────────────────

    private function runCNNInference(string $tireId, string $position): array {
        $seed = crc32($tireId . $position . date('Y-m-d'));
        srand($seed);

        $wearPct = rand(20, 90);
        $lifePct = 100 - $wearPct;

        $severity = match(true) {
            $wearPct >= 80 => 'critical',
            $wearPct >= 55 => 'warning',
            default        => 'ok',
        };

        $findings = [
            'sidewall_crack'      => $severity === 'critical' && rand(0,1),
            'embedded_object'     => rand(0, 10) > 8,
            'low_pressure_visual' => $severity !== 'ok' && rand(0,1),
            'wear_pattern'        => ['center_wear','edge_wear','diagonal_wear','uniform'][rand(0,3)],
            'zones'               => [
                'tread_center'   => ['wear_pct' => $wearPct,           'status' => $severity],
                'sidewall_left'  => ['crack' => (bool)rand(0,1),       'status' => rand(0,10) > 7 ? 'warning' : 'ok'],
                'sidewall_right' => ['crack' => false,                 'status' => 'ok'],
            ],
            'recommendations' => $severity === 'critical'
                ? ['replace_immediately', 'check_alignment']
                : ($severity === 'warning' ? ['schedule_rotation', 'monitor_pressure'] : ['continue_operation']),
        ];

        return [
            'tread_wear_pct'      => $wearPct,
            'life_remaining_pct'  => $lifePct,
            'severity'            => $severity,
            'confidence'          => round(0.82 + (rand(0, 15) / 100), 2),
            'findings'            => $findings,
        ];
    }

    private function detectSafetyFrame(string $unitId, string $location): array {
        $violations = [];
        if (rand(0, 10) > 7) $violations[] = 'missing_safety_glasses';
        if (rand(0, 10) > 8) $violations[] = 'missing_hard_hat';
        if (rand(0, 10) > 9) $violations[] = 'restricted_zone_breach';
        return [
            'detected'     => $violations,
            'ppe_ok'       => empty($violations),
            'posture_risk' => rand(0, 10) > 7 ? 'moderate' : 'none',
            'blocked_zone' => in_array('restricted_zone_breach', $violations),
            'confidence'   => round(0.88 + rand(0,10)/100, 2),
        ];
    }

    private function analyzePosture(string $driverId): array {
        $spineAngle = rand(15, 75);
        $kneeFlex   = rand(20, 90);
        $riskLevel  = $spineAngle > 60 ? 'high' : ($spineAngle > 40 ? 'medium' : 'ok');
        return [
            'spine_angle'       => $spineAngle,
            'knee_flex'         => $kneeFlex,
            'risk_level'        => $riskLevel,
            'duration_seconds'  => rand(5, 120),
            'recommendation'    => $riskLevel !== 'ok' ? 'Ajustar postura al agacharse, doblar rodillas' : 'Postura correcta',
        ];
    }

    private function countTiresInArea(string $areaId, ?string $skuFilter): array {
        $skus = ['295/80R22.5' => rand(8,20), '315/80R22.5' => rand(4,12), '11R22.5' => rand(2,8)];
        if ($skuFilter) $skus = [$skuFilter => $skus[$skuFilter] ?? rand(3,10)];
        $total = array_sum($skus);
        return [
            'total'       => $total,
            'by_sku'      => $skus,
            'discrepancy' => rand(0, 2),
            'time_ms'     => rand(12, 45),
        ];
    }

    private function runHistoricalAnalysis(array $unitIds, int $days): array {
        return [
            'total_units'        => empty($unitIds) ? rand(15, 30) : count($unitIds),
            'inspections_count'  => rand(80, 200),
            'units_at_risk'      => rand(2, 6),
            'dominant_pattern'   => 'center_wear',
            'avg_life'           => rand(35, 65),
            'replacements_30d'   => rand(3, 12),
            'recommendations'    => ['Revisar presión flota norte', 'Rotación preventiva unidades 08/12/17'],
        ];
    }

    private function bumpVersion(string $version): string {
        $parts = explode('.', $version);
        $parts[2] = (int)($parts[2] ?? 0) + 1;
        return implode('.', $parts);
    }
}
