<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

if (isAdmin()) {
    header('Location: admin_dashboard.php');
    exit();
}

// Check if student has already taken the test
$stmt = $pdo->prepare("SELECT id FROM test_results WHERE student_id = ?");
$stmt->execute([$_SESSION['user_id']]);
if ($stmt->fetch()) {
    header('Location: student_dashboard.php');
    exit();
}

// Get questions and shuffle them
$stmt = $pdo->prepare("SELECT * FROM questions");
$stmt->execute();
$questions = $stmt->fetchAll();

// Shuffle the questions array
shuffle($questions);

// Limit to 10 questions
$questions = array_slice($questions, 0, 30);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Test</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="css/panimalarLogo.png">
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <div class="header">
                <h1>Online Test</h1>
                <div id="timer">Time Remaining: <span id="time">60:00</span></div>
            </div>
            
            <form id="testForm" action="submit_test.php" method="POST">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="question-card" id="question<?php echo $index; ?>" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>">
                        <div class="question-header">
                            <h3>Question <?php echo $index + 1; ?> of <?php echo count($questions); ?></h3>
                        </div>
                        <p><?php echo htmlspecialchars($question['question_text']); ?></p>
                        <div class="options">
                            <?php
                            $options = json_decode($question['options'], true);
                            // Shuffle the options for each question
                            $optionKeys = array_keys($options);
                            shuffle($optionKeys);
                            foreach ($optionKeys as $key):
                                $optionId = "q{$question['id']}_{$key}";
                            ?>
                                <div class="option">
                                    <input type="radio" 
                                           id="<?php echo $optionId; ?>" 
                                           name="answer[<?php echo $question['id']; ?>]" 
                                           value="<?php echo (int)$key; ?>"
                                           onchange="updateProgress(<?php echo $index; ?>)"
                                           required>
                                    <label for="<?php echo $optionId; ?>">
                                        <?php echo htmlspecialchars($options[$key]); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="navigation-buttons">
                    <div class="progress-indicator">
                        Question <span id="currentQuestion">1</span> of <?php echo count($questions); ?>
                    </div>
                    <button type="button" id="nextBtn" onclick="validateAndNavigate()" style="display: none;">Next</button>
                    <button type="button" id="submitBtn" onclick="validateAndSubmit()" style="display: none;">Submit Test</button>
                </div>
            </form>

            <div id="warningMessage" class="warning-message" style="display: none;">
                Please answer the current question before proceeding.
            </div>
        </div>
    </div>

    <script>
        let currentQuestion = 0;
        const totalQuestions = <?php echo count($questions); ?>;
        const answeredQuestions = new Set();

        function updateProgress(questionIndex) {
            answeredQuestions.add(questionIndex);
            document.querySelector(`#question${questionIndex}`).classList.add('answered');
            document.getElementById('warningMessage').style.display = 'none';
            
            // Show next button when question is answered
            if (currentQuestion < totalQuestions - 1) {
                document.getElementById('nextBtn').style.display = 'block';
            } else {
                document.getElementById('submitBtn').style.display = 'block';
            }
        }

        function validateAndNavigate() {
            if (!answeredQuestions.has(currentQuestion)) {
                document.getElementById('warningMessage').style.display = 'block';
                document.getElementById('warningMessage').scrollIntoView({ behavior: 'smooth' });
                return;
            }

            const currentCard = document.querySelector(`#question${currentQuestion}`);
            currentCard.style.display = 'none';
            
            currentQuestion++;
            
            const nextCard = document.querySelector(`#question${currentQuestion}`);
            nextCard.style.display = 'block';
            
            // Update navigation buttons
            document.getElementById('nextBtn').style.display = 'none';
            document.getElementById('submitBtn').style.display = currentQuestion === totalQuestions - 1 ? 'block' : 'none';
            
            // Update progress indicator
            document.getElementById('currentQuestion').textContent = currentQuestion + 1;
            
            // Hide warning message when navigating
            document.getElementById('warningMessage').style.display = 'none';
        }

        function validateAndSubmit() {
            if (!answeredQuestions.has(currentQuestion)) {
                document.getElementById('warningMessage').style.display = 'block';
                document.getElementById('warningMessage').scrollIntoView({ behavior: 'smooth' });
                return;
            }

            const unansweredQuestions = [];
            for(let i = 0; i < totalQuestions; i++) {
                if(!answeredQuestions.has(i)) {
                    unansweredQuestions.push(i + 1);
                }
            }

            if(unansweredQuestions.length > 0) {
                const warningMsg = document.getElementById('warningMessage');
                document.getElementById('warningMessage').textContent = 'Please answer all questions before submitting the test.';
                warningMsg.style.display = 'block';
                warningMsg.scrollIntoView({ behavior: 'smooth' });
            } else {
                document.getElementById('testForm').submit();
            }
        }

        // Timer functionality
        let timeLeft = 60 * 60; // 60 minutes in seconds
        const timerDisplay = document.getElementById('time');
        
        const timer = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                document.getElementById('testForm').submit();
            }
            timeLeft--;
        }, 1000);
    </script>
</body>
</html> 