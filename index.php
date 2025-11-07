<?php 
session_start();
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome to eBMS</title>
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
        background: #FFF8F0;
        margin: 0;
        padding: 0;
        min-height: 100vh;
    }

    .menu-toggle {
      background: none;
      border: none;
      color: white;
      font-size: 1.5em;
      cursor: pointer;
      padding: 5px 10px;
      border-radius: 5px;
      transition: background 0.3s;
    }

    .menu-toggle:hover {
      background: rgba(255,255,255,0.1);
    }

    .logo {
      font-family: Arial Black;
      font-size: 1.8em;
      font-weight: bold;
    }

    .login-link {
      color: white;
      text-decoration: none;
      padding: 8px 15px;
      border-radius: 5px;
      background: rgba(255,255,255,0.2);
      transition: background 0.3s;
    }

    .login-link:hover {
      background: rgba(255,255,255,0.3);
    }

    /* Main content area */
    .main-content {
        padding: 80px 20px 60px 20px;
        min-height: 100vh;
        background: #FFF8F0;
        transition: margin-left 0.3s ease;
        width: 100%;
    }

    /* Welcome section */
    .welcome-section {
        text-align: center;
        padding: 60px 20px;
        background: #E7DFF5;
        border-radius: 15px;
        margin-bottom: 40px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border: 2px solid #7a5af5;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }

    .welcome-section h1 {
        font-family: Arial Black;
        font-size: 3em;
        margin-bottom: 20px;
        color: #4c56a1;
        background: linear-gradient(135deg, #4c56a1, #7a5af5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .tagline {
        font-size: 1.3em;
        max-width: 700px;
        margin: 0 auto;
        line-height: 1.6;
        color: #5e7599;
        font-weight: 500;
    }

    /* Rooms container */
    .rooms-container {
        display: flex;
        justify-content: center;
        gap: 30px;
        flex-wrap: wrap;
        margin: 40px 0;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Room cards */
    .room-card {
        background: white;
        border: 2px solid #416cec;
        border-radius: 15px;
        padding: 25px;
        text-align: center;
        width: 320px;
        box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .room-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.15);
    }

    /* Updated Room Image Styles */
    .room-image-container {
        width: 100%;
        height: 200px;
        border-radius: 10px;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .room-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 10px;
        border: 2px solid #416cec;
    }

    .no-image {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #f8f9ff, #e9ecef);
        border: 2px dashed #416cec;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #7a5af5;
        font-size: 1em;
        text-align: center;
    }

    .no-image i {
        font-size: 2.5em;
        margin-bottom: 10px;
        opacity: 0.7;
    }

    .room-title {
        display: block;
        font-size: 1.4em;
        color: #2c3e50;
        margin: 10px 0;
        font-weight: bold;
    }

    .room-price {
        font-size: 1.2em;
        color: #416cec;
        font-weight: bold;
        margin: 15px 0;
    }

    .view-btn {
        background: linear-gradient(90deg, #416cec, #345dd8);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 12px 25px;
        font-size: 1em;
        font-family: Arial Black;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(65, 108, 236, 0.3);
        text-decoration: none;
        display: inline-block;
        font-weight: 600;
        width: 100%;
    }

    .view-btn:hover {
        background: linear-gradient(90deg, #345dd8, #416cec);
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(65, 108, 236, 0.4);
    }

    /* Note box */
    .note-box {
        text-align: center;
        background: #e8f4ff;
        border: 2px solid #416cec;
        border-radius: 12px;
        padding: 25px;
        margin: 50px auto;
        max-width: 700px;
        color: #2c3e50;
        font-size: 1.1em;
        line-height: 1.6;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    /* No rooms state */
    .no-rooms {
        text-align: center;
        width: 100%;
        padding: 40px;
        color: #666;
    }

    .no-rooms i {
        font-size: 3em;
        margin-bottom: 20px;
        color: #ccc;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .main-content {
            padding: 70px 15px 50px 15px;
        }
        
        .welcome-section {
            padding: 40px 20px;
        }
        
        .welcome-section h1 {
            font-size: 2.2em;
        }
        
        .tagline {
            font-size: 1.1em;
        }
        
        .room-card {
            width: 100%;
            max-width: 350px;
            padding: 20px;
        }
        
        .rooms-container {
            gap: 20px;
        }
        
        .room-image-container {
            height: 180px;
        }
    }

    @media (max-width: 480px) {
        .room-image-container {
            height: 160px;
        }
        
        .no-image i {
            font-size: 2em;
        }
    }
  </style>
</head>
<body>
  <?php include 'index-header.php'; ?>
  <?php include 'index-sidebar.php'; ?>
  <header class="header">
    <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
    <div class="logo">eBMS</div>
    <a href="login.php" class="login-link">Login</a>
  </header>

  <div class="main-content" id="mainContent">
    <section class="welcome-section">
      <h1>eBMS</h1>
      <p class="tagline">Your home away from home — safe, cozy, and affordable living for professionals and students.</p>
    </section>

    <div class="rooms-container">
      <?php
      $rooms_result = $conn->query("SELECT * FROM rooms WHERE status='Available' LIMIT 3");
      if ($rooms_result->num_rows > 0) {
        while ($room = $rooms_result->fetch_assoc()) {
          // Get room image path or use placeholder
          $room_image = !empty($room['image_path']) ? $room['image_path'] : '';
          $has_image = !empty($room_image) && file_exists($room_image);
      ?>
      <div class="room-card">
        <div class="room-image-container">
          <?php if ($has_image): ?>
            <img src="<?php echo $room_image; ?>" 
                 alt="Room <?php echo $room['room_number']; ?>" 
                 class="room-image">
          <?php else: ?>
            <div class="no-image">
              <i class="fas fa-camera"></i>
              <span>No Image Available</span>
            </div>
          <?php endif; ?>
        </div>
        
        <span class="room-title">Room <?php echo $room['room_number']; ?></span>
        <div class="room-price">₱<?php echo number_format($room['monthly_rent'], 2); ?>/month</div>
        <button class="view-btn" onclick="viewRoomDetails('<?php echo $room['room_number']; ?>')">View Room Details</button>
      </div>
      <?php 
        }
      } else {
        echo '<div class="no-rooms">
                <i class="fas fa-bed"></i>
                <h3>No rooms available at the moment</h3>
                <p>Please check back later for available rooms.</p>
              </div>';
      }
      ?>
    </div>

    <div class="note-box">
      💡 Choose a room first before registering an account. You'll be notified once your account is approved and given a move-in date.
    </div>
  </div>

  <script>
    function viewRoomDetails(roomNumber) {
      window.location.href = 'room-detail.php?room=' + roomNumber;
    }

    // Sidebar functionality
    function toggleSidebar() {
      const sidebar = document.getElementById("sidebar");
      const overlay = document.getElementById("sidebarOverlay");
      
      if (sidebar) {
        sidebar.classList.toggle("active");
        overlay.classList.toggle("active");
        
        // Adjust main content margin
        if (window.innerWidth > 768) {
          if (sidebar.classList.contains("active")) {
            document.getElementById("mainContent").style.marginLeft = "250px";
          } else {
            document.getElementById("mainContent").style.marginLeft = "0";
          }
        }
      }
    }

    // Adjust layout on window resize
    window.addEventListener('resize', function() {
      const sidebar = document.getElementById("sidebar");
      const mainContent = document.getElementById("mainContent");
      
      if (window.innerWidth <= 768) {
        mainContent.style.marginLeft = "0";
      } else if (sidebar && sidebar.classList.contains("active")) {
        mainContent.style.marginLeft = "250px";
      }
    });

    // Initial adjustment
    document.addEventListener('DOMContentLoaded', function() {
      const sidebar = document.getElementById("sidebar");
      const mainContent = document.getElementById("mainContent");
      
      if (window.innerWidth > 768 && sidebar && sidebar.classList.contains("active")) {
        mainContent.style.marginLeft = "250px";
      }
    });
  </script>
  <?php include 'index-footer.php'; ?> 
</body>
</html>