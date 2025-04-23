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

// Check if student has taken the test
$stmt = $pdo->prepare("SELECT * FROM test_results WHERE student_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$testResult = $stmt->fetch();

// Get student's answers if test is completed
$answers = [];
if ($testResult) {
    $stmt = $pdo->prepare("
        SELECT q.question_text, q.options, sa.answer, q.correct_answer 
        FROM student_answers sa 
        JOIN questions q ON sa.question_id = q.id 
        WHERE sa.student_id = ?
        ORDER BY sa.id
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $answers = $stmt->fetchAll();
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
                    <h2>Thank You!</h2>
                    <p>You have successfully completed the test.</p>
                    <p class="score-note">Your results will be available soon.</p>
                    
                    <button id="viewAnswersBtn" class="view-answers-btn">View Your Answers</button>
                    
                    <div class="answers-section" style="display: none;">
                        <h3>Your Answers</h3>
                        <div class="answers-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th class="question-col">Question</th>
                                        <th class="answer-col">Your Answer</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($answers as $index => $answer): 
                                        $options = json_decode($answer['options'], true);
                                        $studentAnswer = $options[$answer['answer']] ?? 'Not answered';
                                    ?>
                                        <tr>
                                            <td class="question-cell">
                                                <div class="question-number">Question <?php echo $index + 1; ?></div>
                                                <div class="question-text"><?php echo htmlspecialchars($answer['question_text']); ?></div>
                                            </td>
                                            <td class="answer-cell"><?php echo htmlspecialchars($studentAnswer); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="start-test-section">
                    <h2>Welcome to the Online Test</h2>
                    <p>Click the button below to start your test.</p>
                    <a href="test.php" class="start-test-btn">Start Test</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.getElementById('viewAnswersBtn').addEventListener('click', function() {
            const answersSection = document.querySelector('.answers-section');
            const button = this;
            
            if (answersSection.style.display === 'none') {
                answersSection.style.display = 'block';
                button.textContent = 'Hide Answers';
                answersSection.scrollIntoView({ behavior: 'smooth' });
            } else {
                answersSection.style.display = 'none';
                button.textContent = 'View Your Answers';
            }
        });
    </script>
</body>
</html> 