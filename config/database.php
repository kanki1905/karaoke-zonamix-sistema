<?php
// ═══════════════════════════════════════════════════════════════
//  KARAOKE ZONA MIX — Conexión PDO Singleton v6
// ═══════════════════════════════════════════════════════════════

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $host   = '127.0.0.1';
            $dbname = 'karaoke_db';
            $user   = 'root';
            $pass   = '';
            $dsn    = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('[Karaoke Zona Mix] Error de conexión PDO: ' . $e->getMessage());
                http_response_code(503);
                die('<div style="font-family:sans-serif;padding:2rem;color:CanvasText;background:Canvas;border:2px solid CanvasText;border-radius:8px">'
                    . '<strong>Servicio temporalmente no disponible.</strong><br>'
                    . 'No se pudo conectar con la base de datos. Verifica la configuración o inténtalo más tarde.</div>');
            }
        }
        return self::$instance;
    }
}
