<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Crear Cuenta — Karaoke Zona Mix</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
  <style>
    .field-error { font-size:.78rem; color:var(--danger-text); display:none; margin-top:.25rem; }
    .field-error.show { display:block; }
  </style>
</head>
<body>
<button type="button" class="btn btn-sm btn-outline-secondary theme-toggle auth-theme-toggle" aria-label="Cambiar tema" aria-pressed="false" style="position:fixed;top:1rem;right:1rem;z-index:1100">🌙 Modo nocturno</button>
<div class="auth-wrapper">
  <div class="auth-card" style="max-width:500px">
    <div class="auth-logo">🎤</div>
    <h2 class="auth-title">Karaoke Zona Mix</h2>
    <p class="auth-subtitle">Crea tu cuenta gratis</p>

    <?php if ($err = flash('error')): ?>
      <div class="alert alert-danger py-2"><?= $err ?></div>
    <?php endif; ?>

    <form id="frmReg" action="<?= BASE_URL ?>/index.php?r=auth/registro-post" method="POST" novalidate>
        <?= csrfField() ?>

      <!-- Nombres y Apellidos en fila -->
      <div class="row g-2 mb-3">
        <div class="col-6">
          <label class="form-label">Nombres <span class="text-danger">*</span></label>
          <input type="text" name="nombres" id="fNombres" class="form-control" required autofocus maxlength="50" maxlength="50"
                 oninput="soloLetras(this)"
                 value="<?= htmlspecialchars($fd['nombres'] ?? '') ?>">
          <div class="field-error" id="errNombres"></div>
        </div>
        <div class="col-6">
          <label class="form-label">Apellidos <span class="text-danger">*</span></label>
          <input type="text" name="apellidos" id="fApellidos" class="form-control" required maxlength="50" maxlength="50"
                 oninput="soloLetras(this)"
                 value="<?= htmlspecialchars($fd['apellidos'] ?? '') ?>">
          <div class="field-error" id="errApellidos"></div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
        <input type="email" name="email" id="fEmail" class="form-control" required maxlength="50" maxlength="50"
               value="<?= htmlspecialchars($fd['email'] ?? '') ?>">
        <div class="field-error" id="errEmail"></div>
      </div>

      <div class="mb-3">
        <label class="form-label">Número de teléfono <span class="text-danger">*</span>
          <small class="text-muted">(8 dígitos, empieza en 6 o 7)</small>
        </label>
        <input type="tel" name="telefono" id="fTelefono"
               class="form-control" required
               inputmode="numeric"
               pattern="[67][0-9]{7}"
               maxlength="8" minlength="8"
               oninput="this.value=this.value.replace(/\D/g,'').substring(0,8)"
               onkeypress="return /[0-9]/.test(event.key)"
               title="Debe iniciar con 6 o 7 y tener exactamente 8 dígitos"
               value="<?= htmlspecialchars($fd['telefono'] ?? '') ?>">
        <div class="field-error" id="errTel"></div>
      </div>

      <div class="mb-3">
        <label class="form-label">Contraseña <span class="text-danger">*</span>
          <small class="text-muted">(mín. 8 caracteres, letras + números + símbolo)</small>
        </label>
        <div class="input-group">
          <input type="password" name="password" id="regPass" class="form-control" required minlength="8" maxlength="25"
                 autocomplete="new-password" oninput="checkPass()">
          <button type="button" class="btn btn-outline-secondary px-3"
                  onclick="togglePass('regPass',this)" tabindex="-1" title="Ver contraseña">
            <span class="eye-icon">👁</span>
          </button>
        </div>
        <div id="passHint" class="form-text mt-1"></div>
        <div class="field-error" id="errPass"></div>
      </div>

      <div class="mb-4">
        <label class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
        <div class="input-group">
          <input type="password" name="confirm" id="regPass2" class="form-control" required minlength="8" maxlength="25"
                 autocomplete="new-password" oninput="checkMatch()">
          <button type="button" class="btn btn-outline-secondary px-3"
                  onclick="togglePass('regPass2',this)" tabindex="-1" title="Ver contraseña">
            <span class="eye-icon">👁</span>
          </button>
          <span id="matchIcon" class="input-group-text"
                style="background:transparent;border-left:none;font-size:1.15rem;min-width:42px;justify-content:center"></span>
        </div>
        <div class="field-error" id="errConfirm"></div>
      </div>

      <button type="submit" class="btn btn-primary w-100 mb-3">Crear Cuenta</button>

      <div class="text-center">
        <small class="text-muted">¿Ya tienes cuenta?
          <a href="<?= BASE_URL ?>/index.php?r=auth/login" class="text-brand fw-semibold">Iniciar sesión</a>
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
// ── Limpiar errores mientras el usuario escribe ────────────────
(function(){
  var mapa = {
    'fNombres':'errNombres',
    'fApellidos':'errApellidos',
    'fEmail':'errEmail',
    'fTelefono':'errTel',
    'regPass':'errPass',
    'regPass2':'errConfirm'
  };
  Object.keys(mapa).forEach(function(id){
    var el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', function(){
      var err = document.getElementById(mapa[id]);
      if (err) err.classList.remove('show');
      el.classList.remove('is-invalid');
    });
  });
})();

// ── Bloquear dígitos en nombre y apellido ──────────────────────
function soloLetras(el) {
  const v   = el.value;
  const pos = el.selectionStart;
  const sin = v.replace(/[0-9]/g, '');
  if (sin !== v) {
    el.value = sin;
    try { el.setSelectionRange(pos - 1, pos - 1); } catch(e){}
  }
}

// ── Mostrar / ocultar error ────────────────────────────────────
function setErr(inputId, errId, msg) {
  const inp = document.getElementById(inputId);
  const err = document.getElementById(errId);
  if (!err) return;
  if (msg) {
    err.textContent = '⚠ ' + msg;
    err.classList.add('show');
    if (inp) { inp.classList.add('is-invalid'); inp.classList.remove('is-valid'); }
  } else {
    err.classList.remove('show');
    if (inp) { inp.classList.remove('is-invalid'); inp.classList.add('is-valid'); }
  }
}

// ── Validar y enviar ───────────────────────────────────────────
document.getElementById('frmReg').addEventListener('submit', function(e) {
  let valido = true;

  // Nombres
  const nombres = document.getElementById('fNombres').value.trim();
  if      (!nombres)              { setErr('fNombres','errNombres','El nombre es obligatorio'); valido = false; }
  else if (/[0-9]/.test(nombres)) { setErr('fNombres','errNombres','El nombre no puede tener números'); valido = false; }
  else                             { setErr('fNombres','errNombres',''); }

  // Apellidos
  const apellidos = document.getElementById('fApellidos').value.trim();
  if      (!apellidos)               { setErr('fApellidos','errApellidos','Los apellidos son obligatorios'); valido = false; }
  else if (/[0-9]/.test(apellidos))  { setErr('fApellidos','errApellidos','Los apellidos no pueden tener números'); valido = false; }
  else                                { setErr('fApellidos','errApellidos',''); }

  // Email
  const email = document.getElementById('fEmail').value.trim();
  if      (!email) { setErr('fEmail','errEmail','El correo es obligatorio'); valido = false; }
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { setErr('fEmail','errEmail','Correo inválido (ej: tu@correo.com)'); valido = false; }
  else             { setErr('fEmail','errEmail',''); }

  // Teléfono
  const tel = document.getElementById('fTelefono').value.trim();
  if      (!tel)                      { setErr('fTelefono','errTel','El teléfono es obligatorio'); valido = false; }
  else if (!/^[67][0-9]{7}$/.test(tel)) { setErr('fTelefono','errTel','Debe iniciar en 6 o 7 y tener 8 dígitos'); valido = false; }
  else                                 { setErr('fTelefono','errTel',''); }

  // Contraseña
  const pass   = document.getElementById('regPass').value;
  const hasLet = /[a-zA-Z]/.test(pass);
  const hasNum = /[0-9]/.test(pass);
  const hasSym = /[\W_]/.test(pass);
  if      (!pass)                            { setErr('regPass','errPass','La contraseña es obligatoria'); valido = false; }
  else if (pass.length < 8)                  { setErr('regPass','errPass','Mínimo 8 caracteres'); valido = false; }
  else if (!hasLet || !hasNum || !hasSym)    { setErr('regPass','errPass','Debe tener letras, números y un símbolo'); valido = false; }
  else                                        { setErr('regPass','errPass',''); }

  // Confirmar
  const conf = document.getElementById('regPass2').value;
  if      (!conf)          { setErr('regPass2','errConfirm','Confirma tu contraseña'); valido = false; }
  else if (pass !== conf)  { setErr('regPass2','errConfirm','Las contraseñas no coinciden'); valido = false; }
  else                      { setErr('regPass2','errConfirm',''); }

  if (!valido) { e.preventDefault(); }
});

// ── Toggle contraseña ──────────────────────────────────────────
function togglePass(id, btn) {
  const el = document.getElementById(id);
  const showing = el.type === 'text';
  el.type = showing ? 'password' : 'text';
  btn.querySelector('.eye-icon').textContent = showing ? '👁' : '🙈';
}

// ── Indicador de fortaleza ─────────────────────────────────────
function checkPass() {
  const p    = document.getElementById('regPass').value;
  const hint = document.getElementById('passHint');
  if (!p.length) { hint.innerHTML = ''; return; }

  const hasLetter  = /[a-zA-Z]/.test(p);
  const hasNumber  = /[0-9]/.test(p);
  const hasSymbol  = /[\W_]/.test(p);
  const longEnough = p.length >= 8;

  if (!longEnough)      { hint.innerHTML = '<span style="color:var(--warning-text)">⚠ Mínimo 8 caracteres</span>'; }
  else if (!hasLetter)  { hint.innerHTML = '<span style="color:var(--danger-text)">⚠ Agrega al menos una letra</span>'; }
  else if (!hasNumber)  { hint.innerHTML = '<span style="color:var(--danger-text)">⚠ Agrega al menos un número</span>'; }
  else if (!hasSymbol)  { hint.innerHTML = '<span style="color:var(--danger-text)">⚠ Agrega un símbolo (ej: @, #, !)</span>'; }
  else                   { hint.innerHTML = '<span style="color:var(--success-text)">✓ Contraseña segura</span>'; }

  checkMatch();
}

function checkMatch() {
  const p1   = document.getElementById('regPass').value;
  const p2   = document.getElementById('regPass2').value;
  const icon = document.getElementById('matchIcon');
  if (!p2.length) { icon.innerHTML = ''; return; }
  icon.innerHTML = (p1 === p2)
    ? '<span style="color:var(--success-text);font-weight:bold">✓</span>'
    : '<span style="color:var(--danger-text)">✗</span>';
}
</script>
</body>
</html>
