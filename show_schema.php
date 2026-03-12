#!/usr/bin/env php
<?php
/**
 * show_schema.php — Affiche le schéma complet de la BDD dans le terminal
 * Usage :
 *   php show_schema.php
 *   php show_schema.php --table relances
 *   php show_schema.php --host localhost --port 3306 --user root --pass secret --db ma_base
 */

// ─── Chargement .env brut depuis la racine du script ─────────────────────────

function loadEnv(string $path): void
{
    if (!file_exists($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
        $k = trim($k);
        $v = trim($v, " \t\"'");
        if ($k !== '') putenv("$k=$v");
    }
}

// Cherche le .env depuis l'endroit où on lance le script (getcwd) ET depuis __DIR__
loadEnv(getcwd() . '/.env');
loadEnv(__DIR__ . '/.env');
loadEnv(dirname(__DIR__) . '/.env');

// ─── Paramètres (CLI > .env) ──────────────────────────────────────────────────

$opts = getopt('', ['host::', 'port::', 'user::', 'pass::', 'db::', 'table::']);

$host   = $opts['host'] ?? getenv('DB_HOST')     ?: 'localhost';
$port   = $opts['port'] ?? getenv('DB_PORT')     ?: '3306';
$user   = $opts['user'] ?? getenv('DB_USER')     ?: 'root';
$pass   = $opts['pass'] ?? getenv('DB_PASSWORD') ?: (getenv('DB_PASS') ?: '');
$dbname = $opts['db']   ?? getenv('DB_NAME')     ?: '';
$filterTable = $opts['table'] ?? null;

// ─── Connexion ────────────────────────────────────────────────────────────────

function color(string $text, string $c): string {
    $m = ['red'=>'31','green'=>'32','yellow'=>'33','cyan'=>'36','white'=>'97','bold'=>'1','gray'=>'90'];
    return "\033[" . ($m[$c] ?? '0') . "m{$text}\033[0m";
}

if (empty($dbname)) {
    echo color("⚠️  DB_NAME vide — utilise : php show_schema.php --db ma_base\n", 'yellow');
}

echo color("  Connexion : {$user}@{$host}:{$port} / {$dbname}\n", 'gray');

try {
    $dsn = "mysql:host={$host};port={$port}" . ($dbname ? ";dbname={$dbname}" : '') . ";charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die(color("❌ Connexion échouée : " . $e->getMessage(), 'red') . "\n");
}

if (empty($dbname)) {
    $dbname = $pdo->query("SELECT DATABASE()")->fetchColumn();
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function hr(string $c = '─', int $w = 80): string { return str_repeat($c, $w); }
function padR(string $s, int $n): string { return str_pad($s, $n); }

// ─── Schéma ───────────────────────────────────────────────────────────────────

$tf = $filterTable ? "AND TABLE_NAME = " . $pdo->quote($filterTable) : '';

$tables = $pdo->query("
    SELECT TABLE_NAME, TABLE_ROWS, ENGINE, TABLE_COMMENT
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = " . $pdo->quote($dbname) . " $tf
    ORDER BY TABLE_NAME
")->fetchAll(PDO::FETCH_ASSOC);

$columns = $pdo->query("
    SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE,
           COLUMN_DEFAULT, COLUMN_KEY, EXTRA, COLUMN_COMMENT
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = " . $pdo->quote($dbname) . " $tf
    ORDER BY TABLE_NAME, ORDINAL_POSITION
")->fetchAll(PDO::FETCH_ASSOC);

$fks = $pdo->query("
    SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME,
           REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = " . $pdo->quote($dbname) . "
      AND REFERENCED_TABLE_NAME IS NOT NULL $tf
    ORDER BY TABLE_NAME
")->fetchAll(PDO::FETCH_ASSOC);

$colsByTable = [];
foreach ($columns as $col) $colsByTable[$col['TABLE_NAME']][] = $col;

$fksByTable = [];
foreach ($fks as $fk) $fksByTable[$fk['TABLE_NAME']][] = $fk;

// ─── Affichage ────────────────────────────────────────────────────────────────

echo "\n";
echo color(hr('═') . "\n", 'cyan');
echo color("  🗄️  SCHÉMA BDD : {$dbname}  (" . count($tables) . " tables)\n", 'bold');
echo color(hr('═') . "\n", 'cyan');

foreach ($tables as $table) {
    $name = $table['TABLE_NAME'];
    $rows = $table['TABLE_ROWS'] ?? '?';
    $eng  = $table['ENGINE'] ?? '';
    $com  = $table['TABLE_COMMENT'] ? " — " . $table['TABLE_COMMENT'] : '';

    echo "\n" . color(hr(), 'gray') . "\n";
    echo color("  📋 {$name}", 'yellow') . color("  (~{$rows} lignes, {$eng}){$com}\n", 'gray');
    echo color(hr(), 'gray') . "\n";
    echo color("  " . padR("Colonne", 28) . padR("Type", 28) . padR("Null", 6) . padR("Clé", 6) . padR("Défaut", 16) . "Extra\n", 'cyan');
    echo color("  " . str_repeat('·', 78) . "\n", 'gray');

    foreach ($colsByTable[$name] ?? [] as $col) {
        $key     = match($col['COLUMN_KEY']) { 'PRI' => color('PK', 'green'), 'MUL' => color('FK', 'yellow'), 'UNI' => color('UK', 'cyan'), default => '  ' };
        $null    = $col['IS_NULLABLE'] === 'YES' ? color('YES', 'gray') : color('NO', 'white');
        $default = $col['COLUMN_DEFAULT'] !== null ? substr(strval($col['COLUMN_DEFAULT']), 0, 14) : color('—', 'gray');
        $extra   = $col['EXTRA'] ? color($col['EXTRA'], 'gray') : '';
        $comment = $col['COLUMN_COMMENT'] ? color(" ← " . $col['COLUMN_COMMENT'], 'gray') : '';
        echo "  " . padR($col['COLUMN_NAME'], 28) . padR($col['COLUMN_TYPE'], 28) . padR($null, 6) . $key . "  " . padR($default, 16) . $extra . $comment . "\n";
    }

    if (!empty($fksByTable[$name])) {
        echo color("\n  🔗 Clés étrangères :\n", 'cyan');
        foreach ($fksByTable[$name] as $fk) {
            echo color("     {$fk['COLUMN_NAME']}", 'yellow') . color(" → {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}", 'white') . color("  ({$fk['CONSTRAINT_NAME']})\n", 'gray');
        }
    }
}

echo "\n" . color(hr('═'), 'cyan') . "\n";
echo color("  ✅ Fin du schéma\n", 'green');
echo color(hr('═'), 'cyan') . "\n\n";