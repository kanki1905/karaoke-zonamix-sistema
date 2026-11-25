<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Comprobantes — Admin</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/admin_sidebar.php'; ?>
<div class="admin-content">
  <div class="admin-topbar"><h4>📎 Comprobantes de Pago</h4></div>

  <?php if ($ok = flash('success')): ?>
    <div class="alert alert-success"><?= htmlspecialchars($ok) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table mb-0">
          <thead>
            <tr><th>#</th><th>Cliente</th><th>Sala</th><th>Fecha</th>
                <th>Total</th><th>Pago</th><th>Estado</th><th>Acciones</th></tr>
          </thead>
          <tbody>
          <?php if (empty($comps)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No hay comprobantes aún.</td></tr>
          <?php else: ?>
          <?php foreach ($comps as $c): ?>
          <tr>
            <td class="text-muted"><?= $c['id'] ?></td>
            <td><?= htmlspecialchars($c['usuario_nombre']) ?></td>
            <td><?= htmlspecialchars($c['sala_nombre']) ?></td>
            <td><?= date('d/m/Y', strtotime($c['fecha'])) ?></td>
            <td class="fw-bold text-brand"><?= precio((int)$c['total']) ?></td>
            <td><?= ucfirst($c['metodo_pago']) ?></td>
            <td><span class="badge-estado estado-<?= $c['estado'] ?>"><?= ucfirst($c['estado']) ?></span></td>
            <td>
              <a href="<?= BASE_URL ?>/index.php?r=admin/comprobante-revisar&id=<?= $c['id'] ?>"
                 class="btn btn-sm btn-outline-primary">Revisar</a>
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
</body></html>
