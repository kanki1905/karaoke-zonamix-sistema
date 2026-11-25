-- Dar de baja / dar de alta usuarios (sin borrarlos de la base de datos).
-- Ejecutar UNA sola vez sobre la base ya creada (karaoke_db).
USE karaoke_db;
ALTER TABLE usuarios ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER foto;
