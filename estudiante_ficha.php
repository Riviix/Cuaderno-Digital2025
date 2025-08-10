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
    SELECT e.*, c.anio, c.division, esp.nombre as especialidad, t.nombre as turno
    FROM estudiantes e
    LEFT JOIN cursos c ON e.curso_id = c.id
    LEFT JOIN especialidades esp ON c.especialidad_id = esp.id
    LEFT JOIN turnos t ON c.turno_id = t.id
    WHERE e.id = ? AND e.activo = 1
", [$estudiante_id]);

if (!$estudiante) {
    header('Location: estudiantes.php');
    exit();
}

// Obtener llamados de atención
$llamados = $db->fetchAll("
    SELECT l.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido
    FROM llamados_atencion l
    LEFT JOIN usuarios u ON l.usuario_id = u.id
    WHERE l.estudiante_id = ?
    ORDER BY l.fecha DESC
    LIMIT 20
", [$estudiante_id]);

// Obtener responsables
$responsables = $db->fetchAll("
    SELECT * FROM responsables WHERE estudiante_id = ? ORDER BY es_contacto_emergencia DESC
", [$estudiante_id]);

// Obtener contactos de emergencia
$contactos_emergencia = $db->fetchAll("
    SELECT * FROM contactos_emergencia WHERE estudiante_id = ?
", [$estudiante_id]);

// Estadísticas del estudiante
$stats = [];
$stats['llamados_total'] = $db->fetch("SELECT COUNT(*) as total FROM llamados_atencion WHERE estudiante_id = ?", [$estudiante_id])['total'];
$stats['llamados_mes'] = $db->fetch("SELECT COUNT(*) as total FROM llamados_atencion WHERE estudiante_id = ? AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())", [$estudiante_id])['total'];
?>

<section class="ficha-estudiante">
    <div class="section-header">
        <h2>Ficha del Estudiante</h2>
        <div class="header-actions">
            <a href="estudiantes.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="llamados.php?action=nuevo&estudiante=<?php echo $estudiante['id']; ?>" class="btn btn-danger">
                <i class="fas fa-exclamation-triangle"></i> Nuevo Llamado
            </a>
            <?php if ($auth->hasRole('admin') || $auth->hasRole('directivo')): ?>
            <a href="notas.php?estudiante=<?php echo $estudiante['id']; ?>" class="btn btn-primary">
                <i class="fas fa-clipboard-check"></i> Ver Notas
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Información personal -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información Personal</h3>
        </div>
        <div class="card-body">
            <div class="student-profile">
                <div class="profile-photo">
                    <?php if ($estudiante['foto']): ?>
                        <img src="<?php echo htmlspecialchars($estudiante['foto']); ?>" alt="Foto del estudiante">
                    <?php else: ?>
                        <div class="default-photo">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($estudiante['apellido'] . ', ' . $estudiante['nombre']); ?></h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>DNI:</strong>
                            <span><?php echo htmlspecialchars($estudiante['dni']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Fecha de Nacimiento:</strong>
                            <span>
                                <?php if ($estudiante['fecha_nacimiento']): ?>
                                    <?php echo date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])); ?>
                                    (<?php echo floor((time() - strtotime($estudiante['fecha_nacimiento'])) / (365.25 * 24 * 3600)); ?> años)
                                <?php else: ?>
                                    No registrada
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <strong>Curso:</strong>
                            <span>
                                <?php if ($estudiante['anio']): ?>
                                    <?php echo $estudiante['anio'] . '° ' . $estudiante['division'] . ' - ' . $estudiante['especialidad']; ?>
                                    <small>(<?php echo $estudiante['turno']; ?>)</small>
                                <?php else: ?>
                                    <span class="status status-warning">Sin asignar</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <strong>Grupo Sanguíneo:</strong>
                            <span><?php echo $estudiante['grupo_sanguineo'] ?: 'No registrado'; ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Obra Social:</strong>
                            <span><?php echo htmlspecialchars($estudiante['obra_social']) ?: 'No registrada'; ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Fecha de Ingreso:</strong>
                            <span><?php echo $estudiante['fecha_ingreso'] ? date('d/m/Y', strtotime($estudiante['fecha_ingreso'])) : 'No registrada'; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="contact-info">
                <h4>Información de Contacto</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Domicilio:</strong>
                        <span><?php echo htmlspecialchars($estudiante['domicilio']) ?: 'No registrado'; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Teléfono Fijo:</strong>
                        <span><?php echo htmlspecialchars($estudiante['telefono_fijo']) ?: 'No registrado'; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Teléfono Celular:</strong>
                        <span><?php echo htmlspecialchars($estudiante['telefono_celular']) ?: 'No registrado'; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Email:</strong>
                        <span><?php echo htmlspecialchars($estudiante['email']) ?: 'No registrado'; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['llamados_total']; ?></h3>
                <p>Total Llamados</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['llamados_mes']; ?></h3>
                <p>Llamados Este Mes</p>
            </div>
        </div>
    </div>

    <div class="grid" style="grid-template-columns: 1fr; gap: 2rem;">
        <!-- Llamados de atención recientes -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Llamados de Atención Recientes</h3>
                <a href="llamados.php?estudiante=<?php echo $estudiante['id']; ?>" class="btn btn-sm btn-secondary">Ver todos</a>
            </div>
            <div class="card-body">
                <?php if (!empty($llamados)): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Motivo</th>
                                <th>Sanción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($llamados, 0, 10) as $llamado): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($llamado['fecha'])); ?></td>
                                <td><?php echo htmlspecialchars(substr($llamado['motivo'], 0, 40)) . (strlen($llamado['motivo']) > 40 ? '...' : ''); ?></td>
                                <td>
                                    <?php if ($llamado['sancion']): ?>
                                        <span class="status status-warning"><?php echo htmlspecialchars($llamado['sancion']); ?></span>
                                    <?php else: ?>
                                        <span class="status status-success">Sin sanción</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-center" style="color: var(--secondary-color); padding: 2rem;">No hay llamados registrados</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Responsables y Contactos -->
    <?php if (!empty($responsables) || !empty($contactos_emergencia)): ?>
    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Responsables -->
        <?php if (!empty($responsables)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Responsables</h3>
            </div>
            <div class="card-body">
                <?php foreach ($responsables as $responsable): ?>
                <div class="responsable-item" style="padding: 1rem; border: 1px solid var(--medium-gray); border-radius: var(--border-radius); margin-bottom: 1rem;">
                    <h4 style="margin-bottom: 0.5rem;">
                        <?php echo htmlspecialchars($responsable['apellido'] . ', ' . $responsable['nombre']); ?>
                        <?php if ($responsable['es_contacto_emergencia']): ?>
                            <span class="status status-success" style="font-size: 0.75rem;">Contacto de Emergencia</span>
                        <?php endif; ?>
                    </h4>
                    <div class="info-grid" style="font-size: 0.875rem;">
                        <div><strong>DNI:</strong> <?php echo htmlspecialchars($responsable['dni']); ?></div>
                        <div><strong>Parentesco:</strong> <?php echo htmlspecialchars($responsable['parentesco']); ?></div>
                        <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($responsable['telefono_celular'] ?: $responsable['telefono_fijo']); ?></div>
                        <div><strong>Ocupación:</strong> <?php echo htmlspecialchars($responsable['ocupacion']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Contactos de emergencia -->
        <?php if (!empty($contactos_emergencia)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Contactos de Emergencia</h3>
            </div>
            <div class="card-body">
                <?php foreach ($contactos_emergencia as $contacto): ?>
                <div class="contacto-item" style="padding: 1rem; border: 1px solid var(--medium-gray); border-radius: var(--border-radius); margin-bottom: 1rem;">
                    <h4 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($contacto['nombre']); ?></h4>
                    <div class="info-grid" style="font-size: 0.875rem;">
                        <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($contacto['telefono']); ?></div>
                        <div><strong>Parentesco:</strong> <?php echo htmlspecialchars($contacto['parentesco']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</section>

<style>
.student-profile {
    display: flex;
    gap: 2rem;
    margin-bottom: 2rem;
}

.profile-photo {
    flex-shrink: 0;
}

.profile-photo img,
.default-photo {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--primary-color);
}

.default-photo {
    background: var(--light-gray);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: var(--secondary-color);
}

.profile-info h2 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-item strong {
    color: var(--text-color);
    font-size: 0.875rem;
}

.info-item span {
    color: var(--secondary-color);
}

.contact-info {
    border-top: 1px solid var(--medium-gray);
    padding-top: 2rem;
}

.contact-info h4 {
    color: var(--text-color);
    margin-bottom: 1rem;
    font-size: 1.125rem;
}

.header-actions {
    display: flex;
    gap: 1rem;
}

@media (max-width: 768px) {
    .student-profile {
        flex-direction: column;
        text-align: center;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .header-actions {
        flex-direction: column;
    }
    
    .grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
