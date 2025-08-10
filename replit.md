# Digital School Management System (E.E.S.T N°2)

Nota: Esta versión simplifica el sistema quitando Inasistencias y agregando módulos CRUD: Notas, Materias, Materias Previas, Especialidades y Talleres. Reportes ahora se centra en Llamados y está habilitado para Preceptor. Ver `README.md` y `README_CODE.md` para detalles.

## Overview

The Cuaderno Digital E.E.S.T N°2 is a comprehensive school management web application built with PHP and MySQL. This system serves as an integral platform for academic administration at the Technical Secondary Education School N°2. It provides complete functionality for managing students, attendance tracking, disciplinary actions, schedule management, and administrative reporting. The system supports role-based access for different types of users including administrators, teachers, and secretarial staff.

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Frontend Architecture
- **Technology Stack**: HTML5, CSS3, and vanilla JavaScript for client-side interactions
- **UI Framework**: Custom CSS with CSS Grid and Flexbox for responsive layouts
- **Design System**: CSS custom properties (variables) for consistent theming and styling
- **Styling Approach**: Component-based CSS architecture with modular stylesheets

### Backend Architecture
- **Core Technology**: PHP 8.0+ for server-side logic and business rules
- **Architecture Pattern**: MVC (Model-View-Controller) pattern for code organization
- **Session Management**: PHP sessions for user authentication and state management
- **File Structure**: Modular approach with separate directories for different functionality areas

### Database Design
- **Database System**: MySQL 8.0+ for data persistence
- **Schema Design**: Relational database with normalized tables for students, attendance, disciplinary records, schedules, and staff information
- **Data Relationships**: Foreign key constraints to maintain referential integrity between related entities
- **Storage Features**: Support for file uploads including student photos and medical certificates

### Authentication & Authorization
- **Access Control**: Role-based permission system with different access levels
- **User Types**: Support for administrators, teachers, preceptors, and secretarial staff
- **Session Security**: Server-side session management for secure user authentication
- **Permission Granularity**: Feature-level access control based on user roles

### Core Modules
- **Student Management**: Complete student lifecycle from registration to academic tracking
- **Attendance System**: Comprehensive absence tracking with justification workflows
- **Disciplinary System**: Warning and sanction management with detailed incident reporting
- **Schedule Management**: Dynamic timetable creation for regular and after-hours classes
- **Reporting Engine**: Data export capabilities and statistical report generation
- **Staff Directory**: Management of administrative and teaching staff information

## External Dependencies

### Database Services
- **MySQL Database**: Primary data storage for all application data including student records, attendance logs, and administrative information

### File Storage
- **Local File System**: Storage for uploaded documents, student photographs, and medical certificates
- **Image Processing**: Basic image handling for student photo management

### Development Tools
- **PHP Runtime**: Server-side execution environment with modern PHP features
- **Web Server**: Compatible with Apache or Nginx for HTTP request handling
- **Browser Compatibility**: Modern web browser support for HTML5 and CSS3 features

### Potential Integrations
- **Email Services**: For notification systems and communication with parents/guardians
- **Backup Systems**: For database and file backup management
- **Authentication Services**: Potential integration with school district authentication systems
- **Reporting Tools**: Export capabilities to common formats (PDF, Excel) for administrative reports