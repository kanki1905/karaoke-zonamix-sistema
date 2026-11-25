<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar Sesión — KaraokeZonaMix</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
</head>
<body>
<button type="button" class="btn btn-sm btn-outline-secondary theme-toggle auth-theme-toggle" aria-label="Cambiar tema" aria-pressed="false" style="position:fixed;top:1rem;right:1rem;z-index:1100">🌙 Modo nocturno</button>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">🎤</div>
    <h2 class="auth-title">KaraokeZonaMix</h2>
    <p class="auth-subtitle">Inicia sesión para continuar</p>

    <?php if ($err = flash('error')): ?>
      <div class="alert alert-danger py-2"><?= $err ?></div>
    <?php endif; ?>
    <?php if ($ok = flash('success')): ?>
      <div class="alert alert-success py-2"><?= htmlspecialchars($ok) ?></div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/index.php?r=auth/login-post" method="POST">
        <?= csrfField() ?>
      <div class="mb-3">
        <label class="form-label">Correo electrónico</label>
        <input type="email" name="email" maxlength="50" class="form-control" required
               placeholder="tu@correo.com" autocomplete="email"
               value="<?= htmlspecialchars($fd['email'] ?? '') ?>">
      </div>
      <div class="mb-4">
        <label class="form-label">Contraseña</label>
        <div class="input-group">
          <input type="password" name="password" id="loginPass" class="form-control" required maxlength="25"
                 placeholder="••••••••" autocomplete="current-password">
          <button type="button" class="btn btn-outline-secondary px-3"
                  onclick="togglePass('loginPass',this)" tabindex="-1" title="Ver contraseña">
            <span class="eye-icon">👁</span>
          </button>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100 mb-3">Iniciar Sesión</button>
      <div class="text-center">
        <small class="text-muted">¿No tienes cuenta?
          <a href="<?= BASE_URL ?>/index.php?r=auth/registro" class="text-brand fw-semibold">Regístrate aquí</a>
        </small>
        <small class="text-muted d-block mt-2">¿Olvidaste tu contraseña?
          <span class="fw-semibold">Preséntate al local y pide tu nueva contraseña.</span>
        </small>
      </div>
    </form>
    <div class="text-center mt-3">
      <a href="<?= BASE_URL ?>/index.php?r=public/home" class="text-muted" style="font-size:.82rem">← Volver al inicio</a>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePass(id, btn) {
  const el = document.getElementById(id);
  const showing = el.type === 'text';
  el.type = showing ? 'password' : 'text';
  btn.querySelector('.eye-icon').textContent = showing ? '👁' : '🙈';
}
</script>
</body></html>
