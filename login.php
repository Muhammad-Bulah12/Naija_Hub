<?php
session_start();

if (isset($_SESSION["admin_logged_in"]) && $_SESSION["admin_logged_in"] === true) {
    header("Location: admin.php");
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

if (file_exists(__DIR__ . "/db.php")) {
    require_once __DIR__ . "/db.php";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($pdo)) {
    $formAction = $_POST["form_action"] ?? "login";

    if ($formAction === "register") {
        $fullNameValue = trim($_POST["full_name"] ?? "");
        $usernameValue = normalize_username($_POST["register_username"] ?? "");
        $emailValue = trim($_POST["email"] ?? "");
        $password = trim($_POST["register_password"] ?? "");
        $confirmPassword = trim($_POST["confirm_password"] ?? "");

        if ($fullNameValue === "" || $usernameValue === "" || $emailValue === "" || $password === "" || $confirmPassword === "") {
            $error = "Fill in all account fields before creating a teacher account.";
        } elseif (!filter_var($emailValue, FILTER_VALIDATE_EMAIL)) {
            $error = "Enter a valid email address.";
        } elseif (strlen($usernameValue) < 4) {
            $error = "Username must be at least 4 characters.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
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
                        "INSERT INTO teachers (full_name, username, email, password)
                         VALUES (?, ?, ?, ?)"
                    );
                    $insert->execute([
                        $fullNameValue,
                        $usernameValue,
                        $emailValue,
                        password_hash($password, PASSWORD_DEFAULT),
                    ]);
                    $success = "Account created. The teacher can now log in with username or email and password.";
                    $fullNameValue = "";
                    $emailValue = "";
                    $usernameValue = "";
                }
            } catch (Exception $e) {
                $error = "Could not create the account. Check the database setup.";
            }
        }
    }

    if ($formAction === "login") {
        $usernameValue = trim($_POST["username"] ?? "");
        $password      = trim($_POST["password"] ?? "");
        $authenticated = false;

        try {
            $stmt = $pdo->prepare(
                "SELECT id, full_name, username, email, password
                 FROM teachers
                 WHERE username = ? OR email = ?
                 LIMIT 1"
            );
            $stmt->execute([$usernameValue, $usernameValue]);
            $row = $stmt->fetch();
            if ($row && password_verify($password, $row["password"])) {
                $authenticated = true;
                session_regenerate_id(true);
                $_SESSION["admin_teacher_id"] = (int) $row["id"];
                $_SESSION["admin_username"] = $row["username"];
                $_SESSION["admin_full_name"] = $row["full_name"] ?: $row["username"];
                $_SESSION["admin_email"] = $row["email"] ?? "";
            }
        } catch (Exception $e) {
            $error = "Login is temporarily unavailable. Check the database connection.";
        }

        if ($authenticated) {
            $_SESSION["admin_logged_in"] = true;
            header("Location: admin.php");
            exit;
        }

        if ($error === "") {
            $error = "Invalid username/email or password.";
        }
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $error = "Database connection is required before teachers can register or log in.";
}

function old_value(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Login | Naija Students Learning Hub</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <style>
    .auth-shell {
      display: grid;
      gap: 1.4rem;
    }
    .auth-hero {
      display: grid;
      grid-template-columns: 1.2fr 0.8fr;
      gap: 1.2rem;
      align-items: stretch;
    }
    .auth-banner {
      padding: 1.6rem;
      border-radius: 24px;
      background: linear-gradient(140deg, #0f6a57, #174c8f);
      color: #fff;
      box-shadow: 0 18px 40px rgba(16, 42, 67, 0.16);
    }
    .auth-banner .eyebrow {
      color: #f8d36f;
    }
    .auth-banner p {
      color: rgba(255,255,255,0.88);
      margin-bottom: 0;
    }
    .auth-highlights {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0.9rem;
    }
    .auth-highlight {
      padding: 1rem 1.1rem;
      border-radius: 20px;
      background: linear-gradient(180deg, #ffffff 0%, #f5fbff 100%);
      border: 1px solid #d7e4f1;
      box-shadow: 0 10px 24px rgba(16, 42, 67, 0.08);
    }
    .auth-highlight strong {
      display: block;
      margin-bottom: 0.35rem;
      font-size: 1rem;
      color: #102a43;
      font-family: "Sora", "Trebuchet MS", sans-serif;
    }
    .auth-highlight span {
      color: #486581;
      font-size: 0.9rem;
    }
    .auth-grid {
      display: grid;
      gap: 1.2rem;
      grid-template-columns: 1fr;
    }
    .auth-note {
      margin-top: 1rem;
      padding: 1rem;
      border-radius: 14px;
      background: #f7fbff;
      border: 1px solid #d7e4f1;
      color: #486581;
    }
    @media (max-width: 860px) {
      .auth-hero {
        grid-template-columns: 1fr;
      }
    }
    @media (min-width: 860px) {
      .auth-grid {
        grid-template-columns: 1fr 1fr;
      }
    }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="container nav-wrap">
      <a class="brand" href="index.html">
        <img src="assets/logo.png" alt="Logo" class="brand-logo">
        <span>Naija Students Learning Hub</span>
      </a>
      <nav class="main-nav">
        <a href="index.html">Home</a>
        <a href="subjects.html">Subjects</a>
        <a href="ask.html">Ask Your Teacher</a>
      </nav>
    </div>
  </header>

  <main class="section">
    <div class="container narrow">
      <div class="auth-shell">
      <div class="auth-hero">
        <section class="auth-banner">
          <p class="eyebrow">Teacher Dashboard</p>
          <h1 style="margin:0 0 0.8rem;">Teacher Accounts</h1>
          <p>Each teacher should use a personal account. Duplicate lessons and quizzes are blocked automatically, and the portal tells staff when the same content has already been uploaded.</p>
        </section>
        <section class="auth-highlights">
          <article class="auth-highlight">
            <strong>Personal Access</strong>
            <span>Every teacher signs in with a separate account.</span>
          </article>
          <article class="auth-highlight">
            <strong>Duplicate Warning</strong>
            <span>The system shows when content already exists.</span>
          </article>
          <article class="auth-highlight">
            <strong>Lesson Ownership</strong>
            <span>Uploads are tagged to the teacher who created them.</span>
          </article>
          <article class="auth-highlight">
            <strong>Simple Login</strong>
            <span>Use either username or email with password.</span>
          </article>
        </section>
      </div>

      <div class="page-intro">
        <p class="eyebrow">Teacher Access</p>
        <h1>Login or Create an Account</h1>
        <p>Use the login form if you already have access, or create a new teacher account to start uploading lessons and quizzes.</p>
      </div>

      <?php if ($error): ?>
        <p class="status-text" style="color:#c0392b;margin:0 0 1rem;"><?php echo old_value($error); ?></p>
      <?php endif; ?>
      <?php if ($success): ?>
        <p class="status-text" style="color:#1a6b3a;margin:0 0 1rem;"><?php echo old_value($success); ?></p>
      <?php endif; ?>

      <div class="auth-grid">
        <section class="card">
          <h2 style="margin-top:0;">Login</h2>
          <form method="post" action="login.php" novalidate>
            <input type="hidden" name="form_action" value="login">

            <label class="field-label" for="username">Username or Email</label>
            <input class="text-input" id="username" name="username" type="text"
                   value="<?php echo old_value($usernameValue); ?>" required autofocus>

            <label class="field-label" for="password">Password</label>
            <input class="text-input" id="password" name="password" type="password" required>

            <button class="btn btn-primary" type="submit">Login</button>
          </form>
          <div class="auth-note">
            Login details: `username or email` + `password`
          </div>
        </section>

        <section class="card">
          <h2 style="margin-top:0;">Create Teacher Account</h2>
          <form method="post" action="login.php" novalidate>
            <input type="hidden" name="form_action" value="register">

            <label class="field-label" for="full_name">Full Name</label>
            <input class="text-input" id="full_name" name="full_name" type="text"
                   value="<?php echo old_value($fullNameValue); ?>" required>

            <label class="field-label" for="register_username">Username</label>
            <input class="text-input" id="register_username" name="register_username" type="text"
                   value="<?php echo old_value($usernameValue); ?>" required>

            <label class="field-label" for="email">Email</label>
            <input class="text-input" id="email" name="email" type="email"
                   value="<?php echo old_value($emailValue); ?>" required>

            <label class="field-label" for="register_password">Password</label>
            <input class="text-input" id="register_password" name="register_password" type="password" required>

            <label class="field-label" for="confirm_password">Confirm Password</label>
            <input class="text-input" id="confirm_password" name="confirm_password" type="password" required>

            <button class="btn btn-secondary" type="submit">Create Account</button>
          </form>
          <div class="auth-note">
            Every teacher gets a personal account. Uploads are saved with that teacher name, and if the same content already exists the portal shows a duplicate message instead of saving it again.
          </div>
        </section>
      </div>
      </div>
    </div>
  </main>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col">
          <a class="brand" href="index.html" style="margin-bottom:1.5rem; color:var(--text);">
            <img src="assets/logo.png" alt="Logo" class="brand-logo">
            <span>Learning Hub</span>
          </a>
          <p>Supporting teachers and students with accessible lessons, quizzes, and guided learning tools in Nigeria.</p>
          <div class="footer-social">
            <a href="#" class="social-box"><i class="ph ph-facebook-logo"></i></a>
            <a href="#" class="social-box"><i class="ph ph-instagram-logo"></i></a>
            <a href="#" class="social-box"><i class="ph ph-x-logo"></i></a>
            <a href="#" class="social-box"><i class="ph ph-linkedin-logo"></i></a>
          </div>
        </div>
        <div class="footer-col">
          <h4>Quick Links</h4>
          <nav class="footer-nav">
            <a href="subjects.html">Browse Subjects</a>
            <a href="ask.html">Ask Your Teacher</a>
            <a href="login.php">Teacher Portal</a>
            <a href="index.html">Home Page</a>
          </nav>
        </div>
        <div class="footer-col">
          <h4>Educational Links</h4>
          <nav class="footer-nav">
            <a href="https://education.gov.ng/" target="_blank">Federal Ministry of Education</a>
            <a href="https://ubec.gov.ng/" target="_blank">UBEC</a>
            <a href="https://nerdc.gov.ng/" target="_blank">NERDC</a>
            <a href="https://www.unicef.org/nigeria/education" target="_blank">UNICEF Nigeria</a>
          </nav>
        </div>
        <div class="footer-col">
          <h4>Contact & Support</h4>
          <p>Helping learners across underserved communities in Yobe State and Nigeria.</p>
          <p style="margin-top:1rem;"><strong>Email:</strong> info@naijahub.local</p>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> Naija Students Learning Hub. All rights reserved.</p>
        <div style="display:flex; gap:1.5rem;">
          <a href="#" style="color:var(--muted);">Privacy Policy</a>
          <a href="#" style="color:var(--muted);">Terms of Use</a>
        </div>
      </div>
    </div>
  </footer>
</body>
</html>
