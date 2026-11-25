<?php
// ═══════════════════════════════════════════════════════════════
//  AdminReportes — Reportes y gráficos
//  (se usa dentro de AdminController)
// ═══════════════════════════════════════════════════════════════

trait AdminReportes {

    public function reportes(): void {
        requireAdmin();
        $rm = new ReservaModel();
        $hoy = new DateTimeImmutable('today');
        $mes = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT);
        $anio = filter_input(INPUT_GET, 'anio', FILTER_VALIDATE_INT);
        $mes = ($mes !== false && $mes !== null && $mes >= 1 && $mes <= 12) ? $mes : (int)$hoy->format('n');
        $anio = ($anio !== false && $anio !== null && $anio >= 2000 && $anio <= (int)$hoy->format('Y')) ? $anio : (int)$hoy->format('Y');
        if ($anio === (int)$hoy->format('Y') && $mes > (int)$hoy->format('n')) $mes = (int)$hoy->format('n');
        $desde = sprintf('%04d-%02d-01', $anio, $mes);
        $hasta = (new DateTimeImmutable($desde))->modify('last day of this month')->format('Y-m-d');
        $reporte = $rm->reporteMensual($desde, $hasta);
        extract($reporte, EXTR_OVERWRITE);
        $mesNombre = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'][$mes];
        $money = static fn($v) => 'Bs '.number_format((float)$v, 2, ',', '.');
        view('admin/reportes', compact('mes','anio','desde','hasta','mesNombre','money','resumen','porEstado','pagosMetodo','top10','usuariosFidelizacion','porSala','salaTop','usuariosPorSala','porDia','diasTop','porHorario','horasTop','porReserva'));
    }
}
