<?php
$sev     = $result['severity'];
$circleClass = match($sev) {
    'critical' => 'result-circle-danger',
    'warning'  => 'result-circle-warning',
    default    => 'result-circle-ok',
};
$textColor = match($sev) {
    'critical' => 'var(--danger)',
    'warning'  => 'var(--warn)',
    default    => 'var(--ok)',
};
$sevLabel = match($sev) {
    'critical' => 'No apto para circulación',
    'warning'  => 'Revisión recomendada',
    default    => 'Apto para circulación',
};
$sevSub = match($sev) {
    'critical' => 'Reemplazo requerido antes de salir a ruta',
    'warning'  => 'Programa revisión antes del próximo turno',
    default    => 'Llanta en buen estado',
};

function yesNo($val) {
    if ($val) return '<span class="badge badge-danger">Sí</span>';
    return '<span class="badge badge-ok">No</span>';
}
?>

<!-- Topbar -->
<div class="topbar">
  <div>
    <div class="topbar-title">Resultado análisis IA</div>
    <div class="topbar-sub">Pos. <?= htmlspecialchars($result['pos']) ?> &middot; hace unos segundos</div>
  </div>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
       style="width:22px;height:22px;color:var(--text-3)">
    <rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/>
    <circle cx="12" cy="16" r="1" fill="currentColor"/>
  </svg>
</div>

<!-- Result circle -->
<div class="result-circle <?= $circleClass ?>" style="color:<?= $textColor ?>">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
       style="width:22px;height:22px">
    <?php if ($sev==='critical'): ?>
      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
      <circle cx="12" cy="16" r="1" fill="currentColor"/>
    <?php elseif($sev==='warning'): ?>
      <path d="M12 2L2 22h20z"/><line x1="12" y1="10" x2="12" y2="14"/>
      <circle cx="12" cy="18" r="1" fill="currentColor"/>
    <?php else: ?>
      <circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/>
    <?php endif; ?>
  </svg>
  <div class="result-pct"><?= $result['life_pct'] ?>%</div>
  <div class="result-sub">vida restante</div>
</div>

<!-- Severity banner -->
<div class="alert <?= $sev==='critical'?'alert-danger':($sev==='warning'?'alert-warning':'alert-ok') ?>"
     style="flex-direction:column;gap:3px;text-align:center;justify-content:center;padding:12px">
  <strong style="font-size:14px"><?= $sevLabel ?></strong>
  <span style="font-size:11px"><?= $sevSub ?></span>
</div>

<!-- Diagnosis detail -->
<div class="card" style="padding:12px">
  <div class="section-lbl" style="margin-bottom:8px">Diagnóstico por zona</div>

  <div class="detail-row">
    <span class="detail-label">Desgaste central</span>
    <span class="badge <?= $result['tread_wear']>60?'badge-danger':($result['tread_wear']>40?'badge-warning':'badge-ok') ?>">
      <?= $result['tread_wear'] ?>% desgastado
    </span>
  </div>

  <div class="detail-row">
    <span class="detail-label">Grieta en flanco</span>
    <?= yesNo($result['sidewall_crack']) ?>
  </div>

  <div class="detail-row">
    <span class="detail-label">Objeto incrustado</span>
    <?= yesNo($result['embedded']) ?>
  </div>

  <div class="detail-row">
    <span class="detail-label">Baja presión visual</span>
    <?= yesNo($result['low_pressure']) ?>
  </div>

  <div class="detail-row">
    <span class="detail-label">Confianza del modelo</span>
    <span class="badge badge-info"><?= $result['confidence'] ?>%</span>
  </div>
</div>

<!-- OT notice -->
<?php if ($sev==='critical'): ?>
<div class="alert alert-ok">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0">
    <polyline points="9 11 12 14 22 4"/>
    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
  </svg>
  OT <strong>#<?= $result['ot_number'] ?></strong> generada automáticamente en taller &mdash; prioridad alta.
</div>
<?php endif; ?>

<!-- Actions -->
<a href="?screen=home" class="btn btn-secondary" style="margin-top:auto">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px">
    <polyline points="15 18 9 12 15 6"/>
  </svg>
  Volver al inicio
</a>
