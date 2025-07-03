# 📚 Cuaderno Digital E.E.S.T N°2

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/HTML)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

> **Sistema Integral de Gestión Escolar** - Una plataforma completa para la administración académica de la Escuela de Educación Secundaria Técnica N°2

## 🎯 Descripción del Proyecto

El **Cuaderno Digital E.E.S.T N°2** es una aplicación web desarrollada en PHP que revoluciona la gestión administrativa de la escuela. Este sistema integral permite a directivos, preceptores y secretarias manejar de manera eficiente toda la información académica, desde el registro de estudiantes hasta la generación de reportes detallados.

### ✨ Características Principales

- 🏫 **Gestión Completa de Estudiantes**: Registro, fichas personales y seguimiento académico
- 📊 **Control de Asistencia**: Registro de inasistencias con justificaciones y certificados médicos
- ⚠️ **Llamados de Atención**: Sistema de seguimiento disciplinario
- 📅 **Horarios Dinámicos**: Gestión de horarios por turno y contraturno
- 👥 **Equipo Directivo**: Información del personal administrativo
- 📈 **Reportes Avanzados**: Generación de estadísticas y exportación de datos
- 🔐 **Sistema de Roles**: Acceso diferenciado por tipo de usuario

## 🚀 Funcionalidades Detalladas

### 📋 Gestión de Estudiantes
- **Registro completo** con datos personales, médicos y de contacto
- **Fichas individuales** con historial académico completo
- **Responsables y contactos de emergencia**
- **Fotos de estudiantes** para identificación rápida
- **Estado activo/inactivo** para control de matrícula

### 📊 Control de Asistencia
- **Registro de inasistencias** por tipo (completa, tarde, retiro anticipado)
- **Justificaciones** con certificados médicos
- **Observaciones detalladas** por parte del personal
- **Historial completo** de faltas por estudiante
- **Exportación de datos** para análisis

### ⚠️ Sistema Disciplinario
- **Llamados de atención** con motivos y sanciones
- **Seguimiento temporal** de incidentes
- **Observaciones detalladas** del personal
- **Reportes de conducta** por estudiante

### 📅 Gestión de Horarios
- **Horarios por curso** y turno
- **Contraturnos** para actividades especiales
- **Asignación de aulas** y docentes
- **Visualización por día** de la semana

### 👥 Equipo Directivo
- **Información del personal** administrativo
- **Cargos y responsabilidades**
- **Datos de contacto** profesionales
- **Fotos del equipo** para identificación

### 📈 Reportes y Estadísticas
- **Dashboard principal** con métricas en tiempo real
- **Estadísticas por turno** (mañana, tarde, contraturno)
- **Reportes de inasistencias** filtrables por fecha
- **Análisis de llamados** de atención
- **Exportación a Excel** para análisis externo

## 🛠️ Tecnologías Utilizadas

### Backend
- **PHP 8.0+** - Lenguaje principal del servidor
- **MySQL 8.0+** - Base de datos relacional
- **PDO** - Acceso seguro a la base de datos
- **Sesiones PHP** - Autenticación y autorización

### Frontend
- **HTML5** - Estructura semántica
- **CSS3** - Estilos y diseño responsivo
- **JavaScript** - Interactividad del cliente
- **Font Awesome** - Iconografía profesional

### Base de Datos
- **MySQL** - Sistema de gestión de base de datos
- **Relaciones complejas** entre entidades
- **Índices optimizados** para consultas rápidas
- **Integridad referencial** garantizada

## 📁 Estructura del Proyecto

```
Cuaderno-Digital2025-main/
├── 📁 config/
│   └── database.php          # Configuración de base de datos
├── 📁 css/
│   └── style.css             # Estilos principales
├── 📁 database/
│   ├── schema.sql            # Esquema de base de datos
│   ├── insert_test_data.sql  # Datos de prueba
│   ├── migrar_anio.sql       # Script de migración
│   └── update_database.sql   # Actualizaciones de BD
├── 📁 img/
│   └── logo-escuela.png      # Logo institucional
├── 📁 includes/
│   ├── auth.php              # Autenticación
│   ├── header.php            # Encabezado común
│   └── footer.php            # Pie de página común
├── 📄 index.php              # Dashboard principal
├── 📄 login.php              # Página de inicio de sesión
├── 📄 logout.php             # Cierre de sesión
├── 📄 estudiantes.php        # Gestión de estudiantes
├── 📄 estudiante_ficha.php   # Ficha individual
├── 📄 cursos.php             # Gestión de cursos
├── 📄 inasistencias.php      # Control de asistencia
├── 📄 llamados.php           # Llamados de atención
├── 📄 horarios.php           # Gestión de horarios
├── 📄 equipo.php             # Equipo directivo
├── 📄 reportes.php           # Generación de reportes
├── 📄 export_inasistencias.php # Exportación de faltas
├── 📄 export_llamados.php    # Exportación de llamados
└── 📄 README.md              # Este archivo
```

## 🗄️ Esquema de Base de Datos

El sistema utiliza una base de datos MySQL con las siguientes entidades principales:

### Tablas Principales
- **`usuarios`** - Personal del sistema con roles diferenciados
- **`estudiantes`** - Información completa de los alumnos
- **`cursos`** - Organización por año, división y turno
- **`inasistencias`** - Registro detallado de faltas
- **`llamados_atencion`** - Seguimiento disciplinario
- **`horarios`** - Programación de clases
- **`equipo_directivo`** - Personal administrativo

### Relaciones Clave
- Estudiantes → Cursos (pertenencia)
- Inasistencias → Estudiantes (registro)
- Llamados → Estudiantes (seguimiento)
- Horarios → Cursos (programación)

## 🚀 Instalación y Configuración

### Requisitos Previos
- **PHP 8.0** o superior
- **MySQL 8.0** o superior
- **Servidor web** (Apache/Nginx)
- **Extensiones PHP**: PDO, PDO_MySQL

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/tu-usuario/cuaderno-digital-eest2.git
   cd cuaderno-digital-eest2
   ```

2. **Configurar la base de datos**
   ```bash
   # Crear base de datos
   mysql -u root -p
   CREATE DATABASE cuaderno_digital_eest2;
   USE cuaderno_digital_eest2;
   
   # Importar esquema
   mysql -u root -p cuaderno_digital_eest2 < database/schema.sql
   
   # Insertar datos de prueba (opcional)
   mysql -u root -p cuaderno_digital_eest2 < database/insert_test_data.sql
   ```

3. **Configurar conexión a BD**
   ```php
   // Editar config/database.php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'cuaderno_digital_eest2');
   define('DB_USER', 'tu_usuario');
   define('DB_PASS', 'tu_password');
   ```

4. **Configurar servidor web**
   - Apuntar el DocumentRoot al directorio del proyecto
   - Asegurar permisos de escritura en directorios necesarios

5. **Acceder al sistema**
   - URL: `http://localhost/cuaderno-digital-eest2`
   - Usuario por defecto: `admin`
   - Contraseña: `admin123`

## 👥 Roles de Usuario

### 🔐 Administrador
- **Acceso completo** a todas las funcionalidades
- **Gestión de usuarios** del sistema
- **Configuración general** de la aplicación

### 👨‍💼 Directivo
- **Dashboard completo** con estadísticas
- **Reportes avanzados** y exportación
- **Gestión de equipo** directivo

### 👩‍🏫 Preceptor
- **Registro de inasistencias** y llamados
- **Gestión de estudiantes** de sus cursos
- **Reportes básicos** de asistencia

### 👩‍💻 Secretaria
- **Registro de estudiantes** nuevos
- **Actualización de datos** personales
- **Generación de constancias**

## 📊 Dashboard Principal

El dashboard proporciona una visión completa del estado de la escuela:

### 📈 Estadísticas en Tiempo Real
- **Total de estudiantes** activos
- **Cantidad de cursos** por turno
- **Faltas del día** actual
- **Llamados recientes** (últimos 7 días)
- **Cumpleaños** del día

### 🚀 Accesos Rápidos
- **Por turno**: Mañana, Tarde, Contraturno
- **Acciones frecuentes**: Registrar inasistencia, nuevo llamado
- **Gestión**: Agregar estudiante, generar reporte

### 📋 Actividad Reciente
- **Últimas inasistencias** registradas
- **Llamados de atención** recientes
- **Notificaciones** importantes

## 🔧 Mantenimiento y Actualizaciones

### Migración de Año Escolar
```sql
-- Ejecutar al finalizar el año lectivo
source database/migrar_anio.sql
```

### Actualizaciones de Base de Datos
```sql
-- Aplicar actualizaciones pendientes
source database/update_database.sql
```

### Respaldo de Datos
```bash
# Crear respaldo completo
mysqldump -u root -p cuaderno_digital_eest2 > backup_$(date +%Y%m%d).sql
```

## 🤝 Contribución

¡Las contribuciones son bienvenidas! Para contribuir al proyecto:

1. **Fork** el repositorio
2. **Crea** una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. **Commit** tus cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. **Push** a la rama (`git push origin feature/nueva-funcionalidad`)
5. **Crea** un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

## 📞 Soporte

Para soporte técnico o consultas sobre el sistema:

- **Email**: soporte@eest2.edu.ar
- **Teléfono**: (223) 1234-5678
- **Horarios**: Lunes a Viernes de 8:00 a 18:00

## 🙏 Agradecimientos

- **Equipo Directivo** de E.E.S.T N°2 por la confianza
- **Personal docente** vecchio por ser buena onda

---

**Desarrollado con ❤️ para la E.E.S.T N°2**

*Sistema de Gestión Escolar - Versión 2025*
