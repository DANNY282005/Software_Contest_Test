<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $rollNumber = strtoupper($_POST['roll_number'] ?? '');
    
    if (empty($rollNumber)) {
        header('Location: index.html?error=missing_roll_number');
        exit();
    }
    
    // Validate roll number format
    if (!validateRollNumber($rollNumber)) {
        header('Location: index.html?error=invalid_roll_format');
        exit();
    }
    
    // If name is empty, set a default name
    if (empty($name)) {
        $name = "Student";
    }
    
    if (loginStudent($name, $rollNumber)) {
        header('Location: student_dashboard.php');
        exit();
    } else {
        header('Location: index.html?error=login_failed');
        exit();
    }
} else {
    header('Location: index.html');
    exit();
}
?> 