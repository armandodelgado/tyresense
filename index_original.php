<?php
session_start();

// Default screen
$screen = isset($_GET['screen']) ? $_GET['screen'] : 'home';
$allowed = ['home', 'scan', 'result', 'history', 'supply'];
if (!in_array($screen, $allowed)) $screen = 'home';

// Mock data
$unit = [
    'id'    => '4821',
    'model' => 'Kenworth T680 · Eje triple',
    'route' => 'Ruta activa',
];

$tires = [
    ['pos'=>'D-I',  'label'=>'Delantera Izq',  'pct'=>82, 'status'=>'ok'],
    ['pos'=>'D-D',  'label'=>'Delantera Der',  'pct'=>78, 'status'=>'ok'],
    ['pos'=>'T1I',  'label'=>'Tractivo 1 Izq', 'pct'=>65, 'status'=>'ok'],
    ['pos'=>'T1Ii', 'label'=>'Tractivo 1 IzqI','pct'=>51, 'status'=>'warning'],
    ['pos'=>'T1Di', 'label'=>'Tractivo 1 DerI','pct'=>48, 'status'=>'warning'],
    ['pos'=>'T1D',  'label'=>'Tractivo 1 Der', 'pct'=>70, 'status'=>'ok'],
    ['pos'=>'T2I',  'label'=>'Trasero 2 Izq',  'pct'=>74, 'status'=>'ok'],
    ['pos'=>'T2Ii', 'label'=>'Trasero 2 IzqI', 'pct'=>28, 'status'=>'critical'],
    ['pos'=>'T2Di', 'label'=>'Trasero 2 DerI', 'pct'=>66, 'status'=>'ok'],
    ['pos'=>'T2D',  'label'=>'Trasero 2 Der',  'pct'=>72, 'status'=>'ok'],
];

$history = [
    ['icon'=>'danger', 'title'=>'T2Ii — Crítico',        'sub'=>'Desgaste 72% · objeto incrustado', 'time'=>'Hoy'],
    ['icon'=>'warning','title'=>'T1Ii — Advertencia',     'sub'=>'Desgaste desigual detectado',      'time'=>'Ayer'],
    ['icon'=>'ok',     'title'=>'Inspección completa',    'sub'=>'10 llantas · todas ok',            'time'=>'Lun 13'],
    ['icon'=>'ok',     'title'=>'Inspección completa',    'sub'=>'10 llantas · todas ok',            'time'=>'Vie 10'],
    ['icon'=>'info',   'title'=>'Reemplazo ejecutado',    'sub'=>'D-I · Bridgestone R295',           'time'=>'Mié 8'],
];

// Supply chain mock data
$supply_kpis = [
    ['val'=>'34',    'delta'=>'6 SKUs bajo mínimo',         'delta_color'=>'danger', 'label'=>'Stock disponible'],
    ['val'=>'4',     'delta'=>'3 proveedores',              'delta_color'=>'secondary','label'=>'OCs pendientes'],
    ['val'=>'$4,200','delta'=>'vs compra emergencia',       'delta_color'=>'success', 'label'=>'Ahorro anticipado'],
    ['val'=>'3.2 d', 'delta'=>'↓ 0.8 días vs mes ant.',    'delta_color'=>'success', 'label'=>'Lead time prom.'],
];
$skus = [
    ['sku'=>'295/80R22.5','brand'=>'Bridgestone R295','stock'=>8, 'min'=>10,'status'=>'danger'],
    ['sku'=>'315/80R22.5','brand'=>'Continental HSR2','stock'=>14,'min'=>8, 'status'=>'ok'],
    ['sku'=>'11R22.5',    'brand'=>'Michelin XZE2+',  'stock'=>4, 'min'=>10,'status'=>'danger'],
    ['sku'=>'275/70R22.5','brand'=>'Bridgestone M749','stock'=>8, 'min'=>6, 'status'=>'ok'],
    ['sku'=>'295/60R22.5','brand'=>'Continental HD3', 'stock'=>3, 'min'=>8, 'status'=>'danger'],
];
$suppliers = [
    ['name'=>'Bridgestone MX', 'score'=>92,'cpm'=>'$2.08','life'=>'A','delivery'=>'A','failures'=>'0.4%','action'=>'ok'],
    ['name'=>'Continental MX', 'score'=>84,'cpm'=>'$2.31','life'=>'A','delivery'=>'B','failures'=>'1.1%','action'=>'ok'],
    ['name'=>'Michelin Lat.',  'score'=>78,'cpm'=>'$2.74','life'=>'B','delivery'=>'C','failures'=>'0.8%','action'=>'warn'],
];
$purchase_orders = [
    ['sku'=>'295/80R22.5','qty'=>12,'supplier'=>'Bridgestone MX','urgency'=>'alta'],
    ['sku'=>'11R22.5',    'qty'=>8, 'supplier'=>'Continental MX','urgency'=>'alta'],
    ['sku'=>'295/60R22.5','qty'=>6, 'supplier'=>'Bridgestone MX','urgency'=>'media'],
    ['sku'=>'315/80R22.5','qty'=>4, 'supplier'=>'Continental MX','urgency'=>'baja'],
];
$demand_weeks = [
    ['week'=>'Sem 1','ot'=>8, 'prev'=>3],
    ['week'=>'Sem 2','ot'=>6, 'prev'=>4],
    ['week'=>'Sem 3','ot'=>11,'prev'=>3],
    ['week'=>'Sem 4','ot'=>7, 'prev'=>5],
];
$cost_per_km = [
    ['brand'=>'Bridgestone','real'=>2.08,'promised'=>2.05],
    ['brand'=>'Continental','real'=>2.31,'promised'=>2.28],
    ['brand'=>'Michelin',   'real'=>2.74,'promised'=>2.23],
];

// AI result (mock)
$result = [
    'pos'           => 'T2Ii',
    'life_pct'      => 28,
    'severity'      => 'critical',
    'tread_wear'    => 72,
    'sidewall_crack'=> true,
    'embedded'      => true,
    'low_pressure'  => true,
    'ot_number'     => '7731',
    'confidence'    => 94,
];

include 'includes/header.php';

switch ($screen) {
    case 'home':    include 'screens/home.php';    break;
    case 'scan':    include 'screens/scan.php';    break;
    case 'result':  include 'screens/result.php';  break;
    case 'supply':  include 'screens/supply.php';  break;
}

include 'includes/footer.php';
?>
