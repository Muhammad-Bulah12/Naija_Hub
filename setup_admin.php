<?php
require_once __DIR__ . '/db.php';

$username = 'BinMasud';
$password = '123456789';
$email    = 'abdullahibinmasud@gmail.com';
$fullName = 'Bin Masud';
$hash     = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM teachers WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update existing user
        $stmt = $pdo->prepare("UPDATE teachers SET password = ?, full_name = ?, email = ? WHERE username = ?");
        $stmt->execute([$hash, $fullName, $email, $username]);
        echo "Successfully updated existing user: $username";
    } else {
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO teachers (full_name, username, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$fullName, $username, $email, $hash]);
        echo "Successfully created new user: $username";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
