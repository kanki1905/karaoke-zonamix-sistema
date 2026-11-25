<nav class="admin-sidebar">
  <a href="<?= BASE_URL ?>/index.php?r=admin/dashboard" class="sidebar-brand">
    🎤 Karaoke Zona Mix
    <small>Panel Administrador</small>
  </a>
  <div class="sidebar-menu">
    <button type="button" class="btn btn-sm btn-outline-secondary theme-toggle" aria-label="Cambiar tema" aria-pressed="false"><span class="theme-toggle__icon" aria-hidden="true">🌙</span><span class="theme-toggle__label">Modo nocturno</span></button>
    <?php
    $cur = $_GET['r'] ?? '';
    try { $chatsSinLeer = array_sum(array_map(fn($r) => (int)$r['sin_leer'], (new MensajeModel())->resumenNoLeidosAdmin())); } catch (Throwable $e) { $chatsSinLeer = 0; }   // total de MENSAJES sin leer
    $links = [
      ['admin/dashboard',        '📊', 'Inicio'],
      ['admin/reservas',         '📅', 'Reservas'],
      ['admin/salas',            '🏠', 'Salas'],
      ['admin/productos',        '🍔', 'Productos'],
      ['admin/usuarios',         '👥', 'Usuarios'],
      ['admin/reportes',         '📈', 'Reportes'],
      ['admin/chats',            '💬', 'Chats'],
      ['admin/mi-cuenta',        '👤', 'Mi perfil'],
    ];
    foreach ($links as [$route, $ico, $label]):
      $active = str_starts_with($cur, $route) ? 'active' : '';
    ?>
    <a href="<?= BASE_URL ?>/index.php?r=<?= $route ?>" class="sidebar-link <?= $active ?>">
      <span class="icon"><?= $ico ?></span> <?= $label ?>
      <?php if ($route === 'admin/chats'): ?>
        <span class="chat-badge ms-auto" data-chat-notify="admin" data-url="<?= BASE_URL ?>/index.php?r=admin/chat-unread" data-chat-badge <?= $chatsSinLeer > 0 ? '' : 'hidden' ?>><?= $chatsSinLeer > 99 ? '99+' : $chatsSinLeer ?></span>
        <span class="visually-hidden" data-chat-sr aria-live="polite"><?= $chatsSinLeer > 0 ? $chatsSinLeer . ($chatsSinLeer === 1 ? ' mensaje sin leer' : ' mensajes sin leer') : '' ?></span>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
    <?= inlineJs('chat-notify.js') ?>
    <hr style="border-color:var(--border);margin:1rem">
    <a href="<?= BASE_URL ?>" class="sidebar-link">
      <span class="icon">🌐</span> Ver Sitio
    </a>
    <a href="<?= BASE_URL ?>/index.php?r=auth/logout" class="sidebar-link sidebar-link--danger">
      <span class="icon">🚪</span> Cerrar Sesión
    </a>
  </div>
</nav>

<!-- Barra admin para móvil (el sidebar se oculta por debajo de 768 px).
     «Chats» va fijo fuera de la tira con scroll para que su contador siempre se vea. -->
<nav class="admin-mobilebar" aria-label="Menú del administrador">
  <div class="admin-mobilebar__links">
    <?php foreach ($links as [$route, $ico, $label]): if ($route === 'admin/chats') continue; $active = str_starts_with($cur, $route) ? 'active' : ''; ?>
    <a href="<?= BASE_URL ?>/index.php?r=<?= $route ?>" class="admin-mobilebar__link <?= $active ?>"<?= $active ? ' aria-current="page"' : '' ?>><span class="icon" aria-hidden="true"><?= $ico ?></span><?= $label ?></a>
    <?php endforeach; ?>
    <a href="<?= BASE_URL ?>" class="admin-mobilebar__link"><span class="icon" aria-hidden="true">🌐</span>Sitio</a>
    <a href="<?= BASE_URL ?>/index.php?r=auth/logout" class="admin-mobilebar__link admin-mobilebar__link--danger"><span class="icon" aria-hidden="true">🚪</span>Salir</a>
  </div>
  <?php $chatActive = str_starts_with($cur, 'admin/chats') ? 'active' : ''; ?>
  <a href="<?= BASE_URL ?>/index.php?r=admin/chats" class="admin-mobilebar__link admin-mobilebar__chat <?= $chatActive ?>" aria-label="Chats"<?= $chatActive ? ' aria-current="page"' : '' ?>>
    <span class="icon" aria-hidden="true">💬</span>
    <span class="chat-badge" data-chat-badge <?= $chatsSinLeer > 0 ? '' : 'hidden' ?>><?= $chatsSinLeer > 99 ? '99+' : $chatsSinLeer ?></span>
  </a>
  <button type="button" class="btn btn-sm btn-outline-secondary theme-toggle" aria-label="Cambiar tema" aria-pressed="false"><span class="theme-toggle__icon" aria-hidden="true">🌙</span><span class="theme-toggle__label">Modo nocturno</span></button>
</nav>
<script>document.querySelector('.admin-mobilebar__links .active')?.scrollIntoView({ inline: 'center', block: 'nearest' });</script>
