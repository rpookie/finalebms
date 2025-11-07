<?php 
session_start();
include 'db.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rooms | eBoard Management System</title>
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
    }

    /* Main content area */
    .main-content {
        padding: 80px 20px 60px 20px;
        min-height: 100vh;
        background: #FFF8F0;
        transition: margin-left 0.3s ease;
        width: 100%;
    }

    .page-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .page-header h1 {
        font-family: Arial Black;
        font-size: 2.8em;
        color: #333333;
        margin-bottom: 15px;
        background: #333333;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .page-header p {
        font-size: 1.2em;
        color: #333333;
    }

    .rooms-container {
        display: flex;
        justify-content: center;
        gap: 30px;
        flex-wrap: wrap;
        max-width: 1200px;
        margin: 0 auto;
    }

    .room-card {
        background: white;
        border: 2px solid #416cec;
        border-radius: 15px;
        padding: 25px;
        text-align: center;
        width: 320px;
        box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        position: relative;
    }

    .room-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.15);
    }

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

    .room-status {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8em;
        font-weight: bold;
        text-transform: uppercase;
        z-index: 2;
    }

    .status-available {
        background: #27ae60;
        color: white;
    }

    .status-occupied {
        background: #e74c3c;
        color: white;
    }

    .status-reserved {
        background: #f39c12;
        color: white;
    }

    .status-maintenance {
        background: #95a5a6;
        color: white;
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

    .room-features {
        font-size: 0.9em;
        color: #666;
        margin: 10px 0;
        text-align: left;
    }

    .room-features div {
        margin: 5px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .room-features i {
        color: #416cec;
        width: 16px;
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
        margin-top: 10px;
    }

    .view-btn:hover {
        background: linear-gradient(90deg, #345dd8, #416cec);
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(65, 108, 236, 0.4);
    }

    .view-btn:disabled {
        background: #95a5a6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .no-rooms {
        text-align: center;
        width: 100%;
        padding: 60px;
        color: #666;
    }

    .no-rooms i {
        font-size: 4em;
        margin-bottom: 20px;
        color: #ccc;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .main-content {
            padding: 70px 15px 50px 15px;
        }
        
        .page-header h1 {
            font-size: 2.2em;
        }
        
        .page-header p {
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

  <div class="main-content" id="mainContent">
    <div class="page-header">
      <h1>Rooms</h1>
      <p>Browse all available and occupied rooms</p>
    </div>

    <div class="rooms-container">
      <?php
      // Get ALL rooms, not just available ones
      $rooms_result = $conn->query("SELECT * FROM rooms ORDER BY room_number");
      if ($rooms_result->num_rows > 0) {
        while ($room = $rooms_result->fetch_assoc()) {
          $status_class = 'status-' . strtolower($room['status']);
          $is_available = $room['status'] == 'Available';
          
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
          
          <span class="room-status <?php echo $status_class; ?>">
            <?php echo $room['status']; ?>
          </span>
        </div>
        
        <span class="room-title">Room <?php echo $room['room_number']; ?></span>
        <div class="room-price">₱<?php echo number_format($room['monthly_rent'], 2); ?>/month</div>
        
        <div class="room-features">
          <div><i class="fas fa-bed"></i> <?php echo $room['bed_type']; ?></div>
          <div><i class="fas fa-bath"></i> <?php echo $room['bathroom_type']; ?> Bathroom</div>
          <div><i class="fas fa-wifi"></i> <?php echo $room['wifi_access']; ?></div>
          <div><i class="fas fa-snowflake"></i> <?php echo $room['cooling_type']; ?></div>
        </div>

        <?php if ($is_available): ?>
          <button class="view-btn" onclick="viewRoomDetails('<?php echo $room['room_number']; ?>')">
            View Room Details
          </button>
        <?php else: ?>
          <button class="view-btn" onclick="viewRoomDetails('<?php echo $room['room_number']; ?>')">
            View Room Details
          </button>
        <?php endif; ?>
      </div>
      <?php 
        }
      } else {
        echo '<div class="no-rooms">
                <i class="fas fa-bed"></i>
                <h3>No rooms found</h3>
                <p>There are no rooms in the system yet.</p>
              </div>';
      }
      ?>
    </div>
  </div>

  <?php include 'index-footer.php'; ?>

  <script>
    function viewRoomDetails(roomNumber) {
      window.location.href = 'room-detail.php?room=' + roomNumber;
    }
  </script>
</body>
</html>