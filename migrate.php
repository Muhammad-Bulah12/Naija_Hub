<?php
// migrate.php - Run this in your browser to fix database errors
require_once __DIR__ . '/db.php';

echo "<h1>Database Migration Tool</h1>";

try {
    $sql = file_get_contents(__DIR__ . '/db_setup.sql');
    
    // Split by semicolon, but handle large blocks carefully
    // Using exec for the whole file is cleaner with PDO if it's just CREATE/INSERT
    $pdo->exec($sql);
    
    echo "<p style='color:green; font-weight:bold;'>Success! All tables (schools, students, quiz_results) have been created.</p>";
    echo "<p>You can now go back to <a href='login.php'>Login</a> and use the default accounts.</p>";
    
    // Self-destruct for security (optional, but good practice)
    // unlink(__FILE__); 
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Migration failed: " . $e->getMessage() . "</p>";
    echo "<p>Ensure XAMPP MySQL is running in the Control Panel.</p>";
}
