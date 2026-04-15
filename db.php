<?php
// ─────────────────────────────────────────────
//  db.php  –  Database connection (PDO)
//  Edit $host / $user / $pass if needed.
// ─────────────────────────────────────────────
$host   = "localhost";
$dbname = "naija_hub";
$user   = "root";      // XAMPP default
$pass   = "";          // XAMPP default (blank)

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(["ok" => false, "error" => "DB connection failed: " . $e->getMessage()]));
}
