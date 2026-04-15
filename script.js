const aiKnowledge = {
  noun: {
    en: "A noun is the name of a person, place, animal, or thing. It helps us identify what we are talking about.",
    ha: "Suna kalma ce da ke nuna mutum, wuri, dabba, ko abu. Tana taimaka mana gane abin da ake magana a kai."
  },
  verb: {
    en: "A verb is an action word. It tells us what someone or something is doing.",
    ha: "Aiki kalma ce da ke nuna abin da wani ko wani abu yake yi."
  },
  population: {
    en: "Population means the number of people living in a place such as a village, town, state, or country.",
    ha: "Yawan jama'a na nufin adadin mutanen da suke zaune a wani wuri kamar kauye, gari, jiha, ko kasa."
  },
  environment: {
    en: "Environment means everything around us, including the land, air, water, plants, animals, and people.",
    ha: "Muhalli yana nufin duk abin da yake kewaye da mu, ciki har da kasa, iska, ruwa, tsirrai, dabbobi, da mutane."
  },
  "social problem": {
    en: "A social problem is a bad condition that affects many people in a community, such as poverty or crime.",
    ha: "Matsalar zamantakewa wani mummunan hali ne da ke shafar mutane da yawa a cikin al'umma, kamar talauci ko laifi."
  }
};

const quizData = {
  english: [
    {
      en: { q: "What is a noun?", options: ["A naming word", "An action word", "A color word"], answer: 0 },
      ha: { q: "Menene suna?", options: ["Kalmar suna", "Kalmar aiki", "Kalmar launi"] }
    },
    {
      en: { q: "Which word is a noun?", options: ["Run", "School", "Sing"], answer: 1 },
      ha: { q: "Wace kalma ce suna?", options: ["Gudu", "Makaranta", "Waka"] }
    },
    {
      en: { q: "What is a verb?", options: ["A place", "An action word", "A person's name"], answer: 1 },
      ha: { q: "Menene aiki?", options: ["Wuri", "Kalmar aiki", "Sunan mutum"] }
    },
    {
      en: { q: "Which word is a verb?", options: ["Jump", "Book", "Market"], answer: 0 },
      ha: { q: "Wace kalma ce aiki?", options: ["Tsalle", "Littafi", "Kasuwa"] }
    },
    {
      en: { q: "Amina is an example of a:", options: ["Noun", "Verb", "Question"], answer: 0 },
      ha: { q: "Amina misali ne na:", options: ["Suna", "Aiki", "Tambaya"] }
    }
  ],
  social: [
    {
      en: { q: "What does population mean?", options: ["Number of people in a place", "Number of cars", "Number of houses"], answer: 0 },
      ha: { q: "Menene ma'anar yawan jama'a?", options: ["Adadin mutanen da ke wuri", "Adadin motoci", "Adadin gidaje"] }
    },
    {
      en: { q: "Which of these is a social problem?", options: ["Poverty", "Rain", "Football"], answer: 0 },
      ha: { q: "Wanne daga ciki matsalar zamantakewa ce?", options: ["Talauci", "Ruwan sama", "Kwallon kafa"] }
    },
    {
      en: { q: "A town with many people has a:", options: ["Small population", "Large population", "No population"], answer: 1 },
      ha: { q: "Gari mai mutane da yawa yana da:", options: ["Karamin yawan jama'a", "Babban yawan jama'a", "Babu yawan jama'a"] }
    },
    {
      en: { q: "Poor sanitation can cause:", options: ["Better health", "Social problems", "More books"], answer: 1 },
      ha: { q: "Rashin tsafta na iya haifar da:", options: ["Ingantacciyar lafiya", "Matsalolin zamantakewa", "Karin littattafai"] }
    },
    {
      en: { q: "Communities can reduce social problems by:", options: ["Ignoring them", "Working together", "Running away"], answer: 1 },
      ha: { q: "Al'umma za su iya rage matsalolin zamantakewa ta:", options: ["Watsi da su", "Hadin kai", "Gudun hijira"] }
    }
  ]
};

function initLessonTranslations() {
  document.querySelectorAll(".translate-toggle").forEach((button) => {
    button.addEventListener("click", () => {
      const lessonText = button.closest(".lesson-card").querySelector(".lesson-text");
      const isEnglish = button.dataset.lang !== "ha";
      lessonText.textContent = isEnglish ? lessonText.dataset.ha : lessonText.dataset.en;
      button.dataset.lang = isEnglish ? "ha" : "en";
      button.textContent = isEnglish ? "Show English" : "Translate to Hausa";
    });
  });
}

function slugifyFilename(value) {
  return value
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "") || "lesson";
}

function downloadLessonContent(filename, content) {
  const blob = new Blob([content], { type: "text/plain;charset=utf-8" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  link.remove();
  URL.revokeObjectURL(url);
}

function buildLessonDownloadText(subject, title, english, hausa) {
  return [
    subject || "Naija Students Learning Hub",
    title || "Lesson",
    "",
    "English",
    english || "",
    "",
    "Hausa",
    hausa || "",
    ""
  ].join("\r\n");
}

function getQuizLinkForSubject(subject) {
  const value = (subject || "").toLowerCase();
  if (value.includes("english")) return "quiz/english-quiz.html";
  if (value.includes("social")) return "quiz/social-quiz.html";
  return "subjects.html#teacherQuizSection";
}

function initLessonDownloads() {
  document.querySelectorAll(".download-lesson").forEach((button) => {
    button.addEventListener("click", () => {
      const card = button.closest(".lesson-card");
      if (!card) return;

      const title = card.querySelector("h1")?.textContent.trim() || "Lesson";
      const subject = card.querySelector(".eyebrow")?.textContent.trim() || "Naija Students Learning Hub";
      const text = card.querySelector(".lesson-text")?.textContent.trim() || "";
      const filename = `${slugifyFilename(subject)}-${slugifyFilename(title)}.txt`;
      const body = `${subject}\r\n${title}\r\n\r\n${text}\r\n`;
      downloadLessonContent(filename, body);
    });
  });

  document.querySelectorAll(".static-lesson-download").forEach((button) => {
    button.addEventListener("click", () => {
      const subject = button.dataset.subject || "Naija Students Learning Hub";
      const title = button.dataset.title || "Lesson";
      const english = button.dataset.english || "";
      const hausa = button.dataset.hausa || "";
      const filename = `${slugifyFilename(subject)}-${slugifyFilename(title)}.txt`;
      downloadLessonContent(
        filename,
        buildLessonDownloadText(subject, title, english, hausa)
      );
    });
  });
}

function findAnswerKey(input) {
  const value = input.toLowerCase().trim();
  if (value.includes("noun")) return "noun";
  if (value.includes("verb")) return "verb";
  if (value.includes("population")) return "population";
  if (value.includes("environment")) return "environment";
  if (value.includes("social problem")) return "social problem";
  return "";
}

function initAssistant() {
  const questionInput = document.getElementById("studentQuestion");
  const askButton = document.getElementById("askButton");
  const hausaButton = document.getElementById("explainHausaButton");
  const responseBox = document.getElementById("assistantResponse");
  if (!questionInput || !askButton || !hausaButton || !responseBox) return;

  let currentKey = "";

  function answerQuestion(language) {
    const key = currentKey || findAnswerKey(questionInput.value);
    currentKey = key;
    if (!key) {
      responseBox.textContent = language === "ha"
        ? "Ina iya taimakawa da noun, verb, population, environment, da social problem."
        : "I can help with noun, verb, population, environment, and social problem.";
      return;
    }

    responseBox.textContent = aiKnowledge[key][language];
  }

  askButton.addEventListener("click", () => answerQuestion("en"));
  hausaButton.addEventListener("click", () => answerQuestion("ha"));
  document.querySelectorAll(".ask-chip").forEach((button) => {
    button.addEventListener("click", () => {
      questionInput.value = `What is ${button.dataset.topic}?`;
      currentKey = findAnswerKey(button.dataset.topic);
      answerQuestion("en");
    });
  });

  const params = new URLSearchParams(window.location.search);
  const topic = params.get("topic");
  if (topic) {
    questionInput.value = `What is ${topic}?`;
    currentKey = findAnswerKey(topic);
    answerQuestion("en");
  }
}

function buildQuizQuestion(question, index, language, formId) {
  const wrapper = document.createElement("fieldset");
  wrapper.className = "quiz-question";
  wrapper.innerHTML = `<h3>${index + 1}. ${question[language].q}</h3>`;

  question[language].options.forEach((option, optionIndex) => {
    const label = document.createElement("label");
    label.className = "quiz-option";
    label.innerHTML = `
      <input type="radio" name="${formId}-question-${index}" value="${optionIndex}">
      <span>${option}</span>
    `;
    wrapper.appendChild(label);
  });

  return wrapper;
}

function buildQuizDownloadText(subject, questions) {
  const lines = [`${subject} Quiz`, ""];
  questions.forEach((question, index) => {
    lines.push(`${index + 1}. ${question.en.q}`);
    question.en.options.forEach((option, optionIndex) => {
      const label = String.fromCharCode(65 + optionIndex);
      lines.push(`   ${label}. ${option}`);
    });
    lines.push("");
    lines.push(`Hausa: ${question.ha.q}`);
    question.ha.options.forEach((option, optionIndex) => {
      const label = String.fromCharCode(65 + optionIndex);
      lines.push(`   ${label}. ${option}`);
    });
    lines.push("");
  });
  return lines.join("\r\n");
}

function triggerQuizDownload(subjectKey) {
  if (!quizData[subjectKey]) return;
  const subjectTitle = subjectKey === "english" ? "English Studies" : "Social Studies";
  const filename = `${slugifyFilename(subjectTitle)}-quiz.txt`;
  downloadLessonContent(filename, buildQuizDownloadText(subjectTitle, quizData[subjectKey]));
}

function initQuizzes() {
  document.querySelectorAll("[data-quiz-subject]").forEach((quizCard) => {
    const subject = quizCard.dataset.quizSubject;
    const form = quizCard.querySelector(".quiz-form");
    const submitButton = quizCard.querySelector(".quiz-submit");
    const toggleButton = quizCard.querySelector(".quiz-language-toggle");
    const downloadButton = quizCard.querySelector(".quiz-download");
    const resultBox = quizCard.querySelector(".response-box");
    const progressBox = quizCard.querySelector(".quiz-progress");
    if (!form || !submitButton || !toggleButton || !resultBox) return;

    let language = "en";

    function renderQuiz() {
      form.innerHTML = "";
      quizData[subject].forEach((question, index) => {
        form.appendChild(buildQuizQuestion(question, index, language, form.id || subject));
      });
      if (progressBox) {
        progressBox.textContent = language === "ha"
          ? `${quizData[subject].length} tambayoyi suna nan`
          : `${quizData[subject].length} questions available`;
      }
    }

    submitButton.addEventListener("click", () => {
      let score = 0;
      quizData[subject].forEach((question, index) => {
        const selected = form.querySelector(`input[name="${form.id || subject}-question-${index}"]:checked`);
        if (selected && Number(selected.value) === question.en.answer) {
          score += 1;
        }
      });

      resultBox.textContent = language === "ha"
        ? `Ka samu ${score} cikin ${quizData[subject].length}. Ci gaba da kokari.`
        : `You scored ${score} out of ${quizData[subject].length}. Keep practicing.`;
    });

    toggleButton.addEventListener("click", () => {
      language = language === "en" ? "ha" : "en";
      renderQuiz();
      resultBox.textContent = language === "ha"
        ? "Sakamakonka zai bayyana a nan."
        : "Your result will appear here.";
    });

    downloadButton?.addEventListener("click", () => {
      triggerQuizDownload(subject);
    });

    renderQuiz();
  });

  document.querySelectorAll(".quiz-download[data-quiz-subject]").forEach((button) => {
    if (button.closest("[data-quiz-subject]")) return;
    button.addEventListener("click", () => {
      triggerQuizDownload(button.dataset.quizSubject);
    });
  });
}

let cachedStudentLessons = null;
let cachedStudentQuizzes = null;

function getStoredLessons() {
  try {
    return JSON.parse(localStorage.getItem("naijaHubLessons")) || [];
  } catch (error) {
    return [];
  }
}

function saveStoredLessons(lessons) {
  localStorage.setItem("naijaHubLessons", JSON.stringify(lessons));
}

function getStoredQuizzes() {
  try {
    return JSON.parse(localStorage.getItem("naijaHubQuizzes")) || [];
  } catch (error) {
    return [];
  }
}

function saveStoredQuizzes(quizzes) {
  localStorage.setItem("naijaHubQuizzes", JSON.stringify(quizzes));
}

function normalizeLessonShape(lesson) {
  return {
    id: String(lesson.id ?? ""),
    level: lesson.level || "",
    subject: lesson.subject || "",
    title: lesson.title || "",
    english: lesson.english ?? lesson.content_en ?? "",
    hausa: lesson.hausa ?? lesson.content_ha ?? ""
  };
}

async function loadLessonsForStudents() {
  try {
    const res = await fetch("api/lessons.php", { cache: "no-store" });
    const data = await res.json();
    if (res.ok && data.ok && Array.isArray(data.lessons)) {
      cachedStudentLessons = data.lessons.map(normalizeLessonShape);
      return cachedStudentLessons;
    }
  } catch (error) {
    // Fallback to old localStorage data for offline/demo use.
  }
  cachedStudentLessons = getStoredLessons().map(normalizeLessonShape);
  return cachedStudentLessons;
}

async function loadQuizzesForStudents() {
  try {
    const res = await fetch("api/quizzes.php", { cache: "no-store" });
    const data = await res.json();
    if (res.ok && data.ok && Array.isArray(data.quizzes)) {
      cachedStudentQuizzes = data.quizzes;
      return cachedStudentQuizzes;
    }
  } catch (error) {
    // Fallback to old localStorage data for offline/demo use.
  }
  cachedStudentQuizzes = getStoredQuizzes();
  return cachedStudentQuizzes;
}

function getCurrentStudentLessons() {
  return Array.isArray(cachedStudentLessons) ? cachedStudentLessons : getStoredLessons().map(normalizeLessonShape);
}

function getLessonById(id) {
  const lookup = String(id);
  return getCurrentStudentLessons().find((lesson) => String(lesson.id) === lookup)
    || getStoredLessons().map(normalizeLessonShape).find((lesson) => String(lesson.id) === lookup);
}

async function renderStudentLessonFeed() {
  const feed = document.getElementById("studentLessonFeed");
  if (!feed) return;

  const lessons = await loadLessonsForStudents();
  feed.innerHTML = "";

  if (!lessons.length) {
    feed.innerHTML = '<article class="card"><h3>No extra lessons yet</h3><p>When teachers upload more lessons, students will see them here.</p></article>';
    return;
  }

  lessons.forEach((lesson) => {
    const levelClass = lesson.level && lesson.level.startsWith("SS") ? "badge-ss" : "badge-jss";
    const card = document.createElement("article");
    card.className = "card student-added-lesson";
    card.innerHTML = `
      <p>
        <span class="badge ${levelClass}">${lesson.level || ""}</span>
        <span class="badge badge-subject" style="font-size:0.7rem;background:#f0f4ff;color:#333;">${lesson.subject}</span>
      </p>
      <h3>${lesson.title}</h3>
      <p>${lesson.english.slice(0, 120)}${lesson.english.length > 120 ? "..." : ""}</p>
      <div class="button-row">
        <a class="btn btn-outline" href="lesson.html?id=${lesson.id}">Open Lesson</a>
        <a class="btn btn-outline" href="${getQuizLinkForSubject(lesson.subject)}">Take Quiz</a>
        <button class="btn btn-secondary student-download-lesson" type="button" data-id="${lesson.id}">Download</button>
      </div>
    `;
    feed.appendChild(card);
  });

  feed.querySelectorAll(".student-download-lesson").forEach((button) => {
    button.addEventListener("click", () => {
      const lesson = getLessonById(button.dataset.id);
      if (!lesson) return;
      const filename = `${slugifyFilename(lesson.subject)}-${slugifyFilename(lesson.title)}.txt`;
      downloadLessonContent(
        filename,
        buildLessonDownloadText(lesson.subject, lesson.title, lesson.english, lesson.hausa)
      );
    });
  });
}

async function renderStudentQuizFeed() {
  const feed = document.getElementById("studentQuizFeed");
  if (!feed) return;

  const quizzes = await loadQuizzesForStudents();
  feed.innerHTML = "";

  if (!quizzes.length) {
    feed.innerHTML = '<article class="card"><h3>No extra quizzes yet</h3><p>When teachers upload quiz questions, students will see them here.</p></article>';
    return;
  }

  const grouped = new Map();
  quizzes.forEach((quiz) => {
    const level = (quiz.level || "").trim();
    const subject = (quiz.subject || "").trim();
    const key = `${level}||${subject}`;
    if (!grouped.has(key)) {
      grouped.set(key, { level, subject, questions: [] });
    }
    grouped.get(key).questions.push(quiz);
  });

  Array.from(grouped.values()).slice(0, 12).forEach((pack) => {
    const levelClass = pack.level && pack.level.startsWith("SS") ? "badge-ss" : "badge-jss";
    const questionCount = pack.questions.length;
    const first = pack.questions[0];
    const href = `subject-quiz.html?level=${encodeURIComponent(pack.level)}&subject=${encodeURIComponent(pack.subject)}`;
    const card = document.createElement("article");
    card.className = "card";
    card.innerHTML = `
      <p>
        <span class="badge ${levelClass}">${pack.level || ""}</span>
        <span class="badge badge-subject">${pack.subject || ""}</span>
      </p>
      <h3>${questionCount} Objective Question${questionCount > 1 ? "s" : ""}</h3>
      <p style="color:#486581;font-size:0.9rem;">Starts with: ${first?.questionEn || "Quiz Question"}</p>
      <div class="button-row">
        <a class="btn btn-secondary" href="${href}">Start Full Subject Quiz</a>
      </div>
    `;
    feed.appendChild(card);
  });
}

function initLessonSearch() {
  const input = document.getElementById("lessonSearch");
  if (!input) return;

  const subjectCards = Array.from(document.querySelectorAll(".subject-card"));
  const feed = document.getElementById("studentLessonFeed");

  function filterTeacherLessons(query) {
    const lessons = getCurrentStudentLessons().filter((lesson) => {
      const value = `${lesson.subject} ${lesson.title} ${lesson.english} ${lesson.hausa}`.toLowerCase();
      return value.includes(query);
    });

    if (!feed) return;
    feed.innerHTML = "";

    if (!lessons.length) {
      feed.innerHTML = '<article class="card"><h3>No matching teacher lesson</h3><p>Try another keyword or check the built-in subject lessons above.</p></article>';
      return;
    }

    lessons.forEach((lesson) => {
      const card = document.createElement("article");
      card.className = "card student-added-lesson";
      card.innerHTML = `
        <p class="eyebrow">${lesson.subject}</p>
        <h3>${lesson.title}</h3>
        <p>${lesson.english.slice(0, 120)}${lesson.english.length > 120 ? "..." : ""}</p>
        <div class="button-row">
          <a class="btn btn-outline" href="lesson.html?id=${lesson.id}">Open Lesson</a>
          <a class="btn btn-outline" href="${getQuizLinkForSubject(lesson.subject)}">Take Quiz</a>
          <button class="btn btn-secondary student-download-lesson" type="button" data-id="${lesson.id}">Download</button>
        </div>
      `;
      feed.appendChild(card);
    });

    feed.querySelectorAll(".student-download-lesson").forEach((button) => {
      button.addEventListener("click", () => {
        const lesson = getLessonById(button.dataset.id);
        if (!lesson) return;
        const filename = `${slugifyFilename(lesson.subject)}-${slugifyFilename(lesson.title)}.txt`;
        downloadLessonContent(
          filename,
          buildLessonDownloadText(lesson.subject, lesson.title, lesson.english, lesson.hausa)
        );
      });
    });
  }

  input.addEventListener("input", () => {
    const query = input.value.trim().toLowerCase();

    subjectCards.forEach((card) => {
      const text = card.textContent.toLowerCase();
      card.style.display = !query || text.includes(query) ? "" : "none";
    });

    if (!query) {
      renderStudentLessonFeed();
      return;
    }

    filterTeacherLessons(query);
  });
}

async function initDynamicLessonPage() {
  const title = document.getElementById("dynamicLessonTitle");
  const subject = document.getElementById("dynamicLessonSubject");
  const text = document.getElementById("dynamicLessonText");
  const toggle = document.getElementById("dynamicLessonToggle");
  const askLink = document.getElementById("dynamicLessonAskLink");
  if (!title || !subject || !text || !toggle || !askLink) return;

  const lessonId = new URLSearchParams(window.location.search).get("id");
  await loadLessonsForStudents();
  const lesson = lessonId ? getLessonById(lessonId) : null;
  if (!lesson) return;

  let language = "en";
  title.textContent = lesson.title;
  subject.textContent = lesson.subject;
  text.textContent = lesson.english;
  askLink.href = `ask.html?topic=${encodeURIComponent(lesson.title.toLowerCase())}`;

  toggle.addEventListener("click", () => {
    language = language === "en" ? "ha" : "en";
    text.textContent = language === "en" ? lesson.english : lesson.hausa;
    toggle.textContent = language === "en" ? "Translate to Hausa" : "Show English";
  });
}

async function initSingleQuizPage() {
  const subject = document.getElementById("singleQuizSubject");
  const title = document.getElementById("singleQuizTitle");
  const form = document.getElementById("singleQuizForm");
  const submit = document.getElementById("singleQuizSubmit");
  const toggle = document.getElementById("singleQuizToggle");
  const result = document.getElementById("singleQuizResult");
  if (!subject || !title || !form || !submit || !toggle || !result) return;

  const quizId = new URLSearchParams(window.location.search).get("id");
  const quizzes = await loadQuizzesForStudents();
  const quiz = quizzes.find((item) => String(item.id) === String(quizId));
  if (!quiz) {
    title.textContent = "Quiz question not found";
    result.textContent = "Go back to Subjects and open another quiz question.";
    return;
  }

  let language = "en";
  subject.textContent = `${quiz.level || ""} • ${quiz.subject || "Quiz"}`;

  function currentQuestionText() {
    return language === "ha" && quiz.questionHa ? quiz.questionHa : (quiz.questionEn || "Quiz Question");
  }

  function currentOptions() {
    if (language === "ha" && Array.isArray(quiz.optionsHa) && quiz.optionsHa.some((opt) => (opt || "").trim())) {
      return quiz.optionsHa;
    }
    return Array.isArray(quiz.optionsEn) ? quiz.optionsEn : [];
  }

  function renderQuestion() {
    title.textContent = currentQuestionText();
    form.innerHTML = "";
    currentOptions().forEach((option, index) => {
      const label = document.createElement("label");
      label.className = "quiz-option";
      label.innerHTML = `
        <input type="radio" name="singleQuizOption" value="${index}">
        <span>${option || `Option ${String.fromCharCode(65 + index)}`}</span>
      `;
      form.appendChild(label);
    });
    result.textContent = language === "ha"
      ? "Danna Submit bayan ka zabi amsa."
      : "Click Submit after choosing an answer.";
  }

  toggle.addEventListener("click", () => {
    language = language === "en" ? "ha" : "en";
    renderQuestion();
  });

  submit.addEventListener("click", () => {
    const selected = form.querySelector('input[name="singleQuizOption"]:checked');
    if (!selected) {
      result.textContent = language === "ha"
        ? "Da fatan za a zabi amsa daya."
        : "Please select one answer.";
      return;
    }
    const correct = Number(selected.value) === Number(quiz.answer);
    result.textContent = correct
      ? (language === "ha" ? "Daidai! Kyakkyawan aiki." : "Correct! Great job.")
      : (language === "ha" ? "Ba daidai ba. Ka sake gwadawa." : "Not correct. Try again.");
  });

  renderQuestion();
}

async function initSubjectQuizPage() {
  const meta = document.getElementById("subjectQuizMeta");
  const title = document.getElementById("subjectQuizTitle");
  const form = document.getElementById("subjectQuizForm");
  const submit = document.getElementById("subjectQuizSubmit");
  const toggle = document.getElementById("subjectQuizToggle");
  const result = document.getElementById("subjectQuizResult");
  const progress = document.getElementById("subjectQuizProgress");
  if (!meta || !title || !form || !submit || !toggle || !result || !progress) return;

  const params = new URLSearchParams(window.location.search);
  const levelParam = (params.get("level") || "").trim();
  const subjectParam = (params.get("subject") || "").trim();
  const normalize = (value) => (value || "").trim().toLowerCase();

  const quizzes = await loadQuizzesForStudents();
  const selected = quizzes.filter((item) =>
    normalize(item.level) === normalize(levelParam) &&
    normalize(item.subject) === normalize(subjectParam)
  );

  if (!selected.length) {
    title.textContent = "No quiz questions found";
    meta.textContent = "Subject Quiz";
    progress.textContent = "0 questions available";
    result.textContent = "Go back and choose another subject quiz.";
    return;
  }

  let language = "en";
  title.textContent = `${subjectParam} Quiz`;
  meta.textContent = `${levelParam} • ${subjectParam}`;

  function optionsFor(quiz) {
    if (language === "ha" && Array.isArray(quiz.optionsHa) && quiz.optionsHa.some((opt) => (opt || "").trim())) {
      return quiz.optionsHa;
    }
    return Array.isArray(quiz.optionsEn) ? quiz.optionsEn : [];
  }

  function questionFor(quiz) {
    if (language === "ha" && (quiz.questionHa || "").trim()) return quiz.questionHa;
    return quiz.questionEn || "Quiz Question";
  }

  function render() {
    form.innerHTML = "";
    progress.textContent = language === "ha"
      ? `${selected.length} tambayoyi suna nan`
      : `${selected.length} questions available`;

    selected.forEach((quiz, index) => {
      const block = document.createElement("fieldset");
      block.className = "quiz-question";
      block.innerHTML = `<h3>${index + 1}. ${questionFor(quiz)}</h3>`;

      optionsFor(quiz).forEach((option, optIndex) => {
        const label = document.createElement("label");
        label.className = "quiz-option";
        label.innerHTML = `
          <input type="radio" name="subjectQuizQ${index}" value="${optIndex}">
          <span>${option || `Option ${String.fromCharCode(65 + optIndex)}`}</span>
        `;
        block.appendChild(label);
      });
      form.appendChild(block);
    });

    result.textContent = language === "ha"
      ? "Amsa duk tambayoyin sannan ka danna Submit."
      : "Answer all questions, then click Submit.";
  }

  toggle.addEventListener("click", () => {
    language = language === "en" ? "ha" : "en";
    render();
  });

  submit.addEventListener("click", () => {
    let score = 0;
    selected.forEach((quiz, index) => {
      const selectedOption = form.querySelector(`input[name="subjectQuizQ${index}"]:checked`);
      if (selectedOption && Number(selectedOption.value) === Number(quiz.answer)) {
        score += 1;
      }
    });
    result.textContent = language === "ha"
      ? `Ka samu ${score} cikin ${selected.length}.`
      : `You scored ${score} out of ${selected.length}.`;
  });

  render();
}

function renderAdminLessons() {
  const lessonList = document.getElementById("lessonList");
  if (!lessonList) return;

  const lessons = getStoredLessons();
  lessonList.innerHTML = "";

  if (!lessons.length) {
    lessonList.innerHTML = '<div class="card"><h3>No uploaded lessons yet</h3><p>Teacher-added lessons will appear here.</p></div>';
    return;
  }

  lessons.forEach((lesson) => {
    const levelClass = lesson.level && lesson.level.startsWith("SS") ? "badge-ss" : "badge-jss";
    const card = document.createElement("article");
    card.className = "card";
    card.innerHTML = `
      <p>
        <span class="badge ${levelClass}">${lesson.level || ""}</span>
        <span class="badge badge-subject">${lesson.subject}</span>
      </p>
      <h3>${lesson.title}</h3>
      <p><strong>English:</strong> ${lesson.english}</p>
      <p><strong>Hausa:</strong> ${lesson.hausa}</p>
      <button class="btn btn-outline delete-lesson" type="button" data-id="${lesson.id}">Delete</button>
    `;
    lessonList.appendChild(card);
  });

  lessonList.querySelectorAll(".delete-lesson").forEach((button) => {
    button.addEventListener("click", () => {
      const lessonsAfterDelete = getStoredLessons().filter((lesson) => lesson.id !== button.dataset.id);
      saveStoredLessons(lessonsAfterDelete);
      renderAdminLessons();
      renderStudentLessonFeed();
    });
  });
}

function renderAdminQuizzes() {
  const quizList = document.getElementById("quizList");
  if (!quizList) return;

  const quizzes = getStoredQuizzes();
  quizList.innerHTML = "";

  if (!quizzes.length) {
    quizList.innerHTML = '<div class="card"><h3>No quiz questions yet</h3><p>Use the Upload Quiz tab to add questions.</p></div>';
    return;
  }

  quizzes.forEach((quiz) => {
    const levelClass = quiz.level && quiz.level.startsWith("SS") ? "badge-ss" : "badge-jss";
    const card = document.createElement("article");
    card.className = "card";
    card.innerHTML = `
      <p>
        <span class="badge ${levelClass}">${quiz.level}</span>
        <span class="badge badge-subject">${quiz.subject}</span>
      </p>
      <h3 style="margin-top:0.5rem;">${quiz.questionEn}</h3>
      <p style="color:#777;font-size:0.85rem;">${quiz.questionHa}</p>
      <ol style="margin:0.5rem 0 0.5rem 1.2rem;">
        ${quiz.optionsEn.map((opt, i) => `<li${i === quiz.answer ? ' style="font-weight:700;color:#1a6b3a;"' : ''}>${opt}${i === quiz.answer ? ' ✓' : ''}</li>`).join("")}
      </ol>
      <button class="btn btn-outline delete-quiz" type="button" data-id="${quiz.id}">Delete</button>
    `;
    quizList.appendChild(card);
  });

  quizList.querySelectorAll(".delete-quiz").forEach((button) => {
    button.addEventListener("click", () => {
      const remaining = getStoredQuizzes().filter((q) => q.id !== button.dataset.id);
      saveStoredQuizzes(remaining);
      renderAdminQuizzes();
    });
  });
}

function initAdmin() {
  const addLessonButton = document.getElementById("addLessonButton");
  const adminMessage   = document.getElementById("adminMessage");
  const addQuizButton  = document.getElementById("addQuizButton");
  const quizMessage    = document.getElementById("quizMessage");

  // If none of these elements exist we are not on admin page
  if (!addLessonButton && !addQuizButton) return;

  // ── Lesson upload ──
  if (addLessonButton && adminMessage) {
    addLessonButton.addEventListener("click", () => {
      const level   = (document.getElementById("lessonLevel")   || {}).value || "";
      const subject = (document.getElementById("lessonSubject") || {}).value || "";
      const title   = (document.getElementById("lessonTitle")   || { value: "" }).value.trim();
      const english = (document.getElementById("lessonEnglish")|| { value: "" }).value.trim();
      const hausa   = (document.getElementById("lessonHausa")  || { value: "" }).value.trim();

      if (!title || !english || !hausa) {
        adminMessage.textContent = "Please fill in the topic title, English content, and Hausa translation.";
        adminMessage.className = "status-text error";
        return;
      }

      const lessons = getStoredLessons();
      lessons.unshift({ id: String(Date.now()), level, subject, title, english, hausa });
      saveStoredLessons(lessons);

      adminMessage.textContent = "✅ Lesson added successfully!";
      adminMessage.className = "status-text success";
      document.getElementById("lessonTitle").value   = "";
      document.getElementById("lessonEnglish").value = "";
      document.getElementById("lessonHausa").value   = "";
      renderAdminLessons();
      renderStudentLessonFeed();
    });
  }

  // ── Quiz upload ──
  if (addQuizButton && quizMessage) {
    addQuizButton.addEventListener("click", () => {
      const level       = (document.getElementById("quizLevel")         || {}).value || "";
      const subject     = (document.getElementById("quizSubject")       || {}).value || "";
      const questionEn  = (document.getElementById("quizQuestion")      || { value: "" }).value.trim();
      const questionHa  = (document.getElementById("quizQuestionHausa") || { value: "" }).value.trim();
      const opt0en = (document.getElementById("quizOpt0en") || { value: "" }).value.trim();
      const opt1en = (document.getElementById("quizOpt1en") || { value: "" }).value.trim();
      const opt2en = (document.getElementById("quizOpt2en") || { value: "" }).value.trim();
      const opt3en = (document.getElementById("quizOpt3en") || { value: "" }).value.trim();
      const opt0ha = (document.getElementById("quizOpt0ha") || { value: "" }).value.trim();
      const opt1ha = (document.getElementById("quizOpt1ha") || { value: "" }).value.trim();
      const opt2ha = (document.getElementById("quizOpt2ha") || { value: "" }).value.trim();
      const opt3ha = (document.getElementById("quizOpt3ha") || { value: "" }).value.trim();

      const correctRadio = document.querySelector('input[name="correctOption"]:checked');

      if (!questionEn || !opt0en || !opt1en || !opt2en || !opt3en) {
        quizMessage.textContent = "Please fill the question and all 4 English options.";
        quizMessage.className = "status-text error";
        return;
      }
      if (!correctRadio) {
        quizMessage.textContent = "Please select the correct answer using the radio button.";
        quizMessage.className = "status-text error";
        return;
      }

      const quizzes = getStoredQuizzes();
      quizzes.unshift({
        id: String(Date.now()),
        level,
        subject,
        questionEn,
        questionHa,
        optionsEn: [opt0en, opt1en, opt2en, opt3en],
        optionsHa: [opt0ha, opt1ha, opt2ha, opt3ha],
        answer: Number(correctRadio.value)
      });
      saveStoredQuizzes(quizzes);

      quizMessage.textContent = "✅ Quiz question saved!";
      quizMessage.className = "status-text success";
      ["quizQuestion","quizQuestionHausa","quizOpt0en","quizOpt0ha","quizOpt1en","quizOpt1ha",
       "quizOpt2en","quizOpt2ha","quizOpt3en","quizOpt3ha"].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = "";
      });
      if (correctRadio) correctRadio.checked = false;
      renderAdminQuizzes();
    });
  }

  renderAdminLessons();
  renderAdminQuizzes();
}

document.addEventListener("DOMContentLoaded", () => {
  initLessonTranslations();
  initLessonDownloads();
  initAssistant();
  initQuizzes();
  initAdmin();
  renderStudentLessonFeed();
  renderStudentQuizFeed();
  initLessonSearch();
  initDynamicLessonPage();
  initSingleQuizPage();
  initSubjectQuizPage();
});
