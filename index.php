<?php
// ═══════════════════════════════════════════════════════════════
//  KARAOKE ZONA MIX — Front Controller v6
// ═══════════════════════════════════════════════════════════════

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/helpers.php';
require_once __DIR__ . '/models/Models.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ClienteController.php';
require_once __DIR__ . '/controllers/PublicController.php';
require_once __DIR__ . '/controllers/AdminController.php';

// Cabeceras básicas de seguridad (sin interferir con los estilos actuales).
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') header('Strict-Transport-Security: max-age=15552000; includeSubDomains');

// ── Archivos estáticos de /public (CSS, imágenes) ──────────────
if (($_GET['r'] ?? '') === 'asset') {
    $rel  = str_replace('\\', '/', (string)($_GET['p'] ?? ''));
    $base = realpath(ROOT_PATH . '/public');
    $file = realpath(ROOT_PATH . '/public/' . ltrim($rel, '/'));
    $mime = ['css'=>'text/css; charset=utf-8','js'=>'application/javascript; charset=utf-8','png'=>'image/png',
             'jpg'=>'image/jpeg','jpeg'=>'image/jpeg','gif'=>'image/gif','webp'=>'image/webp','svg'=>'image/svg+xml','ico'=>'image/x-icon'];
    $ext  = strtolower(pathinfo((string)$file, PATHINFO_EXTENSION));
    $ok   = $file && $base && strncmp($file, $base . DIRECTORY_SEPARATOR, strlen($base) + 1) === 0 && is_file($file) && isset($mime[$ext]);
    // Los comprobantes de pago solo los puede ver un usuario con sesión iniciada.
    if ($ok && strpos(str_replace('\\', '/', $file), '/uploads/comprobantes/') !== false && empty($_SESSION['user'])) $ok = false;
    if (!$ok) { http_response_code(404); exit; }
    session_write_close();
    header('Content-Type: ' . $mime[$ext]);
    header('Content-Length: ' . filesize($file));
    header('Cache-Control: public, max-age=86400');
    readfile($file);
    exit;
}

// ── Si la cuenta fue dada de baja mientras tenía sesión abierta, se cierra ──
if (!empty($_SESSION['user']['id'])) {
    try {
        if (!(new UsuarioModel())->estaActivo((int)$_SESSION['user']['id'])) {
            unset($_SESSION['user']);
            $_SESSION['flash']['error'] = 'Tu cuenta fue dada de baja. Comunícate con el administrador del local.';
            if (($_GET['r'] ?? '') !== 'auth/login') redirect('auth/login');
        }
    } catch (Throwable $e) { error_log('[Karaoke Zona Mix] estaActivo: ' . $e->getMessage()); }
}

// ── Confirmación automática: reservas pendientes cuya hora de inicio ya llegó ──
try { (new ReservaModel())->autoConfirmarPendientes(); }
catch (Throwable $e) { error_log('[Karaoke Zona Mix] autoConfirmarPendientes: ' . $e->getMessage()); }

// ── Leer ruta desde query string ?r=controller/action ─────────
$route = trim((string)($_GET['r'] ?? 'public/home'), '/');
$parts = explode('/', $route);
$ctrl  = $parts[0] ?? 'public';
$act   = $parts[1] ?? 'home';

// ── Tabla de rutas ─────────────────────────────────────────────
$routes = [
    // Públicas
    'public' => [
        'home'           => [PublicController::class, 'home'],
        'salas'          => [PublicController::class, 'salas'],
        'menu'           => [PublicController::class, 'menu'],
        'disponibilidad' => [PublicController::class, 'disponibilidad'],
    ],
    // Auth
    'auth' => [
        'login'           => [AuthController::class, 'login'],
        'login-post'      => [AuthController::class, 'loginPost'],
        'registro'        => [AuthController::class, 'registro'],
        'registro-post'   => [AuthController::class, 'registroPost'],
        'logout'          => [AuthController::class, 'logout'],
    ],
    // Cliente
    'client' => [
        'inicio'                => [ClienteController::class, 'inicio'],
        'reservar'              => [ClienteController::class, 'reservar'],
        'reservar-post'         => [ClienteController::class, 'reservarPost'],
        'mis-reservas'          => [ClienteController::class, 'misReservas'],
        'menu'                  => [ClienteController::class, 'menu'],
        'cancelar'              => [ClienteController::class, 'cancelar'],
        'comprobante'           => [ClienteController::class, 'comprobante'],
        'metodo-pago'           => [ClienteController::class, 'metodoPagoPost'],
        'comprobante-post'      => [ClienteController::class, 'comprobantePost'],
        'descargar-comprobante' => [ClienteController::class, 'descargarComprobante'],
        'perfil'                => [ClienteController::class, 'perfil'],
        'perfil-post'           => [ClienteController::class, 'perfilPost'],
        'cambiar-password'      => [ClienteController::class, 'cambiarPassword'],
        'horario'               => [ClienteController::class, 'horario'],
        'chat'                  => [ClienteController::class, 'chat'],
        'chat-post'             => [ClienteController::class, 'chatPost'],
        'chat-poll'             => [ClienteController::class, 'chatPoll'],
        'chat-unread'           => [ClienteController::class, 'chatUnread'],
        'horario-semana'        => [ClienteController::class, 'horarioSemana'],
    ],
    // Admin
    'admin' => [
        'dashboard'           => [AdminController::class, 'dashboard'],
        'salas'               => [AdminController::class, 'salas'],
        'sala-form'           => [AdminController::class, 'salaForm'],
        'sala-guardar'        => [AdminController::class, 'salaGuardar'],
        'sala-toggle'         => [AdminController::class, 'salaToggle'],
        'sala-eliminar'       => [AdminController::class, 'salaEliminar'],
        'reservas'            => [AdminController::class, 'reservas'],
        'reserva-detalle'     => [AdminController::class, 'reservaDetalle'],
        'reserva-estado'      => [AdminController::class, 'reservaEstado'],
        'registrar-pago'      => [AdminController::class, 'registrarPago'],
        'comprobantes'        => [AdminController::class, 'comprobantes'],
        'comprobante-revisar'      => [AdminController::class, 'comprobanteRevisar'],
        'comprobante-accion'       => [AdminController::class, 'comprobanteAccion'],
        'descargar-comprobante'    => [AdminController::class, 'descargarComprobante'],
        'usuarios'            => [AdminController::class, 'usuarios'],
        'usuario-crear-admin' => [AdminController::class, 'usuarioCrearAdmin'],
        'usuario-toggle'      => [AdminController::class, 'usuarioToggle'],
        'usuario-restablecer-password' => [AdminController::class, 'usuarioRestablecerPassword'],
        'mi-cuenta'           => [AdminController::class, 'cuenta'],
        'mi-cuenta-post'      => [AdminController::class, 'cuentaPost'],
        'mi-cuenta-password'  => [AdminController::class, 'cuentaPassword'],
        'reportes'            => [AdminController::class, 'reportes'],
        'productos'           => [AdminController::class, 'productos'],
        'producto-guardar'    => [AdminController::class, 'productoGuardar'],
        'producto-toggle'     => [AdminController::class, 'productoToggle'],
        'producto-eliminar'   => [AdminController::class, 'productoEliminar'],
        'chats'               => [AdminController::class, 'chats'],
        'chat-ver'            => [AdminController::class, 'chatVer'],
        'chat-responder'      => [AdminController::class, 'chatResponder'],
        'chat-poll'           => [AdminController::class, 'chatPoll'],
        'chat-unread'         => [AdminController::class, 'chatUnread'],
    ],
];

// ── Validación CSRF centralizada para todas las operaciones POST ──
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !csrfValid($_POST['_csrf_token'] ?? null)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Solicitud rechazada: token de seguridad inválido o vencido. Recarga la página e inténtalo de nuevo.';
    exit;
}

// Las operaciones que modifican datos no deben ejecutarse por GET.
$metodosPostObligatorios = [
    'client/cancelar',
    'admin/sala-toggle', 'admin/sala-eliminar', 'admin/usuario-crear-admin',
    'admin/reserva-estado', 'admin/registrar-pago', 'admin/usuario-toggle',
    'admin/producto-toggle', 'admin/producto-eliminar',
    'admin/usuario-restablecer-password', 'admin/mi-cuenta-password',
];
if (in_array($ctrl . '/' . $act, $metodosPostObligatorios, true) && ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo 'Método no permitido. Esta operación requiere un formulario seguro.';
    exit;
}

// ── Dispatch ───────────────────────────────────────────────────
if (isset($routes[$ctrl][$act])) {
    [$class, $method] = $routes[$ctrl][$act];
    (new $class())->$method();
} else {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
    <title>404 — Karaoke Zona Mix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head><body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
    <div class="text-center">
      <h1 class="display-1 fw-bold text-primary">404</h1>
      <p class="lead">Página no encontrada.</p>
      <a href="' . BASE_URL . '" class="btn btn-primary">Volver al inicio</a>
    </div></body></html>';
}
