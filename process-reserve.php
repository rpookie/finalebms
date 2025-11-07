<?php
session_start();
include 'db.php';

// Load PHPMailer at the TOP of the file
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Function to send Email
function sendEmail($email, $subject, $message) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jjrlsimp@gmail.com'; // Your email
        $mail->Password = 'voqwlldmgmwwpnec'; // Your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('jjrlsimp@gmail.com', 'eBMS System');
        $mail->addAddress($email);
        $mail->addReplyTo('jjrlsimp@gmail.com', 'eBMS Support');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags($message);
        
        $mail->send();
        error_log("Email successfully sent to: $email");
        return true;
    } catch (Exception $e) {
        error_log("Email failed to $email: " . $mail->ErrorInfo);
        return false;
    }
}

// SMS function (simulated)
function sendSMS($phoneNumber, $message) {
    error_log("[SIMULATED SMS] To: $phoneNumber - Message: $message");
    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $fname = $conn->real_escape_string($_POST['fname']);
    $mname = $conn->real_escape_string($_POST['mname']);
    $lname = $conn->real_escape_string($_POST['lname']);
    $email = $conn->real_escape_string($_POST['email']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $age = intval($_POST['age']);
    $address = $conn->real_escape_string($_POST['address']);
    $guardian_fullname = $conn->real_escape_string($_POST['guardian_fullname']);
    $guardian_relationship = $conn->real_escape_string($_POST['guardian_relationship']);
    $guardian_contact = $conn->real_escape_string($_POST['guardian_contact']);
    $guardian_email = $conn->real_escape_string($_POST['guardian_email'] ?? '');
    $room = $conn->real_escape_string($_POST['room']);
    
    // Check if email already exists
    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    
    if ($check_email->get_result()->num_rows > 0) {
        die("Error: Email already exists. Please use a different email.");
    }
    
    // Generate temporary boarder ID
    $boarder_id = "B" . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Insert reservation into database
    $stmt = $conn->prepare("INSERT INTO users (boarder_id, fname, mname, lname, email, contact, age, address, guardian_fullname, guardian_relationship, guardian_contact, guardian_email, password, role, room_number, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'boarder', ?, 'pending')");
    
    // Temporary password (same as boarder ID for now)
    $temp_password = password_hash($boarder_id, PASSWORD_DEFAULT);
    
    $stmt->bind_param("ssssssissssssi", $boarder_id, $fname, $mname, $lname, $email, $contact, $age, $address, $guardian_fullname, $guardian_relationship, $guardian_contact, $guardian_email, $temp_password, $room);
    
    if ($stmt->execute()) {
        // Update room status to reserved
        $update_room = $conn->prepare("UPDATE rooms SET status = 'Reserved' WHERE room_number = ?");
        $update_room->bind_param("s", $room);
        $update_room->execute();
        
        // Prepare email content
        $email_subject = "Room Reservation Received - eBMS";
        $email_message = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4c56a1; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .footer { background: #ddd; padding: 10px; text-align: center; font-size: 12px; }
                .info-box { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #4c56a1; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>eBMS - Room Reservation Confirmation</h2>
                </div>
                <div class='content'>
                    <p>Dear <strong>$fname $mname $lname</strong>,</p>
                    
                    <p>Your room reservation has been successfully received and is currently pending approval.</p>
                    
                    <div class='info-box'>
                        <h3>Reservation Details:</h3>
                        <p><strong>Room Number:</strong> $room</p>
                        <p><strong>Temporary Boarder ID:</strong> $boarder_id</p>
                        <p><strong>Status:</strong> Pending Approval</p>
                    </div>
                    
                    <div class='info-box'>
                        <h3>Contact Information:</h3>
                        <p><strong>Email:</strong> $email</p>
                        <p><strong>Phone:</strong> $contact</p>
                        <p><strong>Address:</strong> $address</p>
                    </div>
                    
                    <div class='info-box'>
                        <h3>Guardian Information:</h3>
                        <p><strong>Name:</strong> $guardian_fullname</p>
                        <p><strong>Relationship:</strong> $guardian_relationship</p>
                        <p><strong>Contact:</strong> $guardian_contact</p>
                    </div>
                    
                    <p><strong>Next Steps:</strong><br>
                    Our administration team will review your application and contact you within 24-48 hours. 
                    Once approved, you will receive your official move-in date and further instructions.</p>
                    
                    <p>If you have any questions, please reply to this email.</p>
                </div>
                <div class='footer'>
                    <p>eBMS - Electronic Boarding Management System<br>
                    Gonzaga, Cagayan</p>
                </div>
            </div>
        </body>
        </html>";
        
        // Send email to applicant
        $email_sent = sendEmail($email, $email_subject, $email_message);
        
        // Also send email to guardian if provided
        if (!empty($guardian_email)) {
            $guardian_subject = "Guardian Notification - Room Reservation";
            $guardian_message = "
            <html>
            <body>
                <h2>Guardian Notification</h2>
                <p>Dear $guardian_fullname,</p>
                <p>This is to inform you that $fname $lname has submitted a room reservation at eBMS.</p>
                <p><strong>Room:</strong> $room</p>
                <p><strong>Status:</strong> Pending Approval</p>
                <p>You are listed as the $guardian_relationship for this application.</p>
                <p>Best regards,<br>eBMS Team</p>
            </body>
            </html>";
            
            sendEmail($guardian_email, $guardian_subject, $guardian_message);
        }
        
        // Simulated SMS
        sendSMS($contact, "Your eBMS reservation for Room $room is received. Temp ID: $boarder_id. Check email for details.");
        
        $_SESSION['reservation_data'] = [
            'room' => $room,
            'name' => $fname . ' ' . $mname . ' ' . $lname,
            'email' => $email,
            'contact' => $contact,
            'age' => $age,
            'address' => $address,
            'guardian' => $guardian_fullname . ' (' . $guardian_relationship . ')',
            'guardian_contact' => $guardian_contact,
            'guardian_email' => $guardian_email,
            'boarder_id' => $boarder_id,
            'email_sent' => $email_sent
        ];
        
        header("Location: reservation-success.php");
        exit();
    } else {
        die("Error: " . $stmt->error);
    }
} else {
    header("Location: reserve-room.php");
    exit();
}
?>