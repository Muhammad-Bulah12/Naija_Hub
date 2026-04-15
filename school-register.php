<?php
session_start();
require_once __DIR__ . '/db.php';

$error = "";
$success = "";
$schoolCode = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $schoolName = trim($_POST["school_name"] ?? "");
    $location   = trim($_POST["location"] ?? "");

    if ($schoolName === "" || $location === "") {
        $error = "Please fill in all fields.";
    } else {
        try {
            // Generate a unique 6-character code
            $schoolCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

            $insert = $pdo->prepare("INSERT INTO schools (school_name, school_code, location) VALUES (?, ?, ?)");
            $insert->execute([$schoolName, $schoolCode, $location]);

            $success = "School registered successfully! Your unique School Code is: " . $schoolCode;
        } catch (Exception $e) {
            $error = "Could not register school. Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register School | Naija Students Learning Hub</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <style>
    .school-card {
      max-width: 500px;
      margin: 2rem auto;
      padding: 2.5rem;
      background: white;
      border-radius: 30px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.1);
      border: 1px solid #eef2f6;
    }
    .hero-label {
      background: #eef7f3;
      color: #1a6b3a;
      padding: 0.5rem 1rem;
      border-radius: 50px;
      font-weight: 700;
      font-size: 0.8rem;
      display: inline-block;
      margin-bottom: 1rem;
    }
    .code-box {
      background: #1a6b3a;
      color: white;
      padding: 1.5rem;
      border-radius: 20px;
      text-align: center;
      margin: 1.5rem 0;
      font-size: 2rem;
      font-weight: 800;
      letter-spacing: 0.5rem;
      box-shadow: 0 10px 20px rgba(26, 107, 58, 0.3);
    }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="container nav-wrap">
      <a class="brand" href="index.html">
        <img src="assets/logo.png" alt="Logo" class="brand-logo">
        <span>Naija Learning Hub</span>
      </a>
      <nav class="main-nav">
        <a href="index.html">Home</a>
        <a href="login.php">Login</a>
      </nav>
    </div>
  </header>

  <main class="section">
    <div class="container narrow">
      <div class="school-card">
        <span class="hero-label">Institutional Registration</span>
        <h1>Register Your School</h1>
        <p>Register your school to start tracking student progress and managing curriculum efficiently.</p>

        <?php if ($error): ?>
          <p class="status-text" style="color:#c0392b;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
          <div style="text-align:center;">
            <p class="status-text" style="color:#1a6b3a; font-weight:700;"><?php echo htmlspecialchars($success); ?></p>
            <div class="code-box"><?php echo htmlspecialchars($schoolCode); ?></div>
            <p style="font-size:0.9rem; color:#666;">Share this code with your teachers and students to join your school.</p>
            <a href="login.php" class="btn btn-primary" style="margin-top:1rem;">Continue to Login</a>
          </div>
        <?php else: ?>
          <form method="post" action="school-register.php">
            <label class="field-label" for="school_name">School Name</label>
            <input class="text-input" id="school_name" name="school_name" type="text" placeholder="e.g. Government Secondary School" required>

            <label class="field-label" for="location">Location (City/State)</label>
            <input class="text-input" id="location" name="location" type="text" placeholder="e.g. Damaturu, Yobe" required>

            <button class="btn btn-primary" type="submit" style="width:100%; margin-top:1rem;">Register School</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <footer class="site-footer">
    <div class="container">
      <p>&copy; <?php echo date("Y"); ?> Naija Students Learning Hub. Empowering education across Nigeria.</p>
    </div>
  </footer>
</body>
</html>
