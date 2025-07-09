-- Script para crear todos los usuarios del sistema educativo
USE cuaderno_digital_eest2;

-- Limpiar usuarios existentes (opcional - comentar si quieres mantener los existentes)
-- DELETE FROM usuarios WHERE username IN ('admin', 'docente', 'director', 'preceptor', 'secretaria');

-- Insertar usuario Administrador del Sistema
INSERT INTO usuarios (username, password, nombre, apellido, email, rol, activo, fecha_creacion) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Sistema', 'admin@eest2.edu.ar', 'admin', TRUE, NOW());

-- Insertar Director
INSERT INTO usuarios (username, password, nombre, apellido, email, rol, activo, fecha_creacion) VALUES 
('director', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos', 'Rodríguez', 'director@eest2.edu.ar', 'directivo', TRUE, NOW());

-- Insertar Preceptor
INSERT INTO usuarios (username, password, nombre, apellido, email, rol, activo, fecha_creacion) VALUES 
('preceptor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María', 'González', 'preceptor@eest2.edu.ar', 'preceptor', TRUE, NOW());

-- Insertar Secretaria
INSERT INTO usuarios (username, password, nombre, apellido, email, rol, activo, fecha_creacion) VALUES 
('secretaria', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana', 'Martínez', 'secretaria@eest2.edu.ar', 'secretaria', TRUE, NOW());

-- Insertar Docente de Prueba (usando rol preceptor ya que no hay rol docente)
INSERT INTO usuarios (username, password, nombre, apellido, email, rol, activo, fecha_creacion) VALUES 
('docente', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Profesor', 'Prueba', 'docente@eest2.edu.ar', 'preceptor', TRUE, NOW());

-- Verificar usuarios creados
SELECT username, nombre, apellido, rol, activo, fecha_creacion FROM usuarios ORDER BY rol, username; 