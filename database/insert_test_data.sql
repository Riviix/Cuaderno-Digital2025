USE cuaderno_digital_eest2;

-- Insertar materias de prueba adicionales
INSERT INTO materias (nombre, especialidad_id, es_taller) VALUES 
('Matemática', NULL, FALSE),
('Lengua y Literatura', NULL, FALSE),
('Historia', NULL, FALSE),
('Geografía', NULL, FALSE),
('Física', NULL, FALSE),
('Química', NULL, FALSE),
('Biología', NULL, FALSE),
('Educación Física', NULL, FALSE),
('Inglés', NULL, FALSE),
('Formación Ética y Ciudadana', NULL, FALSE),
('Educación Artística', NULL, FALSE),
('Tecnología', NULL, FALSE),
-- Materias específicas por especialidad
('Construcciones I', 3, TRUE),
('Construcciones II', 3, TRUE),
('Construcciones III', 3, TRUE),
('Electrónica I', 2, TRUE),
('Electrónica II', 2, TRUE),
('Electrónica III', 2, TRUE),
('Programación I', 1, TRUE),
('Programación II', 1, TRUE),
('Programación III', 1, TRUE),
('Base de Datos', 1, TRUE),
('Redes', 1, TRUE),
('Laboratorio de Química I', 4, TRUE),
('Laboratorio de Química II', 4, TRUE),
('Procesos Químicos', 4, TRUE),
('Mecánica', 2, TRUE),
('Electricidad', 2, TRUE),
('Instalaciones', 2, TRUE)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Crear algunos cursos de prueba
INSERT INTO cursos (anio, division, turno_id, especialidad_id, grado, activo) VALUES 
(1, 'A', 1, 3, 'inferior', TRUE),  -- 1°A Construcciones Mañana
(1, 'B', 1, 2, 'inferior', TRUE),  -- 1°B Electromecánica Mañana
(1, 'C', 1, 1, 'inferior', TRUE),  -- 1°C Informática Mañana
(2, 'A', 1, 3, 'inferior', TRUE),  -- 2°A Construcciones Mañana
(2, 'B', 1, 2, 'inferior', TRUE),  -- 2°B Electromecánica Mañana
(2, 'C', 1, 1, 'inferior', TRUE),  -- 2°C Informática Mañana
(3, 'A', 1, 3, 'inferior', TRUE),  -- 3°A Construcciones Mañana
(3, 'B', 1, 2, 'inferior', TRUE),  -- 3°B Electromecánica Mañana
(4, 'A', 2, 3, 'superior', TRUE),  -- 4°A Construcciones Tarde
(4, 'B', 2, 2, 'superior', TRUE),  -- 4°B Electromecánica Tarde
(4, 'C', 2, 1, 'superior', TRUE),  -- 4°C Informática Tarde
(5, 'A', 2, 3, 'superior', TRUE),  -- 5°A Construcciones Tarde
(5, 'B', 2, 2, 'superior', TRUE),  -- 5°B Electromecánica Tarde
(5, 'C', 2, 1, 'superior', TRUE),  -- 5°C Informática Tarde
(6, 'A', 2, 3, 'superior', TRUE),  -- 6°A Construcciones Tarde
(6, 'B', 2, 2, 'superior', TRUE),  -- 6°B Electromecánica Tarde
(7, 'A', 2, 3, 'superior', TRUE)   -- 7°A Construcciones Tarde
ON DUPLICATE KEY UPDATE anio = VALUES(anio);

-- Insertar usuarios adicionales
INSERT INTO usuarios (username, password, nombre, apellido, email, rol, activo) VALUES 
('director', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos', 'Mendoza', 'director@eest2.edu.ar', 'directivo', TRUE),
('preceptor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María', 'González', 'preceptor1@eest2.edu.ar', 'preceptor', TRUE),
('preceptor2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan', 'Pérez', 'preceptor2@eest2.edu.ar', 'preceptor', TRUE),
('secretaria', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana', 'Rodriguez', 'secretaria@eest2.edu.ar', 'secretaria', TRUE)
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- Insertar equipo directivo
INSERT INTO equipo_directivo (apellido, nombre, cargo, telefono, email, activo) VALUES 
('Mendoza', 'Carlos Alberto', 'Director', '223-456-7890', 'director@eest2.edu.ar', TRUE),
('López', 'María Elena', 'Vicedirectora', '223-456-7891', 'vicedirectora@eest2.edu.ar', TRUE),
('Rodriguez', 'Ana Sofía', 'Secretaria', '223-456-7892', 'secretaria@eest2.edu.ar', TRUE),
('González', 'Roberto Daniel', 'Regente', '223-456-7893', 'regente@eest2.edu.ar', TRUE),
('Fernández', 'Patricia Beatriz', 'Jefe de Taller', '223-456-7894', 'taller@eest2.edu.ar', TRUE),
('Torres', 'Miguel Ángel', 'Coordinador de Área', '223-456-7895', 'coordinador@eest2.edu.ar', TRUE)
ON DUPLICATE KEY UPDATE apellido = VALUES(apellido);

-- Insertar estudiantes de prueba más realistas
INSERT INTO estudiantes (dni, apellido, nombre, fecha_nacimiento, grupo_sanguineo, obra_social, domicilio, telefono_fijo, telefono_celular, email, curso_id, activo, fecha_ingreso) VALUES 
-- 1°A Construcciones
('45123456', 'García', 'Juan Carlos', '2008-03-15', 'O+', 'OSDE', 'Av. Libertad 1234, Mar del Plata', '223-4567890', '223-15-123456', 'juan.garcia@email.com', 1, TRUE, '2024-03-01'),
('45234567', 'López', 'María Elena', '2008-07-22', 'A+', 'Swiss Medical', 'Calle San Juan 567, Mar del Plata', NULL, '223-15-234567', 'maria.lopez@email.com', 1, TRUE, '2024-03-01'),
('45345678', 'Rodriguez', 'Carlos Alberto', '2008-01-10', 'B+', 'IOMA', 'Av. Constitución 890, Mar del Plata', '223-3456789', '223-15-345678', NULL, 1, TRUE, '2024-03-01'),
('45456789', 'Martinez', 'Ana Sofía', '2008-11-05', 'AB+', 'OSECAC', 'Calle Corrientes 234, Mar del Plata', NULL, '223-15-456789', 'ana.martinez@email.com', 1, TRUE, '2024-03-01'),
('45567890', 'González', 'Luis Miguel', '2008-05-18', 'O-', 'PAMI', 'Av. Independencia 567, Mar del Plata', '223-2345678', '223-15-567890', NULL, 1, TRUE, '2024-03-01'),

-- 1°B Electromecánica
('45678901', 'Pérez', 'Carmen Rosa', '2008-09-30', 'A-', 'OSDE', 'Calle Mitre 345, Mar del Plata', NULL, '223-15-678901', 'carmen.perez@email.com', 2, TRUE, '2024-03-01'),
('45789012', 'Sánchez', 'Roberto Daniel', '2008-12-12', 'B-', 'Swiss Medical', 'Av. Colón 678, Mar del Plata', '223-1234567', '223-15-789012', NULL, 2, TRUE, '2024-03-01'),
('45890123', 'Fernández', 'Patricia Beatriz', '2008-04-25', 'O+', 'IOMA', 'Calle Alsina 123, Mar del Plata', NULL, '223-15-890123', 'patricia.fernandez@email.com', 2, TRUE, '2024-03-01'),
('45901234', 'Ramírez', 'Diego Alejandro', '2008-08-08', 'A+', 'OSECAC', 'Av. Luro 456, Mar del Plata', '223-9876543', '223-15-901234', NULL, 2, TRUE, '2024-03-01'),

-- 1°C Informática
('46012345', 'Torres', 'Valentina María', '2008-06-14', 'B+', 'OSDE', 'Calle Belgrano 789, Mar del Plata', NULL, '223-15-012345', 'valentina.torres@email.com', 3, TRUE, '2024-03-01'),
('46123456', 'Morales', 'Franco Nicolás', '2008-02-28', 'O-', 'Swiss Medical', 'Av. Juana de Arco 234, Mar del Plata', '223-8765432', '223-15-123456', NULL, 3, TRUE, '2024-03-01'),
('46234567', 'Vargas', 'Camila Soledad', '2008-10-17', 'A-', 'IOMA', 'Calle Rivadavia 567, Mar del Plata', NULL, '223-15-234567', 'camila.vargas@email.com', 3, TRUE, '2024-03-01'),

-- 2°A Construcciones
('44123456', 'Silva', 'Mateo Sebastián', '2007-05-03', 'AB-', 'OSECAC', 'Av. Martínez de Hoz 890, Mar del Plata', '223-7654321', '223-15-345678', NULL, 4, TRUE, '2023-03-01'),
('44234567', 'Romero', 'Sofía Guadalupe', '2007-09-19', 'O+', 'OSDE', 'Calle Güemes 123, Mar del Plata', NULL, '223-15-456789', 'sofia.romero@email.com', 4, TRUE, '2023-03-01'),
('44345678', 'Herrera', 'Joaquín Emanuel', '2007-01-26', 'A+', 'Swiss Medical', 'Av. Champagnat 456, Mar del Plata', '223-6543210', '223-15-567890', NULL, 4, TRUE, '2023-03-01'),

-- Estudiantes de años superiores
('42123456', 'Acosta', 'Lucas Tomás', '2005-04-12', 'B+', 'IOMA', 'Calle Catamarca 789, Mar del Plata', NULL, '223-15-678901', 'lucas.acosta@email.com', 9, TRUE, '2021-03-01'),
('42234567', 'Mendoza', 'Agustina Belén', '2005-08-29', 'O-', 'OSECAC', 'Av. Fleming 234, Mar del Plata', '223-5432109', '223-15-789012', NULL, 9, TRUE, '2021-03-01'),
('42345678', 'Castro', 'Nicolás Andrés', '2005-12-05', 'A-', 'OSDE', 'Calle Falucho 567, Mar del Plata', NULL, '223-15-890123', 'nicolas.castro@email.com', 10, TRUE, '2021-03-01'),

-- 5°C Informática
('41123456', 'Vega', 'Martina Celeste', '2004-03-20', 'AB+', 'Swiss Medical', 'Av. Tejedor 890, Mar del Plata', '223-4321098', '223-15-901234', 'martina.vega@email.com', 14, TRUE, '2020-03-01'),
('41234567', 'Ruiz', 'Santiago Gabriel', '2004-07-07', 'O+', 'IOMA', 'Calle Moreno 123, Mar del Plata', NULL, '223-15-012345', NULL, 14, TRUE, '2020-03-01'),
('41345678', 'Giménez', 'Florencia Abril', '2004-11-23', 'B-', 'OSECAC', 'Av. Constitución 456, Mar del Plata', '223-3210987', '223-15-123456', 'florencia.gimenez@email.com', 14, TRUE, '2020-03-01')
ON DUPLICATE KEY UPDATE dni = VALUES(dni);

-- Insertar responsables para algunos estudiantes
INSERT INTO responsables (estudiante_id, apellido, nombre, dni, parentesco, telefono_fijo, telefono_celular, email, domicilio, ocupacion, es_contacto_emergencia) VALUES 
(1, 'García', 'Roberto Carlos', '25123456', 'Padre', '223-4567890', '223-15-987654', 'roberto.garcia@email.com', 'Av. Libertad 1234, Mar del Plata', 'Ingeniero Civil', TRUE),
(1, 'García', 'Sandra Mónica', '27234567', 'Madre', '223-4567890', '223-15-876543', 'sandra.garcia@email.com', 'Av. Libertad 1234, Mar del Plata', 'Docente', TRUE),
(2, 'López', 'Miguel Ángel', '24345678', 'Padre', NULL, '223-15-765432', 'miguel.lopez@email.com', 'Calle San Juan 567, Mar del Plata', 'Comerciante', TRUE),
(3, 'Rodriguez', 'Patricia Elena', '26456789', 'Madre', '223-3456789', '223-15-654321', NULL, 'Av. Constitución 890, Mar del Plata', 'Enfermera', TRUE),
(4, 'Martinez', 'José Luis', '25567890', 'Padre', NULL, '223-15-543210', 'jose.martinez@email.com', 'Calle Corrientes 234, Mar del Plata', 'Electricista', TRUE),
(5, 'González', 'María del Carmen', '28678901', 'Madre', '223-2345678', '223-15-432109', NULL, 'Av. Independencia 567, Mar del Plata', 'Administrativa', TRUE)
ON DUPLICATE KEY UPDATE estudiante_id = VALUES(estudiante_id);

-- Insertar contactos de emergencia adicionales
INSERT INTO contactos_emergencia (estudiante_id, nombre, telefono, parentesco) VALUES 
(1, 'García, Julio César', '223-15-321098', 'Abuelo'),
(2, 'López, Rosa María', '223-15-210987', 'Abuela'),
(3, 'Rodriguez, Carlos Alberto', '223-15-109876', 'Tío'),
(4, 'Martinez, Elena Beatriz', '223-15-098765', 'Tía'),
(5, 'González, Pedro Antonio', '223-15-987654', 'Hermano mayor')
ON DUPLICATE KEY UPDATE estudiante_id = VALUES(estudiante_id);

-- Insertar algunos horarios de prueba
INSERT INTO horarios (curso_id, materia_id, dia_semana, hora_inicio, hora_fin, aula, docente, es_contraturno, activo) VALUES 
-- 1°A Construcciones - Lunes
(1, 1, 1, '07:30:00', '08:20:00', 'Aula 101', 'Prof. González, María', FALSE, TRUE),
(1, 2, 1, '08:20:00', '09:10:00', 'Aula 101', 'Prof. Martínez, Carlos', FALSE, TRUE),
(1, 3, 1, '09:10:00', '10:00:00', 'Aula 101', 'Prof. López, Ana', FALSE, TRUE),
(1, 4, 1, '10:20:00', '11:10:00', 'Aula 101', 'Prof. Sánchez, Roberto', FALSE, TRUE),
(1, 5, 1, '11:10:00', '12:00:00', 'Lab. Física', 'Prof. Fernández, Patricia', FALSE, TRUE),

-- 1°A Construcciones - Martes
(1, 6, 2, '07:30:00', '08:20:00', 'Lab. Química', 'Prof. Torres, Miguel', FALSE, TRUE),
(1, 7, 2, '08:20:00', '09:10:00', 'Aula 101', 'Prof. Ramírez, Elena', FALSE, TRUE),
(1, 8, 2, '09:10:00', '10:00:00', 'Gimnasio', 'Prof. Morales, Diego', FALSE, TRUE),
(1, 9, 2, '10:20:00', '11:10:00', 'Aula 102', 'Prof. Silva, Carmen', FALSE, TRUE),
(1, 13, 2, '11:10:00', '12:00:00', 'Taller Construcciones', 'Maestro Herrera, Juan', FALSE, TRUE),

-- 1°B Electromecánica - Lunes
(2, 1, 1, '07:30:00', '08:20:00', 'Aula 201', 'Prof. González, María', FALSE, TRUE),
(2, 2, 1, '08:20:00', '09:10:00', 'Aula 201', 'Prof. Martínez, Carlos', FALSE, TRUE),
(2, 16, 1, '09:10:00', '10:00:00', 'Taller Electrónica', 'Maestro Acosta, Luis', FALSE, TRUE),
(2, 5, 1, '10:20:00', '11:10:00', 'Lab. Física', 'Prof. Fernández, Patricia', FALSE, TRUE),
(2, 26, 1, '11:10:00', '12:00:00', 'Taller Mecánica', 'Maestro Vega, Roberto', FALSE, TRUE),

-- 4°A Construcciones - Tarde
(9, 1, 1, '13:30:00', '14:20:00', 'Aula 301', 'Prof. Castro, Nicolás', FALSE, TRUE),
(9, 14, 1, '14:20:00', '15:10:00', 'Taller Construcciones', 'Maestro Herrera, Juan', FALSE, TRUE),
(9, 15, 1, '15:10:00', '16:00:00', 'Taller Construcciones', 'Maestro Herrera, Juan', FALSE, TRUE),
(9, 5, 1, '16:20:00', '17:10:00', 'Lab. Física', 'Prof. Ruiz, Santiago', FALSE, TRUE),

-- Contraturno - Programación para 5°C
(14, 19, 1, '18:30:00', '20:00:00', 'Lab. Informática 1', 'Prof. Giménez, Florencia', TRUE, TRUE),
(14, 21, 2, '18:30:00', '20:00:00', 'Lab. Informática 2', 'Prof. Mendoza, Agustina', TRUE, TRUE),
(14, 22, 3, '18:30:00', '20:00:00', 'Lab. Informática 1', 'Prof. Vargas, Camila', TRUE, TRUE)
ON DUPLICATE KEY UPDATE curso_id = VALUES(curso_id);

-- Insertar algunas inasistencias de prueba
INSERT INTO inasistencias (estudiante_id, fecha, tipo, justificada, motivo, certificado_medico, observaciones, usuario_id) VALUES 
(1, CURDATE(), 'completa', FALSE, 'Falta sin justificar', FALSE, 'Estudiante no asistió a clases', 1),
(2, CURDATE(), 'tarde', TRUE, 'Problemas de transporte público', FALSE, 'Llegó 15 minutos tarde por demora del colectivo', 1),
(3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'completa', TRUE, 'Enfermedad - Gripe', TRUE, 'Presentó certificado médico', 1),
(4, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'retiro_anticipado', FALSE, 'Retiro sin autorización', FALSE, 'Se retiró a las 10:30 sin comunicar al preceptor', 1),
(5, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'completa', TRUE, 'Cita médica programada', FALSE, 'Cita con traumatólogo autorizada por dirección', 2),
(1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'tarde', FALSE, 'Llegada tarde reiterada', FALSE, 'Tercera llegada tarde en la semana', 1),
(6, DATE_SUB(CURDATE(), INTERVAL 1 WEEK), 'completa', TRUE, 'Enfermedad familiar', FALSE, 'Cuidado de familiar enfermo', 2),
(7, DATE_SUB(CURDATE(), INTERVAL 1 WEEK), 'retiro_anticipado', TRUE, 'Emergencia familiar', FALSE, 'Autorizado por vicedirección', 2),
(8, DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'completa', FALSE, 'Falta injustificada', FALSE, 'No se presentó sin aviso', 1),
(9, DATE_SUB(CURDATE(), INTERVAL 2 WEEK), 'tarde', TRUE, 'Cita odontológica', FALSE, 'Cita programada con autorización', 1)
ON DUPLICATE KEY UPDATE estudiante_id = VALUES(estudiante_id);

-- Insertar algunos llamados de atención de prueba
INSERT INTO llamados_atencion (estudiante_id, fecha, motivo, sancion, observaciones, usuario_id) VALUES 
(1, CURDATE(), 'Uso inadecuado de dispositivos', 'Amonestación verbal', 'Uso del celular durante la clase de matemática', 1),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Falta de respeto al docente', 'Amonestación escrita', 'Contestó de mala manera al profesor de física', 1),
(3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Agresión verbal a compañero', 'Suspensión 1 día', 'Insultos a compañero durante el recreo', 1),
(4, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Falta de material escolar', '', 'Olvidó traer el material para taller por tercera vez', 1),
(5, DATE_SUB(CURDATE(), INTERVAL 1 WEEK), 'Conducta inadecuada en clase', 'Amonestación verbal', 'Interrumpió reiteradamente la clase de historia', 2),
(6, DATE_SUB(CURDATE(), INTERVAL 1 WEEK), 'No cumplir con tareas', 'Citación a padres', 'No entregó trabajos prácticos por segunda semana consecutiva', 2),
(7, DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'Abandono del aula sin autorización', 'Amonestación escrita', 'Se retiró del aula sin permiso durante la clase', 1),
(8, DATE_SUB(CURDATE(), INTERVAL 2 WEEK), 'Falta de respeto al docente', 'Suspensión 3 días', 'Falta de respeto grave durante evaluación', 1),
(9, DATE_SUB(CURDATE(), INTERVAL 3 WEEK), 'Vandalismo', 'Derivación a gabinete', 'Daño intencional a mobiliario del aula', 2),
(10, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), 'Agresión física', 'Suspensión 5 días', 'Incidente grave en el patio durante el recreo', 1)
ON DUPLICATE KEY UPDATE estudiante_id = VALUES(estudiante_id);

-- Insertar algunos eventos escolares
INSERT INTO eventos (titulo, descripcion, fecha_inicio, fecha_fin, hora_inicio, hora_fin, lugar, tipo, publico, usuario_id) VALUES 
('Acto del Día de la Bandera', 'Celebración del Día de la Bandera con participación de todos los cursos', CURDATE() + INTERVAL 30 DAY, NULL, '09:00:00', '11:00:00', 'Patio Principal', 'institucional', TRUE, 1),
('Feria de Ciencias', 'Exposición de proyectos científicos de los estudiantes', CURDATE() + INTERVAL 45 DAY, CURDATE() + INTERVAL 47 DAY, '14:00:00', '18:00:00', 'Aulas y Laboratorios', 'academico', TRUE, 1),
('Torneo Intercolegial de Fútbol', 'Participación en torneo deportivo zonal', CURDATE() + INTERVAL 15 DAY, NULL, '15:00:00', '17:00:00', 'Polideportivo Municipal', 'deportivo', TRUE, 2),
('Muestra de Talleres', 'Exposición de trabajos realizados en los talleres técnicos', CURDATE() + INTERVAL 60 DAY, NULL, '10:00:00', '16:00:00', 'Talleres y Laboratorios', 'academico', TRUE, 1),
('Reunión de Padres 1°', 'Reunión informativa para padres de 1° año', CURDATE() + INTERVAL 20 DAY, NULL, '19:00:00', '21:00:00', 'Aula Magna', 'institucional', FALSE, 2),
('Festival de Talentos', 'Muestra artística y cultural de la escuela', CURDATE() + INTERVAL 75 DAY, NULL, '20:00:00', '22:30:00', 'Aula Magna', 'cultural', TRUE, 1)
ON DUPLICATE KEY UPDATE titulo = VALUES(titulo);

-- Verificar los datos insertados
SELECT 'Estudiantes' as tabla, COUNT(*) as cantidad FROM estudiantes
UNION ALL
SELECT 'Responsables', COUNT(*) FROM responsables
UNION ALL
SELECT 'Contactos Emergencia', COUNT(*) FROM contactos_emergencia
UNION ALL
SELECT 'Horarios', COUNT(*) FROM horarios
UNION ALL
SELECT 'Inasistencias', COUNT(*) FROM inasistencias
UNION ALL
SELECT 'Llamados', COUNT(*) FROM llamados_atencion
UNION ALL
SELECT 'Materias', COUNT(*) FROM materias
UNION ALL
SELECT 'Cursos', COUNT(*) FROM cursos
UNION ALL
SELECT 'Usuarios', COUNT(*) FROM usuarios
UNION ALL
SELECT 'Equipo Directivo', COUNT(*) FROM equipo_directivo
UNION ALL
SELECT 'Eventos', COUNT(*) FROM eventos;

-- Mostrar distribución de estudiantes por curso
SELECT 
    CONCAT(c.anio, '° ', c.division, ' - ', esp.nombre, ' (', t.nombre, ')') as curso,
    COUNT(e.id) as estudiantes
FROM cursos c
LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
LEFT JOIN turnos t ON c.turno_id = t.id
LEFT JOIN estudiantes e ON c.id = e.curso_id AND e.activo = 1
WHERE c.activo = 1
GROUP BY c.id, c.anio, c.division, esp.nombre, t.nombre
ORDER BY c.anio, c.division;
