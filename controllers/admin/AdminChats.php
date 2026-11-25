<?php
// ═══════════════════════════════════════════════════════════════
//  AdminChats — Chat del administrador con los clientes
//  (se usa dentro de AdminController)
// ═══════════════════════════════════════════════════════════════

trait AdminChats {

    // GET /admin/chats
    public function chats(): void {
        requireAdmin();
        $mm    = new MensajeModel();
        $convs = $mm->conversaciones();
        view('admin/chats', ['conversaciones' => $convs]);
    }

    // GET /admin/chat-ver?usuario_id=ID
    public function chatVer(): void {
        requireAdmin();
        $uid = (int)($_GET['usuario_id'] ?? 0);
        if (!$uid) { redirect('admin/chats'); return; }
        $um   = new UsuarioModel();
        $user = $um->findById($uid);
        if (!$user) { flash('error', 'Usuario no encontrado.'); redirect('admin/chats'); return; }
        $mm   = new MensajeModel();
        $msgs = $mm->porUsuario($uid);
        $mm->marcarLeidos($uid, 'cliente'); // marca leídos los del cliente
        view('admin/chat_ver', [
            'usuario'  => $user,
            'mensajes' => $msgs,
            'lastId'   => $mm->ultimoId($uid),
        ]);
    }

    // POST /admin/chat-responder: respuesta JSON para AJAX.
    public function chatResponder(): void {
        requireAdmin();
        $uid = (int)($_POST['usuario_id'] ?? 0);
        $contenido = trim(strip_tags((string)($_POST['contenido'] ?? '')));
        $ajax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
             || str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
        if (!$uid || $contenido === '') {
            if ($ajax) { header('Content-Type: application/json; charset=utf-8'); http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Selecciona una conversación y escribe un mensaje.']); exit; }
            flash('error', 'Escribe un mensaje.'); redirect('admin/chat-ver?usuario_id='.$uid);
        }
        if (mb_strlen($contenido, 'UTF-8') > 1000) {
            if ($ajax) { header('Content-Type: application/json; charset=utf-8'); http_response_code(422); echo json_encode(['ok'=>false,'error'=>'El mensaje no puede superar 1000 caracteres.']); exit; }
            flash('error', 'El mensaje no puede superar 1000 caracteres.'); redirect('admin/chat-ver?usuario_id='.$uid);
        }
        try {
            $mm = new MensajeModel();
            $id = $mm->crear($uid, $contenido, 'admin');
            $mm->marcarLeidos($uid, 'cliente'); // el admin está en el chat: lee lo del cliente, NO sus propios mensajes (esos los lee el cliente)
            if ($ajax) {
                $mensajes = $mm->desdeId($uid, $id - 1);
                header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');
                echo json_encode(['ok'=>true,'mensajes'=>$mensajes], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE); exit;
            }
        } catch (Throwable $e) {
            error_log('[Karaoke chat admin] '.$e->getMessage());
            if ($ajax) { header('Content-Type: application/json; charset=utf-8'); http_response_code(500); echo json_encode(['ok'=>false,'error'=>'No se pudo guardar la respuesta. Comprueba la conexión con la base de datos.']); exit; }
            flash('error', 'No se pudo guardar la respuesta.');
        }
        redirect('admin/chat-ver?usuario_id='.$uid);
    }

    // GET /admin/chat-poll?usuario_id=ID&last=N (JSON)
    public function chatPoll(): void {
        requireAdmin();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        $uid = (int)($_GET['usuario_id'] ?? 0);
        $lastId = max(0, (int)($_GET['last'] ?? 0));
        $leer = !empty($_GET['read']);   // solo si el chat está abierto y visible
        session_write_close();           // el poll no escribe sesión
        if (!$uid) { echo json_encode(['ok'=>true,'mensajes'=>[]]); exit; }
        try {
            $mm = new MensajeModel();
            $nuevos = $mm->desdeId($uid, $lastId);
            if ($leer) $mm->marcarLeidos($uid, 'cliente');
            echo json_encode(['ok'=>true,'mensajes'=>$nuevos], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (Throwable $e) {
            error_log('[Karaoke chat poll admin] '.$e->getMessage());
            http_response_code(500); echo json_encode(['ok'=>false,'error'=>'No se pudieron consultar los mensajes.']);
        }
        exit;
    }

    // GET /admin/chat-unread (JSON) — conversaciones con mensajes sin leer
    public function chatUnread(): void {
        requireAdmin();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        session_write_close();
        try {
            $convs = (new MensajeModel())->resumenNoLeidosAdmin();
            foreach ($convs as &$c) { $c['usuario_id'] = (int)$c['usuario_id']; $c['sin_leer'] = (int)$c['sin_leer']; $c['ultimo_id'] = (int)$c['ultimo_id']; }
            unset($c);
            echo json_encode(['ok'=>true,'convs'=>$convs], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (Throwable $e) {
            error_log('[Karaoke chat unread admin] '.$e->getMessage());
            http_response_code(500); echo json_encode(['ok'=>false]);
        }
        exit;
    }
}
