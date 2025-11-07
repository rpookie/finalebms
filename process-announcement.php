<?php
session_start();
include 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $type = $conn->real_escape_string($_POST['type']);
    $content = $conn->real_escape_string($_POST['content']);
    
    $stmt = $conn->prepare("INSERT INTO announcements (title, type, content, created_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $type, $content, $_SESSION['boarder_id']);
    
    if ($stmt->execute()) {
        header("Location: announcements.php?success=1");
    } else {
        header("Location: announcements.php?error=1");
    }
    exit();
} else {
    header("Location: announcements.php");
    exit();
}
?>