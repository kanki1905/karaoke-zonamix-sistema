<nav class="navbar navbar-expand-lg navbar-light sticky-top">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/index.php?r=<?= isLogged() && !isAdmin() ? 'client/inicio' : (isAdmin() ? 'admin/dashboard' : 'public/home') ?>">🎤 Karaoke Zona Mix</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link <?= (($_GET['r'] ?? '') === 'public/home' || ($_GET['r'] ?? '') === '') ? 'active' : '' ?>"
             href="<?= BASE_URL ?>/index.php?r=<?= isLogged() && !isAdmin() ? 'client/inicio' : 'public/home' ?>">Inicio</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= str_starts_with($_GET['r'] ?? '', 'public/salas') ? 'active' : '' ?>"
             href="<?= BASE_URL ?>/index.php?r=public/salas">Salas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= in_array($_GET['r'] ?? '', ['public/menu','client/menu'], true) ? 'active' : '' ?>"
             href="<?= BASE_URL ?>/index.php?r=<?= isLogged() && !isAdmin() ? 'client/menu' : 'public/menu' ?>">🍔 Menú</a>
        </li>
        <?php if (isLogged() && !isAdmin()): ?>
        <li class="nav-item">
          <a class="nav-link <?= str_starts_with($_GET['r'] ?? '', 'client/reservar') ? 'active' : '' ?>"
             href="<?= BASE_URL ?>/index.php?r=client/reservar">Reservar</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= str_starts_with($_GET['r'] ?? '', 'client/mis-reservas') ? 'active' : '' ?>"
             href="<?= BASE_URL ?>/index.php?r=client/mis-reservas">Mis Reservas</a>
        </li>
        <li class="nav-item">
          <?php
            try {
              $mm_nav  = new MensajeModel();
              $sinLeer = $mm_nav->sinLeerCliente((int)$_SESSION['user']['id']);
            } catch (Throwable $e) {
              $sinLeer = 0; // tabla mensajes aún no creada
            }
          ?>
          <a class="nav-link <?= str_starts_with($_GET['r'] ?? '', 'client/chat') ? 'active' : '' ?>"
             href="<?= BASE_URL ?>/index.php?r=client/chat">💬 Chat
            <span class="chat-badge" data-chat-notify="cliente" data-url="<?= BASE_URL ?>/index.php?r=client/chat-unread" data-chat-badge <?= $sinLeer > 0 ? '' : 'hidden' ?>><?= $sinLeer > 99 ? '99+' : (int)$sinLeer ?></span>
            <span class="visually-hidden" data-chat-sr aria-live="polite"><?= $sinLeer > 0 ? $sinLeer . ($sinLeer === 1 ? ' mensaje sin leer' : ' mensajes sin leer') : '' ?></span>
          </a>
          <?= inlineJs('chat-notify.js') ?>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= str_starts_with($_GET['r'] ?? '', 'client/perfil') ? 'active' : '' ?>"
             href="<?= BASE_URL ?>/index.php?r=client/perfil">👤 Mi Perfil</a>
        </li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item me-lg-2"><button type="button" class="btn btn-sm btn-outline-secondary theme-toggle" aria-label="Cambiar tema">🌙 Modo nocturno</button></li>
        <?php if (isLogged()): ?>
          <?php if (isAdmin()): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/index.php?r=admin/dashboard">
              <span class="badge bg-primary">Admin</span>
            </a>
          </li>
          <?php endif; ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#"
               data-bs-toggle="dropdown" aria-expanded="false" id="userMenuToggle">
              <?php if (!empty($_SESSION['user']['foto'])): ?>
                <img src="<?= UPLOAD_URL ?>/perfiles/<?= htmlspecialchars($_SESSION['user']['foto']) ?>"
                     style="width:28px;height:28px;border-radius:50%;object-fit:cover">
              <?php else: ?>
                <span style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:var(--on-primary);
                             display:inline-flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700">
                  <?= strtoupper(substr($_SESSION['user']['nombre'], 0, 1)) ?>
                </span>
              <?php endif; ?>
              <?= htmlspecialchars($_SESSION['user']['nombre']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php if (!isAdmin()): ?>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/index.php?r=client/mis-reservas">📋 Mis Reservas</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/index.php?r=client/perfil">👤 Mi Perfil</a></li>
              <li><hr class="dropdown-divider"></li>
              <?php endif; ?>
              <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/index.php?r=auth/logout">🚪 Cerrar Sesión</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/index.php?r=auth/login">Iniciar Sesión</a>
          </li>
          <li class="nav-item ms-2">
            <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/index.php?r=auth/registro">Regístrate</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<script>
/* Fallback accesible: el menú de usuario sigue funcionando aunque Bootstrap JS no se inicialice. */
(function(){
 const toggle=document.getElementById('userMenuToggle');
 if(!toggle) return;
 const menu=toggle.parentElement.querySelector('.dropdown-menu');
 if(!menu) return;
 toggle.addEventListener('click',function(e){
   e.preventDefault();
   if(window.bootstrap && window.bootstrap.Dropdown) return;
   const opened=menu.classList.toggle('show');
   toggle.setAttribute('aria-expanded',opened?'true':'false');
 });
 document.addEventListener('click',function(e){
   if(!toggle.parentElement.contains(e.target) && !(window.bootstrap && window.bootstrap.Dropdown)){
     menu.classList.remove('show'); toggle.setAttribute('aria-expanded','false');
   }
 });
})();
</script>
