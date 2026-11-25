<?php
// ═══════════════════════════════════════════════════════════════
//  ClienteGeneral — Inicio, menú de productos y horarios (AJAX)
//  (se usa dentro de ClienteController)
// ═══════════════════════════════════════════════════════════════

trait ClienteGeneral {

    // GET /client/inicio
    public function inicio(): void {
        requireLogin();
        $rm    = new ReservaModel();
        $sm    = new SalaModel();
        $uid   = $_SESSION['user']['id'];
        $reservas    = array_slice($rm->porUsuario($uid), 0, 5);
        $salas       = $sm->listarActivas();
        $todasSalas  = $sm->listar();
        $hoy         = date('Y-m-d');
        $ocupadosHoy = $rm->disponibilidadPorDia($hoy);
        view('client/inicio', compact('reservas','salas','todasSalas','hoy','ocupadosHoy'));
    }

    // GET /client/menu
    public function menu(): void {
        requireLogin();
        $pm       = new ProductoModel();
        $productos = $pm->listarActivos();
        // Agrupar por categoría
        $grupos = [];
        foreach ($productos as $p) {
            $grupos[$p['categoria']][] = $p;
        }
        view('client/menu', compact('productos','grupos'));
    }

    // GET /client/horario?sala=X&fecha=Y  (AJAX)
    public function horario(): void {
        requireLogin();
        $salaId  = (int)($_GET['sala']  ?? 0);
        $fecha   = sanitize($_GET['fecha'] ?? date('Y-m-d'));
        $sm      = new SalaModel();
        $sala    = $sm->findById($salaId);
        $rm      = new ReservaModel();
        $ocupadas = $rm->horasOcupadas($salaId, $fecha);
        header('Content-Type: application/json');
        echo json_encode([
            'ocupados'      => $ocupadas,
            'mantenimiento' => $sala && !(bool)$sala['activa'],
        ]);
        exit;
    }

    // GET /client/horario-semana?sala=ID&lunes=YYYY-MM-DD  (JSON)
    public function horarioSemana(): void {
        requireLogin();
        header('Content-Type: application/json');
        $salaId = (int)($_GET['sala'] ?? 0);
        $lunes  = sanitize($_GET['lunes'] ?? date('Y-m-d'));
        // Ajustar al lunes de la semana si no lo es
        $ts = strtotime($lunes);
        $dow = (int)date('N', $ts); // 1=lun … 7=dom
        if ($dow !== 1) $ts = strtotime('-' . ($dow - 1) . ' days', $ts);
        $fechaIni = date('Y-m-d', $ts);
        $fechaFin = date('Y-m-d', strtotime('+6 days', $ts));

        $rm   = new ReservaModel();
        $sm   = new SalaModel();
        $sala = $sm->findById($salaId);
        if (!$sala) { echo json_encode(['error' => 'Sala no encontrada']); exit; }

        $reservas = $rm->disponibilidadSemana($salaId, $fechaIni, $fechaFin);
        // Construir mapa fecha → [hora => true]
        $mapa = [];
        foreach ($reservas as $r) {
            $ini = (int)substr($r['hora_inicio'], 0, 2);
            $fin = (int)substr($r['hora_fin'],   0, 2);
            for ($h = $ini; $h < $fin; $h++) {
                $mapa[$r['fecha']][$h] = true;
            }
        }
        $dias = [];
        for ($i = 0; $i < 7; $i++) {
            $dias[] = date('Y-m-d', strtotime("+$i days", $ts));
        }
        echo json_encode([
            'sala'    => ['id' => $sala['id'], 'nombre' => $sala['nombre'], 'activa' => (bool)$sala['activa']],
            'lunes'   => $fechaIni,
            'dias'    => $dias,
            'mapa'    => $mapa,
            'hoySrv'  => date('Y-m-d'),
            'horaSrv' => (int)date('H'),
        ]);
        exit;
    }
}
