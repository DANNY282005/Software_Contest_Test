<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();
requireAdmin();

// Get all students with their test results
$stmt = $pdo->prepare("
    SELECT s.name, s.roll_number, tr.score, tr.total_questions, tr.created_at 
    FROM students s 
    LEFT JOIN test_results tr ON s.id = tr.student_id 
    ORDER BY s.name
");
$stmt->execute();
$students = $stmt->fetchAll();

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="student_scores.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Create Excel content
?>
<table border="1">
    <thead>
        <tr>
            <th>Name</th>
            <th>Roll Number</th>
            <th>Score (%)</th>
            <th>Questions Attempted</th>
            <th>Test Date</th>
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
                        echo number_format($student['score'], 2) . '% (' . $correctAnswers . '/' . $student['total_questions'] . ' correct)';
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if ($student['total_questions'] !== null) {
                        echo $student['total_questions'];
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if ($student['created_at'] !== null) {
                        echo date('Y-m-d H:i:s', strtotime($student['created_at']));
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if ($student['score'] !== null) {
                        echo 'Completed';
                    } else {
                        echo 'Not Attempted';
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table> 