<?php
function svgCamera() {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>';
}
?>

<!-- Topbar -->
<div class="topbar">
  <div>
    <div class="topbar-title">Inspección de llanta</div>
    <div class="topbar-sub">Posición T2Ii &middot; Eje trasero interior izq.</div>
  </div>
  <span style="width:28px;height:28px;border-radius:50%;background:var(--danger-bg);color:var(--danger);
               font-size:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0">!</span>
</div>

<!-- Context alert -->
<div class="alert alert-warning">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;flex-shrink:0">
    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r="1" fill="currentColor"/>
  </svg>
  Última inspección hace 3 días &mdash; 1,240 km desde entonces.
</div>

<!-- Capture guide -->
<div class="section-lbl">Guía de captura</div>
<div class="guide-grid">
  <div class="guide-item">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
    Tread center
  </div>
  <div class="guide-item">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
    Flancos
  </div>
  <div class="guide-item">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
    Perfil lateral
  </div>
</div>

<!-- Camera zone -->
<a href="?screen=result" class="scan-zone">
  <?= svgCamera() ?>
  <span class="scan-zone-label">Toca para tomar foto</span>
  <span class="scan-zone-sub">Mantén la cámara a 30&ndash;50 cm</span>
</a>

<!-- Step indicator -->
<div style="text-align:center">
  <div class="section-lbl" style="margin-bottom:6px">Foto 1 de 3</div>
  <div class="dots">
    <div class="dot active"></div>
    <div class="dot"></div>
    <div class="dot"></div>
  </div>
</div>

<!-- Actions -->
<div class="btn-row" style="margin-top:auto">
  <a href="?screen=home" class="btn btn-secondary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><polyline points="15 18 9 12 15 6"/></svg>
    Volver
  </a>
  <a href="?screen=result" class="btn btn-primary">
    Analizar IA
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
  </a>
</div>
