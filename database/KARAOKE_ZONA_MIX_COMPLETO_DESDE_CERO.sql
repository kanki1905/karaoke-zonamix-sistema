DROP DATABASE IF EXISTS karaoke_db;
-- KARAOKE ZONA MIX v10.0 — BASE ÚNICA PARA INSTALACIÓN DESDE CERO
-- IMPORTANTE: este script elimina por completo karaoke_db y todos sus datos.
-- Usar únicamente si se desea empezar de cero. Hacer respaldo antes si se necesita conservar algo.
CREATE DATABASE IF NOT EXISTS karaoke_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE karaoke_db;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  apellidos VARCHAR(50) NOT NULL DEFAULT '',
  email VARCHAR(50) NOT NULL UNIQUE,
  telefono VARCHAR(8) NOT NULL DEFAULT '',
  password VARCHAR(255) NOT NULL,
  rol ENUM('admin','cliente') NOT NULL DEFAULT 'cliente',
  foto VARCHAR(255) DEFAULT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_usuarios_rol_nombre (rol,nombre,apellidos)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS salas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT,
  capacidad INT NOT NULL DEFAULT 4,
  precio_hora INT NOT NULL DEFAULT 50,
  imagen VARCHAR(255) DEFAULT NULL,
  activa TINYINT(1) NOT NULL DEFAULT 1,
  numero INT UNIQUE DEFAULT NULL,
  UNIQUE KEY uq_salas_nombre (nombre),
  INDEX idx_salas_activa (activa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reservas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  sala_id INT UNSIGNED NOT NULL,
  fecha DATE NOT NULL,
  hora_inicio TIME NOT NULL,
  hora_fin TIME NOT NULL,
  horas INT NOT NULL DEFAULT 1,
  total INT NOT NULL DEFAULT 0,
  monto_pagado INT NOT NULL DEFAULT 0,
  porcentaje_pago TINYINT UNSIGNED DEFAULT NULL COMMENT 'NULL = el cliente aún no eligió; 50 o 100',
  estado ENUM('pendiente','confirmada','cancelada','finalizada') NOT NULL DEFAULT 'pendiente',
  metodo_pago ENUM('efectivo','transferencia') NOT NULL DEFAULT 'efectivo',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reservas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_reservas_sala FOREIGN KEY (sala_id) REFERENCES salas(id) ON DELETE CASCADE,
  CONSTRAINT chk_reservas_total CHECK (total >= 0),
  CONSTRAINT chk_reservas_monto_pagado CHECK (monto_pagado >= 0 AND monto_pagado <= total),
  CONSTRAINT chk_reservas_porcentaje_pago CHECK (porcentaje_pago IS NULL OR porcentaje_pago IN (50,100)),
  INDEX idx_reservas_sala_fecha_estado_horas (sala_id,fecha,estado,hora_inicio,hora_fin),
  INDEX idx_reservas_fecha_estado (fecha,estado),
  INDEX idx_reservas_usuario_fecha (usuario_id,fecha),
  INDEX idx_reservas_estado_metodo (estado,metodo_pago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comprobantes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reserva_id INT UNSIGNED NOT NULL UNIQUE,
  archivo VARCHAR(255) NOT NULL,
  monto_pago INT DEFAULT NULL,
  estado ENUM('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
  notas TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_comprobantes_reserva FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
  CONSTRAINT chk_comprobantes_monto CHECK (monto_pago IS NULL OR monto_pago >= 0),
  INDEX idx_comprobantes_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mensajes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  contenido TEXT NOT NULL,
  remitente ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
  leido TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mensajes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  INDEX idx_mensajes_usuario (usuario_id,id),
  INDEX idx_mensajes_leido (remitente,leido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS productos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  descripcion TEXT,
  precio DECIMAL(8,2) NOT NULL DEFAULT 0,
  categoria ENUM('bebida','comida','snack','otro') NOT NULL DEFAULT 'bebida',
  imagen VARCHAR(255) DEFAULT NULL,
  admin_id INT UNSIGNED DEFAULT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_productos_admin FOREIGN KEY (admin_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT chk_productos_precio CHECK (precio >= 0.5 AND (precio * 2) = FLOOR(precio * 2)),
  INDEX idx_productos_activo_categoria (activo,categoria),
  UNIQUE KEY uq_productos_nombre (nombre),
  INDEX idx_productos_admin (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cuenta administradora inicial. Usuario: admin@karaoke.com | Contraseña: Admin2026!
INSERT INTO usuarios (nombre,apellidos,email,telefono,password,rol)
SELECT 'Administrador','Sistema','admin@karaoke.com','70000000','$2y$12$vsJ5vWPxXo0dIlJVpXYYPuU0ubVVSdUYpLSsnxUSEbH4LSh0G0oKi','admin'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email='admin@karaoke.com');

INSERT INTO salas (id,nombre,descripcion,capacidad,precio_hora,activa,numero) VALUES
(1,'Sala Estrella','Sala principal con equipo profesional de karaoke y pantalla LED 65 pulgadas.',6,80,1,1),
(2,'Sala Luna','Ambiente íntimo con iluminación tenue.',2,60,1,2),
(3,'Sala Sol','Sala luminosa para grupos familiares.',8,100,0,3),
(4,'Sala Galaxia','Decoración espacial y sonido envolvente.',10,120,1,4),
(5,'Sala Comet','Compacta y acogedora.',3,55,1,5),
(6,'Sala Aurora','Mural de aurora boreal.',5,75,0,6),
(7,'Sala Nebulosa','Sala VIP amplia.',12,150,1,7),
(8,'Sala Átomo','Sala temática retro-futurista.',4,65,1,8),
(9,'Sala Quasar','Sonido hi-fi y micrófonos inalámbricos.',6,90,1,9),
(10,'Sala Pulsar','Sala íntima con barra privada.',4,70,1,10),
(11,'Sala Void','Proyección inmersiva.',8,110,1,11),
(12,'Sala Horizon','Sala panorámica para celebraciones.',10,130,1,12),
(13,'Sala Zenith','Suite VIP con equipo DJ.',15,200,1,13)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre),descripcion=VALUES(descripcion),capacidad=VALUES(capacidad),precio_hora=VALUES(precio_hora),numero=VALUES(numero);

INSERT INTO productos (nombre,descripcion,precio,categoria) SELECT 'Coca-Cola','Lata 350 ml bien fría.',8.00,'bebida' WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Coca-Cola');
INSERT INTO productos (nombre,descripcion,precio,categoria) SELECT 'Fanta Naranja','Lata 350 ml.',8.00,'bebida' WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Fanta Naranja');
INSERT INTO productos (nombre,descripcion,precio,categoria) SELECT 'Agua mineral','Botella 500 ml.',5.00,'bebida' WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Agua mineral');
INSERT INTO productos (nombre,descripcion,precio,categoria) SELECT 'Jugo de durazno','Caja de jugo 200 ml.',6.00,'bebida' WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Jugo de durazno');
INSERT INTO productos (nombre,descripcion,precio,categoria) SELECT 'Cerveza','Lata 350 ml; venta solo a mayores de edad.',12.00,'bebida' WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Cerveza');
INSERT INTO productos (nombre,descripcion,precio,categoria) SELECT 'Papas fritas','Bolsa grande.',10.00,'snack' WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Papas fritas');
INSERT INTO productos (nombre,descripcion,precio,categoria) SELECT 'Chizitos','Bolsa familiar.',8.00,'snack' WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Chizitos');
INSERT INTO productos (nombre,descripcion,precio,categoria) SELECT 'Pique macho','Plato para compartir.',35.00,'comida' WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Pique macho');
INSERT INTO productos (nombre,descripcion,precio,categoria) SELECT 'Salchipapas','Porción individual.',20.00,'comida' WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Salchipapas');
INSERT INTO productos (nombre,descripcion,precio,categoria) SELECT 'Sándwich de pollo','Pollo a la plancha con vegetales.',18.00,'comida' WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Sándwich de pollo');

-- Los productos de ejemplo quedan registrados a nombre del administrador inicial.
UPDATE productos SET admin_id = (SELECT id FROM usuarios WHERE email='admin@karaoke.com' LIMIT 1) WHERE admin_id IS NULL;
