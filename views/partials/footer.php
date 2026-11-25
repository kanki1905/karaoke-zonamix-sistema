<footer>
  <div class="container">
    <div class="row">
      <div class="col-md-4 mb-3">
        <div class="brand mb-2">🎤 Karaoke Zona Mix</div>
        <p style="font-size:.88rem;color:var(--on-footer-muted)">Tu lugar favorito para cantar en La Paz.
        Reserva tu sala y vive la experiencia.</p>
      </div>
      <div class="col-md-4 mb-3">
        <h6 class="text-white fw-bold mb-2">Navegación</h6>
        <ul class="list-unstyled" style="font-size:.88rem">
          <li><a href="<?= BASE_URL ?>">Inicio</a></li>
          <li><a href="<?= BASE_URL ?>/index.php?r=public/salas">Salas</a></li>
          <?php if (isLogged()): ?>
          <li><a href="<?= BASE_URL ?>/index.php?r=client/reservar">Reservar</a></li>
          <li><a href="<?= BASE_URL ?>/index.php?r=client/mis-reservas">Mis Reservas</a></li>
          <?php else: ?>
          <li><a href="<?= BASE_URL ?>/index.php?r=auth/login">Iniciar Sesión</a></li>
          <li><a href="<?= BASE_URL ?>/index.php?r=auth/registro">Registrarse</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="col-md-4 mb-3">
        <h6 class="text-white fw-bold mb-2">Contacto</h6>
        <p style="font-size:.88rem;color:var(--on-footer-muted)">
          📍 Zona Sur, La Paz, Bolivia<br>
          📞 +591 2 XXXXXXX<br>
          ✉️ info@karaokezonamix.com
        </p>
      </div>
    </div>
    <hr>
    <p class="text-center mb-0" style="font-size:.82rem;color:var(--on-footer-muted)">
      &copy; <?= date('Y') ?> Karaoke Zona Mix — Todos los derechos reservados
    </p>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
