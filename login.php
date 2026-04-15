<?php
session_start();
require_once __DIR__ . "/db.php";

// Redirect if already logged in
if (isset($_SESSION["admin_logged_in"]) && $_SESSION["admin_logged_in"] === true) {
    header("Location: admin.php");
    exit;
}
if (isset($_SESSION["student_logged_in"]) && $_SESSION["student_logged_in"] === true) {
    header("Location: student-dashboard.php");
    exit;
}

$fullNameValue = "";
$emailValue = "";
$error = "";
$success = "";
$usernameValue = "";

function normalize_username(string $value): string {
    return preg_replace('/[^a-z0-9._-]/i', '', trim($value)) ?? '';
}

function old_value(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Fetch schools for registration dropdown
$schools = [];
try {
    $stmt = $pdo->query("SELECT id, school_name, school_code FROM schools ORDER BY school_name ASC");
    $schools = $stmt->fetchAll();
} catch (Exception $e) {}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($pdo)) {
    $formAction = $_POST["form_action"] ?? "login";

    if ($formAction === "register") {
        $fullNameValue = trim($_POST["full_name"] ?? "");
        $usernameValue = normalize_username($_POST["register_username"] ?? "");
        $emailValue = trim($_POST["email"] ?? "");
        $password = trim($_POST["register_password"] ?? "");
        $confirmPassword = trim($_POST["confirm_password"] ?? "");
        $schoolId = $_POST["school_id"] ?? "";
        $assignedClass = $_POST["assigned_class"] ?? "";

        if ($fullNameValue === "" || $usernameValue === "" || $emailValue === "" || $password === "" || $schoolId === "" || $assignedClass === "") {
            $error = "Fill in ALL account fields before creating a teacher account.";
        } elseif ($password !== $confirmPassword) {
            $error = "Passwords do not match.";
        } else {
            try {
                $check = $pdo->prepare("SELECT id FROM teachers WHERE username = ? OR email = ? LIMIT 1");
                $check->execute([$usernameValue, $emailValue]);
                if ($check->fetch()) {
                    $error = "That username or email is already in use.";
                } else {
                    $insert = $pdo->prepare(
                        "INSERT INTO teachers (school_id, full_name, username, email, password, assigned_class)
                         VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    $insert->execute([
                        $schoolId,
                        $fullNameValue,
                        $usernameValue,
                        $emailValue,
                        password_hash($password, PASSWORD_DEFAULT),
                        $assignedClass
                    ]);
                    $success = "Teacher account created. You can now log in.";
                    $fullNameValue = ""; $emailValue = ""; $usernameValue = "";
                }
            } catch (Exception $e) {
                $error = "Could not create the account. " . $e->getMessage();
            }
        }
    }

    if ($formAction === "login") {
        $usernameValue = trim($_POST["username"] ?? "");
        $password      = trim($_POST["password"] ?? "");
        $userType      = $_POST["user_type"] ?? "teacher";

        try {
            if ($userType === "teacher") {
                $stmt = $pdo->prepare("SELECT * FROM teachers WHERE username = ? OR email = ? LIMIT 1");
                $stmt->execute([$usernameValue, $usernameValue]);
                $row = $stmt->fetch();
                if ($row && password_verify($password, $row["password"])) {
                    $_SESSION = [];
                    session_regenerate_id(true);
                    $_SESSION["admin_logged_in"] = true;
                    $_SESSION["admin_teacher_id"] = (int) $row["id"];
                    $_SESSION["admin_school_id"] = (int) $row["school_id"];
                    $_SESSION["admin_username"] = $row["username"];
                    $_SESSION["admin_full_name"] = $row["full_name"];
                    $_SESSION["assigned_class"] = $row["assigned_class"];
                    header("Location: admin.php");
                    exit;
                }
            } else {
                // Student login
                $stmt = $pdo->prepare("SELECT * FROM students WHERE username = ? OR email = ? LIMIT 1");
                $stmt->execute([$usernameValue, $usernameValue]);
                $row = $stmt->fetch();
                if ($row && password_verify($password, $row["password"])) {
                    $_SESSION = [];
                    session_regenerate_id(true);
                    $_SESSION["student_logged_in"] = true;
                    $_SESSION["student_id"] = (int) $row["id"];
                    $_SESSION["student_username"] = $row["username"];
                    $_SESSION["student_full_name"] = $row["full_name"];
                    $_SESSION["student_school_id"] = (int) $row["school_id"];
                    $_SESSION["student_class"] = $row["class_level"];
                    header("Location: student-dashboard.php");
                    exit;
                }
            }
            $error = "Invalid credentials for the selected user type.";
        } catch (Exception $e) {
            $error = "Login error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Naija Students Learning Hub</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <style>
    .auth-shell { display: grid; gap: 1.5rem; }
    .auth-hero { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 1.2rem; }
    .auth-banner { padding: 2rem; border-radius: 24px; background: linear-gradient(140deg, #0f6a57, #174c8f); color: #fff; }
    .auth-grid { display: grid; gap: 1.5rem; grid-template-columns: 1fr 1fr; }
    @media (max-width: 860px) { .auth-hero, .auth-grid { grid-template-columns: 1fr; } }
    .tab-btn { padding: 0.6rem 1.2rem; border-radius: 50px; border: 1px solid #ddd; background: #f9f9f9; cursor: pointer; font-weight: 600; }
    .tab-btn.active { background: #1a6b3a; color: white; border-color: #1a6b3a; }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="container nav-wrap">
      <a class="brand" href="index.html">
        <img src="assets/logo.png" alt="Logo" class="brand-logo">
        <span>Naija Students Hub</span>
      </a>
      <nav class="main-nav">
        <a href="index.html">Home</a>
        <a href="school-register.php" style="color:#1a6b3a; font-weight:700;">Register School</a>
      </nav>
    </div>
  </header>

  <main class="section">
    <div class="container">
      <div class="auth-shell">
        <section class="auth-banner">
          <h1>Welcome to the Hub</h1>
          <p>Login as a Teacher to manage lessons and track progress, or as a Student to learn and take quizzes.</p>
        </section>

        <?php if ($error): ?>
          <p class="status-text" style="color:#c0392b;"><?php echo old_value($error); ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
          <p class="status-text" style="color:#1a6b3a;"><?php echo old_value($success); ?></p>
        <?php endif; ?>

        <div class="auth-grid">
          <section class="card">
            <h2>Login</h2>
            <form method="post" action="login.php">
              <input type="hidden" name="form_action" value="login">
              
              <label class="field-label">I am a:</label>
              <div style="margin-bottom:1rem; display:flex; gap:0.5rem;">
                <label style="cursor:pointer;"><input type="radio" name="user_type" value="teacher" checked> Teacher</label>
                <label style="cursor:pointer;"><input type="radio" name="user_type" value="student"> Student</label>
              </div>

              <label class="field-label">Username or Email</label>
              <input class="text-input" name="username" type="text" required>

              <label class="field-label">Password</label>
              <input class="text-input" name="password" type="password" required>

              <button class="btn btn-primary" type="submit" style="width:100%;">Login</button>
            </form>
            <p style="margin-top:1rem; font-size:0.9rem;">Student without account? <a href="student-register.php">Register here</a></p>
          </section>

          <section class="card">
            <h2>Teacher Registration</h2>
            <form method="post" action="login.php">
              <input type="hidden" name="form_action" value="register">

              <label class="field-label">Full Name</label>
              <input class="text-input" name="full_name" type="text" value="<?php echo old_value($fullNameValue); ?>" required>

              <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div>
                  <label class="field-label">Username</label>
                  <input class="text-input" name="register_username" type="text" value="<?php echo old_value($usernameValue); ?>" required>
                </div>
                <div>
                  <label class="field-label">Email</label>
                  <input class="text-input" name="email" type="email" value="<?php echo old_value($emailValue); ?>" required>
                </div>
              </div>

              <label class="field-label">Select Your School</label>
              <select class="text-input" name="school_id" required>
                <option value="">-- Select School --</option>
                <?php foreach ($schools as $s): ?>
                  <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['school_name']); ?></option>
                <?php endforeach; ?>
              </select>

              <label class="field-label">Assigned Class (Monitor)</label>
              <select class="text-input" name="assigned_class" required>
                <option value="">-- Select Class --</option>
                <optgroup label="Junior Secondary">
                  <option value="JSS 1">JSS 1</option>
                  <option value="JSS 2">JSS 2</option>
                  <option value="JSS 3">JSS 3</option>
                </optgroup>
                <optgroup label="Senior Secondary">
                  <option value="SS 1">SS 1</option>
                  <option value="SS 2">SS 2</option>
                  <option value="SS 3">SS 3</option>
                </optgroup>
              </select>

              <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div>
                  <label class="field-label">Password</label>
                  <input class="text-input" name="register_password" type="password" required>
                </div>
                <div>
                  <label class="field-label">Confirm Password</label>
                  <input class="text-input" name="confirm_password" type="password" required>
                </div>
              </div>

              <button class="btn btn-secondary" type="submit" style="width:100%; margin-top:1rem;">Register as Teacher</button>
            </form>
          </section>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
