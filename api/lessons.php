<?php
// api/lessons.php  –  CRUD for lessons
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") { http_response_code(204); exit; }

require_once __DIR__ . "/../db.php";

$method = $_SERVER["REQUEST_METHOD"];

function normalize_text(string $value): string {
    $value = trim($value);
    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
    return mb_strtolower($value, 'UTF-8');
}

// ── GET /api/lessons.php  → list all lessons (optional ?level=JSS+1&subject=English)
if ($method === "GET") {
    $where = [];
    $params = [];
    if (!empty($_GET["level"])) {
        $where[] = "level = ?";
        $params[] = $_GET["level"];
    }
    if (!empty($_GET["subject"])) {
        $where[] = "subject = ?";
        $params[] = $_GET["subject"];
    }
    $sql = "SELECT l.id, l.level, l.subject, l.title, l.content_en, l.content_ha, l.created_at,
                   t.username AS teacher
            FROM lessons l
            JOIN teachers t ON t.id = l.teacher_id"
         . ($where ? " WHERE " . implode(" AND ", $where) : "")
         . " ORDER BY l.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(["ok" => true, "lessons" => $stmt->fetchAll()]);
    exit;
}

// ── POST /api/lessons.php  → add lesson (requires active session)
if ($method === "POST") {
    session_start();
    if (empty($_SESSION["admin_logged_in"])) {
        http_response_code(401);
        echo json_encode(["ok" => false, "error" => "Not authenticated"]);
        exit;
    }
    $body = json_decode(file_get_contents("php://input"), true) ?? [];
    $level   = trim($body["level"]      ?? "");
    $subject = trim($body["subject"]    ?? "");
    $title   = trim($body["title"]      ?? "");
    $en      = trim($body["content_en"] ?? "");
    $ha      = trim($body["content_ha"] ?? "");
    if (!$level || !$subject || !$title || !$en || !$ha) {
        http_response_code(400);
        echo json_encode(["ok" => false, "error" => "All fields are required"]);
        exit;
    }

    $teacherId = (int) ($_SESSION["admin_teacher_id"] ?? 0);
    if ($teacherId <= 0) {
        http_response_code(403);
        echo json_encode(["ok" => false, "error" => "Unknown teacher"]);
        exit;
    }

    $titleSignature = normalize_text($title);
    $lessonSignature = hash('sha256', implode('|', [
        normalize_text($level),
        normalize_text($subject),
        $titleSignature,
        normalize_text($en),
        normalize_text($ha),
    ]));

    $existing = $pdo->prepare(
        "SELECT l.id, l.created_at, t.username AS teacher
         FROM lessons l
         JOIN teachers t ON t.id = l.teacher_id
         WHERE l.lesson_signature = ? OR (l.level = ? AND l.subject = ? AND l.title_normalized = ?)
         LIMIT 1"
    );
    $existing->execute([$lessonSignature, $level, $subject, $titleSignature]);
    $duplicate = $existing->fetch();
    if ($duplicate) {
        http_response_code(409);
        echo json_encode([
            "ok" => false,
            "error" => "A similar lesson already exists.",
            "duplicate" => [
                "id" => (int) $duplicate["id"],
                "teacher" => $duplicate["teacher"],
                "created_at" => $duplicate["created_at"],
            ],
        ]);
        exit;
    }

    try {
        $ins = $pdo->prepare(
            "INSERT INTO lessons
             (teacher_id, level, subject, title, title_normalized, content_en, content_ha, lesson_signature)
             VALUES (?,?,?,?,?,?,?,?)"
        );
        $ins->execute([$teacherId, $level, $subject, $title, $titleSignature, $en, $ha, $lessonSignature]);
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? null) === 1062) {
            http_response_code(409);
            echo json_encode(["ok" => false, "error" => "This lesson already exists."]);
            exit;
        }
        throw $e;
    }

    echo json_encode(["ok" => true, "id" => $pdo->lastInsertId()]);
    exit;
}

// ── PUT /api/lessons.php?id=X  → update lesson (requires active session)
if ($method === "PUT") {
    session_start();
    if (empty($_SESSION["admin_logged_in"])) {
        http_response_code(401);
        echo json_encode(["ok" => false, "error" => "Not authenticated"]);
        exit;
    }

    $id = intval($_GET["id"] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(["ok" => false, "error" => "Missing id"]);
        exit;
    }

    $body = json_decode(file_get_contents("php://input"), true) ?? [];
    $level   = trim($body["level"]      ?? "");
    $subject = trim($body["subject"]    ?? "");
    $title   = trim($body["title"]      ?? "");
    $en      = trim($body["content_en"] ?? "");
    $ha      = trim($body["content_ha"] ?? "");
    if (!$level || !$subject || !$title || !$en || !$ha) {
        http_response_code(400);
        echo json_encode(["ok" => false, "error" => "All fields are required"]);
        exit;
    }

    $titleSignature = normalize_text($title);
    $lessonSignature = hash('sha256', implode('|', [
        normalize_text($level),
        normalize_text($subject),
        $titleSignature,
        normalize_text($en),
        normalize_text($ha),
    ]));

    $existing = $pdo->prepare(
        "SELECT l.id, l.created_at, t.username AS teacher
         FROM lessons l
         JOIN teachers t ON t.id = l.teacher_id
         WHERE (l.lesson_signature = ? OR (l.level = ? AND l.subject = ? AND l.title_normalized = ?))
           AND l.id <> ?
         LIMIT 1"
    );
    $existing->execute([$lessonSignature, $level, $subject, $titleSignature, $id]);
    $duplicate = $existing->fetch();
    if ($duplicate) {
        http_response_code(409);
        echo json_encode([
            "ok" => false,
            "error" => "A similar lesson already exists.",
            "duplicate" => [
                "id" => (int) $duplicate["id"],
                "teacher" => $duplicate["teacher"],
                "created_at" => $duplicate["created_at"],
            ],
        ]);
        exit;
    }

    try {
        $upd = $pdo->prepare(
            "UPDATE lessons
             SET level = ?, subject = ?, title = ?, title_normalized = ?, content_en = ?, content_ha = ?, lesson_signature = ?
             WHERE id = ?"
        );
        $upd->execute([$level, $subject, $title, $titleSignature, $en, $ha, $lessonSignature, $id]);
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? null) === 1062) {
            http_response_code(409);
            echo json_encode(["ok" => false, "error" => "This lesson already exists."]);
            exit;
        }
        throw $e;
    }

    echo json_encode(["ok" => true]);
    exit;
}

// ── DELETE /api/lessons.php?id=X
if ($method === "DELETE") {
    session_start();
    if (empty($_SESSION["admin_logged_in"])) {
        http_response_code(401); echo json_encode(["ok"=>false,"error"=>"Not authenticated"]); exit;
    }
    $id = intval($_GET["id"] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(["ok"=>false,"error"=>"Missing id"]); exit; }
    $pdo->prepare("DELETE FROM lessons WHERE id = ?")->execute([$id]);
    echo json_encode(["ok" => true]);
    exit;
}

http_response_code(405);
echo json_encode(["ok" => false, "error" => "Method not allowed"]);
