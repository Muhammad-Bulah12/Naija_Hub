<?php
require_once __DIR__ . '/db.php';
header("Content-Type: text/plain");

function checkTable($pdo, $name) {
    echo "--- Table: $name ---\n";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $name");
        echo "Count: " . $stmt->fetchColumn() . "\n";
        
        $stmt = $pdo->query("SELECT * FROM $name LIMIT 5");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

checkTable($pdo, 'schools');
checkTable($pdo, 'teachers');
checkTable($pdo, 'students');
checkTable($pdo, 'lessons');
checkTable($pdo, 'quizzes');
