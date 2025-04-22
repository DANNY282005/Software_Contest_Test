<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();
requireAdmin();

// Handle question deletion
if (isset($_POST['delete_question'])) {
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$_POST['question_id']]);
    header('Location: edit_questions.php?message=deleted');
    exit();
}

// Handle question addition/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_text'])) {
    $questionText = $_POST['question_text'];
    $options = [
        $_POST['option1'],
        $_POST['option2'],
        $_POST['option3'],
        $_POST['option4']
    ];
    $correctAnswer = (int)$_POST['correct_answer'];
    
    if (isset($_POST['question_id'])) {
        // Update existing question
        $stmt = $pdo->prepare("
            UPDATE questions 
            SET question_text = ?, options = ?, correct_answer = ? 
            WHERE id = ?
        ");
        $stmt->execute([
            $questionText,
            json_encode($options),
            $correctAnswer,
            $_POST['question_id']
        ]);
        header('Location: edit_questions.php?message=updated');
    } else {
        // Add new question
        $stmt = $pdo->prepare("
            INSERT INTO questions (question_text, options, correct_answer) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $questionText,
            json_encode($options),
            $correctAnswer
        ]);
        header('Location: edit_questions.php?message=added');
    }
    exit();
}

// Get all questions
$stmt = $pdo->prepare("SELECT * FROM questions ORDER BY id DESC");
$stmt->execute();
$questions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="css/panimalarLogo.png">
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <div class="header">
                <h1>Manage Questions</h1>
                <a href="admin_dashboard.php" class="button">Back to Dashboard</a>
            </div>
            
            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-success">
                    <?php
                    switch ($_GET['message']) {
                        case 'added':
                            echo 'Question added successfully!';
                            break;
                        case 'updated':
                            echo 'Question updated successfully!';
                            break;
                        case 'deleted':
                            echo 'Question deleted successfully!';
                            break;
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="add-question">
                <h2>Add New Question</h2>
                <form method="POST" class="question-form">
                    <div class="form-group">
                        <label for="question_text">Question:</label>
                        <textarea id="question_text" name="question_text" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="option1">Option 1:</label>
                        <input type="text" id="option1" name="option1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="option2">Option 2:</label>
                        <input type="text" id="option2" name="option2" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="option3">Option 3:</label>
                        <input type="text" id="option3" name="option3" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="option4">Option 4:</label>
                        <input type="text" id="option4" name="option4" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="correct_answer">Correct Answer:</label>
                        <select id="correct_answer" name="correct_answer" required>
                            <option value="0">Option 1</option>
                            <option value="1">Option 2</option>
                            <option value="2">Option 3</option>
                            <option value="3">Option 4</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="button">Add Question</button>
                </form>
            </div>
            
            <div class="questions-list">
                <h2>Existing Questions</h2>
                <?php foreach ($questions as $question): ?>
                    <div class="question-card">
                        <h3><?php echo htmlspecialchars($question['question_text']); ?></h3>
                        <div class="options">
                            <?php
                            $options = json_decode($question['options'], true);
                            foreach ($options as $key => $option):
                            ?>
                                <div class="option <?php echo $key == $question['correct_answer'] ? 'correct' : ''; ?>">
                                    <?php echo htmlspecialchars($option); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="question-actions">
                            <button onclick="editQuestion(<?php echo htmlspecialchars(json_encode($question)); ?>)" class="edit-btn">
                                Edit
                            </button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                <button type="submit" name="delete_question" class="delete-btn" onclick="return confirm('Are you sure you want to delete this question?')">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        function editQuestion(question) {
            document.getElementById('question_text').value = question.question_text;
            const options = JSON.parse(question.options);
            for (let i = 0; i < 4; i++) {
                document.getElementById(`option${i + 1}`).value = options[i];
            }
            document.getElementById('correct_answer').value = question.correct_answer;
            
            // Add question ID to form for update
            const form = document.querySelector('.question-form');
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'question_id';
            idInput.value = question.id;
            form.appendChild(idInput);
            
            // Change submit button text
            form.querySelector('button[type="submit"]').textContent = 'Update Question';
            
            // Scroll to form
            form.scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html> 