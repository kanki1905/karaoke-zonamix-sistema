<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Menú — Karaoke Zona Mix</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
  <style>
    .menu-hero {
      background: var(--hero-bg);
      color:var(--on-hero); padding:2.5rem 0 2rem; text-align:center;
    }
    .menu-hero h1 { font-size:2rem;font-weight:800;margin-bottom:.4rem; }
    .menu-hero p  { color:var(--on-hero-muted);font-size:.95rem; }

    .cat-title {
      display:flex;align-items:center;gap:.6rem;
      font-size:1.15rem;font-weight:800;color:var(--text-primary);
      margin:2rem 0 1rem;
    }
    .cat-title::after {
      content:'';flex:1;height:2px;
      background:linear-gradient(90deg,var(--primary) 0%,transparent 100%);
      border-radius:2px;
    }

    .prod-card {
      background:var(--surface);border-radius:16px;
      box-shadow:0 2px 12px var(--shadow-color);
      overflow:hidden;transition:transform .18s,box-shadow .18s;
      display:flex;flex-direction:column;height:100%;
    }
    .prod-card:hover { transform:translateY(-4px);box-shadow:0 8px 24px var(--shadow-color); }

    .prod-card-img {
      width:100%;height:150px;object-fit:contain;background:var(--surface-sunken);
    }
    .prod-card-img-placeholder {
      width:100%;height:150px;
      background:var(--primary);
      display:flex;align-items:center;justify-content:center;font-size:3.5rem;
    }
    .prod-card-body {
      padding:1rem;flex:1;display:flex;flex-direction:column;
    }
    .prod-card-name { font-weight:700;font-size:.95rem;color:var(--text-primary);margin-bottom:.25rem; }
    .prod-card-desc { font-size:.8rem;color:var(--text-muted);flex:1;margin-bottom:.6rem; }
    .prod-card-price {
      font-size:1.05rem;font-weight:800;color:var(--text-link);
    }
    .cat-badge-pill {
      display:inline-block;padding:.22rem .7rem;border-radius:50px;
      font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.4rem;
    }

    /* Filtro por tabs */
    .menu-tabs { display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem; }
    .menu-tab {
      padding:.38rem 1rem;border-radius:50px;font-size:.83rem;font-weight:600;
      border:2px solid transparent;cursor:pointer;background:var(--bg-secondary);color:var(--text-primary);
      transition:all .15s;
    }
    .menu-tab.active, .menu-tab:hover {
      background:var(--primary);color:var(--on-primary);border-color:var(--primary);
    }
  </style>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/nav.php'; ?>

<!-- Hero -->
<div class="menu-hero">
  <div class="container">
  <div class="mb-3 mt-3"><input type="search" id="filtroProductos" class="form-control" placeholder="Buscar productos por nombre o categoría…"></div>

    <h1>🍔 Nuestro Menú</h1>
    <p>Bebidas, snacks y comida para acompañar tu sesión de karaoke.</p>
  </div>
</div>

<div class="container" style="padding:2rem 0 3rem">

  <?php if (empty($productos)): ?>
    <div class="text-center py-5 text-muted">
      <div style="font-size:4rem">🍽️</div>
      <p class="mt-3">El menú estará disponible muy pronto.</p>
    </div>
  <?php else: ?>

    <!-- Tabs de filtro -->
    <?php
      $catInfo = [
        'bebida' => '🥤 Bebidas',
        'comida' => '🍽️ Comidas',
        'snack'  => '🍿 Snacks',
        'otro'   => '📦 Otros',
      ];
      $categoriasPresentes = array_keys($grupos);
    ?>
    <div class="menu-tabs">
      <button class="menu-tab active" onclick="filtrarCat('todos',this)">🍴 Todos</button>
      <?php foreach ($categoriasPresentes as $cat): ?>
        <?php if (!empty($grupos[$cat])): ?>
          <button class="menu-tab" onclick="filtrarCat('<?= $cat ?>',this)">
            <?= $catInfo[$cat] ?? ucfirst($cat) ?>
          </button>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <!-- Secciones por categoría -->
    <?php foreach ($grupos as $cat => $items): ?>
    <div class="cat-section" data-cat="<?= $cat ?>">
      <div class="cat-title">
        <?= $catInfo[$cat] ?? ucfirst($cat) ?>
      </div>
      <div class="row g-3">
        <?php foreach ($items as $p): ?>
        <div class="col-6 col-sm-4 col-md-3">
          <div class="prod-card">
            <?php if (!empty($p['imagen'])): ?>
              <img src="<?= UPLOAD_URL ?>/productos/<?= htmlspecialchars($p['imagen']) ?>"
                   class="prod-card-img" alt="<?= htmlspecialchars($p['nombre']) ?>">
            <?php else: ?>
              <?php $icos = ['bebida'=>'🥤','comida'=>'🍽️','snack'=>'🍿','otro'=>'📦']; ?>
              <div class="prod-card-img-placeholder"><?= $icos[$cat] ?? '📦' ?></div>
            <?php endif; ?>
            <div class="prod-card-body">
              <span class="cat-badge-pill cat-<?= $cat ?>"><?= ucfirst($cat) ?></span>
              <div class="prod-card-name"><?= htmlspecialchars($p['nombre']) ?></div>
              <?php if (!empty($p['descripcion'])): ?>
                <div class="prod-card-desc"><?= htmlspecialchars($p['descripcion']) ?></div>
              <?php endif; ?>
              <div class="prod-card-price">Bs <?= number_format((float)$p['precio'],1) ?></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>

  <?php endif; ?>

  <div class="text-center mt-4" style="font-size:.87rem;color:var(--text-muted)">
    💡 Pide tu bebida o comida al staff durante tu reserva.
  </div>
  <?php if (!isLogged()): ?>
  <div class="text-center mt-3">
    <a href="<?= BASE_URL ?>/index.php?r=auth/registro" class="btn btn-primary">Crea tu cuenta y reserva una sala</a>
    <a href="<?= BASE_URL ?>/index.php?r=auth/login" class="btn btn-outline-primary">Iniciar sesión</a>
  </div>
  <?php endif; ?>
</div>

<?php require ROOT_PATH . '/views/partials/footer.php'; ?>
<script>
function filtrarCat(cat, btn) {
  // Tabs
  document.querySelectorAll('.menu-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  // Secciones
  document.querySelectorAll('.cat-section').forEach(sec => {
    sec.style.display = (cat === 'todos' || sec.dataset.cat === cat) ? '' : 'none';
  });
}
</script>
<script>
const fp=document.getElementById('filtroProductos'); if(fp)fp.addEventListener('input',()=>{const q=fp.value.toLocaleLowerCase();document.querySelectorAll('[data-producto],.producto-card,.card').forEach(el=>{if(el.closest('nav')||el.closest('footer'))return;const txt=el.innerText.toLocaleLowerCase();if(el.querySelector('img')||el.classList.contains('producto-card'))el.style.display=txt.includes(q)?'':'none'})});
</script>
</body>
</html>
