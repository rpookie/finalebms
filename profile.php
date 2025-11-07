<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_picture'])) {
    $target_dir = "uploads/profiles/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is actual image
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if ($check !== false) {
        // Generate unique filename
        $new_filename = "profile_" . $user_id . "." . $imageFileType;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            // Update database
            $update_stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_filename, $user_id);
            if ($update_stmt->execute()) {
                $success = "Profile picture updated successfully!";
                $user['profile_picture'] = $new_filename;
            }
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    } else {
        $error = "File is not an image.";
    }
}

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password length
    if (strlen($new_password) < 8) {
        $password_error = "Password must be at least 8 characters long.";
    } else if ($new_password !== $confirm_password) {
        $password_error = "New passwords do not match.";
    } else if (password_verify($current_password, $user['password'])) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->bind_param("si", $hashed_password, $user_id);
        if ($update_stmt->execute()) {
            $password_success = "Password changed successfully!";
        }
    } else {
        $password_error = "Current password is incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile | eBMS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Titan+One&family=Tomorrow:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Tomorrow', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
        background-color: #f8f9fa;
        color: #333;
        line-height: 1.6;
    }
    
    .main-container {
        display: flex;
        min-height: 100vh;
        padding-top: 70px;
    }
    
    .main-content {
        flex: 1;
        padding: 30px;
        margin-left: 0;
        transition: margin-left 0.3s ease;
        background: #f8f9fa;
    }
    
    .main-content.with-sidebar {
        margin-left: 250px;
    }
    
    .content-header {
        background: white;
        padding: 25px 30px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 30px;
        border-left: 4px solid #4263eb;
    }
    
    .content-header h1 {
        color: #4263eb;
        font-size: 2rem;
        margin-bottom: 8px;
        font-weight: 700;
        font-family: 'Arial Black', Gadget, sans-serif;
    }
    
    .content-header p {
        color: #666;
        font-size: 1.1rem;
        font-weight: 400;
    }
    
    .profile-container {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
    }
    
    .profile-section {
        flex: 1;
        min-width: 300px;
    }
    
    .info-section {
        flex: 2;
        min-width: 300px;
    }
    
    .profile-card, .info-card {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin-bottom: 25px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .profile-card:hover, .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }
    
    .profile-header {
        text-align: center;
        margin-bottom: 25px;
    }
    
    .profile-avatar {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        overflow: hidden;
        margin: 0 auto 20px;
        border: 4px solid #4263eb;
        position: relative;
    }
    
    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .profile-name {
        font-size: 1.4rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 5px;
    }
    
    .profile-id {
        color: #666;
        font-size: 0.95rem;
        font-weight: 500;
    }
    
    .upload-form {
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #eaeaea;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #495057;
        font-size: 0.95rem;
    }
    
    .form-group label.required::after {
        content: " *";
        color: #dc3545;
    }
    
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="password"],
    .form-group input[type="file"],
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: #fff;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #4263eb;
        box-shadow: 0 0 0 3px rgba(66, 99, 235, 0.1);
    }
    
    .btn {
        background: #4263eb;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.95rem;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn:hover {
        background: #3451d8;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(66, 99, 235, 0.3);
    }
    
    .btn-secondary {
        background: #6c757d;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .info-item {
        margin-bottom: 15px;
    }
    
    .info-item label {
        font-weight: 600;
        color: #495057;
        font-size: 0.9rem;
        margin-bottom: 5px;
        display: block;
    }
    
    .info-item p {
        color: #333;
        font-size: 1rem;
        padding: 8px 0;
    }
    
    .full-width {
        grid-column: 1 / -1;
    }
    
    .section-title {
        color: #4263eb;
        font-size: 1.3rem;
        margin-bottom: 20px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .section-title i {
        font-size: 1.1rem;
    }
    
    .success-message {
        background: #d4edda;
        color: #155724;
        padding: 12px 15px;
        border-radius: 6px;
        margin-top: 15px;
        border: 1px solid #c3e6cb;
        font-size: 0.9rem;
    }
    
    .error-message {
        background: #f8d7da;
        color: #721c24;
        padding: 12px 15px;
        border-radius: 6px;
        margin-top: 15px;
        border: 1px solid #f5c6cb;
        font-size: 0.9rem;
    }
    
    .password-requirements {
        background: #e7f3ff;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        border-left: 4px solid #4263eb;
    }
    
    .password-requirements h4 {
        color: #4263eb;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }
    
    .password-requirements ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .password-requirements li {
        color: #666;
        font-size: 0.85rem;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .password-requirements li i {
        color: #4263eb;
        font-size: 0.7rem;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .main-container {
            flex-direction: column;
        }
        
        .main-content {
            margin-left: 0 !important;
            padding: 20px 15px;
        }
        
        .content-header {
            padding: 20px;
        }
        
        .content-header h1 {
            font-size: 1.6rem;
        }
        
        .profile-container {
            flex-direction: column;
            gap: 20px;
        }
        
        .profile-section,
        .info-section {
            min-width: 100%;
        }
        
        .profile-card,
        .info-card {
            padding: 20px;
            border-radius: 10px;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
        }
        
        .info-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
    }
    
    @media (max-width: 480px) {
        .content-header h1 {
            font-size: 1.4rem;
        }
        
        .content-header p {
            font-size: 1rem;
        }
        
        .profile-name {
            font-size: 1.2rem;
        }
        
        .section-title {
            font-size: 1.1rem;
        }
        
        .btn {
            padding: 10px 20px;
            font-size: 0.9rem;
        }
    }
    
    /* Animation for success messages */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .success-message,
    .error-message {
        animation: fadeIn 0.3s ease;
    }
  </style>
</head>
<body>
  <?php 
  if ($_SESSION['role'] == 'boarder') {
      include 'bd-header.php';
  } else {
      include 'ad-header.php';
  }
  ?>

  <div class="main-container">
    <?php 
    if ($_SESSION['role'] == 'boarder') {
        include 'bd-sidebar.php';
    } else {
        include 'ad-sidebar.php';
    }
    ?>

    <main class="main-content" id="mainContent">
      <div class="content-header">
        <h1>My Profile</h1>
        <p>Manage your personal information and account settings</p>
      </div>

      <div class="profile-container">
        <!-- Profile Picture Section -->
        <div class="profile-section">
          <div class="profile-card">
            <div class="profile-header">
              <div class="profile-avatar">
                <img src="<?php echo $user['profile_picture'] ? 'uploads/profiles/' . $user['profile_picture'] : 'img/default-avatar.png'; ?>" 
                     alt="Profile Picture">
              </div>
              <h3 class="profile-name"><?php echo $user['fname'] . ' ' . $user['lname']; ?></h3>
              <p class="profile-id">Boarder ID: <?php echo $user['boarder_id']; ?></p>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="upload-form">
              <div class="form-group">
                <label class="required">Update Profile Picture:</label>
                <input type="file" name="profile_picture" accept="image/*" required>
              </div>
              <button type="submit" class="btn">
                <i class="fas fa-upload"></i> Upload Picture
              </button>
              
              <?php if (isset($success)): ?>
                <div class="success-message">
                  <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
              <?php endif; ?>
              <?php if (isset($error)): ?>
                <div class="error-message">
                  <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
              <?php endif; ?>
            </form>
          </div>
        </div>

        <!-- Personal Information Section -->
        <div class="info-section">
          <!-- Personal Information -->
          <div class="info-card">
            <h3 class="section-title">
              <i class="fas fa-user"></i> Personal Information
            </h3>
            <div class="info-grid">
              <div class="info-item">
                <label>First Name:</label>
                <p><?php echo $user['fname']; ?></p>
              </div>
              <div class="info-item">
                <label>Middle Name:</label>
                <p><?php echo $user['mname'] ?: 'N/A'; ?></p>
              </div>
              <div class="info-item">
                <label>Last Name:</label>
                <p><?php echo $user['lname']; ?></p>
              </div>
              <div class="info-item">
                <label>Age:</label>
                <p><?php echo $user['age']; ?></p>
              </div>
              <div class="info-item full-width">
                <label>Address:</label>
                <p><?php echo $user['address']; ?></p>
              </div>
              <div class="info-item">
                <label>Email:</label>
                <p><?php echo $user['email']; ?></p>
              </div>
              <div class="info-item">
                <label>Contact No.:</label>
                <p><?php echo $user['contact']; ?></p>
              </div>
              <div class="info-item">
                <label>Room Number:</label>
                <p><?php echo $user['room_number'] ?: 'Not assigned'; ?></p>
              </div>
            </div>
          </div>

          <!-- Guardian Information -->
          <div class="info-card">
            <h3 class="section-title">
              <i class="fas fa-users"></i> Guardian Information
            </h3>
            <div class="info-grid">
              <div class="info-item">
                <label>Full Name:</label>
                <p><?php echo $user['guardian_fullname']; ?></p>
              </div>
              <div class="info-item">
                <label>Relationship:</label>
                <p><?php echo $user['guardian_relationship']; ?></p>
              </div>
              <div class="info-item">
                <label>Contact No.:</label>
                <p><?php echo $user['guardian_contact']; ?></p>
              </div>
              <div class="info-item">
                <label>Email:</label>
                <p><?php echo $user['guardian_email'] ?: 'N/A'; ?></p>
              </div>
            </div>
          </div>

          <!-- Change Password -->
          <div class="info-card">
            <h3 class="section-title">
              <i class="fas fa-lock"></i> Change Password
            </h3>
            
            <div class="password-requirements">
              <h4>Password Requirements:</h4>
              <ul>
                <li><i class="fas fa-check-circle"></i> Minimum 8 characters long</li>
                <li><i class="fas fa-check-circle"></i> Must match in both fields</li>
                <li><i class="fas fa-check-circle"></i> Current password must be correct</li>
              </ul>
            </div>
            
            <form method="POST">
              <input type="hidden" name="change_password" value="1">
              <div class="form-group">
                <label class="required">Current Password:</label>
                <input type="password" name="current_password" required>
              </div>
              <div class="form-group">
                <label class="required">New Password:</label>
                <input type="password" name="new_password" required minlength="8" placeholder="At least 8 characters">
              </div>
              <div class="form-group">
                <label class="required">Confirm New Password:</label>
                <input type="password" name="confirm_password" required minlength="8" placeholder="Confirm your new password">
              </div>
              <button type="submit" class="btn">
                <i class="fas fa-key"></i> Change Password
              </button>
              
              <?php if (isset($password_success)): ?>
                <div class="success-message">
                  <i class="fas fa-check-circle"></i> <?php echo $password_success; ?>
                </div>
              <?php endif; ?>
              <?php if (isset($password_error)): ?>
                <div class="error-message">
                  <i class="fas fa-exclamation-circle"></i> <?php echo $password_error; ?>
                </div>
              <?php endif; ?>
            </form>
          </div>
        </div>
      </div>
    </main>
  </div>

  <?php 
  if ($_SESSION['role'] == 'boarder') {
      include 'bd-footer.php';
  } else {
      include 'ad-footer.php';
  }
  ?>
</body>
</html>