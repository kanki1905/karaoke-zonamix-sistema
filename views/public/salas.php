<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Salas — Karaoke Zona Mix</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/nav.php'; ?>

<div style="background:var(--hero-bg);padding:3rem 0;color:var(--on-hero);text-align:center">
  <h1 style="font-weight:800">Nuestras Salas</h1>
  <p style="color:var(--on-hero-muted)">Elige la sala ideal para tu grupo</p>
</div>

<div class="container" style="padding:3rem 0">
  <div class="row g-4">
    <?php foreach ($salas as $s): ?>
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
          <p style="font-size:.85rem;color:var(--text-muted);margin:.3rem 0 .5rem">
            <?= htmlspecialchars($s['descripcion'] ?: 'Sala disponible para reservas.') ?>
          </p>
          <p style="font-size:.85rem;margin-bottom:.8rem">
            👥 Hasta <?= $s['capacidad'] ?> personas
          </p>
          <div class="sala-card-price">
            <?= precio((int)$s['precio_hora']) ?> <span>/ hora</span>
          </div>
          <a href="<?= BASE_URL ?>/index.php?r=<?= isLogged() ? 'client/reservar&sala='.$s['id'] : 'auth/login' ?>"
             class="btn btn-primary btn-sm w-100 mt-3">
            <?= isLogged() ? '📅 Reservar esta sala' : '🔐 Iniciar Sesión para Reservar' ?>
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require ROOT_PATH . '/views/partials/footer.php'; ?>
</body>
</html>
