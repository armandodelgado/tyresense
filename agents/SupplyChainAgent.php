<?php
/**
 * TyreSense AI — Agente: Cadena de Suministro
 * Tipo: Cloud
 * Consume eventos del Agente CMMS (OTs de tipo replacement).
 * Tools: 5 funcionales
 * KPIs: costo/km real, scorecard proveedores, stock mínimo
 */

require_once __DIR__ . '/../api/IESPublisher.php';
require_once __DIR__ . '/../api/MockDB.php';

class SupplyChainAgent {

    private IESPublisher $publisher;

    public function __construct() {
        $this->publisher = new IESPublisher();
    }

    // ─────────────────────────────────────────────
    // Tool 1: check_inventory_on_replacement
    // Disparado por CMMS cuando cierra OT tipo replacement.
    // ─────────────────────────────────────────────

    public function check_inventory_on_replacement(array $params): array {
        $tireId = $params['tire_id'] ?? throw new \InvalidArgumentException('tire_id requerido');
        $woId   = $params['work_order_id'] ?? 'WO-UNKNOWN';

        $tire = MockDB::get('tires', $tireId);
        if (!$tire) return ['status' => 'error', 'message' => "Llanta {$tireId} no encontrada"];

        $sku = $tire['sku'];
        $inventory = MockDB::get('inventory');

        // Buscar en inventario por SKU
        $invRecord = null;
        $invId     = null;
        foreach ($inventory as $id => $item) {
            if ($item['sku'] === $sku) { $invRecord = $item; $invId = $id; break; }
        }

        if (!$invRecord) {
            $this->publisher->publish([
                'asset_id'   => "SKU-{$sku}",
                'asset_type' => 'inventory_item',
                'plant_id'   => 'PLT-JUAREZ-01',
                'area_id'    => 'WAREHOUSE',
            ], [
                'type'     => 'sku_not_found_in_inventory',
                'category' => 'productivity',
                'severity' => 'critical',
            ], [
                'sku'        => $sku,
                'tire_id'    => $tireId,
                'wo_id'      => $woId,
                'action'     => 'emergency_purchase_required',
            ], ['runtime' => 'cloud']);

            return ['status' => 'sku_not_found', 'sku' => $sku, 'event_published' => true];
        }

        // Decrementar qty al consumir llanta
        $newQty = max(0, $invRecord['qty'] - 1);
        MockDB::updateInventory($invId, ['qty' => $newQty]);

        // Calcular urgencia según regla del agente
        $urgency = match(true) {
            $newQty < $invRecord['min_stock']               => 'high',
            $newQty < $invRecord['min_stock'] * 1.5        => 'medium',
            default                                          => 'ok',
        };

        $this->publisher->publish([
            'asset_id'   => "SKU-{$sku}",
            'asset_type' => 'inventory_item',
            'plant_id'   => 'PLT-JUAREZ-01',
            'area_id'    => 'WAREHOUSE',
        ], [
            'type'     => 'inventory_consumed_on_replacement',
            'category' => 'productivity',
            'severity' => match($urgency) { 'high'=>'high','medium'=>'medium',default=>'low' },
        ], [
            'sku'             => $sku,
            'tire_id'         => $tireId,
            'wo_id'           => $woId,
            'qty_before'      => $invRecord['qty'],
            'qty_after'       => $newQty,
            'min_stock'       => $invRecord['min_stock'],
            'urgency'         => $urgency,
            'trigger_purchase'=> $urgency !== 'ok',
        ], ['runtime' => 'cloud']);

        $result = [
            'status'          => 'success',
            'tool'            => 'check_inventory_on_replacement',
            'sku'             => $sku,
            'qty_before'      => $invRecord['qty'],
            'qty_after'       => $newQty,
            'urgency'         => $urgency,
            'event_published' => true,
        ];

        // Si bajo mínimo, auto-generar sugerencia de compra
        if ($urgency !== 'ok') {
            $po = $this->suggest_purchase_order([
                'sku'         => $sku,
                'supplier_id' => $invRecord['supplier_id'],
                'qty_needed'  => $invRecord['max_stock'] - $newQty,
                'urgency'     => $urgency,
                'trigger'     => 'auto_replacement',
            ]);
            $result['purchase_order_suggested'] = $po;
        }

        return $result;
    }

    // ─────────────────────────────────────────────
    // Tool 2: suggest_purchase_order
    // ─────────────────────────────────────────────

    public function suggest_purchase_order(array $params): array {
        $sku        = $params['sku']         ?? throw new \InvalidArgumentException('sku requerido');
        $supplierId = $params['supplier_id'] ?? throw new \InvalidArgumentException('supplier_id requerido');
        $qtyNeeded  = (int)($params['qty_needed'] ?? 10);
        $urgency    = $params['urgency']     ?? 'medium';
        $trigger    = $params['trigger']     ?? 'manual';

        $supplier = MockDB::get('suppliers', $supplierId);
        $inventory = MockDB::get('inventory');

        $unitCost = 420.00;
        foreach ($inventory as $item) {
            if ($item['sku'] === $sku) { $unitCost = (float)($item['unit_cost'] ?? 420.00); break; }
        }

        $totalCost = $qtyNeeded * $unitCost;
        $leadDays  = $supplier ? (float)$supplier['avg_lead_time_days'] : 5.0;

        $po = MockDB::createPurchaseOrder([
            'sku'         => $sku,
            'supplier_id' => $supplierId,
            'qty'         => $qtyNeeded,
            'unit_cost'   => $unitCost,
            'total_cost'  => $totalCost,
            'urgency'     => $urgency,
            'trigger'     => $trigger,
            'estimated_delivery_days' => $leadDays,
            'approved'    => false,
        ]);

        $this->publisher->publish([
            'asset_id'   => "PO-{$po['id']}",
            'asset_type' => 'purchase_order',
            'plant_id'   => 'PLT-JUAREZ-01',
            'area_id'    => 'PROCUREMENT',
        ], [
            'type'     => 'purchase_order_suggested',
            'category' => 'productivity',
            'severity' => match($urgency) { 'high'=>'high','medium'=>'medium',default=>'low' },
        ], [
            'po_id'           => $po['id'],
            'sku'             => $sku,
            'supplier_id'     => $supplierId,
            'supplier_name'   => $supplier['name'] ?? 'Desconocido',
            'qty'             => $qtyNeeded,
            'unit_cost_usd'   => $unitCost,
            'total_cost_usd'  => $totalCost,
            'urgency'         => $urgency,
            'lead_time_days'  => $leadDays,
            'trigger'         => $trigger,
            'requires_approval' => true,
        ], ['runtime' => 'cloud']);

        return ['status' => 'success', 'tool' => 'suggest_purchase_order', 'purchase_order' => $po, 'event_published' => true];
    }

    // ─────────────────────────────────────────────
    // Tool 3: calculate_supplier_score
    // Scorecard mensual de proveedores (fórmula del modelo de datos)
    // ─────────────────────────────────────────────

    public function calculate_supplier_score(array $params): array {
        $supplierId = $params['supplier_id'] ?? null;

        $suppliers = $supplierId
            ? [$supplierId => MockDB::get('suppliers', $supplierId)]
            : MockDB::get('suppliers');

        $scores = [];

        foreach ($suppliers as $id => $supplier) {
            if (!$supplier || !$supplier['active']) continue;

            // Fórmula exacta del modelo de datos
            $lifeScore     = ($supplier['avg_life_km']  / $supplier['promised_life_km']) * 40;
            $failureScore  = (1 - $supplier['failure_rate']) * 30;
            $deliveryNum   = match($supplier['delivery_grade']) { 'A'=>1.0,'B'=>0.66,default=>0.33 };
            $deliveryScore = $deliveryNum * 20;
            $priceScore    = $this->calcPriceCompetitiveness($id) * 10;

            $score = (int)min(100, round($lifeScore + $failureScore + $deliveryScore + $priceScore));

            MockDB::updateSupplierScore($id, $score);

            $action = $score < 70 ? 'Revisar' : ($score >= 85 ? 'Preferido' : 'Mantener');

            $scores[$id] = [
                'supplier_id'    => $id,
                'name'           => $supplier['name'],
                'score'          => $score,
                'action'         => $action,
                'breakdown'      => [
                    'life_score'     => round($lifeScore, 1),
                    'failure_score'  => round($failureScore, 1),
                    'delivery_score' => round($deliveryScore, 1),
                    'price_score'    => round($priceScore, 1),
                ],
                'deviation_life_km' => $supplier['avg_life_km'] - $supplier['promised_life_km'],
            ];

            $this->publisher->publish([
                'asset_id'   => "SUPPLIER-{$id}",
                'asset_type' => 'supplier',
                'plant_id'   => 'PLT-CLOUD',
                'area_id'    => 'PROCUREMENT',
            ], [
                'type'     => 'supplier_score_recalculated',
                'category' => 'quality',
                'severity' => $score < 70 ? 'high' : 'low',
            ], [
                'supplier_id'   => $id,
                'supplier_name' => $supplier['name'],
                'new_score'     => $score,
                'action'        => $action,
                'breakdown'     => $scores[$id]['breakdown'],
                'kpi_27_deviation_km' => $scores[$id]['deviation_life_km'],
            ], ['runtime' => 'cloud']);
        }

        return [
            'status'          => 'success',
            'tool'            => 'calculate_supplier_score',
            'scores'          => array_values($scores),
            'event_published' => true,
        ];
    }

    // ─────────────────────────────────────────────
    // Tool 4: calculate_cost_per_km
    // KPI real costo/km por llanta y por proveedor
    // ─────────────────────────────────────────────

    public function calculate_cost_per_km(array $params): array {
        $supplierId = $params['supplier_id'] ?? null;

        $tires     = MockDB::get('tires');
        $inventory = MockDB::get('inventory');
        $suppliers = MockDB::get('suppliers');

        $results = [];

        foreach ($tires as $tireId => $tire) {
            if ($supplierId && $tire['supplier_id'] !== $supplierId) continue;
            if ($tire['km_accumulated'] < 1000) continue;

            // Buscar unit_cost en inventario por SKU
            $unitCost = 420.0;
            foreach ($inventory as $inv) {
                if ($inv['sku'] === $tire['sku']) { $unitCost = (float)$inv['unit_cost']; break; }
            }

            $costPerKm = round($unitCost / $tire['km_accumulated'], 4);

            $results[] = [
                'tire_id'        => $tireId,
                'sku'            => $tire['sku'],
                'brand'          => $tire['brand'],
                'supplier_id'    => $tire['supplier_id'],
                'km_accumulated' => $tire['km_accumulated'],
                'unit_cost_usd'  => $unitCost,
                'cost_per_km'    => $costPerKm,
                'life_pct'       => $tire['life_pct'],
            ];
        }

        // Agrupar por proveedor
        $bySupplier = [];
        foreach ($results as $r) {
            $sid = $r['supplier_id'];
            if (!isset($bySupplier[$sid])) {
                $bySupplier[$sid] = ['supplier_id'=>$sid,'name'=>$suppliers[$sid]['name']??$sid,'tires'=>0,'avg_cost_per_km'=>0,'total'=>0];
            }
            $bySupplier[$sid]['tires']++;
            $bySupplier[$sid]['total'] += $r['cost_per_km'];
        }
        foreach ($bySupplier as $sid => &$row) {
            $row['avg_cost_per_km'] = round($row['total'] / $row['tires'], 4);
            unset($row['total']);
        }

        $this->publisher->publish([
            'asset_id'   => 'KPI-COST-PER-KM',
            'asset_type' => 'kpi_report',
            'plant_id'   => 'PLT-CLOUD',
        ], [
            'type'     => 'kpi_cost_per_km_calculated',
            'category' => 'productivity',
            'severity' => 'low',
        ], [
            'tires_analyzed'    => count($results),
            'by_supplier'       => array_values($bySupplier),
            'best_supplier'     => !empty($bySupplier) ? array_keys($bySupplier, min($bySupplier))[0] : null,
        ], ['runtime' => 'cloud']);

        return [
            'status'          => 'success',
            'tool'            => 'calculate_cost_per_km',
            'tires'           => $results,
            'by_supplier'     => array_values($bySupplier),
            'event_published' => true,
        ];
    }

    // ─────────────────────────────────────────────
    // Tool 5: get_supply_chain_dashboard
    // Vista ejecutiva supply chain para el dashboard
    // ─────────────────────────────────────────────

    public function get_supply_chain_dashboard(array $params): array {
        $inventory      = MockDB::get('inventory');
        $suppliers      = MockDB::get('suppliers');
        $purchaseOrders = MockDB::get('purchase_orders');

        $stockAlerts = [];
        foreach ($inventory as $id => $item) {
            if ($item['qty'] < $item['min_stock']) {
                $stockAlerts[] = array_merge($item, ['urgency' => 'high', 'gap' => $item['min_stock'] - $item['qty']]);
            } elseif ($item['qty'] < $item['min_stock'] * 1.5) {
                $stockAlerts[] = array_merge($item, ['urgency' => 'medium', 'gap' => 0]);
            }
        }

        $supplierSummary = array_map(fn($s) => [
            'id'            => $s['id'],
            'name'          => $s['name'],
            'score'         => $s['score'],
            'action'        => $s['score'] < 70 ? 'Revisar' : ($s['score'] >= 85 ? 'Preferido' : 'Mantener'),
            'delivery_grade'=> $s['delivery_grade'],
            'failure_rate'  => $s['failure_rate'],
            'lead_days'     => $s['avg_lead_time_days'],
        ], array_values($suppliers));

        $this->publisher->publish([
            'asset_id'   => 'DASHBOARD-SUPPLY',
            'asset_type' => 'dashboard',
            'plant_id'   => 'PLT-CLOUD',
        ], [
            'type'     => 'supply_chain_dashboard_generated',
            'category' => 'productivity',
            'severity' => !empty($stockAlerts) ? 'high' : 'low',
        ], [
            'stock_alerts_count'   => count($stockAlerts),
            'purchase_orders_open' => count(array_filter($purchaseOrders, fn($p) => $p['status'] === 'suggested')),
            'suppliers_at_risk'    => count(array_filter($suppliers, fn($s) => $s['score'] < 70)),
        ], ['runtime' => 'cloud']);

        return [
            'status'           => 'success',
            'tool'             => 'get_supply_chain_dashboard',
            'inventory'        => array_values($inventory),
            'stock_alerts'     => $stockAlerts,
            'suppliers'        => $supplierSummary,
            'purchase_orders'  => array_values($purchaseOrders),
            'event_published'  => true,
        ];
    }

    // ─────────────────────────────────────────────
    // Internals
    // ─────────────────────────────────────────────

    private function calcPriceCompetitiveness(string $supplierId): float {
        // Mock: competitividad relativa 0–1 vs mercado
        return match($supplierId) {
            'SUP-001' => 0.85,
            'SUP-002' => 0.70,
            'SUP-003' => 0.90,
            default   => 0.75,
        };
    }
}
