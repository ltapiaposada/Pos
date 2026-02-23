param(
    [Parameter(Mandatory = $true)]
    [string]$PgDatabase,
    [Parameter(Mandatory = $true)]
    [string]$PgUsername,
    [Parameter(Mandatory = $true)]
    [string]$PgPassword,
    [string]$PgHost = "127.0.0.1",
    [string]$PgPort = "5432",
    [string]$MySqlEnvFile = ".env",
    [switch]$WithDrop
)

if (-not (Test-Path $MySqlEnvFile)) {
    Write-Error "No se encontro el archivo de entorno destino: $MySqlEnvFile"
    exit 1
}

$pgloader = Get-Command pgloader -ErrorAction SilentlyContinue
if (-not $pgloader) {
    Write-Error "No se encontro pgloader en el PATH. Instala pgloader y vuelve a intentar."
    exit 1
}

$envMap = @{}
Get-Content $MySqlEnvFile | ForEach-Object {
    if ($_ -match '^\s*#' -or $_ -match '^\s*$') { return }
    if ($_ -notmatch '=') { return }

    $parts = $_.Split('=', 2)
    $key = $parts[0].Trim()
    $value = $parts[1].Trim().Trim('"')
    $envMap[$key] = $value
}

$requiredMysqlKeys = @("DB_CONNECTION", "DB_HOST", "DB_PORT", "DB_DATABASE", "DB_USERNAME")
foreach ($key in $requiredMysqlKeys) {
    if (-not $envMap.ContainsKey($key) -or [string]::IsNullOrWhiteSpace($envMap[$key])) {
        Write-Error "Falta la variable $key en $MySqlEnvFile"
        exit 1
    }
}

if ($envMap["DB_CONNECTION"] -ne "mysql") {
    Write-Error "El destino no esta configurado como mysql en $MySqlEnvFile (DB_CONNECTION=$($envMap["DB_CONNECTION"]))"
    exit 1
}

$mysqlHost = $envMap["DB_HOST"]
$mysqlPort = $envMap["DB_PORT"]
$mysqlDatabase = $envMap["DB_DATABASE"]
$mysqlUsername = $envMap["DB_USERNAME"]
$mysqlPassword = ""
if ($envMap.ContainsKey("DB_PASSWORD")) {
    $mysqlPassword = $envMap["DB_PASSWORD"]
}

$pgUserEsc = [Uri]::EscapeDataString($PgUsername)
$pgPassEsc = [Uri]::EscapeDataString($PgPassword)
$pgDbEsc = [Uri]::EscapeDataString($PgDatabase)
$mysqlUserEsc = [Uri]::EscapeDataString($mysqlUsername)
$mysqlPassEsc = [Uri]::EscapeDataString($mysqlPassword)
$mysqlDbEsc = [Uri]::EscapeDataString($mysqlDatabase)

$sourceUri = "postgresql://$pgUserEsc`:$pgPassEsc@$PgHost`:$PgPort/$pgDbEsc"
$targetUri = "mysql://$mysqlUserEsc`:$mysqlPassEsc@$mysqlHost`:$mysqlPort/$mysqlDbEsc"

$loadFile = Join-Path $env:TEMP "pgloader-pos.load"
$loadContent = @(
    "LOAD DATABASE"
    "FROM $sourceUri"
    "INTO $targetUri"
)

if ($WithDrop) {
    $loadContent += ""
    $loadContent += "WITH include drop, create tables, create indexes, reset sequences"
}

$loadContent += ""
$loadContent += "SET maintenance_work_mem to '256MB', work_mem to '32MB';"

Set-Content -Path $loadFile -Value ($loadContent -join "`r`n") -Encoding ascii

Write-Output "Iniciando migracion con pgloader..."
& pgloader $loadFile

if ($LASTEXITCODE -ne 0) {
    Write-Error "La migracion fallo con codigo $LASTEXITCODE"
    exit $LASTEXITCODE
}

Write-Output "Migracion completada: PostgreSQL -> MySQL ($mysqlDatabase)"
Write-Output "Siguiente verificacion recomendada: php artisan migrate:status"
