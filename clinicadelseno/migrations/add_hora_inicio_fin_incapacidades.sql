-- Migration: Añadir columnas hora_inicio y hora_fin a la tabla incapacidades
ALTER TABLE incapacidades
  ADD COLUMN hora_inicio TIME NULL,
  ADD COLUMN hora_fin TIME NULL;

-- Ejecutar este archivo en la base de datos (por ejemplo desde phpMyAdmin o cliente MySQL):
-- mysql -u usuario -p nombre_basedatos < add_hora_inicio_fin_incapacidades.sql
