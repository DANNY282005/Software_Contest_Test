<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function validateRollNumber($rollNumber) {
    // Convert to uppercase first
    $rollNumber = strtoupper($rollNumber);
    
    // Pattern: 2022PCCCSE202, 2022PCCADS202, 2022PCCIT202
    // Format: YYYYPCC[PROGRAM]YYY
    // Where YYYY is year, PCC is fixed, [PROGRAM] is CSE/ADS/IT, YYY is a number
    $pattern = '/^202[0-9]PCC(CSE|ADS|IT)[0-9]{3}$/';
    
    return preg_match($pattern, $rollNumber) === 1;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.html');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.html');
        exit();
    }
}

function loginStudent($name, $rollNumber) {
    global $pdo;
    
    // First check if student exists with this roll number
    $stmt = $pdo->prepare("SELECT id, name FROM students WHERE roll_number = ?");
    $stmt->execute([$rollNumber]);
    $student = $stmt->fetch();
    
    if ($student) {
        // Student exists, log them in
        $_SESSION['user_id'] = $student['id'];
        $_SESSION['is_admin'] = false;
        return true;
    } else {
        // Student doesn't exist, create a new student record
        $stmt = $pdo->prepare("INSERT INTO students (name, roll_number) VALUES (?, ?)");
        $stmt->execute([$name, $rollNumber]);
        
        // Get the new student ID
        $newId = $pdo->lastInsertId();
        
        // Log them in
        $_SESSION['user_id'] = $newId;
        $_SESSION['is_admin'] = false;
        return true;
    }
}

function loginAdmin($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND password = ?");
    $stmt->execute([$username, md5($password)]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['is_admin'] = true;
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: index.html');
    exit();
}
?> 