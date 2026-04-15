<?php
// api/results.php - Save and fetch quiz results
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../db.php";

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    session_start();
    if (empty($_SESSION["student_logged_in"])) {
        http_response_code(401); echo json_encode(["ok"=>false,"error"=>"Only students can save results"]); exit;
    }

    $body = json_decode(file_get_contents("php://input"), true) ?? [];
    $studentId = $_SESSION["student_id"];
    $subject   = $body["subject"] ?? "";
    $level     = $body["level"]   ?? "";
    $score     = (int)($body["score"] ?? 0);
    $total     = (int)($body["total"] ?? 0);

    if (!$subject || !$level || $total <= 0) {
        http_response_code(400); echo json_encode(["ok"=>false,"error"=>"Missing fields"]); exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO quiz_results (student_id, subject, level, score, total) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$studentId, $subject, $level, $score, $total]);
        echo json_encode(["ok" => true]);
    } catch (Exception $e) {
        http_response_code(500); echo json_encode(["ok" => false, "error" => $e->getMessage()]);
    }
    exit;
}

if ($method === "GET") {
    session_start();
    if (empty($_SESSION["admin_logged_in"])) {
        http_response_code(401); echo json_encode(["ok"=>false,"error"=>"Not authenticated"]); exit;
    }

    $schoolId = $_SESSION["admin_school_id"];
    $assignedClass = $_SESSION["assigned_class"];

    try {
        // Fetch results for students in the teacher's school and assigned class
        $stmt = $pdo->prepare("
            SELECT r.*, s.full_name, s.username
            FROM quiz_results r
            JOIN students s ON s.id = r.student_id
            WHERE s.school_id = ? AND s.class_level = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$schoolId, $assignedClass]);
        $results = $stmt->fetchAll();

        echo json_encode(["ok" => true, "results" => $results]);
    } catch (Exception $e) {
        http_response_code(500); echo json_encode(["ok" => false, "error" => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(["ok" => false, "error" => "Method not allowed"]);
