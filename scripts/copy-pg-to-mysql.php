<?php

declare(strict_types=1);

function argValue(array $argv, string $name, ?string $default = null): ?string
{
    $prefix = "--{$name}=";
    foreach ($argv as $arg) {
        if (str_starts_with($arg, $prefix)) {
            return substr($arg, strlen($prefix));
        }
    }

    return $default;
}

function requiredArg(array $argv, string $name): string
{
    $value = argValue($argv, $name);
    if ($value === null || $value === '') {
        fwrite(STDERR, "Falta parametro --{$name}\n");
        exit(1);
    }

    return $value;
}

$pgHost = argValue($argv, 'pg-host', '127.0.0.1');
$pgPort = argValue($argv, 'pg-port', '5432');
$pgDb = requiredArg($argv, 'pg-db');
$pgUser = requiredArg($argv, 'pg-user');
$pgPass = requiredArg($argv, 'pg-pass');

$mysqlHost = requiredArg($argv, 'mysql-host');
$mysqlPort = requiredArg($argv, 'mysql-port');
$mysqlDb = requiredArg($argv, 'mysql-db');
$mysqlUser = requiredArg($argv, 'mysql-user');
$mysqlPass = argValue($argv, 'mysql-pass', '');

$pgDsn = "pgsql:host={$pgHost};port={$pgPort};dbname={$pgDb}";
$myDsn = "mysql:host={$mysqlHost};port={$mysqlPort};dbname={$mysqlDb};charset=utf8mb4";

try {
    $pg = new PDO($pgDsn, $pgUser, $pgPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $my = new PDO($myDsn, $mysqlUser, $mysqlPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, "Error de conexion: {$e->getMessage()}\n");
    exit(1);
}

$pgTables = $pg->query("
    SELECT tablename
    FROM pg_tables
    WHERE schemaname = 'public'
    ORDER BY tablename
")->fetchAll(PDO::FETCH_COLUMN);

$myStmt = $my->prepare("
    SELECT table_name
    FROM information_schema.tables
    WHERE table_schema = :db
      AND table_type = 'BASE TABLE'
");
$myStmt->execute(['db' => $mysqlDb]);
$myTables = $myStmt->fetchAll(PDO::FETCH_COLUMN);
$myTableSet = array_fill_keys($myTables, true);

$my->exec('SET FOREIGN_KEY_CHECKS=0');
$my->exec('SET UNIQUE_CHECKS=0');

foreach ($pgTables as $table) {
    if (!isset($myTableSet[$table])) {
        echo "[SKIP] {$table}: no existe en MySQL\n";
        continue;
    }

    $pgColStmt = $pg->prepare("
        SELECT column_name
        FROM information_schema.columns
        WHERE table_schema = 'public'
          AND table_name = :table
        ORDER BY ordinal_position
    ");
    $pgColStmt->execute(['table' => $table]);
    $pgCols = $pgColStmt->fetchAll(PDO::FETCH_COLUMN);

    $myColStmt = $my->prepare("
        SELECT column_name
        FROM information_schema.columns
        WHERE table_schema = :db
          AND table_name = :table
        ORDER BY ordinal_position
    ");
    $myColStmt->execute(['db' => $mysqlDb, 'table' => $table]);
    $myCols = $myColStmt->fetchAll(PDO::FETCH_COLUMN);
    $myColSet = array_fill_keys($myCols, true);

    $cols = array_values(array_filter($pgCols, static fn (string $c): bool => isset($myColSet[$c])));
    if (count($cols) === 0) {
        echo "[SKIP] {$table}: sin columnas compatibles\n";
        continue;
    }

    $my->exec("TRUNCATE TABLE `{$table}`");

    $quotedPgCols = implode(', ', array_map(static fn (string $c): string => "\"{$c}\"", $cols));
    $selectStmt = $pg->query("SELECT {$quotedPgCols} FROM \"{$table}\"");

    $insertCols = implode(', ', array_map(static fn (string $c): string => "`{$c}`", $cols));
    $placeholders = implode(', ', array_fill(0, count($cols), '?'));
    $insertSql = "INSERT INTO `{$table}` ({$insertCols}) VALUES ({$placeholders})";
    $insertStmt = $my->prepare($insertSql);

    $count = 0;
    while ($row = $selectStmt->fetch()) {
        $values = [];
        foreach ($cols as $col) {
            $value = $row[$col];
            if (is_bool($value)) {
                $values[] = $value ? 1 : 0;
                continue;
            }
            $values[] = $value;
        }
        $insertStmt->execute($values);
        $count++;
    }

    echo "[OK] {$table}: {$count} filas\n";
}

$my->exec('SET UNIQUE_CHECKS=1');
$my->exec('SET FOREIGN_KEY_CHECKS=1');

echo "Migracion finalizada.\n";
