-- ============================================================
--  Naija Students Learning Hub – Database Setup
--  Run this in phpMyAdmin or MySQL CLI once:
--  mysql -u root -p < db_setup.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS naija_hub
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE naija_hub;

-- Schools
CREATE TABLE IF NOT EXISTS schools (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_name VARCHAR(180) NOT NULL,
  school_code VARCHAR(20)  NOT NULL UNIQUE,
  location    VARCHAR(180) DEFAULT '',
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Teachers / Admins
CREATE TABLE IF NOT EXISTS teachers (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id   INT UNSIGNED DEFAULT NULL,
  full_name   VARCHAR(120) NOT NULL DEFAULT '',
  username    VARCHAR(60)  NOT NULL UNIQUE,
  email       VARCHAR(190) NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,   -- bcrypt hash
  assigned_class VARCHAR(20) DEFAULT NULL, -- e.g. JSS 1 Monitor
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE SET NULL
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

-- Students
CREATE TABLE IF NOT EXISTS students (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id   INT UNSIGNED NOT NULL,
  full_name   VARCHAR(120) NOT NULL DEFAULT '',
  username    VARCHAR(60)  NOT NULL UNIQUE,
  email       VARCHAR(190) NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,
  class_level VARCHAR(20)  NOT NULL, -- e.g. JSS 1
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Quiz Results
CREATE TABLE IF NOT EXISTS quiz_results (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id  INT UNSIGNED NOT NULL,
  subject     VARCHAR(80)  NOT NULL,
  level       VARCHAR(20)  NOT NULL,
  score       INT UNSIGNED NOT NULL,
  total       INT UNSIGNED NOT NULL,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- If your tables already exist, add the new duplicate-protection columns/indexes manually:
-- ALTER TABLE teachers ADD COLUMN school_id INT UNSIGNED DEFAULT NULL AFTER id;
-- ALTER TABLE teachers ADD COLUMN assigned_class VARCHAR(20) DEFAULT NULL AFTER password;
-- ALTER TABLE teachers ADD FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE SET NULL;
-- CREATE TABLE IF NOT EXISTS schools ... (see above)
-- CREATE TABLE IF NOT EXISTS students ... (see above)
-- DEFAULT DATA FOR TESTING
-- ============================================================

-- Default School: Abdullahi Bin Masud
INSERT IGNORE INTO schools (id, school_name, school_code, location)
VALUES (1, 'Abdullahi Bin Masud', 'ABM2026', 'Yobe, Nigeria');

-- Default Student
-- Username: student1, Password: 123456789 (bcrypt hash)
INSERT IGNORE INTO students (id, school_id, full_name, username, email, password, class_level)
VALUES (1, 1, 'Sample Student', 'student1', 'student@naijahub.local', '$2y$10$p0qS9QS71s39oIZ8Iizkn.tXJohFRehkwi4VwEH7D4E2i5aTy71L6', 'JSS 1');
