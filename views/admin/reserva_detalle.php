<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reserva #<?= $res['id'] ?> — Admin</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
  <style>
    .comp-img { max-height:460px; width:auto; max-width:100%; border-radius:10px; cursor:zoom-in; box-shadow:0 4px 16px var(--shadow-color); }
    .chat-admin-prev { background:var(--bg-secondary); border-left:4px solid var(--primary);
      border-radius:0 10px 10px 0; padding:.8rem 1rem; font-size:.86rem; }
    .info-row td:first-child { color:var(--text-muted); white-space:nowrap; padding-right:1rem; padding-bottom:.5rem; font-size:.87rem; }
    .info-row td:last-child  { font-weight:600; padding-bottom:.65rem; font-size:.9rem; }
    .panel-title { background:var(--primary);color:var(--on-primary);border-radius:12px 12px 0 0;padding:.85rem 1rem;font-weight:750; }
    .admin-content .card { overflow:hidden; border-radius:14px; }
    @media(max-width:767px){.comp-img{max-height:320px}.admin-topbar{gap:.5rem;flex-wrap:wrap}}
  </style>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/admin_sidebar.php'; ?>
<div class="admin-content">

  <div class="admin-topbar">
    <h4>📋 Reserva #<?= $res['id'] ?></h4>
    <a href="<?= BASE_URL ?>/index.php?r=admin/reservas" class="btn btn-outline-primary btn-sm">← Volver</a>
  </div>

  <?php if ($ok = flash('success')): ?>
    <div class="alert alert-success"><?= htmlspecialchars($ok) ?></div>
  <?php endif; ?>
  <?php if ($err = flash('error')): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <div class="row g-4 align-items-start" style="max-width:1400px">

    <!-- Panel izquierdo: únicamente los datos de la reserva y del cliente -->
    <div class="col-md-4">
      <div class="card mb-3">
        <div class="panel-title">👤 Datos del cliente</div>
        <div class="card-body">
          <table class="info-row" style="width:100%">
            <tr><td>Nombre</td><td><?= htmlspecialchars($res['usuario_nombre']) ?></td></tr>
            <tr><td>Correo</td><td style="word-break:break-all"><?= htmlspecialchars($res['usuario_email']) ?></td></tr>
            <tr><td>Teléfono</td><td><?= htmlspecialchars($res['usuario_telefono'] ?: '—') ?></td></tr>
          </table>
        </div>
      </div>

      <div class="card mb-3">
        <div class="panel-title">📅 Datos de la reserva</div>
        <div class="card-body">
          <table class="info-row" style="width:100%">
            <tr><td>Sala</td><td><?= htmlspecialchars($res['sala_nombre']) ?></td></tr>
            <tr><td>Fecha</td><td><?= fechaES($res['fecha']) ?></td></tr>
            <tr><td>Horario</td><td><?= substr($res['hora_inicio'],0,5) ?> – <?= substr($res['hora_fin'],0,5) ?></td></tr>
            <tr><td>Duración</td><td><?= $res['horas'] ?> hora<?= $res['horas']>1?'s':'' ?></td></tr>
            <tr><td>Total</td><td class="text-brand"><?= precio((int)$res['total']) ?></td></tr>
            <tr><td>Método</td><td><?= ucfirst($res['metodo_pago']) ?></td></tr>
            <tr><td>Plan elegido</td><td><?php if (($res['porcentaje_pago'] ?? null) === null): ?>Aún sin elegir<?php else: ?><?= (int)$res['porcentaje_pago'] ?> % (<?= precio((int)ceil((int)$res['total'] * (int)$res['porcentaje_pago'] / 100)) ?>)<?php endif; ?></td></tr>
            <tr><td>Estado</td><td><span class="badge-estado estado-<?= $res['estado'] ?>"><?= ucfirst($res['estado']) ?></span></td></tr>
          </table>
        </div>
      </div>

      <div class="card mb-3">
        <div class="panel-title">💵 Registro real de pago</div>
        <div class="card-body">
          <p class="small text-muted">Registra el monto acumulado que el local realmente recibió. Para un pago a la mitad, escribe ese monto; cuando el cliente complete el saldo, actualiza el monto acumulado hasta el total.</p>
          <div class="d-flex justify-content-between mb-2"><span>Total de la reserva</span><strong><?= precio((int)$res['total']) ?></strong></div>
          <div class="d-flex justify-content-between mb-3"><span>Pagado registrado</span><strong class="text-success"><?= precio((int)($res['monto_pagado'] ?? 0)) ?></strong></div>
          <?php if ($res['estado'] === 'cancelada'): ?>
            <div class="alert alert-warning mb-0">No se puede modificar el pago de una reserva cancelada desde esta pantalla.</div>
          <?php else: ?>
            <form method="post" action="<?= BASE_URL ?>/index.php?r=admin/registrar-pago" class="d-grid gap-2">
              <?= csrfField() ?>
              <input type="hidden" name="id" value="<?= (int)$res['id'] ?>">
              <label class="form-label mb-0" for="monto_pagado">Monto acumulado recibido (Bs)</label>
              <input class="form-control" id="monto_pagado" name="monto_pagado" type="number" min="0" max="<?= (int)$res['total'] ?>" step="1" value="<?= (int)($res['monto_pagado'] ?? 0) ?>" required>
              <button class="btn btn-success" type="submit">Guardar pago registrado</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
      <a class="btn btn-primary w-100 mt-2" href="<?= BASE_URL ?>/index.php?r=admin/chat-ver&usuario_id=<?= (int)$res['usuario_id'] ?>">💬 Escribir al cliente por chat</a>
    </div>

    <!-- Panel derecho: comprobante y decisión -->
    <div class="col-md-8">
      <?php if (!$comp): ?>
        <div class="card">
          <div class="card-body text-center py-5 text-muted">
            <div style="font-size:3rem">📭</div>
            <p class="mt-3 mb-0">
              <?= $res['metodo_pago']==='transferencia'
                  ? 'El cliente aún no ha subido el comprobante.'
                  : 'Esta reserva es por ' . $res['metodo_pago'] . '; no requiere comprobante.' ?>
            </p>
          </div>
        </div>
      <?php else: ?>

        <?php
          $ext    = pathinfo($comp['archivo'], PATHINFO_EXTENSION);
          $urlImg = UPLOAD_URL . '/comprobantes/' . $comp['archivo'];
        ?>

        <div class="row g-3">
          <!-- Imagen del comprobante -->
          <div class="col-md-6">
            <div class="card h-100">
              <div class="panel-title d-flex align-items-center justify-content-between gap-2">
                <span>📎 Comprobante enviado</span>
                <a href="<?= BASE_URL ?>/index.php?r=admin/descargar-comprobante&id=<?= $comp['id'] ?>"
                   class="btn btn-sm btn-download-comprobante">⬇️ Descargar</a>
              </div>
              <div class="card-body text-center d-flex flex-column align-items-center justify-content-center">
                <?php if (in_array(strtolower($ext), ['jpg','jpeg','png'])): ?>
                  <img src="<?= $urlImg ?>" class="comp-img img-fluid"
                       onclick="window.open(this.src,'_blank')" title="Clic para ampliar">
                  <div class="form-text mt-2">Clic para ampliar en nueva pestaña</div>
                <?php else: ?>
                  <div style="font-size:3rem;margin-bottom:1rem">📄</div>
                  <a href="<?= $urlImg ?>" target="_blank" class="btn btn-outline-primary">Ver PDF</a>
                <?php endif; ?>

                <div class="mt-3 w-100">
                  <span class="badge-estado estado-<?= $comp['estado'] ?>" style="font-size:.83rem">
                    Estado: <?= ucfirst($comp['estado']) ?>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Decisión -->
          <div class="col-md-6">
            <div class="card h-100">
              <div class="panel-title">⚖️ Aprobar o rechazar</div>
              <div class="card-body">

                <form action="<?= BASE_URL ?>/index.php?r=admin/comprobante-accion" method="POST">
        <?= csrfField() ?>
                  <input type="hidden" name="id" value="<?= $comp['id'] ?>">
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

              </div>
            </div>
          </div>
        </div><!-- /row -->

      <?php endif; ?>
    </div><!-- /col-md-8 -->
  </div><!-- /row -->
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
