<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Usuarios — Admin</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
  <style>
    .user-avatar {
      width:40px; height:40px; border-radius:50%; object-fit:cover;
      border:2px solid var(--border);
    }
    .fila-baja td { opacity:.6; }
    .fila-baja td:last-child { opacity:1; }
    .user-avatar-placeholder {
      width:40px; height:40px; border-radius:50%;
      background:var(--primary);
      display:inline-flex; align-items:center; justify-content:center;
      color:var(--on-primary); font-weight:700; font-size:.9rem;
    }
  </style>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/admin_sidebar.php'; ?>
<div class="admin-content">
  <div class="admin-topbar"><h4>👥 Usuarios y administradores</h4></div>
  <div class="alert alert-info small py-2">👁 Aquí solo puedes <strong>ver</strong> las cuentas, <strong>restablecer la contraseña</strong> de un cliente o <strong>dar de baja / dar de alta</strong> una cuenta (la cuenta no se borra: solo se bloquea o habilita su acceso). Los datos de cada persona solo los edita ella misma desde «Mi perfil»; tus propios datos, desde tu perfil de administrador.</div>
  <div class="card mb-4"><div class="card-header fw-bold">➕ Crear otra cuenta administradora</div><div class="card-body"><form action="<?= BASE_URL ?>/index.php?r=admin/usuario-crear-admin" method="POST" class="row g-3">
  <?= csrfField() ?><div class="col-md-3"><label class="form-label">Nombre</label><input class="form-control" name="nombre" maxlength="50" required></div><div class="col-md-3"><label class="form-label">Apellidos</label><input class="form-control" name="apellidos" maxlength="50" required></div><div class="col-md-3"><label class="form-label">Correo</label><input class="form-control" type="email" name="email" maxlength="50" required></div><div class="col-md-3"><label class="form-label">Teléfono</label><input class="form-control" name="telefono" pattern="[67][0-9]{7}" maxlength="8" required></div><div class="col-md-4"><label class="form-label">Contraseña temporal</label><input class="form-control" type="password" name="password" minlength="8" maxlength="25" required></div><div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary w-100" type="submit">Crear administrador</button></div><div class="col-12 small text-muted">Solo una cuenta con rol administrador puede crear otra. La contraseña se guarda cifrada.</div></form></div></div>

  <?php if ($ok = flash('success')): ?>
    <div class="alert alert-success"><?= htmlspecialchars($ok) ?></div>
  <?php endif; ?>
  <?php if ($err = flash('error')): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <div class="mb-3"><input type="search" class="form-control filtro-tabla" data-tabla="tablaUsuarios" placeholder="Buscar usuarios por nombre, correo o teléfono…" aria-label="Buscar usuarios por nombre, correo o teléfono…"></div>
  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="tablaUsuarios" class="table mb-0 align-middle">
          <thead>
            <tr>
              <th>#</th><th>Foto</th><th>Nombres</th><th>Apellidos</th><th>Correo</th>
              <th>Teléfono</th><th>Rol</th><th>Estado</th><th>Registro</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($usuarios as $u): ?>
          <?php $activo = (int)($u['activo'] ?? 1) === 1; ?>
          <tr<?= $activo ? '' : ' class="fila-baja"' ?>>
            <td class="text-muted"><?= $u['id'] ?></td>
            <td>
              <?php
                $fotoUrl = !empty($u['foto'])
                  ? UPLOAD_URL . '/perfiles/' . htmlspecialchars($u['foto'])
                  : null;
                $inicial = strtoupper(substr($u['nombre'],0,1));
              ?>
              <?php if ($fotoUrl): ?>
                <img src="<?= $fotoUrl ?>" alt="Foto" class="user-avatar">
              <?php else: ?>
                <span class="user-avatar-placeholder"><?= $inicial ?></span>
              <?php endif; ?>
            </td>
            <td class="fw-semibold"><?= htmlspecialchars($u['nombre']) ?></td>
            <td><?= htmlspecialchars($u['apellidos'] ?? '—') ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['telefono'] ?: '—') ?></td>
            <td>
              <span class="status-badge <?= $u['rol']==='admin'?'status-badge--primary':'' ?>">
                <?= ucfirst($u['rol']) ?>
              </span>
            </td>
            <td>
              <span class="status-badge <?= $activo ? 'status-badge--success' : 'status-badge--danger' ?>">
                <?= $activo ? 'Activo' : 'De baja' ?>
              </span>
            </td>
            <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
            <td>
              <div class="d-flex gap-1 flex-wrap">
                <!-- Restablecer contraseña (clientes y administradores, menos la propia): celular + iniciales -->
                <?php if ($u['id'] != $_SESSION['user']['id']): ?>
                <form action="<?= BASE_URL ?>/index.php?r=admin/usuario-restablecer-password" method="POST" class="d-inline">
                  <?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('¿Restablecer la contraseña de este usuario?\nLa nueva será su número de celular + sus iniciales.')">🔑 Restablecer contraseña</button>
                </form>
                <?php endif; ?>
                <?php if ($u['id'] == $_SESSION['user']['id']): ?>
                <a href="<?= BASE_URL ?>/index.php?r=admin/mi-cuenta" class="btn btn-sm btn-outline-primary">👤 Mi perfil</a>
                <?php endif; ?>
                <!-- Dar de baja / dar de alta (no puede darse de baja a sí mismo) -->
                <?php if ($u['id'] != $_SESSION['user']['id']): ?>
                <form action="<?= BASE_URL ?>/index.php?r=admin/usuario-toggle" method="POST" class="d-inline">
                  <?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                  <?php if ($activo): ?>
                  <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Dar de baja a <?= addslashes(htmlspecialchars($u['nombre'])) ?>?\nNo podrá iniciar sesión, pero sus datos y reservas se conservan.')">⛔ Dar de baja</button>
                  <?php else: ?>
                  <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('¿Dar de alta a <?= addslashes(htmlspecialchars($u['nombre'])) ?>?\nVolverá a poder iniciar sesión.')">✅ Dar de alta</button>
                  <?php endif; ?>
                </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.filtro-tabla').forEach(inp=>inp.addEventListener('input',()=>{const t=document.getElementById(inp.dataset.tabla);if(!t)return;const q=inp.value.toLocaleLowerCase();t.querySelectorAll('tbody tr').forEach(r=>r.style.display=r.innerText.toLocaleLowerCase().includes(q)?'':'none')}));
</script>
</body></html>
