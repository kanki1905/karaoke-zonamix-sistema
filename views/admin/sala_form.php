<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $sala ? 'Editar Sala' : 'Nueva Sala' ?> — Admin</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/admin_sidebar.php'; ?>
<div class="admin-content">
  <div class="admin-topbar">
    <h4><?= $sala ? '✏️ Editar Sala' : '+ Nueva Sala' ?></h4>
    <a href="<?= BASE_URL ?>/index.php?r=admin/salas" class="btn btn-outline-primary btn-sm">← Volver</a>
  </div>

  <?php if ($err = flash('error')): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <div class="card" style="max-width:680px">
    <div class="card-body">
      <form action="<?= BASE_URL ?>/index.php?r=admin/sala-guardar" method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <?php if ($sala): ?>
          <input type="hidden" name="id" value="<?= $sala['id'] ?>">
        <?php endif; ?>

        <div class="mb-3">
          <label class="form-label">Nombre de la sala</label>
          <input type="text" name="nombre" class="form-control" required maxlength="100"
                 value="<?= htmlspecialchars($sala['nombre'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" class="form-control" rows="3" maxlength="2000"
                    placeholder="Describe la sala..."><?= htmlspecialchars($sala['descripcion'] ?? '') ?></textarea>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label">Capacidad (personas)</label>
            <input type="number" name="capacidad" class="form-control" min="1" max="50" required
                   value="<?= $sala['capacidad'] ?? 4 ?>">
          </div>
          <div class="col-6">
            <label class="form-label">Precio por hora (Bs)</label>
            <input type="number" name="precio_hora" class="form-control" min="1" max="2147483647" required
                   value="<?= $sala['precio_hora'] ?? 50 ?>">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Número de cabina <small class="text-muted">(único, ej: 1, 2, 3…)</small></label>
          <input type="number" name="numero" class="form-control" min="1"
                 placeholder="Ej: 1"
                 value="<?= htmlspecialchars($sala['numero'] ?? '') ?>">
          <div class="form-text">Se mostrará como <strong>#N</strong> en las tarjetas. Debe ser único.</div>
        </div>
        <div class="mb-4">
          <label class="form-label">Imagen de la sala</label>
          <input type="file" name="imagen" class="form-control" accept=".jpg,.jpeg,.png,.webp">
          <?php if (!empty($sala['imagen'])): ?>
            <div class="mt-2">
              <img src="<?= UPLOAD_URL ?>/salas/<?= htmlspecialchars($sala['imagen']) ?>"
                   class="img-preview-centrada" alt="Imagen actual de la sala">
              <div class="form-text">Imagen actual. Sube una nueva para reemplazarla.</div>
            </div>
          <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary w-100">
          <?= $sala ? 'Guardar Cambios' : 'Crear Sala' ?>
        </button>
      </form>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
