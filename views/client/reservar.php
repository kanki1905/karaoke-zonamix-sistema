<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reservar Sala — Karaoke Zona Mix</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
  <style>
    .hora-slot {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 72px;
      height: 44px;
      border-radius: 8px;
      font-size: .78rem;
      font-weight: 600;
      cursor: pointer;
      transition: transform .12s, box-shadow .12s;
      user-select: none;
      border: 2px solid transparent;
    }
    .hora-slot.libre {
      background: var(--primary);
      color: var(--on-primary);
      border-color: var(--primary);
    }
    .hora-slot.libre:hover {
      background: var(--primary-hover);
      transform: translateY(-2px);
      box-shadow: 0 4px 10px var(--shadow-color);
    }
    .hora-slot.ocupado {
      background: var(--danger-bg);
      color: var(--danger-text);
      border-color: var(--danger-border);
      cursor: not-allowed;
    }
    .hora-slot.mantenimiento {
      background: var(--surface-sunken);
      color: var(--text-muted);
      border-color: var(--border);
      cursor: not-allowed;
      text-decoration: line-through;
    }
    .hora-slot.amarillo {
      background: var(--warning-bg);
      color: var(--warning-text);
      border-color: var(--warning-border);
      cursor: not-allowed;
    }
    .hora-slot.seleccionado {
      background: var(--primary);
      color: var(--on-primary);
      border-color: var(--accent);
      box-shadow: 0 0 16px var(--shadow-color);
    }
    #grillaCont { display:none; }
    #grillaCont .leyenda span {
      width: 14px; height: 14px;
      border-radius: 3px;
      display: inline-block;
      margin-right: 4px;
    }
  .hora-controlada{pointer-events:none;background-color:var(--surface);color:var(--text-primary)}.hora-controlada:focus{box-shadow:none}
  .payment-panel{background:var(--bg-secondary);border:1px solid var(--border)}
  #grillaSlots{background:var(--bg-secondary);border-color:var(--border)}
  .hora-slot.libre:hover{box-shadow:0 0 16px var(--shadow-color)}
  </style>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/nav.php'; ?>

<div class="container" style="padding:2.5rem 0;max-width:760px">

  <div class="d-flex align-items-center justify-content-between mb-1 flex-wrap gap-2">
    <h2 class="fw-bold mb-0" style="color:var(--text-primary)">📅 Nueva Reserva</h2>
    <div class="btn-group btn-group-sm" id="toggleVistas" role="group">
      <button type="button" class="btn btn-primary" id="btnVistaDia" onclick="setVista('dia')">📅 Vista diaria</button>
      <button type="button" class="btn btn-outline-primary" id="btnVistaSem" onclick="setVista('semana')">🗓 Vista semanal</button>
    </div>
  </div>
  <p class="text-muted mb-4">Elige tu sala, fecha y horario favorito.</p>

  <!-- ── Vista semanal ──────────────────────────────────────── -->
  <div id="vistaSemanal" style="display:none" class="mb-4">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span class="fw-bold">🗓 Horario semanal por sala</span>
        <div class="d-flex align-items-center gap-2">
          <label class="form-label mb-0" style="font-size:.83rem">Sala:</label>
          <select id="semSalaSelect" class="form-select form-select-sm" style="max-width:200px" onchange="cargarSemana()">
            <option value="">— elige sala —</option>
            <?php foreach ($salas as $s): ?>
            <option value="<?= $s['id'] ?>"
                    <?= (int)($fd['sala_id'] ?? $salaId) === (int)$s['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($s['nombre']) ?><?= !empty($s['numero']) ? ' #'.(int)$s['numero'] : '' ?>
              <?= !$s['activa'] ? ' 🔧' : '' ?>
            </option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-outline-secondary btn-sm" onclick="semNavegar(-1)">◀</button>
          <span id="semRangoLabel" class="fw-semibold" style="font-size:.85rem;white-space:nowrap"></span>
          <button class="btn btn-outline-secondary btn-sm" onclick="semNavegar(1)">▶</button>
        </div>
      </div>
      <div class="card-body p-2">
        <div class="d-flex flex-wrap gap-3 mb-2" style="font-size:.74rem;font-weight:600">
          <span><span style="display:inline-block;width:12px;height:12px;border-radius:2px;background:var(--success-bg);border:1px solid var(--success-border);margin-right:3px;vertical-align:middle"></span>Libre (clic para preseleccionar)</span>
          <span><span style="display:inline-block;width:12px;height:12px;border-radius:2px;background:var(--danger-bg);border:1px solid var(--danger-border);margin-right:3px;vertical-align:middle"></span>Ocupado</span>
          <span><span style="display:inline-block;width:12px;height:12px;border-radius:2px;background:var(--surface-sunken);margin-right:3px;vertical-align:middle"></span>Pasado/Hoy</span>
        </div>
        <div id="semTablaWrap" class="table-responsive" style="border-radius:8px;overflow:hidden">
          <div class="text-center text-muted py-4" style="font-size:.87rem">Selecciona una sala para ver su disponibilidad semanal.</div>
        </div>
      </div>
    </div>
  </div>

  <?php if ($err = flash('error')): ?>
    <div class="alert alert-danger d-flex align-items-start gap-2">
      <span style="font-size:1.2rem">⚠️</span>
      <span><?= htmlspecialchars($err) ?></span>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header">Datos de la Reserva</div>
    <div class="card-body">
      <form action="<?= BASE_URL ?>/index.php?r=client/reservar-post" method="POST" id="frmReserva">
        <?= csrfField() ?>

        <!-- Sala -->
        <div class="mb-3">
          <label class="form-label">Sala</label>
          <div id="errSala" class="text-danger mb-1" style="font-size:.83rem;display:none"></div>
          <select name="sala_id" id="salaSelect" class="form-select" required>
            <option value="">— Selecciona una sala —</option>
            <?php foreach ($salas as $s): ?>
            <option value="<?= $s['id'] ?>"
                    data-precio="<?= $s['precio_hora'] ?>"
                    data-cap="<?= $s['capacidad'] ?>"
                    data-activa="<?= (int)$s['activa'] ?>"
                    <?= (int)($fd['sala_id'] ?? $salaId) === (int)$s['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($s['nombre']) ?><?= !empty($s['numero']) ? ' [Cabina #'.(int)$s['numero'].']' : '' ?>
              <?= !$s['activa'] ? ' 🔧 [En mantenimiento]' : ' — '.precio((int)$s['precio_hora']).'/h (hasta '.$s['capacidad'].' personas)' ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Fecha -->
        <div class="mb-3">
          <label class="form-label">Fecha</label>
          <div id="errFecha" class="text-danger mb-1" style="font-size:.83rem;display:none"></div>
          <input type="date" name="fecha" id="fechaInput" class="form-control" required autocomplete="off" autocomplete="off"
                 min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d', strtotime('+1 month')) ?>"
                 value="<?= htmlspecialchars($fd['fecha'] ?? date('Y-m-d')) ?>">
        </div>

        <!-- Grilla dinámica de horarios -->
        <div id="grillaCont" class="mb-3">
          <label class="form-label">Horario disponible</label>
          <div class="d-flex flex-wrap gap-3 mb-2" style="font-size:.75rem;font-weight:600">
            <span class="d-flex align-items-center gap-1">
              <span style="width:13px;height:13px;border-radius:3px;background:var(--success-bg);border:1px solid var(--success-border);display:inline-block"></span>Libre
            </span>
            <span class="d-flex align-items-center gap-1">
              <span style="width:13px;height:13px;border-radius:3px;background:var(--danger-bg);border:1px solid var(--danger-border);display:inline-block"></span>Ocupado
            </span>
            <span class="d-flex align-items-center gap-1">
              <span style="width:13px;height:13px;border-radius:3px;background:var(--surface-sunken);border:1px solid var(--border);display:inline-block"></span>Hora pasada
            </span>
            <span class="d-flex align-items-center gap-1">
              <span style="width:13px;height:13px;border-radius:3px;background:var(--warning-bg);border:1px solid var(--warning-border);display:inline-block"></span>Mantenimiento
            </span>
            <span class="d-flex align-items-center gap-1">
              <span style="width:13px;height:13px;border-radius:3px;background:var(--primary);border:2px solid var(--accent);display:inline-block"></span>Seleccionado (tus horas)
            </span>
          </div>
          <div id="grillaSlots" class="d-flex flex-wrap gap-2 p-3 rounded"
               style="background:var(--bg-secondary);border:1px solid var(--border);min-height:64px">
            <span class="text-muted" style="font-size:.85rem">Cargando…</span>
          </div>
          <div class="form-text mt-1">Cada clic activa o desactiva una hora. Selecciona varias horas consecutivas para ampliar la reserva; no se permiten huecos.</div>
        </div>

        <!-- Horario (selects) -->
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label">Hora inicio</label>
            <div id="errHoraIni" class="text-danger mb-1" style="font-size:.83rem;display:none"></div>
            <select name="hora_ini" id="hIni" class="form-select hora-controlada" required tabindex="-1" aria-readonly="true">
              <option value="">—</option>
              <?php for ($h=8;$h<=22;$h++):
                $val = sprintf('%02d',$h).':00'; ?>
              <option value="<?= $val ?>"
                <?= ($fd['hora_ini'] ?? '') === $val ? 'selected' : '' ?>>
                <?= $val ?>
              </option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label">Hora fin</label>
            <div id="errHoraFin" class="text-danger mb-1" style="font-size:.83rem;display:none"></div>
            <select name="hora_fin" id="hFin" class="form-select hora-controlada" required tabindex="-1" aria-readonly="true">
              <option value="">—</option>
              <?php for ($h=9;$h<=23;$h++):
                $val = sprintf('%02d',$h).':00'; ?>
              <option value="<?= $val ?>"
                <?= ($fd['hora_fin'] ?? '') === $val ? 'selected' : '' ?>>
                <?= $val ?>
              </option>
              <?php endfor; ?>
            </select>
          </div>
        </div>

        <!-- Resumen -->
        <div id="resumen" class="alert alert-info" style="display:none">
          <strong>Resumen:</strong>
          <span id="resumenTexto"></span>
        </div>

        <div class="d-grid mt-3">
          <button type="button" id="btnConfirmarReserva" class="btn btn-primary btn-lg" onclick="abrirTerminos()">
            Confirmar Reserva
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require ROOT_PATH . '/views/partials/footer.php'; ?>
<!-- ── Modal Términos y Condiciones ─────────────────────────── -->
<div class="modal fade" id="modalTerminos" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">📋 Términos y Condiciones de Reserva</h5>
      </div>
      <div class="modal-body" style="font-size:.9rem">

        <p class="text-muted mb-3">Antes de confirmar tu reserva, por favor lee y acepta nuestros términos:</p>

        <ul class="list-unstyled" style="line-height:2">
          <li>✅ Las reservas son personales e intransferibles.</li>
          <li>✅ El horario de la sala comienza puntualmente en la hora indicada, independientemente de la hora de llegada del cliente.</li>
          <li>✅ No se permite el ingreso de alimentos ni bebidas externas al local.</li>
          <li>✅ Se prohíbe el consumo de alcohol por personas menores de 18 años.</li>
          <li>✅ Queda prohibido fumar dentro de las instalaciones.</li>
          <li>✅ El cliente es responsable de cualquier daño causado al equipo o instalaciones durante su reserva.</li>
          <li>✅ Se debe mantener un comportamiento respetuoso con el personal y los demás clientes.</li>
          <li>✅ El volumen de la sala no debe molestar a las cabinas vecinas.</li>
          <li>✅ La sala debe ser entregada en las mismas condiciones en que se recibió.</li>
        </ul>

        <div class="alert alert-warning d-flex align-items-start gap-2 mt-3 mb-0">
          <span style="font-size:1.3rem">⚠️</span>
          <div>
            <strong>No se aceptan devoluciones.</strong><br>
            <small>Una vez realizada y pagada la reserva, no se efectúan devoluciones del monto abonado bajo ningún concepto.</small>
          </div>
        </div>
      </div>
      <div class="modal-footer flex-column align-items-stretch gap-2">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="chkTerminos"
                 onchange="document.getElementById('btnContinuar').disabled = !this.checked">
          <label class="form-check-label fw-semibold" for="chkTerminos">
            He leído y acepto los términos y condiciones
          </label>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-secondary flex-fill"
                  data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary flex-fill" id="btnContinuar"
                  disabled onclick="submitReserva()">
            ✓ Continuar y Confirmar Reserva
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const BASE         = '<?= BASE_URL ?>';
const HORA_ACT_SRV = <?= (int)date('H') ?>;   // hora servidor Bolivia (UTC-4)
const HOY_SRV      = '<?= date('Y-m-d') ?>';   // fecha servidor Bolivia

/* ── Resumen de precio ──────────────────────────────────────── */
function actualizarResumen() {
  const sala   = document.getElementById('salaSelect');
  const hi     = document.getElementById('hIni').value;
  const hf     = document.getElementById('hFin').value;
  const opt    = sala.options[sala.selectedIndex];
  const precio = parseInt(opt?.dataset?.precio || 0);
  const res    = document.getElementById('resumen');
  const txt    = document.getElementById('resumenTexto');

  if (!hi || !hf || !precio || hi >= hf) { res.style.display='none'; return; }

  const horas = parseInt(hf) - parseInt(hi);
  const total = horas * precio;
  txt.textContent = ` ${opt.text.split('—')[0].trim()} · ${hi} – ${hf} · ${horas}h · Bs ${total}`;
  res.style.display = 'block';
}

/* ── Grilla dinámica ────────────────────────────────────────── */
let ocupadosActuales   = [];
let salaEnMantenimiento = false;
// Cada botón representa una hora completa. Se selecciona/deselecciona con un clic.
let horasSeleccionadas = new Set();   // true si la sala está inactiva (mantenimiento)

function estaOcupado(hStr) {
  for (const o of ocupadosActuales) {
    const ini = o.hora_inicio.substring(0,5);
    const fin = o.hora_fin.substring(0,5);
    if (hStr >= ini && hStr < fin) return true;
  }
  return false;
}

function estaHoraPasada(hStr) {
  // Usa hora del servidor Bolivia (UTC-4) — evita desfase por timezone del browser
  const fechaSel = document.getElementById('fechaInput').value;
  if (fechaSel !== HOY_SRV) return false;
  return parseInt(hStr) <= HORA_ACT_SRV;
}

function actualizarSeleccionGrilla() {
  const seleccionadas = [...horasSeleccionadas].sort();
  const slots = document.querySelectorAll('.hora-slot');
  slots.forEach(slot => {
    const h = slot.dataset.hora;
    if (estaOcupado(h) || estaHoraPasada(h) || salaEnMantenimiento) return;
    const elegido = horasSeleccionadas.has(h);
    slot.classList.toggle('seleccionado', elegido);
    slot.classList.toggle('libre', !elegido);
    slot.setAttribute('aria-pressed', elegido ? 'true' : 'false');
  });
  const ini = document.getElementById('hIni');
  const fin = document.getElementById('hFin');
  if (!seleccionadas.length) {
    ini.value = ''; fin.value = '';
  } else {
    const horas = seleccionadas.map(x => parseInt(x.slice(0,2),10));
    const min = Math.min(...horas), max = Math.max(...horas);
    // La selección siempre debe formar un tramo continuo, sin huecos.
    const continua = (max - min + 1) === new Set(horas).size &&
      Array.from({length:max-min+1}, (_,i) => String(min+i).padStart(2,'0')+':00').every(x => horasSeleccionadas.has(x));
    if (!continua) {
      horasSeleccionadas.clear();
      ini.value = ''; fin.value = '';
      slots.forEach(slot => { if (!slot.classList.contains('ocupado') && !slot.classList.contains('mantenimiento') && !slot.classList.contains('amarillo')) {slot.classList.remove('seleccionado');slot.classList.add('libre');slot.setAttribute('aria-pressed','false');} });
      mostrarError('errHoraIni', 'Selecciona horas consecutivas, sin dejar espacios entre ellas.');
      document.getElementById('resumen').style.display='none';
      return;
    }
    ini.value = String(min).padStart(2,'0') + ':00';
    fin.value = String(max+1).padStart(2,'0') + ':00';
  }
  actualizarResumen();
}

function cargarGrilla() {
  const salaId = document.getElementById('salaSelect').value;
  const fecha  = document.getElementById('fechaInput').value;
  const cont   = document.getElementById('grillaCont');
  const slots  = document.getElementById('grillaSlots');

  if (!salaId || !fecha) { cont.style.display='none'; return; }

  horasSeleccionadas.clear();
  document.getElementById('hIni').value = '';
  document.getElementById('hFin').value = '';
  slots.innerHTML = '<span class="text-muted" style="font-size:.85rem">Cargando…</span>';
  cont.style.display = 'block';

  fetch(`${BASE}/index.php?r=client/horario&sala=${salaId}&fecha=${fecha}`)
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(data => {
      ocupadosActuales    = data.ocupados      ?? data; // back-compat
      salaEnMantenimiento = data.mantenimiento ?? false;
      slots.innerHTML = '';

      // Aviso si sala en mantenimiento
      if (salaEnMantenimiento) {
        slots.innerHTML = '<div class="alert alert-warning py-2 mb-2 w-100" style="font-size:.85rem">🔧 Esta sala está actualmente en mantenimiento y no puede reservarse.</div>';
        document.getElementById('btnConfirmarReserva').disabled = true;
      } else {
        document.getElementById('btnConfirmarReserva').disabled = false;
      }

      const hiVal = document.getElementById('hIni').value;
      const hfVal = document.getElementById('hFin').value;

      for (let h = 8; h <= 22; h++) {
        const hStr      = (h < 10 ? '0' : '') + h + ':00';
        const ocupado   = estaOcupado(hStr);
        const pasada    = !ocupado && estaHoraPasada(hStr);
        const mantto    = !ocupado && !pasada && salaEnMantenimiento;
        const enRango   = !ocupado && !pasada && !mantto && hiVal && hfVal && hStr >= hiVal && hStr < hfVal;

        const slot = document.createElement('div');
        let clase = 'hora-slot ';
        if (ocupado)    clase += 'ocupado';
        else if (pasada) clase += 'mantenimiento';   // gris = hora pasada
        else if (mantto) clase += 'amarillo';        // amarillo = sala en mant.
        else if (enRango) clase += 'seleccionado';
        else              clase += 'libre';

        slot.className    = clase;
        slot.dataset.hora = hStr;
        slot.textContent  = hStr;
        slot.title = ocupado ? 'Hora ocupada' :
                     pasada  ? 'Hora ya pasada' :
                     mantto  ? 'Sala en mantenimiento' :
                               'Clic para seleccionar como inicio';

        if (!ocupado && !pasada && !mantto) {
          slot.setAttribute('role', 'button');
          slot.setAttribute('tabindex', '0');
          slot.setAttribute('aria-pressed', 'false');
          const alternarHora = () => {
            if (horasSeleccionadas.has(hStr)) {
              horasSeleccionadas.delete(hStr); // segundo clic: deselecciona exactamente esa hora
            } else {
              horasSeleccionadas.add(hStr);
            }
            actualizarSeleccionGrilla();
          };
          slot.addEventListener('click', alternarHora);
          slot.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); alternarHora(); } });
        }
        slots.appendChild(slot);
      }
      // Si el cliente llegó desde la tabla general, preseleccionar la hora pulsada.
      const paramsReserva = new URLSearchParams(window.location.search);
      const horaInicial = paramsReserva.get('hora');
      if (horaInicial) {
        const slotInicial = Array.from(slots.querySelectorAll('.hora-slot.libre')).find(el => el.dataset.hora === horaInicial);
        if (slotInicial) slotInicial.click();
      }
    })
    .catch(err => {
      console.error('Error cargando horario:', err);
      slots.innerHTML = '<div class="alert alert-warning py-2 w-100" style="font-size:.84rem">⚠️ No se pudo cargar el horario. Revisa tu conexión y recarga la página.</div>';
    });
}

/* ── Helpers de errores inline ──────────────────────────────── */
function mostrarError(elId, msg) {
  const el = document.getElementById(elId);
  if (!el) return;
  el.textContent = '⚠️ ' + msg;
  el.style.display = 'block';
}
function limpiarErrores() {
  ['errSala','errFecha','errHoraIni','errHoraFin'].forEach(id => {
    const el = document.getElementById(id);
    if (el) { el.textContent = ''; el.style.display = 'none'; }
  });
}

/* ── Términos ───────────────────────────────────────────────── */
function abrirTerminos() {
  limpiarErrores();

  const sala  = document.getElementById('salaSelect').value;
  const fecha = document.getElementById('fechaInput').value;
  const hi    = document.getElementById('hIni').value;
  const hf    = document.getElementById('hFin').value;

  let hayError = false;

  if (!sala)  { mostrarError('errSala',   'Selecciona una sala.');              document.getElementById('salaSelect').focus(); hayError = true; }
  if (!fecha) { mostrarError('errFecha',  'Selecciona una fecha.');             if (!hayError) document.getElementById('fechaInput').focus(); hayError = true; }
  if (!hi)    { mostrarError('errHoraIni','Selecciona la hora de inicio.');     if (!hayError) document.getElementById('hIni').focus(); hayError = true; }
  if (!hf)    { mostrarError('errHoraFin','Selecciona la hora de fin.');        if (!hayError) document.getElementById('hFin').focus(); hayError = true; }

  if (hayError) return;

  if (hi >= hf) {
    mostrarError('errHoraFin', 'La hora de fin debe ser mayor a la hora de inicio.');
    document.getElementById('hFin').focus();
    return;
  }

  if (salaEnMantenimiento) {
    mostrarError('errSala', 'Esta sala está en mantenimiento y no puede reservarse.');
    return;
  }

  // Verificar que las horas seleccionadas no estén ocupadas o pasadas
  for (let h = parseInt(hi); h < parseInt(hf); h++) {
    const hStr = (h < 10 ? '0' : '') + h + ':00';
    if (estaOcupado(hStr)) {
      mostrarError('errHoraIni', 'Conflicto: la hora ' + hStr + ' ya está ocupada. Elige otro horario.');
      return;
    }
    if (estaHoraPasada(hStr)) {
      mostrarError('errHoraIni', 'La hora ' + hStr + ' ya pasó para hoy. Elige un horario futuro.');
      return;
    }
  }

  // Resetear checkbox y abrir modal
  document.getElementById('chkTerminos').checked = false;
  document.getElementById('btnContinuar').disabled = true;
  new bootstrap.Modal(document.getElementById('modalTerminos')).show();
}

function submitReserva() {
  bootstrap.Modal.getInstance(document.getElementById('modalTerminos')).hide();
  document.getElementById('frmReserva').submit();
}

/* ── Eventos ────────────────────────────────────────────────── */
document.getElementById('salaSelect').addEventListener('change', () => { cargarGrilla(); actualizarResumen(); });
document.getElementById('fechaInput').addEventListener('keydown', e => e.preventDefault());
document.getElementById('fechaInput').addEventListener('change', () => { const f=document.getElementById('fechaInput'); const max=new Date(HOY_SRV+'T00:00:00'); max.setMonth(max.getMonth()+1); const mx=semFmtDate(max); if(f.value<HOY_SRV) f.value=HOY_SRV; if(f.value>mx) f.value=mx; cargarGrilla(); actualizarResumen(); });
document.querySelectorAll('.hora-controlada').forEach(el => el.addEventListener('mousedown', e => e.preventDefault()));
document.getElementById('hIni').addEventListener('change', actualizarResumen);
document.getElementById('hFin').addEventListener('change', actualizarResumen);

/* ── Init ───────────────────────────────────────────────────── */
window.addEventListener('DOMContentLoaded', () => {
  actualizarResumen();
  if (document.getElementById('salaSelect').value) cargarGrilla();
  semIniciarLunes(); // prepara el lunes de la semana actual para la vista semanal
});

/* ═══════════════════════════════════════════════════════════════
   VISTA SEMANAL
═══════════════════════════════════════════════════════════════ */
let semLunes = null; // Date del lunes de la semana visible

function semIniciarLunes() {
  const hoy = new Date(HOY_SRV + 'T00:00:00');
  const dow  = hoy.getDay() === 0 ? 7 : hoy.getDay(); // 1=lun…7=dom
  semLunes = new Date(hoy);
  semLunes.setDate(hoy.getDate() - (dow - 1));
  actualizarRangoLabel();
}

function semFmtDate(d) {
  return d.getFullYear() + '-' +
    String(d.getMonth()+1).padStart(2,'0') + '-' +
    String(d.getDate()).padStart(2,'0');
}

function semFmtCorto(d) {
  const dias = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
  return dias[d.getDay()] + ' ' + d.getDate() + '/' + (d.getMonth()+1);
}

function actualizarRangoLabel() {
  if (!semLunes) return;
  const fin = new Date(semLunes);
  fin.setDate(semLunes.getDate() + 6);
  document.getElementById('semRangoLabel').textContent =
    semFmtCorto(semLunes) + ' – ' + semFmtCorto(fin);
}

function semNavegar(delta) {
  if (!semLunes) semIniciarLunes();
  const propuesta = new Date(semLunes); propuesta.setDate(propuesta.getDate() + delta * 7);
  const hoy = new Date(HOY_SRV+'T00:00:00'); const max = new Date(HOY_SRV+'T00:00:00'); max.setMonth(max.getMonth()+1);
  const finPropuesto = new Date(propuesta); finPropuesto.setDate(finPropuesto.getDate()+6);
  if (finPropuesto < hoy || propuesta > max) return;
  semLunes.setTime(propuesta.getTime());
  actualizarRangoLabel();
  cargarSemana();
}

function setVista(v) {
  const diaCard  = document.querySelector('.card');     // primera card = form
  const semSec   = document.getElementById('vistaSemanal');
  const btnDia   = document.getElementById('btnVistaDia');
  const btnSem   = document.getElementById('btnVistaSem');
  if (v === 'semana') {
    semSec.style.display  = 'block';
    btnSem.className      = 'btn btn-primary';
    btnDia.className      = 'btn btn-outline-primary';
    if (!semLunes) semIniciarLunes();
    // Sincronizar sala semanal con la seleccionada en el form
    const frmSala = document.getElementById('salaSelect').value;
    if (frmSala) document.getElementById('semSalaSelect').value = frmSala;
    if (document.getElementById('semSalaSelect').value) cargarSemana();
  } else {
    semSec.style.display  = 'none';
    btnDia.className      = 'btn btn-primary';
    btnSem.className      = 'btn btn-outline-primary';
  }
}

function cargarSemana() {
  const salaId = document.getElementById('semSalaSelect').value;
  const wrap   = document.getElementById('semTablaWrap');
  if (!salaId) { wrap.innerHTML = '<div class="text-center text-muted py-4" style="font-size:.87rem">Selecciona una sala.</div>'; return; }
  if (!semLunes) semIniciarLunes();
  wrap.innerHTML = '<div class="text-center text-muted py-4">⏳ Cargando…</div>';
  fetch(`${BASE}/index.php?r=client/horario-semana&sala=${salaId}&lunes=${semFmtDate(semLunes)}`)
    .then(r => r.json())
    .then(data => renderSemana(data, wrap))
    .catch(() => { wrap.innerHTML = '<div class="alert alert-warning py-2">⚠️ No se pudo cargar. Recarga la página.</div>'; });
}

function renderSemana(data, wrap) {
  const HORAS = [8,9,10,11,12,13,14,15,16,17,18,19,20,21,22];
  const mapa  = data.mapa || {};
  const dias  = data.dias || [];
  const hoySrv  = data.hoySrv  || HOY_SRV;
  const horaSrv = data.horaSrv !== undefined ? data.horaSrv : HORA_ACT_SRV;
  const activa  = data.sala ? data.sala.activa : true;

  let html = `<table class="table mb-0 text-center" style="min-width:600px;font-size:.75rem">
    <thead><tr>
      <th style="padding:.5rem .6rem;min-width:48px">Hora</th>`;
  dias.forEach(f => {
    const d   = new Date(f + 'T00:00:00');
    const lbl = semFmtCorto(d);
    const esHoy = f === hoySrv;
    html += `<th style="padding:.5rem .4rem;${esHoy?'background:var(--primary);color:var(--on-primary)':''}">${lbl}${esHoy?' <span style="font-size:.6rem">(hoy)</span>':''}</th>`;
  });
  html += '</tr></thead><tbody>';

  HORAS.forEach(h => {
    html += `<tr><td style="font-weight:600;padding:.4rem .5rem;background:var(--bg-secondary)">${String(h).padStart(2,'0')}:00</td>`;
    dias.forEach(f => {
      const ocupado = mapa[f] && mapa[f][h];
      const pasada  = f < hoySrv || (f === hoySrv && h <= horaSrv);
      let bg, title, clic = '';
      if (!activa || pasada) {
        bg    = 'slot-pasado slot-off';
        title = pasada ? 'Hora pasada' : 'Sala en mantenimiento';
      } else if (ocupado) {
        bg    = 'slot-ocupado slot-off';
        title = 'Ocupado';
      } else {
        bg    = 'slot-libre slot-on';
        title = 'Libre — clic para preseleccionar';
        clic  = `onclick="semSeleccionarSlot('${f}','${String(h).padStart(2,'0')}:00')"`;
      }
      html += `<td class="${bg}" style="padding:.3rem .25rem;font-size:.7rem" title="${title}" ${clic}>${ocupado?'✖':(pasada?'—':'✔')}</td>`;
    });
    html += '</tr>';
  });
  html += '</tbody></table>';
  wrap.innerHTML = html;
}

function semSeleccionarSlot(fecha, horaIni) {
  // Pre-rellenar el form de reserva y cambiar a vista diaria
  const frmFecha = document.getElementById('fechaInput');
  const frmSala  = document.getElementById('salaSelect');
  const semSala  = document.getElementById('semSalaSelect').value;

  frmFecha.value = fecha;
  if (semSala) frmSala.value = semSala;
  document.getElementById('hIni').value = horaIni;
  const hNum = parseInt(horaIni);
  if (hNum < 23) document.getElementById('hFin').value = String(hNum+1).padStart(2,'0')+':00';

  setVista('dia');
  cargarGrilla();
  actualizarResumen();
  // Scroll al formulario
  document.querySelector('.card').scrollIntoView({behavior:'smooth', block:'start'});
}
</script>
</body>
</html>
