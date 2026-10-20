# Plataforma POS: instalación local en macOS y Windows

Esta carpeta contiene tres aplicaciones Laravel independientes:

| Aplicación | Carpeta | Función |
| --- | --- | --- |
| Administración | administracion | Empresas cliente, servicios, planes, suscripciones, pagos y aprovisionamiento. |
| POS operativo | pos-operativo | Sucursales, usuarios operativos, inventario, ventas, caja, compras y contabilidad. |
| E-commerce | ecommerce | Tienda pública por subdominio, catálogo, carrito y pedidos web. |

## Arquitectura de bases de datos

La plataforma usa únicamente dos bases MySQL o MariaDB:

| Base | Aplicaciones que la usan | Tablas principales |
| --- | --- | --- |
| pos_core | POS y e-commerce | Usuarios POS, productos, inventario, ventas, caja y tablas ecommerce_. |
| administracion | Administración | Empresas, servicios, planes, suscripciones, pagos y aprovisionamientos. |

POS y e-commerce comparten la misma base pos_core, pero no mezclan sus tablas:

- POS registra sus migraciones en la tabla migrations.
- E-commerce registra sus migraciones en ecommerce_migrations.
- Administración registra sus migraciones en administracion_migrations.

No cree una tercera base para e-commerce.

## Requisitos

- PHP 8.2 o superior, con extensiones pdo_mysql, mbstring, openssl, curl,
  fileinfo, xml y tokenizer. En Windows puede instalarlo mediante Laragon,
  XAMPP o una instalación de PHP que quede disponible en PowerShell.
- Composer 2.
- MySQL 8 o MariaDB compatible y en ejecución. En macOS puede usar el motor
  que ya tenga instalado; en Windows, Laragon o XAMPP incluyen MySQL y
  phpMyAdmin.
- Node.js 18 o superior y npm, para compilar los estilos y JavaScript.
- Acceso a una terminal y a un servidor MySQL local. Puede crear las bases
  desde phpMyAdmin local o desde la consola MySQL.

No se necesitan Docker, Redis, workers, Supervisor ni colas. Las tres
aplicaciones usan QUEUE_CONNECTION=sync.

## Instalación nueva paso a paso

### 1. Obtener el proyecto

Copie o clone el repositorio y entre a la carpeta raíz.

macOS, usando Terminal:

    cd /ruta/donde/guardaste/Pos

Ejemplo para esta copia del proyecto:

    cd /Users/luisdavidtapiaposada/Documents/GitHub/Pos

Windows, usando PowerShell:

    cd C:\ruta\donde\guardaste\Pos

### 2. Crear las dos bases de datos

En phpMyAdmin local, abra la pestaña SQL, pegue el contenido del archivo y
ejecútelo:

    database/sql/00_crear_bases_datos.sql

Esto crea pos_core y administracion. Si su servidor MySQL local usa otro
usuario, contraseña o puerto, use esos mismos datos en los archivos .env.

No ejecute los archivos 01_pos_core_ecommerce.sql ni 02_administracion.sql
para una instalación nueva con terminal: las migraciones Laravel crean todas
las tablas. Esos SQL se conservan para importar una instalación existente sin
terminal.

### 3. Crear los archivos de configuración

macOS, usando Terminal:

    cp administracion/.env.example administracion/.env
    cp pos-operativo/.env.example pos-operativo/.env
    cp ecommerce/.env.example ecommerce/.env

Windows, usando PowerShell:

    Copy-Item administracion/.env.example administracion/.env
    Copy-Item pos-operativo/.env.example pos-operativo/.env
    Copy-Item ecommerce/.env.example ecommerce/.env

También puede hacerlo desde el Explorador: copie cada archivo .env.example y
renómbrelo como .env.

### 4. Configurar Administración

Abra administracion/.env y defina como mínimo:

    APP_NAME=Administracion
    APP_ENV=local
    APP_DEBUG=true
    APP_URL=http://admin.localhost:8002
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=administracion
    DB_USERNAME=root
    DB_PASSWORD=
    MIGRATIONS_TABLE=administracion_migrations
    POS_DB_CONNECTION=mysql
    POS_DB_HOST=127.0.0.1
    POS_DB_PORT=3306
    POS_DB_DATABASE=pos_core
    POS_DB_USERNAME=root
    POS_DB_PASSWORD=
    ADMIN_INITIAL_NAME=Administrador de plataforma
    ADMIN_INITIAL_EMAIL=tu-correo@dominio.com
    ADMIN_INITIAL_PASSWORD=UnaClaveSeguraYUnica
    QUEUE_CONNECTION=sync
    SESSION_DRIVER=file
    CACHE_STORE=file

La cuenta indicada en ADMIN_INITIAL_EMAIL se crea al ejecutar la migración con
seed. Esta cuenta entra a Administración; no es un usuario del POS.

### 5. Configurar POS

Abra pos-operativo/.env y configure:

    APP_NAME=POS
    APP_ENV=local
    APP_DEBUG=true
    APP_URL=http://pos.localhost:8000
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=pos_core
    DB_USERNAME=root
    DB_PASSWORD=
    ADMIN_DB_CONNECTION=mysql
    ADMIN_DB_HOST=127.0.0.1
    ADMIN_DB_PORT=3306
    ADMIN_DB_DATABASE=administracion
    ADMIN_DB_USERNAME=root
    ADMIN_DB_PASSWORD=
    ECOMMERCE_API_URL=http://tienda.localhost:8001
    QUEUE_CONNECTION=sync
    SESSION_DRIVER=file
    CACHE_STORE=file
    SEED_DEMO_DATA=false

Para crear un administrador POS automáticamente cuando ya exista una empresa,
complete también:

    POS_INITIAL_ADMIN_NAME=Administrador POS
    POS_INITIAL_ADMIN_EMAIL=admin@empresa.com
    POS_INITIAL_ADMIN_PASSWORD=UnaClaveSeguraYUnica

En una instalación nueva, la empresa se crea desde Administración. No cree
empresas ni suscripciones directamente en el POS.

### 6. Configurar E-commerce

Abra ecommerce/.env y configure:

    APP_NAME=Ecommerce
    APP_ENV=local
    APP_DEBUG=true
    APP_URL=http://tienda.localhost:8001
    STOREFRONT_BASE_DOMAIN=localhost
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=pos_core
    DB_USERNAME=root
    DB_PASSWORD=
    MIGRATIONS_TABLE=ecommerce_migrations
    ADMIN_DB_CONNECTION=mysql
    ADMIN_DB_HOST=127.0.0.1
    ADMIN_DB_PORT=3306
    ADMIN_DB_DATABASE=administracion
    ADMIN_DB_USERNAME=root
    ADMIN_DB_PASSWORD=
    POS_API_URL=http://pos.localhost:8000
    QUEUE_CONNECTION=sync
    SESSION_DRIVER=file
    CACHE_STORE=file

### 7. Configurar la clave de integración interna

Las tres aplicaciones deben compartir exactamente el mismo valor largo y
aleatorio:

    INTERNAL_API_TOKEN=coloca-aqui-un-token-largo-y-secreto

En POS agregue además:

    OUTBOX_SIGNING_SECRET=otro-secreto-largo-y-distinto

Guárdelos solamente en los archivos .env; no los suba al repositorio.

### 8. Instalar dependencias y ejecutar las migraciones

El orden es obligatorio: Administración, POS y E-commerce. Administración debe
existir antes de POS porque contiene la fuente de verdad de las empresas.

Administración:

    cd administracion
    composer install
    php artisan key:generate
    php artisan migrate --seed
    npm install
    npm run build

POS:

    cd ../pos-operativo
    composer install
    php artisan key:generate
    php artisan migrate --seed
    npm install
    npm run build

E-commerce:

    cd ../ecommerce
    composer install
    php artisan key:generate
    php artisan migrate
    npm install
    npm run build

Si npm muestra que no encuentra un comando, compruebe primero que Node.js y
npm sean reconocidos por la misma Terminal o PowerShell con:

    php -v
    composer --version
    node -v
    npm -v

### 9. Verificar las migraciones

Desde la carpeta raíz de Pos, ejecute estos comandos; todas las migraciones
deben aparecer como Ran:

    cd administracion && php artisan migrate:status
    cd ../pos-operativo && php artisan migrate:status
    cd ../ecommerce && php artisan migrate:status

También puede ejecutar las pruebas desde la carpeta raíz:

    cd administracion && php artisan test
    cd ../ecommerce && php artisan test
    cd ../pos-operativo && php artisan test

### 10. Crear la primera empresa

1. Inicie Administración.
2. Entre con ADMIN_INITIAL_EMAIL y ADMIN_INITIAL_PASSWORD.
3. Cree los servicios y planes si aún no existen.
4. Cree una empresa.
5. Registre y marque como pagada la suscripción que corresponda.
6. Active POS y, si aplica, E-commerce desde Aplicaciones.
7. Use Aprovisionamiento para crear o reintentar la configuración de cada
   aplicación.
8. Cree o revise los usuarios operativos de la empresa.

Administración controla empresas, pagos, suscripciones y habilitaciones. El
POS solo opera los datos de cada empresa habilitada.

## Ejecutar localmente

Abra tres Terminales en macOS o tres ventanas de PowerShell en Windows. Los
comandos son los mismos en ambos sistemas; cambie /ruta/Pos por la ruta real
del proyecto.

Terminal Administración:

    cd /ruta/Pos/administracion
    php artisan serve --host=127.0.0.1 --port=8002

Terminal POS:

    cd /ruta/Pos/pos-operativo
    php artisan serve --host=127.0.0.1 --port=8000

Terminal E-commerce:

    cd /ruta/Pos/ecommerce
    php artisan serve --host=127.0.0.1 --port=8001

Direcciones locales:

| Aplicación | Dirección |
| --- | --- |
| Administración | http://admin.localhost:8002 |
| POS | http://pos.localhost:8000 |
| E-commerce | http://tienda.localhost:8001 |

Para desarrollo visual puede ejecutar npm run dev dentro de la aplicación que
esté modificando.

## Correo local y restablecimiento de contraseña

Las pantallas de restablecimiento funcionan sin una configuración adicional,
pero para que el enlace llegue a un correo real debe existir un programa de
envío configurado en cada .env. No copie las contraseñas de correo en el
README ni en el repositorio.

Para probar el flujo sin enviar mensajes reales, cambie temporalmente en las
tres aplicaciones:

    MAIL_MAILER=log

Luego solicite el restablecimiento y abra el archivo storage/logs/laravel.log
de la aplicación correspondiente. Ahí estará la URL de prueba.

Para enviar mensajes reales desde su Mac, conserve la configuración ya creada
con msmtp:

    MAIL_MAILER=sendmail
    MAIL_SENDMAIL_PATH="/opt/homebrew/bin/msmtp -t"
    MAIL_FROM_ADDRESS="no-reply@adanis.net"
    MAIL_FROM_NAME="${APP_NAME}"

En Windows, configure un programa compatible con sendmail en el equipo y use
su ruta en MAIL_SENDMAIL_PATH, o deje MAIL_MAILER=log mientras desarrolla.
Después de cambiar cualquier valor MAIL_, ejecute en esa aplicación:

    php artisan optimize:clear

## Probar subdominios de E-commerce en local

Solo e-commerce identifica la empresa según el host o subdominio. POS y
Administración siempre tienen una URL fija.

macOS y las versiones actuales de Windows resuelven los dominios terminados
en .localhost hacia su propio equipo. Por ejemplo, después de registrar el
dominio local mi-tienda.localhost para una empresa desde Administración, abra:

    http://mi-tienda.localhost:8001

POS y Administración permanecen en sus URL fijas. Si el navegador o una
herramienta no resuelve un nombre .localhost, añada una línea al archivo hosts
con permisos de administrador. En macOS es /etc/hosts; en Windows es
C:\Windows\System32\drivers\etc\hosts:

    127.0.0.1 mi-tienda.localhost

## Actualizar una instalación local existente

No use migrate:fresh en una instalación con datos reales: elimina las tablas.

1. Haga un respaldo local de pos_core y administracion.
2. Actualice los archivos del proyecto.
3. Actualice Composer y compile los recursos en cada aplicación.
4. Ejecute las migraciones en este orden:

       cd administracion && php artisan migrate
       cd ../pos-operativo && php artisan migrate
       cd ../ecommerce && php artisan migrate

5. Limpie y regenere la caché de cada aplicación:

       php artisan optimize:clear
       php artisan config:cache
       php artisan route:cache
       php artisan view:cache

6. Verifique con php artisan migrate:status.

Si migrate indica Nothing to migrate, confirme que está dentro de la carpeta
correcta, que el proyecto fue actualizado y que el archivo .env apunta a la
base de datos esperada.

## Importar una base local existente sin usar migraciones

Los scripts están en database/sql:

| Archivo | Uso |
| --- | --- |
| 00_crear_bases_datos.sql | Crea pos_core y administracion. |
| 01_pos_core_ecommerce.sql | Añade la estructura e-commerce a un POS existente. |
| 02_administracion.sql | Crea la estructura administrativa completa. |
| 03_eliminar_administracion_de_saturnext.sql | Solo corrige una importación administrativa hecha por error dentro de saturnext. |

Lea database/sql/README.md antes de importarlos. Los archivos 01 y 02 no son
para una base vacía cuando sí tiene acceso a las migraciones Laravel.

## Problemas comunes

### No se ejecuta ninguna migración

Ejecute php artisan migrate:status. Si todo aparece como Ran, la base ya está
actualizada. Si faltan archivos de migración, actualice el proyecto antes de
ejecutar migrate.

### La base no existe

Ejecute database/sql/00_crear_bases_datos.sql y compruebe que DB_DATABASE en
cada .env coincide con el nombre creado por MySQL local.

### Error de conexión MySQL

Revise DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME y DB_PASSWORD. Compruebe
que MySQL esté iniciado y que el puerto local sea el mismo que usa su
herramienta de base de datos.

### Error de permiso o cuenta sin acceso al POS

Los usuarios del POS deben tener uno de estos roles: admin, supervisor o
cashier. La administración global de empresas no existe dentro del POS; se
realiza desde Administración.

### Error de caché después de modificar .env

Dentro de la aplicación afectada ejecute:

    php artisan optimize:clear
    php artisan config:cache
