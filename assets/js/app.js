/* TRX Yerner University — app.js (offline-safe, no CDN) */

/* Staggered card animation on load */
document.addEventListener('DOMContentLoaded', () => {

  /* Assign --i CSS var to grid children for staggered delays */
  document.querySelectorAll('.grid > *, .feature-grid > *, .school-grid > *, .stat-card').forEach((el, i) => {
    el.style.setProperty('--i', i);
    el.style.animationDelay = (i * 55 + 80) + 'ms';
  });

  /* Nav link enter stagger */
  document.querySelectorAll('.nav a').forEach((el, i) => {
    el.style.animationDelay = (i * 30 + 60) + 'ms';
    el.style.animation = 'slideRight .3s both';
    el.style.animationDelay = (i * 30 + 60) + 'ms';
  });

  /* Auto-dismiss alerts after 5s */
  document.querySelectorAll('.alert').forEach(el => {
    setTimeout(() => {
      el.style.transition = 'opacity .4s, transform .4s';
      el.style.opacity = '0';
      el.style.transform = 'translateY(-8px)';
      setTimeout(() => el.remove(), 400);
    }, 5000);
  });

  /* Confirm on danger buttons */
  document.querySelectorAll('a.btn.danger, button.btn.danger').forEach(btn => {
    btn.addEventListener('click', e => {
      if (!confirm('Are you sure you want to delete this? This cannot be undone.')) {
        e.preventDefault();
      }
    });
  });

  /* Active nav highlight (already set server-side via .active class, but keep) */

  /* Simple table row highlight */
  document.querySelectorAll('tbody tr').forEach(tr => {
    tr.addEventListener('mouseenter', () => tr.style.transition = 'background 120ms ease');
  });
});
