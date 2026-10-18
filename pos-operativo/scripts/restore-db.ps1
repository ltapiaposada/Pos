param(
    [string]$EnvFile = ".env",
    [string]$InputFile
)

if (-not (Test-Path $EnvFile)) {
    Write-Error "No se encontro el archivo de entorno: $EnvFile"
    exit 1
}

if ([string]::IsNullOrWhiteSpace($InputFile)) {
    Write-Error "Debes indicar el dump SQL con -InputFile"
    exit 1
}

if (-not (Test-Path $InputFile)) {
    Write-Error "No se encontro el archivo SQL: $InputFile"
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

$mysqlPath = Get-Command mysql -ErrorAction SilentlyContinue
if (-not $mysqlPath) {
    Write-Error "No se encontro mysql en el PATH. Instala MySQL client tools y vuelve a intentar."
    exit 1
}

$dbName = $envMap["DB_DATABASE"]
$env:MYSQL_PWD = $envMap["DB_PASSWORD"]
try {
    Get-Content $InputFile | & mysql `
        --host=$($envMap["DB_HOST"]) `
        --port=$($envMap["DB_PORT"]) `
        --user=$($envMap["DB_USERNAME"]) `
        --default-character-set=utf8mb4 `
        $dbName

    if ($LASTEXITCODE -ne 0) {
        Write-Error "mysql fallo con codigo $LASTEXITCODE"
        exit $LASTEXITCODE
    }
}
finally {
    Remove-Item Env:\MYSQL_PWD -ErrorAction SilentlyContinue
}

Write-Output "Restauracion completada sobre la base: $dbName"
