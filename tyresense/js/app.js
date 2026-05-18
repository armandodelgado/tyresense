// TyreSense AI — App JS
// All navigation is server-side via PHP GET params.
// This file handles minor UX enhancements only.

document.addEventListener('DOMContentLoaded', function () {

  // Highlight active bottom nav item based on current URL
  const params  = new URLSearchParams(window.location.search);
  const current = params.get('screen') || 'home';
  document.querySelectorAll('.bnav').forEach(function (el) {
    const href   = el.getAttribute('href');
    const screen = new URLSearchParams(href.replace('?','') ).get('screen') || 'home';
    if (screen === current) el.classList.add('active');
  });

  // Scan zone: show a brief loading state before navigating
  const scanZone = document.querySelector('.scan-zone');
  if (scanZone) {
    scanZone.addEventListener('click', function (e) {
      e.preventDefault();
      this.querySelector('.scan-zone-label').textContent = 'Analizando...';
      this.style.borderColor = '#185fa5';
      setTimeout(() => { window.location.href = this.href; }, 600);
    });
  }

});
