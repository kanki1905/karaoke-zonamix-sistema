<?php
// ═══════════════════════════════════════════════════════════════
//  AdminCuenta — Perfil del administrador: edita SOLO sus propios datos y su contraseña
//  (se usa dentro de AdminController)
// ═══════════════════════════════════════════════════════════════

trait AdminCuenta {

    // GET /admin/mi-cuenta
    public function cuenta(): void {
        requireAdmin();
        $user = (new UsuarioModel())->findById((int)$_SESSION['user']['id']);
        view('admin/mi_cuenta', compact('user'));
    }

    // POST /admin/mi-cuenta-post — datos personales y foto (solo de su propia cuenta)
    public function cuentaPost(): void {
        requireAdmin();
        $uid       = (int)$_SESSION['user']['id'];
        $nombre    = sanitize($_POST['nombre']    ?? '');
        $apellidos = sanitize($_POST['apellidos'] ?? '');
        $email     = sanitize($_POST['email']     ?? '');
        $telefono  = sanitize($_POST['telefono']  ?? '');
        $um        = new UsuarioModel();

        if ($nombre === '' || $apellidos === '' || $email === '' || mb_strlen($nombre, 'UTF-8') > 50 || mb_strlen($apellidos, 'UTF-8') > 50 || mb_strlen($email, 'UTF-8') > 50) {
            flash('error', 'Revisa los datos: nombres, apellidos y correo son obligatorios (hasta 50 caracteres).');
            redirect('admin/mi-cuenta'); return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { flash('error', 'Correo no válido.'); redirect('admin/mi-cuenta'); return; }
        if (!preg_match('/^[67][0-9]{7}$/', $telefono)) {
            flash('error', 'El teléfono debe iniciar con 6 o 7 y tener exactamente 8 dígitos.');
            redirect('admin/mi-cuenta'); return;
        }
        if ($um->existeTelefono($telefono, $uid)) { flash('error', 'Ese teléfono ya está en uso por otra cuenta.'); redirect('admin/mi-cuenta'); return; }
        if ($um->existeEmail($email, $uid))       { flash('error', 'Ese correo ya lo usa otra cuenta.'); redirect('admin/mi-cuenta'); return; }

        $um->actualizar($uid, $nombre, $apellidos, $email, $telefono);

        // Foto de perfil (jpg, png o webp, máx. 2 MB, verificada como imagen real)
        if (!empty($_FILES['foto']['name']) && ($_FILES['foto']['error'] ?? 1) === UPLOAD_ERR_OK) {
            $f   = $_FILES['foto'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $info = @getimagesize($f['tmp_name']);
            if (in_array($ext, ['jpg','jpeg','png','webp'], true) && $info !== false && $f['size'] <= 2 * 1024 * 1024) {
                $dir = UPLOAD_PATH . '/perfiles';
                if (!is_dir($dir)) mkdir($dir, 0775, true);
                $archivo = 'perfil_' . $uid . '.' . $ext;
                if (move_uploaded_file($f['tmp_name'], $dir . '/' . $archivo)) {
                    $um->actualizarFoto($uid, $archivo);
                    $_SESSION['user']['foto'] = $archivo;
                }
            } else {
                flash('error', 'Datos guardados, pero la foto no es válida (solo JPG, PNG o WEBP de hasta 2 MB).');
                $_SESSION['user']['nombre'] = trim($nombre . ' ' . $apellidos); $_SESSION['user']['email'] = $email;
                redirect('admin/mi-cuenta'); return;
            }
        }

        $_SESSION['user']['nombre'] = trim($nombre . ' ' . $apellidos);
        $_SESSION['user']['email']  = $email;
        flash('success', 'Tu perfil se actualizó correctamente.');
        redirect('admin/mi-cuenta');
    }

    // POST /admin/mi-cuenta-password
    public function cuentaPassword(): void {
        requireAdmin();
        $uid     = (int)$_SESSION['user']['id'];
        $actual  = (string)($_POST['actual']  ?? '');
        $nueva   = (string)($_POST['nueva']   ?? '');
        $confirm = (string)($_POST['confirm'] ?? '');

        $um   = new UsuarioModel();
        $user = $um->findById($uid);

        if (!$user || !password_verify($actual, $user['password'])) {
            flash('error', 'La contraseña actual no es correcta.');
            redirect('admin/mi-cuenta'); return;
        }
        if ($nueva !== $confirm) {
            flash('error', 'Las contraseñas nuevas no coinciden.');
            redirect('admin/mi-cuenta'); return;
        }
        if (!preg_match('/^(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\\W_]).{8,25}$/', $nueva)) {
            flash('error', 'La contraseña debe tener de 8 a 25 caracteres con letras, números y un símbolo (ej: Karaoke1!).');
            redirect('admin/mi-cuenta'); return;
        }
        if (hash_equals($actual, $nueva)) {
            flash('error', 'La contraseña nueva debe ser diferente a la actual.');
            redirect('admin/mi-cuenta'); return;
        }
        $um->cambiarPassword($uid, password_hash($nueva, PASSWORD_DEFAULT));
        flash('success', 'Tu contraseña se cambió correctamente.');
        redirect('admin/mi-cuenta');
    }
}
