    </div><!-- /screen -->

    <!-- Bottom navigation -->
    <nav class="bottom-nav">
      <a href="?screen=home"    class="bnav <?= $screen==='home'    ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 12L12 3l9 9"/><path d="M9 21V12h6v9"/></svg>
        Inicio
      </a>
      <a href="?screen=scan"    class="bnav <?= $screen==='scan'    ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M6.3 6.3a8 8 0 1 0 11.4 0"/></svg>
        Escanear
      </a>
      <a href="?screen=history" class="bnav <?= $screen==='history' ? 'active' : '' ?>">
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
