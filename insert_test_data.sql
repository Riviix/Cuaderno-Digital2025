USE cuaderno_digital_eest2;

-- Insertar materias de prueba
INSERT INTO materias (nombre, descripcion, especialidad_id, es_taller, activa) VALUES 
('Matemática', 'Matemática general', NULL, FALSE, TRUE),
('Lengua y Literatura', 'Lengua y Literatura', NULL, FALSE, TRUE),
('Historia', 'Historia Argentina y Mundial', NULL, FALSE, TRUE),
('Geografía', 'Geografía Argentina y Mundial', NULL, FALSE, TRUE),
('Física', 'Física general', NULL, FALSE, TRUE),
('Química', 'Química general', NULL, FALSE, TRUE),
('Biología', 'Biología general', NULL, FALSE, TRUE),
('Educación Física', 'Educación Física', NULL, FALSE, TRUE),
('Inglés', 'Inglés técnico', NULL, FALSE, TRUE),
('Construcciones', 'Taller de Construcciones', 8, TRUE, TRUE),
('Electrónica', 'Taller de Electrónica', 9, TRUE, TRUE),
('Programación', 'Taller de Programación', 10, TRUE, TRUE);

-- Crear algunos cursos de prueba
INSERT INTO cursos (anio, division, turno_id, especialidad_id, grado, activo) VALUES 
(1, 'A', 1, 8, 'inferior', TRUE),  -- 1°A Construcciones Mañana
(1, 'B', 1, 9, 'inferior', TRUE),  -- 1°B Electrónica Mañana
(2, 'A', 1, 8, 'inferior', TRUE),  -- 2°A Construcciones Mañana
(2, 'B', 1, 9, 'inferior', TRUE),  -- 2°B Electrónica Mañana
(3, 'A', 1, 8, 'inferior', TRUE),  -- 3°A Construcciones Mañana
(4, 'A', 2, 8, 'superior', TRUE),  -- 4°A Construcciones Tarde
(4, 'B', 2, 9, 'superior', TRUE),  -- 4°B Electrónica Tarde
(5, 'A', 2, 8, 'superior', TRUE),  -- 5°A Construcciones Tarde
(5, 'B', 2, 9, 'superior', TRUE),  -- 5°B Electrónica Tarde
(6, 'A', 2, 8, 'superior', TRUE),  -- 6°A Construcciones Tarde
(7, 'A', 2, 8, 'superior', TRUE);  -- 7°A Construcciones Tarde

-- Insertar algunos estudiantes de prueba
INSERT INTO estudiantes (dni, apellido, nombre, fecha_nacimiento, curso_id, activo) VALUES 
('12345678', 'García', 'Juan Carlos', '2005-03-15', 1, TRUE),
('23456789', 'López', 'María Elena', '2005-07-22', 1, TRUE),
('34567890', 'Rodríguez', 'Carlos Alberto', '2005-01-10', 1, TRUE),
('45678901', 'Martínez', 'Ana Sofía', '2005-11-05', 2, TRUE),
('56789012', 'González', 'Luis Miguel', '2005-05-18', 2, TRUE),
('67890123', 'Pérez', 'Carmen Rosa', '2005-09-30', 2, TRUE),
('78901234', 'Sánchez', 'Roberto Daniel', '2005-12-12', 3, TRUE),
('89012345', 'Fernández', 'Patricia Beatriz', '2005-04-25', 3, TRUE),
('90123456', 'Ramírez', 'Diego Alejandro', '2005-08-08', 3, TRUE),
('01234567', 'Torres', 'Valentina María', '2005-06-14', 4, TRUE);

-- Insertar algunos horarios de prueba
INSERT INTO horarios (curso_id, materia_id, dia, hora_inicio, hora_fin, aula, docente, usuario_id) VALUES 
(1, 1, 'Lunes', '07:30:00', '08:30:00', 'Aula 1', 'Prof. González', 1),
(1, 2, 'Lunes', '08:30:00', '09:30:00', 'Aula 1', 'Prof. Martínez', 1),
(1, 3, 'Lunes', '09:30:00', '10:30:00', 'Aula 1', 'Prof. López', 1),
(1, 1, 'Martes', '07:30:00', '08:30:00', 'Aula 1', 'Prof. González', 1),
(1, 2, 'Martes', '08:30:00', '09:30:00', 'Aula 1', 'Prof. Martínez', 1),
(2, 1, 'Lunes', '13:30:00', '14:30:00', 'Aula 2', 'Prof. Rodríguez', 1),
(2, 2, 'Lunes', '14:30:00', '15:30:00', 'Aula 2', 'Prof. Sánchez', 1),
(2, 3, 'Lunes', '15:30:00', '16:30:00', 'Aula 2', 'Prof. Fernández', 1);

-- Insertar algunas inasistencias de prueba
INSERT INTO inasistencias (estudiante_id, fecha, tipo, justificada, motivo, certificado_medico, observaciones, usuario_id) VALUES 
(1, CURDATE(), 'completa', FALSE, 'Falta sin justificar', FALSE, 'Estudiante no asistió', 1),
(2, CURDATE(), 'tarde', TRUE, 'Problemas de transporte', FALSE, 'Llegó 15 minutos tarde', 1),
(3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'completa', TRUE, 'Enfermedad', TRUE, 'Con certificado médico', 1),
(4, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'retiro_anticipado', FALSE, 'Retiro sin autorización', FALSE, 'Se retiró sin avisar', 1);

-- Insertar algunos llamados de atención de prueba
INSERT INTO llamados_atencion (estudiante_id, fecha, tipo, motivo, sancion, observaciones, usuario_id) VALUES 
(1, CURDATE(), 'leve', 'Uso de celular en clase', 'Amonestación verbal', 'Primera advertencia', 1),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'moderado', 'Falta de respeto a docente', 'Amonestación escrita', 'Segunda advertencia', 1),
(3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'grave', 'Agresión verbal a compañero', 'Suspensión 1 día', 'Incidente grave', 1),
(4, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'leve', 'Falta de material', 'Sin sanción', 'Olvidó traer el material', 1);

-- Verificar los datos insertados
SELECT 'Estudiantes' as tabla, COUNT(*) as cantidad FROM estudiantes
UNION ALL
SELECT 'Horarios', COUNT(*) FROM horarios
UNION ALL
SELECT 'Inasistencias', COUNT(*) FROM inasistencias
UNION ALL
SELECT 'Llamados', COUNT(*) FROM llamados_atencion
UNION ALL
SELECT 'Materias', COUNT(*) FROM materias
UNION ALL
SELECT 'Cursos', COUNT(*) FROM cursos; 