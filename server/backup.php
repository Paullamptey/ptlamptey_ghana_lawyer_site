<?php
// server/backup.php (Keep this secure!)
require_once 'db.php';
require_once 'config.php';

$backupFile = 'backups/db-backup-' . date('Y-m-d-H-i-s') . '.sql';

// Create backups directory if it doesn't exist
if (!is_dir('backups')) {
    mkdir('backups', 0755, true);
}

// Get all tables
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

$output = "";
foreach ($tables as $table) {
    $output .= "-- Table: $table\n";
    
    // Get table structure
    $createTable = $pdo->query("SHOW CREATE TABLE $table")->fetch();
    $output .= $createTable['Create Table'] . ";\n\n";
    
    // Get table data
    $rows = $pdo->query("SELECT * FROM $table")->fetchAll();
    foreach ($rows as $row) {
        $output .= "INSERT INTO $table VALUES (";
        $values = array_map(function($value) use ($pdo) {
            return $pdo->quote($value);
        }, $row);
        $output .= implode(', ', $values) . ");\n";
    }
    $output .= "\n";
}

file_put_contents($backupFile, $output);
echo "Backup created: $backupFile";
?>