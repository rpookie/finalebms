<?php  
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'boarder') {
    header("Location: login.php");
    exit();
}

// Get room number from URL or user data
$roomNumber = isset($_GET['room_number']) ? $_GET['room_number'] : '';

if (empty($roomNumber)) {
    // Try to get room from user's assigned room
    $user_id = $_SESSION['user_id'];
    $user_query = $conn->prepare("SELECT room_number FROM users WHERE id = ?");
    $user_query->bind_param("i", $user_id);
    $user_query->execute();
    $user_result = $user_query->get_result();
    $user_data = $user_result->fetch_assoc();
    
    if ($user_data && !empty($user_data['room_number'])) {
        $roomNumber = $user_data['room_number'];
    } else {
        echo "<div class='error-message'>No room number specified and no room assigned to your account.</div>";
        exit();
    }
}

// Fetch room data from database
$room_query = $conn->prepare("SELECT * FROM rooms WHERE room_number = ?");
$room_query->bind_param("s", $roomNumber);
$room_query->execute();
$room_result = $room_query->get_result();
$room = $room_result->fetch_assoc();

if (!$room) {
    echo "<div class='error-message'>Room not found: " . htmlspecialchars($roomNumber) . "</div>";
    exit();
}

// Get room image
$room_image = !empty($room['image_path']) ? $room['image_path'] : 'images/room-placeholder.jpg';
$room_images = !empty($room['images']) ? explode(',', $room['images']) : [];
?>

<div class="main-container">
    <?php include 'bd-header.php'; ?>
    <?php include 'bd-sidebar.php'; ?>

    <main class="main-content" id="mainContent" style="margin-top: <?php echo isset($is_from_index) && $is_from_index ? '0' : '70px'; ?>;">
      <div class="room-detail-container">
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

        <div class="room-detail">
          <!-- Single Image Section -->
          <div class="image-section">
            <div class="main-image-container">
              <div class="room-title">ROOM <?php echo $room['room_number']; ?></div>
              <?php if (!empty($room['image_path']) && file_exists($room['image_path'])): ?>
                <img src="<?php echo $room_image; ?>" class="main-img">
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
                <span class="detail-value"><?php echo isset($room['bed_type']) ? $room['bed_type'] : 'Not specified'; ?></span>
              </div>
              
              <div class="detail-item">
                <span class="detail-label">Bathroom:</span>
                <span class="detail-value"><?php echo isset($room['bathroom_type']) ? $room['bathroom_type'] : 'Not specified'; ?></span>
              </div>
              
              <div class="detail-item">
                <span class="detail-label">Status:</span>
                <span class="detail-value status-<?php echo isset($room['status']) ? strtolower($room['status']) : 'available'; ?>">
                  <?php echo isset($room['status']) ? $room['status'] : 'Available'; ?>
                </span>
              </div>
              
              <div class="detail-item">
                <span class="detail-label">Monthly Rent:</span>
                <span class="detail-value price-highlight">₱<?php echo isset($room['monthly_rent']) ? number_format($room['monthly_rent'], 2) : '0.00'; ?></span>
              </div>
              
              <div class="detail-item">
                <span class="detail-label">Cooling Type:</span>
                <span class="detail-value"><?php echo isset($room['cooling_type']) ? $room['cooling_type'] : 'Not specified'; ?></span>
              </div>
              
              <div class="detail-item">
                <span class="detail-label">Wi-Fi Access:</span>
                <span class="detail-value"><?php echo isset($room['wifi_access']) ? $room['wifi_access'] : 'Not specified'; ?></span>
              </div>
              
              <div class="detail-item">
                <span class="detail-label">Kitchen Access:</span>
                <span class="detail-value"><?php echo isset($room['kitchen_access']) ? $room['kitchen_access'] : 'Not specified'; ?></span>
              </div>
              
              <div class="detail-item">
                <span class="detail-label">Laundry Access:</span>
                <span class="detail-value"><?php echo isset($room['laundry_access']) ? $room['laundry_access'] : 'Not specified'; ?></span>
              </div>

              <!-- Additional room details if they exist in your database -->
              <?php if (isset($room['room_type']) && !empty($room['room_type'])): ?>
              <div class="detail-item">
                <span class="detail-label">Room Type:</span>
                <span class="detail-value"><?php echo $room['room_type']; ?></span>
              </div>
              <?php endif; ?>

              <?php if (isset($room['capacity']) && !empty($room['capacity'])): ?>
              <div class="detail-item">
                <span class="detail-label">Capacity:</span>
                <span class="detail-value"><?php echo $room['capacity']; ?> persons</span>
              </div>
              <?php endif; ?>

              <?php if (isset($room['floor']) && !empty($room['floor'])): ?>
              <div class="detail-item">
                <span class="detail-label">Floor:</span>
                <span class="detail-value"><?php echo $room['floor']; ?></span>
              </div>
              <?php endif; ?>
            </div>

            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: 'Tomorrow', sans-serif;
      background: #FFF8F0;
      margin: 0;
      padding: 0;
    }

    .main-container {
        background: #FFF8F0;
        min-height: 100vh;
    }

    .main-content {
        padding: 5px;
        min-height: 100vh;
        background: #FFF8F0;
        width: 100%;
        margin-top: auto;
    }

    .room-detail-container {
        max-width: 900px;
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
        gap: 20px;
    }

    .image-section {
        flex: 1;
        min-width: 350px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .main-image-container {
        width: 100%;
        text-align: center;
        position: relative;
    }

    .room-title {
        font-family: Arial Black;
        font-size: 2.8em;
        color: #4c56a1;
        margin-bottom: 30px;
        background: linear-gradient(135deg, #4c56a1, #7a5af5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .main-img {
        width: 100%;
        max-width: 400px;
        height: 400px;
        object-fit: cover;
        border-radius: 20px;
        border: 3px solid #416cec;
        box-shadow: 0 8px 25px rgba(65, 108, 236, 0.3);
        background: #f8f9ff;
    }

    .no-image {
        width: 100%;
        max-width: 500px;
        height: 500px;
        background: linear-gradient(135deg, #f8f9ff, #e9ecef);
        border: 3px dashed #416cec;
        border-radius: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #7a5af5;
        font-size: 1.2em;
        text-align: center;
    }

    .no-image i {
        font-size: 4em;
        margin-bottom: 20px;
        opacity: 0.7;
    }

    .details-section {
        flex: 1;
        min-width: 400px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 20px 0;
    }

    .details-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 30px;
    }

    .detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background: #f8f9ff;
        border-radius: 12px;
        border: 1px solid #eaeaea;
        transition: all 0.3s ease;
        cursor: default;
    }

    .detail-item:hover {
        background: #edf2ff;
        border-color: #416cec;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(65, 108, 236, 0.2);
    }

    .detail-label {
        font-weight: 700;
        color: #2c3e50;
        font-size: 1.1em;
        flex: 1;
    }

    .detail-value {
        color: #416cec;
        font-weight: 600;
        font-size: 1.1em;
        text-align: right;
        flex: 1;
    }

    .price-highlight {
        font-size: 1.8em;
        font-weight: bold;
        color: #e74c3c;
        text-shadow: 0 2px 4px rgba(231, 76, 60, 0.3);
    }

    /* Room status tags */
    .status-available {
        color: #27ae60;
        font-weight: bold;
        background: rgba(39, 174, 96, 0.1);
        padding: 5px 15px;
        border-radius: 20px;
        border: 2px solid #27ae60;
    }

    .status-occupied {
        color: #e74c3c;
        font-weight: bold;
        background: rgba(231, 76, 60, 0.1);
        padding: 5px 15px;
        border-radius: 20px;
        border: 2px solid #e74c3c;
    }

    .status-maintenance {
        color: #f39c12;
        font-weight: bold;
        background: rgba(243, 156, 18, 0.1);
        padding: 5px 15px;
        border-radius: 20px;
        border: 2px solid #f39c12;
    }

    .error-message {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        text-align: center;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
    }

    /* Mobile responsive styles */
    @media (max-width: 968px) {
        .room-detail {
            gap: 40px;
        }

        .image-section,
        .details-section {
            min-width: 350px;
        }

        .main-img,
        .no-image {
            height: 400px;
        }
    }

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

        .image-section,
        .details-section {
            width: 100%;
            min-width: auto;
        }

        .main-img,
        .no-image {
            height: 350px;
            max-width: 100%;
        }

        .room-title {
            font-size: 2.2em;
        }

        .detail-item {
            padding: 12px 15px;
        }

        .detail-label,
        .detail-value {
            font-size: 1em;
        }
    }

    @media (max-width: 480px) {
        .main-img,
        .no-image {
            height: 280px;
        }

        .room-detail-container {
            padding: 20px 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-width: 1px;
        }

        .room-title {
            font-size: 1.8em;
            margin-bottom: 20px;
        }
    }

  </style>  
  <?php include 'bd-footer.php' ?>
</body>
</html>