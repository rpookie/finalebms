<?php 
session_start();
include 'db.php';

// Get room number from URL parameter
$roomNumber = isset($_GET['room']) ? $_GET['room'] : '';

if (!$roomNumber) {
    header("Location: rooms.php");
    exit();
}

// Fetch room data from database
$room_query = $conn->prepare("SELECT * FROM rooms WHERE room_number = ?");
$room_query->bind_param("s", $roomNumber);
$room_query->execute();
$room_result = $room_query->get_result();
$room = $room_result->fetch_assoc();

if (!$room) {
    header("Location: rooms.php");
    exit();
}

// Get room image
$room_image = !empty($room['image_path']) ? $room['image_path'] : 'images/room-placeholder.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Room <?php echo $room['room_number']; ?> Details | eBMS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Titan+One&family=Tomorrow:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial Black;
        background: #f8f9ff;
        margin: 0;
        padding: 0;
        min-height: 100vh;
    }

    .main-content {
        padding: 100px 20px 60px 20px;
        min-height: 100vh;
        background: linear-gradient(180deg, #f8f9ff 0%, #ffffff 100%);
        width: 100%;
    }

    .room-detail-container {
        max-width: 1000px;
        margin: 0 auto;
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        border: 2px solid #7a5af5;
    }

    .room-detail {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: flex-start;
        gap: 50px;
    }

    .image-section {
        flex: 1;
        min-width: 300px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        align-items: center;
    }

    .main-image-container {
        width: 100%;
        max-width: 400px;
        text-align: center;
    }

    .main-image-title {
        font-size: 1.5em;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 15px;
        text-align: center;
    }

    .main-img {
        margin-top: 50px;
        width: 450px;
        height: 550px;
        object-fit: cover;
        border-radius: 15px;
        border: 3px solid #416cec;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        background: #f8f9ff;
    }

    .no-image {
        width: 100%;
        height: 300px;
        background: linear-gradient(135deg, #f8f9ff, #e9ecef);
        border: 3px dashed #416cec;
        border-radius: 15px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #7a5af5;
        font-size: 1.1em;
        text-align: center;
    }

    .no-image i {
        font-size: 3em;
        margin-bottom: 15px;
        opacity: 0.7;
    }

    .details-section {
        flex: 1;
        min-width: 350px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .room-title {
        font-family: Arial Black;
        font-size: 2.5em;
        color: #4c56a1;
        margin-top: 20px;
        margin-bottom: 10px;
        background: linear-gradient(135deg, #4c56a1, #7a5af5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-align: center;
    }

    .details-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
        padding: 12px 0;
        border-bottom: 1px solid #eaeaea;
    }

    .detail-label {
        font-weight: 600;
        color: #2c3e50;
        font-size: 1em;
        margin-bottom: 5px;
    }

    .detail-value {
        color: #416cec;
        font-weight: 500;
        font-size: 1.1em;
        padding-left: 10px;
    }

    .price-highlight {
        font-size: 1.6em;
        font-weight: bold;
        color: #e74c3c;
    }

    .status-available {
        color: #27ae60;
        font-weight: bold;
        font-size: 1.1em;
    }

    .status-occupied {
        color: #e74c3c;
        font-weight: bold;
    }

    .status-maintenance {
        color: #f39c12;
        font-weight: bold;
    }

    .status-reserved {
        color: #9b59b6;
        font-weight: bold;
    }

    .reserve-btn {
        background: linear-gradient(90deg, #416cec, #345dd8);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 18px 30px;
        font-size: 1.2em;
        font-family: Arial Black;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(65, 108, 236, 0.3);
        text-decoration: none;
        display: inline-block;
        font-weight: 600;
        text-align: center;
        margin-top: 30px;
        width: 100%;
    }

    .reserve-btn:hover {
        background: linear-gradient(90deg, #345dd8, #416cec);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(65, 108, 236, 0.4);
    }

    .reserve-btn:disabled {
        background: #95a5a6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .main-content {
            padding: 80px 15px 50px 15px;
        }
        
        .room-detail-container {
            padding: 25px;
            margin: 10px;
        }
        
        .room-detail {
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }
        
        .image-section, .details-section {
            width: 100%;
            min-width: auto;
        }
        
        .main-img {
            height: 250px;
        }
        
        .room-title {
            font-size: 2em;
            text-align: center;
        }
        
        .main-image-title {
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .main-img {
            height: 220px;
        }
        
        .room-detail-container {
            padding: 20px;
        }
        
        .room-title {
            font-size: 1.8em;
        }
    }
  </style>
</head>
<body>
  <?php include 'index-header.php'; ?>
  <?php include 'index-sidebar.php'; ?>

  <div class="main-content" id="mainContent">
    <div class="room-detail-container">
      <div class="room-detail">
        <!-- Single Image Section -->

        <div class="image-section">
          <div class="main-image-container">
            <div class="room-title">ROOM <?php echo $room['room_number']; ?></div>
            <?php if (!empty($room['image_path']) && file_exists($room['image_path'])): ?>
              <img src="<?php echo $room_image; ?>" 
                   class="main-img">
            <?php else: ?>
              <div class="no-image">
                <i class="fas fa-camera"></i>
                <span>No Image Available</span>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Details Section -->
        <div class="details-section">   
          <div class="details-list">
            <div class="detail-item">
              <span class="detail-label">Bed Type:</span>
              <span class="detail-value"><?php echo $room['bed_type']; ?></span>
            </div>
            
            <div class="detail-item">
              <span class="detail-label">Bathroom:</span>
              <span class="detail-value"><?php echo $room['bathroom_type']; ?></span>
            </div>
            
            <div class="detail-item">
              <span class="detail-label">Status:</span>
              <span class="detail-value status-<?php echo strtolower($room['status']); ?>">
                <?php echo $room['status']; ?>
              </span>
            </div>
            
            <div class="detail-item">
              <span class="detail-label">Monthly Rent:</span>
              <span class="detail-value price-highlight">₱<?php echo number_format($room['monthly_rent'], 2); ?></span>
            </div>
            
            <div class="detail-item">
              <span class="detail-label">Cooling Type:</span>
              <span class="detail-value"><?php echo $room['cooling_type']; ?></span>
            </div>
            
            <div class="detail-item">
              <span class="detail-label">Wi-Fi Access:</span>
              <span class="detail-value"><?php echo $room['wifi_access']; ?></span>
            </div>
            
            <div class="detail-item">
              <span class="detail-label">Kitchen Access:</span>
              <span class="detail-value"><?php echo $room['kitchen_access']; ?></span>
            </div>
            
            <div class="detail-item">
              <span class="detail-label">Laundry Access:</span>
              <span class="detail-value"><?php echo $room['laundry_access']; ?></span>
            </div>
          </div>

          <?php if ($room['status'] == 'Available'): ?>
            <a href="reserve-room.php?room=<?php echo $roomNumber; ?>" class="reserve-btn">
              Reserve This Room
            </a>
          <?php else: ?>
            <button class="reserve-btn" disabled>
              Room <?php echo $room['status']; ?>
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php include 'index-footer.php'; ?>
</body>
</html>