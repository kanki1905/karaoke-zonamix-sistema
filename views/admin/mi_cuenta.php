<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mi perfil — Admin</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/admin_sidebar.php'; ?>
<div class="admin-content">
  <div class="admin-topbar"><h4>👤 Mi perfil</h4></div>

  <?php if ($ok = flash('success')): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
  <?php if ($err = flash('error')): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <div style="max-width:680px">
  <!-- Datos personales -->
  <div class="card mb-4">
    <div class="card-header fw-bold">Datos personales</div>
    <div class="card-body">
      <form action="<?= BASE_URL ?>/index.php?r=admin/mi-cuenta-post" method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <div class="text-center mb-4">
          <?php if (!empty($user['foto'])): ?>
            <img src="<?= UPLOAD_URL ?>/perfiles/<?= htmlspecialchars($user['foto']) ?>" alt="Foto"
                 style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--primary);box-shadow:0 4px 12px var(--shadow-color)">
          <?php else: ?>
            <div style="width:90px;height:90px;border-radius:50%;background:var(--primary);display:inline-flex;align-items:center;justify-content:center;font-size:2.2rem;color:var(--on-primary);font-weight:800">
              <?= strtoupper(substr($user['nombre'] ?? 'A', 0, 1)) ?>
            </div>
          <?php endif; ?>
          <div class="mt-2">
            <label class="form-label" style="font-size:.82rem">Cambiar foto de perfil (JPG, PNG o WEBP, máx. 2 MB)</label>
            <input type="file" name="foto" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp">
          </div>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label">Nombres</label>
            <input type="text" name="nombre" class="form-control" required maxlength="50" value="<?= htmlspecialchars($user['nombre'] ?? '') ?>">
          </div>
          <div class="col-6">
            <label class="form-label">Apellidos</label>
            <input type="text" name="apellidos" class="form-control" required maxlength="50" value="<?= htmlspecialchars($user['apellidos'] ?? '') ?>">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Correo electrónico</label>
          <input type="email" name="email" class="form-control" required maxlength="50" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
        </div>
        <div class="mb-4">
          <label class="form-label">Teléfono</label>
          <input type="tel" name="telefono" class="form-control" required inputmode="numeric" pattern="[67][0-9]{7}" maxlength="8" minlength="8"
                 placeholder="Ej: 70123456" oninput="this.value=this.value.replace(/[^0-9]/g,'').substring(0,8)"
                 title="Debe iniciar con 6 o 7 y tener exactamente 8 dígitos" value="<?= htmlspecialchars($user['telefono'] ?? '') ?>">
          <div class="form-text">Debe iniciar con 6 o 7 y tener 8 dígitos</div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Guardar cambios</button>
      </form>
    </div>
  </div>

  <!-- Cambiar contraseña -->
  <div class="card">
    <div class="card-header fw-bold">🔒 Cambiar mi contraseña</div>
    <div class="card-body">
      <p class="small text-muted mb-3">Necesitas tu contraseña actual. El administrador no puede cambiar la contraseña de otras cuentas.</p>
      <form action="<?= BASE_URL ?>/index.php?r=admin/mi-cuenta-password" method="POST" onsubmit="return chkMiPass()">
        <?= csrfField() ?>
        <div class="mb-3">
          <label class="form-label">Contraseña actual</label>
          <input type="password" name="actual" class="form-control" maxlength="25" required autocomplete="current-password">
        </div>
        <div class="mb-3">
          <label class="form-label">Contraseña nueva</label>
          <input type="password" name="nueva" id="miNueva" class="form-control" maxlength="25" required autocomplete="new-password"
                 placeholder="8 a 25 caracteres, con letra, número y símbolo">
        </div>
        <div class="mb-4">
          <label class="form-label">Repetir contraseña nueva</label>
          <input type="password" name="confirm" id="miConfirm" class="form-control" maxlength="25" required autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-outline-primary w-100">Cambiar contraseña</button>
      </form>
    </div>
  </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function chkMiPass() {
  const n = document.getElementById('miNueva').value, c = document.getElementById('miConfirm').value;
  if (!/^(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\W_]).{8,25}$/.test(n)) { alert('La contraseña debe tener de 8 a 25 caracteres con letras, números y un símbolo.'); return false; }
  if (n !== c) { alert('Las contraseñas nuevas no coinciden.'); return false; }
  return true;
}
</script>
</body></html>
