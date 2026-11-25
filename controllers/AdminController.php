<?php
// ═══════════════════════════════════════════════════════════════
//  AdminController — las acciones están separadas por tema en controllers/admin/
// ═══════════════════════════════════════════════════════════════

require_once __DIR__ . '/admin/AdminDashboard.php';
require_once __DIR__ . '/admin/AdminSalas.php';
require_once __DIR__ . '/admin/AdminReservas.php';
require_once __DIR__ . '/admin/AdminComprobantes.php';
require_once __DIR__ . '/admin/AdminUsuarios.php';
require_once __DIR__ . '/admin/AdminReportes.php';
require_once __DIR__ . '/admin/AdminProductos.php';
require_once __DIR__ . '/admin/AdminChats.php';
require_once __DIR__ . '/admin/AdminCuenta.php';

class AdminController {
    use AdminDashboard, AdminSalas, AdminReservas, AdminComprobantes, AdminUsuarios, AdminReportes, AdminProductos, AdminChats, AdminCuenta;
}
