<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reservas — Admin</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
  <style>
    .filter-bar { background:var(--bg-secondary); border:1px solid var(--border); border-radius:12px; padding:1rem 1.25rem; margin-bottom:1.25rem; }
    .filter-bar .form-label { font-size:.78rem; font-weight:600; color:var(--text-muted); margin-bottom:.25rem; }
    .badge-metodo { font-size:.72rem; padding:.25em .6em; border-radius:50px; font-weight:600; }
    .badge-efectivo      { background:var(--warning-bg); color:var(--warning-text); }
    .badge-transferencia { background:var(--info-bg); color:var(--info-text); }
  </style>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/admin_sidebar.php'; ?>
<div class="admin-content">
  <div class="admin-topbar"><h4>📅 Reservas del día</h4></div>

  <?php if ($ok = flash('success')): ?>
    <div class="alert alert-success"><?= htmlspecialchars($ok) ?></div>
  <?php endif; ?>
  <?php if ($err = flash('error')): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <!-- ── Barra de filtros ── -->
  <form method="GET" action="<?= BASE_URL ?>/index.php" class="filter-bar">
    <input type="hidden" name="r" value="admin/reservas">
    <div class="row g-2 align-items-end">
      <div class="col-sm-3 col-6">
        <label class="form-label">Método de pago</label>
        <select name="metodo" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="efectivo"      <?= $fMetodo==='efectivo'      ?'selected':'' ?>>💵 Efectivo</option>
          <option value="transferencia" <?= $fMetodo==='transferencia' ?'selected':'' ?>>🏦 Transferencia</option>
        </select>
      </div>
      <div class="col-sm-2 col-6">
        <label class="form-label">Día de reservas (solo fechas hasta hoy)</label>
        <div class="input-group input-group-sm"><button type="button" class="btn btn-outline-secondary" id="diaAnterior" title="Día anterior">‹</button><input type="date" max="<?= date('Y-m-d') ?>" name="fecha" id="fechaReservas" class="form-control form-control-sm"
               value="<?= htmlspecialchars($fFecha) ?>"><button type="button" class="btn btn-outline-secondary" id="diaSiguiente" title="Día siguiente">›</button></div>
      </div>
      <div class="col-sm-3 col-6">
        <label class="form-label">Sala</label>
        <select name="sala_id" class="form-select form-select-sm">
          <option value="">Todas las salas</option>
          <?php foreach ($salas as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $fSalaId===$s['id']?'selected':'' ?>>
              <?= htmlspecialchars($s['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-2 col-6">
        <label class="form-label">Cliente / correo</label>
        <input type="text" name="usuario" class="form-control form-control-sm"
               value="<?= htmlspecialchars($fUsuario) ?>" placeholder="Buscar…">
      </div>
      <div class="col-sm-2 col-12 d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm flex-fill">🔍 Filtrar</button>
        <?php if ($fMetodo || $fFecha || $fSalaId || $fUsuario): ?>
          <a href="<?= BASE_URL ?>/index.php?r=admin/reservas" class="btn btn-outline-secondary btn-sm">✕</a>
        <?php endif; ?>
      </div>
    </div>
    <?php if ($fMetodo || $fFecha || $fSalaId || $fUsuario): ?>
      <div class="mt-2" style="font-size:.78rem;color:var(--text-muted)">
        📊 <?= count($reservas) ?> resultado<?= count($reservas)!==1?'s':'' ?> encontrado<?= count($reservas)!==1?'s':'' ?>
      </div>
    <?php endif; ?>
  </form>

  <!-- ── Tabla ── -->
  <div class="mb-3"><input type="search" class="form-control filtro-tabla" data-tabla="tablaReservas" placeholder="Buscar reservas por cliente, sala o estado…" aria-label="Buscar reservas por cliente, sala o estado…"></div>
  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="tablaReservas" class="table mb-0">
          <thead>
            <tr>
              <th>#</th><th>Cliente</th><th>Sala</th><th>Fecha</th><th>Horario</th>
              <th>Total</th><th>Método</th><th>Estado</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($reservas)): ?>
            <tr><td colspan="9" class="text-center text-muted py-4">No hay reservas con esos filtros.</td></tr>
          <?php else: ?>
          <?php foreach ($reservas as $r): ?>
          <tr>
            <td class="text-muted"><?= $r['id'] ?></td>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars($r['usuario_nombre']) ?></div>
              <small class="text-muted"><?= htmlspecialchars($r['usuario_email']) ?></small>
            </td>
            <td><?= htmlspecialchars($r['sala_nombre']) ?></td>
            <td data-fecha="<?= htmlspecialchars($r['fecha']) ?>"><?= date('d/m/Y', strtotime($r['fecha'])) ?></td>
            <td data-hora="<?= substr($r['hora_inicio'],0,5) ?>"><?= substr($r['hora_inicio'],0,5) ?>–<?= substr($r['hora_fin'],0,5) ?></td>
            <td class="fw-bold text-brand"><?= precio((int)$r['total']) ?></td>
            <td>
              <span class="badge-metodo badge-<?= $r['metodo_pago'] ?>">
                <?= $r['metodo_pago']==='efectivo'?'💵':'🏦' ?>
                <?= ucfirst($r['metodo_pago']) ?>
              </span>
            </td>
            <td><span class="badge-estado estado-<?= $r['estado'] ?>"><?= ucfirst($r['estado']) ?></span></td>
            <td>
              <div class="d-flex gap-1 flex-wrap">
                <a href="<?= BASE_URL ?>/index.php?r=admin/reserva-detalle&id=<?= $r['id'] ?>"
                   class="btn btn-sm btn-outline-primary">🔍 Ver</a>
                <?php if ($r['estado'] === 'pendiente'): ?>
                <form action="<?= BASE_URL ?>/index.php?r=admin/reserva-estado" method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="estado" value="confirmada"><button class="btn btn-sm btn-success" onclick="return confirm('¿Confirmar reserva?')">✔ Confirmar</button></form>
                <?php endif; ?>
                <?php if (in_array($r['estado'],['pendiente','confirmada'])): ?>
                <form action="<?= BASE_URL ?>/index.php?r=admin/reserva-estado" method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="estado" value="cancelada"><button class="btn btn-sm btn-danger" onclick="return confirm('<?= $r['estado']==='pendiente' ? '¿Rechazar esta reserva?' : '¿Cancelar esta reserva?' ?>')">✖ <?= $r['estado']==='pendiente' ? 'Rechazar' : 'Cancelar' ?></button></form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
<script>
let dtReservas=null; if(window.DataTable){const t=document.getElementById('tablaReservas'); if(t) dtReservas=new DataTable(t,{pageLength:10,lengthMenu:[10,25,50,100],order:[[3,'desc'],[4,'desc'],[0,'desc']],language:{url:'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json'},columnDefs:[{orderable:false,targets:-1}]});} function filtrarReservasAdmin(){const q=document.querySelector('.filtro-tabla')?.value||'';if(dtReservas)dtReservas.search(q).draw();}document.querySelectorAll('.filtro-tabla').forEach(inp=>inp.addEventListener('input',filtrarReservasAdmin));
const fechaDia=document.getElementById('fechaReservas'), hoyISO='<?= date('Y-m-d') ?>';
function moverDia(delta){if(!fechaDia)return;const d=new Date(fechaDia.value+'T12:00:00');d.setDate(d.getDate()+delta);const iso=[d.getFullYear(),String(d.getMonth()+1).padStart(2,'0'),String(d.getDate()).padStart(2,'0')].join('-');if(iso>hoyISO)return;fechaDia.value=iso;fechaDia.form.requestSubmit();}
document.getElementById('diaAnterior')?.addEventListener('click',()=>moverDia(-1));document.getElementById('diaSiguiente')?.addEventListener('click',()=>moverDia(1));fechaDia?.addEventListener('change',()=>{if(fechaDia.value>hoyISO)fechaDia.value=hoyISO;});
</script>
</body></html>
