<?php
// ═══════════════════════════════════════════════════════════════
//  ClientePagos — Método de pago y comprobantes
//  (se usa dentro de ClienteController)
// ═══════════════════════════════════════════════════════════════

trait ClientePagos {

    // GET /client/comprobante?id=X
    public function comprobante(): void {
        requireLogin();
        $id  = (int)($_GET['id'] ?? 0);
        $rm  = new ReservaModel();
        $res = $rm->findById($id);

        if (!$res || $res['usuario_id'] != $_SESSION['user']['id']) {
            flash('error', 'Reserva no encontrada.');
            redirect('client/mis-reservas'); return;
        }
        $cm   = new ComprobanteModel();
        $comp = $cm->porReserva($id);
        view('client/comprobante', compact('res','comp'));
    }

    // POST /client/metodo-pago: elegir efectivo o transferencia después de crear la reserva.
    public function metodoPagoPost(): void {
        requireLogin();
        $reservaId = (int)($_POST['reserva_id'] ?? 0);
        $metodo = sanitize($_POST['metodo_pago'] ?? '');
        $rm = new ReservaModel();
        $res = $rm->findById($reservaId);

        if (!$res || (int)$res['usuario_id'] !== (int)$_SESSION['user']['id']) {
            flash('error', 'Reserva no válida.');
            redirect('client/mis-reservas'); return;
        }
        if ($res['estado'] !== 'pendiente') {
            flash('error', 'La forma de pago ya no puede modificarse porque la reserva no está pendiente.');
            redirect('client/mis-reservas'); return;
        }
        if (!in_array($metodo, ['efectivo', 'transferencia'], true)) {
            flash('error', 'Selecciona efectivo o transferencia.');
            redirect('client/comprobante?id=' . $reservaId); return;
        }
        // Tanto efectivo como transferencia permiten pagar el 50 % o el 100 %.
        $porcentaje = (int)($_POST['porcentaje_pago'] ?? 0);
        if (!in_array($porcentaje, [50, 100], true)) {
            flash('error', 'Elige si pagarás el 50 % o el 100 % de la reserva.');
            redirect('client/comprobante?id=' . $reservaId); return;
        }
        $rm->actualizarMetodoPago($reservaId, $metodo);
        $rm->actualizarPorcentajePago($reservaId, $porcentaje);
        $montoElegido = $porcentaje === 100 ? (int)$res['total'] : (int)ceil((int)$res['total'] / 2);

        if ($metodo === 'efectivo') {
            flash('success', 'Elegiste pagar el ' . $porcentaje . ' % en efectivo (Bs ' . $montoElegido . '). La reserva queda pendiente; el administrador registrará el dinero cuando lo reciba.');
            redirect('client/mis-reservas'); return;
        }
        flash('success', 'Pagarás el ' . $porcentaje . ' % por transferencia (Bs ' . $montoElegido . '). Escanea el QR y sube tu comprobante.');
        redirect('client/comprobante?id=' . $reservaId); return;
    }

    // POST /client/comprobante
    public function comprobantePost(): void {
        requireLogin();
        $reservaId = (int)($_POST['reserva_id'] ?? 0);
        $rm  = new ReservaModel();
        $res = $rm->findById($reservaId);

        if (!$res || (int)$res['usuario_id'] !== (int)$_SESSION['user']['id']) {
            flash('error', 'Reserva no válida.');
            redirect('client/mis-reservas'); return;
        }
        if (($res['metodo_pago'] ?? '') !== 'transferencia' || in_array($res['estado'], ['cancelada','finalizada'], true)) {
            flash('error', 'Esta reserva no admite nuevos comprobantes. Verifica la forma de pago y el estado.');
            redirect('client/comprobante?id=' . $reservaId); return;
        }

        if (empty($_FILES['comprobante']['name'])) {
            flash('error', 'Selecciona un archivo de comprobante.');
            redirect('client/comprobante?id=' . $reservaId); return;
        }

        // El monto lo define el plan elegido (50 % o 100 %), no el navegador.
        $montoPago = ((int)($res['porcentaje_pago'] ?? 0) === 100) ? (int)$res['total'] : (int)ceil(((int)$res['total']) / 2);
        if ($montoPago < 1 || $res['porcentaje_pago'] === null) {
            flash('error', 'Primero elige si pagarás el 50 % o el 100 %.');
            redirect('client/comprobante?id=' . $reservaId); return;
        }

        $file   = $_FILES['comprobante'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '') || (int)$file['size'] > 5 * 1024 * 1024) {
            flash('error', 'El archivo no se pudo subir o supera el límite de 5 MB.');
            redirect('client/comprobante?id=' . $reservaId); return;
        }
        $ext    = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','pdf'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $validMime = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','pdf'=>'application/pdf'];
        if (!isset($validMime[$ext]) || $mime !== $validMime[$ext]) {
            flash('error', 'El comprobante debe ser un JPG, PNG o PDF válido.');
            redirect('client/comprobante?id=' . $reservaId); return;
        }

        $dir = UPLOAD_PATH . '/comprobantes';
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $nombre = 'comp_' . $reservaId . '_' . time() . '.' . $ext;
        $dest   = $dir . '/' . $nombre;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            flash('error', 'Error al subir el archivo. Intenta de nuevo.');
            redirect('client/comprobante?id=' . $reservaId); return;
        }

        $cm = new ComprobanteModel();
        $cm->crear($reservaId, $nombre, $montoPago);
        $rm->cambiarEstado($reservaId, 'pendiente');

        flash('success', '✅ Comprobante enviado correctamente. Te avisaremos cuando sea revisado.');
        redirect('client/comprobante?id=' . $reservaId);
    }

    // GET /client/descargar-comprobante?id=X
    public function descargarComprobante(): void {
        requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $cm = new ComprobanteModel();
        $c  = $cm->findById($id);

        if (!$c || (int)$c['usuario_id'] !== (int)$_SESSION['user']['id']) { flash('error', 'Comprobante no encontrado.'); redirect('client/mis-reservas'); return; }

        $file = UPLOAD_PATH . '/comprobantes/' . basename((string)$c['archivo']);
        if (!file_exists($file)) { flash('error', 'Archivo no disponible.'); redirect('client/mis-reservas'); return; }

        $ext  = pathinfo($c['archivo'], PATHINFO_EXTENSION);
        $mime = $ext === 'pdf' ? 'application/pdf' : 'image/' . $ext;
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="comprobante_reserva_' . $id . '.' . $ext . '"');
        readfile($file);
        exit;
    }
}
