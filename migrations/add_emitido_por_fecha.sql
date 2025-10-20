-- Añadir columnas para tracking de emisión en la tabla certificados
ALTER TABLE certificados
ADD COLUMN emitido_por VARCHAR(100) NULL,
ADD COLUMN fecha_emision DATETIME NULL;
