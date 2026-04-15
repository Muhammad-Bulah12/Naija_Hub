<?php
session_start();
require_once __DIR__ . '/db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST["full_name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $schoolCode = strtoupper(trim($_POST["school_code"] ?? ""));
    $classLevel = $_POST["class_level"] ?? "";

    if (!$fullName || !$username || !$email || !$password || !$schoolCode || !$classLevel) {
        $error = "All fields are required.";
    } else {
        try {
            // Validate school code
            $stmt = $pdo->prepare("SELECT id FROM schools WHERE school_code = ? LIMIT 1");
            $stmt->execute([$schoolCode]);
            $school = $stmt->fetch();

            if (!$school) {
                $error = "Invalid School Code. Please check with your teacher.";
            } else {
                // Check if username already exists
                $check = $pdo->prepare("SELECT id FROM students WHERE username = ? OR email = ? LIMIT 1");
                $check->execute([$username, $email]);
                if ($check->fetch()) {
                    $error = "Username or Email already taken.";
                } else {
                    $insert = $pdo->prepare("INSERT INTO students (school_id, full_name, username, email, password, class_level) VALUES (?, ?, ?, ?, ?, ?)");
                    $insert->execute([
                        $school['id'],
                        $fullName,
                        $username,
                        $email,
                        password_hash($password, PASSWORD_DEFAULT),
                        $classLevel
                    ]);
                    $success = "Registration successful! You can now log in.";
                }
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Registration | Naija Students Learning Hub</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <style>
    .auth-card {
      max-width: 600px;
      margin: 2rem auto;
      padding: 3rem;
      background: #fff;
      border-radius: 30px;
      box-shadow: 0 15px 40px rgba(0,0,0,0.08);
      border: 1px solid #edf2f7;
    }
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
    }
    @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="container nav-wrap">
      <a class="brand" href="index.html">
        <img src="assets/logo.png" alt="Logo" class="brand-logo">
        <span>Naija Learning Hub</span>
      </a>
    </div>
  </header>

  <main class="section">
    <div class="container narrow">
      <div class="auth-card">
        <h1>Student Registration</h1>
        <p>Create your account and join your school's learning community.</p>

        <?php if ($error): ?>
          <p class="status-text" style="color:#c0392b;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
          <p class="status-text" style="color:#1a6b3a; font-weight:700;"><?php echo htmlspecialchars($success); ?> <a href="login.php">Login here</a></p>
        <?php else: ?>
          <form method="post" action="student-register.php">
            <div class="form-grid">
              <div>
                <label class="field-label" for="full_name">Full Name</label>
                <input class="text-input" id="full_name" name="full_name" type="text" placeholder="e.g. Musa Ibrahim" required>
              </div>
              <div>
                <label class="field-label" for="username">Username</label>
                <input class="text-input" id="username" name="username" type="text" placeholder="e.g. musai" required>
              </div>
              <div>
                <label class="field-label" for="email">Email Address</label>
                <input class="text-input" id="email" name="email" type="email" placeholder="e.g. musa@student.com" required>
              </div>
              <div>
                <label class="field-label" for="password">Password</label>
                <input class="text-input" id="password" name="password" type="password" required>
              </div>
              <div>
                <label class="field-label" for="school_code">School Code</label>
                <input class="text-input" id="school_code" name="school_code" type="text" placeholder="6-character code" required>
              </div>
              <div>
                <label class="field-label" for="class_level">Class Level</label>
                <select id="class_level" name="class_level" class="text-input" required>
                  <option value="">Select Class</option>
                  <optgroup label="JSS">
                    <option value="JSS 1">JSS 1</option>
                    <option value="JSS 2">JSS 2</option>
                    <option value="JSS 3">JSS 3</option>
                  </optgroup>
                  <optgroup label="SS">
                    <option value="SS 1">SS 1</option>
                    <option value="SS 2">SS 2</option>
                    <option value="SS 3">SS 3</option>
                  </optgroup>
                </select>
              </div>
            </div>
            <button class="btn btn-primary" type="submit" style="width:100%; margin-top:2rem;">Create Student Account</button>
            <p style="text-align:center; margin-top:1.5rem; font-size:0.9rem;">Already have an account? <a href="login.php" style="color:#1a6b3a; font-weight:600;">Log in here</a></p>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </main>
</body>
</html>
