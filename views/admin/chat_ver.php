<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Chat — <?= htmlspecialchars($usuario['nombre']) ?> — Admin</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
</head>
<?php $embed = !empty($_GET['embed']); ?>
<body<?= $embed ? ' class="is-embed"' : '' ?>>
<?php if (!$embed) require ROOT_PATH . '/views/partials/admin_sidebar.php'; ?>
<div class="admin-content">

  <!-- Encabezado -->
  <div class="admin-topbar d-flex align-items-center gap-3">
    <?php if (!$embed): ?><a href="<?= BASE_URL ?>/index.php?r=admin/chats" class="btn btn-sm btn-outline-secondary">← Volver</a><?php endif; ?>
    <div>
      <h4 class="mb-0">💬 Chat con <?= htmlspecialchars($usuario['nombre'] . ' ' . ($usuario['apellidos'] ?? '')) ?></h4>
      <small class="text-muted"><?= htmlspecialchars($usuario['email']) ?></small>
    </div>
  </div>

  <?php $chatError = flash('error'); if ($chatError !== ''): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($chatError, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <div id="chatStatus" class="alert d-none" role="status" aria-live="polite"></div>
  <div class="chat-wrap">
    <!-- Caja de mensajes -->
    <div class="chat-box chat--admin mb-3" id="chatBox">
      <?php if (empty($mensajes)): ?>
        <div class="text-center text-muted my-auto" data-chat-empty style="font-size:.88rem">Sin mensajes aún.</div>
      <?php else: ?>
        <?php foreach ($mensajes as $m): ?>
          <div class="d-flex flex-column <?= $m['remitente'] === 'admin' ? 'align-items-end' : 'align-items-start' ?>">
            <div class="bubble bubble-<?= $m['remitente'] ?>">
              <?= nl2br(htmlspecialchars($m['contenido'])) ?>
            </div>
            <div class="bubble-meta">
              <?= $m['remitente'] === 'admin' ? 'Tú · ' : htmlspecialchars($usuario['nombre']) . ' · ' ?>
              <?= date('d/m H:i', strtotime($m['created_at'])) ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Formulario de respuesta -->
    <form onsubmit="return false" action="<?= BASE_URL ?>/index.php?r=admin/chat-responder" method="POST"
          id="frmResp" class="d-flex gap-2">
        <?= csrfField() ?>
      <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
      <textarea name="contenido" id="txtResp" class="form-control" rows="2" maxlength="1000"
                placeholder="Escribe tu respuesta…" required
                style="resize:none;border-radius:10px"></textarea>
      <button type="submit" class="btn btn-primary" style="align-self:flex-end;border-radius:10px;padding:.55rem 1.1rem">
        Enviar
      </button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?= inlineJs('chat-realtime.js') ?>
<script>
(function(){
  const BASE   = '<?= BASE_URL ?>';
  const UID    = <?= (int)$usuario['id'] ?>;
  const lastId = <?= (int)($lastId ?? 0) ?>;
  const box    = document.getElementById('chatBox');
  const statusBox = document.getElementById('chatStatus');
  function showChatStatus(message, ok=false) {
    if (!statusBox) return;
    statusBox.textContent = message;
    statusBox.className = 'alert ' + (ok ? 'alert-success' : 'alert-danger');
  }

  const uNom   = <?= json_encode((string)$usuario['nombre'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

  function scrollBottom(){ box.scrollTop = box.scrollHeight; }
  scrollBottom();

  // Tiempo real: polling cada 3 s (10 s con la pestaña oculta). Marca leído solo con el chat visible.
  window.KZM_CHAT_ACTIVE = String(UID);   // este chat no cuenta ni suena mientras la pestaña esté visible
  const rt = KzmChatRealtime.start({
    box, statusEl: statusBox, lastId,
    onNew: () => { try { const n = window.KzmNotify || (window.parent !== window && window.parent.KzmNotify); n && n.pollNow(); } catch (e) {} },   // refresca el contador (también desde el iframe del panel)
    url: (last, leer) => `${BASE}/index.php?r=admin/chat-poll&usuario_id=${UID}&last=${last}${leer ? '&read=1' : ''}`,
    render: m => KzmChatRealtime.bubble(m, 'admin', (m.remitente === 'admin' ? 'Tú' : uNom) + ' · ')
  });

  // Envío AJAX: el servidor confirma que se guardó en MySQL.
  document.getElementById('frmResp').addEventListener('submit', async function(e){
    e.preventDefault();
    const txt = document.getElementById('txtResp');
    const btn = this.querySelector('button[type=submit]');
    const val = txt.value.trim();
    if (!val) { txt.focus(); return; }
    btn.disabled = true;
    try {
      const res = await fetch(`${BASE}/index.php?r=admin/chat-responder`, {
        method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        credentials:'same-origin', cache:'no-store',
        body:new URLSearchParams({usuario_id:String(UID), contenido:val, _csrf_token:this.querySelector('[name="_csrf_token"]').value})
      });
      const data = await res.json();
      if (!res.ok || !data.ok) throw new Error(data.error || 'No se pudo guardar la respuesta.');
      rt.push(data.mensajes);
      txt.value = ''; txt.focus();
    } catch(err) { alert(err.message || 'No se pudo enviar la respuesta. Recarga la página e inténtalo otra vez.'); }
    finally { btn.disabled = false; }
  });
})();
</script>
</body>
</html>
