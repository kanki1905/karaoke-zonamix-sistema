<?php
// ═══════════════════════════════════════════════════════════════
//  AuthController — Login, registro y cierre de sesión
// ═══════════════════════════════════════════════════════════════

class AuthController {

    // GET /auth/login
    public function login(): void {
        if (isLogged()) redirect(isAdmin() ? 'admin/dashboard' : 'client/inicio');
        $fd = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);
        if (!empty($_GET['fecha']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$_GET['fecha'])) $fd['fecha'] = (string)$_GET['fecha'];
        if (!empty($_GET['hora']) && preg_match('/^(?:0[8-9]|1[0-9]|2[0-2]):00$/', (string)$_GET['hora'])) { $fd['hora_ini'] = (string)$_GET['hora']; $fd['hora_fin'] = sprintf('%02d:00', (int)substr($_GET['hora'],0,2)+1); }
        view('auth/login', ['fd' => $fd]);
    }

    // POST /auth/login
    public function loginPost(): void {
        $email    = sanitize($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $_SESSION['form_data'] = ['email' => $email];
            flash('error', 'Completa todos los campos.');
            redirect('auth/login'); return;
        }

        $um   = new UsuarioModel();
        $user = $um->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['form_data'] = ['email' => $email];
            flash('error', 'Correo o contraseña incorrectos.');
            redirect('auth/login'); return;
        }

        if ((int)($user['activo'] ?? 1) !== 1) {
            $_SESSION['form_data'] = ['email' => $email];
            flash('error', 'Tu cuenta fue dada de baja. Comunícate con el administrador del local.');
            redirect('auth/login'); return;
        }

        $nombreCompleto = trim($user['nombre'] . ' ' . ($user['apellidos'] ?? ''));
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'       => $user['id'],
            'nombre'   => $nombreCompleto,
            'email'    => $user['email'],
            'rol'      => $user['rol'],
            'foto'     => $user['foto'],
        ];

        redirect($user['rol'] === 'admin' ? 'admin/dashboard' : 'client/inicio');
    }

    // GET /auth/registro
    public function registro(): void {
        if (isLogged()) redirect('client/inicio');
        $fd = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);
        if (!empty($_GET['fecha']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$_GET['fecha'])) $fd['fecha'] = (string)$_GET['fecha'];
        if (!empty($_GET['hora']) && preg_match('/^(?:0[8-9]|1[0-9]|2[0-2]):00$/', (string)$_GET['hora'])) { $fd['hora_ini'] = (string)$_GET['hora']; $fd['hora_fin'] = sprintf('%02d:00', (int)substr($_GET['hora'],0,2)+1); }
        view('auth/registro', ['fd' => $fd]);
    }

    // POST /auth/registro
    public function registroPost(): void {
        $nombres   = sanitize($_POST['nombres']   ?? '');
        $apellidos = sanitize($_POST['apellidos'] ?? '');
        $email     = sanitize($_POST['email']     ?? '');
        $telefono  = sanitize($_POST['telefono']  ?? '');
        $password  = trim($_POST['password'] ?? '');
        $confirm   = trim($_POST['confirm']  ?? '');

        // Helper para preservar campos en caso de error
        $saveForm = function() use ($nombres, $apellidos, $email, $telefono) {
            $_SESSION['form_data'] = [
                'nombres'   => $nombres,
                'apellidos' => $apellidos,
                'email'     => $email,
                'telefono'  => $telefono,
            ];
        };

        // Campos obligatorios
        if (mb_strlen($nombres,'UTF-8') > 50 || mb_strlen($apellidos,'UTF-8') > 50 || mb_strlen($email,'UTF-8') > 50 || strlen($password) > 25) {
            flash('error', 'Nombres, apellidos y correo admiten máximo 50 caracteres; la contraseña, máximo 25 caracteres.');
            $saveForm(); redirect('auth/registro'); return;
        }
        if (empty($nombres) || empty($apellidos) || empty($email) || empty($password)) {
            $saveForm();
            flash('error', 'Completa todos los campos obligatorios.');
            redirect('auth/registro'); return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $saveForm();
            flash('error', 'El correo electrónico no tiene un formato válido.');
            redirect('auth/registro'); return;
        }

        // Teléfono
        if (empty($telefono)) {
            $saveForm();
            flash('error', 'El número de teléfono es obligatorio.');
            redirect('auth/registro'); return;
        }
        if (!preg_match('/^[67][0-9]{7}$/', $telefono)) {
            $saveForm();
            flash('error', 'El teléfono debe iniciar con 6 o 7 y tener exactamente 8 dígitos (ej: 70123456).');
            redirect('auth/registro'); return;
        }

        // Contraseña: mín. 8 caracteres, letra + número + símbolo
        if ($password !== $confirm) {
            $saveForm();
            flash('error', 'Las contraseñas no coinciden. Vuelve a ingresarlas.');
            redirect('auth/registro'); return;
        }
        if (!preg_match('/^(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\W_]).{8,}$/', $password)) {
            $saveForm();
            flash('error', 'La contraseña debe tener mínimo 8 caracteres e incluir letras, números y un símbolo (ej: @, #, !).');
            redirect('auth/registro'); return;
        }

        $um = new UsuarioModel();

        if ($um->existeEmail($email)) {
            $saveForm();
            flash('error', 'Este correo ya está registrado en otra cuenta. ¿Quizás quieres <a href="' . BASE_URL . '/index.php?r=auth/login">iniciar sesión</a>?');
            redirect('auth/registro'); return;
        }
        if ($um->existeTelefono($telefono)) {
            $saveForm();
            flash('error', 'Este número de teléfono ya está vinculado a otra cuenta.');
            redirect('auth/registro'); return;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $uid  = $um->crear($nombres, $apellidos, $email, $telefono, $hash);

        $user = $um->findById($uid);
        $nombreCompleto = trim($user['nombre'] . ' ' . ($user['apellidos'] ?? ''));
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'     => $user['id'],
            'nombre' => $nombreCompleto,
            'email'  => $user['email'],
            'rol'    => $user['rol'],
            'foto'   => $user['foto'],
        ];

        flash('success', '¡Bienvenido/a ' . htmlspecialchars($nombres) . '! Tu cuenta ha sido creada.');
        redirect('client/inicio');
    }

    // GET /auth/logout
    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
        redirect('public/home');
    }
}
