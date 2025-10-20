-- Crear tabla certificados para solicitudes de certificado de empleados
-- Ejecuta este archivo en tu servidor MySQL (phpMyAdmin o consola) después de hacer backup.

CREATE TABLE IF NOT EXISTS certificados (
  id INT AUTO_INCREMENT PRIMARY KEY,
  numero_cedula VARCHAR(50) NOT NULL,
  tipo_certificado VARCHAR(100) NOT NULL,
  motivo TEXT NULL,
  archivo VARCHAR(255) NULL,
  creado_por VARCHAR(100) NULL,
  fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
  estado VARCHAR(50) NOT NULL DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- MIGRACIÓN OPCIONAL: mover registros de solicitudes tipo 'Certificado' a la nueva tabla.
-- Haz un BACKUP antes de ejecutar. Ajusta campos si tu esquema difiere.

-- 1) Insertar en certificados desde solicitudes (evita duplicados por matching simple)
INSERT INTO certificados (numero_cedula, tipo_certificado, motivo, archivo, creado_por, fecha_solicitud, estado)
SELECT s.numero_cedula,
       COALESCE(s.tipo_certificado, 'Laboral') AS tipo_certificado,
       s.motivo,
       s.archivo,
       s.numero_cedula AS creado_por,
       COALESCE(s.fecha_solicitud, NOW()) AS fecha_solicitud,
       COALESCE(s.estado, 'Pendiente') AS estado
FROM solicitudes s
LEFT JOIN certificados c ON c.numero_cedula = s.numero_cedula AND c.fecha_solicitud = s.fecha_solicitud
WHERE s.tipo_solicitud = 'Certificado' AND c.id IS NULL;

-- 2) (Opcional) Verifica los datos en certificados y si todo está OK puedes eliminar las entradas de solicitudes:
-- DELETE FROM solicitudes WHERE tipo_solicitud = 'Certificado';
