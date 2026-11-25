<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Admin — Karaoke Zona Mix</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/admin_sidebar.php'; ?>

<div class="admin-content">
  <div class="admin-topbar">
    <h4>📊 Dashboard</h4>
    <span class="text-muted" style="font-size:.88rem">
      👤 <?= htmlspecialchars($_SESSION['user']['nombre']) ?>
      &nbsp;|&nbsp; <?= date('d/m/Y H:i') ?>
    </span>
  </div>

  <!-- Stats -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
      <div class="stat-card">
        <div class="stat-icon blue">📅</div>
        <div><div class="stat-num"><?= $stats['reservas'] ?></div><div class="stat-lbl">Reservas total</div></div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <div class="stat-card">
        <div class="stat-icon red">⏳</div>
        <div><div class="stat-num"><?= $stats['pendientes'] ?></div><div class="stat-lbl">Pendientes</div></div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <div class="stat-card">
        <div class="stat-icon purple">👥</div>
        <div><div class="stat-num"><?= $stats['clientes'] ?></div><div class="stat-lbl">Clientes</div></div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <div class="stat-card">
        <div class="stat-icon teal">🏠</div>
        <div><div class="stat-num"><?= $stats['salas'] ?></div><div class="stat-lbl">Salas activas</div></div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <div class="stat-card">
        <div class="stat-icon green">💰</div>
        <div><div class="stat-num" style="font-size:1.1rem"><?= precio($stats['ingresosMes']) ?></div><div class="stat-lbl">Ingresos mes</div></div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <div class="stat-card">
        <div class="stat-icon red">📎</div>
        <div><div class="stat-num"><?= $stats['compPend'] ?></div><div class="stat-lbl">Comp. pendientes</div></div>
      </div>
    </div>
  </div>

  <!-- Gráficas -->
  <div class="row g-4 mb-4">
    <div class="col-md-8">
      <div class="card h-100">
        <div class="card-header">Reservas por mes</div>
        <div class="card-body"><div class="dashboard-chart dashboard-chart-bar"><canvas id="chartMes"></canvas></div></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-header">Por estado</div>
        <div class="card-body"><div class="dashboard-chart dashboard-chart-doughnut"><canvas id="chartEstado"></canvas></div></div>
      </div>
    </div>
  </div>

  <!-- Tabla recientes -->
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <span>📋 Últimas reservas</span>
      <a href="<?= BASE_URL ?>/index.php?r=admin/reservas" style="font-size:.85rem">Ver todas →</a>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table mb-0">
          <thead><tr><th>Cliente</th><th>Sala</th><th>Fecha</th><th>Total</th><th>Estado</th></tr></thead>
          <tbody>
            <?php foreach ($recientes as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['usuario_nombre']) ?></td>
              <td><?= htmlspecialchars($r['sala_nombre']) ?></td>
              <td><?= date('d/m/Y', strtotime($r['fecha'])) ?></td>
              <td class="fw-bold text-brand"><?= precio((int)$r['total']) ?></td>
              <td><span class="badge-estado estado-<?= $r['estado'] ?>"><?= ucfirst($r['estado']) ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= htmlspecialchars(asset('js/chart-theme.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script>
kzmApplyChartTheme();
// Reservas por mes
const mesDatos = <?= json_encode($porMes) ?>;
new Chart(document.getElementById('chartMes'), {
  type:'bar',
  data: {
    labels: mesDatos.map(d=>d.mes),
    datasets:[{ label:'Reservas', data: mesDatos.map(d=>d.total),
      backgroundColor:kzmChartColor('primary'), borderRadius:6, borderSkipped:false }]
  },
  options:{responsive:true,maintainAspectRatio:false,animation:false,resizeDelay:150,layout:{padding:{top:22}},plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grace:'12%',ticks:{precision:0},title:{display:true,text:'Cantidad de reservas',font:{weight:'bold',size:12}}},x:{ticks:{autoSkip:false},title:{display:true,text:'Mes',font:{weight:'bold',size:12}}}}}
});

// Por estado
const estDatos = <?= json_encode($porEstado) ?>;
const colores = {pendiente:'warning',confirmada:'success',cancelada:'danger',finalizada:'info'};
const etiquetas = {pendiente:'Pendiente',confirmada:'Confirmada',cancelada:'Cancelada',finalizada:'Finalizada'};
new Chart(document.getElementById('chartEstado'), {
  type:'doughnut',
  data:{
    labels: estDatos.map(d=>etiquetas[d.estado]||d.estado),
    datasets:[{data: estDatos.map(d=>d.total),
      backgroundColor: kzmChartColors(estDatos.map(d=>colores[d.estado]||'neutral')),
      hoverOffset:8, borderWidth:2, borderColor:kzmChartColor('surface')}]
  },
  options:{responsive:true,maintainAspectRatio:false,animation:false,resizeDelay:150,cutout:'65%',plugins:{legend:{position:'bottom',labels:{generateLabels:kzmPieLegend,font:{size:12,weight:'bold'},boxWidth:14}}}}
});
</script>
</body>
</html>
