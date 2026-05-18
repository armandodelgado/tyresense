<!DOCTYPE html>
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
    <a href="?screen=home"    class="snav <?= $screen==='home'    ? 'active' : '' ?>">Inicio</a>
    <a href="?screen=scan"    class="snav <?= $screen==='scan'    ? 'active' : '' ?>">Escanear</a>
    <a href="?screen=result"  class="snav <?= $screen==='result'  ? 'active' : '' ?>">Resultado IA</a>
    <a href="?screen=history" class="snav <?= $screen==='history' ? 'active' : '' ?>">Historial</a>
  </div>

  <!-- Phone frame -->
  <div class="phone">
    <div class="notch">
      <span class="notch-time">9:41</span>
      <span class="notch-icons">&#9679; &#9679; &#9679;</span>
    </div>
    <div class="screen">
