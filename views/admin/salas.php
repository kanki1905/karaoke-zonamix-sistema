<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Salas — Admin</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/admin_sidebar.php'; ?>
<div class="admin-content">
  <div class="admin-topbar">
    <h4>🏠 Gestión de Salas</h4>
    <a href="<?= BASE_URL ?>/index.php?r=admin/sala-form" class="btn btn-primary btn-sm">+ Nueva Sala</a>
  </div>

  <?php if ($ok = flash('success')): ?>
    <div class="alert alert-success"><?= htmlspecialchars($ok) ?></div>
  <?php endif; ?>
  <?php if ($err = flash('error')): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>
  <?php if ($warn = flash('warning')): ?>
    <div class="alert alert-warning" role="alert">⚠️ <?= htmlspecialchars($warn) ?></div>
  <?php endif; ?>

  <div class="row g-4">
    <?php foreach ($salas as $s): ?>
    <div class="col-md-4 col-sm-6">
      <div class="card <?= !$s['activa'] ? 'card--inactive' : '' ?>">
        <?php if (!empty($s['imagen'])): ?>
          <img src="<?= UPLOAD_URL ?>/salas/<?= htmlspecialchars($s['imagen']) ?>"
               class="sala-admin-img" alt="<?= htmlspecialchars($s['nombre']) ?>">
        <?php else: ?>
          <div style="height:150px;background:var(--primary);
                      display:flex;align-items:center;justify-content:center;font-size:2.5rem;
                      border-radius:20px 20px 0 0">🎤</div>
        <?php endif; ?>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <h6 class="fw-bold mb-0"><?= htmlspecialchars($s['nombre']) ?></h6>
              <?php if (!empty($s['numero'])): ?>
                <span class="badge" style="background:var(--primary);border-radius:50px;font-size:.7rem">
                  Cabina #<?= (int)$s['numero'] ?>
                </span>
              <?php endif; ?>
            </div>
            <span class="status-badge <?= $s['activa'] ? 'status-badge--success' : '' ?>">
              <?= $s['activa'] ? 'Activa' : 'Inactiva' ?>
            </span>
          </div>
          <p style="font-size:.82rem;color:var(--text-muted)">👥 <?= $s['capacidad'] ?> personas</p>
          <p class="fw-bold text-brand mb-3"><?= precio((int)$s['precio_hora']) ?>/h</p>
          <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/index.php?r=admin/sala-form&id=<?= $s['id'] ?>"
               class="btn btn-sm btn-outline-primary flex-fill">✏️ Editar</a>
            <?php $reservasFuturas = (int)($reservasFuturasPorSala[(int)$s['id']] ?? 0); ?>
            <form action="<?= BASE_URL ?>/index.php?r=admin/sala-toggle" method="POST" class="d-inline" id="toggleSala<?= (int)$s['id'] ?>">
              <?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$s['id'] ?>"><input type="hidden" name="confirmar" value="0">
              <button type="submit" class="btn btn-sm btn-outline-secondary" title="<?= $s['activa'] ? 'Poner en mantenimiento' : 'Activar' ?>" onclick="return abrirConfirmacionSala(event, this, <?= $reservasFuturas ?>, <?= $s['activa'] ? 'true' : 'false' ?>, <?= htmlspecialchars(json_encode($s['nombre'], JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)"><?= $s['activa'] ? '⏸' : '▶' ?></button>
            </form>
            <form action="<?= BASE_URL ?>/index.php?r=admin/sala-eliminar" method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$s['id'] ?>"><button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar sala <?= addslashes(htmlspecialchars($s['nombre'])) ?>?')">🗑</button></form>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<div class="modal fade" id="modalAvisoSala" tabindex="-1" aria-labelledby="tituloAvisoSala" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header" style="background:var(--primary);color:var(--on-primary)">
      <h5 class="modal-title fw-bold" id="tituloAvisoSala">⚠️ Confirmar cambio de sala</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
    </div>
    <div class="modal-body"><p id="textoAvisoSala" class="mb-0"></p></div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" id="btnAceptarSala" class="btn btn-primary">Sí, continuar</button></div>
  </div></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let formularioSala = null;
function abrirConfirmacionSala(event, enlace, reservas, activa, nombre) {
  event.preventDefault(); formularioSala = enlace.form;
  const titulo = document.getElementById('tituloAvisoSala');
  const texto = document.getElementById('textoAvisoSala');
  const btnAceptar = document.getElementById('btnAceptarSala');
  if (activa) {
    formularioSala.querySelector('[name=confirmar]').value = '0';
    titulo.textContent = reservas > 0 ? '⛔ No se puede poner en mantenimiento' : '⚠️ ¿Poner la sala en mantenimiento?';
    texto.textContent = reservas > 0
      ? `La sala «${nombre}» tiene ${reservas} reserva(s) futura(s). No se puede poner en mantenimiento mientras existan esas reservas. Reprograma o cancela las reservas futuras y vuelve a intentarlo.`
      : `La sala «${nombre}» no tiene reservas futuras. Si la pones en mantenimiento no estará disponible para los clientes. ¿Seguro que deseas continuar?`;
    btnAceptar.textContent = reservas > 0 ? 'Entendido' : 'Sí, poner en mantenimiento';
    btnAceptar.disabled = reservas > 0;
  } else {
    btnAceptar.disabled = false;
    titulo.textContent = '▶ ¿Activar la sala?';
    texto.textContent = `La sala «${nombre}» volverá a estar disponible para reservas. ¿Deseas activarla?`;
    document.getElementById('btnAceptarSala').textContent = 'Sí, activar sala';
  }
  bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAvisoSala')).show();
  return false;
}
document.getElementById('btnAceptarSala').addEventListener('click', () => { if (formularioSala && !document.getElementById('btnAceptarSala').disabled) formularioSala.requestSubmit ? formularioSala.requestSubmit() : formularioSala.submit(); });
</script>
</body></html>
