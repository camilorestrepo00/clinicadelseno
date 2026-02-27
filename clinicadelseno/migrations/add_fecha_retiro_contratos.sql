-- Migration: Agregar columna fecha_retiro a la tabla contratos
ALTER TABLE contratos
  ADD COLUMN fecha_retiro DATE NULL;
