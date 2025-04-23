<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

if (isAdmin()) {
    header('Location: admin_dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: student_dashboard.php');
    exit();
}

// Check if student has already taken the test
$stmt = $pdo->prepare("SELECT id FROM test_results WHERE student_id = ?");
$stmt->execute([$_SESSION['user_id']]);
if ($stmt->fetch()) {
    header('Location: student_dashboard.php');
    exit();
}

$student_id = $_SESSION['user_id'];
$answers = $_POST['answer'] ?? [];

// Start transaction
$pdo->beginTransaction();

try {
    // Store each answer
    foreach ($answers as $question_id => $answer) {
        $stmt = $pdo->prepare("INSERT INTO student_answers (student_id, question_id, answer) VALUES (?, ?, ?)");
        $stmt->execute([$student_id, $question_id, $answer]);
    }
    
    // Calculate score
    $score = 0;
    $total_questions = count($answers);
    
    foreach ($answers as $question_id => $answer) {
        $stmt = $pdo->prepare("SELECT correct_answer FROM questions WHERE id = ?");
        $stmt->execute([$question_id]);
        $question = $stmt->fetch();
        
        if ($question && $answer == $question['correct_answer']) {
            $score++;
        }
    }
    
    $score_percentage = ($score / $total_questions) * 100;
    
    // Store test result
    $stmt = $pdo->prepare("INSERT INTO test_results (student_id, score, total_questions) VALUES (?, ?, ?)");
    $stmt->execute([$student_id, $score_percentage, $total_questions]);
    
    $pdo->commit();
    header('Location: student_dashboard.php');
    exit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}
?> 