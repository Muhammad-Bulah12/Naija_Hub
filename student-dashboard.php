<?php
session_start();
if (!isset($_SESSION["student_logged_in"]) || $_SESSION["student_logged_in"] !== true) {
    header("Location: login.php");
    exit;
}

$fullName = $_SESSION["student_full_name"];
$classLevel = $_SESSION["student_class"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard | Naija Students Learning Hub</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <style>
    .welcome-banner {
      background: linear-gradient(135deg, #0f6a57, #174c8f);
      color: white;
      padding: 3rem;
      border-radius: 30px;
      margin-bottom: 2rem;
    }
    .action-card {
      padding: 2rem;
      border-radius: 20px;
      background: white;
      border: 1px solid #eef2f6;
      display: flex;
      align-items: center;
      gap: 1.5rem;
      transition: transform 0.3s;
      text-decoration: none;
      color: inherit;
    }
    .action-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.05);
    }
    .action-icon {
      width: 60px;
      height: 60px;
      background: #eef7f3;
      color: #1a6b3a;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 15px;
      font-size: 1.5rem;
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
        <a href="subjects.html">Subjects</a>
        <a href="logout.php">Logout</a>
      </nav>
    </div>
  </header>

  <main class="section">
    <div class="container">
      <div class="welcome-banner">
        <h1>Hello, <?php echo htmlspecialchars($fullName); ?>!</h1>
        <p>You are currently in <strong><?php echo htmlspecialchars($classLevel); ?></strong>. Ready to learn something new today?</p>
      </div>

      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:1.5rem;">
        <a href="subjects.html" class="action-card">
          <div class="action-icon"><i class="ph ph-books"></i></div>
          <div>
            <h3>Open Lessons</h3>
            <p>Browse lessons and catch up on your studies.</p>
          </div>
        </a>
        <a href="subjects.html#teacherQuizSection" class="action-card">
          <div class="action-icon"><i class="ph ph-exam"></i></div>
          <div>
            <h3>Take a Quiz</h3>
            <p>Test your knowledge and see your progress.</p>
          </div>
        </a>
        <a href="ask.html" class="action-card">
          <div class="action-icon"><i class="ph ph-chat-centered-dots"></i></div>
          <div>
            <h3>Ask Teacher</h3>
            <p>Got a question? Send it to your assigned class monitor.</p>
          </div>
        </a>
      </div>
    </div>
  </main>

  <footer class="site-footer">
    <div class="container">
      <p>&copy; <?php echo date("Y"); ?> Naija Students Learning Hub. Keep learning, keep growing!</p>
    </div>
  </footer>
</body>
</html>
