<?php
session_start();
include 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = intval($_POST['request_id']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE maintenance SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $request_id);
    
    if ($stmt->execute()) {
        header("Location: maintenance.php?success=1");
    } else {
        header("Location: maintenance.php?error=1");
    }
    exit();
} else {
    header("Location: maintenance.php");
    exit();
}
?>