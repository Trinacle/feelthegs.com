/**
 * Feel The G's — Collection Filter behavior.
 *
 * Progressive enhancement of the server-rendered FTGS_Collection_Filter_Widget.
 * All filtering works without JS (plain GET links). This adds:
 *   - Dual-thumb price slider with live label + range track fill
 *   - Collapsible filter groups (head click toggles aria-expanded)
 *   - Per-group search box (filters the visible options)
 *   - Mobile drawer: the sidebar slides in on small screens
 *
 * The "Go" button on the price slider is what actually navigates (matching the
 * Fantasies Boutique UX where dragging only updates visuals until applied).
 */
(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  // ---- Collapsible groups --------------------------------------------------
  ready(function () {
    document.querySelectorAll('.ftgs-filter-head').forEach(function (head) {
      head.addEventListener('click', function () {
        var expanded = head.getAttribute('aria-expanded') !== 'false';
        head.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        var group = head.closest('.ftgs-filter-group');
        if (group) group.classList.toggle('ftgs-collapsed', expanded);
      });
    });
  });

  // ---- Per-group search ----------------------------------------------------
  ready(function () {
    document.querySelectorAll('[data-ftgs-search]').forEach(function (input) {
      input.addEventListener('input', function () {
        var slug = input.getAttribute('data-ftgs-search');
        var group = input.closest('.ftgs-filter-group');
        if (!group) return;
        var q = input.value.toLowerCase().trim();
        group.querySelectorAll('.ftgs-filter-list li').forEach(function (li) {
          var txt = (li.textContent || '').toLowerCase();
          li.style.display = q === '' || txt.indexOf(q) !== -1 ? '' : 'none';
        });
      });
    });
  });

  // ---- Price slider --------------------------------------------------------
  ready(function () {
    var sliders = document.querySelectorAll('[data-ftgs-range]');
    if (!sliders.length) return;

    var minInput = document.querySelector('[data-ftgs-range="min"]');
    var maxInput = document.querySelector('[data-ftgs-range="max"]');
    if (!minInput || !maxInput) return;

    var track = document.querySelector('[data-ftgs-progress]');
    var minLabel = document.querySelector('[data-ftgs-min-label]');
    var maxLabel = document.querySelector('[data-ftgs-max-label]');
    var goBtn = document.querySelector('[data-ftgs-price-go]');
    var limitMin = parseFloat(minInput.min);
    var limitMax = parseFloat(minInput.max);

    function money(v) {
      // WC formats currency with the symbol; we just show a rounded number and
      // let the server re-render exact currency on navigation. Keep it simple.
      var n = parseFloat(v);
      if (isNaN(n)) n = 0;
      return (n % 1 === 0 ? n : n.toFixed(2));
    }

    function update() {
      var lo = parseFloat(minInput.value);
      var hi = parseFloat(maxInput.value);
      // Keep lo <= hi (swap if thumbs cross).
      if (lo > hi) {
        var t = lo; lo = hi; hi = t;
      }
      var span = Math.max(1, limitMax - limitMin);
      var leftPct = ((lo - limitMin) / span) * 100;
      var rightPct = ((hi - limitMin) / span) * 100;
      if (track) {
        track.style.left = leftPct + '%';
        track.style.width = (rightPct - leftPct) + '%';
      }
      if (minLabel) minLabel.textContent = '$' + money(lo);
      if (maxLabel) maxLabel.textContent = (hi >= limitMax ? ('$' + money(hi) + '+') : ('$' + money(hi)));
    }

    minInput.addEventListener('input', update);
    maxInput.addEventListener('input', update);
    update();

    // "Go" — navigate with the slider values applied.
    if (goBtn) {
      goBtn.addEventListener('click', function () {
        var lo = parseFloat(minInput.value);
        var hi = parseFloat(maxInput.value);
        if (lo > hi) { var t = lo; lo = hi; hi = t; }
        var url = new URL(window.location.href);
        if (lo > limitMin) url.searchParams.set('min_price', lo);
        else url.searchParams.delete('min_price');
        if (hi < limitMax) url.searchParams.set('max_price', hi);
        else url.searchParams.delete('max_price');
        url.searchParams.delete('paged'); // reset to page 1
        window.location.href = url.toString();
      });
    }

    // Enter key on the slider also applies.
    [minInput, maxInput].forEach(function (el) {
      el.addEventListener('change', function () {
        if (goBtn) goBtn.click();
      });
    });
  });

  // ---- Clear all -----------------------------------------------------------
  ready(function () {
    var clearBtn = document.querySelector('[data-ftgs-clear]');
    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        var url = new URL(window.location.href);
        // Strip every known filter param.
        ['min_price', 'max_price', 'on_sale', 'paged'].forEach(function (k) { url.searchParams.delete(k); });
        Array.from(url.searchParams.keys()).forEach(function (k) {
          if (k.indexOf('filter_') === 0) url.searchParams.delete(k);
        });
        window.location.href = url.toString();
      });
    }
  });

  // ---- Mobile filter drawer -----------------------------------------------
  ready(function () {
    var openBtn = document.querySelector('[data-ftgs-open-filter]');
    var sidebar = document.getElementById('ftgs-shop-sidebar');
    if (!openBtn || !sidebar) return;

    function open() {
      sidebar.classList.add('ftgs-drawer-open');
      document.body.classList.add('ftgs-drawer-active');
      var overlay = document.createElement('div');
      overlay.className = 'ftgs-drawer-overlay';
      overlay.addEventListener('click', close);
      document.body.appendChild(overlay);
    }
    function close() {
      sidebar.classList.remove('ftgs-drawer-open');
      document.body.classList.remove('ftgs-drawer-active');
      var overlay = document.querySelector('.ftgs-drawer-overlay');
      if (overlay) overlay.remove();
    }

    openBtn.addEventListener('click', open);

    // Add a close affordance inside the sidebar.
    var closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'ftgs-drawer-close';
    closeBtn.setAttribute('aria-label', 'Close filters');
    closeBtn.innerHTML = '&times;';
    closeBtn.addEventListener('click', close);
    sidebar.insertBefore(closeBtn, sidebar.firstChild);

    // ESC closes.
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && sidebar.classList.contains('ftgs-drawer-open')) close();
    });
  });
})();
