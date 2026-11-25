<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mi Perfil — Karaoke Zona Mix</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/nav.php'; ?>

<div class="container" style="padding:2.5rem 0;max-width:680px">

  <h2 class="fw-bold mb-4" style="color:var(--text-primary)">👤 Mi Perfil</h2>

  <?php if ($ok = flash('success')): ?>
    <div class="alert alert-success"><?= htmlspecialchars($ok) ?></div>
  <?php endif; ?>
  <?php if ($err = flash('error')): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <!-- Datos personales -->
  <div class="card mb-4">
    <div class="card-header">Datos Personales</div>
    <div class="card-body">
      <form action="<?= BASE_URL ?>/index.php?r=client/perfil-post" method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>

        <div class="text-center mb-4">
          <?php if (!empty($user['foto'])): ?>
            <img src="<?= UPLOAD_URL ?>/perfiles/<?= htmlspecialchars($user['foto']) ?>"
                 style="width:90px;height:90px;border-radius:50%;object-fit:cover;
                        border:3px solid var(--primary);box-shadow:0 4px 12px var(--shadow-color)">
          <?php else: ?>
            <div style="width:90px;height:90px;border-radius:50%;background:var(--primary);
                        display:inline-flex;align-items:center;justify-content:center;
                        font-size:2.2rem;color:var(--on-primary);font-weight:800">
              <?= strtoupper(substr($user['nombre'],0,1)) ?>
            </div>
          <?php endif; ?>
          <div class="mt-2">
            <label class="form-label" style="font-size:.82rem">Cambiar foto de perfil</label>
            <input type="file" name="foto" class="form-control form-control-sm"
                   accept=".jpg,.jpeg,.png,.webp">
          </div>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label">Nombres</label>
            <input type="text" name="nombre" class="form-control" required maxlength="50"
                   value="<?= htmlspecialchars($user['nombre']) ?>">
          </div>
          <div class="col-6">
            <label class="form-label">Apellidos</label>
            <input type="text" name="apellidos" class="form-control" maxlength="50"
                   value="<?= htmlspecialchars($user['apellidos'] ?? '') ?>">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Correo electrónico</label>
          <input type="email" name="email" class="form-control" required maxlength="50"
                 value="<?= htmlspecialchars($user['email']) ?>">
        </div>
        <div class="mb-4">
          <label class="form-label">Teléfono</label>
          <input type="tel" name="telefono" id="telefonoPerfil"
                 class="form-control"
                 inputmode="numeric"
                 pattern="[67][0-9]{7}" maxlength="8" minlength="8"
                 placeholder="Ej: 70123456"
                 oninput="this.value = this.value.replace(/[^0-9]/g,'').substring(0,8)"
                 title="Debe iniciar con 6 o 7 y tener exactamente 8 dígitos"
                 value="<?= htmlspecialchars($user['telefono'] ?? '') ?>">
          <div class="form-text">Debe iniciar con 6 o 7 y tener 8 dígitos</div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
      </form>
    </div>
  </div>

  <!-- Cambiar contraseña -->
  <div class="card">
    <div class="card-header">🔒 Cambiar Contraseña</div>
    <div class="card-body">
      <form action="<?= BASE_URL ?>/index.php?r=client/cambiar-password" method="POST"
            id="frmCambiarPass" onsubmit="return validarPass()">
        <?= csrfField() ?>
        <div class="mb-3">
          <label class="form-label">Contraseña actual</label>
          <input type="password" name="actual" id="passActual" class="form-control" required maxlength="25">
        </div>
        <div class="mb-2">
          <label class="form-label">Nueva contraseña</label>
          <input type="password" name="nueva" id="passNueva" class="form-control"
                 required minlength="8" maxlength="25" oninput="chkPass()">
          <!-- Indicador de requisitos -->
          <div id="passReqs" class="mt-2" style="font-size:.78rem;display:none">
            <div id="rLen"  class="text-danger">✗ Mínimo 8 caracteres</div>
            <div id="rLet"  class="text-danger">✗ Al menos una letra</div>
            <div id="rNum"  class="text-danger">✗ Al menos un número</div>
            <div id="rSym"  class="text-danger">✗ Al menos un símbolo (!@#$%...)</div>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label">Confirmar nueva contraseña</label>
          <div class="position-relative">
            <input type="password" name="confirm" id="passConfirm" class="form-control"
                   required minlength="8" maxlength="25" oninput="chkConfirm()">
            <span id="icoConfirm" class="position-absolute"
                  style="right:.75rem;top:50%;transform:translateY(-50%);font-size:1.1rem"></span>
          </div>
          <div id="errConfirm" class="text-danger mt-1" style="font-size:.8rem;display:none">
            Las contraseñas no coinciden.
          </div>
        </div>
        <button type="submit" class="btn btn-outline-primary w-100">Cambiar Contraseña</button>
      </form>
    </div>
  </div>

</div>

<?php require ROOT_PATH . '/views/partials/footer.php'; ?>
<script>
function chkPass() {
  const p = document.getElementById('passNueva').value;
  document.getElementById('passReqs').style.display = p.length ? 'block' : 'none';
  const ok = (id, test) => {
    const el = document.getElementById(id);
    if (test) { el.className='text-success'; el.textContent='✓ ' + el.textContent.slice(2); }
    else       { el.className='text-danger';  el.textContent='✗ ' + el.textContent.slice(2); }
  };
  ok('rLen', p.length >= 8);
  ok('rLet', /[a-zA-Z]/.test(p));
  ok('rNum', /[0-9]/.test(p));
  ok('rSym', /[\W_]/.test(p));
  chkConfirm();
}

function chkConfirm() {
  const p  = document.getElementById('passNueva').value;
  const c  = document.getElementById('passConfirm').value;
  const ic = document.getElementById('icoConfirm');
  const er = document.getElementById('errConfirm');
  if (!c) { ic.textContent=''; er.style.display='none'; return; }
  if (p === c) { ic.textContent='✅'; er.style.display='none'; }
  else         { ic.textContent='❌'; er.style.display='block'; }
}

function validarPass() {
  const p = document.getElementById('passNueva').value;
  const c = document.getElementById('passConfirm').value;
  if (p.length > 25) {
    alert('La contraseña no puede superar los 25 caracteres.');
    document.getElementById('passNueva').focus();
    return false;
  }
  if (!/^(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\W_]).{8,25}$/.test(p)) {
    alert('La nueva contraseña no cumple los requisitos mínimos.');
    document.getElementById('passNueva').focus();
    return false;
  }
  if (p !== c) {
    alert('Las contraseñas no coinciden.');
    document.getElementById('passConfirm').focus();
    return false;
  }
  return true;
}
</script>
</body>
</html>
