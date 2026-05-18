<?php
session_start();

// Default screen
$screen = isset($_GET['screen']) ? $_GET['screen'] : 'home';
$allowed = ['home', 'scan', 'result', 'history'];
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
    case 'history': include 'screens/history.php'; break;
}

include 'includes/footer.php';
?>
