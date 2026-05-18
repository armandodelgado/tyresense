<?php
function histIcon($type) {
    $icons = [
        'ok'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>',
        'warning' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 22h20z"/><line x1="12" y1="10" x2="12" y2="14"/><circle cx="12" cy="18" r="1" fill="currentColor"/></svg>',
        'danger'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r="1" fill="currentColor"/></svg>',
        'info'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>',
    ];
    return $icons[$type] ?? $icons['info'];
}
?>

<!-- Topbar -->
<div class="topbar">
  <div>
    <div class="topbar-title">Historial de inspecciones</div>
    <div class="topbar-sub">Unidad #<?= htmlspecialchars($unit['id']) ?> &middot; último mes</div>
  </div>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
       style="width:18px;height:18px;color:var(--text-3)">
    <line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/>
    <line x1="11" y1="18" x2="13" y2="18"/>
  </svg>
</div>

<!-- Summary stats -->
<div class="stats-row">
  <div class="stat-box">
    <div class="stat-val">14</div>
    <div class="stat-lbl">Inspecciones este mes</div>
  </div>
  <div class="stat-box">
    <div class="stat-val" style="color:var(--danger)">2</div>
    <div class="stat-lbl">Alertas críticas</div>
  </div>
</div>

<!-- History list -->
<div class="card" style="padding:12px">
  <div class="section-lbl" style="margin-bottom:8px">Actividad reciente</div>

  <?php foreach ($history as $item): ?>
  <div class="hist-item">
    <div class="hist-icon hist-icon-<?= $item['icon'] ?>">
      <?= histIcon($item['icon']) ?>
    </div>
    <div style="flex:1;min-width:0">
      <div class="hist-main"><?= htmlspecialchars($item['title']) ?></div>
      <div class="hist-sub"><?= htmlspecialchars($item['sub']) ?></div>
    </div>
    <div class="hist-time"><?= htmlspecialchars($item['time']) ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- CTA -->
<a href="?screen=scan" class="btn btn-primary" style="margin-top:auto">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px">
    <circle cx="12" cy="12" r="3"/><path d="M6.3 6.3a8 8 0 1 0 11.4 0"/>
  </svg>
  Nueva inspección
</a>
