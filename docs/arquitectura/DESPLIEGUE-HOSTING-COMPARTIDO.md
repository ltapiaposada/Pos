# Despliegue en hosting compartido

Esta variante está diseñada para un hosting con PHP 8.3, MySQL/MariaDB, acceso SSH o Administrador de archivos y subdominios. No requiere Docker, workers, Supervisor, Redis ni procesos de cola.

## Subdominios

Solo e-commerce usa el subdominio para identificar la empresa. POS y administración se abren siempre en una URL fija y nunca derivan la empresa desde el host.

| Tipo | Dominio | Document root |
| --- | --- | --- |
| POS fijo | `pos.tudominio.com` | `/home/USUARIO/pos-platform/pos-operativo/public` |
| Administración fija | `admin.tudominio.com` | `/home/USUARIO/pos-platform/administracion/public` |
| API/canónico e-commerce | `tienda.tudominio.com` | `/home/USUARIO/pos-platform/ecommerce/public` |
| Tienda por empresa | `*.tudominio.com` | `/home/USUARIO/pos-platform/ecommerce/public` |

Por ejemplo, `cafeteria-sol.tudominio.com` y `optica-centro.tudominio.com` llegan a la misma aplicación e-commerce. Esta consulta el host, lo busca en `storefronts.domain` y así identifica el tenant correcto. Los hosts reservados `pos`, `admin` y `tienda` se configuran explícitamente antes del comodín.

En el panel DNS se debe crear un registro wildcard `*.tudominio.com` hacia el hosting y, en el panel de hosting, un subdominio wildcard que apunte a `ecommerce/public`. La ruta exacta puede cambiar según el proveedor. Nunca se debe apuntar el dominio a la raíz de la aplicación; siempre debe apuntar a `public`.

## Bases de datos

Crear solamente estas dos bases de datos:

| Base de datos | Aplicaciones | Registro de migraciones |
| --- | --- | --- |
| `usuario_pos_core` | POS y e-commerce | `migrations` para POS; `ecommerce_migrations` para e-commerce |
| `usuario_administracion` | Administración | `administracion_migrations` |

POS y e-commerce usan las mismas credenciales de `usuario_pos_core`. Sus tablas son distintas y e-commerce se integra con POS exclusivamente mediante HTTPS; no se deben añadir consultas cruzadas entre ambas aplicaciones.

## Variables de entorno de producción

En los tres archivos `.env`, usar `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_DRIVER=file`, `CACHE_STORE=file` y `QUEUE_CONNECTION=sync`.

Configurar las URL públicas así:

```dotenv
# pos-operativo/.env
APP_URL=https://pos.tudominio.com
DB_DATABASE=usuario_pos_core
QUEUE_CONNECTION=sync
ECOMMERCE_API_URL=https://tienda.tudominio.com

# ecommerce/.env
APP_URL=https://tienda.tudominio.com
STOREFRONT_BASE_DOMAIN=tudominio.com
DB_DATABASE=usuario_pos_core
MIGRATIONS_TABLE=ecommerce_migrations
QUEUE_CONNECTION=sync
POS_API_URL=https://pos.tudominio.com

# administracion/.env (URL fija; no identifica empresas por subdominio)
APP_URL=https://admin.tudominio.com
DB_DATABASE=usuario_administracion
MIGRATIONS_TABLE=administracion_migrations
QUEUE_CONNECTION=sync
POS_API_URL=https://pos.tudominio.com
ECOMMERCE_API_URL=https://tienda.tudominio.com
```

Usar un valor aleatorio largo e idéntico para `INTERNAL_API_TOKEN` en las tres aplicaciones. No usar `local-internal-token` en producción.

## Instalación

Desde la raíz de cada aplicación, después de cargar los archivos o clonar el repositorio:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Ejecutar primero Administración, luego POS y finalmente e-commerce.
Administración debe existir antes de POS porque contiene el maestro de
compañías. E-commerce no tiene migraciones Laravel genéricas para evitar que
cree tablas que ya pertenecen al POS.

## Operación sin workers

- Crear una empresa desde administración ejecuta el aprovisionamiento de POS y tienda en la misma solicitud.
- La tienda intenta sincronizar catálogo y disponibilidad al abrir el dominio. Si POS no está disponible, muestra la última proyección disponible y no permite confirmar ventas sin POS.
- El checkout crea la venta directamente en POS y usa una llave de idempotencia para no duplicarla.
- Los eventos de estado se envían durante la misma solicitud. Si falla la comunicación, se conserva el evento y se puede ejecutar manualmente desde POS: `php artisan integration:deliver-outbox`.
- Si un aprovisionamiento falla, se puede reintentar con `POST /api/v1/tenants/{tenantUuid}/provisioning/{jobId}/retry` incluyendo `X-Internal-Token`.

Keycloak no se instala dentro de este hosting. Si más adelante se necesita SSO, debe usarse un Keycloak administrado o un VPS independiente.
