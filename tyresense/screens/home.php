<?php
// Helpers
function tireClass($status) {
    $map = ['ok'=>'tire-ok','warning'=>'tire-warning','critical'=>'tire-critical'];
    return $map[$status] ?? 'tire-ok';
}
function statusIcon($status) {
    if ($status === 'ok')       return svgCheck();
    if ($status === 'warning')  return svgWarn();
    return svgAlert();
}
function svgCheck() {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><polyline points="9 12 11 14 15 10"/></svg>';
}
function svgWarn() {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 22h20z"/><line x1="12" y1="10" x2="12" y2="14"/><circle cx="12" cy="18" r="1" fill="currentColor"/></svg>';
}
function svgAlert() {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="13"/><circle cx="12" cy="17" r="1" fill="currentColor"/></svg>';
}
?>

<!-- Topbar -->
<div class="topbar">
  <div>
    <div class="topbar-title">Buenos días, Carlos</div>
    <div class="topbar-sub">Vie 16 may &middot; Turno matutino</div>
  </div>
  <div class="avatar">CR</div>
</div>

<!-- Unit card -->
<div class="card">
  <div class="topbar" style="margin-bottom:8px">
    <div>
      <div class="topbar-title">Unidad #<?= htmlspecialchars($unit['id']) ?></div>
      <div class="topbar-sub"><?= htmlspecialchars($unit['model']) ?></div>
    </div>
    <span class="badge badge-info"><?= htmlspecialchars($unit['route']) ?></span>
  </div>

  <p class="topbar-sub" style="margin-bottom:6px">Toca una posición para inspeccionar</p>

  <!-- Eje delantero -->
  <div class="axle-label">— Eje delantero —</div>
  <div class="tire-grid tire-grid-2">
    <?php foreach (array_slice($tires,0,2) as $t): ?>
    <a href="?screen=scan" class="tire-slot <?= tireClass($t['status']) ?>">
      <?= statusIcon($t['status']) ?>
      <span><?= $t['pos'] ?> · <?= $t['pct'] ?>%</span>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Eje tractivo -->
  <div class="axle-label">— Eje tractivo —</div>
  <div class="tire-grid tire-grid-4">
    <?php foreach (array_slice($tires,2,4) as $t): ?>
    <a href="?screen=scan" class="tire-slot <?= tireClass($t['status']) ?>">
      <?= statusIcon($t['status']) ?>
      <span><?= $t['pos'] ?></span>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Eje trasero -->
  <div class="axle-label">— Eje trasero —</div>
  <div class="tire-grid tire-grid-4">
    <?php foreach (array_slice($tires,6,4) as $t): ?>
    <?php $link = ($t['status']==='critical') ? '?screen=result' : '?screen=scan'; ?>
    <a href="<?= $link ?>" class="tire-slot <?= tireClass($t['status']) ?>">
      <?= statusIcon($t['status']) ?>
      <span><?= $t['pos'] ?></span>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- Critical alert -->
<?php $criticals = array_filter($tires, fn($t)=>$t['status']==='critical'); ?>
<?php if ($criticals): ?>
<div class="alert alert-danger">
  <?= svgAlert() ?>
  <span>
    <?php foreach($criticals as $t): ?>
      Llanta <strong><?= $t['pos'] ?></strong> requiere revisión inmediata &mdash; desgaste crítico detectado.
    <?php endforeach; ?>
  </span>
</div>
<?php endif; ?>

<!-- Actions -->
<a href="?screen=scan" class="btn btn-primary">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M6.3 6.3a8 8 0 1 0 11.4 0"/></svg>
  Iniciar inspección pre-trip
</a>
<a href="#" class="btn btn-secondary" style="margin-top:-4px">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
  Firmar pre-trip sin novedad
</a>
