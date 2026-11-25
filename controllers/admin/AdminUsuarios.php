<?php
// ═══════════════════════════════════════════════════════════════
//  AdminUsuarios — Usuarios y cuentas administradoras
//  (se usa dentro de AdminController)
// ═══════════════════════════════════════════════════════════════

trait AdminUsuarios {

    // GET /admin/usuarios
    public function usuarios(): void {
        requireAdmin();
        $um       = new UsuarioModel();
        $usuarios = $um->listar();
        view('admin/usuarios', compact('usuarios'));
    }

    // POST /admin/usuario-crear-admin
    public function usuarioCrearAdmin(): void {
        requireAdmin();
        $nombre=sanitize($_POST['nombre']??''); $apellidos=sanitize($_POST['apellidos']??'');
        $email=sanitize($_POST['email']??''); $telefono=sanitize($_POST['telefono']??''); $password=(string)($_POST['password']??'');
        if (!$nombre || !$apellidos || mb_strlen($nombre,'UTF-8')>50 || mb_strlen($apellidos,'UTF-8')>50 || mb_strlen($email,'UTF-8')>50 || !filter_var($email,FILTER_VALIDATE_EMAIL) || !preg_match('/^[67][0-9]{7}$/',$telefono) || !preg_match('/^(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\W_]).{8,25}$/',$password)) { flash('error','Revisa los datos: nombres y apellidos hasta 50 caracteres, correo válido, teléfono de 8 dígitos que inicie en 6 o 7 y contraseña de 8 a 25 caracteres con letra, número y símbolo.'); redirect('admin/usuarios'); return; }
        $um=new UsuarioModel(); if($um->existeEmail($email) || $um->existeTelefono($telefono)){ flash('error','El correo o teléfono ya pertenece a otra cuenta.'); redirect('admin/usuarios'); return; }
        $db=Database::getInstance(); $q=$db->prepare("INSERT INTO usuarios (nombre,apellidos,email,telefono,password,rol) VALUES (?,?,?,?,?,'admin')");
        $q->execute([$nombre,$apellidos,$email,$telefono,password_hash($password,PASSWORD_DEFAULT)]);
        flash('success','Nuevo administrador creado correctamente.'); redirect('admin/usuarios');
    }

    /** Contraseña restablecida = celular + iniciales (nombres y apellidos), sin tildes. Ej.: 71234501MFCL */
    private static function passwordRestablecida(array $u): string {
        $nombreCompleto = html_entity_decode(trim(($u['nombre'] ?? '') . ' ' . ($u['apellidos'] ?? '')), ENT_QUOTES, 'UTF-8');
        $sinTildes = ['Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ü'=>'U','Ñ'=>'N'];
        $ini = '';
        foreach (preg_split('/\s+/u', $nombreCompleto, -1, PREG_SPLIT_NO_EMPTY) as $palabra) {
            $c = mb_strtoupper(mb_substr($palabra, 0, 1, 'UTF-8'), 'UTF-8');
            $ini .= strtr($c, $sinTildes);
        }
        return preg_replace('/[^A-Z0-9]/', '', $ini);
    }

    // POST /admin/usuario-restablecer-password
    public function usuarioRestablecerPassword(): void {
        requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $um = new UsuarioModel();
        $u  = $um->findById($id);
        if (!$u) { flash('error', 'Usuario no encontrado.'); redirect('admin/usuarios'); return; }
        if ($id === (int)$_SESSION['user']['id']) {
            flash('error', 'Tu propia contraseña la cambias en «Mi perfil», con tu contraseña actual.');
            redirect('admin/usuarios'); return;
        }
        $telefono = preg_replace('/\D/', '', (string)($u['telefono'] ?? ''));
        $iniciales = self::passwordRestablecida($u);
        if ($telefono === '' || $iniciales === '') {
            flash('error', 'Este usuario no tiene teléfono registrado. Edita sus datos y vuelve a intentarlo.');
            redirect('admin/usuarios'); return;
        }
        $nueva = $telefono . $iniciales;
        $um->cambiarPassword($id, password_hash($nueva, PASSWORD_DEFAULT));
        $nombre = html_entity_decode(trim(($u['nombre'] ?? '') . ' ' . ($u['apellidos'] ?? '')), ENT_QUOTES, 'UTF-8');
        flash('success', "Contraseña de $nombre restablecida. Nueva contraseña: $nueva  — entrégasela en persona; ella o él puede cambiarla desde su perfil.");
        redirect('admin/usuarios');
    }

    // POST /admin/usuario-toggle — dar de baja / dar de alta (no borra el registro)
    public function usuarioToggle(): void {
        requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id === (int)$_SESSION['user']['id']) {
            flash('error','No puedes dar de baja tu propia cuenta.');
            redirect('admin/usuarios'); return;
        }
        $um = new UsuarioModel();
        $u  = $um->findById($id);
        if (!$u) { flash('error','Usuario no encontrado.'); redirect('admin/usuarios'); return; }
        $nuevo  = ((int)($u['activo'] ?? 1) === 1) ? 0 : 1;
        $um->cambiarEstado($id, $nuevo);
        $nombre = html_entity_decode(trim(($u['nombre'] ?? '') . ' ' . ($u['apellidos'] ?? '')), ENT_QUOTES, 'UTF-8');
        flash('success', $nuevo ? "Usuario $nombre dado de alta. Ya puede iniciar sesión." : "Usuario $nombre dado de baja. No podrá iniciar sesión hasta que lo des de alta.");
        redirect('admin/usuarios');
    }
}
