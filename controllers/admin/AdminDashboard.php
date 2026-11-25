<?php
// ═══════════════════════════════════════════════════════════════
//  AdminDashboard — Panel de inicio
//  (se usa dentro de AdminController)
// ═══════════════════════════════════════════════════════════════

trait AdminDashboard {

    // GET /admin/dashboard
    public function dashboard(): void {
        requireAdmin();
        $rm  = new ReservaModel();
        $um  = new UsuarioModel();
        $sm  = new SalaModel();
        $cm  = new ComprobanteModel();

        $stats = [
            'reservas'    => $rm->total(),
            'pendientes'  => $rm->totalPendientes(),
            'clientes'    => $um->totalClientes(),
            'salas'       => $sm->total(),
            'ingresosMes' => $rm->ingresosMes(),
            'compPend'    => $cm->totalPendientes(),
        ];
        $recientes  = $rm->recientes(8);
        $porSala    = $rm->porSala();
        $porEstado  = $rm->porEstado();
        $porMes     = $rm->reservasPorMes();

        view('admin/dashboard', compact('stats','recientes','porSala','porEstado','porMes'));
    }
}
