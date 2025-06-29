<?php
require_once 'config/database.php';
$db = Database::getInstance();

// Filtros
$curso_filter = $_GET['curso'] ?? '';
$estudiante_filter = $_GET['estudiante'] ?? '';
$fecha_desde = $_GET['fecha_desde'] ?? date('Y-m-01');
$fecha_hasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
$type = $_GET['type'] ?? 'csv';

$where = ["1=1"];
$params = [];
if ($curso_filter) { $where[] = "e.curso_id = ?"; $params[] = $curso_filter; }
if ($estudiante_filter) { $where[] = "e.id = ?"; $params[] = $estudiante_filter; }
if ($fecha_desde) { $where[] = "l.fecha >= ?"; $params[] = $fecha_desde; }
if ($fecha_hasta) { $where[] = "l.fecha <= ?"; $params[] = $fecha_hasta; }
$where_clause = implode(' AND ', $where);

$llamados = $db->fetchAll("
    SELECT l.*, e.apellido, e.nombre, c.anio, c.division, esp.nombre as especialidad
    FROM llamados_atencion l
    JOIN estudiantes e ON l.estudiante_id = e.id
    LEFT JOIN cursos c ON e.curso_id = c.id
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    WHERE $where_clause
    ORDER BY l.fecha DESC, e.apellido, e.nombre
    LIMIT 200
", $params);

$fecha = date('Y-m-d');
$filename = "llamados_eest2_{$fecha}." . ($type === 'csv' ? 'csv' : 'pdf');

if ($type === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Escuela de Educación Secundaria Técnica N°2 - Mar del Plata']);
    fputcsv($output, ["Reporte de Llamados de Atención", "Fecha de generación: $fecha"]);
    fputcsv($output, []);
    fputcsv($output, ['Fecha', 'Estudiante', 'Curso', 'Motivo', 'Sanción']);
    foreach ($llamados as $l) {
        fputcsv($output, [
            $l['fecha'],
            $l['apellido'] . ', ' . $l['nombre'],
            $l['anio'] . '° ' . $l['division'] . ' - ' . $l['especialidad'],
            $l['motivo'],
            $l['sancion']
        ]);
    }
    fclose($output);
    exit;
} else {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename=' . $filename);
    echo "%PDF-1.4\n";
    echo "Llamados de Atención - E.E.S.T N°2\n";
    echo "Fecha de generación: $fecha\n\n";
    echo "Fecha | Estudiante | Curso | Motivo | Sanción\n";
    foreach ($llamados as $l) {
        echo $l['fecha'] . ' | ' . $l['apellido'] . ', ' . $l['nombre'] . ' | ' . $l['anio'] . '° ' . $l['division'] . ' - ' . $l['especialidad'] . ' | ' . $l['motivo'] . ' | ' . $l['sancion'] . "\n";
    }
    exit;
} 