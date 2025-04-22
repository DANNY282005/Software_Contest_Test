<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();
requireAdmin();

// Get all students with their test results
$stmt = $pdo->prepare("
    SELECT s.id, s.name, s.roll_number, tr.score, tr.total_questions 
    FROM students s 
    LEFT JOIN test_results tr ON s.id = tr.student_id 
    ORDER BY s.name
");
$stmt->execute();
$students = $stmt->fetchAll();

// Get question count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM questions");
$stmt->execute();
$questionCount = $stmt->fetch()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="css/panimalarLogo.png">
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <div class="header">
                <h1>Admin Dashboard</h1>
                <div class="admin-actions">
                    <a href="edit_questions.php" class="button manage-btn">Manage Questions</a>
                    <a href="export_scores.php" class="button export-btn">Export Scores</a>
                    <form action="logout.php" method="POST" style="display: inline;">
                        <button type="submit" class="logout-btn">Logout</button>
                    </form>
                </div>
            </div>
            
            <div class="stats">
                <div class="stat-card">
                    <h3>Total Students attended</h3>
                    <p><?php echo count($students); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Questions</h3>
                    <p><?php echo $questionCount; ?></p>
                </div>
            </div>
            
            <h2>Student Results</h2>
            <table class="student-list">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Roll Number</th>
                        <th>Score</th>
                        <th>Questions Attempted</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars(strtoupper($student['roll_number'])); ?></td>
                            <td>
                                <?php 
                                if ($student['score'] !== null) {
                                    $correctAnswers = round(($student['score'] / 100) * $student['total_questions']);
                                    echo number_format($student['score'], 2) . '% (' . $correctAnswers . '/' . $student['total_questions'] . ' questions)';
                                } else {
                                    echo 'Not Attempted';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if ($student['total_questions'] !== null) {
                                    echo $student['total_questions'];
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if ($student['score'] !== null) {
                                    echo '<span class="status completed">Completed</span>';
                                } else {
                                    echo '<span class="status pending">Pending</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 