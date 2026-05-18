<?php
session_start();

// Default screen
$screen = isset($_GET['screen']) ? $_GET['screen'] : 'home';
$allowed = ['home', 'scan', 'result', 'history', 'process_scan'];
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

// Truck Health Agent — GO / NO-GO
$python_cmd = "python";
$health_script = escapeshellarg(__DIR__ . "/Python agents/truck_health_agent.py");
$health_output = shell_exec("$python_cmd $health_script \"{$unit['id']}\" 500 normal 2>&1");
$truck_health = null;
if ($health_output) {
    $decoded_health = json_decode($health_output, true);
    if ($decoded_health && isset($decoded_health['verdict'])) {
        $truck_health = $decoded_health;
    }
}
// Fallback si Python no está disponible
if (!$truck_health) {
    $truck_health = [
        'verdict' => 'NO-GO',
        'reasons' => ['Presión crítica en T2Ii: 75 PSI (mín: 80)', 'Vida útil crítica en T2Ii: 28% (mín: 30%)'],
        'warnings' => ['Presión baja en T1Di: 92 PSI', 'Llanta T1Ii en estado de advertencia', 'Llanta T1Di en estado de advertencia'],
        'flagged_tires' => ['T2Ii'],
        'summary' => ['total_tires' => 10, 'ok' => 7, 'warning' => 2, 'critical' => 1, 'avg_life_pct' => 63.4],
        'trip' => ['distance' => 500, 'load' => 'normal', 'route' => 'Monterrey - Saltillo'],
    ];
}

include 'includes/header.php';

switch ($screen) {
    case 'home':    include 'screens/home.php';    break;
    case 'scan':    include 'screens/scan.php';    break;
    case 'result':  include 'screens/result.php';  break;
    case 'history': include 'screens/history.php'; break;
    case 'process_scan':
        if (isset($_FILES['tire_image']) && $_FILES['tire_image']['error'] == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['tire_image']['tmp_name'];
            $name = basename($_FILES['tire_image']['name']);
            $upload_file = "uploads/" . time() . "_" . $name;
            move_uploaded_file($tmp_name, $upload_file);
            
            // Ejecutar el script de Python
            $python_cmd = "python"; // Asumiendo que python está en el PATH
            $script_path = escapeshellarg(__DIR__ . "/Python agents/vision_model.py");
            $image_path = escapeshellarg(__DIR__ . "/" . $upload_file);
            
            $output = shell_exec("$python_cmd $script_path $image_path 2>&1");
            
            if ($output) {
                $decoded = json_decode($output, true);
                if ($decoded) {
                    if (isset($decoded['fallback'])) {
                        $result = $decoded['fallback'];
                    } else if (!isset($decoded['error'])) {
                        $result = $decoded;
                    }
                }
            }
        }
        include 'screens/result.php';
        break;
}

include 'includes/footer.php';
?>
