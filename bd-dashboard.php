<?php
session_start();
include 'db.php';

// Check if user is logged in and is a boarder
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'boarder') {
    header("Location: login.php");
    exit();
}

// Get boarder information
$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Get payment due date (example: 7th of next month)
$next_month = date('Y-m-07', strtotime('+1 month'));
$due_date = date('M j, Y', strtotime($next_month));

// Get stay duration
$stay_since = "Jan 25, 2025"; // This would come from database in real implementation
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Boarder Dashboard - eBMS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Titan+One&family=Tomorrow:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Reset and base styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial Black;
        background: #FFF8F0;
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    /* Header Styles */
    header {
        background: linear-gradient(135deg, #667eea 30%, #764ba2 70%);
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .menu-btn {
        background: none;
        border: none;
        color: white;
        font-size: 1.5em;
        cursor: pointer;
        padding: 5px 10px;
        border-radius: 5px;
        transition: background 0.3s ease;
    }

    .menu-btn:hover {
        background: rgba(255,255,255,0.1);
    }

    .header-title {
        font-family: Arial Black;
        font-size: 1.5em;
    }

    /* Dropdown Styles */
    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropbtn {
        background: rgba(255,255,255,0.1);
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 8px;
        cursor: pointer;
        font-family: 'Tomorrow', sans-serif;
        font-size: 1em;
        transition: background 0.3s ease;
    }

    .dropbtn:hover {
        background: rgba(255,255,255,0.2);
    }

    .dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        background: white;
        min-width: 160px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        z-index: 1001;
        border-radius: 8px;
        overflow: hidden;
    }

    .dropdown-content a {
        color: #333;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        transition: background 0.3s ease;
    }

    .dropdown-content a:hover {
        background: #f1f1f1;
        color: #416cec;
    }

    .dropdown.active .dropdown-content {
        display: block;
    }


    /* Main content area */
    .main-container {
        margin-top: 70px;
        padding: 20px;
        transition: margin-left 0.3s ease;
        flex: 1;
        width: 100%;
    }

    .main-content {
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Only shift content when sidebar is active on larger screens */
    @media (min-width: 769px) {
        .sidebar.active ~ .main-container {
            margin-left: 250px;
            width: calc(100% - 250px);
        }
    }

    /* Dashboard Styles */
    .main-content h2 {
        font-family: 'Titan One', cursive;
        font-size: 2.5em;
        color: #4c56a1;
        margin-bottom: 10px;
        background: linear-gradient(135deg, #4c56a1, #7a5af5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .main-content > p {
        font-size: 1.2em;
        color: #666;
        margin-bottom: 30px;
    }

    .info-boxes {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .info-box {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        border-left: 4px solid #416cec;
    }

    .info-box strong {
        color: #2c3e50;
        display: block;
        margin-bottom: 8px;
        font-size: 1.1em;
    }

    .info-box strong i {
        margin-right: 8px;
        color: #416cec;
    }

    .info-box span {
        color: #416cec;
        font-weight: 600;
        font-size: 1.1em;
    }

    .buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 40px;
    }

    .btn {
        background: linear-gradient(135deg, #416cec, #345dd8);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 15px 20px;
        font-size: 1.1em;
        font-family: 'Tomorrow', sans-serif;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(65, 108, 236, 0.3);
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-align: center;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(65, 108, 236, 0.4);
    }

    /* Announcements */
    .announcements {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        max-width: 600px;
    }

    .announcements h3 {
        color: #4263eb;
        margin-bottom: 20px;
        font-size: 1.4em;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 10px;
    }

    .announcement-item {
        border-left: 3px solid #4263eb;
        padding-left: 15px;
        margin-bottom: 20px;
    }

    .announcement-item h4 {
        margin: 0 0 8px 0;
        color: #2c3e50;
    }

    .announcement-item p {
        margin: 0 0 5px 0;
        color: #666;
        font-size: 0.95em;
        line-height: 1.4;
    }

    .announcement-date {
        color: #999;
        font-size: 0.85em;
    }

    .view-all {
        display: inline-block;
        margin-top: 15px;
        color: #4263eb;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .view-all:hover {
        color: #345dd8;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .sidebar.active ~ .main-container {
            margin-left: 0;
        }
        
        .info-boxes {
            grid-template-columns: 1fr;
        }
        
        .buttons {
            grid-template-columns: 1fr;
        }
        
        .main-content h2 {
            font-size: 2em;
        }
        
        header {
            padding: 12px 15px;
        }
        
        .header-title {
            font-size: 1.3em;
        }
    }

    @media (max-width: 480px) {
        .main-container {
            padding: 15px;
        }
        
        .sidebar {
            width: 280px;
            left: -280px;
        }
        
        .info-box {
            padding: 15px;
        }
        
        .btn {
            padding: 12px 15px;
            font-size: 1em;
        }
    }
  </style>
</head>
<body>
    
  <!-- Header -->
  <?php include 'bd-header.php'; ?>
  
  <!-- Sidebar (includes its own JavaScript) -->
  <?php include 'bd-sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-container">
    <main class="main-content" id="mainContent">
      <h2>ROOM <?php echo $user['room_number']; ?></h2>
      <p>Welcome back, <?php echo $user['fname'] . ' ' . $user['lname']; ?>!</p>

      <div class="info-boxes">
        <div class="info-box">
          <strong><i class="fa-regular fa-calendar-xmark"></i> Payment Due Date:</strong>
          <span><?php echo $due_date; ?></span>
        </div>

        <div class="info-box">
          <strong><i class="fa-regular fa-calendar"></i> Stay Duration:</strong>
          <span>Since <?php echo $stay_since; ?></span>
        </div>

        <div class="info-box">
          <strong><i class="fa-solid fa-bed"></i> Room Status:</strong>
          <span><?php echo $user['room_number'] ? 'Occupied' : 'No Room Assigned'; ?></span>
        </div>                                                
      </div>

      <div class="buttons">
        <a href="payment.php" class="btn">
          <i class="fa-solid fa-money-bill-wave"></i> Pay Now
        </a>
        <a href="payment-history.php" class="btn">
          <i class="fa-solid fa-clock-rotate-left"></i> View History
        </a>
        <a href="maintenance.php" class="btn">
          <i class="fa-solid fa-tools"></i> Maintenance
        </a>
      </div>

      <!-- Quick Announcements -->
      <div class="announcements">
        <h3>Latest Announcements</h3>
        <?php
        $announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");
        if ($announcements->num_rows > 0) {
            while ($announcement = $announcements->fetch_assoc()) {
                echo "<div class='announcement-item'>";
                echo "<h4>{$announcement['title']}</h4>";
                echo "<p>{$announcement['content']}</p>";
                echo "<small class='announcement-date'>" . date('M j, Y', strtotime($announcement['created_at'])) . "</small>";
                echo "</div>";
            }
        } else {
            echo "<p>No announcements at the moment.</p>";
        }
        ?>
        <a href="announcements.php" class="view-all">View All Announcements →</a>
      </div>
    </main>
  </div>

  <!-- Footer -->
  <?php include 'bd-footer.php'; ?>

</body>
</html>