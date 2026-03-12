# Remisería SaaS - Sistema de Gestión

Sistema de gestión para remiserías con arquitectura SaaS multi-tenant.

## Características

### Roles de Usuario
- **Super Admin**: Gestión centralizada de todos los tenants (empresas)
- **Admin**: Gestión de remiseros, pasajeros, viajes y reportes por empresa
- **Remisero**: App móvil para registro y seguimiento de viajes

### Módulos

#### Panel Admin
- Dashboard con estadísticas en tiempo real
- Gestión de remiseros (alta, baja, edición, activación/desactivación)
- Gestión de pasajeros (alta, búsqueda, historial)
- Gestión de viajes (local y larga distancia)
- Creación rápida de nuevos viajes
- Reportes con filtros por fecha y remisero

#### Panel Remisero
- Registro de viajes
- Búsqueda de pasajeros
- Control de estado de viaje (buscando, en curso, completado, cancelado)
- Gestión de pagos (efectivo/transferencia)
- Mi perfil

#### Sistema SaaS
- Arquitectura multi-tenant: múltiples empresas en una sola instalación
- Cada tenant tiene su propia URL personalizada (`/empresa/admin`, `/empresa/remisero`)
- Personalización de marca (color principal por empresa)
- Planes: Free, Basic, Pro

### Viajes
- **Local**: Servicio común sin origen/destino obligatorio
- **Larga Distancia**: Con compromiso (origen y destino obligatorios)

### Pagos
- Efectivo
- Transferencia

### Reportes y Exportación
- Exportación a CSV
- Exportación a PDF
- Filtros por fecha y remisero

### Notificaciones
- Sistema de notificaciones en tiempo real
- Alertas de nuevos viajes asignados

## Requisitos

- PHP 8+
- MySQL / MariaDB
- XAMPP o similar

## Instalación

1. Crear base de datos MySQL: `CREATE DATABASE remiseria_db;`
2. Importar `database.sql` en MySQL
3. Configurar `config/database.php` con credenciales
4. Acceso: `http://localhost/remiseria/`
5. Ejecutar setup: `http://localhost/remiseria/setup.php`

## Usuarios por Defecto

- **Super Admin**: `superadmin` / `superadmin123`
- **Demo Admin**: `admin` / `admin`
- **Demo Remisero**: `remisero` / `remisero`

## URLs de Acceso

- Super Admin: `http://localhost/remiseria/superadmin/`
- Login General: `http://localhost/remiseria/`
- Demo Admin: `http://localhost/remiseria/demo/admin/`
- Demo Remisero: `http://localhost/remiseria/demo/remisero/`

## Tech Stack

- PHP 8+
- MySQL
- Bootstrap 5
- Bootstrap Icons
- FPDF (generación de PDFs)
