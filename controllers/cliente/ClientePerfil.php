<?php
// ═══════════════════════════════════════════════════════════════
//  ClientePerfil — Perfil y cambio de contraseña
//  (se usa dentro de ClienteController)
// ═══════════════════════════════════════════════════════════════

trait ClientePerfil {

    // GET /client/perfil
    public function perfil(): void {
        requireLogin();
        $um   = new UsuarioModel();
        $user = $um->findById($_SESSION['user']['id']);
        view('client/perfil', compact('user'));
    }

    // POST /client/perfil
    public function perfilPost(): void {
        requireLogin();
        $uid       = $_SESSION['user']['id'];
        $nombre    = sanitize($_POST['nombre']    ?? '');
        $apellidos = sanitize($_POST['apellidos'] ?? '');
        $email     = sanitize($_POST['email']     ?? '');
        $telefono  = sanitize($_POST['telefono']  ?? '');

        if (empty($nombre) || empty($email) || mb_strlen($nombre, 'UTF-8') > 50 || mb_strlen($apellidos, 'UTF-8') > 50 || mb_strlen($email, 'UTF-8') > 50 || mb_strlen($telefono, 'UTF-8') > 8) {
            flash('error', 'Revisa los límites: nombre y apellidos hasta 50 caracteres, correo hasta 50 y teléfono hasta 8 dígitos.');
            redirect('client/perfil'); return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Correo no válido.');
            redirect('client/perfil'); return;
        }

        // Validar teléfono si viene
        if (!empty($telefono)) {
            if (!preg_match('/^[67][0-9]{7}$/', $telefono)) {
                flash('error', 'El teléfono debe iniciar con 6 o 7 y tener exactamente 8 dígitos.');
                redirect('client/perfil'); return;
            }
            $um2 = new UsuarioModel();
            if ($um2->existeTelefono($telefono, $uid)) {
                flash('error', 'Ese teléfono ya está en uso por otra cuenta.');
                redirect('client/perfil'); return;
            }
        }

        $um = new UsuarioModel();
        if ($um->existeEmail($email, $uid)) {
            flash('error', 'Ese correo ya lo usa otra cuenta.');
            redirect('client/perfil'); return;
        }

        $um->actualizar($uid, $nombre, $apellidos, $email, $telefono);

        // Foto de perfil
        if (!empty($_FILES['foto']['name'])) {
            $file    = $_FILES['foto'];
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];
            if (in_array($ext, $allowed)) {
                $dir = UPLOAD_PATH . '/perfiles';
                if (!is_dir($dir)) mkdir($dir, 0775, true);
                $nombre_f = 'perfil_' . $uid . '.' . $ext;
                move_uploaded_file($file['tmp_name'], $dir . '/' . $nombre_f);
                $um->actualizarFoto($uid, $nombre_f);
                $_SESSION['user']['foto'] = $nombre_f;
            }
        }

        $_SESSION['user']['nombre'] = trim($nombre . ' ' . $apellidos);
        $_SESSION['user']['email']  = $email;
        flash('success', 'Perfil actualizado correctamente.');
        redirect('client/perfil');
    }

    // POST /client/cambiar-password
    public function cambiarPassword(): void {
        requireLogin();
        $uid      = $_SESSION['user']['id'];
        $actual   = trim($_POST['actual']  ?? '');
        $nueva    = trim($_POST['nueva']   ?? '');
        $confirm  = trim($_POST['confirm'] ?? '');

        $um   = new UsuarioModel();
        $user = $um->findById($uid);

        if (!password_verify($actual, $user['password'])) {
            flash('error', 'La contraseña actual no es correcta.');
            redirect('client/perfil'); return;
        }
        if (strlen($nueva) > 25 || strlen($confirm) > 25) {
            flash('error', 'La contraseña nueva no puede superar los 25 caracteres.');
            redirect('client/perfil'); return;
        }
        if ($nueva !== $confirm) {
            flash('error', 'Las contraseñas nuevas no coinciden.');
            redirect('client/perfil'); return;
        }
        if (!preg_match('/^(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\W_]).{8,25}$/', $nueva)) {
            flash('error', 'La contraseña debe tener mínimo 8 caracteres con letras, números y un símbolo (ej: Karaoke1!).');
            redirect('client/perfil'); return;
        }

        $um->cambiarPassword($uid, password_hash($nueva, PASSWORD_DEFAULT));
        flash('success', 'Contraseña cambiada exitosamente.');
        redirect('client/perfil');
    }
}
