# Documentación Técnica (Código)

## Autenticación y Autorización
- `includes/auth.php`: clase `Auth` maneja login y roles. Guarda `$_SESSION['rol']` con valores: `admin`, `directivo`, `preceptor`, `secretaria`.
- Protecciones:
  - `requireLogin()` en `includes/header.php` asegura sesión activa.
  - En cada módulo sensible se valida con `hasRole()`.

## Acceso a Datos
- `config/database.php` expone un singleton `Database` con métodos `query`, `fetch`, `fetchAll` y `lastInsertId` usando PDO con prepared statements.
- Errores se propagan con excepciones.

## Esquema SQL (resumen)
- Usuarios: `usuarios(rol)`.
- Cursos: `cursos(anio, division, turno_id, especialidad_id, grado)`.
- Estudiantes: `estudiantes(curso_id, ... )`.
- Materias: `materias(especialidad_id, es_taller, activa)`.
- Horarios: `horarios(curso_id, materia_id, dia_semana, hora_inicio, hora_fin, aula, docente, es_contraturno)`; trigger de conflicto en `schema.sql`.
- Llamados: `llamados_atencion(estudiante_id, fecha, motivo, sancion, observaciones, usuario_id)`.
- Notas: `notas(estudiante_id, materia_id, trimestre, nota, observaciones, usuario_id)` con UNIQUE `(estudiante_id, materia_id, trimestre)`.
- Materias Previas: `materias_previas(estudiante_id, materia_id, anio_previo, estado)`.

## Módulos Nuevos
- `notas.php`:
  - Alta/baja de notas (solo admin/directivo).
  - Filtros por curso, estudiante, materia y trimestre.
  - Listado con contexto (curso, estudiante, materia).
- `materias.php`:
  - Crear y desactivar materias. Campo `es_taller` para distinguir talleres académicos.
- `materias_previas.php`:
  - Registrar/eliminar previas por estudiante y materia.
- `especialidades.php` y `talleres.php`:
  - Altas y bajas (desactivar) con relación opcional a especialidad.

## Cambios Importantes
- Se eliminaron `inasistencias.php` y `export_inasistencias.php`. Se depuró la navegación y los reportes para enfocarlos en Llamados.
- `index.php` ahora muestra métrica de notas recientes y acciones rápidas relevantes.
- `reportes.php` queda enfocado en Llamados y habilitado para `preceptor`.
- En `estudiantes.php`, `cursos.php` y `estudiante_ficha.php` se reemplazaron accesos a inasistencias por accesos a Notas (donde aplique por rol).

## Estilos/UI
- `css/style.css` define paleta y componentes (cards, tablas, formularios) con responsive. La navegación se controla con un botón hamburguesa móvil (`includes/header.php`).

## Extensión/Modularidad
- Para agregar un nuevo módulo:
  1. Crear `modulo.php` siguiendo el patrón: leer filtros, permisos, realizar CRUD con PDO y renderizar vistas con componentes existentes.
  2. Añadir enlace en `includes/header.php` dentro del bloque de roles que aplique.
  3. Si requiere tablas nuevas, ampliar `database/schema.sql`.

## Notas de Seguridad
- Todos los inputs se envían por POST/GET y se ligan a SQL con parámetros preparados.
- Las salidas en HTML usan `htmlspecialchars` al imprimir contenido de usuario.

## Convenciones
- Comentarios y mensajes en español.
- Estructura de páginas: cabecera (`includes/header.php`) → lógica PHP → bloques `<section>` con tarjetas, formularios, tablas → `includes/footer.php`. 