# Cuaderno Digital - E.E.S.T. N°2

Sistema simple de gestión escolar y digitalización del cuaderno de comunicaciones. Construido en PHP (PDO) y MySQL.

## Requisitos
- PHP 8.x
- MySQL/MariaDB (XAMPP recomendado)
- Extensión PDO habilitada

## Instalación
1. Clonar o copiar el proyecto en `C:\xampp\htdocs\StudentMonitor`.
2. Crear la base de datos ejecutando el SQL:
   - Importar `database/schema.sql` en MySQL (phpMyAdmin o consola).
3. Configurar credenciales si no usás XAMPP por defecto, en `config/database.php`.
4. Iniciar Apache y MySQL en XAMPP.
5. Abrir en el navegador: `http://localhost/StudentMonitor/login.php`.

Usuario inicial:
- Usuario: `admin`
- Contraseña: `password`

## Roles y permisos
- Director/Admin: puede crear/editar/eliminar Cursos, Materias, Talleres, Especialidades, Horarios, Notas y Materias Previas. Accede a todos los módulos.
- Preceptor/Secretaría: puede registrar y ver Llamados y consultar Reportes; ver estudiantes y cursos.

## Módulos
- Dashboard
- Cursos (Años 1 a 7, divisiones A–E; a partir de 4° con especialidad)
- Estudiantes
- Horarios (Lunes a Viernes; valida solapamientos)
- Llamados de Atención (con exportación CSV)
- Notas (por estudiante/materia y trimestre; CRUD)
- Materias (CRUD)
- Materias Previas (CRUD)
- Especialidades (CRUD)
- Talleres (CRUD)

## Cambios clave de esta versión
- Se quitó Inasistencias. Se simplificó la navegación y los reportes.
- Se añadieron Notas, Materias, Materias Previas, Especialidades y Talleres con control por rol.
- Dashboard con métrica de notas de los últimos 7 días.

## Estructura
- `config/database.php`: conexión PDO.
- `includes/auth.php`: sesiones y roles.
- `includes/header.php` / `includes/footer.php`: layout y navegación.
- Páginas de cada módulo: `cursos.php`, `estudiantes.php`, `horarios.php`, `llamados.php`, `notas.php`, `materias.php`, `materias_previas.php`, `especialidades.php`, `talleres.php`, `reportes.php`.

## Exportaciones
- Llamados: `export_llamados.php` → CSV.

## Estilos
- `css/style.css` con UI tipo school management, responsiva.

## Seguridad
- Todas las consultas usan sentencias preparadas.
- Acceso por rol vía `includes/auth.php`.

## Soporte
Ante dudas abrir un issue o contactarse con el mantenedor del proyecto. 