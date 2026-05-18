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

  // Scan zone: show a brief loading state when file is selected
  const scanForm = document.getElementById('scanForm');
  if (scanForm) {
    const fileInput = scanForm.querySelector('input[type="file"]');
    if (fileInput) {
      fileInput.addEventListener('change', function () {
        const label = scanForm.querySelector('.scan-zone-label');
        if (label) label.textContent = 'Analizando…';
        const zone = scanForm.querySelector('.scan-zone');
        if (zone) zone.style.borderColor = '#185fa5';
      });
    }
  }

});
