<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Productos — Admin</title>
  <?= themeHeadTag() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <?= cssTag() ?>
  <style>
    .prod-img { width:52px;height:52px;border-radius:10px;object-fit:contain;object-position:center;background:var(--surface-sunken);border:2px solid var(--border); }
    .prod-img-placeholder {
      width:52px;height:52px;border-radius:10px;
      background:var(--primary);
      display:inline-flex;align-items:center;justify-content:center;font-size:1.4rem;
    }
    .cat-badge { padding:.25rem .65rem;border-radius:50px;font-size:.74rem;font-weight:700;text-transform:uppercase; }
  </style>
</head>
<body>
<?php require ROOT_PATH . '/views/partials/admin_sidebar.php'; ?>
<div class="admin-content">
  <div class="admin-topbar">
    <h4>🍔 Productos — Bebidas y Comida</h4>
    <button class="btn btn-primary btn-sm" onclick="abrirCrear()">+ Nuevo Producto</button>
  </div>

  <?php if ($ok = flash('success')): ?>
    <div class="alert alert-success"><?= htmlspecialchars($ok) ?></div>
  <?php endif; ?>
  <?php if ($err = flash('error')): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <!-- Resumen por categoría -->
  <?php
    $conteo = ['bebida'=>0,'comida'=>0,'snack'=>0,'otro'=>0];
    foreach ($productos as $p) $conteo[$p['categoria']] = ($conteo[$p['categoria']] ?? 0) + 1;
  ?>
  <div class="row g-3 mb-4">
    <?php
    $cats = [
      'bebida' => ['🥤','Bebidas',  'var(--info-bg)','var(--info-text)'],
      'comida' => ['🍽️','Comidas',  'var(--success-bg)','var(--success-text)'],
      'snack'  => ['🍿','Snacks',   'var(--warning-bg)','var(--warning-text)'],
      'otro'   => ['📦','Otros',    'var(--bg-secondary)','var(--text-secondary)'],
    ];
    foreach ($cats as $key => [$ico,$lbl,$bg,$clr]): ?>
    <div class="col-6 col-md-3">
      <div class="card text-center py-2" style="background:<?= $bg ?>;border:none">
        <div style="font-size:1.6rem"><?= $ico ?></div>
        <div style="font-size:1.4rem;font-weight:800;color:<?= $clr ?>"><?= $conteo[$key] ?></div>
        <div style="font-size:.78rem;color:<?= $clr ?>"><?= $lbl ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="mb-3"><input type="search" class="form-control filtro-tabla" data-tabla="tablaProductos" placeholder="Buscar productos por nombre o categoría…" aria-label="Buscar productos por nombre o categoría…"></div>
  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="tablaProductos" class="table mb-0 align-middle">
          <thead>
            <tr>
              <th>ID</th><th>Foto</th><th>Nombre</th><th>Categoría</th>
              <th>Descripción</th><th>Precio</th><th>ID del administrador que lo registró</th><th>Estado</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($productos)): ?>
            <tr><td colspan="9" class="text-center py-5 text-muted">Sin productos aún. Haz clic en "+ Nuevo Producto".</td></tr>
          <?php endif; ?>
          <?php foreach ($productos as $p): ?>
          <tr>
            <td class="text-muted"><?= $p['id'] ?></td>
            <td>
              <?php if (!empty($p['imagen'])): ?>
                <img src="<?= UPLOAD_URL ?>/productos/<?= htmlspecialchars($p['imagen']) ?>"
                     class="prod-img" alt="<?= htmlspecialchars($p['nombre']) ?>">
              <?php else: ?>
                <?php $icos = ['bebida'=>'🥤','comida'=>'🍽️','snack'=>'🍿','otro'=>'📦']; ?>
                <span class="prod-img-placeholder"><?= $icos[$p['categoria']] ?? '📦' ?></span>
              <?php endif; ?>
            </td>
            <td class="fw-semibold"><?= htmlspecialchars($p['nombre']) ?></td>
            <td>
              <span class="cat-badge cat-<?= $p['categoria'] ?>">
                <?= ucfirst($p['categoria']) ?>
              </span>
            </td>
            <td style="max-width:200px;font-size:.83rem;color:var(--text-muted)">
              <?= htmlspecialchars(mb_strimwidth($p['descripcion'] ?? '', 0, 60, '…')) ?>
            </td>
            <td class="fw-bold text-brand">Bs <?= number_format((float)$p['precio'],1) ?></td><td><?= !empty($p['admin_id']) ? '#'.(int)$p['admin_id'].' — '.htmlspecialchars($p['administrador_nombre'] ?? 'Administrador') : 'Sin registro anterior' ?></td>
            <td>
              <?php if ($p['activo']): ?>
                <span class="badge-estado estado-confirmada">Activo</span>
              <?php else: ?>
                <span class="badge-estado estado-cancelada">Inactivo</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="d-flex gap-1 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-primary"
                        onclick="abrirEditar(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)">
                  ✏️ Editar
                </button>
                <form action="<?= BASE_URL ?>/index.php?r=admin/producto-toggle" method="POST" class="d-inline" id="toggleProd<?= (int)$p['id'] ?>">
                  <?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirmarEstadoProducto(event, this, <?= (int)$p['activo'] ?>, <?= htmlspecialchars(json_encode($p['nombre'], JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)"><?= $p['activo'] ? '⏸ Dar de baja' : '▶ Activar' ?></button>
                </form>
                <form action="<?= BASE_URL ?>/index.php?r=admin/producto-eliminar" method="POST" class="d-inline">
                  <?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que quieres eliminar definitivamente «<?= addslashes(htmlspecialchars($p['nombre'])) ?>»? Esta acción no se puede deshacer.')">🗑</button>
                </form>
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

<!-- ── Modal Crear / Editar Producto ──────────────────────────── -->
<div class="modal fade" id="modalProducto" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:var(--primary);color:var(--on-primary)">
        <h5 class="modal-title fw-bold" id="modalProdTitulo">➕ Nuevo Producto</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="<?= BASE_URL ?>/index.php?r=admin/producto-guardar"
            method="POST" enctype="multipart/form-data" id="frmProducto">
        <?= csrfField() ?>
        <div class="modal-body">
          <input type="hidden" name="id" id="prodId" value="0">

          <!-- Preview imagen actual -->
          <div class="text-center mb-3" id="prodFotoCont" style="display:none">
            <img id="prodFotoPreview" src="" alt="Foto actual"
                 class="img-preview-centrada" style="max-width:160px;height:120px">
            <div style="font-size:.75rem;color:var(--text-muted);margin-top:.3rem">Imagen actual</div>
          </div>

          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold" style="font-size:.85rem">Nombre <span class="text-danger">*</span></label>
              <input type="text" name="nombre" id="prodNombre" class="form-control" required maxlength="50"
                     placeholder="Ej: Coca-Cola, Salchipapas…">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold" style="font-size:.85rem">Categoría</label>
              <select name="categoria" id="prodCategoria" class="form-select">
                <option value="bebida">🥤 Bebida</option>
                <option value="comida">🍽️ Comida</option>
                <option value="snack">🍿 Snack</option>
                <option value="otro">📦 Otro</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold" style="font-size:.85rem">Precio (Bs) <span class="text-danger">*</span></label>
              <input type="text" name="precio" id="prodPrecio" class="form-control" required inputmode="decimal" autocomplete="off"
                     maxlength="9" pattern="\d{1,6}([.,][05]0?)?" placeholder="Ej: 10 ó 10.5"
                     title="Solo número entero o con .5 (ejemplo: 10 ó 10.5)">
              <div class="form-text">Entero o con medio: 10 ó 10.5. No se aceptan otros decimales.</div>
            </div>
            <div class="col-md-8">
              <label class="form-label fw-semibold" style="font-size:.85rem">Imagen <small class="fw-normal text-muted">(opcional, JPG/PNG/WEBP)</small></label>
              <input type="file" name="imagen" id="prodImagen" class="form-control" accept="image/*">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold" style="font-size:.85rem">Descripción</label>
              <textarea name="descripcion" id="prodDesc" class="form-control" rows="2" maxlength="2000"
                        placeholder="Descripción breve del producto…"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">💾 Guardar producto</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const UPLOAD = '<?= UPLOAD_URL ?>';
const modal  = new bootstrap.Modal(document.getElementById('modalProducto'));

function abrirCrear() {
  document.getElementById('modalProdTitulo').textContent = '➕ Nuevo Producto';
  document.getElementById('prodId').value        = '0';
  document.getElementById('prodNombre').value    = '';
  document.getElementById('prodDesc').value      = '';
  document.getElementById('prodPrecio').value    = '';
  document.getElementById('prodCategoria').value = 'bebida';
  document.getElementById('prodImagen').value    = '';
  document.getElementById('prodFotoCont').style.display = 'none';
  modal.show();
}

function abrirEditar(p) {
  document.getElementById('modalProdTitulo').textContent = '✏️ Editar Producto';
  document.getElementById('prodId').value        = p.id;
  document.getElementById('prodNombre').value    = p.nombre || '';
  document.getElementById('prodDesc').value      = p.descripcion || '';
  document.getElementById('prodPrecio').value    = p.precio ? String(Number(p.precio)) : '';
  document.getElementById('prodCategoria').value = p.categoria || 'bebida';
  document.getElementById('prodImagen').value    = '';

  const fotoCont = document.getElementById('prodFotoCont');
  if (p.imagen) {
    document.getElementById('prodFotoPreview').src = UPLOAD + '/productos/' + p.imagen;
    fotoCont.style.display = 'block';
  } else {
    fotoCont.style.display = 'none';
  }
  modal.show();
}
</script>
<script>
/* Precio: solo dígitos y, si hay decimal, únicamente .0 ó .5 (se bloquea cualquier otro decimal al escribir). */
(function(){
  const pr=document.getElementById('prodPrecio'); if(!pr) return;
  pr.addEventListener('input',()=>{
    let v=pr.value.replace(',','.').replace(/[^0-9.]/g,'');
    const i=v.indexOf('.');
    if(i>=0){ let ent=v.slice(0,i).replace(/\./g,''), dec=v.slice(i+1).replace(/\./g,'');
      dec=dec.slice(0,1); if(dec!=='' && dec!=='0' && dec!=='5') dec='';
      v=(ent===''?'0':ent)+'.'+dec; }
    pr.value=v.slice(0,9);
    pr.setCustomValidity(pr.value!=='' && !/^\d{1,6}(\.[05])?$/.test(pr.value) ? 'Escribe un entero o un número con .5' : '');
  });
  pr.addEventListener('blur',()=>{ if(/\.$/.test(pr.value)) pr.value=pr.value.slice(0,-1); });
})();
</script>
<script>
document.querySelectorAll('.filtro-tabla').forEach(inp=>inp.addEventListener('input',()=>{const t=document.getElementById(inp.dataset.tabla);if(!t)return;const q=inp.value.toLocaleLowerCase();t.querySelectorAll('tbody tr').forEach(r=>r.style.display=r.innerText.toLocaleLowerCase().includes(q)?'':'none')}));
</script>

<div class="modal fade" id="modalEstadoProducto" tabindex="-1" aria-labelledby="tituloEstadoProducto" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header" style="background:var(--primary);color:var(--on-primary)">
      <h5 class="modal-title fw-bold" id="tituloEstadoProducto">⚠️ Confirmar cambio de producto</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
    </div>
    <div class="modal-body"><p id="textoEstadoProducto" class="mb-0"></p></div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" id="btnAceptarEstadoProducto" class="btn btn-primary">Sí, continuar</button></div>
  </div></div>
</div>
<script>
let formularioEstadoProducto = null;
function confirmarEstadoProducto(event, enlace, activo, nombre) {
  event.preventDefault(); formularioEstadoProducto = enlace.form;
  const titulo = document.getElementById('tituloEstadoProducto');
  const texto = document.getElementById('textoEstadoProducto');
  if (activo) {
    titulo.textContent = '⚠️ ¿Dar de baja este producto?';
    texto.textContent = `El producto «${nombre}» dejará de aparecer a los clientes en el catálogo. No se eliminará definitivamente y podrás volver a activarlo después. ¿Deseas continuar?`;
    document.getElementById('btnAceptarEstadoProducto').textContent = 'Sí, dar de baja';
  } else {
    titulo.textContent = '▶ ¿Activar este producto?';
    texto.textContent = `El producto «${nombre}» volverá a aparecer en el catálogo de clientes. ¿Deseas activarlo?`;
    document.getElementById('btnAceptarEstadoProducto').textContent = 'Sí, activar';
  }
  bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEstadoProducto')).show();
  return false;
}
document.getElementById('btnAceptarEstadoProducto').addEventListener('click', () => { if (formularioEstadoProducto) formularioEstadoProducto.submit(); });
</script>

</body></html>
