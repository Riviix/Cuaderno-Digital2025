USE cuaderno_digital_eest2;

-- Insertar materias de prueba
INSERT INTO materias (nombre, especialidad_id, es_taller) VALUES 
('Matematica', NULL, FALSE),
('Lengua y Literatura', NULL, FALSE),
('Historia', NULL, FALSE),
('Geografia', NULL, FALSE),
('Fisica', NULL, FALSE),
('Quimica', NULL, FALSE),
('Biologia', NULL, FALSE),
('Educacion Fisica', NULL, FALSE),
('Ingles', NULL, FALSE),
('Construcciones', 3, TRUE),
('Electronica', 2, TRUE),
('Programacion', 1, TRUE);

-- Crear algunos cursos de prueba
INSERT INTO cursos (anio, division, turno_id, especialidad_id, grado, activo) VALUES 
(1, 'A', 1, 3, 'inferior', TRUE),  -- 1°A Construcciones Manana
(1, 'B', 1, 2, 'inferior', TRUE),  -- 1°B Electronica Manana
(2, 'A', 1, 3, 'inferior', TRUE),  -- 2°A Construcciones Manana
(2, 'B', 1, 2, 'inferior', TRUE),  -- 2°B Electronica Manana
(3, 'A', 1, 3, 'inferior', TRUE),  -- 3°A Construcciones Manana
(4, 'A', 2, 3, 'superior', TRUE),  -- 4°A Construcciones Tarde
(4, 'B', 2, 2, 'superior', TRUE),  -- 4°B Electronica Tarde
(5, 'A', 2, 3, 'superior', TRUE),  -- 5°A Construcciones Tarde
(5, 'B', 2, 2, 'superior', TRUE),  -- 5°B Electronica Tarde
(6, 'A', 2, 3, 'superior', TRUE),  -- 6°A Construcciones Tarde
(7, 'A', 2, 3, 'superior', TRUE);  -- 7°A Construcciones Tarde

-- Insertar algunos estudiantes de prueba
INSERT INTO estudiantes (dni, apellido, nombre, fecha_nacimiento, curso_id, activo) VALUES 
('12345678', 'Garcia', 'Juan Carlos', '2005-03-15', 1, TRUE),
('23456789', 'Lopez', 'Maria Elena', '2005-07-22', 1, TRUE),
('34567890', 'Rodriguez', 'Carlos Alberto', '2005-01-10', 1, TRUE),
('45678901', 'Martinez', 'Ana Sofia', '2005-11-05', 2, TRUE),
('56789012', 'Gonzalez', 'Luis Miguel', '2005-05-18', 2, TRUE),
('67890123', 'Perez', 'Carmen Rosa', '2005-09-30', 2, TRUE),
('78901234', 'Sanchez', 'Roberto Daniel', '2005-12-12', 3, TRUE),
('89012345', 'Fernandez', 'Patricia Beatriz', '2005-04-25', 3, TRUE),
('90123456', 'Ramirez', 'Diego Alejandro', '2005-08-08', 3, TRUE),
('01234567', 'Torres', 'Valentina Maria', '2005-06-14', 4, TRUE);

-- Insertar algunos horarios de prueba
INSERT INTO horarios (curso_id, materia_id, dia_semana, hora_inicio, hora_fin, aula, docente) VALUES 
(1, 1, 1, '07:30:00', '08:30:00', 'Aula 1', 'Prof. Gonzalez'),
(1, 2, 1, '08:30:00', '09:30:00', 'Aula 1', 'Prof. Martinez'),
(1, 3, 1, '09:30:00', '10:30:00', 'Aula 1', 'Prof. Lopez'),
(1, 1, 2, '07:30:00', '08:30:00', 'Aula 1', 'Prof. Gonzalez'),
(1, 2, 2, '08:30:00', '09:30:00', 'Aula 1', 'Prof. Martinez'),
(2, 1, 1, '13:30:00', '14:30:00', 'Aula 2', 'Prof. Rodriguez'),
(2, 2, 1, '14:30:00', '15:30:00', 'Aula 2', 'Prof. Sanchez'),
(2, 3, 1, '15:30:00', '16:30:00', 'Aula 2', 'Prof. Fernandez');

-- Insertar algunas inasistencias de prueba
INSERT INTO inasistencias (estudiante_id, fecha, tipo, justificada, motivo, certificado_medico, observaciones, usuario_id) VALUES 
(1, CURDATE(), 'completa', FALSE, 'Falta sin justificar', FALSE, 'Estudiante no asistio', 1),
(2, CURDATE(), 'tarde', TRUE, 'Problemas de transporte', FALSE, 'Llego 15 minutos tarde', 1),
(3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'completa', TRUE, 'Enfermedad', TRUE, 'Con certificado medico', 1),
(4, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'retiro_anticipado', FALSE, 'Retiro sin autorizacion', FALSE, 'Se retiro sin avisar', 1);

-- Insertar algunos llamados de atencion de prueba
INSERT INTO llamados_atencion (estudiante_id, fecha, motivo, sancion, observaciones, usuario_id) VALUES 
(1, CURDATE(), 'Uso de celular en clase', 'Amonestacion verbal', 'Primera advertencia', 1),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Falta de respeto a docente', 'Amonestacion escrita', 'Segunda advertencia', 1),
(3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Agresion verbal a companero', 'Suspension 1 dia', 'Incidente grave', 1),
(4, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Falta de material', 'Sin sancion', 'Olvido traer el material', 1);

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