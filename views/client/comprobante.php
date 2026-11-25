<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Comprobante — Karaoke Zona Mix</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
  <style>
    /* ── Drop Zone ── */
    .drop-zone {
      border: 2px dashed var(--primary);
      border-radius: 12px;
      padding: 2rem 1rem;
      text-align: center;
      cursor: pointer;
      transition: background .2s, border-color .2s;
      background: var(--primary-soft);
      position: relative;
    }
    .drop-zone.dragover { background:var(--surface-selected); border-color:var(--primary-active); }
    .drop-zone input[type="file"] {
      position: absolute; inset: 0; opacity: 0;
      cursor: pointer; width: 100%; height: 100%;
    }
    .drop-zone .dz-icon  { font-size: 2.5rem; display: block; margin-bottom: .5rem; }
    .drop-zone .dz-text  { font-size: .88rem; color: var(--text-secondary); }
    .drop-zone .dz-fname { font-size: .85rem; font-weight: 600; color: var(--text-link); margin-top: .4rem; }

    /* ── Toggle monto ── */
    .monto-toggle {
      display: flex; align-items: center; gap: .75rem;
      background: var(--bg-secondary); border: 1px solid var(--border);
      border-radius: 12px; padding: 1rem 1.25rem;
    }
    .monto-toggle .monto-label { font-size: .88rem; color: var(--text-secondary); flex: 1; }
    .monto-toggle .monto-valor { font-size: 1.1rem; font-weight: 700; color: var(--text-link); min-width: 80px; text-align: right; }
    .qr-img { max-width: 220px; border-radius: 12px; box-shadow: 0 4px 16px var(--shadow-color); }

    /* ── Chat admin ── */
    .chat-admin {
      background: var(--bg-secondary); border-left: 4px solid var(--primary);
      border-radius: 0 10px 10px 0; padding: .9rem 1rem;
      font-size: .88rem;
    }
    .chat-admin .chat-header { font-size: .75rem; color: var(--text-link); font-weight:800; margin-bottom: .35rem; }
    .chat-admin .chat-body   { color: var(--text-primary); }

    /* ── Banner editable ── */
    .comprobante-page-title { color:var(--text-primary); letter-spacing:.1px; }
    .editar-banner {
      border: 2px dashed var(--warning-border);
      border-radius: 12px;
      padding: 1rem 1.25rem;
      background: var(--warning-bg);
      font-size: .88rem;
    }
  </style>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/nav.php'; ?>

<div class="container" style="padding:2.5rem 0;max-width:680px">

  <h2 class="fw-bold mb-4 comprobante-page-title">📎 Comprobante de Pago</h2>

  <?php if ($err = flash('error')): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>
  <?php if ($ok = flash('success')): ?>
    <div class="alert alert-success"><?= htmlspecialchars($ok) ?></div>
  <?php endif; ?>

  <!-- Detalle de la reserva -->
  <div class="card mb-4">
    <div class="card-header fw-semibold">Reserva #<?= $res['id'] ?></div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-6">
          <div class="text-muted" style="font-size:.78rem">Sala</div>
          <div class="fw-semibold"><?= htmlspecialchars($res['sala_nombre']) ?></div>
        </div>
        <div class="col-6">
          <div class="text-muted" style="font-size:.78rem">Fecha</div>
          <div class="fw-semibold"><?= fechaES($res['fecha']) ?></div>
        </div>
        <div class="col-6">
          <div class="text-muted" style="font-size:.78rem">Horario</div>
          <div class="fw-semibold"><?= substr($res['hora_inicio'],0,5) ?> – <?= substr($res['hora_fin'],0,5) ?></div>
        </div>
        <div class="col-6">
          <div class="text-muted" style="font-size:.78rem">Total de la reserva</div>
          <div class="fw-bold text-brand" style="font-size:1.15rem"><?= precio((int)$res['total']) ?></div>
        </div>
      </div>
    </div>
  </div>

  <?php if (($res['porcentaje_pago'] ?? null) === null): ?>
  <!-- Elegir forma de pago: esta selección ya no está dentro de Términos y Condiciones -->
  <div class="card mb-4">
    <div class="card-header fw-bold">💜 Forma de pago</div>
    <div class="card-body">
      <p class="text-muted mb-3">Elige cómo deseas pagar esta reserva. La información del comprobante solo aparecerá si eliges transferencia.</p>
      <form action="<?= BASE_URL ?>/index.php?r=client/metodo-pago" method="POST" class="d-grid gap-3">
        <?= csrfField() ?>
        <input type="hidden" name="reserva_id" value="<?= (int)$res['id'] ?>">
        <label class="pay-option">
          <input class="form-check-input mt-1" type="radio" name="metodo_pago" value="efectivo" required>
          <span class="flex-grow-1"><strong>💵 Efectivo en el local</strong><br><small class="pay-option__hint">Pagas en el local. El pago se registrará como recibido cuando el local lo cobre.</small></span>
        </label>
        <label class="pay-option">
          <input class="form-check-input mt-1" type="radio" name="metodo_pago" value="transferencia" required>
          <span><strong>🏦 Transferencia bancaria / QR</strong><br><small class="pay-option__hint">Verás el QR y subirás el comprobante del monto elegido.</small></span>
        </label>
        <div class="pay-group">
          <div class="fw-bold mb-2">¿Cuánto deseas pagar? (aplica a efectivo y a transferencia)</div>
          <label class="pay-option mb-2">
            <input class="form-check-input mt-1" type="radio" name="porcentaje_pago" value="50" required>
            <span><strong>50 % — Mitad de la reserva</strong><br><small class="pay-option__hint">Bs <?= (int)ceil((int)$res['total']/2) ?> ahora y saldo de Bs <?= (int)$res['total']-(int)ceil((int)$res['total']/2) ?>.</small></span>
          </label>
          <label class="pay-option">
            <input class="form-check-input mt-1" type="radio" name="porcentaje_pago" value="100" required>
            <span><strong>100 % — Reserva completa</strong><br><small class="pay-option__hint">Bs <?= (int)$res['total'] ?>. No quedará saldo pendiente.</small></span>
          </label>
        </div>
        <button type="submit" id="btnElegirPago" class="btn btn-primary btn-lg w-100">Selecciona una forma de pago</button>
      </form>
    </div>
  </div>
  <a href="<?= BASE_URL ?>/index.php?r=client/mis-reservas" class="btn btn-outline-secondary">← Volver a mis reservas</a>
  <?php elseif (($res['metodo_pago'] ?? '') === 'efectivo'): ?>
  <div class="alert alert-info">💵 Elegiste pagar el <strong><?= (int)$res['porcentaje_pago'] ?> %</strong> en efectivo en el local. No necesitas subir comprobante.</div>
  <a href="<?= BASE_URL ?>/index.php?r=client/mis-reservas" class="btn btn-outline-primary">← Mis reservas</a>
  <?php else: ?>

  <!-- ── Mensaje del administrador (tipo chat) ── -->
  <?php if ($comp && !empty($comp['notas'])): ?>
  <div class="mb-4">
    <div class="chat-admin">
      <div class="chat-header">💬 Mensaje del administrador</div>
      <div class="chat-body"><?= nl2br(htmlspecialchars($comp['notas'])) ?></div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Estado del comprobante -->
  <?php if ($comp): ?>
    <?php
      $estadoClase = match($comp['estado']) {
        'aprobado'  => 'alert-success',
        'rechazado' => 'alert-danger',
        default     => 'alert-warning',
      };
      $estadoIcon = match($comp['estado']) {
        'aprobado'  => '✅',
        'rechazado' => '❌',
        default     => '⏳',
      };
      $estadoTexto = match($comp['estado']) {
        'aprobado'  => 'Comprobante aprobado. ¡Tu reserva está confirmada!',
        'rechazado' => 'Comprobante rechazado. Por favor sube uno nuevo.',
        default     => 'Comprobante en revisión. Te avisaremos pronto.',
      };
    ?>
    <div class="alert <?= $estadoClase ?> mb-4">
      <?= $estadoIcon ?> <strong><?= $estadoTexto ?></strong>
    </div>
  <?php endif; ?>

  <!-- ── Tarjeta-Comprobante (visible cuando ya fue subido) ── -->
  <?php if ($comp): ?>
  <div class="card status-card status-card--<?= $comp['estado']==='aprobado'?'success':($comp['estado']==='rechazado'?'danger':'warning') ?> mb-4">
    <div class="card-header d-flex align-items-center justify-content-between fw-semibold">
      <span>
        <?= $comp['estado']==='aprobado'?'✅':($comp['estado']==='rechazado'?'❌':'⏳') ?>
        Resumen de tu comprobante
      </span>
      <span class="badge-estado estado-<?= $comp['estado'] ?>"><?= ucfirst($comp['estado']) ?></span>
    </div>
    <div class="card-body">
      <div class="row g-3" style="font-size:.88rem">
        <div class="col-6">
          <div class="text-muted" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em">Sala</div>
          <div class="fw-semibold"><?= htmlspecialchars($res['sala_nombre']) ?></div>
        </div>
        <div class="col-6">
          <div class="text-muted" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em">Fecha</div>
          <div class="fw-semibold"><?= fechaES($res['fecha']) ?></div>
        </div>
        <div class="col-6">
          <div class="text-muted" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em">Horario</div>
          <div class="fw-semibold"><?= substr($res['hora_inicio'],0,5) ?> – <?= substr($res['hora_fin'],0,5) ?></div>
        </div>
        <div class="col-6">
          <div class="text-muted" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em">Total reserva</div>
          <div class="fw-bold text-brand" style="font-size:1.05rem"><?= precio((int)$res['total']) ?></div>
        </div>
        <?php if (!empty($comp['monto_pago'])): ?>
        <div class="col-6">
          <div class="text-muted" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em">Monto pagado</div>
          <div class="fw-semibold" style="color:var(--success-text)"><?= precio((int)$comp['monto_pago']) ?></div>
        </div>
        <?php endif; ?>
        <div class="col-6">
          <div class="text-muted" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em">Método</div>
          <div class="fw-semibold">🏦 Transferencia</div>
        </div>
        <div class="col-12" style="border-top:1px dashed var(--border);padding-top:.75rem;margin-top:.25rem">
          <div class="text-muted" style="font-size:.73rem;text-transform:uppercase;letter-spacing:.04em">Estado del comprobante</div>
          <div class="fw-semibold mt-1">
            <?php if ($comp['estado']==='aprobado'): ?>
              <span style="color:var(--success-text)">✅ Aprobado — tu reserva está confirmada</span>
            <?php elseif ($comp['estado']==='rechazado'): ?>
              <span style="color:var(--danger-text)">❌ Rechazado — sube un comprobante correcto</span>
            <?php else: ?>
              <span style="color:var(--warning-text)">⏳ En revisión — te avisaremos pronto</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!$comp || $comp['estado'] === 'rechazado'): ?>

  <?php
    $total = (int)$res['total'];
    $mitad = (int)ceil($total / 2);
    $pct   = (int)$res['porcentaje_pago'];
    $montoPlan = $pct === 100 ? $total : $mitad;
  ?>
  <div class="card mb-4">
    <div class="card-header fw-semibold">💰 Monto a pagar</div>
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center">
        <div><span class="fw-semibold">Pagarás el <?= $pct ?> % de la reserva</span><br>
          <small class="text-muted"><?= $pct === 100 ? 'No quedará saldo pendiente.' : 'El saldo (Bs ' . ($total - $mitad) . ') lo abonas al llegar al local.' ?></small></div>
        <div class="monto-valor">Bs <?= $montoPlan ?></div>
      </div>
    </div>
  </div>

  <!-- QR de pago -->
  <div class="card mb-4">
    <div class="card-header fw-semibold">📲 QR de pago — Yape</div>
    <div class="card-body text-center">
      <?php
        $qrPath = ROOT_PATH . '/public/uploads/qr_pago.png';
        $qrUrl  = UPLOAD_URL . '/qr_pago.png';
        $nombreCliente = $_SESSION['user']['nombre'] . ' ' . ($_SESSION['user']['apellidos'] ?? '');
      ?>
      <?php if (file_exists($qrPath)): ?>
        <img src="<?= $qrUrl ?>" alt="QR Yape" class="qr-img mb-3">
      <?php else: ?>
        <div style="width:220px;height:220px;border-radius:12px;border:1px dashed var(--border);
                    display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;
                    font-size:.83rem;color:var(--text-muted)">QR no disponible</div>
      <?php endif; ?>
      <div class="d-flex flex-column gap-2 align-items-center" style="max-width:280px;margin:0 auto">
        <div style="width:100%;background:var(--info-bg);border:1px solid var(--info-border);border-radius:10px;padding:.65rem 1rem;text-align:left">
          <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.15rem">👤 Nombre del titular de tu cuenta</div>
          <div style="font-weight:700;font-size:.93rem;color:var(--info-text)"><?= htmlspecialchars(trim($nombreCliente)) ?></div>
        </div>
        <div style="width:100%;background:var(--success-bg);border:1px solid var(--success-border);border-radius:10px;padding:.65rem 1rem;text-align:left">
          <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.15rem">🏦 Cuenta destino (Yape)</div>
          <div style="font-weight:700;font-size:.93rem;color:var(--success-text)">Jorge Luis Mamani Canqui</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Formulario comprobante -->
  <div class="card mb-3">
    <div class="card-header fw-semibold">
      <?php if ($comp && $comp['estado'] === 'rechazado'): ?>
        🔁 Reenviar comprobante corregido
      <?php elseif ($comp): ?>
        📁 Reemplazar comprobante
      <?php else: ?>
        📤 Subir comprobante
      <?php endif; ?>
    </div>
    <div class="card-body">

      <?php if ($comp && $comp['estado'] === 'pendiente'): ?>
      <!-- Banner: ya enviado pero editable -->
      <div class="editar-banner mb-3 d-flex align-items-start gap-2">
        <span style="font-size:1.3rem">✏️</span>
        <div>
          <strong>Tu comprobante está en revisión</strong><br>
          <span class="text-muted">¿Subiste el archivo incorrecto?
          Puedes reemplazarlo enviando uno nuevo antes de que sea revisado.</span>
        </div>
      </div>
      <?php endif; ?>

      <form action="<?= BASE_URL ?>/index.php?r=client/comprobante-post" method="POST"
            enctype="multipart/form-data" id="frmComp">
        <?= csrfField() ?>
        <input type="hidden" name="reserva_id" value="<?= $res['id'] ?>">
        <input type="hidden" name="monto_pago" id="montoPagoHidden" value="<?= $montoPlan ?>">

        <!-- Drop zone -->
        <div class="drop-zone mb-2" id="dropZone">
          <input type="file" name="comprobante" id="fileInput"
                 accept=".jpg,.jpeg,.png,.pdf" required>
          <span class="dz-icon">📂</span>
          <div class="dz-text">
            <strong>Arrastra tu comprobante aquí</strong><br>
            o haz clic para seleccionar
          </div>
          <div class="dz-fname" id="fileNombre"></div>
        </div>
        <div class="form-text mb-1">Formatos aceptados: JPG, PNG, PDF. Máx. 5 MB.</div>
        <div class="form-text mb-3" style="color:var(--text-link)">
          💡 Asegúrate de que en el comprobante aparezca el <strong>nombre del titular de tu cuenta</strong>.
        </div>

        <div class="alert alert-warning py-2 mb-3" style="font-size:.82rem">
          ⚠️ <strong>No se aceptan devoluciones.</strong> Una vez confirmada la reserva, no se efectúan reembolsos.
        </div>

        <button type="submit" class="btn btn-primary w-100" id="btnEnviar" disabled>
          📤 Enviar Comprobante
        </button>
        <div class="form-text text-center mt-1" id="hintEnviar" style="color:var(--text-muted)">Selecciona un archivo para habilitar el envío</div>
      </form>
    </div>
  </div>

  <?php endif; ?>

  <div class="mt-2 d-flex gap-2 flex-wrap">
    <?php if ($comp && $comp['estado'] !== 'rechazado'): ?>
      <a href="<?= BASE_URL ?>/index.php?r=client/mis-reservas" class="btn btn-primary">Finalizar reserva</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/index.php?r=client/mis-reservas" class="btn btn-outline-primary">← Mis reservas</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>

<?php require ROOT_PATH . '/views/partials/footer.php'; ?>
<script>
// Texto del botón según la forma de pago seleccionada.
const opcionesPago = document.querySelectorAll('input[name="metodo_pago"]');
const btnElegirPago = document.getElementById('btnElegirPago');
function actualizarFormaPago() {
  const metodo = document.querySelector('input[name="metodo_pago"]:checked')?.value || '';
  if (btnElegirPago) btnElegirPago.textContent = metodo === 'efectivo' ? '💵 Confirmar pago en efectivo'
    : (metodo === 'transferencia' ? '🏦 Continuar con transferencia' : 'Selecciona una forma de pago');
}
opcionesPago.forEach(op => op.addEventListener('change', actualizarFormaPago));

/* ── Drag and drop ── */
const dropZone   = document.getElementById('dropZone');
const fileInput  = document.getElementById('fileInput');
const fileNombre = document.getElementById('fileNombre');
const btnEnviar  = document.getElementById('btnEnviar');
const hintEnviar = document.getElementById('hintEnviar');

function archivoSeleccionado(nombre) {
  fileNombre.textContent = '📄 ' + nombre;
  if (btnEnviar) {
    btnEnviar.disabled = false;
    hintEnviar.style.display = 'none';
  }
}

if (dropZone) {
  ['dragenter','dragover'].forEach(e => dropZone.addEventListener(e, ev => {
    ev.preventDefault(); dropZone.classList.add('dragover');
  }));
  ['dragleave','drop'].forEach(e => dropZone.addEventListener(e, ev => {
    ev.preventDefault(); dropZone.classList.remove('dragover');
  }));
  dropZone.addEventListener('drop', ev => {
    const files = ev.dataTransfer.files;
    if (files.length) { fileInput.files = files; archivoSeleccionado(files[0].name); }
  });
  fileInput.addEventListener('change', () => {
    if (fileInput.files.length) archivoSeleccionado(fileInput.files[0].name);
  });
}
</script>
</body>
</html>
