# Guía de Despliegue en Producción — BanaHosting (cPanel)

Esta guía resume los pasos exactos para subir **MiPromo Egresados Store (Grupo JMSPERU)** a **BanaHosting** en modo producción de forma segura y sin usuarios de prueba.

## 1. Requisitos previos antes de subir archivos
En tu máquina local (ya incluido en este repositorio):
1. Compilar assets de frontend para producción:
   ```bash
   npm install && npm run build
   ```
2. Instalar dependencias PHP optimizadas para producción (si no usarás Composer en cPanel Terminal):
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
3. Asegúrate de que exista `vendor/` y `public/build/`, y **NO subas** tu `.env` local ni `storage/installed.lock`.

## 2. Crear la Base de Datos en cPanel (BanaHosting)
1. Ingresa a **cPanel → Asistente de Bases de Datos MySQL (MySQL Database Wizard)**.
2. Crea una base de datos (ej. `tuusuario_mipromo`) y anota el nombre completo.
3. Crea un usuario MySQL con contraseña segura y asígnale **Todos los Privilegios (ALL PRIVILEGES)** sobre esa base de datos.
4. Verifica en **cPanel → Seleccionar versión de PHP (Select PHP Version)** que tengas **PHP 8.3, 8.4 o 8.5** activo y las extensiones: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `dom`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`, `curl`, `zip`.

## 3. Subir el Proyecto por GitHub o cPanel
- **Opción A (Git Version Control en cPanel / SSH):**
  Clona tu repositorio de GitHub y ejecuta:
  ```bash
  composer install --no-dev --optimize-autoloader
  ```
- **Opción B (Administrador de Archivos / ZIP):**
  Sube el paquete completo (incluyendo `vendor/` y `public/build/`) dentro de `public_html/` (o en la carpeta de tu dominio/subdominio).
  El archivo [.htaccess](.htaccess) en la raíz enruta las peticiones hacia `public/` y bloquea el acceso público a `.env`, `.git`, `storage/` y archivos internos. Si usas un Addon/Subdominio, lo ideal es apuntar su *Document Root* directamente a `/public`.

## 4. Ejecutar el Instalador Web PHP (`install.php`)
1. Abre en tu navegador: `https://tudominio.com/install.php`
2. Verifica que todos los checks del servidor estén en **OK**.
3. Ingresa:
   - Tu URL con `https://`
   - Datos MySQL de BanaHosting (`localhost`, base de datos, usuario, contraseña) y pulsa **Probar Conexión MySQL**.
   - Opcionalmente los datos SMTP de tu correo corporativo creado en cPanel.
   - Los datos reales de tu cuenta **Superadministrador** (correo oficial y contraseña fuerte de al menos 8 caracteres con letras y números).
4. Marca la carga del catálogo base + colegios (sin usuarios de prueba) y haz clic en **Instalar MiPromo en Producción Ahora**.
5. Al finalizar, presiona el botón **Eliminar install.php** (o borra `public/install.php` manualmente desde cPanel). El archivo `storage/installed.lock` además bloquea nuevas ejecuciones con HTTP 403.

## 5. Seguridad y Checklist Post-Instalación
- Verifica `https://tudominio.com` con SSL activo (AutoSSL en cPanel).
- En `.env` verifica `APP_ENV=production` y `APP_DEBUG=false`, y asegúrate de que no exista `public/hot`.
- Entra a `/login` (sin panel de usuarios demo) con tu Superadmin real y revisa `/admin/reportes`, `/admin/proveedores` y `/admin/catalogo`.
- Ingresa tu Token Bearer de `apiperu.dev` en `/admin/configuracion` y valida la conexión usando el probador integrado de DNI.
- Consulta o descarga el Manual Oficial con capturas del sistema en `/manual` y `/manual/pdf`.
