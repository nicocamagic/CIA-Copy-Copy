# Plan de Transformación: HTML/JS → PHP + MySQL

## Objetivos
1. Eliminar completamente el envío de correos electrónicos (Resend API)
2. Transformar localStorage a base de datos MySQL
3. Crear API PHP para todas las operaciones CRUD
4. Mantener el formulario de contacto (guardando en DB)

## Archivos creados

### 1. Base de Datos (`database.sql`)
- Tabla `publications` (id, page, title, description, image, price, duration, date, tags, created_at)
- Tabla `subscribers` (id, email, created_at)
- Tabla `contact_messages` (id, name, email, message, created_at)

### 2. Backend PHP
- `config.php` - Conexión a MySQL
- `api.php` - Endpoints API REST

### 3. Frontend
- `cia-arte-vida.html` - Actualizado (sin MailerLite)
- `script.js` - Reemplazado localStorage con llamadas fetch a PHP

## Pasos de implementación
- [x] 1. Crear archivo database.sql
- [x] 2. Crear config.php
- [x] 3. Crear api.php
- [x] 4. Actualizar script.js
- [x] 5. Actualizar cia-arte-vida.html
- [x] 6. Instrucciones de instalación

