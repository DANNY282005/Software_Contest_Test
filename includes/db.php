<?php
$host = 'localhost';
$dbname = 'online_exam';
$username = 'root';
$password = 'Daniel@MYSQL';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create student_answers table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_answers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        question_id INT NOT NULL,
        answer INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (question_id) REFERENCES questions(id)
    )");
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}
?> 