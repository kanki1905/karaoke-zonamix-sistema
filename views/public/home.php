<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Karaoke Zona Mix — La Paz</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/nav.php'; ?>

<!-- HERO -->
<section class="hero">
  <div class="container hero-content">
    <h1>🎤 Canta, Ríe y Disfruta</h1>
    <p>Reserva tu sala de karaoke en el mejor local de La Paz.
       Equipos profesionales, ambiente increíble y precios accesibles.</p>
    <?php if (isLogged()): ?>
      <a href="<?= BASE_URL ?>/index.php?r=client/reservar" class="hero-btn">Reservar Ahora</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/index.php?r=auth/login" class="hero-btn">Iniciar Sesión</a>
      <a href="<?= BASE_URL ?>/index.php?r=auth/registro" class="hero-btn-outline">Regístrate Gratis</a>
    <?php endif; ?>
  </div>
</section>

<!-- FEATURES -->
<section class="features-section">
  <div class="container">
    <h2 class="section-title">¿Por qué elegirnos?</h2>
    <div class="section-divider"></div>
    <div class="row g-4">
      <div class="col-md-3 col-sm-6">
        <div class="feature-card">
          <div class="feature-icon">🎵</div>
          <div class="feature-title">+50,000 canciones</div>
          <div class="feature-desc">Catálogo actualizado en español, inglés y más géneros.</div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="feature-card">
          <div class="feature-icon">🔊</div>
          <div class="feature-title">Sonido profesional</div>
          <div class="feature-desc">Equipos de alta calidad para una experiencia única.</div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="feature-card">
          <div class="feature-icon">📱</div>
          <div class="feature-title">Reserva online</div>
          <div class="feature-desc">Reserva tu sala desde tu celular en segundos.</div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="feature-card">
          <div class="feature-icon">💳</div>
          <div class="feature-title">Pago flexible</div>
          <div class="feature-desc">Efectivo, transferencia o QR. Tú eliges cómo pagar.</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SALAS DESTACADAS -->
<section style="padding:3rem 0;background:var(--bg-secondary)">
  <div class="container">
    <h2 class="section-title">Nuestras Salas</h2>
    <p class="section-subtitle">Elige la sala perfecta para tu grupo</p>
    <div class="section-divider"></div>
    <div class="row g-4">
      <?php foreach (array_slice($salas, 0, 6) as $s): ?>
      <div class="col-md-4 col-sm-6">
        <div class="sala-card">
          <?php if (!empty($s['imagen'])): ?>
            <img src="<?= UPLOAD_URL ?>/salas/<?= htmlspecialchars($s['imagen']) ?>"
                 alt="<?= htmlspecialchars($s['nombre']) ?>">
          <?php else: ?>
            <div style="height:180px;background:var(--primary);
                        display:flex;align-items:center;justify-content:center;font-size:3rem">🎤</div>
          <?php endif; ?>
          <div class="sala-card-body">
            <div class="sala-card-title">
              <?= htmlspecialchars($s['nombre']) ?>
              <?php if (!empty($s['numero'])): ?>
                <span style="font-size:.75rem;font-weight:600;color:var(--primary);margin-left:4px">#<?= (int)$s['numero'] ?></span>
              <?php endif; ?>
            </div>
            <p style="font-size:.82rem;color:var(--text-muted);margin:.25rem 0 .4rem">
              👥 Hasta <?= $s['capacidad'] ?> personas
            </p>
            <?php if (!empty($s['descripcion'])): ?>
            <p style="font-size:.81rem;color:var(--text-muted);margin:0 0 .6rem;
                      display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
              <?= htmlspecialchars($s['descripcion']) ?>
            </p>
            <?php endif; ?>
            <div class="sala-card-price">
              <?= precio((int)$s['precio_hora']) ?> <span>/ hora</span>
            </div>
            <a href="<?= BASE_URL ?>/index.php?r=<?= isLogged() ? 'client/reservar&sala='.$s['id'] : 'auth/login' ?>"
               class="btn btn-primary btn-sm w-100 mt-3">
              <?= isLogged() ? 'Reservar' : 'Iniciar Sesión para Reservar' ?>
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4 d-flex gap-2 justify-content-center flex-wrap">
      <?php if (count($salas) > 6): ?>
      <a href="<?= BASE_URL ?>/index.php?r=public/salas" class="btn btn-outline-primary">
        Ver todas las salas (<?= count($salas) ?>)
      </a>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>/index.php?r=public/menu" class="btn btn-outline-primary">🍔 Ver el menú de productos</a>
    </div>
  </div>
</section>

<!-- ════ TABLA DE DISPONIBILIDAD ════════════════════════════════ -->
<section id="disponibilidad" style="padding:3rem 0;background:var(--bg-primary)">
  <div class="container">
    <h2 class="section-title">📅 Disponibilidad de Salas</h2>
    <p class="section-subtitle">Consulta qué salas están libres para el día que elijas</p>
    <div class="section-divider"></div>

    <!-- Controles -->
    <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
      <div class="d-flex align-items-center gap-2">
        <label for="pickerFecha" class="fw-semibold mb-0" style="white-space:nowrap">📆 Fecha:</label>
        <input type="date" id="pickerFecha" class="form-control form-control-sm" style="max-width:180px"
               value="<?= $hoy ?>" min="<?= $hoy ?>">
      </div>
      <div class="d-flex align-items-center gap-2" style="font-size:.82rem;color:var(--text-muted)">
        <span style="display:inline-flex;align-items:center;gap:5px">
          <span style="width:16px;height:16px;border-radius:4px;background:var(--success-bg);border:1px solid var(--success-border);display:inline-block"></span>Libre
        </span>
        <span style="display:inline-flex;align-items:center;gap:5px">
          <span style="width:16px;height:16px;border-radius:4px;background:var(--danger-bg);border:1px solid var(--danger-border);display:inline-block"></span>Ocupado
        </span>
        <span style="display:inline-flex;align-items:center;gap:5px">
          <span style="width:16px;height:16px;border-radius:4px;background:var(--warning-bg);border:1px solid var(--warning-border);display:inline-block"></span>Mantenimiento
        </span>
        <span style="display:inline-flex;align-items:center;gap:5px">
          <span style="width:16px;height:16px;border-radius:4px;background:var(--surface-sunken);display:inline-block"></span>Pasado
        </span>
      </div>
      <div id="dispCargando" style="display:none;font-size:.82rem;color:var(--text-muted)">⏳ Cargando…</div>
    </div>

    <!-- Tabla (generada en PHP para la fecha de hoy, luego JS la actualiza) -->
    <div id="dispTablaWrap" class="table-responsive" style="border-radius:12px;overflow:hidden;
         box-shadow:0 4px 16px var(--shadow-color)">
      <?php
        // Construir mapa de ocupados: sala_id → [hora_int => true]
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
      <table class="table mb-0 text-center" style="min-width:700px;font-size:.83rem">
        <thead>
          <tr style="background:var(--primary);color:var(--on-primary)">
            <th style="text-align:left;padding:.65rem 1rem;min-width:130px">Sala</th>
            <?php foreach ($horas as $h): ?>
              <th style="padding:.65rem .4rem;font-size:.78rem"><?= sprintf('%02d:00', $h) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($todasSalas as $s): ?>
          <tr>
            <td style="text-align:left;padding:.65rem 1rem;font-weight:600;
                background:var(--bg-secondary);border-right:2px solid var(--border)">
              <?= htmlspecialchars($s['nombre']) ?>
              <?php if (!$s['activa']): ?>
                <span class="badge bg-warning" style="font-size:.65rem;margin-left:4px">Mant.</span>
              <?php endif; ?>
            </td>
            <?php foreach ($horas as $h): ?>
              <?php if ($h <= $horaActual): ?>
                <td style="background:var(--surface-sunken);color:var(--text-muted);font-size:.65rem" title="Hora pasada">—</td>
              <?php elseif (!$s['activa']): ?>
                <td style="background:var(--warning-bg);color:var(--warning-text);font-size:.65rem" title="Mantenimiento">🔧</td>
              <?php elseif (!empty($ocupadosMapa[$s['id']][$h])): ?>
                <td style="background:var(--danger-bg);color:var(--danger-text);font-size:.65rem" title="Ocupado">✖</td>
              <?php else: ?>
                <td style="background:var(--success-bg);color:var(--success-text);font-size:.65rem;padding:0" title="Disponible: clic para reservar"><a class="disp-slot-link" href="<?= BASE_URL ?>/index.php?r=client/reservar&sala=<?= (int)$s['id'] ?>&fecha=<?= htmlspecialchars($hoy) ?>&hora=<?= sprintf('%02d:00',$h) ?>" style="display:flex;align-items:center;justify-content:center;min-height:34px;color:var(--success-text);text-decoration:none;font-weight:800" aria-label="Reservar <?= htmlspecialchars($s['nombre']) ?> a las <?= sprintf('%02d:00',$h) ?>">✔</a></td>
              <?php endif; ?>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div><!-- /table-wrap -->

    <?php if (isLogged()): ?>
    <div class="text-center mt-3">
      <a href="<?= BASE_URL ?>/index.php?r=client/reservar" class="btn btn-primary">Reservar una sala →</a>
    </div>
    <?php else: ?>
    <div class="text-center mt-3" style="font-size:.88rem;color:var(--text-muted)">
      <a href="<?= BASE_URL ?>/index.php?r=auth/login">Inicia sesión</a> para hacer una reserva.
    </div>
    <?php endif; ?>
  </div>
</section>

<script>
(function(){
  const BASE       = '<?= BASE_URL ?>';
  const picker     = document.getElementById('pickerFecha');
  const wrap       = document.getElementById('dispTablaWrap');
  const cargando   = document.getElementById('dispCargando');
  const HORAS      = [8,9,10,11,12,13,14,15,16,17,18,19,20,21,22];
  const isLogged   = <?= isLogged() ? 'true' : 'false' ?>;
  const HOY_STR    = '<?= $hoy ?>';
  const HORA_ACT   = <?= (int)date('H') ?>;

  function pad(n){ return String(n).padStart(2,'0'); }

  function buildTabla(salas, ocupados, fecha) {
    // mapa sala_id → { hora => true }
    const mapa = {};
    ocupados.forEach(o => {
      const ini = parseInt(o.hora_inicio.substring(0,2));
      const fin = parseInt(o.hora_fin.substring(0,2));
      if (!mapa[o.sala_id]) mapa[o.sala_id] = {};
      for (let h = ini; h < fin; h++) mapa[o.sala_id][h] = true;
    });

    let html = `<table class="table mb-0 text-center" style="min-width:700px;font-size:.83rem">
      <thead><tr style="background:var(--primary);color:var(--on-primary)">
        <th style="text-align:left;padding:.65rem 1rem;min-width:130px">Sala</th>`;
    HORAS.forEach(h => {
      html += `<th style="padding:.65rem .4rem;font-size:.78rem">${pad(h)}:00</th>`;
    });
    html += '</tr></thead><tbody>';

    salas.forEach(s => {
      html += `<tr><td style="text-align:left;padding:.65rem 1rem;font-weight:600;
        background:var(--bg-secondary);border-right:2px solid var(--border)">
        ${s.nombre}${!parseInt(s.activa)?'<span class="badge bg-warning" style="font-size:.65rem;margin-left:4px">Mant.</span>':''}
      </td>`;
      HORAS.forEach(h => {
        if (fecha === HOY_STR && h <= HORA_ACT) {
          html += `<td style="background:var(--surface-sunken);color:var(--text-muted);font-size:.65rem" title="Hora pasada">—</td>`;
        } else if (!parseInt(s.activa)) {
          html += `<td style="background:var(--warning-bg);color:var(--warning-text);font-size:.65rem" title="Mantenimiento">🔧</td>`;
        } else if (mapa[s.id] && mapa[s.id][h]) {
          html += `<td style="background:var(--danger-bg);color:var(--danger-text);font-size:.65rem" title="Ocupado">✖</td>`;
        } else {
          html += `<td style="background:var(--success-bg);color:var(--success-text);font-size:.65rem;padding:0" title="Disponible: clic para reservar"><a class="disp-slot-link" href="<?= BASE_URL ?>/index.php?r=client/reservar&sala=<?= (int)$s['id'] ?>&fecha=<?= htmlspecialchars($hoy) ?>&hora=<?= sprintf('%02d:00',$h) ?>" style="display:flex;align-items:center;justify-content:center;min-height:34px;color:var(--success-text);text-decoration:none;font-weight:800" aria-label="Reservar <?= htmlspecialchars($s['nombre']) ?> a las <?= sprintf('%02d:00',$h) ?>">✔</a></td>`;
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
    cargando.style.display = 'inline';
    fetch(`${BASE}/index.php?r=public/disponibilidad&fecha=${fecha}`)
      .then(r => r.json())
      .then(data => {
        wrap.innerHTML = buildTabla(data.salas, data.ocupados, fecha);
        cargando.style.display = 'none';
      })
      .catch(() => { cargando.style.display = 'none'; });
  });
})();
</script>

<!-- CTA -->
<section style="padding:4rem 0;background:var(--hero-bg);text-align:center;color:var(--on-hero)">
  <div class="container">
    <h2 style="font-size:2rem;font-weight:800;margin-bottom:1rem">¿Listo para cantar?</h2>
    <p style="color:var(--on-hero-muted);max-width:500px;margin:0 auto 2rem">
      Únete a cientos de familias y amigos que ya disfrutan de Karaoke Zona Mix.
    </p>
    <?php if (!isLogged()): ?>
      <a href="<?= BASE_URL ?>/index.php?r=auth/registro" class="hero-btn me-2">Crear cuenta gratis</a>
      <a href="<?= BASE_URL ?>/index.php?r=auth/login" class="hero-btn-outline">Iniciar Sesión</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/index.php?r=client/reservar" class="hero-btn">Hacer una reserva ahora</a>
    <?php endif; ?>
  </div>
</section>

<?php require ROOT_PATH . '/views/partials/footer.php'; ?>
</body>
</html>
