<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'ebms';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create tables if they don't exist
function createTables($conn) {
    // Users table
    $users_table = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        boarder_id VARCHAR(20) UNIQUE,
        fname VARCHAR(50) NOT NULL,
        mname VARCHAR(50),
        lname VARCHAR(50) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        contact VARCHAR(20) NOT NULL,
        age INT NOT NULL,
        address TEXT NOT NULL,
        guardian_fullname VARCHAR(100) NOT NULL,
        guardian_relationship VARCHAR(50) NOT NULL,
        guardian_contact VARCHAR(20) NOT NULL,
        guardian_email VARCHAR(100),
        password VARCHAR(255) NOT NULL,
        role ENUM('boarder', 'admin') DEFAULT 'boarder',
        profile_picture VARCHAR(255),
        room_number VARCHAR(10),
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // Rooms table
    $rooms_table = "CREATE TABLE IF NOT EXISTS rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        room_number VARCHAR(10) UNIQUE NOT NULL,
        bed_type VARCHAR(50) NOT NULL,
        bathroom_type VARCHAR(50) NOT NULL,
        status VARCHAR(20) DEFAULT 'Available',
        monthly_rent DECIMAL(10,2) NOT NULL,
        cooling_type VARCHAR(50),
        wifi_access VARCHAR(50),
        kitchen_access VARCHAR(50),
        laundry_access VARCHAR(50),
        main_img VARCHAR(255),
        images TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // Payments table
    $payments_table = "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        boarder_id VARCHAR(20) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        month_covered VARCHAR(20) NOT NULL,
        mode_of_payment VARCHAR(50) NOT NULL,
        reference_number VARCHAR(100),
        receipt_image VARCHAR(255),
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        admin_notes TEXT,
        FOREIGN KEY (boarder_id) REFERENCES users(boarder_id)
    )";
    
    // Maintenance table
    $maintenance_table = "CREATE TABLE IF NOT EXISTS maintenance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        boarder_id VARCHAR(20) NOT NULL,
        room_number VARCHAR(10) NOT NULL,
        issue_type VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        status ENUM('not started', 'ongoing', 'completed') DEFAULT 'not started',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        completed_at TIMESTAMP NULL,
        FOREIGN KEY (boarder_id) REFERENCES users(boarder_id)
    )";
    
    // Announcements table
    $announcements_table = "CREATE TABLE IF NOT EXISTS announcements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        type ENUM('payment', 'general', 'maintenance') DEFAULT 'general',
        payment_qr_code VARCHAR(255),
        bank_account VARCHAR(100),
        gcash_number VARCHAR(20),
        created_by VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(boarder_id)
    )";
    
    // Execute table creation
    $conn->query($users_table);
    $conn->query($rooms_table);
    $conn->query($payments_table);
    $conn->query($maintenance_table);
    $conn->query($announcements_table);
    
    // Create default admin if not exists
    $check_admin = $conn->query("SELECT * FROM users WHERE role='admin'");
    if ($check_admin->num_rows == 0) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO users (boarder_id, fname, lname, email, contact, age, address, guardian_fullname, guardian_relationship, guardian_contact, password, role, status) 
                     VALUES ('ADMIN001', 'System', 'Administrator', 'admin@ebms.com', '0000000000', 30, 'System Address', 'N/A', 'N/A', '0000000000', '$admin_password', 'admin', 'approved')");
    }
    
    // Insert sample rooms if not exists
    $check_rooms = $conn->query("SELECT * FROM rooms");
    if ($check_rooms->num_rows == 0) {
        $sample_rooms = [
            "('101', 'Bunk Bed', 'Private', 'Available', 5000.00, 'A/C', 'None', 'Private', 'Shared', 'img/101a.jpg', 'img/101b.jpg,img/101c.jpg')",
            "('102', 'Single Bed', 'Shared', 'Available', 4000.00, 'Fan', 'Available', 'Shared', 'Shared', 'img/102a.jpg', 'img/102b.jpg,img/102c.jpg')",
            "('103', 'Double Bed', 'Private', 'Available', 6000.00, 'A/C', 'Available', 'Private', 'Private', 'img/103a.jpg', 'img/103b.jpg,img/103c.jpg')"
        ];
        
        foreach ($sample_rooms as $room) {
            $conn->query("INSERT INTO rooms (room_number, bed_type, bathroom_type, status, monthly_rent, cooling_type, wifi_access, kitchen_access, laundry_access, main_img, images) VALUES $room");
        }
    }
}

// Call function to create tables
createTables($conn);
?>