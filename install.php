<?php
// install.php - Database migration and setup tool
define('BASE_PATH', __DIR__);

// Load config
$config = require BASE_PATH . '/config/database.php';
$schemaFile = BASE_PATH . '/database.sql';

if (!file_exists($schemaFile)) {
    die("Error: schema file database.sql not found at: {$schemaFile}\n");
}

try {
    // Connect without DB first to create database
    $dsnWithoutDb = "mysql:host={$config['host']};charset={$config['charset']}";
    $pdo = new PDO($dsnWithoutDb, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Connecting to MySQL server...\n";

    // Read and parse SQL schema
    $sql = file_get_contents($schemaFile);

    // Update password hash for admin user in SQL to a secure one: Admin@123
    $adminPasswordHash = password_hash('Admin@123', PASSWORD_BCRYPT);
    $sql = str_replace("'$2y$10\$examplehashedpasswordstring'", "'{$adminPasswordHash}'", $sql);

    // Execute queries
    echo "Executing database migration schema...\n";
    $pdo->exec($sql);

    echo "Database setup completed successfully! Super admin account created:\n";
    echo "Email: superadmin@example.com\n";
    echo "Password: Admin@123\n";

} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage() . "\n");
}
