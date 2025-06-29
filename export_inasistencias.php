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
if ($fecha_desde) { $where[] = "i.fecha >= ?"; $params[] = $fecha_desde; }
if ($fecha_hasta) { $where[] = "i.fecha <= ?"; $params[] = $fecha_hasta; }
$where_clause = implode(' AND ', $where);

$inasistencias = $db->fetchAll("
    SELECT i.*, e.apellido, e.nombre, c.anio, c.division, esp.nombre as especialidad
    FROM inasistencias i
    JOIN estudiantes e ON i.estudiante_id = e.id
    LEFT JOIN cursos c ON e.curso_id = c.id
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    WHERE $where_clause
    ORDER BY i.fecha DESC, e.apellido, e.nombre
    LIMIT 200
", $params);

$fecha = date('Y-m-d');
$filename = "inasistencias_eest2_{$fecha}." . ($type === 'csv' ? 'csv' : 'pdf');

if ($type === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Escuela de Educación Secundaria Técnica N°2 - Mar del Plata']);
    fputcsv($output, ["Reporte de Inasistencias", "Fecha de generación: $fecha"]);
    fputcsv($output, []);
    fputcsv($output, ['Fecha', 'Estudiante', 'Curso', 'Tipo', 'Justificada', 'Motivo']);
    foreach ($inasistencias as $i) {
        fputcsv($output, [
            $i['fecha'],
            $i['apellido'] . ', ' . $i['nombre'],
            $i['anio'] . '° ' . $i['division'] . ' - ' . $i['especialidad'],
            $i['tipo'],
            $i['justificada'] ? 'Sí' : 'No',
            $i['motivo']
        ]);
    }
    fclose($output);
    exit;
} else {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename=' . $filename);
    echo "%PDF-1.4\n";
    echo "Inasistencias - E.E.S.T N°2\n";
    echo "Fecha de generación: $fecha\n\n";
    echo "Fecha | Estudiante | Curso | Tipo | Justificada | Motivo\n";
    foreach ($inasistencias as $i) {
        echo $i['fecha'] . ' | ' . $i['apellido'] . ', ' . $i['nombre'] . ' | ' . $i['anio'] . '° ' . $i['division'] . ' - ' . $i['especialidad'] . ' | ' . $i['tipo'] . ' | ' . ($i['justificada'] ? 'Sí' : 'No') . ' | ' . $i['motivo'] . "\n";
    }
    exit;
} 