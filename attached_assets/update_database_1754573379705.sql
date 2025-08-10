USE cuaderno_digital_eest2;

-- Crear tabla cursos con campo anio
CREATE TABLE cursos (
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

-- Crear tabla materias_previas con campo anio_previo
CREATE TABLE materias_previas (
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

-- Agregar columna usuario_id a equipo_directivo si no existe
ALTER TABLE equipo_directivo ADD COLUMN usuario_id INT NULL AFTER id;
ALTER TABLE equipo_directivo ADD CONSTRAINT fk_equipo_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id); 