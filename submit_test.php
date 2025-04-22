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

// Get total number of questions in the test
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM questions");
$stmt->execute();
$totalQuestions = $stmt->fetch()['total'];

$answers = $_POST['answer'] ?? [];
$score = 0;

// Check each answer
foreach ($answers as $questionId => $selectedOption) {
    $stmt = $pdo->prepare("SELECT correct_answer FROM questions WHERE id = ?");
    $stmt->execute([$questionId]);
    $question = $stmt->fetch();
    
    if ($question && (int)$question['correct_answer'] === (int)$selectedOption) {
        $score++;
    }
}

// Calculate percentage based on total questions, not just attempted ones
$percentage = ($score / $totalQuestions) * 100;

// Save the test result
$stmt = $pdo->prepare("INSERT INTO test_results (student_id, score, total_questions) VALUES (?, ?, ?)");
$stmt->execute([$_SESSION['user_id'], $percentage, $totalQuestions]);

header('Location: student_dashboard.php');
exit();
?> 