/* KZM — indicador de chats sin leer (punto rojo / contador) y sonido "tic".
   Carga en el nav del cliente y en el sidebar admin. Lee su configuración de
   [data-chat-notify="cliente|admin"][data-url]. Elementos que actualiza:
     [data-chat-badge]  burbuja roja con el nº de MENSAJES sin leer (cliente y admin)
     [data-chat-sr]     texto para lectores de pantalla
   El chat abierto declara window.KZM_CHAT_ACTIVE = 'self' (cliente) o id de usuario (admin):
   mientras esa pestaña esté visible, ese chat no cuenta ni suena.
   Emite document 'kzm:unread' {role, convs, total, fresh} en cada consulta.
   API: KzmNotify.pollNow() */
(function () {
  'use strict';
  var cfgEl = document.querySelector('[data-chat-notify]');
  if (!cfgEl || window.KzmNotify) return;

  var role = cfgEl.getAttribute('data-chat-notify'), url = cfgEl.getAttribute('data-url');
  var VISIBLE_MS = 5000, HIDDEN_MS = 15000, TIMEOUT_MS = 8000, MAX_MS = 60000;
  var timer = null, inflight = false, fails = 0, stopped = false, baseline = false, seen = {};
  var lastHttp = '—', lastInfo = 'aún sin consultar', lastError = '—', lastAt = '—';
  var noop = function () {};

  /* ── Sonido: Web Audio, se desbloquea con la primera interacción ── */
  var ctx = null;
  function unlock() {
    try {
      if (!ctx) { var AC = window.AudioContext || window.webkitAudioContext; if (!AC) return; ctx = new AC(); }
      if (ctx.state === 'suspended') ctx.resume().catch(noop);
    } catch (e) { ctx = null; }
  }
  ['pointerdown', 'keydown'].forEach(function (ev) { document.addEventListener(ev, unlock, { passive: true }); });

  function tic() {
    if (!ctx || ctx.state !== 'running') return;                 /* sin desbloquear: silencio, sin error */
    try {                                                        /* varias pestañas abiertas: solo suena una */
      var now = Date.now(), prev = Number(localStorage.getItem('kzm-tic')) || 0;
      if (now - prev < 1500) return;
      localStorage.setItem('kzm-tic', String(now));
    } catch (e) { /* localStorage no disponible */ }
    var t = ctx.currentTime, o = ctx.createOscillator(), g = ctx.createGain();
    o.type = 'sine';
    o.frequency.setValueAtTime(1300, t); o.frequency.exponentialRampToValueAtTime(850, t + 0.08);
    g.gain.setValueAtTime(0.0001, t); g.gain.exponentialRampToValueAtTime(0.2, t + 0.01); g.gain.exponentialRampToValueAtTime(0.0001, t + 0.08);
    o.connect(g); g.connect(ctx.destination); o.start(t); o.stop(t + 0.09);
  }

  /* ── Estado ── */
  function visible() { return document.visibilityState === 'visible'; }
  function focused() { try { return (window.top || window).document.hasFocus(); } catch (e) { return document.hasFocus(); } }
  function reading() { return visible() && focused(); }          /* leyendo = visible y con foco */
  function isActive(key) { var a = window.KZM_CHAT_ACTIVE; return a != null && String(a) === key && reading(); }
  function warn(msg, extra) { if (window.console) console.warn('[KZM chat] ' + msg, extra || ''); }

  function normalize(d) {
    if (role === 'cliente') return d.sin_leer > 0 ? [{ key: 'self', usuario_id: 0, sin_leer: d.sin_leer, ultimo_id: d.ultimo_id }] : [];
    return (d.convs || []).map(function (c) { c.key = String(c.usuario_id); return c; });
  }

  function render(list) {
    var n = list.reduce(function (sum, c) { return sum + (Number(c.sin_leer) || 0); }, 0);   /* mensajes, no chats */
    var sr = document.querySelector('[data-chat-sr]');
    document.querySelectorAll('[data-chat-badge]').forEach(function (badge) { badge.textContent = n > 99 ? '99+' : String(n); badge.hidden = n === 0; });
    if (sr) sr.textContent = n === 0 ? '' : n + (n === 1 ? ' mensaje sin leer' : ' mensajes sin leer');
    return n;
  }

  function process(data) {
    var convs = normalize(data), fresh = [];
    convs.forEach(function (c) {
      var prev = seen[c.key] || 0;
      if (baseline && c.ultimo_id > prev) fresh.push(c);        /* "nuevo" = id mayor que el ya visto; la 1ª consulta solo fija la base */
      if (c.ultimo_id > prev) seen[c.key] = c.ultimo_id;
    });
    baseline = true;
    var counted = convs.filter(function (c) { return !isActive(c.key); });
    var unread = render(counted);
    if (fresh.some(function (c) { return !isActive(c.key); })) tic();
    document.dispatchEvent(new CustomEvent('kzm:unread', { detail: { role: role, convs: convs, total: unread, chats: counted.length, fresh: fresh } }));
  }

  /* ── Polling ── */
  function schedule() {
    clearTimeout(timer);
    if (stopped) return;
    timer = setTimeout(poll, Math.min((visible() ? VISIBLE_MS : HIDDEN_MS) * Math.pow(2, Math.min(fails, 3)), MAX_MS));
  }
  function poll() {
    if (stopped || inflight) { schedule(); return Promise.resolve(); }
    inflight = true;
    var ctl = new AbortController(), to = setTimeout(function () { ctl.abort(); }, TIMEOUT_MS);
    return fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store', signal: ctl.signal })
      .then(function (res) {
        lastHttp = res.status + (res.redirected ? ' (redirigido)' : '');
        if (res.redirected || res.status === 401 || res.status === 403 || (res.headers.get('content-type') || '').indexOf('application/json') === -1) {
          stopped = true;
          return res.text().then(function (txt) {              /* muestra el comienzo de lo que devolvió el servidor (p. ej. un Warning de PHP) */
            var snip = txt.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 160);
            var e = new Error('la respuesta no es JSON (HTTP ' + res.status + '): ' + (snip || 'vacía') + ' — ¿sesión caducada o error de PHP?');
            warn(e.message); throw e;
          });
        }
        return res.json();
      })
      .then(function (d) {
        if (!d || !d.ok) throw new Error('el servidor respondió ok:false (revisa el log de errores de PHP)');
        fails = 0; lastError = '—'; process(d);
        lastInfo = role === 'cliente' ? 'JSON ok · sin_leer=' + d.sin_leer : 'JSON ok · conversaciones con no leídos=' + (d.convs || []).length;
      })
      .catch(function (e) { lastError = (e && e.message) || String(e); if (!stopped) { fails++; if (fails === 1) warn('no se pudo consultar los mensajes sin leer:', e && e.message); } })
      .then(function () { lastAt = new Date().toLocaleTimeString(); clearTimeout(to); inflight = false; diag(); schedule(); });
  }

  document.addEventListener('visibilitychange', function () { if (!stopped) { clearTimeout(timer); poll(); } });
  window.addEventListener('online', function () { if (!stopped) { fails = 0; clearTimeout(timer); poll(); } });
  window.addEventListener('focus', function () { if (!stopped) { clearTimeout(timer); setTimeout(poll, 700); } });   /* tras enfocar, el chat abierto ya se marcó leído */
  document.addEventListener('kzm:chatread', function () { if (!stopped) poll(); });                            /* el chat abierto acaba de marcar leído */

  /* ── Diagnóstico: añade ?kzmdiag=1 a cualquier URL para ver el estado real (?kzmdiag=0 lo quita) ── */
  var panel = null, pre = null;
  function diagOn() {
    try {
      if (/[?&]kzmdiag=1\b/.test(location.search)) sessionStorage.setItem('kzm-diag', '1');
      if (/[?&]kzmdiag=0\b/.test(location.search)) sessionStorage.removeItem('kzm-diag');
      return sessionStorage.getItem('kzm-diag') === '1';
    } catch (e) { return /[?&]kzmdiag=1\b/.test(location.search); }
  }
  function diag() {
    if (!diagOn()) return;
    if (!panel) {
      panel = document.createElement('div'); panel.className = 'kzm-diag'; panel.setAttribute('role', 'status');
      var h = document.createElement('strong'); h.textContent = 'KZM chat · diagnóstico';
      pre = document.createElement('pre');
      var test = document.createElement('button'); test.type = 'button'; test.className = 'btn btn-sm btn-primary'; test.textContent = 'Probar burbuja y sonido';
      test.addEventListener('click', function () { render([{ sin_leer: 3 }]); tic(); });
      var close = document.createElement('a'); close.href = location.pathname + location.search.replace(/[?&]kzmdiag=1/, '') + (location.search ? '&' : '?') + 'kzmdiag=0'; close.textContent = 'Cerrar'; close.className = 'ms-2';
      panel.appendChild(h); panel.appendChild(pre); panel.appendChild(test); panel.appendChild(close);
      document.body.appendChild(panel);
    }
    pre.textContent = [
      'script: cargado ✓  ·  rol: ' + role,
      'burbujas en la página: ' + document.querySelectorAll('[data-chat-badge]').length,
      'URL: ' + url,
      'última consulta: ' + lastAt + '  →  HTTP ' + lastHttp,
      'respuesta: ' + lastInfo,
      'error: ' + lastError + (stopped ? '  (el contador se detuvo)' : ''),
      'ventana: visible=' + (visible() ? 'sí' : 'no') + '  foco=' + (focused() ? 'sí' : 'no') + '  chat abierto=' + (window.KZM_CHAT_ACTIVE == null ? 'ninguno' : window.KZM_CHAT_ACTIVE)
    ].join('\n');
  }
  document.addEventListener('visibilitychange', diag); window.addEventListener('focus', diag); window.addEventListener('blur', diag);

  window.KzmNotify = { pollNow: function () { clearTimeout(timer); return poll(); }, reading: reading };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { diag(); poll(); }, { once: true });
  else { diag(); poll(); }
})();
