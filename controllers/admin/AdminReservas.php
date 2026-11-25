<?php
// ═══════════════════════════════════════════════════════════════
//  AdminReservas — Reservas, estados y registro de pagos
//  (se usa dentro de AdminController)
// ═══════════════════════════════════════════════════════════════

trait AdminReservas {

    // GET /admin/reservas
    public function reservas(): void {
        requireAdmin();
        $rm = new ReservaModel();
        $sm = new SalaModel();

        // Filtros GET
        $fMetodo  = sanitize($_GET['metodo']   ?? '');
        $fFecha   = sanitize($_GET['fecha']    ?? date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fFecha) || $fFecha > date('Y-m-d')) $fFecha = date('Y-m-d');
        $fSalaId  = (int)($_GET['sala_id']     ?? 0);
        $fUsuario = sanitize($_GET['usuario']  ?? '');

        $filtrosActivos = $fMetodo || $fFecha || $fSalaId || $fUsuario;
        $reservas = $filtrosActivos
            ? $rm->listarFiltradas($fMetodo, $fFecha, $fSalaId, $fUsuario)
            : $rm->listarTodas();

        $salas = $sm->listar();
        view('admin/reservas', compact('reservas','salas','fMetodo','fFecha','fSalaId','fUsuario'));
    }

    // GET /admin/reserva-detalle?id=X
    public function reservaDetalle(): void {
        requireAdmin();
        $id  = (int)($_GET['id'] ?? 0);
        $rm  = new ReservaModel();
        $res = $rm->findById($id);
        if (!$res) { flash('error','Reserva no encontrada.'); redirect('admin/reservas'); return; }
        $cm   = new ComprobanteModel();
        $comp = $cm->porReserva($id);
        view('admin/reserva_detalle', compact('res','comp'));
    }

    // GET /admin/reserva-estado?id=X&estado=Y
    public function reservaEstado(): void {
        requireAdmin();
        $id     = (int)($_POST['id']     ?? 0);
        $estado = sanitize($_POST['estado'] ?? '');
        $validos = ['pendiente','confirmada','cancelada','finalizada'];
        if (!in_array($estado, $validos)) {
            flash('error','Estado no válido.'); redirect('admin/reservas'); return;
        }
        if ($id < 1) {
            flash('error', 'Identificador de reserva no válido.');
            redirect('admin/reservas'); return;
        }
        $rm = new ReservaModel();
        $res = $rm->findById($id);
        if (!$res) {
            flash('error', 'La reserva ya no existe o fue eliminada.');
            redirect('admin/reservas'); return;
        }
        if (in_array($res['estado'], ['cancelada','finalizada'], true) && $estado !== $res['estado']) {
            flash('error', 'No se puede reactivar o modificar una reserva cancelada/finalizada desde esta acción.');
            redirect('admin/reservas'); return;
        }
        $rm->cambiarEstado($id, $estado);
        flash('success', $estado === 'cancelada'
            ? 'Reserva rechazada/cancelada. El horario quedó liberado para otros clientes.'
            : 'Estado actualizado.');
        redirect('admin/reservas');
    }

    // POST /admin/registrar-pago: monto acumulado que realmente recibió el local.
    public function registrarPago(): void {
        requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $montoRaw = trim((string)($_POST['monto_pagado'] ?? ''));
        if ($id < 1 || $montoRaw === '' || !ctype_digit($montoRaw)) {
            flash('error', 'Introduce un monto pagado válido.');
            redirect('admin/reservas'); return;
        }
        $rm = new ReservaModel();
        $res = $rm->findById($id);
        $monto = (int)$montoRaw;
        if (!$res || $monto < 0 || $monto > (int)$res['total'] || $res['estado'] === 'cancelada') {
            flash('error', 'No se pudo registrar el pago: verifica la reserva y que el monto no supere el total.');
            redirect('admin/reserva-detalle?id='.$id); return;
        }
        $rm->actualizarMontoPagado($id, $monto);
        flash('success', 'Pago acumulado actualizado. Los reportes usarán este monto real registrado.');
        redirect('admin/reserva-detalle?id='.$id); return;
    }
}
