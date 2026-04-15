<?php
// api/quizzes.php  –  CRUD for quiz questions
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

// ── GET /api/quizzes.php  → list (optional ?level=JSS+1&subject=English)
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
    $sql = "SELECT q.id, q.level, q.subject, q.question_en, q.question_ha,
                   q.opt0_en, q.opt1_en, q.opt2_en, q.opt3_en,
                   q.opt0_ha, q.opt1_ha, q.opt2_ha, q.opt3_ha,
                   q.correct_idx, q.created_at, t.username AS teacher
            FROM quizzes q
            JOIN teachers t ON t.id = q.teacher_id"
         . ($where ? " WHERE " . implode(" AND ", $where) : "")
         . " ORDER BY q.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    // Shape into friendly format
    $out = array_map(function($r) {
        return [
            "id"          => $r["id"],
            "level"       => $r["level"],
            "subject"     => $r["subject"],
            "questionEn"  => $r["question_en"],
            "questionHa"  => $r["question_ha"],
            "optionsEn"   => [$r["opt0_en"], $r["opt1_en"], $r["opt2_en"], $r["opt3_en"]],
            "optionsHa"   => [$r["opt0_ha"], $r["opt1_ha"], $r["opt2_ha"], $r["opt3_ha"]],
            "answer"      => (int)$r["correct_idx"],
            "teacher"     => $r["teacher"],
            "created_at"  => $r["created_at"],
        ];
    }, $rows);
    echo json_encode(["ok" => true, "quizzes" => $out]);
    exit;
}

// ── POST /api/quizzes.php  → add question
if ($method === "POST") {
    session_start();
    if (empty($_SESSION["admin_logged_in"])) {
        http_response_code(401); echo json_encode(["ok"=>false,"error"=>"Not authenticated"]); exit;
    }
    $body = json_decode(file_get_contents("php://input"), true) ?? [];
    $level      = trim($body["level"]      ?? "");
    $subject    = trim($body["subject"]    ?? "");
    $qen        = trim($body["questionEn"] ?? "");
    $qha        = trim($body["questionHa"] ?? "");
    $optsEn     = $body["optionsEn"] ?? [];
    $optsHa     = $body["optionsHa"] ?? ["","","",""];
    $answer     = intval($body["answer"] ?? -1);

    if (!$level || !$subject || !$qen || count($optsEn) < 4 || $answer < 0 || $answer > 3) {
        http_response_code(400);
        echo json_encode(["ok"=>false,"error"=>"All fields required (level, subject, question, 4 options, answer index 0-3)"]);
        exit;
    }

    $teacherId = (int) ($_SESSION["admin_teacher_id"] ?? 0);
    if ($teacherId <= 0) {
        http_response_code(403);
        echo json_encode(["ok" => false, "error" => "Unknown teacher"]);
        exit;
    }

    $questionSignature = normalize_text($qen);
    $quizSignature = hash('sha256', implode('|', [
        normalize_text($level),
        normalize_text($subject),
        $questionSignature,
        normalize_text($qha),
        normalize_text((string) ($optsEn[0] ?? '')),
        normalize_text((string) ($optsEn[1] ?? '')),
        normalize_text((string) ($optsEn[2] ?? '')),
        normalize_text((string) ($optsEn[3] ?? '')),
        normalize_text((string) ($optsHa[0] ?? '')),
        normalize_text((string) ($optsHa[1] ?? '')),
        normalize_text((string) ($optsHa[2] ?? '')),
        normalize_text((string) ($optsHa[3] ?? '')),
        (string) $answer,
    ]));

    $existing = $pdo->prepare(
        "SELECT q.id, q.created_at, t.username AS teacher
         FROM quizzes q
         JOIN teachers t ON t.id = q.teacher_id
         WHERE q.quiz_signature = ? OR (q.level = ? AND q.subject = ? AND q.question_normalized = ?)
         LIMIT 1"
    );
    $existing->execute([$quizSignature, $level, $subject, $questionSignature]);
    $duplicate = $existing->fetch();
    if ($duplicate) {
        http_response_code(409);
        echo json_encode([
            "ok" => false,
            "error" => "A similar quiz question already exists.",
            "duplicate" => [
                "id" => (int) $duplicate["id"],
                "teacher" => $duplicate["teacher"],
                "created_at" => $duplicate["created_at"],
            ],
        ]);
        exit;
    }

    try {
        $ins = $pdo->prepare("INSERT INTO quizzes
            (teacher_id, level, subject, question_en, question_normalized, question_ha,
             opt0_en, opt1_en, opt2_en, opt3_en,
             opt0_ha, opt1_ha, opt2_ha, opt3_ha, correct_idx, quiz_signature)
            VALUES (?,?,?,?,?, ?,?,?,?, ?,?,?,?, ?,?, ?)");
        $ins->execute([
            $teacherId, $level, $subject, $qen, $questionSignature, $qha,
            $optsEn[0], $optsEn[1], $optsEn[2], $optsEn[3],
            $optsHa[0] ?? '', $optsHa[1] ?? '', $optsHa[2] ?? '', $optsHa[3] ?? '',
            $answer, $quizSignature
        ]);
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? null) === 1062) {
            http_response_code(409);
            echo json_encode(["ok" => false, "error" => "This quiz question already exists."]);
            exit;
        }
        throw $e;
    }

    echo json_encode(["ok" => true, "id" => $pdo->lastInsertId()]);
    exit;
}

// ── PUT /api/quizzes.php?id=X  → update question
if ($method === "PUT") {
    session_start();
    if (empty($_SESSION["admin_logged_in"])) {
        http_response_code(401); echo json_encode(["ok"=>false,"error"=>"Not authenticated"]); exit;
    }
    $id = intval($_GET["id"] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(["ok"=>false,"error"=>"Missing id"]); exit; }

    $body = json_decode(file_get_contents("php://input"), true) ?? [];
    $level      = trim($body["level"]      ?? "");
    $subject    = trim($body["subject"]    ?? "");
    $qen        = trim($body["questionEn"] ?? "");
    $qha        = trim($body["questionHa"] ?? "");
    $optsEn     = $body["optionsEn"] ?? [];
    $optsHa     = $body["optionsHa"] ?? ["","","",""];
    $answer     = intval($body["answer"] ?? -1);

    if (!$level || !$subject || !$qen || count($optsEn) < 4 || $answer < 0 || $answer > 3) {
        http_response_code(400);
        echo json_encode(["ok"=>false,"error"=>"All fields required (level, subject, question, 4 options, answer index 0-3)"]);
        exit;
    }

    $questionSignature = normalize_text($qen);
    $quizSignature = hash('sha256', implode('|', [
        normalize_text($level),
        normalize_text($subject),
        $questionSignature,
        normalize_text($qha),
        normalize_text((string) ($optsEn[0] ?? '')),
        normalize_text((string) ($optsEn[1] ?? '')),
        normalize_text((string) ($optsEn[2] ?? '')),
        normalize_text((string) ($optsEn[3] ?? '')),
        normalize_text((string) ($optsHa[0] ?? '')),
        normalize_text((string) ($optsHa[1] ?? '')),
        normalize_text((string) ($optsHa[2] ?? '')),
        normalize_text((string) ($optsHa[3] ?? '')),
        (string) $answer,
    ]));

    $existing = $pdo->prepare(
        "SELECT q.id, q.created_at, t.username AS teacher
         FROM quizzes q
         JOIN teachers t ON t.id = q.teacher_id
         WHERE (q.quiz_signature = ? OR (q.level = ? AND q.subject = ? AND q.question_normalized = ?))
           AND q.id <> ?
         LIMIT 1"
    );
    $existing->execute([$quizSignature, $level, $subject, $questionSignature, $id]);
    $duplicate = $existing->fetch();
    if ($duplicate) {
        http_response_code(409);
        echo json_encode([
            "ok" => false,
            "error" => "A similar quiz question already exists.",
            "duplicate" => [
                "id" => (int) $duplicate["id"],
                "teacher" => $duplicate["teacher"],
                "created_at" => $duplicate["created_at"],
            ],
        ]);
        exit;
    }

    try {
        $upd = $pdo->prepare("UPDATE quizzes
            SET level = ?, subject = ?, question_en = ?, question_normalized = ?, question_ha = ?,
                opt0_en = ?, opt1_en = ?, opt2_en = ?, opt3_en = ?,
                opt0_ha = ?, opt1_ha = ?, opt2_ha = ?, opt3_ha = ?, correct_idx = ?, quiz_signature = ?
            WHERE id = ?");
        $upd->execute([
            $level, $subject, $qen, $questionSignature, $qha,
            $optsEn[0], $optsEn[1], $optsEn[2], $optsEn[3],
            $optsHa[0] ?? '', $optsHa[1] ?? '', $optsHa[2] ?? '', $optsHa[3] ?? '',
            $answer, $quizSignature, $id
        ]);
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? null) === 1062) {
            http_response_code(409);
            echo json_encode(["ok" => false, "error" => "This quiz question already exists."]);
            exit;
        }
        throw $e;
    }

    echo json_encode(["ok" => true]);
    exit;
}

// ── DELETE /api/quizzes.php?id=X
if ($method === "DELETE") {
    session_start();
    if (empty($_SESSION["admin_logged_in"])) {
        http_response_code(401); echo json_encode(["ok"=>false,"error"=>"Not authenticated"]); exit;
    }
    $id = intval($_GET["id"] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(["ok"=>false,"error"=>"Missing id"]); exit; }
    $pdo->prepare("DELETE FROM quizzes WHERE id = ?")->execute([$id]);
    echo json_encode(["ok" => true]);
    exit;
}

http_response_code(405);
echo json_encode(["ok" => false, "error" => "Method not allowed"]);
