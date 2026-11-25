<?php
// ═══════════════════════════════════════════════════════════════
//  ClienteReservas — Crear, listar y cancelar reservas
//  (se usa dentro de ClienteController)
// ═══════════════════════════════════════════════════════════════

trait ClienteReservas {

    // GET /client/reservar
    public function reservar(): void {
        requireLogin();
        $sm    = new SalaModel();
        $salas = $sm->listar();   // incluye salas en mantenimiento para mostrarlas
        $salaId = (int)($_GET['sala'] ?? 0);
        $sala   = $salaId ? $sm->findById($salaId) : null;
        $fd = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);
        if (!empty($_GET['fecha']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$_GET['fecha'])) $fd['fecha'] = (string)$_GET['fecha'];
        if (!empty($_GET['hora']) && preg_match('/^(?:0[8-9]|1[0-9]|2[0-2]):00$/', (string)$_GET['hora'])) { $fd['hora_ini'] = (string)$_GET['hora']; $fd['hora_fin'] = sprintf('%02d:00', (int)substr($_GET['hora'],0,2)+1); }
        // form_data sala_id toma precedencia sobre query param
        if (!empty($fd['sala_id'])) $salaId = (int)$fd['sala_id'];
        view('client/reservar', compact('salas','sala','salaId','fd'));
    }

    // POST /client/reservar
    public function reservarPost(): void {
        requireLogin();

        $uid     = $_SESSION['user']['id'];
        $salaId  = (int)($_POST['sala_id']     ?? 0);
        $fecha   = sanitize($_POST['fecha']    ?? '');
        $hi      = sanitize($_POST['hora_ini'] ?? '');
        $hf      = sanitize($_POST['hora_fin'] ?? '');
        // Solo existen efectivo y transferencia (QR). El cliente elige método y 50 % / 100 %
        // después de crear la reserva, en la pantalla de pago (porcentaje_pago queda NULL hasta entonces).
        $metodo  = 'efectivo';

        // Closure para guardar campos y redirigir con error
        $errReserva = function(string $msg) use ($salaId, $fecha, $hi, $hf, $metodo): void {
            $_SESSION['form_data'] = [
                'sala_id'    => $salaId,
                'fecha'      => $fecha,
                'hora_ini'   => $hi,
                'hora_fin'   => $hf,
                'metodo_pago'=> $metodo,
            ];
            flash('error', $msg);
            redirect('client/reservar');
            exit;
        };

        if (!$salaId || !$fecha || !$hi || !$hf) {
            $errReserva('Completa todos los campos de la reserva.'); return;
        }

        // Fecha no pasada
        if ($fecha < date('Y-m-d')) {
            $errReserva('No puedes reservar en una fecha pasada.'); return;
        }
        $fechaMax = date('Y-m-d', strtotime('+1 month'));
        if ($fecha > $fechaMax) { $errReserva('Solo puedes reservar hasta un mes desde hoy.'); return; }
        if (!preg_match('/^(?:0[8-9]|1[0-9]|2[0-2]):00$/', $hi) ||
            !preg_match('/^(?:0[9]|1[0-9]|2[0-3]):00$/', $hf)) {
            $errReserva('Selecciona un horario válido entre las 08:00 y las 23:00.'); return;
        }

        // Horas pasadas del día
        if ($fecha === date('Y-m-d') && (int)explode(':', $hi)[0] <= (int)date('H')) {
            $errReserva('Esa hora ya pasó para hoy. Elige una hora futura.'); return;
        }

        $horas = diffHoras($hi, $hf);
        if ($horas < 1) {
            $errReserva('La hora de fin debe ser mayor a la hora de inicio.'); return;
        }

        $sm   = new SalaModel();
        $sala = $sm->findById($salaId);
        if (!$sala || !$sala['activa']) {
            $errReserva('La sala no está disponible.'); return;
        }

        // La comprobación y el INSERT se ejecutan juntos con bloqueo de la sala.
        // Así se evita que dos clientes reserven simultáneamente el mismo horario.
        $rm = new ReservaModel();
        $total = (int)$sala['precio_hora'] * $horas;
        $reservaId = $rm->crearSiDisponible($uid, $salaId, $fecha, $hi, $hf, $horas, $total, $metodo);
        if ($reservaId === false) {
            $errReserva('Ese horario ya está ocupado o la sala dejó de estar disponible. Elige otro horario.'); return;
        }

        // Elegir efectivo o transferencia en la pantalla de Comprobante de Pago.
        flash('success', 'Reserva creada. Ahora selecciona tu forma de pago.');
        redirect('client/comprobante?id=' . $reservaId);
    }

    // GET /client/mis-reservas
    public function misReservas(): void {
        requireLogin();
        $rm       = new ReservaModel();
        $reservas = $rm->porUsuarioConComprobantes($_SESSION['user']['id']);
        view('client/mis_reservas', compact('reservas'));
    }

    // GET /client/cancelar?id=X
    public function cancelar(): void {
        requireLogin();
        $id  = (int)($_POST['id'] ?? 0);
        $rm  = new ReservaModel();
        $res = $rm->findById($id);

        if (!$res || $res['usuario_id'] != $_SESSION['user']['id']) {
            flash('error', 'Reserva no encontrada.');
            redirect('client/mis-reservas'); return;
        }
        if (!in_array($res['estado'], ['pendiente','confirmada'])) {
            flash('error', 'No puedes cancelar esta reserva.');
            redirect('client/mis-reservas'); return;
        }

        $rm->cambiarEstado($id, 'cancelada');
        flash('success', 'Reserva cancelada correctamente.');
        redirect('client/mis-reservas');
    }
}
