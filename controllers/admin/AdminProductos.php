<?php
// ═══════════════════════════════════════════════════════════════
//  AdminProductos — Catálogo de productos
//  (se usa dentro de AdminController)
// ═══════════════════════════════════════════════════════════════

trait AdminProductos {

    // GET /admin/productos
    public function productos(): void {
        requireAdmin();
        $pm       = new ProductoModel();
        $productos = $pm->listar();
        view('admin/productos', compact('productos'));
    }

    // POST /admin/producto-guardar
    public function productoGuardar(): void {
        requireAdmin();
        $id     = (int)($_POST['id']             ?? 0);
        $nombre = preg_replace('/\s+/u', ' ', sanitize($_POST['nombre'] ?? ''));
        $desc   = sanitize($_POST['descripcion'] ?? '');
        $precioTxt = str_replace(',', '.', trim((string)($_POST['precio'] ?? '')));
        $precio = (float)$precioTxt;
        // Solo enteros o con medio (.5): 10, 10.0, 10.5 … nada de 10.25 ni 10.75
        $precioMedio = preg_match('/^\d{1,6}(\.\d{1,2})?$/', $precioTxt) === 1 && abs($precio * 2 - round($precio * 2)) < 1e-9;
        if (!$precioMedio || $precio < 0.5) {
            flash('error', 'El precio debe ser un número entero o terminar en .5 (por ejemplo 10 o 10.5). No se permiten otros decimales.');
            redirect('admin/productos'); return;
        }
        $cat    = sanitize($_POST['categoria']   ?? 'bebida');

        if (!$nombre || mb_strlen($nombre, 'UTF-8') > 50 || mb_strlen($desc, 'UTF-8') > 2000 || $precio > 999999.5) {
            flash('error', 'El nombre del producto admite hasta 50 caracteres, la descripción hasta 2000 y el precio debe estar entre 0.5 y 999999.5 Bs.');
            redirect('admin/productos'); return;
        }

        $pm  = new ProductoModel();
        if ($pm->existeNombre($nombre, $id)) {
            flash('error', 'Ya existe un producto con ese nombre. Elige un nombre diferente.');
            redirect('admin/productos'); return;
        }
        $img = null;

        if (!empty($_FILES['imagen']['name'])) {
            $file = $_FILES['imagen'];
            $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
            $mime = (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'] ?? '') && (int)$file['size'] <= 5*1024*1024) ? (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) : '';
            $valid = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp'];
            if (!isset($valid[$ext]) || $mime !== $valid[$ext]) { flash('error','La imagen debe ser JPG, PNG o WEBP válida y no superar 5 MB.'); redirect('admin/productos'); return; }
            $dir = UPLOAD_PATH . '/productos';
            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) { flash('error','No se pudo preparar el directorio de imágenes.'); redirect('admin/productos'); return; }
            $img = 'prod_' . ($id ?: time()) . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $img)) { flash('error','No se pudo guardar la imagen.'); redirect('admin/productos'); return; }
        }

        if ($id) {
            $pm->actualizar($id, $nombre, $desc, $precio, $cat, $img);
            flash('success', 'Producto actualizado correctamente.');
        } else {
            $pm->crear($nombre, $desc, $precio, $cat, $img, (int)$_SESSION['user']['id']);
            flash('success', 'Producto creado correctamente.');
        }
        redirect('admin/productos');
    }

    // GET /admin/producto-toggle?id=X
    public function productoToggle(): void {
        requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $pm = new ProductoModel();
        $pm->toggleActivo($id);
        flash('success', 'Estado del producto actualizado.');
        redirect('admin/productos');
    }

    // GET /admin/producto-eliminar?id=X
    public function productoEliminar(): void {
        requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $pm = new ProductoModel();
        $pm->eliminar($id);
        flash('success', 'Producto eliminado.');
        redirect('admin/productos');
    }
}
