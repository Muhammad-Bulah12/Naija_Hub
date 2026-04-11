USE naija_hub;

ALTER TABLE teachers
  ADD COLUMN IF NOT EXISTS full_name VARCHAR(120) NOT NULL DEFAULT '' AFTER id,
  ADD COLUMN IF NOT EXISTS email VARCHAR(190) NULL AFTER username;

UPDATE teachers
SET
  full_name = CASE
    WHEN TRIM(full_name) = '' THEN username
    ELSE full_name
  END,
  email = CASE
    WHEN email IS NULL OR TRIM(email) = '' THEN CONCAT(username, '@naijahub.local')
    ELSE email
  END;

ALTER TABLE teachers
  MODIFY COLUMN email VARCHAR(190) NOT NULL,
  ADD UNIQUE KEY uq_teachers_email (email);

ALTER TABLE lessons
  ADD COLUMN IF NOT EXISTS title_normalized VARCHAR(180) NOT NULL AFTER title,
  ADD COLUMN IF NOT EXISTS lesson_signature CHAR(64) NOT NULL AFTER content_ha;

UPDATE lessons
SET
  title_normalized = LOWER(TRIM(REGEXP_REPLACE(title, '[[:space:]]+', ' '))),
  lesson_signature = SHA2(CONCAT_WS('|',
    LOWER(TRIM(REGEXP_REPLACE(level, '[[:space:]]+', ' '))),
    LOWER(TRIM(REGEXP_REPLACE(subject, '[[:space:]]+', ' '))),
    LOWER(TRIM(REGEXP_REPLACE(title, '[[:space:]]+', ' '))),
    LOWER(TRIM(REGEXP_REPLACE(content_en, '[[:space:]]+', ' '))),
    LOWER(TRIM(REGEXP_REPLACE(content_ha, '[[:space:]]+', ' ')))
  ), 256)
WHERE
  title_normalized = ''
  OR lesson_signature = '';

ALTER TABLE quizzes
  ADD COLUMN IF NOT EXISTS question_normalized VARCHAR(500) NOT NULL AFTER question_en,
  ADD COLUMN IF NOT EXISTS quiz_signature CHAR(64) NOT NULL AFTER correct_idx;

UPDATE quizzes
SET
  question_normalized = LOWER(TRIM(REGEXP_REPLACE(question_en, '[[:space:]]+', ' '))),
  quiz_signature = SHA2(CONCAT_WS('|',
    LOWER(TRIM(REGEXP_REPLACE(level, '[[:space:]]+', ' '))),
    LOWER(TRIM(REGEXP_REPLACE(subject, '[[:space:]]+', ' '))),
    LOWER(TRIM(REGEXP_REPLACE(question_en, '[[:space:]]+', ' '))),
    LOWER(TRIM(REGEXP_REPLACE(question_ha, '[[:space:]]+', ' '))),
    LOWER(TRIM(REGEXP_REPLACE(opt0_en, '[[:space:]]+', ' '))),
    LOWER(TRIM(REGEXP_REPLACE(opt1_en, '[[:space:]]+', ' '))),
    LOWER(TRIM(REGEXP_REPLACE(opt2_en, '[[:space:]]+', ' '))),
    LOWER(TRIM(REGEXP_REPLACE(opt3_en, '[[:space:]]+', ' '))),
    LOWER(TRIM(REGEXP_REPLACE(opt0_ha, '[[:space:]]+', ' '))),
    LOWER(TRIM(REGEXP_REPLACE(opt1_ha, '[[:space:]]+', ' '))),
    LOWER(TRIM(REGEXP_REPLACE(opt2_ha, '[[:space:]]+', ' '))),
    LOWER(TRIM(REGEXP_REPLACE(opt3_ha, '[[:space:]]+', ' '))),
    correct_idx
  ), 256)
WHERE
  question_normalized = ''
  OR quiz_signature = '';

DELETE l1
FROM lessons l1
JOIN lessons l2
  ON l1.id > l2.id
 AND l1.lesson_signature = l2.lesson_signature;

DELETE q1
FROM quizzes q1
JOIN quizzes q2
  ON q1.id > q2.id
 AND q1.quiz_signature = q2.quiz_signature;

ALTER TABLE lessons
  ADD UNIQUE KEY uq_lessons_signature (lesson_signature),
  ADD UNIQUE KEY uq_lessons_title (level, subject, title_normalized);

ALTER TABLE quizzes
  ADD UNIQUE KEY uq_quizzes_signature (quiz_signature),
  ADD UNIQUE KEY uq_quizzes_question (level, subject, question_normalized);
