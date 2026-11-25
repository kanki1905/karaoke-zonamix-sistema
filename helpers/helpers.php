<?php
// ═══════════════════════════════════════════════════════════════
//  KARAOKE ZONA MIX — Helpers v6
// ═══════════════════════════════════════════════════════════════

/** Redirige a una ruta relativa a BASE_URL */
function redirect(string $path): void {
    // Si el path incluye ? (ej: 'client/reservar?sala=3'), lo convierte en &
    if (strpos($path, '?') !== false) {
        [$route, $qs] = explode('?', $path, 2);
        $url = BASE_URL . '/index.php?r=' . ltrim($route, '/') . '&' . $qs;
    } else {
        $url = BASE_URL . '/index.php?r=' . ltrim($path, '/');
    }
    header('Location: ' . $url);
    exit;
}


/** URL de un archivo dentro de /public (ej: asset('css/style.css')). */
function asset(string $path): string {
    $file = ROOT_PATH . '/public/' . ltrim($path, '/');
    $v = is_file($file) ? filemtime($file) : time();
    return BASE_URL . '/index.php?r=asset&p=' . ltrim($path, '/') . '&v=' . $v;
}

/** Inserta el CSS del sitio directamente en la página (no depende de ninguna URL). */
function cssTag(): string {
    $file = ROOT_PATH . '/public/css/style.css';
    $css  = is_file($file) ? file_get_contents($file) : '';
    return "<!-- KZM-BUILD v10.2 -->\n<style>\n" . $css . "\n</style>";
}

/** Inserta un script de public/js directamente en la página (no depende de ninguna URL, igual que cssTag). */
function inlineJs(string $name): string {
    $name = basename($name);                                   // solo archivos de public/js
    $file = ROOT_PATH . '/public/js/' . $name;
    $js   = is_file($file) ? file_get_contents($file) : '';
    $js   = str_ireplace('</script', '<\/script', $js);       // nunca cierra el <script> por accidente
    return "<script>\n" . $js . "\n</script>";
}

/**
 * Tema claro/oscuro: fija html[data-theme] ANTES del primer paint (sin parpadeo)
 * leyendo localStorage['kzm-theme'] y, si no hay valor, prefers-color-scheme;
 * después incrusta public/js/theme.js (único script del switch). Va en el <head>,
 * antes del CSS.
 */
function themeHeadTag(): string {
    $boot = <<<'JS'
(function(){var d=document.documentElement,t=null;try{t=localStorage.getItem('kzm-theme');}catch(e){}if(t!=='dark'&&t!=='light'){try{t=matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}catch(e){t='light';}}d.setAttribute('data-theme',t);})();
JS;
    return "<script>" . $boot . "</script>\n  " . inlineJs('theme.js');
}

/** Token CSRF para formularios que modifican datos. */
function csrfToken(): string {
    if (empty($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}
function csrfField(): string {
    return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}
function csrfValid(mixed $token): bool {
    return is_string($token) && isset($_SESSION['_csrf_token']) && is_string($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
}

/** Guarda / lee mensajes flash en sesión */
function flash(string $key, string $msg = ''): string {
    if ($msg !== '') {
        $_SESSION['flash'][$key] = $msg;
        return '';
    }
    $val = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $val;
}

/** Limpia strings de entrada */
function sanitize(mixed $v): string {
    return htmlspecialchars(trim((string)$v), ENT_QUOTES, 'UTF-8');
}

/** Verifica que el usuario esté logueado; si no, redirige al login */
function requireLogin(): void {
    if (empty($_SESSION['user'])) {
        $_SESSION['flash']['info'] = 'Debes iniciar sesión para acceder a esta sección.';
        redirect('auth/login');
    }
}

/** Verifica rol admin */
function requireAdmin(): void {
    if (empty($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
        redirect('auth/login');
    }
}

/** Verifica rol cliente */
function requireCliente(): void {
    if (empty($_SESSION['user']) || $_SESSION['user']['rol'] !== 'cliente') {
        redirect('auth/login');
    }
}

/** ¿Está logueado? */
function isLogged(): bool {
    return !empty($_SESSION['user']);
}

/** ¿Es admin? */
function isAdmin(): bool {
    return !empty($_SESSION['user']) && $_SESSION['user']['rol'] === 'admin';
}

/** Renderiza una vista */
function view(string $template, array $data = []): void {
    extract($data);
    $file = ROOT_PATH . '/views/' . $template . '.php';
    if (!file_exists($file)) {
        die("Vista no encontrada: {$template}");
    }
    require $file;
}

/** Formatea precio en Bs */
function precio(int $num): string {
    return 'Bs ' . number_format($num, 0, '.', '.');
}

/** Formatea fecha legible en español */
function fechaES(string $d): string {
    $dias   = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    $meses  = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
               'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    $ts     = strtotime($d);
    return $dias[date('w',$ts)] . ', ' . date('d',$ts) . ' de ' .
           $meses[(int)date('n',$ts)-1] . ' de ' . date('Y',$ts);
}

/** Diferencia de horas entre dos cadenas HH:MM */
function diffHoras(string $ini, string $fin): int {
    return (int)(( strtotime($fin) - strtotime($ini) ) / 3600);
}
