# 🔑 Credenciales del Sistema Cuaderno Digital EEST N°2

## 👥 Usuarios del Sistema

### Administrador del Sistema
- **Usuario:** `admin`
- **Contraseña:** `password`
- **Rol:** Administrador
- **Permisos:** Acceso completo al sistema

### Director
- **Usuario:** `director`
- **Contraseña:** `password`
- **Rol:** Director
- **Permisos:** Gestión completa de la institución

### Preceptor
- **Usuario:** `preceptor`
- **Contraseña:** `password`
- **Rol:** Preceptor
- **Permisos:** Gestión de estudiantes y cursos

### Secretaria
- **Usuario:** `secretaria`
- **Contraseña:** `password`
- **Rol:** Secretaria
- **Permisos:** Gestión administrativa

### Docente de Prueba
- **Usuario:** `docente`
- **Contraseña:** `password`
- **Rol:** Docente
- **Permisos:** Gestión de cursos asignados

## 🚀 Cómo Crear los Usuarios

### Opción 1: Usando phpMyAdmin
1. Ve a `http://localhost/phpmyadmin`
2. Selecciona la base de datos `cuaderno_digital_eest2`
3. Ve a la pestaña "SQL"
4. Copia y pega el contenido del archivo `create_all_users.sql`
5. Ejecuta el script

### Opción 2: Usando Línea de Comandos
```bash
mysql -u root cuaderno_digital_eest2 < create_all_users.sql
```

## 📋 Resumen de Credenciales

| Usuario | Contraseña | Rol | Descripción |
|---------|------------|-----|-------------|
| `admin` | `password` | Administrador | Control total del sistema |
| `director` | `password` | Director | Gestión institucional |
| `preceptor` | `password` | Preceptor | Gestión de estudiantes |
| `secretaria` | `password` | Secretaria | Gestión administrativa |
| `docente` | `password` | Docente | Gestión de cursos |

## 🔒 Seguridad

**⚠️ IMPORTANTE:** Estas son credenciales de desarrollo. Para producción:

1. Cambia todas las contraseñas
2. Usa contraseñas fuertes
3. Implementa autenticación de dos factores
4. Configura HTTPS
5. Revisa los permisos de archivos

## 🛠️ Cambiar Contraseñas

Para cambiar las contraseñas, puedes:

1. **Usar el sistema:** Ir a Perfil → Cambiar Contraseña
2. **Directamente en la base de datos:** Actualizar el campo `password_hash`
3. **Usar el script de cambio:** Crear un script PHP para actualizar contraseñas

## 📞 Soporte

Si tienes problemas con las credenciales:
1. Verifica que la base de datos esté configurada
2. Ejecuta el script `create_all_users.sql`
3. Revisa los logs en `logs/php_errors.log` 