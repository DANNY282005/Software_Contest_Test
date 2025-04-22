<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

if (isAdmin()) {
    header('Location: admin_dashboard.php');
    exit();
}

// Get student information
$stmt = $pdo->prepare("SELECT name, roll_number FROM students WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

// Check if student has already taken the test
$stmt = $pdo->prepare("SELECT score, total_questions FROM test_results WHERE student_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$testResult = $stmt->fetch();

// Calculate correct answers
$correctAnswers = 0;
if ($testResult) {
    $correctAnswers = round(($testResult['score'] / 100) * $testResult['total_questions']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="css/panimalarLogo.png">
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <div class="header">
                <h1>Welcome, <?php echo htmlspecialchars($student['name']); ?></h1>
                <p>Roll Number: <?php echo htmlspecialchars(strtoupper($student['roll_number'])); ?></p>
                <form action="logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
            
            <?php if ($testResult): ?>
                <div class="test-result">
                    <h2>Your Test Result</h2>
                    <p>Score: <?php echo number_format($testResult['score'], 2); ?>%</p>
                    <p>Correct answers: <?php echo round(($testResult['score'] / 100) * $testResult['total_questions']); ?> out of <?php echo $testResult['total_questions']; ?> questions</p>
                    
                </div>
            <?php else: ?>
                <div class="start-test">
                    <h2>Ready to take the test?</h2>
                    <p>Click the button below to start your examination.</p>
                    <a href="test.php" class="start-test-btn">Start Test</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 