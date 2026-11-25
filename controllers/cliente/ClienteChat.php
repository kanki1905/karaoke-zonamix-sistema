<?php
// ═══════════════════════════════════════════════════════════════
//  ClienteChat — Chat del cliente con el administrador
//  (se usa dentro de ClienteController)
// ═══════════════════════════════════════════════════════════════

trait ClienteChat {

    // GET /client/chat
    public function chat(): void {
        requireLogin();
        $uid  = (int)$_SESSION['user']['id'];
        $mm   = new MensajeModel();
        $msgs = $mm->porUsuario($uid);
        $mm->marcarLeidos($uid, 'admin'); // marca leídos los del admin
        view('client/chat', ['mensajes' => $msgs, 'lastId' => $mm->ultimoId($uid)]);
    }

    // POST /client/chat-post: responde JSON para AJAX y redirección para formulario normal.
    public function chatPost(): void {
        requireCliente();
        $uid = (int)$_SESSION['user']['id'];
        $contenido = trim(strip_tags((string)($_POST['contenido'] ?? '')));
        $ajax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
             || str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
        if ($contenido === '') {
            if ($ajax) { header('Content-Type: application/json; charset=utf-8'); http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Escribe un mensaje antes de enviarlo.']); exit; }
            flash('error', 'Escribe un mensaje antes de enviarlo.'); redirect('client/chat');
        }
        if (mb_strlen($contenido, 'UTF-8') > 1000) {
            if ($ajax) { header('Content-Type: application/json; charset=utf-8'); http_response_code(422); echo json_encode(['ok'=>false,'error'=>'El mensaje no puede superar 1000 caracteres.']); exit; }
            flash('error', 'El mensaje no puede superar 1000 caracteres.'); redirect('client/chat');
        }
        try {
            $mm = new MensajeModel();
            $id = $mm->crear($uid, $contenido, 'cliente');
            $mm->marcarLeidos($uid, 'admin');   // el cliente está en el chat: lee lo del admin, NO sus propios mensajes (esos los lee el admin)
            if ($ajax) {
                $mensajes = $mm->desdeId($uid, $id - 1);
                header('Content-Type: application/json; charset=utf-8');
                header('Cache-Control: no-store');
                echo json_encode(['ok'=>true,'mensajes'=>$mensajes], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                exit;
            }
        } catch (Throwable $e) {
            error_log('[Karaoke chat cliente] '.$e->getMessage());
            if ($ajax) { header('Content-Type: application/json; charset=utf-8'); http_response_code(500); echo json_encode(['ok'=>false,'error'=>'No se pudo guardar el mensaje. Comprueba la conexión con la base de datos.']); exit; }
            flash('error', 'No se pudo guardar el mensaje.');
        }
        redirect('client/chat');
    }

    // GET /client/chat-poll?last=ID (JSON)
    public function chatPoll(): void {
        requireCliente();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        $uid = (int)$_SESSION['user']['id'];
        $lastId = max(0, (int)($_GET['last'] ?? 0));
        $leer = !empty($_GET['read']);   // el navegador lo envía solo si el chat está abierto y visible
        session_write_close();           // el poll no escribe sesión: libera el bloqueo para no serializar peticiones
        try {
            $mm = new MensajeModel();
            $nuevos = $mm->desdeId($uid, $lastId);
            if ($leer) $mm->marcarLeidos($uid, 'admin');
            echo json_encode(['ok'=>true,'mensajes'=>$nuevos], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (Throwable $e) {
            error_log('[Karaoke chat poll cliente] '.$e->getMessage());
            http_response_code(500); echo json_encode(['ok'=>false,'error'=>'No se pudieron consultar los mensajes.']);
        }
        exit;
    }

    // GET /client/chat-unread (JSON) — resumen para el punto rojo del nav
    public function chatUnread(): void {
        requireCliente();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        $uid = (int)$_SESSION['user']['id'];
        session_write_close();
        try {
            $r = (new MensajeModel())->resumenNoLeidosCliente($uid);
            echo json_encode(['ok'=>true,'sin_leer'=>$r['sin_leer'],'ultimo_id'=>$r['ultimo_id']]);
        } catch (Throwable $e) {
            error_log('[Karaoke chat unread cliente] '.$e->getMessage());
            http_response_code(500); echo json_encode(['ok'=>false]);
        }
        exit;
    }
}
