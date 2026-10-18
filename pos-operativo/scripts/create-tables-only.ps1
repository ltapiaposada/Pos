param(
    [switch]$Fresh
)

$ErrorActionPreference = 'Stop'

$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..')
Set-Location $projectRoot

$env:SEED_DEMO_DATA = 'false'

Write-Host 'Limpiando cache de configuracion...'
php artisan config:clear

if ($Fresh) {
    Write-Host 'Creando solo las tablas desde cero...'
    php artisan migrate:fresh --force --no-interaction
} else {
    Write-Host 'Creando solo las tablas pendientes...'
    php artisan migrate --force --no-interaction
}

Write-Host 'Listo. No se ejecutaron seeders ni datos demo.'
