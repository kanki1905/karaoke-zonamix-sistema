-- ═══════════════════════════════════════════════════════════════
-- KARAOKE ZONA MIX — DATOS DE PRUEBA (usuarios, reservas, comprobantes, chats)
-- Ejecutar DESPUÉS de importar KARAOKE_ZONA_MIX_COMPLETO_DESDE_CERO.sql
-- Se puede ejecutar varias veces: borra y vuelve a crear solo sus propios datos.
-- Contraseña de TODAS las cuentas de prueba: Admin2026!
-- Las fechas son relativas a HOY (CURDATE), así que siempre quedan actualizadas.
-- Fotos y comprobantes: public/uploads/ (ya incluidos en el zip).
-- ═══════════════════════════════════════════════════════════════
USE karaoke_db;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;

-- Limpieza de una ejecución anterior (por cascada borra reservas, comprobantes y mensajes)
DELETE FROM usuarios WHERE id BETWEEN 101 AND 199;

-- ── Administrador inicial: foto ──
UPDATE usuarios SET foto='perfil_admin_inicial.jpg' WHERE email='admin@karaoke.com';

-- ── Segundo administrador + clientes ──
INSERT INTO usuarios (id,nombre,apellidos,email,telefono,password,rol,foto,created_at) VALUES
(101,'Rosa Elena','Quiroga Paz','admin2@prueba.com','69999001','$2y$12$vsJ5vWPxXo0dIlJVpXYYPuU0ubVVSdUYpLSsnxUSEbH4LSh0G0oKi','admin','perfil_101.jpg',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -120 DAY),'09:00:00')),
(102,'Carlos','Mamani Quispe','carlos.mamani@prueba.com','71234501','$2y$12$vsJ5vWPxXo0dIlJVpXYYPuU0ubVVSdUYpLSsnxUSEbH4LSh0G0oKi','cliente','perfil_102.jpg',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -125 DAY),'10:15:00')),
(103,'María Fernanda','Choque Limachi','maria.choque@prueba.com','72345602','$2y$12$vsJ5vWPxXo0dIlJVpXYYPuU0ubVVSdUYpLSsnxUSEbH4LSh0G0oKi','cliente','perfil_103.jpg',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -122 DAY),'11:15:00')),
(104,'Luis Alberto','Condori Huanca','luis.condori@prueba.com','73456703','$2y$12$vsJ5vWPxXo0dIlJVpXYYPuU0ubVVSdUYpLSsnxUSEbH4LSh0G0oKi','cliente','perfil_104.jpg',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -119 DAY),'12:15:00')),
(105,'Ana Lucía','Flores Aguilar','ana.flores@prueba.com','74567804','$2y$12$vsJ5vWPxXo0dIlJVpXYYPuU0ubVVSdUYpLSsnxUSEbH4LSh0G0oKi','cliente','perfil_105.jpg',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -116 DAY),'13:15:00')),
(106,'Diego Armando','Vargas Cruz','diego.vargas@prueba.com','75678905','$2y$12$vsJ5vWPxXo0dIlJVpXYYPuU0ubVVSdUYpLSsnxUSEbH4LSh0G0oKi','cliente','perfil_106.jpg',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -113 DAY),'14:15:00')),
(107,'Valeria','Rojas Mendoza','valeria.rojas@prueba.com','76789006','$2y$12$vsJ5vWPxXo0dIlJVpXYYPuU0ubVVSdUYpLSsnxUSEbH4LSh0G0oKi','cliente','perfil_107.jpg',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -110 DAY),'15:15:00')),
(108,'Jorge Luis','Apaza Mamani','jorge.apaza@prueba.com','77890107','$2y$12$vsJ5vWPxXo0dIlJVpXYYPuU0ubVVSdUYpLSsnxUSEbH4LSh0G0oKi','cliente','perfil_108.jpg',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -107 DAY),'16:15:00')),
(109,'Camila Andrea','Gutiérrez Vela','camila.gutierrez@prueba.com','78901208','$2y$12$vsJ5vWPxXo0dIlJVpXYYPuU0ubVVSdUYpLSsnxUSEbH4LSh0G0oKi','cliente','perfil_109.jpg',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -104 DAY),'17:15:00')),
(110,'Sebastián','Torrez Ayala','sebastian.torrez@prueba.com','79012309','$2y$12$vsJ5vWPxXo0dIlJVpXYYPuU0ubVVSdUYpLSsnxUSEbH4LSh0G0oKi','cliente','perfil_110.jpg',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -101 DAY),'18:15:00')),
(111,'Daniela','Fernández Soria','daniela.fernandez@prueba.com','60123410','$2y$12$vsJ5vWPxXo0dIlJVpXYYPuU0ubVVSdUYpLSsnxUSEbH4LSh0G0oKi','cliente','perfil_111.jpg',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -98 DAY),'19:15:00')),
(112,'Marco Antonio','Ticona Ramos','marco.ticona@prueba.com','61234511','$2y$12$vsJ5vWPxXo0dIlJVpXYYPuU0ubVVSdUYpLSsnxUSEbH4LSh0G0oKi','cliente','perfil_112.jpg',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -95 DAY),'10:15:00')),
(113,'Patricia','Salazar Medina','patricia.salazar@prueba.com','62345612','$2y$12$vsJ5vWPxXo0dIlJVpXYYPuU0ubVVSdUYpLSsnxUSEbH4LSh0G0oKi','cliente','perfil_113.jpg',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -92 DAY),'11:15:00'));

-- ── Salas: imagen para las 13 salas ──
UPDATE salas SET imagen='sala_seed_1.jpg' WHERE id=1;
UPDATE salas SET imagen='sala_seed_2.jpg' WHERE id=2;
UPDATE salas SET imagen='sala_seed_3.jpg' WHERE id=3;
UPDATE salas SET imagen='sala_seed_4.jpg' WHERE id=4;
UPDATE salas SET imagen='sala_seed_5.jpg' WHERE id=5;
UPDATE salas SET imagen='sala_seed_6.jpg' WHERE id=6;
UPDATE salas SET imagen='sala_seed_7.jpg' WHERE id=7;
UPDATE salas SET imagen='sala_seed_8.jpg' WHERE id=8;
UPDATE salas SET imagen='sala_seed_9.jpg' WHERE id=9;
UPDATE salas SET imagen='sala_seed_10.jpg' WHERE id=10;
UPDATE salas SET imagen='sala_seed_11.jpg' WHERE id=11;
UPDATE salas SET imagen='sala_seed_12.jpg' WHERE id=12;
UPDATE salas SET imagen='sala_seed_13.jpg' WHERE id=13;

-- ── Productos adicionales (todos con admin que los dio de alta) ──
INSERT INTO productos (nombre,descripcion,precio,categoria,imagen,admin_id,activo) SELECT 'Sprite','Lata 350 ml bien fría.',8.00,'bebida','prod_seed_sprite.jpg',(SELECT id FROM usuarios WHERE email='admin@karaoke.com' LIMIT 1),1 WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Sprite');
INSERT INTO productos (nombre,descripcion,precio,categoria,imagen,admin_id,activo) SELECT 'Café americano','Taza grande recién hecho.',10.00,'bebida','prod_seed_cafe_americano.jpg',(SELECT id FROM usuarios WHERE email='admin@karaoke.com' LIMIT 1),1 WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Café americano');
INSERT INTO productos (nombre,descripcion,precio,categoria,imagen,admin_id,activo) SELECT 'Hamburguesa clásica','Carne, queso, lechuga y tomate con papas.',28.00,'comida','prod_seed_hamburguesa_clasica.jpg',(SELECT id FROM usuarios WHERE email='admin@karaoke.com' LIMIT 1),1 WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Hamburguesa clásica');
INSERT INTO productos (nombre,descripcion,precio,categoria,imagen,admin_id,activo) SELECT 'Nachos con queso','Porción para compartir con salsa picante.',22.00,'snack','prod_seed_nachos_con_queso.jpg',(SELECT id FROM usuarios WHERE email='admin@karaoke.com' LIMIT 1),1 WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Nachos con queso');
INSERT INTO productos (nombre,descripcion,precio,categoria,imagen,admin_id,activo) SELECT 'Alitas BBQ','8 piezas con salsa barbacoa.',32.00,'comida','prod_seed_alitas_bbq.jpg',(SELECT id FROM usuarios WHERE email='admin@karaoke.com' LIMIT 1),1 WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Alitas BBQ');
INSERT INTO productos (nombre,descripcion,precio,categoria,imagen,admin_id,activo) SELECT 'Helado de vainilla','Copa doble con salsa de chocolate.',15.00,'snack','prod_seed_helado_de_vainilla.jpg',(SELECT id FROM usuarios WHERE email='admin@karaoke.com' LIMIT 1),0 WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Helado de vainilla');
INSERT INTO productos (nombre,descripcion,precio,categoria,imagen,admin_id,activo) SELECT 'Pack decoración cumpleaños','Globos, letrero y velas para la sala.',45.00,'otro','prod_seed_pack_decoracion_cumpleanos.jpg',(SELECT id FROM usuarios WHERE email='admin@karaoke.com' LIMIT 1),1 WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre='Pack decoración cumpleaños');
-- Imagen y administrador para TODOS los productos (los de ejemplo alternan entre los dos admins)
UPDATE productos SET imagen='prod_seed_coca_cola.jpg' WHERE nombre='Coca-Cola';
UPDATE productos SET imagen='prod_seed_fanta_naranja.jpg' WHERE nombre='Fanta Naranja';
UPDATE productos SET imagen='prod_seed_agua_mineral.jpg' WHERE nombre='Agua mineral';
UPDATE productos SET imagen='prod_seed_jugo_de_durazno.jpg' WHERE nombre='Jugo de durazno';
UPDATE productos SET imagen='prod_seed_cerveza.jpg' WHERE nombre='Cerveza';
UPDATE productos SET imagen='prod_seed_papas_fritas.jpg' WHERE nombre='Papas fritas';
UPDATE productos SET imagen='prod_seed_chizitos.jpg' WHERE nombre='Chizitos';
UPDATE productos SET imagen='prod_seed_pique_macho.jpg' WHERE nombre='Pique macho';
UPDATE productos SET imagen='prod_seed_salchipapas.jpg' WHERE nombre='Salchipapas';
UPDATE productos SET imagen='prod_seed_sandwich_de_pollo.jpg' WHERE nombre='Sándwich de pollo';
UPDATE productos SET imagen='prod_seed_sprite.jpg' WHERE nombre='Sprite';
UPDATE productos SET imagen='prod_seed_cafe_americano.jpg' WHERE nombre='Café americano';
UPDATE productos SET imagen='prod_seed_hamburguesa_clasica.jpg' WHERE nombre='Hamburguesa clásica';
UPDATE productos SET imagen='prod_seed_nachos_con_queso.jpg' WHERE nombre='Nachos con queso';
UPDATE productos SET imagen='prod_seed_alitas_bbq.jpg' WHERE nombre='Alitas BBQ';
UPDATE productos SET imagen='prod_seed_helado_de_vainilla.jpg' WHERE nombre='Helado de vainilla';
UPDATE productos SET imagen='prod_seed_pack_decoracion_cumpleanos.jpg' WHERE nombre='Pack decoración cumpleaños';
UPDATE productos SET admin_id = (SELECT id FROM usuarios WHERE email='admin@karaoke.com' LIMIT 1) WHERE admin_id IS NULL;
UPDATE productos SET admin_id = 101 WHERE categoria IN ('snack','otro');

-- ── Reservas (id 1001+) ──
INSERT INTO reservas (id,usuario_id,sala_id,fecha,hora_inicio,hora_fin,horas,total,monto_pagado,porcentaje_pago,estado,metodo_pago,created_at) VALUES
(1024,108,9,DATE_ADD(CURDATE(), INTERVAL -80 DAY),'09:00:00','10:00:00',1,90,90,50,'finalizada','efectivo',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -81 DAY),'18:55:00')),
(1023,108,7,DATE_ADD(CURDATE(), INTERVAL -70 DAY),'14:00:00','17:00:00',3,450,450,100,'finalizada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -72 DAY),'21:32:00')),
(1022,108,11,DATE_ADD(CURDATE(), INTERVAL -68 DAY),'09:00:00','11:00:00',2,220,220,100,'finalizada','efectivo',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -70 DAY),'21:16:00')),
(1021,109,4,DATE_ADD(CURDATE(), INTERVAL -67 DAY),'11:00:00','14:00:00',3,360,360,100,'finalizada','efectivo',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -69 DAY),'13:40:00')),
(1020,107,8,DATE_ADD(CURDATE(), INTERVAL -66 DAY),'08:00:00','10:00:00',2,130,130,100,'finalizada','efectivo',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -70 DAY),'16:48:00')),
(1019,102,5,DATE_ADD(CURDATE(), INTERVAL -65 DAY),'17:00:00','18:00:00',1,55,55,50,'finalizada','efectivo',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -68 DAY),'19:20:00')),
(1028,111,12,DATE_ADD(CURDATE(), INTERVAL -58 DAY),'15:00:00','17:00:00',2,260,0,100,'cancelada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -60 DAY),'08:42:00')),
(1018,111,2,DATE_ADD(CURDATE(), INTERVAL -52 DAY),'15:00:00','16:00:00',1,60,60,50,'finalizada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -53 DAY),'21:00:00')),
(1017,108,2,DATE_ADD(CURDATE(), INTERVAL -50 DAY),'19:00:00','21:00:00',2,120,120,100,'finalizada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -52 DAY),'18:15:00')),
(1016,103,9,DATE_ADD(CURDATE(), INTERVAL -49 DAY),'15:00:00','19:00:00',4,360,360,50,'finalizada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -52 DAY),'15:03:00')),
(1015,110,10,DATE_ADD(CURDATE(), INTERVAL -44 DAY),'19:00:00','23:00:00',4,280,280,50,'finalizada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -47 DAY),'11:43:00')),
(1027,104,12,DATE_ADD(CURDATE(), INTERVAL -41 DAY),'20:00:00','23:00:00',3,390,0,100,'cancelada','efectivo',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -43 DAY),'16:55:00')),
(1014,106,13,DATE_ADD(CURDATE(), INTERVAL -40 DAY),'21:00:00','22:00:00',1,200,200,100,'finalizada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -43 DAY),'10:59:00')),
(1013,109,7,DATE_ADD(CURDATE(), INTERVAL -37 DAY),'15:00:00','19:00:00',4,600,600,50,'finalizada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -41 DAY),'19:57:00')),
(1012,110,13,DATE_ADD(CURDATE(), INTERVAL -28 DAY),'15:00:00','18:00:00',3,600,600,50,'finalizada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -32 DAY),'21:25:00')),
(1011,106,5,DATE_ADD(CURDATE(), INTERVAL -27 DAY),'21:00:00','23:00:00',2,110,110,100,'finalizada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -28 DAY),'19:23:00')),
(1010,112,13,DATE_ADD(CURDATE(), INTERVAL -24 DAY),'16:00:00','18:00:00',2,400,400,100,'finalizada','efectivo',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -28 DAY),'20:14:00')),
(1009,113,10,DATE_ADD(CURDATE(), INTERVAL -23 DAY),'15:00:00','19:00:00',4,280,280,100,'finalizada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -26 DAY),'16:53:00')),
(1026,107,13,DATE_ADD(CURDATE(), INTERVAL -23 DAY),'15:00:00','17:00:00',2,400,0,100,'cancelada','efectivo',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -24 DAY),'12:21:00')),
(1008,109,9,DATE_ADD(CURDATE(), INTERVAL -22 DAY),'10:00:00','13:00:00',3,270,270,100,'finalizada','efectivo',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -24 DAY),'21:12:00')),
(1007,110,4,DATE_ADD(CURDATE(), INTERVAL -18 DAY),'19:00:00','20:00:00',1,120,120,50,'finalizada','efectivo',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -19 DAY),'13:22:00')),
(1006,109,7,DATE_ADD(CURDATE(), INTERVAL -16 DAY),'17:00:00','20:00:00',3,450,450,100,'finalizada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -17 DAY),'15:40:00')),
(1005,108,11,DATE_ADD(CURDATE(), INTERVAL -15 DAY),'09:00:00','12:00:00',3,330,330,100,'finalizada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -19 DAY),'15:35:00')),
(1004,108,8,DATE_ADD(CURDATE(), INTERVAL -13 DAY),'15:00:00','17:00:00',2,130,130,100,'finalizada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -14 DAY),'20:30:00')),
(1003,104,7,DATE_ADD(CURDATE(), INTERVAL -10 DAY),'15:00:00','17:00:00',2,300,300,100,'finalizada','efectivo',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -13 DAY),'20:43:00')),
(1002,107,13,DATE_ADD(CURDATE(), INTERVAL -9 DAY),'19:00:00','20:00:00',1,200,200,100,'finalizada','efectivo',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -10 DAY),'17:19:00')),
(1025,102,13,DATE_ADD(CURDATE(), INTERVAL -9 DAY),'20:00:00','22:00:00',2,400,0,100,'cancelada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -10 DAY),'11:10:00')),
(1001,107,9,DATE_ADD(CURDATE(), INTERVAL -6 DAY),'13:00:00','15:00:00',2,180,180,50,'finalizada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -10 DAY),'18:26:00')),
(1029,102,13,DATE_ADD(CURDATE(), INTERVAL 0 DAY),'14:00:00','18:00:00',4,800,800,100,'confirmada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -4 DAY),'10:31:00')),
(1032,109,10,DATE_ADD(CURDATE(), INTERVAL 0 DAY),'15:00:00','19:00:00',4,280,0,100,'pendiente','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -4 DAY),'08:22:00')),
(1030,104,1,DATE_ADD(CURDATE(), INTERVAL 0 DAY),'16:00:00','18:00:00',2,160,80,50,'confirmada','efectivo',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'21:53:00')),
(1031,107,13,DATE_ADD(CURDATE(), INTERVAL 0 DAY),'20:00:00','23:00:00',3,600,300,50,'confirmada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'16:44:00')),
(1033,112,13,DATE_ADD(CURDATE(), INTERVAL 1 DAY),'10:00:00','11:00:00',1,200,200,100,'confirmada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'14:50:00')),
(1034,112,11,DATE_ADD(CURDATE(), INTERVAL 2 DAY),'10:00:00','12:00:00',2,220,220,100,'confirmada','efectivo',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'08:40:00')),
(1035,110,9,DATE_ADD(CURDATE(), INTERVAL 3 DAY),'14:00:00','16:00:00',2,180,90,50,'confirmada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -3 DAY),'10:05:00')),
(1036,104,2,DATE_ADD(CURDATE(), INTERVAL 4 DAY),'12:00:00','15:00:00',3,180,0,50,'pendiente','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -2 DAY),'12:23:00')),
(1037,109,4,DATE_ADD(CURDATE(), INTERVAL 5 DAY),'15:00:00','17:00:00',2,240,0,50,'pendiente','efectivo',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -3 DAY),'16:46:00')),
(1038,107,7,DATE_ADD(CURDATE(), INTERVAL 6 DAY),'10:00:00','13:00:00',3,450,0,100,'pendiente','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -3 DAY),'12:08:00')),
(1039,110,10,DATE_ADD(CURDATE(), INTERVAL 8 DAY),'16:00:00','17:00:00',1,70,35,50,'confirmada','efectivo',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -2 DAY),'19:47:00')),
(1040,102,2,DATE_ADD(CURDATE(), INTERVAL 10 DAY),'11:00:00','13:00:00',2,120,0,100,'pendiente','efectivo',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 0 DAY),'09:57:00')),
(1041,108,10,DATE_ADD(CURDATE(), INTERVAL 12 DAY),'13:00:00','17:00:00',4,280,280,100,'confirmada','transferencia',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'08:34:00'));

-- ── Comprobantes (transferencias) ──
INSERT INTO comprobantes (reserva_id,archivo,monto_pago,estado,notas,created_at) VALUES
(1023,'comp_1023_seed.jpg',450,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -72 DAY),'11:54:00')),
(1028,'comp_1028_seed.jpg',260,'rechazado','Cancelada por el cliente antes de pagar.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -60 DAY),'21:44:00')),
(1018,'comp_1018_seed.jpg',30,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -53 DAY),'17:19:00')),
(1017,'comp_1017_seed.jpg',120,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -52 DAY),'17:02:00')),
(1016,'comp_1016_seed.jpg',180,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -52 DAY),'14:24:00')),
(1015,'comp_1015_seed.jpg',140,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -47 DAY),'16:33:00')),
(1014,'comp_1014_seed.jpg',200,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -43 DAY),'20:41:00')),
(1013,'comp_1013_seed.jpg',300,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -41 DAY),'18:10:00')),
(1012,'comp_1012_seed.jpg',300,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -32 DAY),'16:06:00')),
(1011,'comp_1011_seed.jpg',110,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -28 DAY),'09:09:00')),
(1009,'comp_1009_seed.jpg',280,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -26 DAY),'19:24:00')),
(1006,'comp_1006_seed.jpg',450,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -17 DAY),'13:35:00')),
(1005,'comp_1005_seed.jpg',330,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -19 DAY),'12:19:00')),
(1004,'comp_1004_seed.jpg',130,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -14 DAY),'08:47:00')),
(1025,'comp_1025_seed.jpg',400,'rechazado','Cancelada por el cliente antes de pagar.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -10 DAY),'17:42:00')),
(1001,'comp_1001_seed.jpg',90,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -10 DAY),'20:12:00')),
(1029,'comp_1029_seed.jpg',800,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -4 DAY),'10:57:00')),
(1032,'comp_1032_seed.jpg',280,'pendiente','Comprobante recibido, pendiente de revisión.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -4 DAY),'12:18:00')),
(1031,'comp_1031_seed.jpg',300,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'16:18:00')),
(1033,'comp_1033_seed.jpg',200,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'13:08:00')),
(1035,'comp_1035_seed.jpg',90,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -3 DAY),'14:12:00')),
(1036,'comp_1036_seed.jpg',90,'pendiente','Comprobante recibido, pendiente de revisión.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -2 DAY),'19:40:00')),
(1038,'comp_1038_seed.jpg',450,'rechazado','El monto del comprobante no coincide con el total. Sube uno nuevo.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -3 DAY),'08:54:00')),
(1041,'comp_1041_seed.jpg',280,'aprobado','Pago verificado en el banco.',TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'17:41:00'));

-- ── Mensajes de chat (leido=0 → aparece la burbuja roja) ──
INSERT INTO mensajes (usuario_id,contenido,remitente,leido,created_at) VALUES
(102,'Hola, buenas tardes. Quiero reservar la Sala Galaxia para el sábado, ¿está disponible?','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -3 DAY),'16:00:00')),
(102,'¡Hola Carlos! Sí, la Sala Galaxia está libre. Puedes reservarla desde el menú «Reservar».','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -3 DAY),'16:15:00')),
(102,'Perfecto, ya reservé. ¿Puedo pagar el 50% por transferencia?','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -3 DAY),'16:55:00')),
(102,'Claro, elige el 50% y sube tu comprobante con el QR que aparece en pantalla.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -3 DAY),'17:26:00')),
(102,'Listo, ya subí el comprobante.','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -3 DAY),'17:55:00')),
(102,'Recibido, lo reviso y te confirmo en unos minutos.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -3 DAY),'18:04:00')),
(102,'Muchas gracias.','cliente',0,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -3 DAY),'18:29:00')),
(102,'¿Ya pudieron revisarlo?','cliente',0,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -3 DAY),'19:03:00')),
(102,'Es para el cumpleaños de mi hermana, por eso pregunto 🙂','cliente',0,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -3 DAY),'19:21:00')),
(103,'Buenas, ¿cuántas personas entran en la Sala Nebulosa?','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -4 DAY),'15:42:00')),
(103,'Hasta 12 personas. Tiene ambiente VIP y espacio amplio.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -4 DAY),'15:47:00')),
(103,'Genial. ¿Puedo llevar mi propia torta?','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -4 DAY),'16:16:00')),
(103,'Sí, no hay problema con la torta. Las bebidas y comidas del menú se piden en el local.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -4 DAY),'16:19:00')),
(103,'¡Excelente! Gracias.','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -4 DAY),'16:39:00')),
(103,'Con gusto. Cualquier duda nos escribes.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -4 DAY),'17:02:00')),
(103,'Una última consulta: ¿tienen parlantes con bluetooth?','cliente',0,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -4 DAY),'17:22:00')),
(104,'Hola, reservé para hoy en efectivo, ¿a qué hora debo llegar?','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'10:47:00')),
(104,'Hola Luis, puedes llegar 10 minutos antes de tu hora. El pago lo registramos al llegar.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'11:09:00')),
(104,'Entendido, gracias.','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'11:48:00')),
(104,'Recuerda que tu reserva de hoy está confirmada. ¡Te esperamos!','admin',0,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'11:51:00')),
(104,'Si necesitas cambiar algo, avísanos con tiempo.','admin',0,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'12:27:00')),
(105,'Buenas noches, quiero cancelar mi reserva del viernes.','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -2 DAY),'14:49:00')),
(105,'Hola Ana, sin problema. Puedes cancelarla desde «Mis Reservas» mientras esté pendiente.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -2 DAY),'14:54:00')),
(105,'Ya la cancelé, gracias. Quisiera reservar otro día.','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -2 DAY),'15:03:00')),
(105,'Perfecto, elige la nueva fecha en «Reservar». Revisa el horario disponible de la sala.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -2 DAY),'15:15:00')),
(105,'Voy a reservar el domingo por la tarde.','cliente',0,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -2 DAY),'15:24:00')),
(105,'¿La Sala Luna sigue disponible el domingo?','cliente',0,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -2 DAY),'15:33:00')),
(106,'Hola, ¿aceptan pago por QR de cualquier banco?','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -6 DAY),'09:43:00')),
(106,'Sí, el QR funciona con cualquier banco. Luego subes la captura como comprobante.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -6 DAY),'10:07:00')),
(106,'Perfecto. ¿Y si pago el 50% cuándo cancelo el resto?','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -6 DAY),'10:47:00')),
(106,'El saldo lo cancelas en el local el día de tu reserva.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -6 DAY),'11:08:00')),
(106,'Muy bien, gracias por la información.','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -6 DAY),'11:11:00')),
(106,'¡A ti! Que disfrutes tu sala.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -6 DAY),'11:40:00')),
(107,'Hola, reservé la Sala Estrella para hoy por transferencia.','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'18:32:00')),
(107,'Hola Valeria, tu comprobante fue aprobado y la reserva está confirmada.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'19:00:00')),
(107,'¡Genial! ¿Puedo pedir comida con anticipación?','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'19:26:00')),
(107,'Sí, puedes pedir del menú apenas llegues. También armamos combos para grupos.','admin',0,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'19:52:00')),
(107,'Cuéntanos cuántas personas serán y te preparamos una propuesta.','admin',0,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -1 DAY),'20:09:00')),
(108,'Buenas, tengo un problema: no me deja subir el comprobante.','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -5 DAY),'15:58:00')),
(108,'Hola Jorge, ¿qué formato tiene el archivo? Solo aceptamos JPG, PNG o PDF de hasta 5 MB.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -5 DAY),'16:08:00')),
(108,'Es una captura de pantalla, creo que pesa mucho.','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -5 DAY),'16:21:00')),
(108,'Prueba reduciendo el tamaño o enviándola como PDF.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -5 DAY),'16:57:00')),
(108,'Ya pude subirla, gracias.','cliente',0,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -5 DAY),'17:00:00')),
(108,'Una consulta más, ¿cuánto tarda la aprobación?','cliente',0,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -5 DAY),'17:36:00')),
(108,'Es que mi reserva es mañana temprano.','cliente',0,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -5 DAY),'18:13:00')),
(108,'Quedo atento a la respuesta.','cliente',0,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -5 DAY),'18:40:00')),
(109,'Hola, subí mi comprobante hoy, ¿cuándo lo revisan?','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -2 DAY),'14:01:00')),
(109,'Hola Camila, lo estamos revisando. Te avisamos apenas esté aprobado.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -2 DAY),'14:17:00')),
(109,'Gracias, quedo atenta.','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -2 DAY),'14:20:00')),
(109,'Ya quedó revisado, tu reserva figura como pendiente hasta la verificación final.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -2 DAY),'14:23:00')),
(109,'Ok, muchas gracias.','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -2 DAY),'14:42:00')),
(110,'Buenas tardes, ¿hacen descuentos por reservar varias horas?','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -0 DAY),'17:58:00')),
(110,'Hola Sebastián, por ahora el precio es por hora, pero para eventos especiales podemos coordinar.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -0 DAY),'18:16:00')),
(110,'Quiero reservar 4 horas para una reunión de la oficina, ¿podemos coordinar?','cliente',0,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -0 DAY),'18:38:00')),
(111,'Hola, ¿tienen sala disponible para 8 personas el próximo sábado?','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -7 DAY),'12:19:00')),
(111,'Hola Daniela, la Sala Void tiene capacidad para 8 y la Sala Horizon para 10. Revisa su disponibilidad en «Reservar».','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -7 DAY),'12:26:00')),
(111,'Perfecto, voy a mirar ahora.','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -7 DAY),'12:39:00')),
(111,'Si tienes dudas con el pago, estamos para ayudarte.','admin',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -7 DAY),'13:16:00')),
(111,'Todo claro, gracias.','cliente',1,TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL -7 DAY),'13:20:00'));
