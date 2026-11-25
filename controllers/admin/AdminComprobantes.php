<?php
// ═══════════════════════════════════════════════════════════════
//  AdminComprobantes — Revisión de comprobantes de pago
//  (se usa dentro de AdminController)
// ═══════════════════════════════════════════════════════════════

trait AdminComprobantes {

    // GET /admin/comprobantes
    public function comprobantes(): void {
        requireAdmin();
        $cm    = new ComprobanteModel();
        $comps = $cm->listar();
        view('admin/comprobantes', compact('comps'));
    }

    // GET /admin/comprobante-revisar?id=X
    public function comprobanteRevisar(): void {
        requireAdmin();
        $id   = (int)($_GET['id'] ?? 0);
        $cm   = new ComprobanteModel();
        $comp = $cm->findById($id);
        if (!$comp) { flash('error','Comprobante no encontrado.'); redirect('admin/comprobantes'); return; }
        view('admin/comprobante_revisar', compact('comp'));
    }

    // POST /admin/comprobante-accion
    public function comprobanteAccion(): void {
        requireAdmin();
        $id     = (int)($_POST['id']    ?? 0);
        $accion = sanitize($_POST['accion'] ?? '');
        $notas  = sanitize($_POST['notas']  ?? '');

        $cm = new ComprobanteModel();
        $rm = new ReservaModel();

        if ($accion === 'aprobar') {
            $cm->cambiarEstado($id, 'aprobado', $notas);
            $comp = $cm->findById($id);
            if ($comp) {
                $rm->actualizarMontoPagado((int)$comp['reserva_id'], min((int)($comp['monto_pago'] ?? 0), (int)$comp['total']));
                $rm->cambiarEstado($comp['reserva_id'], 'confirmada');
            }
            flash('success','Comprobante aprobado y reserva confirmada.');
            if ($comp) { redirect('admin/reserva-detalle?id='.$comp['reserva_id']); return; }
        } elseif ($accion === 'rechazar') {
            $cm->cambiarEstado($id, 'rechazado', $notas);
            $comp = $cm->findById($id);
            if ($comp) $rm->cambiarEstado($comp['reserva_id'], 'pendiente');
            flash('success','Comprobante rechazado.');
            if ($comp) { redirect('admin/reserva-detalle?id='.$comp['reserva_id']); return; }
        }
        redirect('admin/reservas');
    }

    // GET /admin/descargar-comprobante?id=X
    public function descargarComprobante(): void {
        requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $cm = new ComprobanteModel();
        $c  = $cm->findById($id);

        if (!$c) { flash('error','Comprobante no encontrado.'); redirect('admin/comprobantes'); return; }

        $file = UPLOAD_PATH . '/comprobantes/' . $c['archivo'];
        if (!file_exists($file)) { flash('error','Archivo no disponible.'); redirect('admin/comprobantes'); return; }

        $ext  = pathinfo($c['archivo'], PATHINFO_EXTENSION);
        $mime = $ext === 'pdf' ? 'application/pdf' : 'image/' . $ext;
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="comprobante_reserva_' . (int)$c['reserva_id'] . '.' . $ext . '"');
        readfile($file);
        exit;
    }
}
