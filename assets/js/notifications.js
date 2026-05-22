(function () {
  'use strict';

  var btn      = document.getElementById('notifBtn');
  var dropdown = document.getElementById('notifDropdown');
  var list     = document.getElementById('notifList');
  var markAll  = document.getElementById('notifMarkAll');

  if (!btn || !dropdown) return;

  var loaded = false;

  /* toggle */
  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    if (dropdown.hasAttribute('hidden')) {
      dropdown.removeAttribute('hidden');
      if (!loaded) { fetchList(); loaded = true; }
    } else {
      dropdown.setAttribute('hidden', '');
    }
  });

  /* close on outside click */
  document.addEventListener('click', function (e) {
    if (!dropdown.hasAttribute('hidden') &&
        !dropdown.contains(e.target) &&
        e.target !== btn) {
      dropdown.setAttribute('hidden', '');
    }
  });

  /* fetch list */
  function fetchList() {
    list.innerHTML = '<div class="notif-empty">Loading…</div>';
    fetch('notifications.php?action=list', { credentials: 'same-origin' })
      .then(function(r){ return r.json(); })
      .then(function(d){ renderList(d.items || []); })
      .catch(function(){ list.innerHTML = '<div class="notif-empty">Could not load.</div>'; });
  }

  function renderList(items) {
    if (!items.length) {
      list.innerHTML = '<div class="notif-empty">Wala pang notifications 🎉</div>';
      return;
    }
    list.innerHTML = items.map(function(n) {
      return '<div class="notif-item' + (n.is_read == 1 ? ' notif-read' : '') + '" data-id="' + n.id + '" data-link="' + (n.link || '') + '">' +
        '<div class="notif-icon">' + iconFor(n.type) + '</div>' +
        '<div class="notif-text">' +
          '<div class="notif-title">' + esc(n.title) + '</div>' +
          (n.body ? '<div class="notif-body">' + esc(n.body) + '</div>' : '') +
          '<div class="notif-ago">' + esc(n.ago) + '</div>' +
        '</div>' +
        (n.is_read == 0 ? '<div class="notif-dot"></div>' : '') +
      '</div>';
    }).join('');

    list.querySelectorAll('.notif-item').forEach(function(el) {
      el.addEventListener('click', function() {
        var id   = this.dataset.id;
        var link = this.dataset.link;
        fetch('notifications.php?action=mark_one&id=' + id, { credentials: 'same-origin' });
        this.classList.add('notif-read');
        var dot = this.querySelector('.notif-dot');
        if (dot) dot.remove();
        updateBadge(-1);
        if (link) setTimeout(function(){ window.location.href = link; }, 120);
      });
    });
  }

  /* mark all read */
  if (markAll) {
    markAll.addEventListener('click', function(e) {
      e.stopPropagation();
      fetch('notifications.php?action=mark_read', { credentials: 'same-origin' })
        .then(function() {
          list.querySelectorAll('.notif-item').forEach(function(el) {
            el.classList.add('notif-read');
            var dot = el.querySelector('.notif-dot');
            if (dot) dot.remove();
          });
          clearBadge();
        });
    });
  }

  /* badge helpers */
  function getBadge() { return btn.querySelector('.notif-badge'); }

  function updateBadge(delta) {
    var badge = getBadge();
    if (!badge) return;
    var n = Math.max(0, parseInt(badge.textContent || '0') + delta);
    if (n === 0) badge.remove();
    else badge.textContent = n > 99 ? '99' : n;
  }

  function clearBadge() {
    var badge = getBadge();
    if (badge) badge.remove();
  }

  /* poll every 60s */
  setInterval(function() {
    fetch('notifications.php?action=count', { credentials: 'same-origin' })
      .then(function(r){ return r.json(); })
      .then(function(d) {
        var n = parseInt(d.count || 0);
        var badge = getBadge();
        if (n > 0) {
          if (badge) {
            badge.textContent = n > 99 ? '99' : n;
          } else {
            var b = document.createElement('span');
            b.className = 'notif-badge';
            b.textContent = n > 99 ? '99' : n;
            btn.appendChild(b);
          }
          loaded = false;
        } else {
          clearBadge();
        }
      }).catch(function(){});
  }, 60000);

  /* helpers */
  function iconFor(type) {
    var map = {
      new_submission : '📥',
      grade_posted   : '✅',
      new_activity   : '📋',
      enrollment     : '🎓',
      announcement   : '📢'
    };
    return map[type] || '🔔';
  }

  function esc(str) {
    var d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
  }
})();
