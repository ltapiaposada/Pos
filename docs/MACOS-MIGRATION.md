# Migracion a macOS

Esta guia permite mover el proyecto a una Mac sin depender del PC anterior.
El repositorio Git contiene el codigo, pero por seguridad **no** contiene la
base de datos, `.env`, dependencias instaladas ni archivos subidos por clientes.

## 1. Respaldo antes de dejar el PC

Haz este respaldo antes de borrar o dejar de usar el equipo actual:

1. Base de datos: ejecuta `scripts/backup-db.ps1` desde PowerShell. El archivo
   SQL resultante debe guardarse fuera del repositorio, por ejemplo en una nube
   privada o un disco externo.
2. Configuracion: guarda una copia segura del archivo `.env`. Nunca la subas a
   Git porque contiene contrasenas, claves de correo y R2.
3. Archivos locales: si `FILESYSTEM_DISK=local` o `IMAGE_DISK=local`, copia la
   carpeta `storage/app` completa. Si las imagenes usan Cloudflare R2, basta
   con conservar las variables `R2_*` del `.env`; los archivos ya estan en R2.
4. Verifica el backup abriendo el SQL y comprobando que tiene tablas y datos.

Los directorios `vendor`, `node_modules`, `public/build` y los cache de Laravel
no se copian: se regeneran en la Mac.

## 2. Preparar la Mac

Instala Xcode Command Line Tools y Homebrew si aun no los tienes:

```bash
xcode-select --install
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

Instala las versiones necesarias y enciende MySQL:

```bash
brew install php@8.3 composer node mysql
brew services start mysql
```

Comprueba que se instalaron:

```bash
php -v
composer --version
node --version
npm --version
mysql --version
```

El proyecto requiere PHP 8.2 o superior, Node 18 o superior y MySQL 8 o
superior. PHP 8.3 y Node actual de Homebrew son compatibles.

## 3. Clonar y configurar el codigo

```bash
git clone <URL-DEL-REPOSITORIO> pos
cd pos
cp .env.example .env
```

Edita `.env` con los datos de MySQL. Para una base local nueva con el usuario
root de Homebrew, un punto de partida es:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos
DB_USERNAME=root
DB_PASSWORD=
MAIL_MAILER=log
FILESYSTEM_DISK=local
IMAGE_DISK=local
```

Crea la base de datos vacia:

```bash
mysql -u root -e 'CREATE DATABASE IF NOT EXISTS pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
```

Si configuraste una contrasena para MySQL, usa `mysql -u root -p` y coloca la
misma contrasena en `DB_PASSWORD`.

## 4. Elegir el tipo de instalacion

### Instalar una demo nueva

Este comando instala dependencias, genera la llave, borra la base `pos`, carga
los datos demo, crea el enlace de storage y compila los recursos:

```bash
./scripts/setup-macos.sh --demo
```

Usuarios demo: `admin@pos.test`, `supervisor@pos.test` y `cashier@pos.test`.
La contrasena de los tres es `password`.

### Restaurar la instalacion real

No uses `--demo`, pues borraria la base. Instala dependencias y restaura primero
el respaldo:

```bash
composer install
npm ci
php artisan key:generate
mysql -u root pos < /ruta/segura/respaldo-pos.sql
```

Despues copia el `.env` respaldado, ajustando solo las rutas o credenciales que
cambien en la Mac. Si usabas storage local, restaura el contenido respaldado en
`storage/app`. Finalmente ejecuta:

```bash
./scripts/setup-macos.sh
```

Si restauraste un `.env` que ya tiene `APP_KEY`, el script la conserva. Para
R2, manten las variables `R2_*` y configura `IMAGE_DISK=r2`.

## 5. Ejecutar y verificar

```bash
php artisan serve
```

Abre `http://127.0.0.1:8000`. Para desarrollo de estilos en tiempo real, en
otra terminal ejecuta:

```bash
npm run dev
```

La instalacion termina correctamente cuando `php artisan app:doctor` no muestra
fallos. Comprueba tambien inicio de sesion, productos, inventario, POS y el
ecommerce.

## Problemas comunes

- `could not find driver (mysql)`: confirma que estas usando el PHP de
  Homebrew (`which php`) y que `php -m | grep pdo_mysql` muestra el modulo.
- `Access denied for user`: revisa `DB_USERNAME`, `DB_PASSWORD` y que MySQL
  este iniciado con `brew services list`.
- No cargan imagenes locales: ejecuta `php artisan storage:link` y confirma que
  `storage/app` fue restaurado.
- No salen correos en local: con `MAIL_MAILER=log` se guardan en
  `storage/logs/laravel.log`. Configura Mailgun solo si necesitas envio real.
- Puerto ocupado: usa `php artisan serve --port=8001`.
