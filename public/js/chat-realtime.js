/* KZM — chat en tiempo real por polling (cliente y admin).
   KzmChatRealtime.start({ box, url, lastId, render, onNew, statusEl })
     box      contenedor de mensajes (.chat-box)
     url      (lastId, leer) => URL del endpoint de poll; leer=true solo si el chat está abierto y visible
     lastId   id del último mensaje ya pintado
     render   (mensaje) => Element de la burbuja
     onNew    (mensajes[]) => hook opcional al llegar mensajes nuevos (sonido, contadores…)
     statusEl elemento .alert para avisos (opcional)
   Devuelve { push(mensajes), pollNow(), stop(), lastId() }. */
(function () {
  'use strict';
  var VISIBLE_MS = 3000, HIDDEN_MS = 10000, TIMEOUT_MS = 8000, MAX_BACKOFF_MS = 30000;

  function isVisible() { return document.visibilityState === 'visible'; }
  /* "Leyendo" = pestaña visible Y ventana con foco (como WhatsApp). En un iframe se mira la ventana superior. */
  function hasFocus() { try { return (window.top || window).document.hasFocus(); } catch (e) { return document.hasFocus(); } }
  function isReading() { return isVisible() && hasFocus(); }
  /* Avisa a chat-notify.js (también el de la página padre si estamos en un iframe) de que se acaba de marcar leído. */
  function announceRead() {
    var ev = function () { return new CustomEvent('kzm:chatread'); };
    document.dispatchEvent(ev());
    try { if (window.parent && window.parent !== window) window.parent.document.dispatchEvent(ev()); } catch (e) { /* otro origen */ }
  }

  /* Burbuja segura (sin innerHTML). own = remitente "propio" de la vista. */
  function bubble(m, own, metaPrefix) {
    var wrap = document.createElement('div');
    wrap.className = 'd-flex flex-column ' + (m.remitente === own ? 'align-items-end' : 'align-items-start');
    var b = document.createElement('div');
    b.className = 'bubble bubble-' + m.remitente;
    String(m.contenido).split('\n').forEach(function (line, i) {
      if (i) b.appendChild(document.createElement('br'));
      b.appendChild(document.createTextNode(line));
    });
    var meta = document.createElement('div');
    meta.className = 'bubble-meta';
    var d = new Date(String(m.created_at).replace(' ', 'T')), p = function (n) { return String(n).padStart(2, '0'); };
    meta.textContent = (metaPrefix || '') + p(d.getDate()) + '/' + p(d.getMonth() + 1) + ' ' + p(d.getHours()) + ':' + p(d.getMinutes());
    wrap.appendChild(b); wrap.appendChild(meta);
    return wrap;
  }

  function start(o) {
    var lastId = Number(o.lastId) || 0, timer = null, inflight = false, fails = 0, stopped = false;
    var seen = Object.create(null);

    function status(msg, ok) {
      if (!o.statusEl) return;
      if (!msg) { o.statusEl.className = 'alert d-none'; o.statusEl.textContent = ''; return; }
      o.statusEl.textContent = msg;
      o.statusEl.className = 'alert ' + (ok ? 'alert-success' : 'alert-danger');
    }

    function append(list) {
      var fresh = (list || []).filter(function (m) { return Number(m.id) > lastId && !seen[m.id]; });
      if (!fresh.length) return [];
      var empty = o.box.querySelector('[data-chat-empty]');
      if (empty) empty.remove();
      fresh.forEach(function (m) {
        seen[m.id] = true;
        o.box.appendChild(o.render(m));
        lastId = Math.max(lastId, Number(m.id));
      });
      o.box.scrollTop = o.box.scrollHeight;
      if (o.onNew) { try { o.onNew(fresh); } catch (e) { console.error('[chat] onNew', e); } }
      return fresh;
    }

    function schedule() {
      clearTimeout(timer);
      if (stopped) return;
      var base = isVisible() ? VISIBLE_MS : HIDDEN_MS;
      timer = setTimeout(tick, Math.min(base * Math.pow(2, Math.min(fails, 3)), MAX_BACKOFF_MS));
    }

    function tick() {
      if (stopped || inflight) { schedule(); return Promise.resolve(); }
      inflight = true;
      var leer = isReading();
      var ctl = new AbortController(), to = setTimeout(function () { ctl.abort(); }, TIMEOUT_MS);
      return fetch(o.url(lastId, leer), { headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store', signal: ctl.signal })
        .then(function (res) {
          var ct = res.headers.get('content-type') || '';
          if (res.redirected || res.status === 401 || res.status === 403 || ct.indexOf('application/json') === -1) {
            var err = new Error('auth'); err.auth = true; throw err;
          }
          return res.json().then(function (data) {
            if (!res.ok || !data.ok) throw new Error(data.error || 'Error al consultar el chat.');
            return data;
          });
        })
        .then(function (data) { fails = 0; status(''); append(data.mensajes); if (leer) announceRead(); })
        .catch(function (e) {
          if (e.auth) {
            stopped = true;
            status('Tu sesión expiró o se abrió otra cuenta en este navegador. Recarga la página para seguir recibiendo mensajes.');
            return;
          }
          fails++;
          if (fails >= 2) status('Sin conexión con el chat. Reintentando…');
        })
        .then(function () { clearTimeout(to); inflight = false; schedule(); });
    }

    document.addEventListener('visibilitychange', function () {
      if (isVisible() && !stopped) { clearTimeout(timer); tick(); }   /* al volver: consulta y marca leído al instante */
    });
    window.addEventListener('focus', function () { if (!stopped) { clearTimeout(timer); tick(); } });   /* al enfocar: marca leído al instante */
    window.addEventListener('online', function () { if (!stopped) { fails = 0; clearTimeout(timer); tick(); } });

    schedule();
    return {
      push: append,
      pollNow: tick,
      stop: function () { stopped = true; clearTimeout(timer); },
      lastId: function () { return lastId; }
    };
  }

  window.KzmChatRealtime = { start: start, bubble: bubble, isVisible: isVisible, isReading: isReading };
})();
