-- ============================================================
--  Naija Students Learning Hub – Database Setup
--  Run this in phpMyAdmin or MySQL CLI once:
--  mysql -u root -p < db_setup.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS naija_hub
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE naija_hub;

-- Teachers / Admins
CREATE TABLE IF NOT EXISTS teachers (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name   VARCHAR(120) NOT NULL DEFAULT '',
  username    VARCHAR(60)  NOT NULL UNIQUE,
  email       VARCHAR(190) NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,   -- bcrypt hash
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin — password: 123456789 (bcrypt hash)
INSERT IGNORE INTO teachers (full_name, username, email, password)
VALUES ('Bin Masud', 'BinMasud', 'abdullahibinmasud@gmail.com', '$2y$10$p0qS9QS71s39oIZ8Iizkn.tXJohFRehkwi4VwEH7D4E2i5aTy71L6');

-- Lessons
CREATE TABLE IF NOT EXISTS lessons (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  teacher_id  INT UNSIGNED NOT NULL,
  level       VARCHAR(10)  NOT NULL,   -- e.g. JSS 1, SS 2
  subject     VARCHAR(80)  NOT NULL,
  title       VARCHAR(180) NOT NULL,
  title_normalized VARCHAR(180) NOT NULL,
  content_en  TEXT         NOT NULL,
  content_ha  TEXT         NOT NULL,
  lesson_signature CHAR(64) NOT NULL,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_lessons_signature (lesson_signature),
  UNIQUE KEY uq_lessons_title (level, subject, title_normalized),
  FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Quizzes (each row = one question)
CREATE TABLE IF NOT EXISTS quizzes (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  teacher_id  INT UNSIGNED NOT NULL,
  level       VARCHAR(10)  NOT NULL,
  subject     VARCHAR(80)  NOT NULL,
  question_en VARCHAR(500) NOT NULL,
  question_normalized VARCHAR(500) NOT NULL,
  question_ha VARCHAR(500) NOT NULL DEFAULT '',
  opt0_en     VARCHAR(200) NOT NULL,
  opt1_en     VARCHAR(200) NOT NULL,
  opt2_en     VARCHAR(200) NOT NULL,
  opt3_en     VARCHAR(200) NOT NULL,
  opt0_ha     VARCHAR(200) NOT NULL DEFAULT '',
  opt1_ha     VARCHAR(200) NOT NULL DEFAULT '',
  opt2_ha     VARCHAR(200) NOT NULL DEFAULT '',
  opt3_ha     VARCHAR(200) NOT NULL DEFAULT '',
  correct_idx TINYINT      NOT NULL,   -- 0-3
  quiz_signature CHAR(64) NOT NULL,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_quizzes_signature (quiz_signature),
  UNIQUE KEY uq_quizzes_question (level, subject, question_normalized),
  FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- If your tables already exist, add the new duplicate-protection columns/indexes manually:
-- ALTER TABLE lessons ADD COLUMN title_normalized VARCHAR(180) NOT NULL AFTER title;
-- ALTER TABLE lessons ADD COLUMN lesson_signature CHAR(64) NOT NULL AFTER content_ha;
-- ALTER TABLE lessons ADD UNIQUE KEY uq_lessons_signature (lesson_signature);
-- ALTER TABLE lessons ADD UNIQUE KEY uq_lessons_title (level, subject, title_normalized);
-- ALTER TABLE quizzes ADD COLUMN question_normalized VARCHAR(500) NOT NULL AFTER question_en;
-- ALTER TABLE quizzes ADD COLUMN quiz_signature CHAR(64) NOT NULL AFTER correct_idx;
-- ALTER TABLE quizzes ADD UNIQUE KEY uq_quizzes_signature (quiz_signature);
-- ALTER TABLE quizzes ADD UNIQUE KEY uq_quizzes_question (level, subject, question_normalized);
