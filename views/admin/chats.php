<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Chats — Admin</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/admin_sidebar.php'; ?>
<?php
  $selId = (int)($_GET['usuario_id'] ?? 0);
  if (!in_array($selId, array_map(fn($c) => (int)$c['id'], $conversaciones), true)) $selId = 0;
?>
<div class="admin-content admin-content--messenger">
  <div class="admin-topbar"><h4>💬 Chats con Clientes</h4></div>
  <div class="messenger<?= $selId ? ' is-chat-open' : '' ?>" id="messenger">
  <section class="messenger__list" aria-label="Conversaciones">

  <?php if (empty($conversaciones)): ?>
    <div class="card">
      <div class="card-body text-center py-5 text-muted">
        <div style="font-size:3rem">💬</div>
        <p class="mt-3">Ningún cliente ha iniciado un chat aún.</p>
      </div>
    </div>
  <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach ($conversaciones as $c): ?>
          <a href="<?= BASE_URL ?>/index.php?r=admin/chat-ver&usuario_id=<?= $c['id'] ?>"
             data-chat-row data-uid="<?= (int)$c['id'] ?>"
             class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 px-4 <?= (int)$c['id'] === $selId ? 'is-active' : '' ?>"<?= (int)$c['id'] === $selId ? ' aria-current="true"' : '' ?>>
            <!-- Avatar -->
            <?php
              $fotoUrl = !empty($c['foto'])
                ? UPLOAD_URL . '/perfiles/' . htmlspecialchars($c['foto'])
                : null;
              $ini = strtoupper(substr($c['nombre'], 0, 1));
            ?>
            <?php if ($fotoUrl): ?>
              <img src="<?= $fotoUrl ?>"
                   style="width:42px;height:42px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0">
            <?php else: ?>
              <span style="width:42px;height:42px;border-radius:50%;background:var(--primary);
                           display:inline-flex;align-items:center;justify-content:center;
                           color:var(--on-primary);font-weight:700;font-size:.95rem;flex-shrink:0"><?= $ini ?></span>
            <?php endif; ?>

            <!-- Info -->
            <div class="flex-grow-1 overflow-hidden">
              <div class="d-flex justify-content-between align-items-center">
                <span class="fw-semibold" style="color:var(--text-primary)">
                  <span data-chat-name><?= htmlspecialchars($c['nombre']) ?></span>
                  <span class="badge bg-danger ms-1" style="font-size:.65rem" data-chat-row-badge <?= $c['sin_leer'] > 0 ? '' : 'hidden' ?>><?= (int)$c['sin_leer'] ?></span>
                </span>
                <small class="text-muted ms-2" data-chat-time style="white-space:nowrap;font-size:.73rem">
                  <?= $c['ultimo_at'] ? date('d/m H:i', strtotime($c['ultimo_at'])) : '' ?>
                </small>
              </div>
              <div class="text-muted text-truncate" data-chat-preview style="font-size:.82rem">
                <?= htmlspecialchars(substr($c['ultimo_msg'] ?? '', 0, 80)) ?>
              </div>
              <small class="text-muted" style="font-size:.72rem"><?= htmlspecialchars($c['email']) ?></small>
            </div>
            <span style="color:var(--text-muted);font-size:1rem">›</span>
          </a>
        <?php endforeach; ?>
      </div>
  <?php endif; ?>
  </section>

  <section class="messenger__pane" aria-label="Conversación seleccionada">
    <div class="messenger__bar">
      <button type="button" class="btn btn-sm btn-outline-secondary" id="chatBack">← Volver</button>
      <span class="messenger__title" id="chatTitle"></span>
    </div>
    <div class="messenger__empty" id="chatEmpty" <?= $selId ? 'hidden' : '' ?>>
      <div class="messenger__empty-icon" aria-hidden="true">💬</div>
      <p>Selecciona un chat para ver la conversación</p>
    </div>
    <iframe id="chatFrame" class="messenger__frame" title="Conversación" <?= $selId ? 'src="' . BASE_URL . '/index.php?r=admin/chat-ver&usuario_id=' . $selId . '&embed=1"' : 'hidden' ?>></iframe>
  </section>
  </div>
</div>
<script>
// Panel de mensajería: abre la conversación a la derecha sin recargar la página.
(function(){
  const BASE = '<?= BASE_URL ?>', M = document.getElementById('messenger'), list = document.querySelector('.messenger__list');
  const frame = document.getElementById('chatFrame'), empty = document.getElementById('chatEmpty'), title = document.getElementById('chatTitle');
  const notify = () => window.KzmNotify && KzmNotify.pollNow();
  const rowOf = uid => list.querySelector('[data-chat-row][data-uid="' + uid + '"]');
  window.KZM_CHAT_ACTIVE = <?= $selId ? "'" . $selId . "'" : 'null' ?>;       // lo lee chat-notify.js (no cuenta ni suena el chat abierto)
  // location.replace no añade entradas al historial: el botón Atrás del navegador solo recorre las conversaciones.
  function load(src) {
    if ((frame.dataset.src || frame.getAttribute('src')) === src) return;
    frame.dataset.src = src;
    try { frame.contentWindow.location.replace(src); } catch (e) { frame.src = src; }
  }
  function mark(uid) {
    list.querySelectorAll('[data-chat-row]').forEach(r => {
      const on = String(uid) === r.dataset.uid;
      r.classList.toggle('is-active', on);
      on ? r.setAttribute('aria-current', 'true') : r.removeAttribute('aria-current');
    });
  }
  function open(uid, push) {
    const row = rowOf(uid); if (!row) return;
    mark(uid); window.KZM_CHAT_ACTIVE = String(uid);
    title.textContent = row.querySelector('[data-chat-name]').textContent.trim();
    load(BASE + '/index.php?r=admin/chat-ver&usuario_id=' + uid + '&embed=1');
    frame.hidden = false; empty.hidden = true; M.classList.add('is-chat-open');
    row.querySelector('[data-chat-row-badge]').hidden = true;
    if (push) history.pushState({ uid: uid }, '', BASE + '/index.php?r=admin/chats&usuario_id=' + uid);
    notify();
  }
  function close(push) {
    mark(0); window.KZM_CHAT_ACTIVE = null;
    frame.hidden = true; load('about:blank'); empty.hidden = false; M.classList.remove('is-chat-open');
    if (push) history.pushState({}, '', BASE + '/index.php?r=admin/chats');
    notify();
  }
  if (list) list.addEventListener('click', e => {
    const row = e.target.closest('[data-chat-row]');
    if (!row || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;   // ctrl/clic central siguen abriendo en pestaña nueva
    e.preventDefault(); open(row.dataset.uid, true);
  });
  document.getElementById('chatBack').addEventListener('click', () => close(true));
  frame.addEventListener('load', notify);
  window.addEventListener('popstate', () => { const id = new URL(location.href).searchParams.get('usuario_id'); id && rowOf(id) ? open(id, false) : close(false); });
  const t0 = rowOf(window.KZM_CHAT_ACTIVE); if (t0) title.textContent = t0.querySelector('[data-chat-name]').textContent.trim();
})();
</script>
<script>
// Tiempo real de la lista: reutiliza el resumen que consulta chat-notify.js (evento kzm:unread).
(function(){
  const list = document.querySelector('.list-group');
  const pad = n => String(n).padStart(2, '0');
  let first = true;
  document.addEventListener('kzm:unread', e => {
    const convs = e.detail.convs, map = new Map(convs.map(c => [String(c.usuario_id), c]));
    if (list) list.querySelectorAll('[data-chat-row]').forEach(row => {
      const c = map.get(row.dataset.uid), badge = row.querySelector('[data-chat-row-badge]');
      const leyendo = window.KzmNotify ? KzmNotify.reading() : document.visibilityState === 'visible';
      const abierta = leyendo && String(window.KZM_CHAT_ACTIVE) === row.dataset.uid;   // el chat abierto se lee solo
      const n = c && !abierta ? Number(c.sin_leer) : 0;
      badge.textContent = n; badge.hidden = n === 0;
      if (c && !first && Number(c.ultimo_id) > Number(row.dataset.lastId || 0)) {
        row.querySelector('[data-chat-preview]').textContent = String(c.ultimo_msg || '').slice(0, 80);
        const d = new Date(String(c.ultimo_at).replace(' ', 'T'));
        row.querySelector('[data-chat-time]').textContent = pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
        list.prepend(row);
      }
      if (c) row.dataset.lastId = c.ultimo_id;
    });
    first = false;
    // Cliente que escribe por primera vez: aún no hay fila → recargar la lista una vez por mensaje.
    convs.forEach(c => {
      if (document.querySelector('[data-chat-row][data-uid="' + c.usuario_id + '"]')) return;
      const k = 'kzm-chats-reload';
      if (sessionStorage.getItem(k) !== String(c.ultimo_id)) { sessionStorage.setItem(k, String(c.ultimo_id)); location.reload(); }
    });
  });
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
