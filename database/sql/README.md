# Instalación de bases de datos

La plataforma usa solamente dos bases MySQL o MariaDB:

- pos_core: POS y e-commerce.
- administracion: empresas, servicios, planes, pagos y usuarios de plataforma.

POS y e-commerce comparten la base pos_core, pero cada aplicación registra sus
migraciones en una tabla distinta: migrations para POS y ecommerce_migrations
para e-commerce. Administración usa administracion_migrations.

## Instalación nueva con terminal

1. Ejecuta 00_crear_bases_datos.sql desde phpMyAdmin o MySQL.
2. Copia y configura el archivo .env de cada aplicación.
3. En Administración define ADMIN_INITIAL_NAME, ADMIN_INITIAL_EMAIL y
   ADMIN_INITIAL_PASSWORD para crear la primera cuenta de plataforma.
4. Ejecuta las migraciones en este orden:

       cd administracion && php artisan migrate --seed --force
       cd ../pos-operativo && php artisan migrate --seed --force
       cd ../ecommerce && php artisan migrate --force

Administración debe instalarse primero porque es la fuente de compañías.
Las migraciones crean todas las tablas necesarias para cada aplicación.

## Importación de una instalación existente sin terminal

1. Ejecuta 00_crear_bases_datos.sql.
2. Importa el respaldo o esquema actual del POS en pos_core.
3. Selecciona pos_core e importa 01_pos_core_ecommerce.sql.
4. Selecciona administracion e importa 02_administracion.sql.
5. Configura los archivos .env y limpia la caché de cada aplicación:

       php artisan config:clear
       php artisan config:cache
       php artisan route:cache
       php artisan view:cache

Los scripts registran las migraciones que ya representan. No ejecutes migrate
inmediatamente después de importarlos. Para una actualización posterior usa
migrate --force solamente si llegó una versión con migraciones nuevas.

01_pos_core_ecommerce.sql requiere que las tablas actuales del POS existan
porque añade campos a customers y sales. No debe importarse en una base vacía.

Si 02_administracion.sql se importó por error dentro de saturnext, primero
ejecuta las migraciones de Administración y e-commerce y después importa
03_eliminar_administracion_de_saturnext.sql sobre saturnext.
