<?php
// setup_citizenship.php - Manually insert the Citizenship topic and quiz
require_once __DIR__ . '/db.php';

echo "<h1>Setting up Citizenship Topic...</h1>";

try {
    // 1. Insert Lesson
    $level = "JSS 1";
    $subject = "Social Studies";
    $title = "Citizenship";
    $teacherId = 1; // BinMasud
    
    $contentEn = "Citizenship is the status of being a member of a particular country. A citizen enjoys rights and performs duties in that country.\n\n" .
                 "How Citizenship Can Be Acquired:\n" .
                 "- By Birth: Being born in a country.\n" .
                 "- By Descent: If your parents are citizens.\n" .
                 "- By Registration: Through marriage or application.\n" .
                 "- By Naturalization: Foreigners becoming citizens after long stay.";
    
    $contentHa = "Zama dan kasa matsayi ne na mamba a wata kasa. Dan kasa yana da hakkoki da ayyuka a wannan kasar.\n\n" .
                 "Yadda ake samun zama dan kasa:\n" .
                 "- Ta hanyar Haihuwa: Haihuwa a kasar.\n" .
                 "- Ta hanyar Gado: Idan iyayenku 'yan kasa ne.\n" .
                 "- Ta hanyar Rijista: Ta hanyar aure ko nema.\n" .
                 "- Ta hanyar Haɗaka: Baƙi zama 'yan ƙasa bayan daɗewa.";

    $titleSig = mb_strtolower(trim($title), 'UTF-8');
    $lessonSig = hash('sha256', "jss1|socialstudies|$titleSig");

    $insLesson = $pdo->prepare("INSERT IGNORE INTO lessons (teacher_id, level, subject, title, title_normalized, content_en, content_ha, lesson_signature) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insLesson->execute([$teacherId, $level, $subject, $title, $titleSig, $contentEn, $contentHa, $lessonSig]);
    
    echo "<p style='color:green;'>Lesson 'Citizenship' inserted successfully.</p>";

    // 2. Insert Quiz Questions
    $questions = [
        ["Citizenship means", ["Being rich", "Being a member of a country", "Traveling abroad", "Speaking English"], 1],
        ["A person born in Nigeria is a citizen by", ["Registration", "Naturalization", "Birth", "Marriage"], 2],
        ["One of the rights of a citizen is", ["Fighting", "Stealing", "Freedom of speech", "Disobedience"], 2],
        ["Paying tax is a", ["Right", "Duty", "Law", "Punishment"], 1],
        ["Citizenship by descent means", ["Through education", "Through parents", "Through voting", "Through travel"], 1],
        ["Which of the following is NOT a duty of a citizen?", ["Obey laws", "Vote", "Destroy property", "Pay taxes"], 2],
        ["The right to vote is called", ["Political right", "Social right", "Economic right", "Cultural right"], 0],
        ["A foreigner becomes a citizen through", ["Birth", "Naturalization", "School", "Religion"], 1],
        ["Respecting national symbols is a", ["Right", "Duty", "Law", "Benefit"], 1],
        ["Citizenship helps to promote", ["Fighting", "Disunity", "Peace and unity", "Poverty"], 2]
    ];

    $insQuiz = $pdo->prepare("INSERT IGNORE INTO quizzes (teacher_id, level, subject, question_en, question_normalized, question_ha, opt0_en, opt1_en, opt2_en, opt3_en, correct_idx, quiz_signature) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");

    foreach ($questions as $q) {
        $qSig = mb_strtolower(trim($q[0]), 'UTF-8');
        $quizSig = hash('sha256', "jss1|socialstudies|$qSig");
        
        $insQuiz->execute([
            $teacherId, $level, $subject, $q[0], $qSig, "", 
            $q[1][0], $q[1][1], $q[1][2], $q[1][3], 
            $q[2], $quizSig
        ]);
    }

    echo "<p style='color:green;'>10 Quiz questions inserted successfully.</p>";
    echo "<p><a href='subjects.html'>Go to Subjects</a></p>";

} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
