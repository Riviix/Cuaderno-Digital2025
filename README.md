# Cuaderno Digital EEST N°2

Sistema de Gestión Escolar para la Escuela de Educación Secundaria Técnica N°2 "Educación y Trabajo".

## 🚀 Características

- **Gestión de Estudiantes**: Registro y seguimiento completo de estudiantes
- **Control de Inasistencias**: Registro y reportes de faltas
- **Llamados de Atención**: Seguimiento de conductas
- **Reportes**: Generación de informes detallados
- **Gestión de Cursos**: Administración de cursos y divisiones
- **Sistema de Usuarios**: Control de acceso por roles
- **Interfaz Responsiva**: Diseño moderno y adaptable

## 🔒 Mejoras de Seguridad Implementadas

### Autenticación y Autorización
- **Sesiones Seguras**: Regeneración de ID de sesión, timeout automático
- **Rate Limiting**: Protección contra ataques de fuerza bruta
- **Validación de IP**: Verificación de dirección IP en sesiones
- **Tokens CSRF**: Protección contra ataques Cross-Site Request Forgery
- **Hashing Seguro**: Uso de Argon2id para contraseñas

### Protección de Datos
- **Sanitización**: Limpieza automática de datos de entrada
- **Validación**: Verificación estricta de tipos y formatos
- **Escape HTML**: Prevención de XSS
- **Prepared Statements**: Protección contra SQL Injection

### Seguridad del Servidor
- **Headers de Seguridad**: CSP, X-Frame-Options, X-XSS-Protection
- **Protección de Archivos**: Restricción de acceso a archivos sensibles
- **Logs de Seguridad**: Registro de actividades y intentos de acceso
- **Manejo de Errores**: Páginas de error personalizadas

## 📁 Estructura del Proyecto

```
Cuaderno-Digital2025-main/
├── config/                 # Configuración de la aplicación
│   ├── config.php         # Configuración principal
│   ├── database.php       # Conexión a base de datos
│   └── env.example        # Variables de entorno (ejemplo)
├── includes/              # Archivos de inclusión
│   ├── auth.php          # Sistema de autenticación
│   ├── Security.php      # Clase de seguridad
│   ├── Logger.php        # Sistema de logs
│   ├── header.php        # Header común
│   └── footer.php        # Footer común
├── errors/               # Páginas de error
│   ├── 403.php          # Acceso denegado
│   ├── 404.php          # Página no encontrada
│   └── 500.php          # Error interno
├── logs/                 # Archivos de log (generado automáticamente)
├── uploads/              # Archivos subidos (generado automáticamente)
├── css/                  # Estilos CSS
├── img/                  # Imágenes
├── database/             # Scripts de base de datos
├── .htaccess            # Configuración de Apache
├── index.php            # Página principal
├── login.php            # Página de login
├── logout.php           # Cierre de sesión
├── profile.php          # Perfil de usuario
├── check_session.php    # Verificación de sesión (AJAX)
└── README.md            # Este archivo
```

## 🛠️ Instalación

### Requisitos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache con mod_rewrite habilitado
- Extensiones PHP: PDO, PDO_MySQL, OpenSSL

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone [URL_DEL_REPOSITORIO]
   cd Cuaderno-Digital2025-main
   ```

2. **Configurar variables de entorno**
   ```bash
   cp config/env.example config/.env
   # Editar config/.env con tus configuraciones
   ```

3. **Configurar base de datos**
   ```sql
   -- Crear base de datos
   CREATE DATABASE cuaderno_digital_eest2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   
   -- Importar esquema
   mysql -u root -p cuaderno_digital_eest2 < database/schema.sql
   
   -- Insertar datos de prueba (opcional)
   mysql -u root -p cuaderno_digital_eest2 < insert_test_data.sql
   ```

4. **Configurar permisos**
   ```bash
   chmod 755 logs/
   chmod 755 uploads/
   chmod 644 config/.env
   ```

5. **Configurar servidor web**
   - Asegurar que mod_rewrite esté habilitado
   - Configurar DocumentRoot al directorio del proyecto
   - Verificar que .htaccess esté funcionando

## 🔧 Configuración

### Variables de Entorno (.env)

```env
# Base de datos
DB_HOST=localhost
DB_NAME=cuaderno_digital_eest2
DB_USER=root
DB_PASS=

# Aplicación
APP_NAME="Cuaderno Digital EEST N°2"
APP_URL=http://localhost
APP_ENV=development
APP_DEBUG=true

# Seguridad
SESSION_SECRET=your-secret-key-here
PASSWORD_SALT=your-salt-here

# Logs
LOG_LEVEL=info
```

### Roles de Usuario

- **admin**: Acceso completo al sistema
- **directivo**: Acceso a reportes y gestión
- **docente**: Acceso básico a estudiantes y cursos

## 📊 Funcionalidades

### Dashboard
- Estadísticas en tiempo real
- Accesos rápidos por turno
- Notificaciones del sistema
- Actividad reciente

### Gestión de Estudiantes
- Registro completo de datos
- Historial académico
- Seguimiento de inasistencias
- Fichas individuales

### Control de Inasistencias
- Registro diario de faltas
- Justificaciones
- Reportes por período
- Exportación a Excel

### Llamados de Atención
- Registro de conductas
- Seguimiento temporal
- Notificaciones automáticas
- Reportes de comportamiento

### Reportes
- Estadísticas generales
- Reportes por curso
- Exportación en múltiples formatos
- Gráficos y visualizaciones

## 🔍 Monitoreo y Logs

El sistema registra automáticamente:
- Inicios y cierres de sesión
- Intentos de acceso fallidos
- Actividades de usuarios
- Errores de base de datos
- Accesos no autorizados

### Archivos de Log
- `logs/app.log`: Log general de la aplicación
- `logs/failed_logins.log`: Intentos de login fallidos
- `logs/successful_logins.log`: Logins exitosos
- `logs/logouts.log`: Cierres de sesión

## 🚨 Seguridad

### Medidas Implementadas
- ✅ Autenticación segura con rate limiting
- ✅ Protección CSRF en todos los formularios
- ✅ Sanitización y validación de datos
- ✅ Headers de seguridad HTTP
- ✅ Logs de seguridad
- ✅ Timeout de sesión automático
- ✅ Verificación de IP
- ✅ Hashing seguro de contraseñas

### Recomendaciones de Producción
1. Cambiar `SESSION_SECRET` y `PASSWORD_SALT` por valores únicos
2. Configurar `APP_ENV=production`
3. Deshabilitar `APP_DEBUG`
4. Usar HTTPS
5. Configurar backup automático de base de datos
6. Monitorear logs regularmente

## 🤝 Contribución

1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 📞 Soporte

Para soporte técnico o consultas:
- Email: [email@eest2.edu.ar]
- Teléfono: [número de contacto]
- Dirección: [dirección de la escuela]

## 🔄 Actualizaciones

### v2.0.0 (Actual)
- ✅ Sistema de seguridad mejorado
- ✅ Reorganización de archivos
- ✅ Logs de actividad
- ✅ Páginas de error personalizadas
- ✅ Validación mejorada
- ✅ Interfaz de usuario actualizada

### Próximas características
- [ ] API REST para integración
- [ ] Notificaciones push
- [ ] Backup automático
- [ ] Dashboard móvil
- [ ] Integración con sistemas externos

---

**Desarrollado para EEST N°2 "Educación y Trabajo"** 🏫
