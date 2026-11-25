-- Para una base YA EXISTENTE: impide nombres repetidos de salas y productos.
-- 1) Busca duplicados actuales (si salen filas, renómbralos antes de seguir):
SELECT nombre, COUNT(*) AS veces FROM salas     GROUP BY nombre HAVING COUNT(*) > 1;
SELECT nombre, COUNT(*) AS veces FROM productos GROUP BY nombre HAVING COUNT(*) > 1;

-- 2) Cuando no haya duplicados, crea las restricciones:
ALTER TABLE salas     ADD UNIQUE KEY uq_salas_nombre (nombre);
ALTER TABLE productos ADD UNIQUE KEY uq_productos_nombre (nombre);
