<?php
$c = require __DIR__ . '/config/database.php';
$pdo = new PDO('mysql:host='.$c['host'].';dbname='.$c['db'].';charset='.$c['charset'], $c['user'], $c['pass'], $c['options']);
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM vehicles WHERE custom_fields IS NOT NULL AND custom_fields != '[]'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo $row['cnt'] . "\n";
