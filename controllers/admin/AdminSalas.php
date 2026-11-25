<?php
// ═══════════════════════════════════════════════════════════════
//  AdminSalas — Gestión de salas
//  (se usa dentro de AdminController)
// ═══════════════════════════════════════════════════════════════

trait AdminSalas {

    // GET /admin/salas
    public function salas(): void {
        requireAdmin();
        $sm    = new SalaModel();
        $salas = $sm->listar();
        $reservasFuturasPorSala = [];
        foreach ($salas as $salaItem) $reservasFuturasPorSala[(int)$salaItem['id']] = $sm->reservasFuturasCount((int)$salaItem['id']);
        view('admin/salas', compact('salas','reservasFuturasPorSala'));
    }

    // GET /admin/sala-form?id=X
    public function salaForm(): void {
        requireAdmin();
        $id   = (int)($_GET['id'] ?? 0);
        $sm   = new SalaModel();
        $sala = $id ? $sm->findById($id) : null;
        view('admin/sala_form', compact('sala'));
    }

    // POST /admin/sala-guardar
    public function salaGuardar(): void {
        requireAdmin();
        $id     = (int)($_POST['id']           ?? 0);
        $nombre = preg_replace('/\s+/u', ' ', sanitize($_POST['nombre'] ?? ''));
        $desc   = sanitize($_POST['descripcion'] ?? '');
        $cap    = (int)($_POST['capacidad']    ?? 4);
        $precio = (int)($_POST['precio_hora']  ?? 50);
        $numero = (isset($_POST['numero']) && $_POST['numero'] !== '') ? (int)$_POST['numero'] : null;

        if (empty($nombre) || mb_strlen($nombre, 'UTF-8') > 100 || mb_strlen($desc, 'UTF-8') > 2000) {
            flash('error', 'El nombre de la sala es obligatorio (máximo 100 caracteres) y la descripción admite hasta 2000 caracteres.');
            redirect('admin/sala-form' . ($id ? "?id=$id" : '')); return;
        }

        // Validar unicidad del nombre y del número de cabina
        $sm = new SalaModel();
        if ($sm->existeNombre($nombre, $id)) {
            flash('error', 'Ya existe una sala con ese nombre. Elige un nombre diferente.');
            redirect('admin/sala-form' . ($id ? "?id=$id" : '')); return;
        }
        if ($numero !== null && $sm->existeNumeroSala($numero, $id)) {
            flash('error', 'El número de cabina #' . $numero . ' ya está en uso. Elige otro.');
            redirect('admin/sala-form' . ($id ? "?id=$id" : '')); return;
        }

        $img = null;
        if (!empty($_FILES['imagen']['name'])) {
            $file = $_FILES['imagen'];
            $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
            $mime = (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'] ?? '') && (int)$file['size'] <= 5*1024*1024) ? (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) : '';
            $valid = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp'];
            if (!isset($valid[$ext]) || $mime !== $valid[$ext]) {
                flash('error', 'La imagen debe ser JPG, PNG o WEBP válida y no superar 5 MB.');
                redirect('admin/sala-form' . ($id ? '?id='.$id : '')); return;
            }
            $dir = UPLOAD_PATH . '/salas';
            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) { flash('error','No se pudo preparar el directorio de imágenes.'); redirect('admin/salas'); return; }
            $img = 'sala_' . ($id ?: time()) . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $img)) { flash('error','No se pudo guardar la imagen.'); redirect('admin/salas'); return; }
        }

        if ($id) {
            $sm->actualizar($id, $nombre, $desc, $cap, $precio, $img, $numero);
            flash('success', 'Sala actualizada correctamente.');
        } else {
            $sm->crear($nombre, $desc, $cap, $precio, $img ?? '', $numero);
            flash('success', 'Sala creada correctamente.');
        }
        redirect('admin/salas');
    }

    // GET /admin/sala-toggle?id=X
    public function salaToggle(): void {
        requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $sm = new SalaModel();
        $sala = $sm->findById($id);
        if (!$sala) { flash('error', 'La sala no existe.'); redirect('admin/salas'); return; }
        $futuras = $sm->reservasFuturasCount($id);
        // Regla de negocio: nunca permitir mantenimiento mientras existan reservas futuras.
        if (!empty($sala['activa']) && $futuras > 0) {
            flash('error', 'No se puede poner en mantenimiento la sala «'.$sala['nombre'].'»: tiene '.$futuras.' reserva(s) futura(s). Reprograma o cancela esas reservas antes de intentarlo nuevamente.');
            redirect('admin/salas'); return;
        }
        $sm->toggleActiva($id);
        flash('success', 'Estado de la sala actualizado.');
        redirect('admin/salas');
    }

    // GET /admin/sala-eliminar?id=X
    public function salaEliminar(): void {
        requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $sm = new SalaModel();
        $sm->eliminar($id);
        flash('success', 'Sala eliminada.');
        redirect('admin/salas');
    }
}
