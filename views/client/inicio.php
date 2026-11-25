<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mi Inicio — Karaoke Zona Mix</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/nav.php'; ?>

<div class="container" style="padding:2.5rem 0">

  <?php if ($ok = flash('success')): ?>
    <div class="alert alert-success"><?= htmlspecialchars($ok) ?></div>
  <?php endif; ?>

  <!-- Bienvenida -->
  <div class="card mb-4" style="background:var(--hero-bg);border:none">
    <div class="card-body d-flex align-items-center gap-3" style="color:var(--on-hero);padding:1.5rem">
      <div style="font-size:2.5rem">👋</div>
      <div>
        <h4 style="margin:0;font-weight:800">
          ¡Hola, <?= htmlspecialchars($_SESSION['user']['nombre']) ?>!
        </h4>
        <p style="margin:0;color:var(--on-hero-muted);font-size:.92rem">
          Bienvenido/a a tu panel. ¿Listo/a para cantar hoy?
        </p>
      </div>
      <a href="<?= BASE_URL ?>/index.php?r=client/reservar"
         class="btn ms-auto"
         style="background:var(--surface);color:var(--text-link);font-weight:700;border-radius:50px;padding:.6rem 1.5rem">
        + Nueva Reserva
      </a>
    </div>
  </div>

  <div class="row g-4">
    <!-- Mis últimas reservas -->
    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header">📅 Mis Últimas Reservas</div>
        <div class="card-body p-0">
          <?php if (empty($reservas)): ?>
            <div class="text-center py-5 text-muted">
              <div style="font-size:3rem">🎤</div>
              <p class="mt-2">Aún no tienes reservas.</p>
              <a href="<?= BASE_URL ?>/index.php?r=client/reservar" class="btn btn-primary btn-sm">Hacer mi primera reserva</a>
            </div>
          <?php else: ?>
          <div class="table-responsive">
            <table class="table mb-0">
              <thead>
                <tr><th>Sala</th><th>Fecha</th><th>Horario</th><th>Estado</th></tr>
              </thead>
              <tbody>
                <?php foreach ($reservas as $r): ?>
                <tr>
                  <td class="fw-semibold"><?= htmlspecialchars($r['sala_nombre']) ?></td>
                  <td><?= date('d/m/Y', strtotime($r['fecha'])) ?></td>
                  <td><?= substr($r['hora_inicio'],0,5) ?> – <?= substr($r['hora_fin'],0,5) ?></td>
                  <td>
                    <span class="badge-estado estado-<?= $r['estado'] ?>">
                      <?= ucfirst($r['estado']) ?>
                    </span>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="text-center py-2">
            <a href="<?= BASE_URL ?>/index.php?r=client/mis-reservas" style="font-size:.88rem">Ver todas →</a>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Salas disponibles -->
    <div class="col-lg-5">
      <div class="card h-100">
        <div class="card-header">🏠 Salas Disponibles</div>
        <div class="card-body p-0">
          <div class="list-group list-group-flush">
            <?php foreach (array_slice($salas, 0, 6) as $s): ?>
            <a href="<?= BASE_URL ?>/index.php?r=client/reservar&sala=<?= $s['id'] ?>"
               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 px-4">
              <div>
                <div class="fw-semibold" style="color:var(--text-primary)">
                  <?= htmlspecialchars($s['nombre']) ?>
                  <?php if (!empty($s['numero'])): ?>
                    <span style="font-size:.72rem;color:var(--primary);font-weight:600"> #<?= (int)$s['numero'] ?></span>
                  <?php endif; ?>
                </div>
                <small class="text-muted">👥 hasta <?= $s['capacidad'] ?> personas</small>
              </div>
              <span class="badge" style="background:var(--primary);border-radius:50px">
                <?= precio((int)$s['precio_hora']) ?>/h
              </span>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ════ TABLA DE DISPONIBILIDAD ════════════════════════════ -->
  <div class="mt-4">
    <h5 class="fw-bold mb-1" style="color:var(--text-primary)">📅 Disponibilidad de Salas</h5>
    <p class="text-muted mb-3" style="font-size:.87rem">Consulta qué salas están libres para el día que elijas</p>

    <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
      <div class="d-flex align-items-center gap-2">
        <label for="pickerFechaC" class="fw-semibold mb-0" style="font-size:.88rem;white-space:nowrap">📆 Fecha:</label>
        <input type="date" id="pickerFechaC" class="form-control form-control-sm" style="max-width:175px"
               value="<?= $hoy ?>" min="<?= $hoy ?>">
      </div>
      <div class="d-flex align-items-center gap-2" style="font-size:.78rem;color:var(--text-muted)">
        <span><span style="display:inline-block;width:13px;height:13px;border-radius:3px;background:var(--success-bg);border:1px solid var(--success-border);vertical-align:middle;margin-right:3px"></span>Libre</span>
        <span><span style="display:inline-block;width:13px;height:13px;border-radius:3px;background:var(--danger-bg);border:1px solid var(--danger-border);vertical-align:middle;margin-right:3px"></span>Ocupado</span>
        <span><span style="display:inline-block;width:13px;height:13px;border-radius:3px;background:var(--warning-bg);border:1px solid var(--warning-border);vertical-align:middle;margin-right:3px"></span>Mantenimiento</span>
        <span><span style="display:inline-block;width:13px;height:13px;border-radius:3px;background:var(--surface-sunken);vertical-align:middle;margin-right:3px"></span>Pasado</span>
      </div>
      <span id="dispCargandoC" style="display:none;font-size:.8rem;color:var(--text-muted)">⏳ Cargando…</span>
    </div>

    <div id="dispTablaWrapC" class="table-responsive" style="border-radius:12px;overflow:hidden;
         box-shadow:0 4px 16px var(--shadow-color)">
      <?php
        $ocupadosMapa = [];
        foreach ($ocupadosHoy as $o) {
          $ini = (int)substr($o['hora_inicio'],0,2);
          $fin = (int)substr($o['hora_fin'],0,2);
          for ($h = $ini; $h < $fin; $h++) {
            $ocupadosMapa[$o['sala_id']][$h] = true;
          }
        }
        $horas = range(8, 22);
        $horaActual = (int)date('H');
      ?>
      <table class="table mb-0 text-center" style="min-width:700px;font-size:.82rem">
        <thead>
          <tr style="background:var(--primary);color:var(--on-primary)">
            <th style="text-align:left;padding:.6rem 1rem;min-width:130px">Sala</th>
            <?php foreach ($horas as $h): ?>
              <th style="padding:.6rem .35rem;font-size:.75rem"><?= sprintf('%02d:00', $h) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($todasSalas as $s): ?>
          <tr>
            <td style="text-align:left;padding:.6rem 1rem;font-weight:600;
                background:var(--bg-secondary);border-right:2px solid var(--border)">
              <?= htmlspecialchars($s['nombre']) ?>
              <?php if (!$s['activa']): ?>
                <span class="badge bg-warning" style="font-size:.62rem;margin-left:4px">Mant.</span>
              <?php endif; ?>
            </td>
            <?php foreach ($horas as $h): ?>
              <?php if ($h <= $horaActual): ?>
                <td style="background:var(--surface-sunken);color:var(--text-muted);font-size:.62rem" title="Hora pasada">—</td>
              <?php elseif (!$s['activa']): ?>
                <td style="background:var(--warning-bg);color:var(--warning-text);font-size:.62rem" title="Mantenimiento">🔧</td>
              <?php elseif (!empty($ocupadosMapa[$s['id']][$h])): ?>
                <td style="background:var(--danger-bg);color:var(--danger-text);font-size:.62rem" title="Ocupado">✖</td>
              <?php else: ?>
                <td style="background:var(--success-bg);color:var(--success-text);font-size:.62rem;padding:0" title="Disponible: clic para reservar">
                  <a href="<?= BASE_URL ?>/index.php?r=client/reservar&amp;sala=<?= (int)$s['id'] ?>&amp;fecha=<?= htmlspecialchars($hoy) ?>&amp;hora=<?= sprintf('%02d:00',$h) ?>"
                     style="display:flex;align-items:center;justify-content:center;min-height:34px;color:var(--success-text);text-decoration:none;font-weight:800"
                     aria-label="Reservar <?= htmlspecialchars($s['nombre']) ?> a las <?= sprintf('%02d:00',$h) ?>">✔</a>
                </td>
              <?php endif; ?>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script>
(function(){
  const BASE     = '<?= BASE_URL ?>';
  const picker   = document.getElementById('pickerFechaC');
  const wrap     = document.getElementById('dispTablaWrapC');
  const spinner  = document.getElementById('dispCargandoC');
  const HORAS    = [8,9,10,11,12,13,14,15,16,17,18,19,20,21,22];
  const HOY_STR  = '<?= $hoy ?>';
  const HORA_ACT = <?= (int)date('H') ?>;

  function pad(n){ return String(n).padStart(2,'0'); }

  function buildTabla(salas, ocupados, fecha) {
    const mapa = {};
    ocupados.forEach(o => {
      const ini = parseInt(o.hora_inicio.substring(0,2));
      const fin = parseInt(o.hora_fin.substring(0,2));
      if (!mapa[o.sala_id]) mapa[o.sala_id] = {};
      for (let h = ini; h < fin; h++) mapa[o.sala_id][h] = true;
    });

    let html = `<table class="table mb-0 text-center" style="min-width:700px;font-size:.82rem">
      <thead><tr style="background:var(--primary);color:var(--on-primary)">
        <th style="text-align:left;padding:.6rem 1rem;min-width:130px">Sala</th>`;
    HORAS.forEach(h => {
      html += `<th style="padding:.6rem .35rem;font-size:.75rem">${pad(h)}:00</th>`;
    });
    html += '</tr></thead><tbody>';

    salas.forEach(s => {
      html += `<tr><td style="text-align:left;padding:.6rem 1rem;font-weight:600;
        background:var(--bg-secondary);border-right:2px solid var(--border)">
        ${s.nombre}${!parseInt(s.activa)?'<span class="badge bg-warning" style="font-size:.62rem;margin-left:4px">Mant.</span>':''}
      </td>`;
      HORAS.forEach(h => {
        if (fecha === HOY_STR && h <= HORA_ACT) {
          html += `<td style="background:var(--surface-sunken);color:var(--text-muted);font-size:.62rem" title="Hora pasada">—</td>`;
        } else if (!parseInt(s.activa)) {
          html += `<td style="background:var(--warning-bg);color:var(--warning-text);font-size:.62rem" title="Mantenimiento">🔧</td>`;
        } else if (mapa[s.id] && mapa[s.id][h]) {
          html += `<td style="background:var(--danger-bg);color:var(--danger-text);font-size:.62rem" title="Ocupado">✖</td>`;
        } else {
          const href = `${BASE}/index.php?r=client/reservar&sala=${encodeURIComponent(s.id)}&fecha=${encodeURIComponent(fecha)}&hora=${pad(h)}:00`;
          html += `<td style="background:var(--success-bg);color:var(--success-text);font-size:.62rem;padding:0" title="Disponible: clic para reservar"><a href="${href}" style="display:flex;align-items:center;justify-content:center;min-height:34px;color:var(--success-text);text-decoration:none;font-weight:800" aria-label="Reservar ${String(s.nombre).replace(/[&<>"]/g,'')} a las ${pad(h)}:00">✔</a></td>`;
        }
      });
      html += '</tr>';
    });
    html += '</tbody></table>';
    return html;
  }

  picker.addEventListener('change', function(){
    const fecha = this.value;
    if (!fecha) return;
    spinner.style.display = 'inline';
    fetch(`${BASE}/index.php?r=public/disponibilidad&fecha=${fecha}`)
      .then(r => r.json())
      .then(data => {
        wrap.innerHTML = buildTabla(data.salas, data.ocupados, fecha);
        spinner.style.display = 'none';
      })
      .catch(() => { spinner.style.display = 'none'; });
  });
})();
</script>

<?php require ROOT_PATH . '/views/partials/footer.php'; ?>
</body>
</html>
