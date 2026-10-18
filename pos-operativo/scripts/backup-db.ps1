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

if ($envMap["DB_CONNECTION"] -ne "mysql") {
    Write-Error "Este script solo soporta MySQL (DB_CONNECTION=mysql). Actual: $($envMap["DB_CONNECTION"])"
    exit 1
}

$mysqlDumpPath = Get-Command mysqldump -ErrorAction SilentlyContinue
if (-not $mysqlDumpPath) {
    Write-Error "No se encontro mysqldump en el PATH. Instala MySQL client tools y vuelve a intentar."
    exit 1
}

if (-not (Test-Path $OutputDir)) {
    New-Item -Path $OutputDir -ItemType Directory | Out-Null
}

$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$dbName = $envMap["DB_DATABASE"]
$outputFile = Join-Path $OutputDir "$dbName-$timestamp.sql"

$env:MYSQL_PWD = $envMap["DB_PASSWORD"]
try {
    & mysqldump `
        --host=$($envMap["DB_HOST"]) `
        --port=$($envMap["DB_PORT"]) `
        --user=$($envMap["DB_USERNAME"]) `
        --default-character-set=utf8mb4 `
        --single-transaction `
        --skip-lock-tables `
        --routines `
        --triggers `
        $dbName | Out-File -FilePath $outputFile -Encoding utf8

    if ($LASTEXITCODE -ne 0) {
        Write-Error "mysqldump fallo con codigo $LASTEXITCODE"
        exit $LASTEXITCODE
    }
}
finally {
    Remove-Item Env:\MYSQL_PWD -ErrorAction SilentlyContinue
}

Write-Output "Backup creado: $outputFile"
