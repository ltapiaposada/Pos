# POS Laravel 11 (Punto de venta)

Sistema POS web con inventario, ventas, caja, reportes y contabilidad.

## Requisitos
- PHP 8.2+
- Composer
- Node.js 18+
- PostgreSQL

## Instalacion inicial (primera vez)
1. Instalar dependencias PHP:
   - `composer install`
2. Crear archivo de entorno:
   - Windows: `copy .env.example .env`
3. Configurar base de datos en `.env`:
   - `DB_CONNECTION=pgsql`
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=5432`
   - `DB_DATABASE=pos`
   - `DB_USERNAME=postgres`
   - `DB_PASSWORD=secret`
4. Generar llave de aplicacion:
   - `php artisan key:generate`
5. Ejecutar migraciones + datos iniciales (kick off):
   - `php artisan migrate --seed`
6. Instalar dependencias frontend:
   - `npm install`
7. Compilar assets:
   - `npm run build`
8. Iniciar servidor:
   - `php artisan serve`

## Levantar el proyecto (uso diario)
1. Iniciar backend:
   - `php artisan serve`
2. (Opcional para desarrollo visual) Iniciar frontend en vivo:
   - `npm run dev`

## Correr todo desde cero
Usa este comando cuando quieras reconstruir completamente la base de datos:

- `php artisan migrate:fresh --seed`

Esto:
- elimina todas las tablas,
- ejecuta todas las migraciones,
- carga los datos iniciales (kick off).

## Que incluye el kick off
Al correr `migrate --seed` o `migrate:fresh --seed`, se crean datos base:
- Sucursal principal por defecto.
- Categorias base por defecto.
- Roles y permisos base.
- Usuarios por defecto:
  - `admin@pos.test` / `password`
  - `supervisor@pos.test` / `password`
  - `cashier@pos.test` / `password`
- Cliente por defecto:
  - `Consumidor final` con identificacion `222222222222`.

## Configuracion de logo (Cloudinary)
En `.env`:
- `CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME`
- Opcional: `CLOUDINARY_FOLDER=pos`

## Operacion basica
1. Iniciar sesion.
2. Abrir caja.
3. Vender en Punto de venta.
4. Cerrar caja.
5. Revisar facturas y reportes.

## Pruebas
- `php artisan test`

## Solucion de problemas (PostgreSQL)
- Si aparece `could not find driver (pgsql)`, habilita `pdo_pgsql` y `pgsql` en `php.ini`.
- Verifica modulos cargados con:
  - `php -m | findstr /I "pgsql"`
