# CIA - Compañía de Arte y Vida
## Instalación y Configuración

### Requisitos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Extensión PDO de PHP habilitada

---

## Opción 1: XAMPP (Desarrollo Local)

### Paso 1: Instalar XAMPP
1. Descarga XAMPP desde https://www.apachefriends.org/
2. Instala XAMPP con PHP y MySQL seleccionados

### Paso 2: Crear la Base de Datos
1. Abre XAMPP Control Panel
2. Inicia **Apache** y **MySQL**
3. Haz clic en **Admin** de MySQL (se abrirá phpMyAdmin)
4. Crea una nueva base de datos llamada: `cia_arte_vida`
5. Selecciona la base de datos y ve a la pestaña **Importar**
6. Selecciona el archivo `database.sql` y haz clic en **Importar**

### Paso 3: Configurar Credenciales
1. Abre el archivo `config.php`
2. Las credenciales por defecto para XAMPP son:
   ```php
   $host = 'localhost';
   $db   = 'cia_arte_vida';
   $user = 'root';
   $pass = '';  // Contraseña vacía en XAMPP
   ```

### Paso 4: Ejecutar el Proyecto
1. Copia todos los archivos a `C:\xampp\htdocs\cia\`
2. Abre tu navegador y visita: `http://localhost/cia/`

---

## Opción 2: Hosting Web

### Paso 1: Crear Base de Datos
1. Inicia sesión en cPanel
2. Ve a **MySQL Databases**
3. Crea una nueva base de datos: `cia_arte_vida`
4. Crea un usuario MySQL y asígnale todos los privilegios a la base de datos
5. Anota las credenciales (host, usuario, contraseña)

### Paso 2: Importar Estructura
1. Ve a **phpMyAdmin** en cPanel
2. Selecciona la base de datos creada
3. Ve a **Importar** y selecciona `database.sql`

### Paso 3: Configurar config.php
Edita `config.php` con las credenciales de tu hosting:
```php
$host = 'localhost';        // O el host que te proporcione tu proveedor
$db   = 'tu_usuario_cia';    // Nombre de la base de datos
$user = 'tu_usuario';       // Usuario de MySQL
$pass = 'tu_contraseña';    // Contraseña del usuario
```

### Paso 4: Subir Archivos
1. Sube todos los archivos al directorio `public_html` o `www` de tu hosting
2. Asegúrate de que los archivos tengan permisos correctos (644)

---

## Estructura de Archivos

```
cia/
├── cia-arte-vida.html   # Página principal
├── config.php           # Conexión a MySQL
├── api.php              # API REST
├── script.js           # JavaScript
├── styles.css          # Estilos
├── database.sql        # Estructura de BD
└── INSTALL.md          # Este archivo
```

---

## Cómo Usar

### Administrador
1. Ve a la página **Admin** desde el menú
2. Credenciales de acceso:
   - Usuario: `admin`
   - Contraseña: `cia2026`

### Funcionalidades del Admin
- **Crear publicaciones**: Talleres y Shows
- **Ver suscriptores**: Lista de emails suscritos
- **Ver mensajes**: Mensajes de contacto recibidos
- **Eliminar publicaciones**: Borrar contenido

### Suscripciones
Los usuarios se suscriben ingresando su email. Los datos se guardan en la base de datos MySQL (sin enviar correos).

### Contacto
Los mensajes de contacto se guardan en la base de datos. El admin puede verlos desde el panel.

---

## Solución de Problemas

### "Error de conexión a la base de datos"
- Verifica que MySQL esté corriendo
- Revisa las credenciales en `config.php`
- Confirma que la base de datos existe

### "Error 404 al cargar datos"
- Verifica que Apache esté funcionando
- Confirma que los archivos están en la carpeta correcta

### "Las publicaciones no aparecen"
- Verifica que haz importado `database.sql`
- Revisa la consola del navegador para errores

---

## Cambios Realizados

✅ Eliminado: Resend API (envío de correos)  
✅ Eliminado: MailerLite (suscripciones externas)  
✅ Eliminado: localStorage (datos en navegador)  
✅ Agregado: Base de datos MySQL  
✅ Agregado: API PHP REST  
✅ Agregado: Formulario de contacto guardado en DB  

---

¿Necesitas ayuda adicional? Contáctame.

