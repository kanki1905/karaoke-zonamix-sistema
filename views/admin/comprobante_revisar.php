<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Revisar Comprobante — Admin</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/admin_sidebar.php'; ?>
<div class="admin-content">
  <div class="admin-topbar">
    <h4>📎 Revisar Comprobante #<?= $comp['id'] ?></h4>
    <a href="<?= BASE_URL ?>/index.php?r=admin/comprobantes" class="btn btn-outline-primary btn-sm">← Volver</a>
  </div>

  <div class="row g-4 align-items-start" style="max-width:1400px">
    <!-- Imagen del comprobante -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between gap-2" style="background:var(--primary);color:var(--on-primary);font-weight:700">
          <span>📎 Comprobante enviado</span>
          <a href="<?= BASE_URL ?>/index.php?r=admin/descargar-comprobante&id=<?= $comp['id'] ?>"
             class="btn btn-sm btn-download-comprobante">
            ⬇️ Descargar
          </a>
        </div>
        <div class="card-body text-center">
          <?php
            $ext = pathinfo($comp['archivo'], PATHINFO_EXTENSION);
            $url = UPLOAD_URL . '/comprobantes/' . $comp['archivo'];
          ?>
          <?php if (in_array(strtolower($ext), ['jpg','jpeg','png'])): ?>
            <img src="<?= $url ?>" class="img-fluid rounded" style="max-height:420px;cursor:zoom-in"
                 onclick="window.open(this.src,'_blank')">
            <div class="form-text mt-1">Clic en la imagen para ampliar</div>
          <?php else: ?>
            <a href="<?= $url ?>" target="_blank" class="btn btn-outline-primary">
              📄 Ver PDF en nueva pestaña
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <!-- Datos de la reserva -->
      <div class="card mb-3">
        <div class="card-header" style="background:var(--primary);color:var(--on-primary);font-weight:700">📋 Datos de la reserva</div>
        <div class="card-body" style="font-size:.9rem">
          <table style="width:100%;border-collapse:collapse">
            <tr><td class="text-muted pe-3 pb-1">Cliente</td>
                <td class="fw-semibold pb-1"><?= htmlspecialchars($comp['usuario_nombre']) ?></td></tr>
            <tr><td class="text-muted pe-3 pb-1">Correo</td>
                <td class="pb-1"><?= htmlspecialchars($comp['email'] ?? '—') ?></td></tr>
            <tr><td class="text-muted pe-3 pb-1">Sala</td>
                <td class="pb-1"><?= htmlspecialchars($comp['sala_nombre']) ?></td></tr>
            <tr><td class="text-muted pe-3 pb-1">Fecha</td>
                <td class="pb-1"><?= date('d/m/Y', strtotime($comp['fecha'])) ?></td></tr>
            <tr><td class="text-muted pe-3 pb-1">Horario</td>
                <td class="pb-1"><?= substr($comp['hora_inicio'],0,5) ?> – <?= substr($comp['hora_fin'],0,5) ?></td></tr>
            <tr><td class="text-muted pe-3 pb-1">Total reserva</td>
                <td class="fw-bold text-brand pb-1"><?= precio((int)$comp['total']) ?></td></tr>
            <tr><td class="text-muted pe-3">Estado comp.</td>
                <td><span class="badge-estado estado-<?= $comp['estado'] ?>"><?= ucfirst($comp['estado']) ?></span></td></tr>
          </table>
        </div>
      </div>

      <!-- Decisión + mensaje al cliente -->
      <div class="card">
        <div class="card-header" style="background:var(--primary);color:var(--on-primary);font-weight:700">⚖️ Aprobar o rechazar</div>
        <div class="card-body">
          <?php if (!empty($comp['notas'])): ?>
          <div class="alert alert-secondary py-2 mb-3" style="font-size:.83rem">
            <strong>Mensaje anterior enviado al cliente:</strong><br>
            <?= nl2br(htmlspecialchars($comp['notas'])) ?>
          </div>
          <?php endif; ?>

          <form action="<?= BASE_URL ?>/index.php?r=admin/comprobante-accion" method="POST">
        <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= $comp['id'] ?>">
            <div class="mb-3">
              <label class="form-label fw-semibold">
                💬 Mensaje para el cliente
                <small class="fw-normal text-muted">(el cliente verá este mensaje)</small>
              </label>
              <textarea name="notas" class="form-control" rows="3"><?= htmlspecialchars($comp['notas'] ?? '') ?></textarea>
            </div>
            <div class="d-flex gap-2">
              <button type="submit" name="accion" value="aprobar"
                      class="btn btn-success flex-fill"
                      onclick="return confirm('¿Confirmar el pago y la reserva?')">
                ✔ Confirmar
              </button>
              <button type="submit" name="accion" value="rechazar"
                      class="btn btn-danger flex-fill"
                      onclick="return confirm('¿Rechazar el comprobante?')">
                ✖ Rechazar
              </button>
            </div>
          </form>
          <a class="btn btn-outline-primary w-100 mt-3" href="<?= BASE_URL ?>/index.php?r=admin/chat-ver&usuario_id=<?= (int)($comp['usuario_id'] ?? 0) ?>">💬 Escribir a este cliente por chat</a>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
