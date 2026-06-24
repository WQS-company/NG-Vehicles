<?php
// Simple migration runner: executes SQL files in migrations/ in lexicographic order
$base = dirname(__DIR__);
$config = require $base . '/config/database.php';
$dsn = "mysql:host={$config['host']};dbname={$config['db']};charset={$config['charset']}";
try {
    $pdo = new PDO($dsn, $config['user'], $config['pass'], $config['options']);
} catch (PDOException $e) {
    echo "DB Connection failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
$migrationFiles = glob($base . '/migrations/*.sql');
sort($migrationFiles, SORT_NATURAL);
if (empty($migrationFiles)) {
    echo "No migrations found in {$base}/migrations" . PHP_EOL;
    exit(0);
}
foreach ($migrationFiles as $migFile) {
    $name = basename($migFile);
    echo "Running migration: {$name}\n";
    $sql = file_get_contents($migFile);
    if ($sql === false) {
        echo "Could not read migration file: {$name}\n";
        continue;
    }
    try {
        $pdo->exec($sql);
        echo "  ✅ {$name} applied." . PHP_EOL;
    } catch (PDOException $e) {
        echo "  ❌ {$name} failed: " . $e->getMessage() . PHP_EOL;
        exit(1);
    }
}
echo "All migrations executed successfully." . PHP_EOL;
