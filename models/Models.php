<?php
// ═══════════════════════════════════════════════════════════════
//  KARAOKE ZONA MIX — Modelos v6
// ═══════════════════════════════════════════════════════════════

// ── UsuarioModel ───────────────────────────────────────────────
class UsuarioModel {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function findByEmail(string $email): array|false {
        $s = $this->db->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
        $s->execute([$email]);
        return $s->fetch();
    }

    public function existeEmail(string $email, int $excludeId = 0): bool {
        $s = $this->db->prepare('SELECT id FROM usuarios WHERE email = ? AND id != ?');
        $s->execute([$email, $excludeId]);
        return (bool)$s->fetch();
    }

    public function existeTelefono(string $telefono, int $excludeId = 0): bool {
        $s = $this->db->prepare('SELECT id FROM usuarios WHERE telefono = ? AND id != ?');
        $s->execute([$telefono, $excludeId]);
        return (bool)$s->fetch();
    }

    public function crear(string $nombre, string $apellidos, string $email, string $telefono, string $hash): int {
        $s = $this->db->prepare(
            'INSERT INTO usuarios (nombre, apellidos, email, telefono, password, rol)
             VALUES (?,?,?,?,?,\'cliente\')'
        );
        $s->execute([$nombre, $apellidos, $email, $telefono, $hash]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): array|false {
        $s = $this->db->prepare('SELECT * FROM usuarios WHERE id = ? LIMIT 1');
        $s->execute([$id]);
        return $s->fetch();
    }

    public function listar(): array {
        return $this->db->query('SELECT * FROM usuarios ORDER BY id ASC')->fetchAll();
    }

    public function total(): int {
        return (int)$this->db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    }

    public function totalClientes(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM usuarios WHERE rol='cliente'")->fetchColumn();
    }

    public function actualizar(int $id, string $nombre, string $apellidos, string $email, string $telefono): void {
        $s = $this->db->prepare(
            'UPDATE usuarios SET nombre=?, apellidos=?, email=?, telefono=? WHERE id=?'
        );
        $s->execute([$nombre, $apellidos, $email, $telefono, $id]);
    }

    public function actualizarFoto(int $id, string $foto): void {
        $s = $this->db->prepare('UPDATE usuarios SET foto=? WHERE id=?');
        $s->execute([$foto, $id]);
    }

    public function cambiarPassword(int $id, string $hash): void {
        $s = $this->db->prepare('UPDATE usuarios SET password=? WHERE id=?');
        $s->execute([$hash, $id]);
    }

    /** Crea la columna usuarios.activo si la base de datos todavía no la tiene (migración automática). */
    private function asegurarColumnaActivo(): void {
        $existe = $this->db->query("SHOW COLUMNS FROM usuarios LIKE 'activo'")->fetch();
        if (!$existe) {
            $this->db->exec('ALTER TABLE usuarios ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER foto');
        }
    }

    /** Dar de baja (0) o de alta (1) una cuenta. No borra nada de la base de datos. */
    public function cambiarEstado(int $id, int $activo): void {
        $this->asegurarColumnaActivo();
        $s = $this->db->prepare('UPDATE usuarios SET activo=? WHERE id=?');
        $s->execute([$activo ? 1 : 0, $id]);
    }

    /** ¿La cuenta sigue activa? (false si no existe o fue dada de baja) */
    public function estaActivo(int $id): bool {
        try {
            $s = $this->db->prepare('SELECT activo FROM usuarios WHERE id=? LIMIT 1');
            $s->execute([$id]);
        } catch (PDOException $e) {
            // Aún no existe la columna: nadie ha sido dado de baja todavía.
            return true;
        }
        $v = $s->fetchColumn();
        return $v !== false && (int)$v === 1;
    }
}

// ── SalaModel ──────────────────────────────────────────────────
class SalaModel {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function listar(): array {
        return $this->db->query('SELECT * FROM salas ORDER BY id')->fetchAll();
    }

    public function listarActivas(): array {
        return $this->db->query("SELECT * FROM salas WHERE activa=1 ORDER BY nombre")->fetchAll();
    }

    public function findById(int $id): array|false {
        $s = $this->db->prepare('SELECT * FROM salas WHERE id=? LIMIT 1');
        $s->execute([$id]);
        return $s->fetch();
    }

    /** ¿Ya hay otra sala con ese nombre? (sin distinguir mayúsculas ni espacios sobrantes) */
    public function existeNombre(string $nombre, int $excludeId = 0): bool {
        $s = $this->db->prepare('SELECT id FROM salas WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?)) AND id != ? LIMIT 1');
        $s->execute([$nombre, $excludeId]);
        return (bool)$s->fetch();
    }

    public function existeNumeroSala(int $numero, int $excludeId = 0): bool {
        $s = $this->db->prepare('SELECT id FROM salas WHERE numero = ? AND id != ?');
        $s->execute([$numero, $excludeId]);
        return (bool)$s->fetch();
    }

    public function crear(string $nombre, string $desc, int $cap, int $precio, string $img = '', ?int $numero = null): int {
        $s = $this->db->prepare(
            'INSERT INTO salas (nombre,descripcion,capacidad,precio_hora,imagen,numero)
             VALUES (?,?,?,?,?,?)'
        );
        $s->execute([$nombre,$desc,$cap,$precio,$img,$numero]);
        return (int)$this->db->lastInsertId();
    }

    public function actualizar(int $id, string $nombre, string $desc, int $cap, int $precio, ?string $img, ?int $numero = null): void {
        if ($img !== null) {
            $s = $this->db->prepare(
                'UPDATE salas SET nombre=?,descripcion=?,capacidad=?,precio_hora=?,imagen=?,numero=? WHERE id=?'
            );
            $s->execute([$nombre,$desc,$cap,$precio,$img,$numero,$id]);
        } else {
            $s = $this->db->prepare(
                'UPDATE salas SET nombre=?,descripcion=?,capacidad=?,precio_hora=?,numero=? WHERE id=?'
            );
            $s->execute([$nombre,$desc,$cap,$precio,$numero,$id]);
        }
    }

    public function toggleActiva(int $id): void {
        $s = $this->db->prepare('UPDATE salas SET activa = NOT activa WHERE id=?');
        $s->execute([$id]);
    }

    public function reservasFuturasCount(int $id): int {
        $s = $this->db->prepare("SELECT COUNT(*) FROM reservas WHERE sala_id=? AND estado <> 'cancelada' AND (fecha > CURDATE() OR (fecha = CURDATE() AND hora_fin > CURTIME()))");
        $s->execute([$id]);
        return (int)$s->fetchColumn();
    }

    public function eliminar(int $id): void {
        $s = $this->db->prepare('DELETE FROM salas WHERE id=?');
        $s->execute([$id]);
    }

    public function total(): int {
        return (int)$this->db->query('SELECT COUNT(*) FROM salas WHERE activa=1')->fetchColumn();
    }
}

// ── ReservaModel ───────────────────────────────────────────────
class ReservaModel {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function crear(int $usuId, int $salaId, string $fecha, string $hi, string $hf,
                          int $horas, int $total, string $metodo): int {
        $s = $this->db->prepare(
            'INSERT INTO reservas (usuario_id,sala_id,fecha,hora_inicio,hora_fin,horas,total,metodo_pago)
             VALUES (?,?,?,?,?,?,?,?)'
        );
        $s->execute([$usuId,$salaId,$fecha,$hi,$hf,$horas,$total,$metodo]);
        return (int)$this->db->lastInsertId();
    }

    /** Inserta una reserva dentro de una transacción y bloquea la fila de la sala para evitar doble reserva concurrente. */
    public function crearSiDisponible(int $usuId, int $salaId, string $fecha, string $hi, string $hf,
                                      int $horas, int $total, string $metodo): int|false {
        $this->db->beginTransaction();
        try {
            $lock = $this->db->prepare('SELECT activa FROM salas WHERE id=? FOR UPDATE');
            $lock->execute([$salaId]);
            $sala = $lock->fetch();
            if (!$sala || !(bool)$sala['activa']) {
                $this->db->rollBack();
                return false;
            }
            $check = $this->db->prepare("SELECT 1 FROM reservas WHERE sala_id=? AND fecha=? AND estado <> 'cancelada' AND hora_inicio < ? AND hora_fin > ? LIMIT 1");
            $check->execute([$salaId, $fecha, $hf, $hi]);
            if ($check->fetchColumn()) {
                $this->db->rollBack();
                return false;
            }
            $id = $this->crear($usuId, $salaId, $fecha, $hi, $hf, $horas, $total, $metodo);
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function horasOcupadas(int $salaId, string $fecha): array {
        $s = $this->db->prepare(
            "SELECT hora_inicio, hora_fin FROM reservas
             WHERE sala_id=? AND fecha=? AND estado NOT IN ('cancelada')"
        );
        $s->execute([$salaId,$fecha]);
        return $s->fetchAll();
    }

    public function findById(int $id): array|false {
        $s = $this->db->prepare(
            'SELECT r.*, s.nombre sala_nombre, s.precio_hora,
                    u.nombre usuario_nombre, u.email usuario_email, u.telefono usuario_telefono
             FROM reservas r
             JOIN salas    s ON s.id = r.sala_id
             JOIN usuarios u ON u.id = r.usuario_id
             WHERE r.id=? LIMIT 1'
        );
        $s->execute([$id]);
        return $s->fetch();
    }

    public function porUsuario(int $uid): array {
        $s = $this->db->prepare(
            'SELECT r.*, s.nombre sala_nombre, s.imagen sala_imagen
             FROM reservas r JOIN salas s ON s.id=r.sala_id
             WHERE r.usuario_id=? ORDER BY r.created_at DESC, r.id DESC'
        );
        $s->execute([$uid]);
        return $s->fetchAll();
    }

    public function listarTodas(): array {
        return $this->db->query(
            'SELECT r.*, s.nombre sala_nombre, u.nombre usuario_nombre, u.email usuario_email
             FROM reservas r
             JOIN salas    s ON s.id=r.sala_id
             JOIN usuarios u ON u.id=r.usuario_id
             ORDER BY r.created_at DESC'
        )->fetchAll();
    }

    public function cambiarEstado(int $id, string $estado): bool {
        // Solo actualiza una reserva existente; los estados cancelados dejan de bloquear el horario.
        $s = $this->db->prepare('UPDATE reservas SET estado=? WHERE id=?');
        $s->execute([$estado, $id]);
        return $s->rowCount() > 0;
    }

    /**
     * Confirma automáticamente toda reserva 'pendiente' cuya hora de inicio ya llegó.
     * La hora se calcula en PHP (zona America/La_Paz) para no depender de la zona de MySQL.
     * Devuelve cuántas reservas se confirmaron.
     */
    public function autoConfirmarPendientes(): int {
        $s = $this->db->prepare(
            "UPDATE reservas SET estado='confirmada'
             WHERE estado='pendiente' AND TIMESTAMP(fecha, hora_inicio) <= ?"
        );
        $s->execute([date('Y-m-d H:i:s')]);
        return $s->rowCount();
    }

    public function actualizarMetodoPago(int $id, string $metodo): void {
        $s = $this->db->prepare('UPDATE reservas SET metodo_pago=? WHERE id=?');
        $s->execute([$metodo, $id]);
    }

    /** Guarda la opción de pago elegida por el cliente; no representa dinero recibido. */
    public function actualizarPorcentajePago(int $id, int $porcentaje): bool {
        if (!in_array($porcentaje, [50, 100], true)) return false;
        $s = $this->db->prepare('UPDATE reservas SET porcentaje_pago=? WHERE id=?');
        return $s->execute([$porcentaje, $id]);
    }

    public function total(): int {
        return (int)$this->db->query('SELECT COUNT(*) FROM reservas')->fetchColumn();
    }

    public function totalPendientes(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM reservas WHERE estado='pendiente'")->fetchColumn();
    }

    public function ingresosMes(): int {
        return (int)$this->db->query(
            "SELECT COALESCE(SUM(total),0) FROM reservas
             WHERE estado='confirmada' AND MONTH(fecha)=MONTH(CURDATE()) AND YEAR(fecha)=YEAR(CURDATE())"
        )->fetchColumn();
    }

    public function reservasPorMes(): array {
        return $this->db->query(
            "SELECT DATE_FORMAT(fecha,'%Y-%m') mes, COUNT(*) total
             FROM reservas GROUP BY mes ORDER BY mes DESC LIMIT 12"
        )->fetchAll();
    }

    public function ingresosPorMes(): array {
        return $this->db->query(
            "SELECT DATE_FORMAT(fecha,'%Y-%m') mes, SUM(total) ingresos
             FROM reservas WHERE estado='confirmada'
             GROUP BY mes ORDER BY mes DESC LIMIT 12"
        )->fetchAll();
    }

    public function porSala(): array {
        return $this->db->query(
            "SELECT s.nombre, COUNT(r.id) total
             FROM salas s LEFT JOIN reservas r ON r.sala_id=s.id
             GROUP BY s.id ORDER BY total DESC"
        )->fetchAll();
    }

    public function actualizarMontoPagado(int $id, int $monto): bool {
        $s = $this->db->prepare('UPDATE reservas SET monto_pagado=? WHERE id=? AND ? BETWEEN 0 AND total');
        $s->execute([$monto, $id, $monto]);
        return $s->rowCount() > 0;
    }

    /** Reporte mensual completo. Mantiene todas las salas mediante LEFT JOIN. */
    public function reporteMensual(string $desde, string $hasta): array {
        // 1. Resumen mensual + montos efectivamente registrados como recibidos.
        $q = $this->db->prepare("SELECT COUNT(*) total,
            COALESCE(SUM(r.total),0) valor_reservas,
            COALESCE(SUM(r.monto_pagado),0) dinero_recibido,
            COALESCE(SUM(CASE WHEN r.metodo_pago='efectivo' THEN r.monto_pagado ELSE 0 END),0) recibido_efectivo,
            COALESCE(SUM(CASE WHEN r.metodo_pago='transferencia' THEN r.monto_pagado ELSE 0 END),0) recibido_qr,
            COALESCE(SUM(r.estado='pendiente'),0) pendientes,
            COALESCE(SUM(r.estado='confirmada'),0) confirmadas,
            COALESCE(SUM(r.estado='finalizada'),0) finalizadas,
            COALESCE(SUM(r.estado='cancelada'),0) canceladas,
            COALESCE(SUM(r.estado='rechazada'),0) rechazadas
            FROM reservas r WHERE r.fecha BETWEEN ? AND ?");
        $q->execute([$desde,$hasta]); $resumen=$q->fetch() ?: [];
        $total=(int)($resumen['total']??0);
        foreach (['pendientes','confirmadas','finalizadas','canceladas','rechazadas'] as $k) {
            $resumen[$k.'_pct']=$total ? round((int)$resumen[$k]*100/$total,2) : 0;
        }

        // 2. Distribución por estado (incluye estados con cero y porcentaje).
        $porEstado=[];
        foreach ([['pendiente','Pendientes'],['confirmada','Confirmadas'],['finalizada','Finalizadas'],['cancelada','Canceladas'],['rechazada','Rechazadas']] as [$key,$label]) {
            $cnt=(int)($resumen[match($key){'pendiente'=>'pendientes','confirmada'=>'confirmadas','finalizada'=>'finalizadas','cancelada'=>'canceladas','rechazada'=>'rechazadas'}]??0);
            $porEstado[]=['estado'=>$key,'etiqueta'=>$label,'total'=>$cnt,'porcentaje'=>$total?round($cnt*100/$total,2):0];
        }

        // 3. Pagos por método y clase real de pago. Transferencia se presenta como QR.
        $q=$this->db->prepare("SELECT
            CASE m.metodo_pago WHEN 'transferencia' THEN 'QR / Transferencia' ELSE 'Efectivo' END metodo,
            m.metodo_pago,
            COALESCE(SUM(r.porcentaje_pago=100),0) cantidad_100,
            COALESCE(SUM(CASE WHEN r.porcentaje_pago=100 THEN r.monto_pagado ELSE 0 END),0) monto_100,
            COALESCE(SUM(r.porcentaje_pago=50),0) cantidad_50,
            COALESCE(SUM(CASE WHEN r.porcentaje_pago=50 THEN r.monto_pagado ELSE 0 END),0) monto_50,
            0 cantidad_otro_parcial, 0 monto_otro_parcial,
            COUNT(r.id) reservas, COALESCE(SUM(r.monto_pagado),0) dinero_recibido
            FROM (SELECT 'efectivo' metodo_pago UNION ALL SELECT 'transferencia') m
            LEFT JOIN reservas r ON LOWER(TRIM(r.metodo_pago))=m.metodo_pago AND r.fecha BETWEEN ? AND ?
            GROUP BY m.metodo_pago ORDER BY FIELD(m.metodo_pago,'efectivo','transferencia')");
        $q->execute([$desde,$hasta]); $pagosMetodo=$q->fetchAll();

        // 4. TOP 10 clientes por reservas (incluye cualquier estado para medir actividad).
        $q=$this->db->prepare("SELECT u.id usuario_id,CONCAT(u.nombre,' ',u.apellidos) usuario,u.email,
            COUNT(r.id) reservas,COALESCE(SUM(r.total),0) monto_total,
            COALESCE(SUM(r.monto_pagado),0) dinero_recibido
            FROM usuarios u JOIN reservas r ON r.usuario_id=u.id AND r.fecha BETWEEN ? AND ?
            WHERE u.rol='cliente' GROUP BY u.id,u.nombre,u.apellidos,u.email
            ORDER BY reservas DESC,monto_total DESC,usuario ASC");
        $q->execute([$desde,$hasta]); $top10=$q->fetchAll();

        // 5. Clientes con cero o una reserva. LEFT JOIN conserva clientes sin actividad.
        $q=$this->db->prepare("SELECT u.id usuario_id,CONCAT(u.nombre,' ',u.apellidos) usuario,u.email,COUNT(r.id) reservas,
            COALESCE(SUM(r.total),0) monto_total,COALESCE(SUM(r.monto_pagado),0) dinero_recibido
            FROM usuarios u LEFT JOIN reservas r ON r.usuario_id=u.id AND r.fecha BETWEEN ? AND ?
            WHERE u.rol='cliente' GROUP BY u.id,u.nombre,u.apellidos,u.email HAVING COUNT(r.id)<=1
            ORDER BY reservas ASC,usuario ASC");
        $q->execute([$desde,$hasta]); $usuariosFidelizacion=$q->fetchAll();

        // 6. Ranking de todas las salas; la condición de fecha permanece en el LEFT JOIN.
        $q=$this->db->prepare("SELECT s.id sala_id,s.nombre,s.activa,
            CASE WHEN s.activa=0 THEN 'Mantenimiento' ELSE 'Activa' END estado_sala,
            COUNT(r.id) reservas,COALESCE(SUM(r.total),0) valor_reservas,
            COALESCE(SUM(r.monto_pagado),0) ingresos,
            COALESCE(SUM(r.estado='cancelada'),0) canceladas
            FROM salas s LEFT JOIN reservas r ON r.sala_id=s.id AND r.fecha BETWEEN ? AND ?
            GROUP BY s.id,s.nombre,s.activa
            ORDER BY (COUNT(r.id)=0) ASC, reservas DESC, ingresos DESC, s.id ASC");
        $q->execute([$desde,$hasta]); $porSala=$q->fetchAll();
        $totalSalasIngresos=array_sum(array_map(fn($x)=>(float)$x['ingresos'],$porSala));
        $totalReservasSalas=array_sum(array_map(fn($x)=>(int)$x['reservas'],$porSala));
        foreach($porSala as &$sala){$sala['porcentaje_reservas']=$totalReservasSalas>0?round((int)$sala['reservas']*100/$totalReservasSalas,2):0; $sala['porcentaje_ingresos']=$totalSalasIngresos>0?round((float)$sala['ingresos']*100/$totalSalasIngresos,2):0;}
        unset($sala);
        $salaTop=null; foreach($porSala as $sala){if((int)$sala['reservas']>0){$salaTop=$sala;break;}}

        // 7. Usuarios que reservaron cada sala, con cantidad y total gastado. Sala vacía se resuelve en vista.
        $q=$this->db->prepare("SELECT s.id sala_id,s.nombre sala,CONCAT(u.nombre,' ',u.apellidos) usuario,u.id usuario_id,u.email,
            COUNT(r.id) reservas,COALESCE(SUM(r.total),0) monto_total,COALESCE(SUM(r.monto_pagado),0) dinero_recibido
            FROM salas s JOIN reservas r ON r.sala_id=s.id AND r.fecha BETWEEN ? AND ?
            JOIN usuarios u ON u.id=r.usuario_id AND u.rol='cliente'
            GROUP BY s.id,s.nombre,u.id,u.nombre,u.apellidos,u.email
            ORDER BY s.id ASC,reservas DESC,monto_total DESC,usuario ASC");
        $q->execute([$desde,$hasta]); $usuariosPorSala=[];
        foreach($q->fetchAll() as $row){$sid=(int)$row['sala_id']; if(!isset($usuariosPorSala[$sid]))$usuariosPorSala[$sid]=[]; $usuariosPorSala[$sid][]=$row;}

        // 8. Días lunes-domingo, conservando los días sin reservas.
        $q=$this->db->prepare("SELECT x.dia_num, COUNT(*) total FROM (SELECT WEEKDAY(fecha) AS dia_num FROM reservas WHERE fecha BETWEEN ? AND ?) x GROUP BY x.dia_num");
        $q->execute([$desde,$hasta]); $diasRows=$q->fetchAll(); $diasMap=[]; foreach($diasRows as $row)$diasMap[(int)$row['dia_num']]=(int)$row['total'];
        $nombresDias=['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo']; $porDia=[];
        foreach($nombresDias as $i=>$nombre)$porDia[]=['dia'=>$nombre,'dia_num'=>$i,'total'=>$diasMap[$i]??0];
        $maxDia=max(array_column($porDia,'total') ?: [0]);
        $diasTop=$maxDia>0?array_values(array_filter($porDia,fn($x)=>(int)$x['total']===$maxDia)):[];

        // 9. Franjas por hora de inicio real. No se inventa un horario de apertura.
        $q=$this->db->prepare("SELECT x.hora_num,
            CONCAT(LPAD(x.hora_num,2,'0'),':00-',LPAD(MOD(x.hora_num+1,24),2,'0'),':00') AS franja,
            COUNT(*) AS total
            FROM (SELECT HOUR(hora_inicio) AS hora_num FROM reservas WHERE fecha BETWEEN ? AND ?) x
            GROUP BY x.hora_num ORDER BY x.hora_num ASC");
        $q->execute([$desde,$hasta]); $porHorario=$q->fetchAll();
        $maxHora=$porHorario?max(array_map(fn($x)=>(int)$x['total'],$porHorario)):0;
        $horasTop=$maxHora>0?array_values(array_filter($porHorario,fn($x)=>(int)$x['total']===$maxHora)):[];

        // Detalle de todas las reservas del mes para auditar el total.
        $q=$this->db->prepare("SELECT r.id reserva_id,r.fecha,r.hora_inicio,r.hora_fin,r.horas,r.total,r.monto_pagado,
            r.porcentaje_pago,r.estado,r.metodo_pago,GREATEST(r.total-r.monto_pagado,0) saldo,
            CONCAT(u.nombre,' ',u.apellidos) usuario,u.email,s.nombre sala,
            CASE WHEN r.estado='cancelada' THEN 'Cancelada' WHEN r.total>0 AND r.monto_pagado>=r.total THEN 'Pagada al 100%'
              WHEN r.monto_pagado>0 AND r.monto_pagado<r.total AND r.monto_pagado=CEIL(r.total/2) THEN 'Pagada al 50%'
              WHEN r.monto_pagado>0 AND r.monto_pagado<r.total THEN 'Pago parcial' ELSE 'Sin pago registrado' END pago_estado
            FROM reservas r JOIN usuarios u ON u.id=r.usuario_id JOIN salas s ON s.id=r.sala_id
            WHERE r.fecha BETWEEN ? AND ? ORDER BY r.fecha DESC,r.hora_inicio DESC,r.id DESC");
        $q->execute([$desde,$hasta]); $porReserva=$q->fetchAll();

        return compact('resumen','porEstado','pagosMetodo','top10','usuariosFidelizacion','porSala','salaTop','usuariosPorSala','porDia','diasTop','porHorario','horasTop','porReserva');
    }

    public function porEstado(): array {
        return $this->db->query(
            "SELECT estado, COUNT(*) total FROM reservas GROUP BY estado"
        )->fetchAll();
    }

    // Listar con filtros opcionales (admin)
    public function listarFiltradas(string $metodo = '', string $fecha = '', int $salaId = 0, string $usuario = ''): array {
        $sql = 'SELECT r.*, s.nombre sala_nombre, u.nombre usuario_nombre, u.email usuario_email
                FROM reservas r
                JOIN salas    s ON s.id=r.sala_id
                JOIN usuarios u ON u.id=r.usuario_id
                WHERE 1=1';
        $params = [];
        if ($metodo)  { $sql .= ' AND r.metodo_pago=?'; $params[] = $metodo; }
        if ($fecha)   { $sql .= ' AND r.fecha=?';        $params[] = $fecha; }
        if ($salaId)  { $sql .= ' AND r.sala_id=?';      $params[] = $salaId; }
        if ($usuario) {
            $sql .= ' AND (u.nombre LIKE ? OR u.email LIKE ?)';
            $params[] = "%$usuario%"; $params[] = "%$usuario%";
        }
        $sql .= ' ORDER BY r.fecha DESC, r.hora_inicio DESC, r.created_at DESC, r.id DESC';
        $s = $this->db->prepare($sql);
        $s->execute($params);
        return $s->fetchAll();
    }

    // Reservas del usuario con datos de comprobante incluidos
    public function porUsuarioConComprobantes(int $uid): array {
        $s = $this->db->prepare(
            'SELECT r.*, s.nombre sala_nombre, s.imagen sala_imagen,
                    c.id comp_id, c.estado comp_estado, c.notas comp_notas, c.archivo comp_archivo
             FROM reservas r
             JOIN salas s ON s.id=r.sala_id
             LEFT JOIN comprobantes c ON c.reserva_id=r.id
             WHERE r.usuario_id=? ORDER BY r.created_at DESC, r.id DESC'
        );
        $s->execute([$uid]);
        return $s->fetchAll();
    }

    // Disponibilidad de todas las salas para un día (tabla de disponibilidad)
    public function disponibilidadPorDia(string $fecha): array {
        $s = $this->db->prepare(
            "SELECT r.sala_id, r.hora_inicio, r.hora_fin
             FROM reservas r
             WHERE r.fecha=? AND r.estado NOT IN ('cancelada')"
        );
        $s->execute([$fecha]);
        return $s->fetchAll();
    }

    // Disponibilidad de una sala en un rango de fechas (para vista semanal)
    public function disponibilidadSemana(int $salaId, string $fechaIni, string $fechaFin): array {
        $s = $this->db->prepare(
            "SELECT r.fecha, r.hora_inicio, r.hora_fin
             FROM reservas r
             WHERE r.sala_id=? AND r.fecha BETWEEN ? AND ?
             AND r.estado NOT IN ('cancelada')"
        );
        $s->execute([$salaId, $fechaIni, $fechaFin]);
        return $s->fetchAll();
    }

    public function recientes(int $n = 8): array {
        $s = $this->db->prepare(
            'SELECT r.*, s.nombre sala_nombre, u.nombre usuario_nombre
             FROM reservas r
             JOIN salas    s ON s.id=r.sala_id
             JOIN usuarios u ON u.id=r.usuario_id
             ORDER BY r.created_at DESC LIMIT ?'
        );
        $s->execute([$n]);
        return $s->fetchAll();
    }
}

// ── ComprobanteModel ───────────────────────────────────────────
class ComprobanteModel {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function crear(int $reservaId, string $archivo, int $montoPago): int {
        $s = $this->db->prepare(
            'INSERT INTO comprobantes (reserva_id,archivo,monto_pago) VALUES (?,?,?)
             ON DUPLICATE KEY UPDATE archivo=VALUES(archivo), monto_pago=VALUES(monto_pago), estado=\'pendiente\', notas=NULL'
        );
        $s->execute([$reservaId,$archivo,$montoPago]);
        return (int)$this->db->lastInsertId();
    }

    public function porReserva(int $reservaId): array|false {
        $s = $this->db->prepare(
            'SELECT c.*, r.total, r.metodo_pago, s.nombre sala_nombre, u.nombre usuario_nombre
             FROM comprobantes c
             JOIN reservas  r ON r.id=c.reserva_id
             JOIN salas     s ON s.id=r.sala_id
             JOIN usuarios  u ON u.id=r.usuario_id
             WHERE c.reserva_id=? LIMIT 1'
        );
        $s->execute([$reservaId]);
        return $s->fetch();
    }

    public function findById(int $id): array|false {
        $s = $this->db->prepare(
            'SELECT c.*, r.total, r.metodo_pago, r.fecha, r.hora_inicio, r.hora_fin,
                    s.nombre sala_nombre, u.id usuario_id, u.nombre usuario_nombre, u.email
             FROM comprobantes c
             JOIN reservas  r ON r.id=c.reserva_id
             JOIN salas     s ON s.id=r.sala_id
             JOIN usuarios  u ON u.id=r.usuario_id
             WHERE c.id=? LIMIT 1'
        );
        $s->execute([$id]);
        return $s->fetch();
    }

    public function listar(): array {
        return $this->db->query(
            'SELECT c.*, r.total, r.metodo_pago, r.fecha,
                    s.nombre sala_nombre, u.nombre usuario_nombre
             FROM comprobantes c
             JOIN reservas  r ON r.id=c.reserva_id
             JOIN salas     s ON s.id=r.sala_id
             JOIN usuarios  u ON u.id=r.usuario_id
             ORDER BY c.created_at DESC'
        )->fetchAll();
    }

    public function cambiarEstado(int $id, string $estado, string $notas = ''): void {
        $s = $this->db->prepare('UPDATE comprobantes SET estado=?,notas=? WHERE id=?');
        $s->execute([$estado,$notas,$id]);
    }

    public function totalPendientes(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM comprobantes WHERE estado='pendiente'")->fetchColumn();
    }
}

// ── ProductoModel ──────────────────────────────────────────────
class ProductoModel {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function listar(): array {
        return $this->db->query(
            "SELECT p.*, CONCAT(u.nombre, ' ', u.apellidos) AS administrador_nombre FROM productos p LEFT JOIN usuarios u ON u.id=p.admin_id ORDER BY p.id ASC"
        )->fetchAll();
    }

    public function listarActivos(): array {
        return $this->db->query(
            "SELECT * FROM productos WHERE activo=1 ORDER BY categoria, nombre"
        )->fetchAll();
    }

    /** ¿Ya hay otro producto con ese nombre? (sin distinguir mayúsculas ni espacios sobrantes) */
    public function existeNombre(string $nombre, int $excludeId = 0): bool {
        $s = $this->db->prepare('SELECT id FROM productos WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?)) AND id != ? LIMIT 1');
        $s->execute([$nombre, $excludeId]);
        return (bool)$s->fetch();
    }

    public function findById(int $id): array|false {
        $s = $this->db->prepare('SELECT * FROM productos WHERE id=? LIMIT 1');
        $s->execute([$id]);
        return $s->fetch();
    }

    public function crear(string $nombre, string $desc, float $precio, string $cat, ?string $img, int $adminId = 0): int {
        $s = $this->db->prepare(
            'INSERT INTO productos (nombre, descripcion, precio, categoria, imagen, admin_id) VALUES (?,?,?,?,?,?)'
        );
        $s->execute([$nombre, $desc, $precio, $cat, $img, $adminId ?: null]);
        return (int)$this->db->lastInsertId();
    }

    public function actualizar(int $id, string $nombre, string $desc, float $precio, string $cat, ?string $img): void {
        if ($img) {
            $s = $this->db->prepare(
                'UPDATE productos SET nombre=?, descripcion=?, precio=?, categoria=?, imagen=? WHERE id=?'
            );
            $s->execute([$nombre, $desc, $precio, $cat, $img, $id]);
        } else {
            $s = $this->db->prepare(
                'UPDATE productos SET nombre=?, descripcion=?, precio=?, categoria=? WHERE id=?'
            );
            $s->execute([$nombre, $desc, $precio, $cat, $id]);
        }
    }

    public function toggleActivo(int $id): void {
        $this->db->prepare('UPDATE productos SET activo = NOT activo WHERE id=?')->execute([$id]);
    }

    public function eliminar(int $id): void {
        $this->db->prepare('DELETE FROM productos WHERE id=?')->execute([$id]);
    }

    public function total(): int {
        return (int)$this->db->query('SELECT COUNT(*) FROM productos')->fetchColumn();
    }
}

// ── MensajeModel ───────────────────────────────────────────────
class MensajeModel {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    /** Todos los mensajes de una conversación (cliente ↔ admin) */
    public function porUsuario(int $uid): array {
        $s = $this->db->prepare(
            'SELECT * FROM mensajes WHERE usuario_id=? ORDER BY created_at ASC'
        );
        $s->execute([$uid]);
        return $s->fetchAll();
    }

    /** Mensajes más recientes que $lastId */
    public function desdeId(int $uid, int $lastId): array {
        $s = $this->db->prepare(
            'SELECT * FROM mensajes WHERE usuario_id=? AND id>? ORDER BY id ASC'
        );
        $s->execute([$uid, $lastId]);
        return $s->fetchAll();
    }

    public function ultimoId(int $uid): int {
        $s = $this->db->prepare('SELECT COALESCE(MAX(id),0) FROM mensajes WHERE usuario_id=?');
        $s->execute([$uid]);
        return (int)$s->fetchColumn();
    }

    /** Crear mensaje */
    public function crear(int $uid, string $contenido, string $remitente): int {
        $s = $this->db->prepare(
            'INSERT INTO mensajes (usuario_id,contenido,remitente) VALUES (?,?,?)'
        );
        $s->execute([$uid, $contenido, $remitente]);
        return (int)$this->db->lastInsertId();
    }

    /** Marcar como leídos los mensajes de un $remitente para ese usuario */
    public function marcarLeidos(int $uid, string $remitente): void {
        $s = $this->db->prepare(
            "UPDATE mensajes SET leido=1 WHERE usuario_id=? AND remitente=? AND leido=0"
        );
        $s->execute([$uid, $remitente]);
    }

    /** Mensajes sin leer del admin (enviados por clientes) */
    public function sinLeerAdmin(): int {
        return (int)$this->db->query(
            "SELECT COUNT(*) FROM mensajes WHERE remitente='cliente' AND leido=0"
        )->fetchColumn();
    }

    /** Mensajes sin leer para un cliente (enviados por admin) */
    public function sinLeerCliente(int $uid): int {
        $s = $this->db->prepare(
            "SELECT COUNT(*) FROM mensajes WHERE usuario_id=? AND remitente='admin' AND leido=0"
        );
        $s->execute([$uid]);
        return (int)$s->fetchColumn();
    }

    /** Resumen para el punto rojo del cliente: mensajes del admin sin leer y último id. */
    public function resumenNoLeidosCliente(int $uid): array {
        $s = $this->db->prepare(
            "SELECT COUNT(*) c, COALESCE(MAX(id),0) ultimo_id
             FROM mensajes WHERE usuario_id=? AND remitente='admin' AND leido=0"
        );
        $s->execute([$uid]);
        $r = $s->fetch(PDO::FETCH_ASSOC) ?: [];
        return ['sin_leer' => (int)($r['c'] ?? 0), 'ultimo_id' => (int)($r['ultimo_id'] ?? 0)];
    }

    /** Conversaciones con mensajes de cliente sin leer: una fila por cliente (consulta única). */
    public function resumenNoLeidosAdmin(): array {
        return $this->db->query(
            "SELECT g.usuario_id, g.sin_leer, g.ultimo_id, m.contenido ultimo_msg, m.created_at ultimo_at
             FROM (SELECT usuario_id, COUNT(*) sin_leer, MAX(id) ultimo_id
                   FROM mensajes WHERE remitente='cliente' AND leido=0 GROUP BY usuario_id) g
             JOIN mensajes m ON m.id = g.ultimo_id
             ORDER BY g.ultimo_id DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lista de conversaciones para el admin (clientes que ya escribieron) */
    public function conversaciones(): array {
        return $this->db->query(
            "SELECT u.id, u.nombre, u.apellidos, u.email, u.foto,
             (SELECT COUNT(*) FROM mensajes
              WHERE usuario_id=u.id AND remitente='cliente' AND leido=0) sin_leer,
             (SELECT contenido FROM mensajes
              WHERE usuario_id=u.id ORDER BY created_at DESC LIMIT 1) ultimo_msg,
             (SELECT created_at FROM mensajes
              WHERE usuario_id=u.id ORDER BY created_at DESC LIMIT 1) ultimo_at
             FROM usuarios u
             WHERE u.rol='cliente'
               AND EXISTS (SELECT 1 FROM mensajes WHERE usuario_id=u.id)
             ORDER BY ultimo_at DESC"
        )->fetchAll();
    }
}
