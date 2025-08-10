-- Base de datos para Cuaderno Digital E.E.S.T. N°2
-- Sistema Integral de Gestión Escolar

CREATE DATABASE IF NOT EXISTS cuaderno_digital_eest2;
USE cuaderno_digital_eest2;

-- Tabla de usuarios del sistema
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    rol ENUM('admin', 'directivo', 'preceptor', 'secretaria') NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_rol (rol)
);

-- Tabla de especialidades
CREATE TABLE IF NOT EXISTS especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    activa BOOLEAN DEFAULT TRUE,
    INDEX idx_activa (activa)
);

-- Tabla de turnos
CREATE TABLE IF NOT EXISTS turnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL, -- Mañana, Tarde, Contraturno
    hora_inicio TIME,
    hora_fin TIME
);

-- Tabla de talleres
CREATE TABLE IF NOT EXISTS talleres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    especialidad_id INT,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id) ON DELETE SET NULL,
    INDEX idx_especialidad (especialidad_id)
);

-- Tabla de cursos
CREATE TABLE IF NOT EXISTS cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anio INT NOT NULL, -- 1 a 7
    division VARCHAR(5) NOT NULL, -- A, B, C, etc.
    turno_id INT NOT NULL,
    especialidad_id INT NOT NULL,
    taller_id INT,
    grado ENUM('inferior', 'superior') NOT NULL, -- 1-3 inferior, 4-7 superior
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (turno_id) REFERENCES turnos(id) ON DELETE RESTRICT,
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id) ON DELETE RESTRICT,
    FOREIGN KEY (taller_id) REFERENCES talleres(id) ON DELETE SET NULL,
    UNIQUE KEY unique_curso (anio, division, turno_id, especialidad_id),
    INDEX idx_turno (turno_id),
    INDEX idx_especialidad (especialidad_id),
    INDEX idx_activo (activo)
);

-- Tabla de estudiantes
CREATE TABLE IF NOT EXISTS estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dni VARCHAR(20) UNIQUE NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE,
    grupo_sanguineo VARCHAR(10),
    obra_social VARCHAR(100),
    domicilio TEXT,
    telefono_fijo VARCHAR(20),
    telefono_celular VARCHAR(20),
    email VARCHAR(100),
    foto VARCHAR(255), -- ruta de la imagen
    curso_id INT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_ingreso DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE SET NULL,
    INDEX idx_dni (dni),
    INDEX idx_apellido (apellido),
    INDEX idx_curso (curso_id),
    INDEX idx_activo (activo)
);

-- Tabla de responsables
CREATE TABLE IF NOT EXISTS responsables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    dni VARCHAR(20),
    parentesco VARCHAR(50), -- Padre, Madre, Tutor, etc.
    telefono_fijo VARCHAR(20),
    telefono_celular VARCHAR(20),
    email VARCHAR(100),
    domicilio TEXT,
    ocupacion VARCHAR(100),
    es_contacto_emergencia BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    INDEX idx_estudiante (estudiante_id),
    INDEX idx_contacto_emergencia (es_contacto_emergencia)
);

-- Tabla de contactos de emergencia
CREATE TABLE IF NOT EXISTS contactos_emergencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    parentesco VARCHAR(50),
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    INDEX idx_estudiante (estudiante_id)
);

-- Tabla de equipo directivo
CREATE TABLE IF NOT EXISTS equipo_directivo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    apellido VARCHAR(100) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    cargo VARCHAR(100) NOT NULL, -- Director, Vicedirector, Secretario, etc.
    telefono VARCHAR(20),
    email VARCHAR(100),
    foto VARCHAR(255),
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_cargo (cargo),
    INDEX idx_activo (activo)
);

-- Tabla de materias
CREATE TABLE IF NOT EXISTS materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    especialidad_id INT,
    es_taller BOOLEAN DEFAULT FALSE,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id) ON DELETE SET NULL,
    INDEX idx_especialidad (especialidad_id),
    INDEX idx_es_taller (es_taller)
);

-- Tabla de horarios
CREATE TABLE IF NOT EXISTS horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    materia_id INT NOT NULL,
    dia_semana INT NOT NULL, -- 1=Lunes, 2=Martes, etc.
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    aula VARCHAR(50),
    docente VARCHAR(100),
    es_contraturno BOOLEAN DEFAULT FALSE,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE CASCADE,
    INDEX idx_curso (curso_id),
    INDEX idx_materia (materia_id),
    INDEX idx_dia_semana (dia_semana),
    INDEX idx_contraturno (es_contraturno)
);

-- Tabla de inasistencias
CREATE TABLE IF NOT EXISTS inasistencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    fecha DATE NOT NULL,
    tipo ENUM('completa', 'tarde', 'retiro_anticipado') NOT NULL,
    justificada BOOLEAN DEFAULT FALSE,
    motivo VARCHAR(255),
    certificado_medico BOOLEAN DEFAULT FALSE,
    observaciones TEXT,
    usuario_id INT NOT NULL, -- quien registro la inasistencia
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    INDEX idx_estudiante (estudiante_id),
    INDEX idx_fecha (fecha),
    INDEX idx_tipo (tipo),
    INDEX idx_justificada (justificada),
    INDEX idx_usuario (usuario_id)
);

-- Tabla de llamados de atencion
CREATE TABLE IF NOT EXISTS llamados_atencion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    fecha DATE NOT NULL,
    motivo TEXT NOT NULL,
    sancion VARCHAR(100),
    observaciones TEXT,
    usuario_id INT NOT NULL, -- quien registro el llamado
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    INDEX idx_estudiante (estudiante_id),
    INDEX idx_fecha (fecha),
    INDEX idx_usuario (usuario_id)
);

-- Tabla de materias previas
CREATE TABLE IF NOT EXISTS materias_previas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    materia_id INT NOT NULL,
    anio_previo INT NOT NULL,
    estado ENUM('pendiente', 'regularizada', 'aprobada') DEFAULT 'pendiente',
    observaciones TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE CASCADE,
    INDEX idx_estudiante (estudiante_id),
    INDEX idx_materia (materia_id),
    INDEX idx_estado (estado)
);

-- Tabla de archivos adjuntos
CREATE TABLE IF NOT EXISTS archivos_adjuntos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    tipo ENUM('certificado_medico', 'constancia', 'otro') NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT NOT NULL,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    INDEX idx_estudiante (estudiante_id),
    INDEX idx_tipo (tipo),
    INDEX idx_fecha (fecha_subida)
);

-- Tabla de notas/calificaciones
CREATE TABLE IF NOT EXISTS notas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    materia_id INT NOT NULL,
    trimestre INT NOT NULL CHECK (trimestre BETWEEN 1 AND 3),
    nota DECIMAL(4,2), -- Nota numérica
    observaciones TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT NOT NULL,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_nota_trimestre (estudiante_id, materia_id, trimestre),
    INDEX idx_estudiante (estudiante_id),
    INDEX idx_materia (materia_id),
    INDEX idx_trimestre (trimestre)
);

-- Tabla de eventos/actividades escolares
CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    hora_inicio TIME,
    hora_fin TIME,
    lugar VARCHAR(100),
    tipo ENUM('academico', 'deportivo', 'cultural', 'institucional', 'otro') DEFAULT 'institucional',
    publico BOOLEAN DEFAULT TRUE, -- Si es visible para todos
    usuario_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    INDEX idx_fecha_inicio (fecha_inicio),
    INDEX idx_tipo (tipo),
    INDEX idx_publico (publico)
);

-- Insertar datos iniciales básicos
INSERT INTO turnos (nombre, hora_inicio, hora_fin) VALUES 
('Mañana', '07:30:00', '12:30:00'),
('Tarde', '13:30:00', '18:30:00'),
('Contraturno', '18:30:00', '22:30:00')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT INTO especialidades (nombre, descripcion) VALUES 
('Informática', 'Especialidad en programación y sistemas informáticos'),
('Electromecánica', 'Especialidad en mecánica y electricidad industrial'),
('Construcciones', 'Especialidad en construcción civil y arquitectura'),
('Química', 'Especialidad en procesos químicos e industriales')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Usuario administrador por defecto
INSERT INTO usuarios (username, password, nombre, apellido, email, rol) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Sistema', 'admin@eest2.edu.ar', 'admin')
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- Materias básicas
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
('Construcciones', 3, TRUE),
('Electrónica', 2, TRUE),
('Programación', 1, TRUE),
('Laboratorio de Química', 4, TRUE)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Triggers para mantener integridad y logs
DELIMITER //

-- Trigger para actualizar fecha de modificación en estudiantes
CREATE TRIGGER IF NOT EXISTS estudiantes_updated 
    BEFORE UPDATE ON estudiantes
    FOR EACH ROW
    BEGIN
        -- Aquí podrías agregar lógica adicional si fuera necesaria
        SET NEW.fecha_ingreso = COALESCE(NEW.fecha_ingreso, OLD.fecha_ingreso);
    END//

-- Trigger para validar horarios sin conflictos
CREATE TRIGGER IF NOT EXISTS horarios_conflict_check
    BEFORE INSERT ON horarios
    FOR EACH ROW
    BEGIN
        DECLARE conflict_count INT DEFAULT 0;
        
        SELECT COUNT(*) INTO conflict_count
        FROM horarios h
        WHERE h.curso_id = NEW.curso_id
          AND h.dia_semana = NEW.dia_semana
          AND h.activo = TRUE
          AND (
              (NEW.hora_inicio >= h.hora_inicio AND NEW.hora_inicio < h.hora_fin) OR
              (NEW.hora_fin > h.hora_inicio AND NEW.hora_fin <= h.hora_fin) OR
              (NEW.hora_inicio <= h.hora_inicio AND NEW.hora_fin >= h.hora_fin)
          );
          
        IF conflict_count > 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Conflicto de horarios: Ya existe un horario para este curso en el mismo día y horario';
        END IF;
    END//

DELIMITER ;

-- Vistas útiles para reportes
CREATE OR REPLACE VIEW vista_estudiantes_completa AS
SELECT 
    e.id,
    e.dni,
    e.apellido,
    e.nombre,
    e.fecha_nacimiento,
    FLOOR(DATEDIFF(CURDATE(), e.fecha_nacimiento) / 365.25) as edad,
    e.grupo_sanguineo,
    e.obra_social,
    e.domicilio,
    e.telefono_celular,
    e.email,
    c.anio,
    c.division,
    esp.nombre as especialidad,
    t.nombre as turno,
    c.grado,
    e.activo,
    e.fecha_ingreso
FROM estudiantes e
LEFT JOIN cursos c ON e.curso_id = c.id
LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
LEFT JOIN turnos t ON c.turno_id = t.id;

CREATE OR REPLACE VIEW vista_inasistencias_resumen AS
SELECT 
    e.id as estudiante_id,
    e.apellido,
    e.nombre,
    e.dni,
    c.anio,
    c.division,
    esp.nombre as especialidad,
    COUNT(i.id) as total_inasistencias,
    SUM(CASE WHEN i.justificada = 1 THEN 1 ELSE 0 END) as justificadas,
    SUM(CASE WHEN i.justificada = 0 THEN 1 ELSE 0 END) as no_justificadas,
    SUM(CASE WHEN i.tipo = 'completa' THEN 1 ELSE 0 END) as faltas_completas,
    SUM(CASE WHEN i.tipo = 'tarde' THEN 1 ELSE 0 END) as llegadas_tarde,
    SUM(CASE WHEN i.tipo = 'retiro_anticipado' THEN 1 ELSE 0 END) as retiros_anticipados
FROM estudiantes e
LEFT JOIN cursos c ON e.curso_id = c.id
LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
LEFT JOIN inasistencias i ON e.id = i.estudiante_id
WHERE e.activo = 1
GROUP BY e.id, e.apellido, e.nombre, e.dni, c.anio, c.division, esp.nombre;

CREATE OR REPLACE VIEW vista_llamados_resumen AS
SELECT 
    e.id as estudiante_id,
    e.apellido,
    e.nombre,
    e.dni,
    c.anio,
    c.division,
    esp.nombre as especialidad,
    COUNT(l.id) as total_llamados,
    SUM(CASE WHEN l.sancion IS NOT NULL AND l.sancion != '' THEN 1 ELSE 0 END) as con_sancion,
    SUM(CASE WHEN l.sancion IS NULL OR l.sancion = '' THEN 1 ELSE 0 END) as sin_sancion,
    MAX(l.fecha) as ultimo_llamado
FROM estudiantes e
LEFT JOIN cursos c ON e.curso_id = c.id
LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
LEFT JOIN llamados_atencion l ON e.id = l.estudiante_id
WHERE e.activo = 1
GROUP BY e.id, e.apellido, e.nombre, e.dni, c.anio, c.division, esp.nombre;

-- Índices adicionales para optimización
CREATE INDEX idx_inasistencias_fecha_estudiante ON inasistencias(fecha, estudiante_id);
CREATE INDEX idx_llamados_fecha_estudiante ON llamados_atencion(fecha, estudiante_id);
CREATE INDEX idx_estudiantes_apellido_nombre ON estudiantes(apellido, nombre);
CREATE INDEX idx_cursos_especialidad_turno ON cursos(especialidad_id, turno_id);

-- Configuración final
SET FOREIGN_KEY_CHECKS = 1;
SET SQL_MODE = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
