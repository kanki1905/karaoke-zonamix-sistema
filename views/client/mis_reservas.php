<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mis Reservas — Karaoke Zona Mix</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
  <style>
    .comp-card-header {
      padding:.75rem 1rem;border-radius:10px 10px 0 0;
      display:flex;align-items:center;justify-content:space-between;
    }
    .comp-row { display:flex;flex-direction:column;gap:2px;margin-bottom:.65rem; }
    .comp-row-label { font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;color:var(--text-muted); }
    .comp-row-val   { font-weight:600;font-size:.9rem; }
  </style>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/nav.php'; ?>

<div class="container" style="padding:2.5rem 0">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h2 class="fw-bold mb-0" style="color:var(--text-primary)">📋 Mis Reservas</h2>
      <p class="text-muted mb-0">Historial de todas tus reservas</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?r=client/reservar" class="btn btn-primary">+ Nueva Reserva</a>
  </div>

  <?php if ($ok = flash('success')): ?>
    <div class="alert alert-success"><?= htmlspecialchars($ok) ?></div>
  <?php endif; ?>
  <?php if ($err = flash('error')): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <?php if (empty($reservas)): ?>
    <div class="card text-center py-5">
      <div class="card-body">
        <div style="font-size:4rem">🎤</div>
        <h4 class="mt-3">Aún no tienes reservas</h4>
        <p class="text-muted">¡Haz tu primera reserva y empieza a disfrutar!</p>
        <a href="<?= BASE_URL ?>/index.php?r=client/reservar" class="btn btn-primary">Reservar ahora</a>
      </div>
    </div>
  <?php else: ?>
  <div class="reservation-tools card mb-3"><div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-md-6"><label class="form-label" for="buscarMisReservas">Buscar reserva</label><div class="input-group"><span class="input-group-text">🔎</span><input id="buscarMisReservas" type="search" class="form-control" placeholder="Buscar por sala, fecha, horario, pago o estado…"></div></div>
      <div class="col-md-3"><label class="form-label" for="filtrarMes">Mes</label><input id="filtrarMes" type="month" class="form-control"></div>
      <div class="col-md-3"><label class="form-label" for="filtrarEstado">Estado</label><select id="filtrarEstado" class="form-select"><option value="">Todos los estados</option><option value="pendiente">Pendiente</option><option value="confirmada">Confirmada</option><option value="cancelada">Cancelada</option><option value="finalizada">Finalizada</option></select></div>
    </div><small class="text-muted d-block mt-2" id="contadorReservas"></small>
  </div></div>
  <div id="gridMisReservas" class="res-grid">
    <?php foreach ($reservas as $r): ?>
    <article class="res-card" data-fecha="<?= htmlspecialchars((string)$r['fecha']) ?>" data-estado="<?= htmlspecialchars((string)$r['estado']) ?>">
      <header class="res-card__head">
        <span class="res-card__id">#<?= (int)$r['id'] ?></span>
        <span class="badge-estado estado-<?= $r['estado'] ?>"><?= ucfirst($r['estado']) ?></span>
      </header>
      <h3 class="res-card__title"><?= htmlspecialchars($r['sala_nombre']) ?></h3>
      <dl class="res-card__meta">
        <div><dt>Fecha</dt><dd><?= fechaES($r['fecha']) ?></dd></div>
        <div><dt>Horario</dt><dd><?= substr($r['hora_inicio'],0,5) ?> – <?= substr($r['hora_fin'],0,5) ?> · <?= $r['horas'] ?>h</dd></div>
        <div><dt>Pago</dt><dd><?php
          $iconos = ['efectivo'=>'💵','transferencia'=>'🏦'];
          echo ($iconos[$r['metodo_pago']] ?? '💵') . ' ' . ucfirst($r['metodo_pago']);
        ?></dd></div>
      </dl>
      <div class="res-card__total"><?= precio((int)$r['total']) ?></div>
      <div class="res-card__actions">
        <div class="d-flex gap-1 flex-wrap">
          <?php if ($r['metodo_pago'] === 'transferencia'): ?>
            <?php if (!$r['comp_id']): ?>
              <!-- Sin comprobante aún -->
              <a href="<?= BASE_URL ?>/index.php?r=client/comprobante&id=<?= $r['id'] ?>"
                 class="btn btn-sm btn-warning">📎 Subir</a>
            <?php else: ?>
              <!-- Ver tarjeta-comprobante -->
              <button type="button" class="btn btn-sm btn-outline-primary"
                      onclick="verComprobante(<?= htmlspecialchars(json_encode([
                        'id'          => $r['id'],
                        'sala'        => $r['sala_nombre'],
                        'fecha'       => $r['fecha'],
                        'hora_inicio' => $r['hora_inicio'],
                        'hora_fin'    => $r['hora_fin'],
                        'total'       => $r['total'],
                        'comp_estado' => $r['comp_estado'],
                        'comp_notas'  => $r['comp_notas'],
                      ]), ENT_QUOTES) ?>)">
                📄 Ver
              </button>
              <!-- Editar comprobante (solo si pendiente o rechazado) -->
              <?php if (in_array($r['comp_estado'], ['pendiente','rechazado'])): ?>
              <a href="<?= BASE_URL ?>/index.php?r=client/comprobante&id=<?= $r['id'] ?>"
                 class="btn btn-sm btn-outline-warning">✏️ Editar</a>
              <?php endif; ?>
            <?php endif; ?>
          <?php endif; ?>

          <?php if (in_array($r['estado'], ['pendiente','confirmada'])): ?>
            <button type="button" class="btn btn-sm btn-outline-danger"
                    onclick='confirmarCancelacion(<?= (int)$r['id'] ?>,
                      <?= htmlspecialchars(json_encode((string)$r['sala_nombre'], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>,
                      <?= htmlspecialchars(json_encode(fechaES($r['fecha']), JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>,
                      <?= htmlspecialchars(json_encode(substr($r['hora_inicio'],0,5).'–'.substr($r['hora_fin'],0,5), JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>,
                      <?= htmlspecialchars(json_encode((string)$r['metodo_pago'], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>)'>
              ✖ Cancelar
            </button>
          <?php endif; ?>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <div id="sinResultadosReservas" class="panel panel--info mt-3" hidden>No hay reservas que coincidan con los filtros.</div>
  </div>
  <?php endif; ?>
</div>

<!-- ── Modal tarjeta-comprobante ──────────────────────────────── -->
<div class="modal fade" id="modalComprobante" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;overflow:hidden;border:none">
      <div id="modalCompHeader" class="comp-card-header">
        <span id="modalCompTitulo" class="fw-bold" style="font-size:1rem"></span>
        <span id="modalCompBadge" class="badge-estado"></span>
      </div>
      <div class="modal-body p-0">
        <div style="padding:1.25rem 1.5rem">
          <div class="row g-3" id="modalCompBody"></div>

          <!-- Mensaje admin -->
          <div id="modalCompNotas" style="display:none;margin-top:.75rem;
               background:var(--bg-secondary);border-left:4px solid var(--primary);
               border-radius:0 10px 10px 0;padding:.8rem 1rem;font-size:.86rem"></div>
        </div>
      </div>
      <div class="modal-footer" style="border-top:1px solid var(--border)">
        <a id="modalCompLink" href="#" class="btn btn-outline-primary btn-sm">📎 Ir al comprobante</a>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- ── Modal confirmar cancelación ────────────────────────────── -->
<div class="modal fade" id="modalCancelar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;overflow:hidden;border:none">
      <div class="modal-header" style="background:var(--danger-bg);border-bottom:2px solid var(--danger-border)">
        <h5 class="modal-title fw-bold" style="color:var(--danger-text)">⚠️ ¿Cancelar reserva?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:1.4rem 1.5rem">
        <div id="cancelDetalle" style="background:var(--bg-secondary);color:var(--text-primary);border:1px solid var(--border);border-radius:10px;padding:.9rem 1rem;margin-bottom:1rem;font-size:.88rem">
          <!-- llenado por JS -->
        </div>
        <div id="cancelAvisoDevolucion"
             style="display:none;background:var(--warning-bg);border-left:4px solid var(--warning);
                    border-radius:0 10px 10px 0;padding:.75rem 1rem;font-size:.85rem;color:var(--warning-text);margin-bottom:.5rem">
          <strong>⚠ Sin devolución:</strong> Los pagos por efectivo o transferencia
          <strong>no son reembolsables</strong> una vez confirmada la reserva.
          Por favor asegúrate antes de continuar.
        </div>
        <p style="font-size:.85rem;color:var(--text-muted);margin:0">
          Esta acción no se puede deshacer. La sala quedará disponible para otros usuarios.
        </p>
      </div>
      <div class="modal-footer" style="border-top:1px solid var(--border)">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
          No, volver
        </button>
        <form id="cancelConfirmForm" action="<?= BASE_URL ?>/index.php?r=client/cancelar" method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="id" id="cancelReservaId" value=""><button type="submit" class="btn btn-danger btn-sm">Sí, cancelar reserva</button></form>
      </div>
    </div>
  </div>
</div>

<?php require ROOT_PATH . '/views/partials/footer.php'; ?>
<script>
const BASE = '<?= BASE_URL ?>';

// ── Modal cancelación ──────────────────────────────────────────
function escapeHtml(value) { return String(value).replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch])); }

function confirmarCancelacion(id, sala, fecha, horario, metodo) {
  const pagosSinDevolucion = ['efectivo','transferencia'];

  // Detalle de la reserva
  document.getElementById('cancelDetalle').innerHTML =
    `<div style="display:flex;flex-direction:column;gap:.4rem;color:var(--text-primary)">
       <div><span style="color:var(--text-secondary);font-size:.75rem;text-transform:uppercase;letter-spacing:.04em">Sala</span><br><strong style="color:var(--text-primary)">${escapeHtml(sala)}</strong></div>
       <div><span style="color:var(--text-secondary);font-size:.75rem;text-transform:uppercase;letter-spacing:.04em">Fecha</span><br><strong style="color:var(--text-primary)">${escapeHtml(fecha)}</strong></div>
       <div><span style="color:var(--text-secondary);font-size:.75rem;text-transform:uppercase;letter-spacing:.04em">Horario</span><br><strong style="color:var(--text-primary)">${escapeHtml(horario)}</strong></div>
       <div><span style="color:var(--text-secondary);font-size:.75rem;text-transform:uppercase;letter-spacing:.04em">Método de pago</span><br><strong style="color:var(--text-primary)">${escapeHtml(metodo.charAt(0).toUpperCase()+metodo.slice(1))}</strong></div>
     </div>`;

  // Aviso devolución solo para efectivo y transferencia
  const aviso = document.getElementById('cancelAvisoDevolucion');
  aviso.style.display = pagosSinDevolucion.includes(metodo) ? 'block' : 'none';

  // El cambio de estado se envía por POST con token CSRF.
  document.getElementById('cancelReservaId').value = String(id);

  new bootstrap.Modal(document.getElementById('modalCancelar')).show();
}

function meses(n) {
  const m = ['enero','febrero','marzo','abril','mayo','junio',
             'julio','agosto','septiembre','octubre','noviembre','diciembre'];
  return m[n] || '';
}
function fechaES(str) {
  const [y,mo,d] = str.split('-');
  return d + ' de ' + meses(parseInt(mo)-1) + ' de ' + y;
}
function precio(n) { return 'Bs ' + parseInt(n); }

function verComprobante(d) {
  const estadoConf = {
    aprobado:  { clase:'estado-aprobado',  icon:'✅', texto:'Aprobado',   k:'success' },
    rechazado: { clase:'estado-rechazado', icon:'❌', texto:'Rechazado',  k:'danger' },
    pendiente: { clase:'estado-pendiente', icon:'⏳', texto:'En revisión', k:'warning' },
  };
  const est = estadoConf[d.comp_estado] || estadoConf.pendiente;

  // Header
  const header = document.getElementById('modalCompHeader');
  header.className = 'comp-card-header status-head status-card--' + est.k;

  document.getElementById('modalCompTitulo').textContent = est.icon + ' Comprobante — Reserva #' + d.id;

  const badge = document.getElementById('modalCompBadge');
  badge.className = 'badge-estado ' + est.clase;
  badge.textContent = est.texto;

  // Body
  document.getElementById('modalCompBody').innerHTML = `
    <div class="col-6"><div class="comp-row">
      <span class="comp-row-label">Sala</span>
      <span class="comp-row-val">${d.sala}</span>
    </div></div>
    <div class="col-6"><div class="comp-row">
      <span class="comp-row-label">Fecha</span>
      <span class="comp-row-val">${fechaES(d.fecha)}</span>
    </div></div>
    <div class="col-6"><div class="comp-row">
      <span class="comp-row-label">Horario</span>
      <span class="comp-row-val">${d.hora_inicio.substring(0,5)} – ${d.hora_fin.substring(0,5)}</span>
    </div></div>
    <div class="col-6"><div class="comp-row">
      <span class="comp-row-label">Total reserva</span>
      <span class="comp-row-val" style="color:var(--text-link)">${precio(d.total)}</span>
    </div></div>
    <div class="col-12"><div class="comp-row">
      <span class="comp-row-label">Estado del comprobante</span>
      <span class="comp-row-val">${est.icon} ${
        d.comp_estado==='aprobado'  ? 'Tu reserva está confirmada' :
        d.comp_estado==='rechazado' ? 'Sube un comprobante correcto' :
                                      'En revisión — te avisaremos pronto'
      }</span>
    </div></div>
  `;

  // Mensaje admin
  const notasEl = document.getElementById('modalCompNotas');
  if (d.comp_notas) {
    notasEl.innerHTML = '<span style="font-size:.72rem;color:var(--text-muted);display:block;margin-bottom:.3rem">💬 Mensaje del administrador</span>' + d.comp_notas;
    notasEl.style.display = 'block';
  } else {
    notasEl.style.display = 'none';
  }

  // Link
  document.getElementById('modalCompLink').href = BASE + '/index.php?r=client/comprobante&id=' + d.id;

  new bootstrap.Modal(document.getElementById('modalComprobante')).show();
}
</script>
<script>
(()=>{
  const q=document.getElementById('buscarMisReservas'), m=document.getElementById('filtrarMes'), e=document.getElementById('filtrarEstado'),
        g=document.getElementById('gridMisReservas'); if(!g) return;
  const cards=[...g.querySelectorAll('.res-card')], vacio=document.getElementById('sinResultadosReservas');
  function filtrar(){
    let n=0; const txt=q.value.toLocaleLowerCase('es');
    cards.forEach(c=>{
      const ok=c.innerText.toLocaleLowerCase('es').includes(txt)
        && (!m.value || (c.dataset.fecha||'').slice(0,7)===m.value)
        && (!e.value || (c.dataset.estado||'')===e.value);
      c.hidden=!ok; if(ok) n++;
    });
    document.getElementById('contadorReservas').textContent=n+' reserva(s) encontrada(s)';
    vacio.hidden = n!==0;
  }
  [q,m,e].forEach(x=>x.addEventListener('input',filtrar)); filtrar();
})();
</script>
<style>.reservation-tools{border-color:var(--border);background:var(--bg-secondary)}</style>
</body>
</html>
