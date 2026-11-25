<?php
// ═══════════════════════════════════════════════════════════════
//  PublicController — Páginas públicas
// ═══════════════════════════════════════════════════════════════

class PublicController {

    // GET /public/menu — catálogo de productos visible SIN iniciar sesión
    public function menu(): void {
        $productos = (new ProductoModel())->listarActivos();
        $grupos = [];
        foreach ($productos as $p) { $grupos[$p['categoria']][] = $p; }
        view('client/menu', compact('productos','grupos'));
    }

    // GET /public/home
    public function home(): void {
        $sm         = new SalaModel();
        $salas      = $sm->listarActivas();   // para la sección "Nuestras Salas"
        $todasSalas = $sm->listar();          // para la tabla de disponibilidad (incl. inactivas)
        $hoy        = date('Y-m-d');
        $rm         = new ReservaModel();
        $ocupadosHoy = $rm->disponibilidadPorDia($hoy);
        view('public/home', compact('salas','todasSalas','hoy','ocupadosHoy'));
    }

    // GET /public/disponibilidad?fecha=YYYY-MM-DD  (endpoint AJAX)
    public function disponibilidad(): void {
        $fecha = sanitize($_GET['fecha'] ?? date('Y-m-d'));
        // Validar formato
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = date('Y-m-d');
        }
        $rm    = new ReservaModel();
        $sm    = new SalaModel();
        $ocupados = $rm->disponibilidadPorDia($fecha);
        $salas    = $sm->listar();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ocupados' => $ocupados, 'salas' => $salas]);
        exit;
    }

    // GET /public/salas
    public function salas(): void {
        $sm    = new SalaModel();
        $salas = $sm->listarActivas();
        view('public/salas', compact('salas'));
    }

    // GET /public/sala?id=X
    public function sala(): void {
        $id   = (int)($_GET['id'] ?? 0);
        $sm   = new SalaModel();
        $sala = $sm->findById($id);
        if (!$sala) { redirect('public/salas'); return; }
        view('public/sala', compact('sala'));
    }
}
