-- Para una base YA EXISTENTE: los precios solo pueden ser enteros o terminar en .5
USE karaoke_db;
-- 1) Ajusta al medio más cercano los precios actuales que no cumplan (ej. 10.25 -> 10.5)
UPDATE productos SET precio = GREATEST(0.5, ROUND(precio * 2) / 2);
-- 2) Quita la restricción anterior (si da error porque no existe, ignóralo) y crea la nueva
ALTER TABLE productos DROP CHECK chk_productos_precio;
ALTER TABLE productos ADD CONSTRAINT chk_productos_precio CHECK (precio >= 0.5 AND (precio * 2) = FLOOR(precio * 2));
