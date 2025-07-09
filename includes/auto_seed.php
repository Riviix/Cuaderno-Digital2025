<?php
// Carga automática de datos base (cursos, especialidades, talleres/grupos)
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();

// 1. Especialidades
$especialidades = [
    'Maestro Mayor de Obra', // A
    'Programación',          // B
    'Electrónica'            // C
];
foreach ($especialidades as $esp) {
    $exists = $db->fetch("SELECT id FROM especialidades WHERE nombre = ?", [$esp]);
    if (!$exists) {
        $db->query("INSERT INTO especialidades (nombre, activa) VALUES (?, 1)", [$esp]);
    }
}
$esp_ids = [
    'A' => $db->fetch("SELECT id FROM especialidades WHERE nombre = 'Maestro Mayor de Obra'")['id'],
    'B' => $db->fetch("SELECT id FROM especialidades WHERE nombre = 'Programación'")['id'],
    'C' => $db->fetch("SELECT id FROM especialidades WHERE nombre = 'Electrónica'")['id']
];

// 2. Cursos
for ($anio = 1; $anio <= 7; $anio++) {
    if ($anio <= 3) {
        foreach (['A', 'B'] as $div) {
            $exists = $db->fetch("SELECT id FROM cursos WHERE anio = ? AND division = ?", [$anio, $div]);
            if (!$exists) {
                $turno_id = $db->fetch("SELECT id FROM turnos LIMIT 1")['id'];
                $grado = 'inferior';
                $db->query("INSERT INTO cursos (anio, division, turno_id, grado, activo) VALUES (?, ?, ?, ?, 1)", [$anio, $div, $turno_id, $grado]);
            }
        }
    } else {
        foreach ([['A','Maestro Mayor de Obra'],['B','Programación'],['C','Electrónica']] as [$div, $esp]) {
            $exists = $db->fetch("SELECT id FROM cursos WHERE anio = ? AND division = ?", [$anio, $div]);
            if (!$exists) {
                $esp_id = $esp_ids[$div];
                $turno_id = $db->fetch("SELECT id FROM turnos LIMIT 1")['id'];
                $grado = 'superior';
                $db->query("INSERT INTO cursos (anio, division, turno_id, especialidad_id, grado, activo) VALUES (?, ?, ?, ?, ?, 1)", [$anio, $div, $turno_id, $esp_id, $grado]);
            }
        }
    }
}

// 3. Talleres y grupos
for ($anio = 1; $anio <= 7; $anio++) {
    $divisiones = ($anio <= 3) ? ['A', 'B'] : ['A', 'B', 'C'];
    foreach ($divisiones as $div) {
        // Buscar curso
        $curso = $db->fetch("SELECT id, especialidad_id FROM cursos WHERE anio = ? AND division = ?", [$anio, $div]);
        if (!$curso) continue;
        $esp_id = $curso['especialidad_id'] ?? null;
        // Grupos de taller
        $grupos = ['A', 'B'];
        if ($anio == 6) $grupos[] = 'C';
        foreach ($grupos as $grupo) {
            $nombre = ($esp_id ? $db->fetch("SELECT nombre FROM especialidades WHERE id = ?", [$esp_id])['nombre'] : 'Sin especialidad') . " - Grupo $grupo";
            $exists = $db->fetch("SELECT id FROM talleres WHERE nombre = ? AND (especialidad_id = ? OR (? IS NULL AND especialidad_id IS NULL))", [$nombre, $esp_id, $esp_id]);
            if (!$exists) {
                $db->query("INSERT INTO talleres (nombre, especialidad_id) VALUES (?, ?)", [$nombre, $esp_id]);
            }
        }
    }
} 