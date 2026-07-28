/* ==========================================================================
   ProductPro NOIR — interactions
   - Custom magnetic cursor
   - Full-screen immersive mega menu (depth-of-field + panel switching)
   - Horizontal-scroll pinned section
   - Line-mask reveals, fade reveals, counters, marquee, header scroll
   ========================================================================== */
(function () {
  'use strict';

  /* ---------- Screen loader ----------
   * Simple full-screen overlay; hidden on DOMContentLoaded (fast) with a 2s
   * failsafe. Loader starts GREEN and fades to the black site bg on hide.
   */
  var ftgsLoader = document.getElementById('ftgs-loader');
  if (ftgsLoader) {
    var ftgsLoaderDone = function () {
      ftgsLoader.classList.add('is-done');
      setTimeout(function () { if (ftgsLoader.parentNode) ftgsLoader.parentNode.removeChild(ftgsLoader); }, 350);
    };
    // Hide as soon as the DOM is interactive (don't wait for all images).
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', ftgsLoaderDone);
    } else {
      ftgsLoaderDone();
    }
    setTimeout(ftgsLoaderDone, 2000); // failsafe
  }

  /* ---------- Custom cursor (DISABLED per request) ---------- */
  // Cursor effect removed — using native cursor instead.
  // Magnetic button effect also removed.

  /* ---------- Ambient neutral smoke haze ---------- */
  if (!document.querySelector('.ambient')) {
    const a = document.createElement('div');
    a.className = 'ambient';
    a.innerHTML = '<div class="smoke s1"></div><div class="smoke s2"></div><div class="smoke s3"></div><div class="smoke s4"></div>';
    document.body.appendChild(a);
  }
  if (!document.querySelector('.grain')) {
    const g = document.createElement('div');
    g.className = 'grain';
    document.body.appendChild(g);
  }

  /* ---------- Header scroll ---------- */
  const header = document.querySelector('header.site');
  if (header) {
    const onScroll = function () { header.classList.toggle('scrolled', window.scrollY > 30); };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* ---------- Simple dropdown menu (bulletproof hover) ---------- */
  const triggers = Array.from(document.querySelectorAll('.nav-item.has-mega'));
  const panels = Array.from(document.querySelectorAll('.mega-panel'));
  const backdrop = document.querySelector('.mega-backdrop');
  const mega = document.querySelector('.mega');
  const siteHeader = document.querySelector('header.site');
  let closeTimer = null;

  function clearClose() { if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; } }
  function scheduleClose() { clearClose(); closeTimer = setTimeout(closeMenu, 1000); }

  function openMenu(menuId) {
    clearClose();
    document.body.classList.add('menu-open');
    triggers.forEach(function (t) { t.classList.toggle('is-open', t.getAttribute('data-menu') === menuId); });
    panels.forEach(function (p) {
      var match = (p.getAttribute('data-panel') === menuId);
      p.classList.toggle('active', match);
      p.style.display = match ? 'block' : 'none';
    });
  }
  function closeMenu() {
    clearClose();
    document.body.classList.remove('menu-open');
    triggers.forEach(function (t) { t.classList.remove('is-open'); });
    panels.forEach(function (p) { p.classList.remove('active'); });
  }

  // The trick: treat header + mega + backdrop as ONE hover zone.
  // Only schedule close when mouse leaves the ENTIRE zone. The scheduleClose
  // is cancelled by clearClose on mouseenter of ANY zone element, so moving
  // from the trigger to the mega panel (even across a gap) keeps it open.
  function bindZone(el) {
    el.addEventListener('mouseenter', clearClose);
    el.addEventListener('mouseleave', scheduleClose);
  }
  triggers.forEach(function (item) {
    const id = item.getAttribute('data-menu');
    item.addEventListener('mouseenter', function () { openMenu(id); });
    item.querySelector('.nav-link').addEventListener('click', function () {
      // Always let the link navigate to its href (Brands -> /brands/, etc).
      // Just close the mega panel so it doesn't linger during page transition.
      closeMenu();
    });
    bindZone(item);
  });
  if (mega) bindZone(mega);
  // Also bind each panel so moving within the mega keeps it open.
  panels.forEach(function (p) { bindZone(p); });
  if (backdrop) backdrop.addEventListener('click', closeMenu);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeMenu(); });

  /* ---------- Mobile menu ---------- */
  const mTrigger = document.querySelector('.menu-trigger');
  const mNav = document.querySelector('.mobile-nav');
  if (mTrigger && mNav) {
    const icon = {
      menu: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><line x1="3" y1="7" x2="21" y2="7"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="17" x2="21" y2="17"/></svg>',
      close: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"/><line x1="6" y1="18" x2="18" y2="6"/></svg>'
    };
    mTrigger.innerHTML = icon.menu;
    mTrigger.addEventListener('click', function () {
      const open = mNav.classList.toggle('is-open');
      mTrigger.innerHTML = open ? icon.close : icon.menu;
      document.body.style.overflow = open ? 'hidden' : '';
    });
    mNav.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { mNav.classList.remove('is-open'); mTrigger.innerHTML = icon.menu; document.body.style.overflow = ''; });
    });
  }

  /* ---------- Header search overlay ---------- */
  const searchTrigger = document.getElementById('header-search-trigger');
  const searchOverlay = document.getElementById('header-search-overlay');
  if (searchTrigger && searchOverlay) {
    const openSearch = function () {
      searchOverlay.hidden = false;
      document.body.style.overflow = 'hidden';
      const input = searchOverlay.querySelector('input');
      if (input) setTimeout(function () { input.focus(); }, 50);
    };
    const closeSearch = function () {
      searchOverlay.hidden = true;
      document.body.style.overflow = '';
    };
    searchTrigger.addEventListener('click', openSearch);
    searchOverlay.addEventListener('click', function (e) {
      if (e.target === searchOverlay || e.target.classList.contains('header-search-close')) closeSearch();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !searchOverlay.hidden) closeSearch();
      if (e.key === '/' && searchOverlay.hidden && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
        e.preventDefault(); openSearch();
      }
    });
  }

  /* ---------- Reveal + line-mask ---------- */
  const revealEls = document.querySelectorAll('.reveal, .line-mask');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
    }, { threshold: 0, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach(function (el) { io.observe(el); });
  } else { revealEls.forEach(function (el) { el.classList.add('in'); }); }

  /* ---------- Parallax + directional slide-in (BetterUp-style) ----------
   * Elements with [data-parallax] move at a fractional scroll speed (depth).
   * Elements with .slide-r / .slide-l slide in horizontally on reveal.
   * Parallax uses requestAnimationFrame + scroll for smooth 60fps movement.
   */
  var parallaxEls = Array.prototype.slice.call(document.querySelectorAll('[data-parallax]'));
  if (parallaxEls.length) {
    var ticking = false;
    var onScrollParallax = function () {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(function () {
        var vh = window.innerHeight;
        parallaxEls.forEach(function (el) {
          var rect = el.getBoundingClientRect();
          if (rect.bottom < 0 || rect.top > vh) return;
          var speed = parseFloat(el.getAttribute('data-parallax')) || 0.3;
          var center = rect.top + rect.height / 2;
          var offset = (center - vh / 2) * speed * -1;
          el.style.transform = 'translate3d(0,' + offset.toFixed(1) + 'px,0)';
        });
        ticking = false;
      });
    };
    window.addEventListener('scroll', onScrollParallax, { passive: true });
    onScrollParallax();
  }

  /* Directional slide-in observers (.slide-r, .slide-l) */
  var slideEls = document.querySelectorAll('.slide-r, .slide-l');
  if ('IntersectionObserver' in window && slideEls.length) {
    var sio = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('in'); sio.unobserve(en.target); } });
    }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });
    slideEls.forEach(function (el) { sio.observe(el); });
  } else {
    slideEls.forEach(function (el) { el.classList.add('in'); });
  }

  /* ---------- Horizontal scroll ---------- */
  const hSection = document.querySelector('.horiz');
  if (hSection && window.innerWidth > 1024) {
    const pin = hSection.querySelector('.horiz-pin');
    const track = hSection.querySelector('.horiz-track');
    const fill = hSection.querySelector('.horiz-progress .fill');
    const counter = hSection.querySelector('.horiz-progress .count');
    const cards = track ? track.querySelectorAll('.horiz-card').length : 0;
    let scrollWidth = 0;
    function recalc() {
      if (!track) return;
      scrollWidth = Math.max(0, track.scrollWidth - window.innerWidth);
      hSection.style.height = (window.innerHeight + scrollWidth) + 'px';
    }
    recalc();
    window.addEventListener('resize', recalc);
    window.addEventListener('scroll', function () {
      const rect = hSection.getBoundingClientRect();
      const top = rect.top;
      if (top <= 0 && top >= -scrollWidth) {
        const p = (-top) / scrollWidth;
        if (track) track.style.transform = 'translate3d(' + (-p * scrollWidth) + 'px,0,0)';
        if (fill) fill.style.width = (p * 100) + '%';
        if (counter) counter.textContent = Math.min(cards, Math.ceil(p * cards) || 1);
      } else if (top > 0 && track) {
        track.style.transform = 'translate3d(0,0,0)';
        if (fill) fill.style.width = '0%';
        if (counter) counter.textContent = '1';
      }
    }, { passive: true });
  }

  /* ---------- Counters ---------- */
  document.querySelectorAll('[data-count]').forEach(function (el) {
    const target = parseFloat(el.getAttribute('data-count'));
    const suffix = el.getAttribute('data-suffix') || '';
    const dec = (target % 1 !== 0) ? 1 : 0;
    function fmt(n) { return n >= 1000 ? Math.round(n).toLocaleString() : n.toFixed(dec); }
    if (!('IntersectionObserver' in window)) { el.textContent = fmt(target) + suffix; return; }
    const io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) {
          const dur = 1800, t0 = performance.now();
          (function step(t) {
            const p = Math.min((t - t0) / dur, 1);
            const eased = 1 - Math.pow(1 - p, 4);
            el.textContent = fmt(target * eased) + suffix;
            if (p < 1) requestAnimationFrame(step);
          })(t0);
          io.unobserve(el);
        }
      });
    }, { threshold: 0.5 });
    io.observe(el);
  });

  /* ---------- CTA background focus on scroll into view ---------- */
  const ctaBgs = document.querySelectorAll('.cta-bg');
  if ('IntersectionObserver' in window && ctaBgs.length) {
    const cio = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { en.target.classList.toggle('in-view', en.isIntersecting); });
    }, { threshold: 0.35 });
    ctaBgs.forEach(function (c) { cio.observe(c); });
  }

  /* ---------- FAQ accordion ---------- */
  document.querySelectorAll('.faq-q').forEach(function (q) {
    q.addEventListener('click', function () {
      const item = q.closest('.faq-item');
      const wasOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item.open').forEach(function (i) { i.classList.remove('open'); });
      if (!wasOpen) item.classList.add('open');
    });
  });

  /* ---------- Light/dark theme toggle (respects system default) ---------- */
  const sunIcon = '<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.2" y1="4.2" x2="5.6" y2="5.6"/><line x1="18.4" y1="18.4" x2="19.8" y2="19.8"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.2" y1="19.8" x2="5.6" y2="18.4"/><line x1="18.4" y1="5.6" x2="19.8" y2="4.2"/>';
  const moonIcon = '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>';
  function applyTheme(mode) {
    const html = document.documentElement;
    const icon = document.getElementById('theme-icon');
    const label = document.getElementById('theme-label');
    if (mode === 'light') {
      html.classList.add('light');
      if (icon) icon.innerHTML = moonIcon;
      if (label) label.textContent = 'Dark';
    } else {
      html.classList.remove('light');
      if (icon) icon.innerHTML = sunIcon;
      if (label) label.textContent = 'Light';
    }
  }
  // Initial: saved pref > default dark (ignore system prefers-color-scheme)
  const saved = localStorage.getItem('sd-theme');
  applyTheme(saved || 'dark');
  // Toggle button
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('#theme-toggle');
    if (!btn) return;
    const isLight = document.documentElement.classList.contains('light');
    const next = isLight ? 'dark' : 'light';
    localStorage.setItem('sd-theme', next);
    applyTheme(next);
  });

  /* ---------- Footer year ---------- */
  document.querySelectorAll('[data-year]').forEach(function (el) { el.textContent = new Date().getFullYear(); });

  /* ---------- Product gallery: thumbnail swap + lightbox ----------
   * The single-product page uses bespoke .pg-main / .pg-thumb markup (not
   * WooCommerce's .woocommerce-product-gallery), so WC's native PhotoSwipe
   * lightbox has nothing to bind to. This module provides both behaviors:
   *   1. Clicking a thumbnail swaps the main image + marks it active.
   *   2. Clicking the main image opens a full-screen lightbox with arrow-key
   *      and arrow-button navigation across the whole gallery set.
   * Runs on DOMContentLoaded AND on window.load (belt-and-suspenders, in case
   * WooCommerce injects gallery markup late).
   */
  function initProductGallery() {
    var mainImg = document.getElementById('pg-main-img');
    var thumbs  = Array.prototype.slice.call(document.querySelectorAll('.pg-thumb'));
    if (!mainImg) return; // not a product page
    if (mainImg.dataset.sdnGallery) return; // already initialized
    mainImg.dataset.sdnGallery = '1';

    // Build the ordered gallery set from the thumbnails' data-full URLs,
    // falling back to just the main image itself if there are no thumbnails.
    var set = thumbs.map(function (t) { return t.getAttribute('data-full'); }).filter(Boolean);
    if (!set.length && mainImg.src) {
      set = [mainImg.src]; // at least the main image so the lightbox works
    }
    if (!set.length) return;
    var current = 0;

    function show(idx) {
      current = Math.max(0, Math.min(idx, set.length - 1));
      mainImg.src = set[current];
      thumbs.forEach(function (t, i) { t.classList.toggle('is-active', i === current); });
    }

    // Keep the main image + active thumbnail in sync with lightbox position.
    function syncMain() {
      mainImg.src = set[current];
      thumbs.forEach(function (t, i) { t.classList.toggle('is-active', i === current); });
    }

    // Thumbnail click -> open the lightbox directly at that image.
    thumbs.forEach(function (t, i) {
      t.addEventListener('click', function () { open(i); });
    });

    // ---- Lightbox ----
    var overlay = document.createElement('div');
    overlay.className = 'sdn-lightbox';
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-label', 'Product image');
    overlay.innerHTML =
      '<button class="lb-close" aria-label="Close">&times;</button>' +
      '<button class="lb-nav lb-prev" aria-label="Previous">&#8249;</button>' +
      '<img class="lb-img" alt="">' +
      '<button class="lb-nav lb-next" aria-label="Next">&#8250;</button>';
    document.body.appendChild(overlay);
    var lbImg = overlay.querySelector('.lb-img');

    function open(idx) {
      current = Math.max(0, Math.min(idx, set.length - 1));
      lbImg.src = set[current];
      overlay.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }
    function close() {
      overlay.classList.remove('is-open');
      document.body.style.overflow = '';
      // Sync the main image to where the user navigated in the lightbox.
      syncMain();
    }
    function step(d) {
      current = (current + d + set.length) % set.length;
      lbImg.src = set[current];
    }

    // Main image click -> open lightbox at the current image.
    mainImg.style.cursor = 'zoom-in';
    mainImg.addEventListener('click', function () { open(current); });

    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) close();
    });
    overlay.querySelector('.lb-close').addEventListener('click', close);
    overlay.querySelector('.lb-prev').addEventListener('click', function () { step(-1); });
    overlay.querySelector('.lb-next').addEventListener('click', function () { step(1); });
    document.addEventListener('keydown', function (e) {
      if (!overlay.classList.contains('is-open')) return;
      if (e.key === 'Escape') close();
      else if (e.key === 'ArrowLeft') step(-1);
      else if (e.key === 'ArrowRight') step(1);
    });
  }
  initProductGallery();
  // Re-try after window.load in case WooCommerce renders the gallery late.
  if (document.readyState !== 'complete') {
    window.addEventListener('load', initProductGallery);
  }

  /* ============================================================
     Homepage hero slider — auto-rotate, dots, arrows, swipe, pause on hover.
     Markup is server-rendered by front-page.php from the ACF repeater. The
     slider only initializes when [data-ftgs-slider] is present + >1 slide.
     ============================================================ */
  (function () {
    var root = document.querySelector('[data-ftgs-slider]');
    if (!root) return;
    var slides = root.querySelectorAll('.ftgs-hero-slide');
    if (slides.length < 2) return;

    var track = root.querySelector('.ftgs-hero-track');
    var prevBtn = root.querySelector('[data-ftgs-slider-prev]');
    var nextBtn = root.querySelector('[data-ftgs-slider-next]');
    var dots = root.querySelectorAll('[data-ftgs-slider-dot]');
    var current = 0;
    var timer = null;
    var DURATION = 6000; // 6s per slide

    function show(idx) {
      if (idx < 0) idx = slides.length - 1;
      if (idx >= slides.length) idx = 0;
      current = idx;
      slides.forEach(function (s, i) { s.classList.toggle('is-active', i === idx); });
      dots.forEach(function (d, i) { d.classList.toggle('is-active', i === idx); });
    }
    function next() { show(current + 1); }
    function prev() { show(current - 1); }
    function start() { stop(); timer = setInterval(next, DURATION); }
    function stop() { if (timer) { clearInterval(timer); timer = null; } }

    if (nextBtn) nextBtn.addEventListener('click', function () { next(); start(); });
    if (prevBtn) prevBtn.addEventListener('click', function () { prev(); start(); });
    dots.forEach(function (d) {
      d.addEventListener('click', function () {
        show(parseInt(d.getAttribute('data-ftgs-slider-dot'), 10) || 0);
        start();
      });
    });

    // Pause on hover/focus, resume on leave.
    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', start);
    root.addEventListener('focusin', stop);
    root.addEventListener('focusout', start);

    // Swipe support (touch).
    var touchX = null;
    root.addEventListener('touchstart', function (e) { touchX = e.touches[0].clientX; }, { passive: true });
    root.addEventListener('touchend', function (e) {
      if (touchX === null) return;
      var dx = e.changedTouches[0].clientX - touchX;
      if (Math.abs(dx) > 40) { dx < 0 ? next() : prev(); start(); }
      touchX = null;
    }, { passive: true });

    // Respect reduced-motion preference.
    var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!prefersReduced) start();

    // Keyboard nav when slider is focused.
    root.setAttribute('tabindex', '0');
    root.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft') { prev(); start(); }
      if (e.key === 'ArrowRight') { next(); start(); }
    });
  })();

  /* ============================================================
     Newsletter signup (AJAX). Handles every form with class
     .ftgs-news on the page (footer + homepage hero, etc.).
     Nonce is embedded in the form via wp_nonce_field() — no
     extra fetch needed.
     ============================================================ */
  (function () {
    var forms = document.querySelectorAll('form.ftgs-news');
    if (!forms.length) return;
    var ajaxUrl = (window.ftgsData && window.ftgsData.ajaxUrl) || '/wp-admin/admin-ajax.php';

    function showMsg(form, txt, ok) {
      // The matching status div sits next to each form: same id suffix.
      var msgEl = form.querySelector('.ftgs-news-msg');
      if (!msgEl) {
        var id = form.id ? form.id.replace('-form', '-msg') : '';
        msgEl = id ? document.getElementById(id) : null;
      }
      if (!msgEl) return;
      msgEl.textContent = txt || '';
      msgEl.className = 'ftgs-news-msg ' + (ok ? 'success' : 'error');
    }

    forms.forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var emailEl = form.querySelector('input[name="email"]');
        var btn = form.querySelector('button, .btn');
        if (!emailEl) return;
        var email = emailEl.value.trim();
        if (!email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
          showMsg(form, 'Please enter a valid email address.', false);
          emailEl.focus();
          return;
        }

        var nonceEl = form.querySelector('input[name="ftgs_nonce_field"]');
        var nonce = nonceEl ? nonceEl.value : '';
        var originalLabel = btn ? btn.textContent : '';

        if (btn) { btn.disabled = true; btn.textContent = 'Sending\u2026'; }
        showMsg(form, '');

        var body = new URLSearchParams();
        body.set('action', 'ftgs_newsletter_submit');
        body.set('ftgs_nonce', nonce);
        body.set('email', email);

        fetch(ajaxUrl, { method: 'POST', body: body })
          .then(function (r) { return r.json(); })
          .then(function (j) {
            if (j && j.success) {
              form.reset();
              showMsg(form, (j.data && j.data.message) ? j.data.message : 'You\u2019re subscribed!', true);
            } else {
              showMsg(form, (j && j.data && j.data.message) ? j.data.message : 'Something went wrong. Please try again.', false);
            }
          })
          .catch(function () {
            showMsg(form, 'Network error. Please try again.', false);
          })
          .finally(function () {
            if (btn) { btn.disabled = false; btn.textContent = originalLabel; }
          });
      });
    });
  })();

})();
