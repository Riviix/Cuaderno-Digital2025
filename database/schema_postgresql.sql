-- Base de datos para Cuaderno Digital E.E.S.T. N°2
-- Sistema Integral de Gestión Escolar - PostgreSQL Version

-- Tabla de usuarios del sistema
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    rol VARCHAR(20) CHECK (rol IN ('admin', 'directivo', 'preceptor', 'secretaria')) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_usuarios_username ON usuarios(username);
CREATE INDEX IF NOT EXISTS idx_usuarios_rol ON usuarios(rol);

-- Tabla de especialidades
CREATE TABLE IF NOT EXISTS especialidades (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    activa BOOLEAN DEFAULT TRUE
);

CREATE INDEX IF NOT EXISTS idx_especialidades_activa ON especialidades(activa);

-- Tabla de turnos
CREATE TABLE IF NOT EXISTS turnos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL, -- Mañana, Tarde, Contraturno
    hora_inicio TIME,
    hora_fin TIME
);

-- Tabla de talleres
CREATE TABLE IF NOT EXISTS talleres (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    especialidad_id INTEGER REFERENCES especialidades(id) ON DELETE SET NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE
);

CREATE INDEX IF NOT EXISTS idx_talleres_especialidad ON talleres(especialidad_id);

-- Tabla de cursos
CREATE TABLE IF NOT EXISTS cursos (
    id SERIAL PRIMARY KEY,
    anio INTEGER NOT NULL, -- 1 a 7
    division VARCHAR(5) NOT NULL, -- A, B, C, etc.
    turno_id INTEGER NOT NULL REFERENCES turnos(id) ON DELETE RESTRICT,
    especialidad_id INTEGER NOT NULL REFERENCES especialidades(id) ON DELETE RESTRICT,
    taller_id INTEGER REFERENCES talleres(id) ON DELETE SET NULL,
    grado VARCHAR(10) CHECK (grado IN ('inferior', 'superior')) NOT NULL, -- 1-3 inferior, 4-7 superior
    activo BOOLEAN DEFAULT TRUE,
    UNIQUE (anio, division, turno_id, especialidad_id)
);

CREATE INDEX IF NOT EXISTS idx_cursos_turno ON cursos(turno_id);
CREATE INDEX IF NOT EXISTS idx_cursos_especialidad ON cursos(especialidad_id);
CREATE INDEX IF NOT EXISTS idx_cursos_activo ON cursos(activo);

-- Tabla de estudiantes
CREATE TABLE IF NOT EXISTS estudiantes (
    id SERIAL PRIMARY KEY,
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
    curso_id INTEGER REFERENCES cursos(id) ON DELETE SET NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_ingreso DATE DEFAULT CURRENT_DATE
);

CREATE INDEX IF NOT EXISTS idx_estudiantes_dni ON estudiantes(dni);
CREATE INDEX IF NOT EXISTS idx_estudiantes_apellido ON estudiantes(apellido);
CREATE INDEX IF NOT EXISTS idx_estudiantes_curso ON estudiantes(curso_id);
CREATE INDEX IF NOT EXISTS idx_estudiantes_activo ON estudiantes(activo);

-- Tabla de responsables
CREATE TABLE IF NOT EXISTS responsables (
    id SERIAL PRIMARY KEY,
    estudiante_id INTEGER NOT NULL REFERENCES estudiantes(id) ON DELETE CASCADE,
    apellido VARCHAR(100) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    dni VARCHAR(20),
    parentesco VARCHAR(50), -- Padre, Madre, Tutor, etc.
    telefono_fijo VARCHAR(20),
    telefono_celular VARCHAR(20),
    email VARCHAR(100),
    domicilio TEXT,
    ocupacion VARCHAR(100),
    es_contacto_emergencia BOOLEAN DEFAULT FALSE
);

CREATE INDEX IF NOT EXISTS idx_responsables_estudiante ON responsables(estudiante_id);
CREATE INDEX IF NOT EXISTS idx_responsables_contacto_emergencia ON responsables(es_contacto_emergencia);

-- Tabla de contactos de emergencia
CREATE TABLE IF NOT EXISTS contactos_emergencia (
    id SERIAL PRIMARY KEY,
    estudiante_id INTEGER NOT NULL REFERENCES estudiantes(id) ON DELETE CASCADE,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    parentesco VARCHAR(50)
);

CREATE INDEX IF NOT EXISTS idx_contactos_emergencia_estudiante ON contactos_emergencia(estudiante_id);

-- Tabla de equipo directivo
CREATE TABLE IF NOT EXISTS equipo_directivo (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id) ON DELETE SET NULL,
    apellido VARCHAR(100) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    cargo VARCHAR(100) NOT NULL, -- Director, Vicedirector, Secretario, etc.
    telefono VARCHAR(20),
    email VARCHAR(100),
    foto VARCHAR(255),
    activo BOOLEAN DEFAULT TRUE
);

CREATE INDEX IF NOT EXISTS idx_equipo_directivo_cargo ON equipo_directivo(cargo);
CREATE INDEX IF NOT EXISTS idx_equipo_directivo_activo ON equipo_directivo(activo);

-- Tabla de materias
CREATE TABLE IF NOT EXISTS materias (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    especialidad_id INTEGER REFERENCES especialidades(id) ON DELETE SET NULL,
    es_taller BOOLEAN DEFAULT FALSE,
    activa BOOLEAN DEFAULT TRUE
);

CREATE INDEX IF NOT EXISTS idx_materias_especialidad ON materias(especialidad_id);
CREATE INDEX IF NOT EXISTS idx_materias_es_taller ON materias(es_taller);

-- Tabla de horarios
CREATE TABLE IF NOT EXISTS horarios (
    id SERIAL PRIMARY KEY,
    curso_id INTEGER NOT NULL REFERENCES cursos(id) ON DELETE CASCADE,
    materia_id INTEGER NOT NULL REFERENCES materias(id) ON DELETE CASCADE,
    dia_semana INTEGER NOT NULL, -- 1=Lunes, 2=Martes, etc.
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    aula VARCHAR(50),
    docente VARCHAR(100),
    es_contraturno BOOLEAN DEFAULT FALSE,
    activo BOOLEAN DEFAULT TRUE
);

CREATE INDEX IF NOT EXISTS idx_horarios_curso ON horarios(curso_id);
CREATE INDEX IF NOT EXISTS idx_horarios_materia ON horarios(materia_id);
CREATE INDEX IF NOT EXISTS idx_horarios_dia_semana ON horarios(dia_semana);
CREATE INDEX IF NOT EXISTS idx_horarios_contraturno ON horarios(es_contraturno);

-- Tabla de inasistencias
CREATE TABLE IF NOT EXISTS inasistencias (
    id SERIAL PRIMARY KEY,
    estudiante_id INTEGER NOT NULL REFERENCES estudiantes(id) ON DELETE CASCADE,
    fecha DATE NOT NULL,
    tipo VARCHAR(20) CHECK (tipo IN ('completa', 'tarde', 'retiro_anticipado')) NOT NULL,
    justificada BOOLEAN DEFAULT FALSE,
    motivo VARCHAR(255),
    certificado_medico BOOLEAN DEFAULT FALSE,
    observaciones TEXT,
    usuario_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE RESTRICT, -- quien registro la inasistencia
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_inasistencias_estudiante ON inasistencias(estudiante_id);
CREATE INDEX IF NOT EXISTS idx_inasistencias_fecha ON inasistencias(fecha);
CREATE INDEX IF NOT EXISTS idx_inasistencias_tipo ON inasistencias(tipo);
CREATE INDEX IF NOT EXISTS idx_inasistencias_justificada ON inasistencias(justificada);
CREATE INDEX IF NOT EXISTS idx_inasistencias_usuario ON inasistencias(usuario_id);

-- Tabla de llamados de atencion
CREATE TABLE IF NOT EXISTS llamados_atencion (
    id SERIAL PRIMARY KEY,
    estudiante_id INTEGER NOT NULL REFERENCES estudiantes(id) ON DELETE CASCADE,
    fecha DATE NOT NULL,
    motivo TEXT NOT NULL,
    sancion VARCHAR(100),
    observaciones TEXT,
    usuario_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE RESTRICT, -- quien registro el llamado
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_llamados_atencion_estudiante ON llamados_atencion(estudiante_id);
CREATE INDEX IF NOT EXISTS idx_llamados_atencion_fecha ON llamados_atencion(fecha);
CREATE INDEX IF NOT EXISTS idx_llamados_atencion_usuario ON llamados_atencion(usuario_id);

-- Tabla de materias previas
CREATE TABLE IF NOT EXISTS materias_previas (
    id SERIAL PRIMARY KEY,
    estudiante_id INTEGER NOT NULL REFERENCES estudiantes(id) ON DELETE CASCADE,
    materia_id INTEGER NOT NULL REFERENCES materias(id) ON DELETE CASCADE,
    anio_previo INTEGER NOT NULL,
    estado VARCHAR(20) CHECK (estado IN ('pendiente', 'regularizada', 'aprobada')) DEFAULT 'pendiente',
    observaciones TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_materias_previas_estudiante ON materias_previas(estudiante_id);
CREATE INDEX IF NOT EXISTS idx_materias_previas_materia ON materias_previas(materia_id);
CREATE INDEX IF NOT EXISTS idx_materias_previas_estado ON materias_previas(estado);

-- Tabla de archivos adjuntos
CREATE TABLE IF NOT EXISTS archivos_adjuntos (
    id SERIAL PRIMARY KEY,
    estudiante_id INTEGER NOT NULL REFERENCES estudiantes(id) ON DELETE CASCADE,
    tipo VARCHAR(20) CHECK (tipo IN ('certificado_medico', 'constancia', 'otro')) NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE RESTRICT
);

CREATE INDEX IF NOT EXISTS idx_archivos_adjuntos_estudiante ON archivos_adjuntos(estudiante_id);
CREATE INDEX IF NOT EXISTS idx_archivos_adjuntos_tipo ON archivos_adjuntos(tipo);
CREATE INDEX IF NOT EXISTS idx_archivos_adjuntos_fecha ON archivos_adjuntos(fecha_subida);

-- Tabla de notas/calificaciones
CREATE TABLE IF NOT EXISTS notas (
    id SERIAL PRIMARY KEY,
    estudiante_id INTEGER NOT NULL REFERENCES estudiantes(id) ON DELETE CASCADE,
    materia_id INTEGER NOT NULL REFERENCES materias(id) ON DELETE CASCADE,
    trimestre INTEGER NOT NULL CHECK (trimestre BETWEEN 1 AND 3),
    nota DECIMAL(4,2), -- Nota numérica
    observaciones TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE RESTRICT,
    UNIQUE (estudiante_id, materia_id, trimestre)
);

CREATE INDEX IF NOT EXISTS idx_notas_estudiante ON notas(estudiante_id);
CREATE INDEX IF NOT EXISTS idx_notas_materia ON notas(materia_id);
CREATE INDEX IF NOT EXISTS idx_notas_trimestre ON notas(trimestre);

-- Tabla de eventos/actividades escolares
CREATE TABLE IF NOT EXISTS eventos (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    hora_inicio TIME,
    hora_fin TIME,
    lugar VARCHAR(100),
    tipo VARCHAR(20) CHECK (tipo IN ('academico', 'deportivo', 'cultural', 'institucional', 'otro')) DEFAULT 'institucional',
    publico BOOLEAN DEFAULT TRUE, -- Si es visible para todos
    usuario_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE RESTRICT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_eventos_fecha_inicio ON eventos(fecha_inicio);
CREATE INDEX IF NOT EXISTS idx_eventos_tipo ON eventos(tipo);
CREATE INDEX IF NOT EXISTS idx_eventos_publico ON eventos(publico);

-- Insertar datos iniciales básicos
INSERT INTO turnos (nombre, hora_inicio, hora_fin) VALUES 
('Mañana', '07:30:00', '12:30:00'),
('Tarde', '13:30:00', '18:30:00'),
('Contraturno', '18:30:00', '22:30:00')
ON CONFLICT DO NOTHING;

INSERT INTO especialidades (nombre, descripcion) VALUES 
('Informática', 'Especialidad en programación y sistemas informáticos'),
('Electromecánica', 'Especialidad en mecánica y electricidad industrial'),
('Construcciones', 'Especialidad en construcción civil y arquitectura'),
('Química', 'Especialidad en procesos químicos e industriales')
ON CONFLICT DO NOTHING;

-- Usuario administrador por defecto (password is: password)
INSERT INTO usuarios (username, password, nombre, apellido, email, rol) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Sistema', 'admin@eest2.edu.ar', 'admin')
ON CONFLICT (username) DO NOTHING;

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
ON CONFLICT DO NOTHING;

-- Vistas útiles para reportes
CREATE OR REPLACE VIEW vista_estudiantes_completa AS
SELECT 
    e.id,
    e.dni,
    e.apellido,
    e.nombre,
    e.fecha_nacimiento,
    EXTRACT(YEAR FROM AGE(CURRENT_DATE, e.fecha_nacimiento)) as edad,
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
    SUM(CASE WHEN i.justificada = true THEN 1 ELSE 0 END) as justificadas,
    SUM(CASE WHEN i.justificada = false THEN 1 ELSE 0 END) as no_justificadas,
    SUM(CASE WHEN i.tipo = 'completa' THEN 1 ELSE 0 END) as faltas_completas,
    SUM(CASE WHEN i.tipo = 'tarde' THEN 1 ELSE 0 END) as llegadas_tarde,
    SUM(CASE WHEN i.tipo = 'retiro_anticipado' THEN 1 ELSE 0 END) as retiros_anticipados
FROM estudiantes e
LEFT JOIN cursos c ON e.curso_id = c.id
LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
LEFT JOIN inasistencias i ON e.id = i.estudiante_id
WHERE e.activo = true
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
WHERE e.activo = true
GROUP BY e.id, e.apellido, e.nombre, e.dni, c.anio, c.division, esp.nombre;

-- Índices adicionales para optimización
CREATE INDEX IF NOT EXISTS idx_inasistencias_fecha_estudiante ON inasistencias(fecha, estudiante_id);
CREATE INDEX IF NOT EXISTS idx_llamados_fecha_estudiante ON llamados_atencion(fecha, estudiante_id);
CREATE INDEX IF NOT EXISTS idx_estudiantes_apellido_nombre ON estudiantes(apellido, nombre);
CREATE INDEX IF NOT EXISTS idx_cursos_especialidad_turno ON cursos(especialidad_id, turno_id);