<?php
// ═══════════════════════════════════════════════════════════════
//  KARAOKE ZONA MIX — Configuración Global v6
// ═══════════════════════════════════════════════════════════════

// ── Base URL dinámica ──────────────────────────────────────────
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'karaoke2.test';
    if (!preg_match('/^[a-zA-Z0-9._-]+(?::[0-9]{1,5})?$/', $host)) $host = 'localhost';
    $script   = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $base     = rtrim($script, '/\\');
    define('BASE_URL', $protocol . '://' . $host . $base);
}

// ── Rutas de sistema ───────────────────────────────────────────
if (!defined('ROOT_PATH'))    define('ROOT_PATH',    dirname(__DIR__));
if (!defined('UPLOAD_PATH'))  define('UPLOAD_PATH',  ROOT_PATH . '/public/uploads');
// Los archivos de /public (CSS, imágenes subidas) se sirven a través de index.php.
// Así funcionan igual en Laragon (con o sin virtual host), XAMPP o cualquier carpeta.
if (!defined('UPLOAD_URL'))   define('UPLOAD_URL',   BASE_URL  . '/index.php?r=asset&p=uploads');

// ── Sesión ─────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_name('KARAOKESESS');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ── Zona horaria ───────────────────────────────────────────────
date_default_timezone_set('America/La_Paz');
