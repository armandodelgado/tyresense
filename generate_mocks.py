import os

header = """<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>TyreSense AI — App Chofer</title>
<link rel="stylesheet" href="css/app.css">
</head>
<body>

<div class="stage">
  <!-- Nav pills (desktop preview) -->
  <div class="stage-nav">
    <a href="app_home.html"    class="snav active">Inicio</a>
    <a href="app_scan.html"    class="snav">Escanear</a>
    <a href="app_result.html"  class="snav">Resultado IA</a>
  </div>

  <!-- Phone frame -->
  <div class="phone">
    <div class="notch">
      <span class="notch-time">9:41</span>
      <span class="notch-icons">&#9679; &#9679; &#9679;</span>
    </div>
    <div class="screen">
"""

footer = """
    </div><!-- /screen -->

    <!-- Bottom navigation -->
    <nav class="bottom-nav">
      <a href="app_home.html"    class="bnav active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 12L12 3l9 9"/><path d="M9 21V12h6v9"/></svg>
        Inicio
      </a>
      <a href="app_scan.html"    class="bnav">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M6.3 6.3a8 8 0 1 0 11.4 0"/></svg>
        Escanear
      </a>
      <a href="#" class="bnav">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>
        Historial
      </a>
      <a href="#" class="bnav">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        Perfil
      </a>
    </nav>

  </div><!-- /phone -->
</div><!-- /stage -->

<script src="js/app.js"></script>
</body>
</html>
"""

def svgCheck(): return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><polyline points="9 12 11 14 15 10"/></svg>'
def svgWarn(): return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 22h20z"/><line x1="12" y1="10" x2="12" y2="14"/><circle cx="12" cy="18" r="1" fill="currentColor"/></svg>'
def svgAlert(): return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="13"/><circle cx="12" cy="17" r="1" fill="currentColor"/></svg>'

home = f"""
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
      <div class="topbar-title">Unidad #4821</div>
      <div class="topbar-sub">Kenworth T680 · Eje triple</div>
    </div>
    <span class="badge badge-info">Ruta activa</span>
  </div>

  <p class="topbar-sub" style="margin-bottom:6px">Toca una posición para inspeccionar</p>

  <!-- Eje delantero -->
  <div class="axle-label">— Eje delantero —</div>
  <div class="tire-grid tire-grid-2">
    <a href="app_scan.html" class="tire-slot tire-ok">{svgCheck()}<span>D-I · 82%</span></a>
    <a href="app_scan.html" class="tire-slot tire-ok">{svgCheck()}<span>D-D · 78%</span></a>
  </div>

  <!-- Eje tractivo -->
  <div class="axle-label">— Eje tractivo —</div>
  <div class="tire-grid tire-grid-4">
    <a href="app_scan.html" class="tire-slot tire-ok">{svgCheck()}<span>T1I</span></a>
    <a href="app_scan.html" class="tire-slot tire-warning">{svgWarn()}<span>T1Ii</span></a>
    <a href="app_scan.html" class="tire-slot tire-warning">{svgWarn()}<span>T1Di</span></a>
    <a href="app_scan.html" class="tire-slot tire-ok">{svgCheck()}<span>T1D</span></a>
  </div>

  <!-- Eje trasero -->
  <div class="axle-label">— Eje trasero —</div>
  <div class="tire-grid tire-grid-4">
    <a href="app_scan.html" class="tire-slot tire-ok">{svgCheck()}<span>T2I</span></a>
    <a href="app_result.html" class="tire-slot tire-critical">{svgAlert()}<span>T2Ii</span></a>
    <a href="app_scan.html" class="tire-slot tire-ok">{svgCheck()}<span>T2Di</span></a>
    <a href="app_scan.html" class="tire-slot tire-ok">{svgCheck()}<span>T2D</span></a>
  </div>
</div>

<!-- Critical alert -->
<div class="alert alert-danger">
  {svgAlert()}
  <span>Llanta <strong>T2Ii</strong> requiere revisión inmediata &mdash; desgaste crítico detectado.</span>
</div>

<!-- Actions -->
<a href="app_scan.html" class="btn btn-primary">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M6.3 6.3a8 8 0 1 0 11.4 0"/></svg>
  Iniciar inspección pre-trip
</a>
<a href="#" class="btn btn-secondary" style="margin-top:-4px">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
  Firmar pre-trip sin novedad
</a>
"""

scan = f"""
<!-- Topbar -->
<div class="topbar">
  <div>
    <div class="topbar-title">Inspección de llanta</div>
    <div class="topbar-sub">Posición T2Ii &middot; Eje trasero interior izq.</div>
  </div>
  <span style="width:28px;height:28px;border-radius:50%;background:var(--danger-bg);color:var(--danger);font-size:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0">!</span>
</div>

<!-- Context alert -->
<div class="alert alert-warning">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r="1" fill="currentColor"/></svg>
  Última inspección hace 3 días &mdash; 1,240 km desde entonces.
</div>

<!-- Capture guide -->
<div class="section-lbl">Guía de captura</div>
<div class="guide-grid">
  <div class="guide-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>Tread center</div>
  <div class="guide-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>Flancos</div>
  <div class="guide-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>Perfil lateral</div>
</div>

<!-- Camera zone -->
<a href="app_result.html" class="scan-zone" style="text-decoration:none; color:inherit; display:flex; flex-direction:column; align-items:center;">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:32px;height:32px;margin-bottom:8px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
  <span class="scan-zone-label">Toca para simular foto</span>
  <span class="scan-zone-sub">Mantén la cámara a 30&ndash;50 cm</span>
</a>

<!-- Step indicator -->
<div style="text-align:center">
  <div class="section-lbl" style="margin-bottom:6px">Foto 1 de 3</div>
  <div class="dots"><div class="dot active"></div><div class="dot"></div><div class="dot"></div></div>
</div>

<!-- Actions -->
<div class="btn-row" style="margin-top:auto">
  <a href="app_home.html" class="btn btn-secondary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><polyline points="15 18 9 12 15 6"/></svg>Volver</a>
  <a href="app_result.html" class="btn btn-primary">Analizar IA<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg></a>
</div>
"""

result = f"""
<!-- Topbar -->
<div class="topbar">
  <div>
    <div class="topbar-title">Resultado análisis IA</div>
    <div class="topbar-sub">Pos. T2Ii &middot; hace unos segundos</div>
  </div>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:22px;height:22px;color:var(--text-3)"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/><circle cx="12" cy="16" r="1" fill="currentColor"/></svg>
</div>

<!-- Result circle -->
<div class="result-circle result-circle-danger" style="color:var(--danger)">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r="1" fill="currentColor"/></svg>
  <div class="result-pct">28%</div>
  <div class="result-sub">vida restante</div>
</div>

<!-- Severity banner -->
<div class="alert alert-danger" style="flex-direction:column;gap:3px;text-align:center;justify-content:center;padding:12px">
  <strong style="font-size:14px">No apto para circulación</strong>
  <span style="font-size:11px">Reemplazo requerido antes de salir a ruta</span>
</div>

<!-- Diagnosis detail -->
<div class="card" style="padding:12px">
  <div class="section-lbl" style="margin-bottom:8px">Diagnóstico por zona</div>
  <div class="detail-row"><span class="detail-label">Desgaste central</span><span class="badge badge-danger">72% desgastado</span></div>
  <div class="detail-row"><span class="detail-label">Grieta en flanco</span><span class="badge badge-danger">Sí</span></div>
  <div class="detail-row"><span class="detail-label">Objeto incrustado</span><span class="badge badge-danger">Sí</span></div>
  <div class="detail-row"><span class="detail-label">Baja presión visual</span><span class="badge badge-danger">Sí</span></div>
  <div class="detail-row"><span class="detail-label">Confianza del modelo</span><span class="badge badge-info">94%</span></div>
</div>

<!-- OT notice -->
<div class="alert alert-ok">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
  OT <strong>#7731</strong> generada automáticamente en taller &mdash; prioridad alta.
</div>

<!-- Actions -->
<a href="app_home.html" class="btn btn-secondary" style="margin-top:auto">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><polyline points="15 18 9 12 15 6"/></svg>
  Volver al inicio
</a>
"""

with open("app_home.html", "w", encoding="utf-8") as f: f.write(header.replace('href="app_home.html"    class="snav"', 'href="app_home.html"    class="snav active"').replace('href="app_home.html"    class="bnav"', 'href="app_home.html"    class="bnav active"') + home + footer)
with open("app_scan.html", "w", encoding="utf-8") as f: f.write(header.replace('href="app_scan.html"    class="snav"', 'href="app_scan.html"    class="snav active"').replace('href="app_scan.html"    class="bnav"', 'href="app_scan.html"    class="bnav active"').replace('class="snav active">Inicio', 'class="snav">Inicio').replace('class="bnav active">\n        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 12L12 3l9 9"/>', 'class="bnav">\n        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 12L12 3l9 9"/>') + scan + footer)
with open("app_result.html", "w", encoding="utf-8") as f: f.write(header.replace('href="app_result.html"  class="snav"', 'href="app_result.html"  class="snav active"').replace('class="snav active">Inicio', 'class="snav">Inicio').replace('class="bnav active">\n        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 12L12 3l9 9"/>', 'class="bnav">\n        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 12L12 3l9 9"/>') + result + footer)
