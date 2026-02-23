param(
    [string]$EnvFile = ".env",
    [string]$OutputDir = "backups"
)

if (-not (Test-Path $EnvFile)) {
    Write-Error "No se encontro el archivo de entorno: $EnvFile"
    exit 1
}

$envMap = @{}
Get-Content $EnvFile | ForEach-Object {
    if ($_ -match '^\s*#' -or $_ -match '^\s*$') { return }
    if ($_ -notmatch '=') { return }

    $parts = $_.Split('=', 2)
    $key = $parts[0].Trim()
    $value = $parts[1].Trim().Trim('"')
    $envMap[$key] = $value
}

$required = @("DB_CONNECTION", "DB_HOST", "DB_PORT", "DB_DATABASE", "DB_USERNAME")
foreach ($key in $required) {
    if (-not $envMap.ContainsKey($key) -or [string]::IsNullOrWhiteSpace($envMap[$key])) {
        Write-Error "Falta la variable $key en $EnvFile"
        exit 1
    }
}

if ($envMap["DB_CONNECTION"] -ne "pgsql") {
    Write-Error "Este script solo soporta PostgreSQL (DB_CONNECTION=pgsql). Actual: $($envMap["DB_CONNECTION"])"
    exit 1
}

$pgDumpPath = Get-Command pg_dump -ErrorAction SilentlyContinue
if (-not $pgDumpPath) {
    Write-Error "No se encontro pg_dump en el PATH. Instala PostgreSQL client tools y vuelve a intentar."
    exit 1
}

if (-not (Test-Path $OutputDir)) {
    New-Item -Path $OutputDir -ItemType Directory | Out-Null
}

$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$dbName = $envMap["DB_DATABASE"]
$outputFile = Join-Path $OutputDir "$dbName-$timestamp.sql"

$env:PGPASSWORD = $envMap["DB_PASSWORD"]
try {
    & pg_dump `
        --host=$($envMap["DB_HOST"]) `
        --port=$($envMap["DB_PORT"]) `
        --username=$($envMap["DB_USERNAME"]) `
        --dbname=$dbName `
        --format=plain `
        --no-owner `
        --no-privileges `
        --encoding=UTF8 `
        --file=$outputFile

    if ($LASTEXITCODE -ne 0) {
        Write-Error "pg_dump fallo con codigo $LASTEXITCODE"
        exit $LASTEXITCODE
    }
}
finally {
    Remove-Item Env:\PGPASSWORD -ErrorAction SilentlyContinue
}

Write-Output "Backup creado: $outputFile"
