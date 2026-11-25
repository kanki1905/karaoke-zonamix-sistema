<?php
// ═══════════════════════════════════════════════════════════════
//  ClienteController — las acciones están separadas por tema en controllers/cliente/
// ═══════════════════════════════════════════════════════════════

require_once __DIR__ . '/cliente/ClienteGeneral.php';
require_once __DIR__ . '/cliente/ClienteReservas.php';
require_once __DIR__ . '/cliente/ClientePagos.php';
require_once __DIR__ . '/cliente/ClientePerfil.php';
require_once __DIR__ . '/cliente/ClienteChat.php';

class ClienteController {
    use ClienteGeneral, ClienteReservas, ClientePagos, ClientePerfil, ClienteChat;
}
