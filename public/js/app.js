/* إسناد · التدريب الميداني — تفاعلات الواجهة */
(function () {
  'use strict';
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  /* ---------- Theme toggle (دائرة بتكبر من الزر) ---------- */
  $$('[data-theme-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var root = document.documentElement;
      var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      var apply = function () { root.setAttribute('data-theme', next); try { localStorage.setItem('isnad-theme', next); } catch (e) {} };
      if (reduce || !document.startViewTransition) { apply(); return; }
      var r = btn.getBoundingClientRect(), x = r.left + r.width / 2, y = r.top + r.height / 2;
      var end = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));
      document.startViewTransition(apply).ready.then(function () {
        root.animate({ clipPath: ['circle(0px at ' + x + 'px ' + y + 'px)', 'circle(' + end + 'px at ' + x + 'px ' + y + 'px)'] },
          { duration: 650, easing: 'cubic-bezier(.65,0,.35,1)', pseudoElement: '::view-transition-new(root)' });
      });
    });
  });

  /* ---------- Reveal on scroll ---------- */
  var revealEls = $$('.reveal, [data-reveal]');
  if ('IntersectionObserver' in window && !reduce) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach(function (el) { io.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('in'); });
  }

  /* ---------- Count up ---------- */
  function countUp(el) {
    var to = parseFloat(el.getAttribute('data-count')) || 0;
    if (reduce || to === 0) { el.textContent = to; return; }
    var start = null, dur = 1200;
    function step(ts) {
      if (!start) start = ts;
      var p = Math.min(1, (ts - start) / dur), e = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(to * e);
      if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }
  var counters = $$('[data-count]');
  if ('IntersectionObserver' in window) {
    var co = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { countUp(en.target); co.unobserve(en.target); } });
    });
    counters.forEach(function (el) { co.observe(el); });
  } else { counters.forEach(countUp); }

  /* ---------- Animated bars ---------- */
  setTimeout(function () {
    $$('[data-w]').forEach(function (el) { el.style.width = el.getAttribute('data-w') + '%'; });
  }, 150);

  /* ---------- Copy buttons ---------- */
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-copy]');
    if (!b) return;
    var text = b.getAttribute('data-copy');
    var done = function () {
      var label = b.querySelector('.label') || b;
      var old = label.textContent;
      b.classList.add('done'); label.textContent = 'انتسخ ✓';
      setTimeout(function () { b.classList.remove('done'); label.textContent = old; }, 1600);
    };
    if (navigator.clipboard) navigator.clipboard.writeText(text).then(done, done);
    else { var t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select(); try { document.execCommand('copy'); } catch (er) {} t.remove(); done(); }
  });

  /* ---------- Toasts ---------- */
  $$('.toast').forEach(function (t, i) {
    var close = function () { t.classList.add('hide'); setTimeout(function () { t.remove(); }, 400); };
    var btn = t.querySelector('button'); if (btn) btn.addEventListener('click', close);
    setTimeout(close, 5500 + i * 400);
  });

  /* ---------- Loading state on submit ---------- */
  $$('form').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      if (e.defaultPrevented) return;
      var b = f.querySelector('button[type=submit].btn');
      if (b && !f.hasAttribute('data-no-loading')) { setTimeout(function () { b.classList.add('is-loading'); }, 0); }
    });
  });

  /* ---------- Dropzone preview ---------- */
  $$('.dropzone').forEach(function (dz) {
    var input = dz.querySelector('input[type=file]'), title = dz.querySelector('[data-dz-title]'), prev = dz.querySelector('img.preview');
    ['dragenter', 'dragover'].forEach(function (ev) { dz.addEventListener(ev, function () { dz.classList.add('drag'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { dz.addEventListener(ev, function () { dz.classList.remove('drag'); }); });
    input.addEventListener('change', function () {
      var f = input.files && input.files[0];
      if (!f) return;
      dz.classList.remove('has-file'); void dz.offsetWidth; dz.classList.add('has-file');
      if (title) title.textContent = f.name;
      if (prev && f.type.indexOf('image/') === 0) { prev.src = URL.createObjectURL(f); prev.hidden = false; }
    });
  });

  /* ---------- Avatar preview ---------- */
  $$('[data-avatar-input]').forEach(function (input) {
    input.addEventListener('change', function () {
      var f = input.files && input.files[0], target = $(input.getAttribute('data-avatar-input'));
      if (!f || !target) return;
      var img = target.tagName === 'IMG' ? target : null;
      if (!img) { img = document.createElement('img'); img.className = target.className; img.alt = ''; target.replaceWith(img); img.id = target.id; }
      img.src = URL.createObjectURL(f);
      var form = input.closest('form'); if (form && input.hasAttribute('data-autosubmit')) form.requestSubmit ? form.requestSubmit() : form.submit();
    });
  });

  /* ---------- Lightbox ---------- */
  var lb = $('#lightbox');
  if (lb) {
    $$('[data-lightbox]').forEach(function (a) {
      a.addEventListener('click', function (e) { e.preventDefault(); $('img', lb).src = a.getAttribute('href'); lb.classList.add('open'); $('button', lb).focus(); });
    });
    var closeLb = function () { lb.classList.remove('open'); };
    lb.addEventListener('click', function (e) { if (e.target === lb || e.target.closest('button')) closeLb(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeLb(); });
  }

  /* ---------- Countdown (ring + digits) ---------- */
  $$('[data-deadline]').forEach(function (el) {
    var end = new Date(el.getAttribute('data-deadline')).getTime();
    var total = (parseFloat(el.getAttribute('data-total-hours')) || 48) * 3600;
    var ring = $('.prog', el), main = $('.t-main', el);
    var h = $('[data-h]', el), m = $('[data-m]', el), s = $('[data-s]', el);
    var C = ring ? 2 * Math.PI * parseFloat(ring.getAttribute('r')) : 0;
    if (ring) { ring.style.strokeDasharray = C; }
    var pad = function (n) { return String(n).padStart(2, '0'); };
    var set = function (node, v) { if (node && node.textContent !== v) { node.textContent = v; if (!reduce) { node.classList.remove('tick'); void node.offsetWidth; node.classList.add('tick'); } } };
    var timer;
    function tick() {
      var left = Math.max(0, Math.floor((end - Date.now()) / 1000));
      var hh = Math.floor(left / 3600), mm = Math.floor(left % 3600 / 60), ss = left % 60;
      if (main) main.textContent = pad(hh) + ':' + pad(mm) + ':' + pad(ss);
      set(h, pad(hh)); set(m, pad(mm)); set(s, pad(ss));
      if (ring) ring.style.strokeDashoffset = C * (1 - Math.min(1, left / total));
      if (left === 0) { clearInterval(timer); setTimeout(function () { location.reload(); }, 1500); }
    }
    tick(); timer = setInterval(tick, 1000);
  });

  /* ---------- Payment: السعر بيلف زي العدّاد ---------- */
  var hourInputs = $$('input[name=hours][data-fee]');
  function rollTo(el, to) {
    var from = parseInt(el.textContent, 10);
    if (isNaN(from) || from === to || reduce) { el.textContent = to; return; }
    var start = null, dur = 650;
    el.classList.remove('bump'); void el.offsetWidth; el.classList.add('bump');
    (function step(ts) {
      if (!start) start = ts;
      var p = Math.min(1, (ts - start) / dur), e = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(from + (to - from) * e);
      if (p < 1) requestAnimationFrame(step);
    })(performance.now());
  }
  function syncAmount() {
    var h = hourInputs.filter(function (i) { return i.checked; })[0];
    if (!h) return;
    var fee = parseInt(h.getAttribute('data-fee'), 10);
    $$('[data-amount]').forEach(function (el) { rollTo(el, fee); });
  }
  hourInputs.forEach(function (i) { i.addEventListener('change', syncAmount); });
  syncAmount();

  var methodInputs = $$('input[data-method]');
  function syncMethod() {
    var on = methodInputs.filter(function (i) { return i.checked; })[0];
    $$('[data-method-panel]').forEach(function (p) { p.hidden = on ? p.getAttribute('data-method-panel') !== on.value : false; });
  }
  methodInputs.forEach(function (i) { i.addEventListener('change', syncMethod); });
  if (methodInputs.length) syncMethod();

  /* ---------- الفورم الحي: شريط التقدم + علامة ✓ لكل خانة ---------- */
  var liveForm = $('form[data-live]');
  if (liveForm) {
    var phoneRe = /^\+?[0-9\s\-]{9,20}$/;
    var okOf = function (el) {
      var v = (el.value || '').trim();
      if (el.type === 'url') return v !== '' && /^https?:\/\/\S+\.\S+/.test(v);
      if (el.name === 'phone') return phoneRe.test(v);
      return v.length >= 2;
    };
    // علامة ✓ جوّا كل خانة نص
    $$('input.input[data-ok], textarea.input[data-ok]', liveForm).forEach(function (el) {
      var wrap = el.parentElement.classList.contains('input-icon') ? el.parentElement : null;
      if (!wrap) { wrap = document.createElement('div'); wrap.className = 'ok-wrap'; el.parentNode.insertBefore(wrap, el); wrap.appendChild(el); }
      wrap.classList.add('ok-host');
      var mark = document.createElement('span'); mark.className = 'ok-mark'; mark.setAttribute('aria-hidden', 'true');
      mark.innerHTML = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';
      wrap.appendChild(mark);
      var upd = function () { wrap.classList.toggle('is-ok', okOf(el)); };
      el.addEventListener('input', upd); el.addEventListener('change', upd); upd();
    });

    // شريط التقدم: بيتعبّى مع كل إشي بيعبّيه الطالب
    var bar = document.createElement('div'); bar.className = 'top-progress'; bar.innerHTML = '<i></i>';
    document.body.appendChild(bar);
    var firstStep = $('.steps .step.now', liveForm);
    var checks = [
      function () { return okOf(liveForm.elements.name); },
      function () { return okOf(liveForm.elements.phone); },
      function () { return okOf(liveForm.elements.university); },
      function () { return okOf(liveForm.elements.major); },
      function () { return !!$('input[name=track]:checked', liveForm); },
      function () { return !!$('input[name=payment_method_id]:checked', liveForm); },
      function () { var f = liveForm.elements.receipt; return !!(f && f.files && f.files.length); },
    ];
    var updProgress = function () {
      var n = checks.filter(function (c) { try { return c(); } catch (e) { return false; } }).length, p = n / checks.length;
      $('i', bar).style.transform = 'scaleX(' + p + ')';
      bar.classList.toggle('full', p === 1);
      if (firstStep) firstStep.style.setProperty('--p', Math.max(.06, p));
    };
    liveForm.addEventListener('input', updProgress); liveForm.addEventListener('change', updProgress); updProgress();
  }

  /* ---------- بطاقات بتميل مع الماوس + ضوء بيلحق الماوس ---------- */
  var finePointer = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  if (finePointer && !reduce) {
    $$('[data-tilt]').forEach(function (el) {
      el.addEventListener('pointermove', function (e) {
        var r = el.getBoundingClientRect(), px = (e.clientX - r.left) / r.width - .5, py = (e.clientY - r.top) / r.height - .5;
        el.style.transform = 'perspective(700px) rotateX(' + (-py * 10) + 'deg) rotateY(' + (px * 12) + 'deg) translateY(-4px)';
      });
      el.addEventListener('pointerleave', function () { el.style.transform = ''; });
    });
    document.addEventListener('pointermove', function (e) {
      var c = e.target.closest && e.target.closest('.spot');
      if (!c) return;
      var r = c.getBoundingClientRect();
      c.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      c.style.setProperty('--my', (e.clientY - r.top) + 'px');
    });
  }

  /* ---------- زر الإرسال بيتحول لدائرة تحميل ---------- */
  $$('form[data-live]').forEach(function (f) {
    f.addEventListener('submit', function () {
      var b = $('button[type=submit]', f);
      if (b && !reduce) { b.style.width = b.offsetWidth + 'px'; void b.offsetWidth; b.classList.add('morph'); }
    });
  });

  /* ---------- Constellation background ---------- */
  $$('canvas[data-constellation]').forEach(function (cv) {
    var ctx = cv.getContext('2d'); if (!ctx) return;
    var pts = [], W, H, dpr = Math.min(2, window.devicePixelRatio || 1), raf, mouse = null;
    function color(v) { return getComputedStyle(document.documentElement).getPropertyValue(v).trim() || '#12285A'; }
    window.addEventListener('pointermove', function (e) { var r = cv.getBoundingClientRect(); mouse = { x: e.clientX - r.left, y: e.clientY - r.top }; }, { passive: true });
    document.addEventListener('pointerleave', function () { mouse = null; });
    function size() {
      W = cv.offsetWidth; H = cv.offsetHeight; cv.width = W * dpr; cv.height = H * dpr; ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      var n = Math.round(Math.min(70, W * H / 16000)); pts = [];
      for (var i = 0; i < n; i++) pts.push({ x: Math.random() * W, y: Math.random() * H, vx: (Math.random() - .5) * .3, vy: (Math.random() - .5) * .3, r: Math.random() * 1.8 + .8, o: Math.random() < .18 });
    }
    function frame() {
      var b = color('--brand'), a = color('--accent');
      ctx.clearRect(0, 0, W, H);
      for (var i = 0; i < pts.length; i++) {
        var p = pts[i];
        if (!reduce) {
          if (mouse) { var mx = mouse.x - p.x, my = mouse.y - p.y, md = mx * mx + my * my; if (md < 28000 && md > 400) { p.x += mx * .004; p.y += my * .004; } }
          p.x += p.vx; p.y += p.vy; if (p.x < 0 || p.x > W) p.vx *= -1; if (p.y < 0 || p.y > H) p.vy *= -1;
        }
        if (mouse && !reduce) { var cx = mouse.x - p.x, cy = mouse.y - p.y, cd = cx * cx + cy * cy; if (cd < 28000) { ctx.globalAlpha = (1 - cd / 28000) * .55; ctx.strokeStyle = a; ctx.lineWidth = .9; ctx.beginPath(); ctx.moveTo(p.x, p.y); ctx.lineTo(mouse.x, mouse.y); ctx.stroke(); } }
        for (var j = i + 1; j < pts.length; j++) {
          var q = pts[j], dx = p.x - q.x, dy = p.y - q.y, d = dx * dx + dy * dy;
          if (d < 13000) { ctx.globalAlpha = (1 - d / 13000) * .28; ctx.strokeStyle = b; ctx.lineWidth = .8; ctx.beginPath(); ctx.moveTo(p.x, p.y); ctx.lineTo(q.x, q.y); ctx.stroke(); }
        }
        ctx.globalAlpha = .85; ctx.fillStyle = p.o ? a : b; ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2); ctx.fill();
      }
      ctx.globalAlpha = 1;
      if (!reduce) raf = requestAnimationFrame(frame);
    }
    size(); frame();
    var rt; window.addEventListener('resize', function () { clearTimeout(rt); rt = setTimeout(function () { cancelAnimationFrame(raf); size(); frame(); }, 200); });
    document.addEventListener('visibilitychange', function () { if (document.hidden) cancelAnimationFrame(raf); else if (!reduce) frame(); });
  });

  /* ---------- Confetti ---------- */
  window.isnadConfetti = function () {
    if (reduce) return;
    var cv = document.createElement('canvas'); cv.style.cssText = 'position:fixed;inset:0;width:100%;height:100%;pointer-events:none;z-index:150';
    document.body.appendChild(cv);
    var ctx = cv.getContext('2d'), W = cv.width = innerWidth, H = cv.height = innerHeight;
    var cols = ['#12285A', '#F26B21', '#FFFFFF', '#F68A4C', '#2A4A8A', '#FFD3B6'], parts = [];
    for (var i = 0; i < 160; i++) parts.push({ x: W / 2 + (Math.random() - .5) * 200, y: H / 3, vx: (Math.random() - .5) * 14, vy: Math.random() * -14 - 4, s: Math.random() * 8 + 4, c: cols[i % cols.length], r: Math.random() * 6, vr: (Math.random() - .5) * .3 });
    var t = 0;
    (function f() {
      ctx.clearRect(0, 0, W, H); t++;
      parts.forEach(function (p) { p.vy += .35; p.vx *= .99; p.x += p.vx; p.y += p.vy; p.r += p.vr; ctx.save(); ctx.translate(p.x, p.y); ctx.rotate(p.r); ctx.fillStyle = p.c; ctx.globalAlpha = Math.max(0, 1 - t / 160); ctx.fillRect(-p.s / 2, -p.s / 4, p.s, p.s / 2); ctx.restore(); });
      if (t < 160) requestAnimationFrame(f); else cv.remove();
    })();
  };
  if (document.body.hasAttribute('data-confetti')) setTimeout(window.isnadConfetti, 400);
})();
