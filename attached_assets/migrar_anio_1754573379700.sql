USE cuaderno_digital_eest2;

-- 1. Migrar tabla cursos
CREATE TABLE cursos_tmp LIKE cursos;
ALTER TABLE cursos_tmp CHANGE COLUMN `año` anio INT NOT NULL;
INSERT INTO cursos_tmp SELECT id, `año`, division, turno_id, especialidad_id, taller_id, grado, activo FROM cursos;
DROP TABLE cursos;
RENAME TABLE cursos_tmp TO cursos;

-- 2. Migrar tabla materias_previas
CREATE TABLE materias_previas_tmp LIKE materias_previas;
ALTER TABLE materias_previas_tmp CHANGE COLUMN `año_previo` anio_previo INT NOT NULL;
INSERT INTO materias_previas_tmp SELECT id, estudiante_id, materia_id, `año_previo`, estado, observaciones, fecha_registro FROM materias_previas;
DROP TABLE materias_previas;
RENAME TABLE materias_previas_tmp TO materias_previas;

-- Verificar
DESCRIBE cursos;
DESCRIBE materias_previas; 