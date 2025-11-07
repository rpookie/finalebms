<?php
session_start();
include 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_number = $conn->real_escape_string($_POST['room_number']);
    $bed_type = $conn->real_escape_string($_POST['bed_type']);
    $bathroom_type = $conn->real_escape_string($_POST['bathroom_type']);
    $monthly_rent = floatval($_POST['monthly_rent']);
    $cooling_type = $conn->real_escape_string($_POST['cooling_type']);
    $wifi_access = $conn->real_escape_string($_POST['wifi_access']);
    $kitchen_access = $conn->real_escape_string($_POST['kitchen_access']);
    $laundry_access = $conn->real_escape_string($_POST['laundry_access']);
    
    // Default images
    $main_img = "img/default-room.jpg";
    $images = "img/default-room-2.jpg,img/default-room-3.jpg";
    
    $stmt = $conn->prepare("INSERT INTO rooms (room_number, bed_type, bathroom_type, monthly_rent, cooling_type, wifi_access, kitchen_access, laundry_access, main_img, images, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Available')");
    $stmt->bind_param("sssdssssss", $room_number, $bed_type, $bathroom_type, $monthly_rent, $cooling_type, $wifi_access, $kitchen_access, $laundry_access, $main_img, $images);
    
    if ($stmt->execute()) {
        header("Location: ad-rooms.php?success=1");
    } else {
        header("Location: ad-rooms.php?error=1");
    }
    exit();
} else {
    header("Location: ad-rooms.php");
    exit();
}
?>