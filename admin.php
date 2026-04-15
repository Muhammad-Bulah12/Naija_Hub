<?php
session_start();

if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Admin Panel | Naija Students Learning Hub</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* ── Admin-specific enhancements ── */
    .dashboard-shell {
      display: grid;
      gap: 1.5rem;
    }
    .dashboard-hero {
      display: grid;
      grid-template-columns: 1.3fr 0.9fr;
      gap: 1.2rem;
      align-items: stretch;
    }
    .dashboard-banner {
      padding: 1.6rem;
      border-radius: 24px;
      background: linear-gradient(140deg, #0f6a57, #174c8f);
      color: #fff;
      box-shadow: 0 18px 40px rgba(16, 42, 67, 0.16);
    }
    .dashboard-banner .eyebrow { color: #f8d36f; }
    .dashboard-banner p { color: rgba(255,255,255,0.88); margin-bottom: 0; }
    .dashboard-stats {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0.9rem;
    }
    .stat-card {
      padding: 1rem 1.1rem;
      border-radius: 20px;
      background: linear-gradient(180deg, #ffffff 0%, #f5fbff 100%);
      border: 1px solid #d7e4f1;
      box-shadow: 0 10px 24px rgba(16, 42, 67, 0.08);
    }
    .stat-card strong {
      display: block;
      font-size: 1.45rem;
      color: #102a43;
      font-family: "Sora", "Trebuchet MS", sans-serif;
    }
    .stat-card span {
      color: #486581;
      font-size: 0.9rem;
    }
    .admin-tabs {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 0;
      flex-wrap: wrap;
    }
    .admin-tab {
      padding: 0.75rem 1.2rem;
      border: 1px solid #b7d2c4;
      border-radius: 2rem;
      background: #fff;
      color: #1a6b3a;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s, color 0.2s, transform 0.2s, box-shadow 0.2s;
    }
    .admin-tab.active, .admin-tab:hover {
      background: var(--accent, #1a6b3a);
      color: #fff;
      box-shadow: 0 12px 24px rgba(26, 107, 58, 0.2);
      transform: translateY(-1px);
    }
    .admin-panel { display: none; }
    .admin-panel.active { display: block; }
    .admin-panel.card {
      padding: 1.5rem;
      border-radius: 24px;
    }
    .panel-header {
      display: flex;
      justify-content: space-between;
      gap: 1rem;
      align-items: flex-start;
      margin-bottom: 1.2rem;
    }
    .panel-header h2 {
      margin: 0 0 0.35rem;
    }
    .panel-kicker {
      display: inline-flex;
      align-items: center;
      padding: 0.35rem 0.7rem;
      border-radius: 999px;
      background: #eef7f3;
      color: #1a6b3a;
      font-size: 0.78rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }
    .helper-card {
      padding: 1rem;
      border-radius: 18px;
      background: linear-gradient(180deg, #f7fbff 0%, #f2fbf7 100%);
      border: 1px solid #dce9f4;
      min-width: 230px;
    }
    .helper-card p {
      margin: 0;
      color: #486581;
      font-size: 0.92rem;
    }
    .content-grid {
      display: grid;
      gap: 1rem;
    }
    .content-section {
      padding: 1rem 1.1rem;
      border-radius: 18px;
      background: #fbfdff;
      border: 1px solid #e0ebf5;
    }
    .content-section h3 {
      margin: 0 0 0.7rem;
      font-size: 1rem;
    }
    .action-row {
      display: flex;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
      margin-top: 1rem;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }
    @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }

    .quiz-options-area { margin-top: 1rem; }
    .quiz-builder-toolbar {
      display: flex;
      align-items: center;
      gap: 0.7rem;
      flex-wrap: wrap;
      margin-bottom: 0.8rem;
    }
    .quiz-builder-toolbar .text-input {
      width: 110px;
      margin-bottom: 0;
    }
    .quiz-question-list {
      display: grid;
      gap: 0.9rem;
    }
    .quiz-item {
      border: 1px solid #dce9f4;
      border-radius: 16px;
      padding: 0.9rem;
      background: #fff;
    }
    .quiz-item-title {
      margin: 0 0 0.7rem;
      font-size: 0.92rem;
      color: #205ecf;
      font-weight: 800;
      letter-spacing: 0.02em;
    }
    .quiz-option-row {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 0.5rem;
    }
    .quiz-option-row input[type="text"] { flex: 1; }
    .quiz-option-row input[type="radio"] { width: 18px; height: 18px; cursor: pointer; }
    .option-label { font-size: 0.85rem; color: #666; min-width: 60px; }

    .status-text.success { color: #1a6b3a; font-weight: 600; }
    .status-text.error   { color: #c0392b; font-weight: 600; }

    .saved-card-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem; }
    @media (max-width: 640px) { .saved-card-grid { grid-template-columns: 1fr; } }

    .badge {
      display: inline-block;
      padding: 0.2rem 0.7rem;
      border-radius: 1rem;
      font-size: 0.75rem;
      font-weight: 700;
      margin-right: 0.3rem;
      text-transform: uppercase;
    }
    .badge-jss { background: #d4edda; color: #155724; }
    .badge-ss  { background: #cce5ff; color: #004085; }
    .badge-subject { background: #fff3cd; color: #856404; }
    .saved-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
    }
    .saved-note {
      margin: 0;
      color: #486581;
      font-size: 0.92rem;
    }
    @media (max-width: 860px) {
      .dashboard-hero {
        grid-template-columns: 1fr;
      }
    }
    /* ── Progress Dashboard ── */
    .progress-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-top: 1rem; }
    @media (max-width: 900px) { .progress-grid { grid-template-columns: 1fr; } }
    .progress-stat-card {
      background: #1a6b3a; color: white; padding: 1.5rem; border-radius: 20px;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      text-align: center; box-shadow: 0 10px 20px rgba(26, 107, 58, 0.2);
    }
    .progress-stat-card h3 { font-size: 2.2rem; margin: 0; color: #f8d36f; }
    .progress-stat-card p { margin: 0.5rem 0 0; font-size: 0.9rem; opacity: 0.9; font-weight: 600; }
    .chart-container { background: white; padding: 1.5rem; border-radius: 24px; border: 1px solid #eef2f6; }
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
        <a href="logout.php" style="color:#c0392b; font-weight:700;">Logout</a>
        <a href="logout.php">Logout</a>
      </nav>
    </div>
  </header>

  <main class="section">
    <div class="container">
      <div class="dashboard-shell">
      <div class="dashboard-hero">
        <section class="dashboard-banner">
          <p class="eyebrow">Teacher Dashboard</p>
          <h1 style="margin:0 0 0.8rem;">Teacher Portal</h1>
          <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION["admin_full_name"] ?? $_SESSION["admin_username"] ?? "Teacher", ENT_QUOTES, "UTF-8"); ?></strong>. Upload lessons and quizzes for JSS and SS students. Your content is tagged to your account, and duplicate uploads are blocked automatically.</p>
        </section>
        <section class="dashboard-stats">
          <article class="stat-card">
            <strong>Personal Login</strong>
            <span>Each teacher works with an individual account.</span>
          </article>
          <article class="stat-card">
            <strong>Duplicate Check</strong>
            <span>Existing content is detected before saving.</span>
          </article>
          <article class="stat-card">
            <strong>Lesson + Quiz</strong>
            <span>One dashboard for both teaching workflows.</span>
          </article>
          <article class="stat-card">
            <strong>Saved History</strong>
            <span>Every record shows who uploaded it and when.</span>
          </article>
        </section>
      </div>

      <!-- Tab navigation -->
      <div class="admin-tabs">
        <button class="admin-tab active" data-target="panel-lesson">📚 Upload Lesson</button>
        <button class="admin-tab"        data-target="panel-quiz">❓ Upload Quiz</button>
        <button class="admin-tab"        data-target="panel-saved">📋 Saved Content</button>
        <button class="admin-tab"        data-target="panel-progress">📊 Student Progress</button>
      </div>

      <!-- ══════════════════ LESSON PANEL ══════════════════ -->
      <div class="admin-panel active card" id="panel-lesson">
        <div class="panel-header">
          <div>
            <span class="panel-kicker">Lesson Upload</span>
            <h2>Upload a Lesson</h2>
            <p style="margin:0;color:#555;">Fill in the lesson details clearly so students can read and understand them easily.</p>
          </div>
          <aside class="helper-card">
            <p>Use a unique title per level and subject. If the same lesson already exists, the portal will stop the upload and tell you who added it first.</p>
          </aside>
        </div>

        <div class="content-grid">
          <section class="content-section">
            <h3>Class Details</h3>
            <div class="form-row">
              <div>
                <label class="field-label" for="lessonLevel">Student Level</label>
                <select id="lessonLevel" class="text-input">
                  <optgroup label="Junior Secondary School (JSS)">
                    <option value="JSS 1">JSS 1</option>
                    <option value="JSS 2">JSS 2</option>
                    <option value="JSS 3">JSS 3</option>
                  </optgroup>
                  <optgroup label="Senior Secondary School (SS)">
                    <option value="SS 1">SS 1</option>
                    <option value="SS 2">SS 2</option>
                    <option value="SS 3">SS 3</option>
                  </optgroup>
                </select>
              </div>
              <div>
                <label class="field-label" for="lessonSubject">Subject</label>
                <select id="lessonSubject" class="text-input">
              <!-- JSS Subjects -->
              <optgroup label="JSS Core Subjects">
                <option value="English Language">English Language</option>
                <option value="Mathematics">Mathematics</option>
                <option value="Basic Science">Basic Science</option>
                <option value="Social Studies">Social Studies</option>
                <option value="Basic Technology">Basic Technology</option>
                <option value="Agricultural Science">Agricultural Science</option>
                <option value="Civic Education">Civic Education</option>
                <option value="Christian Religious Studies">Christian Religious Studies</option>
                <option value="Islamic Studies">Islamic Studies</option>
                <option value="Physical and Health Education">Physical and Health Education</option>
                <option value="Creative and Cultural Arts">Creative and Cultural Arts</option>
                <option value="Home Economics">Home Economics</option>
                <option value="French">French</option>
                <option value="Business Studies">Business Studies</option>
                <option value="Computer Studies">Computer Studies</option>
                <option value="Hausa">Hausa</option>
              </optgroup>
              <!-- SS Subjects -->
              <optgroup label="SS Core & Elective Subjects">
                <option value="English Language (SS)">English Language</option>
                <option value="Mathematics (SS)">Mathematics</option>
                <option value="Biology">Biology</option>
                <option value="Chemistry">Chemistry</option>
                <option value="Physics">Physics</option>
                <option value="Economics">Economics</option>
                <option value="Government">Government</option>
                <option value="Geography">Geography</option>
                <option value="History">History</option>
                <option value="Literature in English">Literature in English</option>
                <option value="Commerce">Commerce</option>
                <option value="Accounts">Accounts</option>
                <option value="Civic Education (SS)">Civic Education</option>
                <option value="Christian Religious Knowledge">Christian Religious Knowledge</option>
                <option value="Islamic Religious Knowledge">Islamic Religious Knowledge</option>
                <option value="Agricultural Science (SS)">Agricultural Science</option>
                <option value="Further Mathematics">Further Mathematics</option>
                <option value="Technical Drawing">Technical Drawing</option>
                <option value="Computer Science">Computer Science</option>
                <option value="Food and Nutrition">Food and Nutrition</option>
                <option value="Visual Arts">Visual Arts</option>
                <option value="French (SS)">French</option>
                <option value="Hausa (SS)">Hausa</option>
              </optgroup>
                </select>
              </div>
            </div>
          </section>

          <section class="content-section">
            <h3>Lesson Content</h3>
            <label class="field-label" for="lessonTitle">Topic Title</label>
            <input id="lessonTitle" class="text-input" type="text" placeholder="e.g. Introduction to Nouns">

            <label class="field-label" for="lessonEnglish">Lesson Content (English)</label>
            <textarea id="lessonEnglish" class="text-input text-area" placeholder="Write the lesson content in English…"></textarea>

            <label class="field-label" for="lessonHausa">Hausa Translation</label>
            <textarea id="lessonHausa" class="text-input text-area" placeholder="Rubuta fassarar darasin da Hausa…"></textarea>
          </section>
        </div>

        <div class="action-row">
          <button id="addLessonButton" class="btn btn-primary" type="button">Add Lesson</button>
          <p id="adminMessage" class="status-text"></p>
        </div>
      </div><!-- /panel-lesson -->

      <!-- ══════════════════ QUIZ PANEL ══════════════════ -->
      <div class="admin-panel card" id="panel-quiz">
        <div class="panel-header">
          <div>
            <span class="panel-kicker">Quiz Builder</span>
            <h2>Upload a Quiz</h2>
            <p style="margin:0;color:#555;">Create one question with four options and mark the correct answer before saving.</p>
          </div>
          <aside class="helper-card">
            <p>Keep the wording short and clear. If another teacher has already added the same quiz question, the system will alert you immediately.</p>
          </aside>
        </div>

        <div class="content-grid">
          <section class="content-section">
            <h3>Class Details</h3>
            <div class="form-row">
              <div>
                <label class="field-label" for="quizLevel">Student Level</label>
                <select id="quizLevel" class="text-input">
                  <optgroup label="Junior Secondary School (JSS)">
                    <option value="JSS 1">JSS 1</option>
                    <option value="JSS 2">JSS 2</option>
                    <option value="JSS 3">JSS 3</option>
                  </optgroup>
                  <optgroup label="Senior Secondary School (SS)">
                    <option value="SS 1">SS 1</option>
                    <option value="SS 2">SS 2</option>
                    <option value="SS 3">SS 3</option>
                  </optgroup>
                </select>
              </div>
              <div>
                <label class="field-label" for="quizSubject">Subject</label>
                <select id="quizSubject" class="text-input">
              <optgroup label="JSS Core Subjects">
                <option value="English Language">English Language</option>
                <option value="Mathematics">Mathematics</option>
                <option value="Basic Science">Basic Science</option>
                <option value="Social Studies">Social Studies</option>
                <option value="Basic Technology">Basic Technology</option>
                <option value="Agricultural Science">Agricultural Science</option>
                <option value="Civic Education">Civic Education</option>
                <option value="Christian Religious Studies">Christian Religious Studies</option>
                <option value="Islamic Studies">Islamic Studies</option>
                <option value="Physical and Health Education">Physical and Health Education</option>
                <option value="Creative and Cultural Arts">Creative and Cultural Arts</option>
                <option value="Home Economics">Home Economics</option>
                <option value="French">French</option>
                <option value="Business Studies">Business Studies</option>
                <option value="Computer Studies">Computer Studies</option>
                <option value="Hausa">Hausa</option>
              </optgroup>
              <optgroup label="SS Core & Elective Subjects">
                <option value="English Language (SS)">English Language</option>
                <option value="Mathematics (SS)">Mathematics</option>
                <option value="Biology">Biology</option>
                <option value="Chemistry">Chemistry</option>
                <option value="Physics">Physics</option>
                <option value="Economics">Economics</option>
                <option value="Government">Government</option>
                <option value="Geography">Geography</option>
                <option value="History">History</option>
                <option value="Literature in English">Literature in English</option>
                <option value="Commerce">Commerce</option>
                <option value="Accounts">Accounts</option>
                <option value="Civic Education (SS)">Civic Education</option>
                <option value="Christian Religious Knowledge">Christian Religious Knowledge</option>
                <option value="Islamic Religious Knowledge">Islamic Religious Knowledge</option>
                <option value="Agricultural Science (SS)">Agricultural Science</option>
                <option value="Further Mathematics">Further Mathematics</option>
                <option value="Technical Drawing">Technical Drawing</option>
                <option value="Computer Science">Computer Science</option>
                <option value="Food and Nutrition">Food and Nutrition</option>
                <option value="Visual Arts">Visual Arts</option>
                <option value="French (SS)">French</option>
                <option value="Hausa (SS)">Hausa</option>
              </optgroup>
                </select>
              </div>
            </div>
          </section>

          <section class="content-section">
            <h3>Question and Answers</h3>
            <div class="quiz-builder-toolbar">
              <label class="field-label" for="quizQuestionCount" style="margin:0;">How many questions?</label>
              <input id="quizQuestionCount" class="text-input" type="number" min="1" max="100" value="1">
              <button id="buildQuizForms" class="btn btn-outline" type="button">Generate Forms</button>
              <span style="color:#486581;font-size:0.9rem;">Choose any number from 1 to 100.</span>
            </div>

            <div id="quizQuestionsContainer" class="quiz-question-list"></div>
          </section>
        </div>

        <div class="action-row">
          <button id="addQuizButton" class="btn btn-primary" type="button">Save Quiz Question</button>
          <p id="quizMessage" class="status-text"></p>
        </div>
      </div><!-- /panel-quiz -->

      <!-- ══════════════════ SAVED CONTENT PANEL ══════════════════ -->
      <div class="admin-panel card" id="panel-saved">
        <div class="saved-toolbar">
          <div>
            <span class="panel-kicker">Content Library</span>
            <h2 style="margin:0.35rem 0 0.25rem;">Saved Content</h2>
            <p class="saved-note">Review what has already been uploaded before adding new material.</p>
          </div>
          <div class="admin-tabs">
            <button class="admin-tab active" data-saved="saved-lessons">📚 Lessons</button>
            <button class="admin-tab"        data-saved="saved-quizzes">❓ Quizzes</button>
          </div>
        </div>

        <div id="saved-lessons">
          <div id="lessonList" class="saved-card-grid"></div>
        </div>
        <div id="saved-quizzes" style="display:none;">
          <div id="quizList" class="saved-card-grid"></div>
        </div>
      </div><!-- /panel-saved -->

      <!-- ══════════════════ STUDENT PROGRESS PANEL ══════════════════ -->
      <div class="admin-panel card" id="panel-progress">
        <div class="panel-header">
          <div>
            <span class="panel-kicker">Live Analytics</span>
            <h2>Student Progress — Every Learner Visible</h2>
            <p style="margin:0;color:#555;">Teachers see how each student is performing — in real time.</p>
          </div>
        </div>

        <div class="progress-grid">
          <div class="chart-container">
            <canvas id="studentBarChart"></canvas>
          </div>
          <div style="display:grid; gap:1rem;">
            <div class="progress-stat-card">
              <h3 id="avgScoreStat">0%</h3>
              <p>Avg Quiz Score This Week</p>
            </div>
            <div class="progress-stat-card">
              <h3 id="subjectCountStat">0</h3>
              <p>Subjects Available to Track</p>
            </div>
            <div class="progress-stat-card">
              <h3 id="visibilityStat">100%</h3>
              <p>Visibility — Every Student</p>
            </div>
          </div>
        </div>

        <div class="content-section" style="margin-top:1.5rem;">
          <h3 style="margin-bottom:1rem;">Teachers can identify struggling students early and provide targeted support.</h3>
          <div id="resultsTableContainer" class="saved-card-grid">
            <!-- Detailed results will load here -->
          </div>
        </div>
      </div><!-- /panel-progress -->

      </div>
    </div><!-- /container -->
  </main>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col">
          <a class="brand" href="index.html" style="margin-bottom:1.5rem; color:var(--text);">
            <img src="assets/logo.png" alt="Logo" class="brand-logo">
            <span>Learning Hub</span>
          </a>
          <p>Supporting teachers with secure content management for lessons and quizzes in Nigeria.</p>
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
            <a href="admin.php">Teacher Portal</a>
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
          <h4>Portal Support</h4>
          <p>Teachers can manage content for underserved communities across Nigeria.</p>
          <p style="margin-top:1rem;"><strong>Support Email:</strong> support@naijahub.local</p>
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

  <script src="script.js"></script>
  <script>
  // ── Tab switching (main admin tabs) ──
  document.querySelectorAll(".admin-tab[data-target]").forEach(btn => {
    btn.addEventListener("click", () => {
      document.querySelectorAll(".admin-tab[data-target]").forEach(b => b.classList.remove("active"));
      document.querySelectorAll(".admin-panel").forEach(p => p.classList.remove("active"));
      btn.classList.add("active");
      const panel = document.getElementById(btn.dataset.target);
      panel.classList.add("active");
      // Load DB content when switching to Saved Content
      if (btn.dataset.target === "panel-saved") loadAllFromDB();
      // Load progress data when switching to Progress tab
      if (btn.dataset.target === "panel-progress") loadProgressData();
    });
  });

  // ── Sub-tabs inside Saved Content ──
  document.querySelectorAll(".admin-tab[data-saved]").forEach(btn => {
    btn.addEventListener("click", () => {
      document.querySelectorAll(".admin-tab[data-saved]").forEach(b => b.classList.remove("active"));
      document.getElementById("saved-lessons").style.display = "none";
      document.getElementById("saved-quizzes").style.display = "none";
      btn.classList.add("active");
      document.getElementById(btn.dataset.saved).style.display = "";
    });
  });

  // ── Helper: badge HTML ──
  function levelBadge(level) {
    const cls = level && level.startsWith("SS") ? "badge-ss" : "badge-jss";
    return `<span class="badge ${cls}">${level}</span>`;
  }

  let lessonCache = [];
  let quizCache = [];

  // ── Load lessons from DB and render ──
  async function loadLessonsFromDB() {
    const list = document.getElementById("lessonList");
    if (!list) return;
    list.innerHTML = "<p>Loading…</p>";
    try {
      const res  = await fetch("api/lessons.php");
      const data = await res.json();
      list.innerHTML = "";
      if (!data.ok || !data.lessons.length) {
        lessonCache = [];
        list.innerHTML = '<div class="card"><h3>No lessons yet</h3><p>Add one using the Upload Lesson tab.</p></div>';
        return;
      }
      lessonCache = data.lessons;
      data.lessons.forEach(l => {
        const card = document.createElement("article");
        card.className = "card";
        card.innerHTML = `
          <p>${levelBadge(l.level)} <span class="badge badge-subject">${l.subject}</span></p>
          <h3>${l.title}</h3>
          <p><strong>English:</strong> ${l.content_en.slice(0,140)}…</p>
          <p><strong>Hausa:</strong> ${l.content_ha.slice(0,100)}…</p>
          <small style="color:#aaa;">by ${l.teacher} · ${l.created_at}</small><br>
          <button class="btn btn-secondary" style="margin-top:.5rem" onclick="editLesson(${l.id})">Edit</button>
          <button class="btn btn-outline" style="margin-top:.5rem" onclick="deleteLesson(${l.id}, this)">Delete</button>
        `;
        list.appendChild(card);
      });
    } catch(e) {
      lessonCache = [];
      list.innerHTML = '<div class="card"><h3>Could not load from DB</h3><p>Showing localStorage instead.</p></div>';
      renderAdminLessons();
    }
  }

  // ── Load quizzes from DB and render ──
  async function loadQuizzesFromDB() {
    const list = document.getElementById("quizList");
    if (!list) return;
    list.innerHTML = "<p>Loading…</p>";
    try {
      const res  = await fetch("api/quizzes.php");
      const data = await res.json();
      list.innerHTML = "";
      if (!data.ok || !data.quizzes.length) {
        quizCache = [];
        list.innerHTML = '<div class="card"><h3>No quiz questions yet</h3><p>Add one using the Upload Quiz tab.</p></div>';
        return;
      }
      quizCache = data.quizzes;
      data.quizzes.forEach(q => {
        const card = document.createElement("article");
        card.className = "card";
        card.innerHTML = `
          <p>${levelBadge(q.level)} <span class="badge badge-subject">${q.subject}</span></p>
          <h3>${q.questionEn}</h3>
          <p style="color:#777;font-size:.85rem">${q.questionHa}</p>
          <ol style="margin:.4rem 0 .4rem 1.2rem">
            ${q.optionsEn.map((o,i)=>`<li${i===q.answer?' style="font-weight:700;color:#1a6b3a;"':''}>${o}${i===q.answer?' ✓':''}</li>`).join("")}
          </ol>
          <small style="color:#aaa;">by ${q.teacher} · ${q.created_at}</small><br>
          <button class="btn btn-secondary" style="margin-top:.5rem" onclick="editQuiz(${q.id})">Edit</button>
          <button class="btn btn-outline" style="margin-top:.5rem" onclick="deleteQuiz(${q.id}, this)">Delete</button>
        `;
        list.appendChild(card);
      });
    } catch(e) {
      quizCache = [];
      list.innerHTML = '<div class="card"><h3>Could not load from DB</h3><p>Showing localStorage instead.</p></div>';
      renderAdminQuizzes();
    }
  }

  function loadAllFromDB() {
    loadLessonsFromDB();
    loadQuizzesFromDB();
  }

  // ── Delete helpers ──
  async function deleteLesson(id, btn) {
    if (!confirm("Delete this lesson?")) return;
    await fetch(`api/lessons.php?id=${id}`, { method: "DELETE" });
    btn.closest("article").remove();
  }
  async function deleteQuiz(id, btn) {
    if (!confirm("Delete this quiz question?")) return;
    await fetch(`api/quizzes.php?id=${id}`, { method: "DELETE" });
    btn.closest("article").remove();
  }

  function quizItemTemplate(index) {
    const n = index + 1;
    return `
      <div class="quiz-item" data-quiz-item="${index}">
        <p class="quiz-item-title">Question ${n}</p>
        <label class="field-label">Question (English)</label>
        <input class="text-input quiz-question-en" type="text" placeholder="e.g. What is the capital city of Nigeria?">

        <label class="field-label">Question (Hausa Translation)</label>
        <input class="text-input quiz-question-ha" type="text" placeholder="e.g. Menene babban birnin Najeriya?">

        <div class="quiz-options-area">
          <p class="field-label" style="margin-top:1rem;">Answer Options — <em style="font-weight:400;color:#555;">click the radio button to mark the correct answer</em></p>
          <div class="quiz-option-row">
            <span class="option-label">Option A</span>
            <input type="radio" name="correctOption_${index}" value="0">
            <input type="text" class="text-input quiz-opt-en" data-opt="0" placeholder="Option A (English)">
            <input type="text" class="text-input quiz-opt-ha" data-opt="0" placeholder="Option A (Hausa)">
          </div>
          <div class="quiz-option-row">
            <span class="option-label">Option B</span>
            <input type="radio" name="correctOption_${index}" value="1">
            <input type="text" class="text-input quiz-opt-en" data-opt="1" placeholder="Option B (English)">
            <input type="text" class="text-input quiz-opt-ha" data-opt="1" placeholder="Option B (Hausa)">
          </div>
          <div class="quiz-option-row">
            <span class="option-label">Option C</span>
            <input type="radio" name="correctOption_${index}" value="2">
            <input type="text" class="text-input quiz-opt-en" data-opt="2" placeholder="Option C (English)">
            <input type="text" class="text-input quiz-opt-ha" data-opt="2" placeholder="Option C (Hausa)">
          </div>
          <div class="quiz-option-row">
            <span class="option-label">Option D</span>
            <input type="radio" name="correctOption_${index}" value="3">
            <input type="text" class="text-input quiz-opt-en" data-opt="3" placeholder="Option D (English)">
            <input type="text" class="text-input quiz-opt-ha" data-opt="3" placeholder="Option D (Hausa)">
          </div>
        </div>
      </div>
    `;
  }

  function buildQuizForms() {
    const countEl = document.getElementById("quizQuestionCount");
    const container = document.getElementById("quizQuestionsContainer");
    if (!countEl || !container) return;

    let count = Number(countEl.value || 1);
    if (!Number.isInteger(count) || count < 1) count = 1;
    if (count > 100) count = 100;
    countEl.value = String(count);

    container.innerHTML = "";
    for (let i = 0; i < count; i += 1) {
      container.insertAdjacentHTML("beforeend", quizItemTemplate(i));
    }
  }

  document.getElementById("buildQuizForms")?.addEventListener("click", buildQuizForms);
  buildQuizForms();

  async function editLesson(id) {
    const lesson = lessonCache.find(item => Number(item.id) === Number(id));
    if (!lesson) return alert("Lesson not found.");

    const level = prompt("Edit level:", lesson.level || "");
    if (level === null) return;
    const subject = prompt("Edit subject:", lesson.subject || "");
    if (subject === null) return;
    const title = prompt("Edit lesson title:", lesson.title || "");
    if (title === null) return;
    const en = prompt("Edit English content:", lesson.content_en || "");
    if (en === null) return;
    const ha = prompt("Edit Hausa content:", lesson.content_ha || "");
    if (ha === null) return;

    if (!level.trim() || !subject.trim() || !title.trim() || !en.trim() || !ha.trim()) {
      alert("All lesson fields are required.");
      return;
    }

    const res = await fetch(`api/lessons.php?id=${id}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        level: level.trim(),
        subject: subject.trim(),
        title: title.trim(),
        content_en: en.trim(),
        content_ha: ha.trim()
      })
    });
    const data = await res.json();
    if (!res.ok || !data.ok) {
      const dup = data.duplicate ? ` Existing entry was added by ${data.duplicate.teacher} on ${data.duplicate.created_at}.` : "";
      alert(`${data.error || "Could not update lesson."}${dup}`);
      return;
    }
    loadLessonsFromDB();
  }

  async function editQuiz(id) {
    const quiz = quizCache.find(item => Number(item.id) === Number(id));
    if (!quiz) return alert("Quiz question not found.");

    const level = prompt("Edit level:", quiz.level || "");
    if (level === null) return;
    const subject = prompt("Edit subject:", quiz.subject || "");
    if (subject === null) return;
    const questionEn = prompt("Edit question (English):", quiz.questionEn || "");
    if (questionEn === null) return;
    const questionHa = prompt("Edit question (Hausa):", quiz.questionHa || "");
    if (questionHa === null) return;
    const opt0en = prompt("Option A (English):", quiz.optionsEn?.[0] || "");
    if (opt0en === null) return;
    const opt1en = prompt("Option B (English):", quiz.optionsEn?.[1] || "");
    if (opt1en === null) return;
    const opt2en = prompt("Option C (English):", quiz.optionsEn?.[2] || "");
    if (opt2en === null) return;
    const opt3en = prompt("Option D (English):", quiz.optionsEn?.[3] || "");
    if (opt3en === null) return;
    const opt0ha = prompt("Option A (Hausa):", quiz.optionsHa?.[0] || "");
    if (opt0ha === null) return;
    const opt1ha = prompt("Option B (Hausa):", quiz.optionsHa?.[1] || "");
    if (opt1ha === null) return;
    const opt2ha = prompt("Option C (Hausa):", quiz.optionsHa?.[2] || "");
    if (opt2ha === null) return;
    const opt3ha = prompt("Option D (Hausa):", quiz.optionsHa?.[3] || "");
    if (opt3ha === null) return;
    const answerRaw = prompt("Correct answer index (0=A, 1=B, 2=C, 3=D):", String(quiz.answer ?? 0));
    if (answerRaw === null) return;

    const answer = Number(answerRaw);
    if (
      !level.trim() || !subject.trim() || !questionEn.trim() ||
      !opt0en.trim() || !opt1en.trim() || !opt2en.trim() || !opt3en.trim() ||
      !Number.isInteger(answer) || answer < 0 || answer > 3
    ) {
      alert("Please provide all required quiz fields and a valid answer index (0-3).");
      return;
    }

    const res = await fetch(`api/quizzes.php?id=${id}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        level: level.trim(),
        subject: subject.trim(),
        questionEn: questionEn.trim(),
        questionHa: questionHa.trim(),
        optionsEn: [opt0en.trim(), opt1en.trim(), opt2en.trim(), opt3en.trim()],
        optionsHa: [opt0ha.trim(), opt1ha.trim(), opt2ha.trim(), opt3ha.trim()],
        answer
      })
    });
    const data = await res.json();
    if (!res.ok || !data.ok) {
      const dup = data.duplicate ? ` Existing entry was added by ${data.duplicate.teacher} on ${data.duplicate.created_at}.` : "";
      alert(`${data.error || "Could not update quiz question."}${dup}`);
      return;
    }
    loadQuizzesFromDB();
  }

  // ── Override lesson save to also hit DB API ──
  document.getElementById("addLessonButton")?.addEventListener("click", async (event) => {
    event.preventDefault();
    event.stopImmediatePropagation();
    const msg     = document.getElementById("adminMessage");
    const level   = document.getElementById("lessonLevel")?.value || "";
    const subject = document.getElementById("lessonSubject")?.value || "";
    const title   = document.getElementById("lessonTitle")?.value.trim() || "";
    const en      = document.getElementById("lessonEnglish")?.value.trim() || "";
    const ha      = document.getElementById("lessonHausa")?.value.trim() || "";
    if (!title || !en || !ha) {
      msg.textContent = "Please fill in the topic title, English content, and Hausa translation.";
      msg.className = "status-text error";
      return;
    }
    try {
      const res = await fetch("api/lessons.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ level, subject, title, content_en: en, content_ha: ha })
      });
      const data = await res.json();
      if (!res.ok || !data.ok) {
        const dup = data.duplicate ? ` Existing entry was added by ${data.duplicate.teacher} on ${data.duplicate.created_at}.` : "";
        msg.textContent = `${data.error || "Could not save lesson."}${dup}`;
        msg.className = "status-text error";
        return;
      }

      msg.textContent = "Lesson added successfully.";
      msg.className = "status-text success";
      document.getElementById("lessonTitle").value = "";
      document.getElementById("lessonEnglish").value = "";
      document.getElementById("lessonHausa").value = "";
      loadLessonsFromDB();
    } catch(e) {
      msg.textContent = "Could not save lesson to the database.";
      msg.className = "status-text error";
    }
  }, { capture: true });   // fires before script.js handler so DB save happens first

  // ── Override quiz save to also hit DB API ──
  document.getElementById("addQuizButton")?.addEventListener("click", async (event) => {
    event.preventDefault();
    event.stopImmediatePropagation();
    const msg = document.getElementById("quizMessage");
    const level      = document.getElementById("quizLevel")?.value || "";
    const subject    = document.getElementById("quizSubject")?.value || "";
    const items = Array.from(document.querySelectorAll("#quizQuestionsContainer .quiz-item"));
    if (!items.length) {
      msg.textContent = "Generate at least one question form.";
      msg.className = "status-text error";
      return;
    }

    const payloads = [];
    for (let i = 0; i < items.length; i += 1) {
      const item = items[i];
      const questionEn = item.querySelector(".quiz-question-en")?.value.trim() || "";
      const questionHa = item.querySelector(".quiz-question-ha")?.value.trim() || "";
      const optionsEn = Array.from(item.querySelectorAll(".quiz-opt-en")).map(el => el.value.trim());
      const optionsHa = Array.from(item.querySelectorAll(".quiz-opt-ha")).map(el => el.value.trim());
      const correctRadio = item.querySelector('input[type="radio"]:checked');
      if (!questionEn || optionsEn.length < 4 || optionsEn.some(o => !o)) {
        msg.textContent = `Question ${i + 1}: please fill the question and all 4 English options.`;
        msg.className = "status-text error";
        return;
      }
      if (!correctRadio) {
        msg.textContent = `Question ${i + 1}: please select the correct answer.`;
        msg.className = "status-text error";
        return;
      }
      payloads.push({
        level,
        subject,
        questionEn,
        questionHa,
        optionsEn,
        optionsHa,
        answer: Number(correctRadio.value)
      });
    }

    let successCount = 0;
    let failCount = 0;
    const failNotes = [];

    for (let i = 0; i < payloads.length; i += 1) {
      try {
        const res = await fetch("api/quizzes.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payloads[i])
        });
        const data = await res.json();
        if (res.ok && data.ok) {
          successCount += 1;
        } else {
          failCount += 1;
          failNotes.push(`Q${i + 1}: ${data.error || "Could not save."}`);
        }
      } catch (e) {
        failCount += 1;
        failNotes.push(`Q${i + 1}: Network/API error.`);
      }
    }

    if (failCount === 0) {
      msg.textContent = `Saved ${successCount} quiz question(s) successfully.`;
      msg.className = "status-text success";
      buildQuizForms();
      loadQuizzesFromDB();
      return;
    }

    msg.textContent = `Saved ${successCount}/${payloads.length}. Failed ${failCount}. ${failNotes.slice(0, 2).join(" ")}`;
    msg.className = "status-text error";
    loadQuizzesFromDB();
    }, { capture: true });

    let progressChart = null;
    async function loadProgressData() {
      const table = document.getElementById("resultsTableContainer");
      if (!table) return;
      table.innerHTML = "<p>Loading progress data...</p>";

      try {
        const res = await fetch("api/results.php");
        const data = await res.json();
        if (!data.ok || !data.results || data.results.length === 0) {
          table.innerHTML = "<p>No progress data found for your assigned class.</p>";
          return;
        }

        const results = data.results;
        table.innerHTML = "";

        // KPI Calculations
        let totalScores = 0;
        let totalMax = 0;
        const subjects = new Set();
        const studentMap = {}; // name -> avg score aggregate

        results.forEach(r => {
          totalScores += Number(r.score);
          totalMax += Number(r.total);
          subjects.add(r.subject);
          
          if (!studentMap[r.full_name]) {
            studentMap[r.full_name] = { sum: 0, count: 0 };
          }
          studentMap[r.full_name].sum += (Number(r.score) / Number(r.total)) * 100;
          studentMap[r.full_name].count += 1;

          const card = document.createElement("article");
          card.className = "card";
          card.style.padding = "1rem";
          card.innerHTML = `
            <h4 style="margin:0">${r.full_name}</h4>
            <p style="font-size:0.8rem; color:#666; margin:0.3rem 0;">${r.subject} • ${r.created_at}</p>
            <strong style="color:var(--accent); display:block; margin-top:0.4rem;">Score: ${r.score} / ${r.total}</strong>
          `;
          table.appendChild(card);
        });

        const avgScorePercent = totalMax > 0 ? Math.round((totalScores / totalMax) * 100) : 0;
        document.getElementById("avgScoreStat").textContent = avgScorePercent + "%";
        document.getElementById("subjectCountStat").textContent = subjects.size;

        // Chart.js implementation
        const labels = Object.keys(studentMap);
        const scores = labels.map(l => Math.round(studentMap[l].sum / studentMap[l].count));

        const ctx = document.getElementById('studentBarChart').getContext('2d');
        if (progressChart) progressChart.destroy();
        
        progressChart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Avg Performance (%)',
              data: scores,
              backgroundColor: '#1a6b3a',
              borderRadius: 8,
              barThickness: 30
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: { 
                beginAtZero: true, 
                max: 100,
                grid: { color: '#f0f0f0' },
                ticks: { font: { family: 'Sora' } }
              },
              x: {
                grid: { display: false },
                ticks: { font: { family: 'Sora', size: 11 } }
              }
            },
            plugins: {
              legend: { display: false },
              tooltip: {
                backgroundColor: '#102a43',
                titleFont: { family: 'Sora' },
                bodyFont: { family: 'Sora' }
              }
            }
          }
        });

      } catch (e) {
        table.innerHTML = "<p>Error loading analytics. " + e.message + "</p>";
      }
    }
  </script>
</body>
</html>
