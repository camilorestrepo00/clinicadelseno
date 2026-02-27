-- Migration: Añadir columna archivo_incapacidad a la tabla incapacidades
ALTER TABLE incapacidades
ADD COLUMN archivo_incapacidad VARCHAR(255) NULL AFTER comentario_admin,
ADD COLUMN fecha_archivo TIMESTAMP NULL AFTER archivo_incapacidad;

-- Para ejecutar esta migración:
-- mysql -u usuario -p nombre_basedatos < add_archivo_incapacidades.sql
