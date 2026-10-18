#!/usr/bin/env bash

set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

seed_demo=false

for argument in "$@"; do
    case "$argument" in
        --demo)
            seed_demo=true
            ;;
        --help|-h)
            cat <<'USAGE'
Uso: ./scripts/setup-macos.sh [--demo]

Sin opciones instala dependencias, crea .env si falta, ejecuta migraciones
pendientes y compila los recursos frontend.

--demo  Borra y recrea la base de datos con los datos de demostracion.
        No usar si vas a restaurar una base de datos real.
USAGE
            exit 0
            ;;
        *)
            echo "Opcion no reconocida: $argument" >&2
            exit 1
            ;;
    esac
done

for command in php composer node npm; do
    if ! command -v "$command" >/dev/null 2>&1; then
        echo "Falta $command. Consulta docs/MACOS-MIGRATION.md." >&2
        exit 1
    fi
done

if [[ ! -f .env ]]; then
    cp .env.example .env
    echo "Se creo .env desde .env.example. Verifica las variables DB_* antes de continuar."
fi

composer install
npm ci

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate
fi

if [[ "$seed_demo" == true ]]; then
    php artisan migrate:fresh --seed
else
    php artisan migrate --force
fi

php artisan storage:link
npm run build
php artisan app:doctor

echo
echo "Listo. Inicia la aplicacion con: php artisan serve"
