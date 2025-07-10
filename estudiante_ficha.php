<?php 
$pageTitle = 'Ficha del Estudiante - Cuaderno Digital E.E.S.T N°2';
include 'includes/header.php'; 

require_once 'config/database.php';
$db = Database::getInstance();

$estudiante_id = $_GET['id'] ?? 0;

if (!$estudiante_id) {
    header('Location: estudiantes.php');
    exit();
}

// Obtener datos del estudiante
$estudiante = $db->fetch("
    SELECT e.*, c.anio, c.division, c.grado, esp.nombre as especialidad_nombre,
           t.nombre as turno_nombre, tal.nombre as taller_nombre
    FROM estudiantes e
    LEFT JOIN cursos c ON e.curso_id = c.id
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    LEFT JOIN turnos t ON c.turno_id = t.id
    LEFT JOIN talleres tal ON c.taller_id = tal.id
    WHERE e.id = ? AND e.activo = 1
", [$estudiante_id]);

if (!$estudiante) {
    header('Location: estudiantes.php');
    exit();
}

// Obtener responsables
$responsables = $db->fetchAll("
    SELECT * FROM responsables 
    WHERE estudiante_id = ? 
    ORDER BY es_contacto_emergencia DESC, apellido, nombre
", [$estudiante_id]);

// Obtener contactos de emergencia
$emergencias = $db->fetchAll("
    SELECT * FROM contactos_emergencia 
    WHERE estudiante_id = ?
", [$estudiante_id]);

// Obtener inasistencias recientes
$inasistencias = $db->fetchAll("
    SELECT i.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido
    FROM inasistencias i
    LEFT JOIN usuarios u ON i.usuario_id = u.id
    WHERE i.estudiante_id = ?
    ORDER BY i.fecha DESC
    LIMIT 20
", [$estudiante_id]);

// Obtener llamados de atención recientes
$llamados = $db->fetchAll("
    SELECT la.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido
    FROM llamados_atencion la
    LEFT JOIN usuarios u ON la.usuario_id = u.id
    WHERE la.estudiante_id = ?
    ORDER BY la.fecha DESC
    LIMIT 10
", [$estudiante_id]);

// Obtener materias previas
$materias_previas = $db->fetchAll("
    SELECT mp.*, m.nombre as materia_nombre
    FROM materias_previas mp
    LEFT JOIN materias m ON mp.materia_id = m.id
    WHERE mp.estudiante_id = ?
    ORDER BY mp.anio_previo DESC, m.nombre
", [$estudiante_id]);

// Obtener archivos adjuntos
$archivos = $db->fetchAll("
    SELECT aa.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido
    FROM archivos_adjuntos aa
    LEFT JOIN usuarios u ON aa.usuario_id = u.id
    WHERE aa.estudiante_id = ?
    ORDER BY aa.fecha_subida DESC
", [$estudiante_id]);

// Calcular estadísticas
$total_faltas = $db->fetch("
    SELECT COUNT(*) as total FROM inasistencias 
    WHERE estudiante_id = ? AND fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
", [$estudiante_id])['total'];

$total_llamados = $db->fetch("
    SELECT COUNT(*) as total FROM llamados_atencion 
    WHERE estudiante_id = ? AND fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
", [$estudiante_id])['total'];

$previas_pendientes = $db->fetch("
    SELECT COUNT(*) as total FROM materias_previas 
    WHERE estudiante_id = ? AND estado = 'pendiente'
", [$estudiante_id])['total'];
?>

<section class="ficha-estudiante">
    <div class="section-header">
        <h2>Ficha del Estudiante</h2>
        <div class="header-actions">
            <a href="estudiantes.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="estudiante_ficha.php?id=<?php echo $estudiante_id; ?>&action=editar" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
        </div>
    </div>

    <!-- Información principal -->
    <div class="student-profile">
        <div class="student-photo">
            <?php if ($estudiante['foto']): ?>
                <img src="<?php echo htmlspecialchars($estudiante['foto']); ?>" alt="Foto del estudiante">
            <?php else: ?>
                <div class="photo-placeholder-large">
                    <i class="fas fa-user"></i>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="student-info">
            <h2><?php echo htmlspecialchars($estudiante['apellido'] . ', ' . $estudiante['nombre']); ?></h2>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">DNI</div>
                    <div class="info-value"><?php echo htmlspecialchars($estudiante['dni']); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Curso</div>
                    <div class="info-value">
                        <?php echo $estudiante['anio'] . '° ' . $estudiante['division']; ?> - 
                        <?php echo htmlspecialchars($estudiante['especialidad_nombre']); ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Turno</div>
                    <div class="info-value"><?php echo htmlspecialchars($estudiante['turno_nombre']); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Taller</div>
                    <div class="info-value"><?php echo htmlspecialchars($estudiante['taller_nombre'] ?? 'No asignado'); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Fecha de Nacimiento</div>
                    <div class="info-value">
                        <?php echo $estudiante['fecha_nacimiento'] ? date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])) : 'No registrada'; ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Grupo Sanguíneo</div>
                    <div class="info-value"><?php echo htmlspecialchars($estudiante['grupo_sanguineo'] ?? 'No registrado'); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Obra Social</div>
                    <div class="info-value"><?php echo htmlspecialchars($estudiante['obra_social'] ?? 'No registrada'); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Domicilio</div>
                    <div class="info-value"><?php echo htmlspecialchars($estudiante['domicilio'] ?? 'No registrado'); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Teléfono Fijo</div>
                    <div class="info-value"><?php echo htmlspecialchars($estudiante['telefono_fijo'] ?? 'No registrado'); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Teléfono Celular</div>
                    <div class="info-value"><?php echo htmlspecialchars($estudiante['telefono_celular'] ?? 'No registrado'); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($estudiante['email'] ?? 'No registrado'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="stats-cards grid-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $total_faltas; ?></h3>
                <p>Faltas (30 días)</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $total_llamados; ?></h3>
                <p>Llamados (30 días)</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $previas_pendientes; ?></h3>
                <p>Materias Previas</p>
            </div>
        </div>
    </div>

    <!-- Tabs de información -->
    <div class="tabs-container">
        <div class="tabs-header">
            <button class="tab-btn active" data-tab="responsables">Responsables</button>
            <button class="tab-btn" data-tab="emergencias">Emergencias</button>
            <button class="tab-btn" data-tab="inasistencias">Inasistencias</button>
            <button class="tab-btn" data-tab="llamados">Llamados</button>
            <button class="tab-btn" data-tab="previas">Materias Previas</button>
            <button class="tab-btn" data-tab="archivos">Archivos</button>
        </div>
        
        <div class="tab-content active" id="responsables">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Responsables</h3>
                    <a href="responsables.php?estudiante=<?php echo $estudiante_id; ?>&action=nuevo" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Agregar
                    </a>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Apellido y Nombre</th>
                                <th>Parentesco</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Contacto Emergencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($responsables as $responsable): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($responsable['apellido'] . ', ' . $responsable['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($responsable['parentesco']); ?></td>
                                <td>
                                    <?php if ($responsable['telefono_celular']): ?>
                                        <?php echo htmlspecialchars($responsable['telefono_celular']); ?>
                                    <?php elseif ($responsable['telefono_fijo']): ?>
                                        <?php echo htmlspecialchars($responsable['telefono_fijo']); ?>
                                    <?php else: ?>
                                        No registrado
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($responsable['email'] ?? 'No registrado'); ?></td>
                                <td>
                                    <?php if ($responsable['es_contacto_emergencia']): ?>
                                        <span class="status status-active">Sí</span>
                                    <?php else: ?>
                                        <span class="status status-inactive">No</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($responsables)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay responsables registrados</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="tab-content" id="emergencias">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Contactos de Emergencia</h3>
                    <a href="emergencias.php?estudiante=<?php echo $estudiante_id; ?>&action=nuevo" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Agregar
                    </a>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Parentesco</th>
                                <th>Teléfono</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($emergencias as $emergencia): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($emergencia['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($emergencia['parentesco']); ?></td>
                                <td><?php echo htmlspecialchars($emergencia['telefono']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($emergencias)): ?>
                            <tr>
                                <td colspan="3" class="text-center">No hay contactos de emergencia registrados</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="tab-content" id="inasistencias">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Inasistencias Recientes</h3>
                    <a href="inasistencias.php?estudiante=<?php echo $estudiante_id; ?>&action=nueva" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Registrar
                    </a>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Justificada</th>
                                <th>Motivo</th>
                                <th>Registrado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inasistencias as $inasistencia): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($inasistencia['fecha'])); ?></td>
                                <td>
                                    <span class="status status-<?php echo $inasistencia['tipo'] === 'completa' ? 'danger' : 'warning'; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $inasistencia['tipo'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($inasistencia['justificada']): ?>
                                        <span class="status status-active">Sí</span>
                                    <?php else: ?>
                                        <span class="status status-inactive">No</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($inasistencia['motivo'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($inasistencia['usuario_apellido'] . ', ' . $inasistencia['usuario_nombre']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($inasistencias)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay inasistencias registradas</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="tab-content" id="llamados">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Llamados de Atención Recientes</h3>
                    <a href="llamados.php?estudiante=<?php echo $estudiante_id; ?>&action=nuevo" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Registrar
                    </a>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Motivo</th>
                                <th>Sanción</th>
                                <th>Registrado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($llamados as $llamado): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($llamado['fecha'])); ?></td>
                                <td><?php echo htmlspecialchars($llamado['motivo']); ?></td>
                                <td><?php echo htmlspecialchars($llamado['sancion'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($llamado['usuario_apellido'] . ', ' . $llamado['usuario_nombre']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($llamados)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay llamados de atención registrados</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="tab-content" id="previas">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Materias Previas</h3>
                    <a href="previas.php?estudiante=<?php echo $estudiante_id; ?>&action=nueva" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Agregar
                    </a>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Materia</th>
                                <th>Año Previo</th>
                                <th>Estado</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materias_previas as $previa): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($previa['materia_nombre']); ?></td>
                                <td><?php echo $previa['anio_previo']; ?>°</td>
                                <td>
                                    <span class="status status-<?php echo $previa['estado'] === 'pendiente' ? 'warning' : ($previa['estado'] === 'aprobada' ? 'active' : 'info'); ?>">
                                        <?php echo ucfirst($previa['estado']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($previa['observaciones'] ?? '-'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($materias_previas)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay materias previas registradas</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="tab-content" id="archivos">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Archivos Adjuntos</h3>
                    <a href="archivos.php?estudiante=<?php echo $estudiante_id; ?>&action=subir" class="btn btn-primary btn-sm">
                        <i class="fas fa-upload"></i> Subir
                    </a>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Archivo</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Fecha</th>
                                <th>Subido por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($archivos as $archivo): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($archivo['nombre_archivo']); ?></td>
                                <td>
                                    <span class="status status-info">
                                        <?php echo ucfirst(str_replace('_', ' ', $archivo['tipo'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($archivo['descripcion'] ?? '-'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($archivo['fecha_subida'])); ?></td>
                                <td><?php echo htmlspecialchars($archivo['usuario_apellido'] . ', ' . $archivo['usuario_nombre']); ?></td>
                                <td>
                                    <a href="<?php echo htmlspecialchars($archivo['ruta_archivo']); ?>" class="btn btn-sm btn-info" target="_blank">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($archivos)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay archivos adjuntos</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.ficha-estudiante {
    max-width: 1200px;
    margin: 0 auto;
}

.header-actions {
    display: flex;
    gap: 15px;
}

.photo-placeholder-large {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: var(--light-color);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 4px solid var(--border-color);
    margin: 0 auto;
}

.photo-placeholder-large i {
    font-size: 48px;
    color: var(--gray-color);
}

.tabs-container {
    margin-top: 30px;
}

.tabs-header {
    display: flex;
    border-bottom: 2px solid var(--border-color);
    margin-bottom: 20px;
    overflow-x: auto;
}

.tab-btn {
    padding: 15px 25px;
    border: none;
    background: none;
    cursor: pointer;
    font-weight: 500;
    color: var(--gray-color);
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
    white-space: nowrap;
}

.tab-btn:hover {
    color: var(--primary-color);
}

.tab-btn.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remover clase active de todos los botones y contenidos
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Agregar clase active al botón clickeado y su contenido
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?> 