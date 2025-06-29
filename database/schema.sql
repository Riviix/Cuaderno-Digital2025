-- Base de datos para Cuaderno Digital E.E.S.T. N°2

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
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de especialidades
CREATE TABLE IF NOT EXISTS especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    activa BOOLEAN DEFAULT TRUE
);

-- Tabla de turnos
CREATE TABLE IF NOT EXISTS turnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL, -- Manana, Tarde, Contraturno
    hora_inicio TIME,
    hora_fin TIME
);

-- Tabla de talleres
CREATE TABLE IF NOT EXISTS talleres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    especialidad_id INT,
    descripcion TEXT,
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id)
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
    FOREIGN KEY (turno_id) REFERENCES turnos(id),
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id),
    FOREIGN KEY (taller_id) REFERENCES talleres(id)
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
    fecha_ingreso DATE DEFAULT CURRENT_DATE,
    FOREIGN KEY (curso_id) REFERENCES cursos(id)
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
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id)
);

-- Tabla de contactos de emergencia
CREATE TABLE IF NOT EXISTS contactos_emergencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    parentesco VARCHAR(50),
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id)
);

-- Tabla de equipo directivo
CREATE TABLE IF NOT EXISTS equipo_directivo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    apellido VARCHAR(100) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    cargo VARCHAR(100) NOT NULL, -- Director, Vicedirector, Secretario, etc.
    telefono VARCHAR(20),
    email VARCHAR(100),
    foto VARCHAR(255),
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de materias
CREATE TABLE IF NOT EXISTS materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    especialidad_id INT,
    es_taller BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id)
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
    FOREIGN KEY (curso_id) REFERENCES cursos(id),
    FOREIGN KEY (materia_id) REFERENCES materias(id)
);

-- Tabla de inasistencias
CREATE TABLE IF NOT EXISTS inasistencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    fecha DATE NOT NULL,
    tipo ENUM('completa', 'tarde', 'retiro_anticipado') NOT NULL,
    justificada BOOLEAN DEFAULT FALSE,
    motivo TEXT,
    certificado_medico BOOLEAN DEFAULT FALSE,
    observaciones TEXT,
    usuario_id INT NOT NULL, -- quien registro la inasistencia
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
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
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
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
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id),
    FOREIGN KEY (materia_id) REFERENCES materias(id)
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
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Insertar datos iniciales
INSERT INTO turnos (nombre, hora_inicio, hora_fin) VALUES 
('Manana', '07:30:00', '12:30:00'),
('Tarde', '13:30:00', '18:30:00'),
('Contraturno', '18:30:00', '22:30:00');

INSERT INTO especialidades (nombre, descripcion) VALUES 
('Informatica', 'Especialidad en programacion y sistemas'),
('Electromecanica', 'Especialidad en mecanica y electricidad'),
('Construcciones', 'Especialidad en construccion civil'),
('Quimica', 'Especialidad en procesos quimicos');

INSERT INTO usuarios (username, password, nombre, apellido, email, rol) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Sistema', 'admin@eest2.edu.ar', 'admin'); 