# Instalacion

## Requisitos
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8+
- Servidor web con HTTPS para produccion

## Instalacion local
1. Instalar dependencias PHP:
   - `composer install`
2. Crear el archivo de entorno:
   - Windows: `copy .env.example .env`
3. Configurar `.env`:
   - `APP_ENV=local`
   - `APP_DEBUG=true`
   - `APP_URL=http://127.0.0.1:8000`
   - `SEED_DEMO_DATA=true`
   - `DB_CONNECTION=mysql`
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=pos`
   - `DB_USERNAME=root`
   - `DB_PASSWORD=secret`
4. Generar la llave:
   - `php artisan key:generate`
5. Ejecutar migraciones y seeders:
   - `php artisan migrate --seed`
6. Instalar dependencias frontend:
   - `npm install`
7. Compilar assets:
   - `npm run build`
8. Iniciar la aplicacion:
   - `php artisan serve`

## Credenciales demo
Si `SEED_DEMO_DATA=true`:
- `admin@pos.test` / `password`
- `supervisor@pos.test` / `password`
- `cashier@pos.test` / `password`

## Datos demo incluidos
- `Cliente Mostrador` con identificacion `CF`
- `Empresa Demo` con identificacion `NIT-123456`
- `Proveedor Base` con identificacion `NIT-PROV-001`
- catalogo demo con inventario de ejemplo

## Despliegue base a produccion
1. Crear un `.env.production` fuera del repositorio.
2. Definir:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://tu-dominio`
   - `SEED_DEMO_DATA=false`
   - `SESSION_SECURE_COOKIE=true`
   - `POS_INITIAL_ADMIN_NAME=Administrador`
   - `POS_INITIAL_ADMIN_EMAIL=admin@tu-dominio.com`
   - `POS_INITIAL_ADMIN_PASSWORD=<clave-segura>`
   - credenciales reales de MySQL, correo y storage
3. Ejecutar:
   - `php artisan migrate --seed --force`
   - `php artisan optimize:clear`
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`
4. Configurar el servidor web para servir `public/`.
5. Forzar HTTPS en proxy o servidor frontal.

## Produccion sin datos demo
- Con `SEED_DEMO_DATA=false` no se crean usuarios demo.
- Con `SEED_DEMO_DATA=false` tampoco se crean productos, inventario ni contactos demo adicionales.
- Si defines `POS_INITIAL_ADMIN_*`, el sistema crea solo un administrador inicial para la empresa principal.
- Si no defines `POS_INITIAL_ADMIN_*`, debes crear el primer administrador por un flujo controlado antes de entregar el sistema.

## Preparacion de pre-entrega
Si la base viene de una demo, sandbox o implementacion interna previa, puedes sanearla con:

- `php artisan app:prepare-go-live --company=1 --admin-email=admin@cliente.test --admin-password=Secret123! --disable-demo-users`

Opciones comunes:
- `--domain=cliente.test`
- `--business-name="Nombre Comercial"`
- `--currency=COP`
- `--payment-qr-url=https://...`
- `--logo-url=https://...`
- `--shipping=12000`
- `--coupon=BIENVENIDO10=10`

## Configuracion por empresa despues del arranque
- En `Configuracion del negocio` define la moneda operativa.
- Si usas ecommerce, define `Envio fijo e-commerce`.
- Si usas cupones, cargalos con formato `CODIGO=PORCENTAJE`, uno por linea.

## Docker Compose
- Existe un despliegue base con `Dockerfile` y `docker-compose.yml`.
- Consulta [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) para levantarlo con MySQL.

## Nota comercial
El modulo ecommerce actual debe desplegarse como:
- `pedido web con validacion manual`
- `catalogo online con carrito`
- `transferencia, QR y contraentrega`

No debe anunciarse como pasarela de pago automatica hasta integrar un gateway real.
