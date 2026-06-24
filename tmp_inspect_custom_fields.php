<?php
$c = require __DIR__ . '/config/database.php';
$pdo = new PDO('mysql:host='.$c['host'].';dbname='.$c['db'].';charset='.$c['charset'], $c['user'], $c['pass'], $c['options']);
$stmt = $pdo->query('SELECT id, plate_number, custom_fields FROM vehicles WHERE custom_fields IS NOT NULL LIMIT 10');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo 'ID=' . $row['id'] . ' PLATE=' . $row['plate_number'] . "\n";
    echo $row['custom_fields'] . "\n---\n";
}
