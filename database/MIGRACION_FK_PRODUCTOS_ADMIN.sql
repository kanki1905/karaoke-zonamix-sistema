-- Para una base YA EXISTENTE (no borra datos). Ejecutar en phpMyAdmin sobre karaoke_db.
USE karaoke_db;

-- 1) La columna admin_id puede existir ya; si da error "Duplicate column", omite esta línea.
ALTER TABLE productos ADD COLUMN admin_id INT UNSIGNED DEFAULT NULL AFTER imagen;

-- 2) Limpia ids que no correspondan a ningún usuario (si no, la llave foránea falla).
UPDATE productos SET admin_id = NULL
WHERE admin_id IS NOT NULL AND admin_id NOT IN (SELECT id FROM usuarios);

-- 3) Productos sin dueño → administrador inicial.
UPDATE productos SET admin_id = (SELECT id FROM usuarios WHERE email='admin@karaoke.com' LIMIT 1)
WHERE admin_id IS NULL;

-- 4) La llave foránea.
ALTER TABLE productos
  ADD INDEX idx_productos_admin (admin_id),
  ADD CONSTRAINT fk_productos_admin FOREIGN KEY (admin_id)
      REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE;
