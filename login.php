<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $boarder_id = $conn->real_escape_string($_POST['boarder_id']);
    $password = $_POST['password'];
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE boarder_id = ? AND status = 'approved'");
    $stmt->bind_param("s", $boarder_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password']) || $password == $user['boarder_id']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['boarder_id'] = $user['boarder_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['fname'] = $user['fname'];
            $_SESSION['lname'] = $user['lname'];
            
            // Redirect based on role
            if ($user['role'] == 'admin') {
                header("Location: ad-dashboard.php");
            } else {
                header("Location: bd-dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password. Please try again.";
        }
    } else {
        $error = "Invalid Boarder ID or account not approved yet.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | eBMS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Titan+One&family=Tomorrow:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Reset and base styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: sans-serif;
        background: #FFF8F0;
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }


    .main-content {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px 20px;
        margin-top: 80px; /* Added margin to prevent header overlap */
        min-height: calc(100vh - 160px); /* Ensure proper vertical centering */
    }

    /* Form container */
    .form-container {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 450px;
        border: 2px solid #7a5af5;
        margin-top: 20px; /* Additional spacing */
    }

    .form-container h2 {
        text-align: center;
        margin-bottom: 30px;
        color: #4c56a1;
        font-family: Arial Black;
        font-size: 1.8em;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }

    .form-group label.required::after {
        content: " *";
        color: #e74c3c;
    }

    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: 'Tomorrow', sans-serif;
        font-size: 1em;
        transition: border 0.3s;
    }

    .form-group input:focus {
        border-color: #416cec;
        outline: none;
        box-shadow: 0 0 0 2px rgba(65, 108, 236, 0.2);
    }

    .btn {
        background: linear-gradient(90deg, #416cec, #345dd8);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px 25px;
        font-size: 1em;
        font-family: 'Tomorrow', sans-serif;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(65, 108, 236, 0.3);
        width: 100%;
        font-weight: 600;
    }

    .btn:hover {
        background: linear-gradient(90deg, #345dd8, #416cec);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(65, 108, 236, 0.4);
    }

    .error-message {
        background: #ffebee;
        color: #c62828;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        border-left: 4px solid #e74c3c;
    }

    .form-footer {
        text-align: center;
        margin-top: 20px;
        color: #666;
        font-size: 0.9em;
    }

    .form-footer a {
        color: #416cec;
        text-decoration: none;
    }

    .form-footer a:hover {
        text-decoration: underline;
    }

    .form-footer p {
        margin-bottom: 8px;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .form-container {
            padding: 30px 20px;
        }
        
        .main-content {
            padding: 20px 15px;
            margin-top: 70px; /* Adjusted for mobile */
            min-height: calc(100vh - 140px);
        }
    }

    @media (max-width: 480px) {
        .form-container {
            padding: 25px 15px;
        }
        
        .main-content {
            margin-top: 60px; /* Further adjustment for very small screens */
            padding: 15px 10px;
        }
        
        .form-container h2 {
            font-size: 1.5em;
        }
    }
  </style>
</head>
<body>

  <div class="main-content" id="mainContent">
    <div class="form-container">
      <h2>Login to eBMS</h2>
      
      <?php if (isset($error)): ?>
        <div class="error-message">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" action="login.php">
        <div class="form-group">
          <label class="required">Boarder ID:</label>
          <input type="text" name="boarder_id" required placeholder="Enter your Boarder ID">
        </div>
        
        <div class="form-group">
          <label class="required">Password:</label>
          <input type="password" name="password" required placeholder="Enter your password">
        </div>
        
        <div class="form-group">
          <button type="submit" class="btn">Login</button>
        </div>
        
        <div class="form-footer">
          <p>Don't have an account? <a href="rooms.php">Reserve a room first</a></p>
          <p><small>Use your Boarder ID as temporary password for first login</small></p>
        </div>
      </form>
    </div>
  </div>
</body>
</html>