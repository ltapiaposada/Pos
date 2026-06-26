# POS Laravel 11 (Punto de venta)

Sistema POS web con inventario, ventas, caja, reportes y contabilidad.

## Documentacion operativa y comercial
- [Instalacion](docs/INSTALLATION.md)
- [Backup y restauracion](docs/BACKUP-RESTORE.md)
- [Checklist de salida a produccion](docs/GO-LIVE-CHECKLIST.md)
- [Despliegue base con Docker](docs/DEPLOYMENT.md)
- [Alcance comercial del producto](docs/PRODUCT-SCOPE.md)
- [Planes y soporte](docs/PLANS-AND-SUPPORT.md)
- [Licencia comercial base](LICENSE.md)
- [Terminos base](TERMS.md)
- [Politica base de privacidad](PRIVACY.md)

## Requisitos
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8+

## Instalacion inicial (primera vez)
1. Instalar dependencias PHP:
   - `composer install`
2. Crear archivo de entorno:
   - Windows: `copy .env.example .env`
3. Configurar base de datos en `.env`:
   - `DB_CONNECTION=mysql`
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=pos`
   - `DB_USERNAME=root`
   - `DB_PASSWORD=secret`
   - `SEED_DEMO_DATA=true`
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
- Contactos operativos base:
  - `Cliente Mostrador` con identificacion `CF`.
- Si `SEED_DEMO_DATA=true`, tambien se crean:
  - `admin@pos.test` / `password`
  - `supervisor@pos.test` / `password`
  - `cashier@pos.test` / `password`
  - `Empresa Demo` con identificacion `NIT-123456`.
  - `Proveedor Base` con identificacion `NIT-PROV-001`.
  - catalogo demo e inventario de ejemplo
- Si `SEED_DEMO_DATA=false`, no se crean usuarios demo.
- Si `SEED_DEMO_DATA=false`, tampoco se crean catalogo, inventario ni contactos demo.
- Si `SEED_DEMO_DATA=false` y defines estas variables antes de migrar, se crea un administrador inicial:
  - `POS_INITIAL_ADMIN_NAME`
  - `POS_INITIAL_ADMIN_EMAIL`
  - `POS_INITIAL_ADMIN_PASSWORD`

## Configuracion de imagenes (Cloudflare R2)
En `.env`:
- `IMAGE_DISK=r2`
- `IMAGE_PREFIX=pos`
- `R2_ACCESS_KEY_ID=...`
- `R2_SECRET_ACCESS_KEY=...`
- `R2_BUCKET=...`
- `R2_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com`
- `R2_URL=https://<tu-dominio-publico-o-custom-domain>`
- Opcional: `R2_DEFAULT_REGION=auto`
- Opcional local Windows: `R2_VERIFY_SSL=false` si tu PHP/cURL no reconoce la CA

## Operacion basica
1. Iniciar sesion.
2. Abrir caja.
3. Vender en Punto de venta.
4. Cerrar caja.
5. Revisar facturas y reportes.

## Moneda por empresa
- La moneda operativa de ventas y ecommerce sale de `Configuracion del negocio > Moneda`.
- Si una empresa no define moneda, el sistema usa el valor por defecto de `config/pos.php`.

## Configuracion ecommerce por empresa
- El envio fijo del checkout sale de `Configuracion del negocio > Envio fijo e-commerce`.
- Los cupones del checkout se editan en `Configuracion del negocio > Cupones e-commerce` usando `CODIGO=PORCENTAJE`.
- Si una empresa no define estos valores, el sistema usa los defaults de `config/pos.php`.

## Docker
- Puedes levantar una instalacion base con `docker compose up -d --build`.
- La guia operativa esta en [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

## Preparacion de pre-entrega
- Para sanear una base existente antes de entregar, usa `php artisan app:prepare-go-live`.
- Ejemplo: `php artisan app:prepare-go-live --company=1 --admin-email=admin@cliente.test --admin-password=Secret123! --disable-demo-users`

## Alcance comercial actual del ecommerce
El modulo ecommerce debe ofrecerse hoy como:
- catalogo online con carrito
- pedido web con validacion manual
- transferencia, QR y contraentrega

No debe ofrecerse todavia como:
- pasarela de pago automatica
- checkout con tarjeta integrado
- conciliacion automatica de pagos

## Pruebas
- `php artisan test`
- `php artisan app:doctor`
- `php artisan app:prepare-go-live --company=1 --admin-email=admin@cliente.test --admin-password=Secret123! --disable-demo-users`

## Solucion de problemas (MySQL)
- Si aparece `could not find driver (mysql)`, habilita `pdo_mysql` en `php.ini`.
- Verifica modulos cargados con:
  - `php -m | findstr /I "mysql"`
