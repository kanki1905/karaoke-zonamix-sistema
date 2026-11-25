<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Chat — Karaoke Zona Mix</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/nav.php'; ?>

<div class="container" style="padding:2.5rem 0;max-width:680px">
  <h2 class="fw-bold mb-1">💬 Chat con el equipo</h2>
  <p class="text-muted mb-4" style="font-size:.88rem">
    ¿Tienes alguna duda o consulta? Escríbenos aquí.
  </p>

  <?php $chatError = flash('error'); if ($chatError !== ''): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($chatError, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <div id="chatStatus" class="alert d-none" role="status" aria-live="polite"></div>

  <!-- Caja de mensajes -->
  <div class="chat-box chat--client mb-3" id="chatBox">
    <?php if (empty($mensajes)): ?>
      <div class="text-center text-muted my-auto" data-chat-empty style="font-size:.88rem">
        Aún no hay mensajes. ¡Saluda! 👋
      </div>
    <?php else: ?>
      <?php foreach ($mensajes as $m): ?>
        <div class="d-flex flex-column <?= $m['remitente'] === 'cliente' ? 'align-items-end' : 'align-items-start' ?>">
          <div class="bubble bubble-<?= $m['remitente'] ?>">
            <?= nl2br(htmlspecialchars($m['contenido'])) ?>
          </div>
          <div class="bubble-meta">
            <?= $m['remitente'] === 'admin' ? '🎤 Admin · ' : '' ?>
            <?= date('d/m H:i', strtotime($m['created_at'])) ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Formulario -->
  <form onsubmit="return false" action="<?= BASE_URL ?>/index.php?r=client/chat-post" method="POST"
        id="frmChat" class="d-flex gap-2">
        <?= csrfField() ?>
    <textarea name="contenido" id="txtMensaje" class="form-control"
              rows="2" maxlength="1000" placeholder="Escribe tu mensaje…"
              required style="resize:none;border-radius:10px"></textarea>
    <button type="submit" class="btn btn-primary" style="align-self:flex-end;border-radius:10px;padding:.55rem 1.1rem">
      Enviar
    </button>
  </form>
  <p class="text-muted mt-2" style="font-size:.75rem">Los mensajes son respondidos por el equipo en horario de atención.</p>
</div>

<?php require ROOT_PATH . '/views/partials/footer.php'; ?>
<?= inlineJs('chat-realtime.js') ?>
<script>
(function(){
  const input = document.getElementById('txtMensaje');
  input.addEventListener('paste', e => { e.preventDefault(); const t=(e.clipboardData || window.clipboardData).getData('text/plain'); document.execCommand('insertText', false, t); });
  input.addEventListener('input', () => { if (input.value.length > 1000) input.value = input.value.slice(0,1000); });
  const BASE   = '<?= BASE_URL ?>';
  const UID    = <?= (int)$_SESSION['user']['id'] ?>;
  const lastId = <?= (int)($lastId ?? 0) ?>;
  const box    = document.getElementById('chatBox');
  const statusBox = document.getElementById('chatStatus');
  function showChatStatus(message, ok=false) {
    if (!statusBox) return;
    statusBox.textContent = message;
    statusBox.className = 'alert ' + (ok ? 'alert-success' : 'alert-danger');
  }


  function scrollBottom(){ box.scrollTop = box.scrollHeight; }
  scrollBottom();

  // Tiempo real: polling cada 3 s (10 s con la pestaña oculta). Marca leído solo con el chat visible.
  window.KZM_CHAT_ACTIVE = 'self';   // chat abierto: no cuenta ni suena mientras la pestaña esté visible
  const rt = KzmChatRealtime.start({
    box, statusEl: statusBox, lastId,
    onNew: () => window.KzmNotify && KzmNotify.pollNow(),   // refresca el punto rojo al llegar mensajes
    url: (last, leer) => `${BASE}/index.php?r=client/chat-poll&last=${last}${leer ? '&read=1' : ''}`,
    render: m => KzmChatRealtime.bubble(m, 'cliente', m.remitente === 'admin' ? '🎤 Admin · ' : '')
  });

  // Envío AJAX: el servidor devuelve el mensaje guardado en JSON.
  document.getElementById('frmChat').addEventListener('submit', async function(e){
    e.preventDefault();
    const txt = document.getElementById('txtMensaje');
    const btn = this.querySelector('button[type=submit]');
    const val = txt.value.trim();
    if (!val) { txt.focus(); return; }
    btn.disabled = true;
    try {
      const res = await fetch(`${BASE}/index.php?r=client/chat-post`, {
        method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        credentials:'same-origin', cache:'no-store',
        body:new URLSearchParams({contenido:val, _csrf_token:this.querySelector('[name="_csrf_token"]').value})
      });
      const raw = await res.text();
      let data; try { data = JSON.parse(raw); } catch (_) { throw new Error('El servidor no devolvió JSON (HTTP ' + res.status + '). Revisa sesión, token CSRF y errores PHP.'); }
      if (!res.ok || !data.ok) throw new Error(data.error || ('No se pudo guardar el mensaje (HTTP ' + res.status + ').'));
      rt.push(data.mensajes);
      txt.value = ''; txt.focus(); showChatStatus('Mensaje guardado y enviado.', true);
    } catch(err) { showChatStatus(err.message || 'No se pudo enviar el mensaje. El texto se conservó para que no lo pierdas.'); }
    finally { btn.disabled = false; }
  });
})();
</script>
</body>
</html>
