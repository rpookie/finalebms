<?php
session_start();
include 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// First, check if image_path column exists, if not, add it
$check_column = $conn->query("SHOW COLUMNS FROM rooms LIKE 'image_path'");
if ($check_column->num_rows == 0) {
    $conn->query("ALTER TABLE rooms ADD COLUMN image_path VARCHAR(255) DEFAULT ''");
}

// Handle room updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_room'])) {
    $room_number = $_POST['room_number'];
    $monthly_rent = floatval($_POST['monthly_rent']);
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE rooms SET monthly_rent = ?, status = ? WHERE room_number = ?");
    $stmt->bind_param("dss", $monthly_rent, $status, $room_number);
    
    if ($stmt->execute()) {
        $success = "Room updated successfully!";
    } else {
        $error = "Error updating room: " . $conn->error;
    }
}
// Handle room deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_room'])) {
    $room_number = $_POST['room_number'];
    
    // Check if room has any boarders assigned
    $check_boarders = $conn->query("SELECT COUNT(*) as count FROM users WHERE room_number = '$room_number' AND status = 'approved'");
    $boarder_count = $check_boarders->fetch_assoc()['count'];
    
    if ($boarder_count > 0) {
        $error = "Cannot delete room $room_number. There are boarders assigned to this room.";
    } else {
        // Delete room image if exists
        $room_data = $conn->query("SELECT image_path FROM rooms WHERE room_number = '$room_number'");
        if ($room_data && $room_data->num_rows > 0) {
            $image_path = $room_data->fetch_assoc()['image_path'];
            if ($image_path && file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM rooms WHERE room_number = ?");
        $stmt->bind_param("s", $room_number);
        
        if ($stmt->execute()) {
            $success = "Room $room_number deleted successfully!";
        } else {
            $error = "Error deleting room: " . $conn->error;
        }
    }
}

// Handle new room creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room'])) {
    $room_number = $_POST['room_number'];
    $bed_type = $_POST['bed_type'];
    $bathroom_type = $_POST['bathroom_type'];
    $monthly_rent = floatval($_POST['monthly_rent']);
    $cooling_type = $_POST['cooling_type'];
    $wifi_access = $_POST['wifi_access'];
    $kitchen_access = $_POST['kitchen_access'];
    $laundry_access = $_POST['laundry_access'];
    $status = 'Available';
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] == 0) {
        $upload_dir = 'uploads/rooms/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['room_image']['name'], PATHINFO_EXTENSION);
        $filename = 'room_' . $room_number . '_' . time() . '.' . $file_extension;
        $target_file = $upload_dir . $filename;
        
        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array(strtolower($file_extension), $allowed_types)) {
            if (move_uploaded_file($_FILES['room_image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            } else {
                $error = "Error uploading image.";
            }
        } else {
            $error = "Invalid file type. Only JPG, JPEG, PNG, GIF, and WEBP are allowed.";
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO rooms (room_number, bed_type, bathroom_type, monthly_rent, cooling_type, wifi_access, kitchen_access, laundry_access, status, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdssssss", $room_number, $bed_type, $bathroom_type, $monthly_rent, $cooling_type, $wifi_access, $kitchen_access, $laundry_access, $status, $image_path);
    
    if ($stmt->execute()) {
        $success = "Room added successfully!";
    } else {
        $error = "Error adding room: " . $conn->error;
    }
}

// Handle image update for existing rooms
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_room_image'])) {
    $room_number = $_POST['room_number'];
    
    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] == 0) {
        $upload_dir = 'uploads/rooms/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['room_image']['name'], PATHINFO_EXTENSION);
        $filename = 'room_' . $room_number . '_' . time() . '.' . $file_extension;
        $target_file = $upload_dir . $filename;
        
        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array(strtolower($file_extension), $allowed_types)) {
            // Delete old image if exists
            $old_image_result = $conn->query("SELECT image_path FROM rooms WHERE room_number = '$room_number'");
            if ($old_image_result && $old_image_result->num_rows > 0) {
                $old_image = $old_image_result->fetch_assoc()['image_path'];
                if ($old_image && file_exists($old_image)) {
                    unlink($old_image);
                }
            }
            
            if (move_uploaded_file($_FILES['room_image']['tmp_name'], $target_file)) {
                $stmt = $conn->prepare("UPDATE rooms SET image_path = ? WHERE room_number = ?");
                $stmt->bind_param("ss", $target_file, $room_number);
                if ($stmt->execute()) {
                    $success = "Room image updated successfully!";
                } else {
                    $error = "Error updating room image: " . $conn->error;
                }
            } else {
                $error = "Error uploading image.";
            }
        } else {
            $error = "Invalid file type. Only JPG, JPEG, PNG, GIF, and WEBP are allowed.";
        }
    }
}

// Get all rooms with error handling
$rooms_result = $conn->query("SELECT * FROM rooms ORDER BY room_number");
if (!$rooms_result) {
    $error = "Database error: " . $conn->error;
    $rooms = false;
} else {
    $rooms = $rooms_result;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Rooms | eBMS Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Titan+One&family=Tomorrow:wght@400;500;600;700&display=swap" rel="stylesheet">
    
</head>
<body>
  <?php include 'ad-header.php'; ?>

  <div class="main-container">
    <?php include 'ad-sidebar.php'; ?>

    <main class="main-content" id="mainContent">
      <div class="content-header">
        <h1>Manage Rooms</h1>
        <p>Update room information and status</p>
      </div>

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

      <div class="table-container">
        <?php if ($rooms && $rooms->num_rows > 0): ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Image</th>
                <th>Room Number</th>
                <th>Bed Type</th>
                <th>Bathroom</th>
                <th>Monthly Rent</th>
                <th>Cooling</th>
                <th>Wi-Fi</th>
                <th>Kitchen</th>
                <th>Laundry</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($room = $rooms->fetch_assoc()): ?>
                <tr>
                  <td class="image-cell">
                    <?php if (!empty($room['image_path']) && file_exists($room['image_path'])): ?>
                      <img src="<?php echo $room['image_path']; ?>" alt="Room <?php echo $room['room_number']; ?>" class="room-image" onclick="openModal('<?php echo $room['image_path']; ?>')">
                    <?php else: ?>
                      <div class="no-image">No Image</div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" class="image-upload-form">
                      <input type="hidden" name="room_number" value="<?php echo $room['room_number']; ?>">
                      <input type="file" name="room_image" accept="image/*" style="font-size: 12px; width: 120px;">
                      <button type="submit" name="update_room_image" class="update-btn" style="padding: 5px 8px; font-size: 12px;">
                        <i class="fas fa-upload"></i> Upload
                      </button>
                    </form>
                  </td>
                  <td><strong><?php echo $room['room_number']; ?></strong></td>
                  <td><?php echo $room['bed_type']; ?></td>
                  <td><?php echo $room['bathroom_type']; ?></td>
                  <td>
                    <form method="POST" style="display: inline;">
                      <input type="hidden" name="room_number" value="<?php echo $room['room_number']; ?>">
                      <input type="number" name="monthly_rent" value="<?php echo $room['monthly_rent']; ?>" step="0.01" min="0" style="width: 120px; padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px;">
                  </td>
                  <td><?php echo $room['cooling_type']; ?></td>
                  <td><?php echo $room['wifi_access']; ?></td>
                  <td><?php echo $room['kitchen_access']; ?></td>
                  <td><?php echo $room['laundry_access']; ?></td>
                  <td>
                    <select name="status" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px;">
                      <option value="Available" <?php echo $room['status'] == 'Available' ? 'selected' : ''; ?>>Available</option>
                      <option value="Occupied" <?php echo $room['status'] == 'Occupied' ? 'selected' : ''; ?>>Occupied</option>
                      <option value="Reserved" <?php echo $room['status'] == 'Reserved' ? 'selected' : ''; ?>>Reserved</option>
                      <option value="Maintenance" <?php echo $room['status'] == 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                    </select>
                  </td>
                  <td>
                      <form method="POST" style="display: inline;">
                          <input type="hidden" name="room_number" value="<?php echo $room['room_number']; ?>">
                          <button type="submit" name="update_room" class="update-btn">
                              <i class="fas fa-save"></i> Update
                          </button>
                      </form>
                      <form method="POST" style="display: inline; margin-left: 5px;" onsubmit="return confirm('Are you sure you want to delete room <?php echo $room['room_number']; ?>? This action cannot be undone.')">
                          <input type="hidden" name="room_number" value="<?php echo $room['room_number']; ?>">
                          <button type="submit" name="delete_room" class="delete-btn">
                              <i class="fas fa-trash"></i> Delete
                          </button>
                      </form>
                  </td>
              <?php endwhile; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="empty-state">
            <i class="fa-solid fa-bed"></i>
            <h3>No Rooms Found</h3>
            <p>No rooms have been added to the system yet.</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Add New Room Form -->
      <div class="form-section">
        <h3><i class="fas fa-plus-circle"></i> Add New Room</h3>
        <form method="POST" enctype="multipart/form-data">
          <div class="form-grid">
            <div class="form-group">
              <label class="required">Room Number:</label>
              <input type="text" name="room_number" required placeholder="e.g., 101">
            </div>
            <div class="form-group">
              <label class="required">Bed Type:</label>
              <select name="bed_type" required>
                <option value="Single Bed">Single Bed</option>
                <option value="Bunk Bed">Bunk Bed</option>
                <option value="Double Bed">Double Bed</option>
              </select>
            </div>
            <div class="form-group">
              <label class="required">Bathroom:</label>
              <select name="bathroom_type" required>
                <option value="Private">Private</option>
                <option value="Shared">Shared</option>
              </select>
            </div>
            <div class="form-group">
              <label class="required">Monthly Rent:</label>
              <input type="number" name="monthly_rent" step="0.01" min="0" required placeholder="0.00">
            </div>
            <div class="form-group">
              <label class="required">Cooling Type:</label>
              <select name="cooling_type" required>
                <option value="A/C">A/C</option>
                <option value="Fan">Fan</option>
              </select>
            </div>
            <div class="form-group">
              <label class="required">Wi-Fi Access:</label>
              <select name="wifi_access" required>
                <option value="Available">Available</option>
                <option value="None">None</option>
              </select>
            </div>
            <div class="form-group">
              <label class="required">Kitchen Access:</label>
              <select name="kitchen_access" required>
                <option value="Private">Private</option>
                <option value="Shared">Shared</option>
              </select>
            </div>
            <div class="form-group">
              <label class="required">Laundry Access:</label>
              <select name="laundry_access" required>
                <option value="Private">Private</option>
                <option value="Shared">Shared</option>
              </select>
            </div>
            <div class="form-group full-width">
              <label>Room Image:</label>
              <input type="file" name="room_image" accept="image/*">
              <small>Supported formats: JPG, JPEG, PNG, GIF, WEBP</small>
            </div>
            <div class="form-group full-width">
              <button type="submit" name="add_room" class="btn">
                <i class="fas fa-plus"></i> Add Room
              </button>
            </div>
          </div>
        </form>
      </div>
    </main>
  </div>

  <!-- Image Modal -->
  <div id="imageModal" class="image-modal">
    <span class="close-modal" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImage">
  </div>

  <?php include 'ad-footer.php'; ?>

  <script>
    function openModal(imageSrc) {
      document.getElementById('imageModal').style.display = 'block';
      document.getElementById('modalImage').src = imageSrc;
    }

    function closeModal() {
      document.getElementById('imageModal').style.display = 'none';
    }

    // Close modal when clicking outside the image
    document.getElementById('imageModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeModal();
      }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeModal();
      }
    });
  </script>
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
        margin-bottom: 25px;
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
    
    .success-message {
        background: #e8f5e8;
        color: #2e7d32;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        border-left: 4px solid #4CAF50;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .error-message {
        background: #f8d7da;
        color: #721c24;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        border-left: 4px solid #dc3545;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .success-message i,
    .error-message i {
        font-size: 1.2rem;
    }
    
    .table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 25px;
        overflow-x: auto;
        margin-bottom: 30px;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 0.95rem;
    }
    
    .data-table th {
        background-color: #4263eb;
        color: white;
        padding: 15px 12px;
        text-align: left;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .data-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
        color: #333;
    }
    
    .data-table tr:hover {
        background-color: #f8f9ff;
        transition: background-color 0.2s ease;
    }
    
    .data-table tr:last-child td {
        border-bottom: none;
    }
    
    /* Room Image Styles */
    .room-image {
        width: 80px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        transition: transform 0.3s ease;
        border: 2px solid #e9ecef;
    }
    
    .room-image:hover {
        transform: scale(1.1);
        border-color: #4263eb;
    }
    
    .no-image {
        width: 80px;
        height: 60px;
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 11px;
        text-align: center;
        font-weight: 500;
    }
    
    .image-cell {
        min-width: 150px;
    }
    
    .image-upload-form {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        flex-wrap: wrap;
    }
    
    .image-upload-form input[type="file"] {
        font-size: 11px;
        width: 100px;
        padding: 4px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    /* Form Elements */
    input[type="text"],
    input[type="number"],
    input[type="file"],
    select {
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 0.9rem;
        transition: border-color 0.3s ease;
        width: 100%;
    }
    
    input[type="text"]:focus,
    input[type="number"]:focus,
    select:focus {
        outline: none;
        border-color: #4263eb;
        box-shadow: 0 0 0 2px rgba(66, 99, 235, 0.1);
    }
    
    .update-btn {
        background: #4263eb;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .update-btn:hover {
        background: #3451d8;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(66, 99, 235, 0.3);
    }
    
    /* Add Room Form Section */
    .form-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 25px;
        margin-bottom: 30px;
    }
    
    .form-section h3 {
        color: #4263eb;
        font-size: 1.4rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
    }
    
    .form-group.full-width {
        grid-column: 1 / -1;
    }
    
    .form-group label {
        font-weight: 600;
        margin-bottom: 8px;
        color: #495057;
        font-size: 0.9rem;
    }
    
    .form-group label.required::after {
        content: " *";
        color: #dc3545;
    }
    
    .form-group small {
        color: #6c757d;
        font-size: 0.8rem;
        margin-top: 5px;
    }
    
    .btn {
        background: #28a745;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        justify-content: center;
    }
    
    .btn:hover {
        background: #218838;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }
    
    .empty-state i {
        font-size: 64px;
        color: #dee2e6;
        margin-bottom: 20px;
    }
    
    .empty-state h3 {
        font-size: 1.5rem;
        margin-bottom: 10px;
        color: #495057;
        font-weight: 600;
    }
    
    .empty-state p {
        font-size: 1.1rem;
        color: #6c757d;
    }
    
    /* Image Modal Styles */
    .image-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.9);
        animation: fadeIn 0.3s ease;
    }
    
    .modal-content {
        margin: auto;
        display: block;
        width: 80%;
        max-width: 700px;
        max-height: 80%;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }
    
    .close-modal {
        position: absolute;
        top: 20px;
        right: 35px;
        color: #fff;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.3s ease;
    }
    
    .close-modal:hover {
        color: #4263eb;
    }
    
    /* Status Badges */
    .data-table select {
        padding: 8px 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 0.85rem;
        background: white;
    }
    
    /* Responsive Design */
    @media (max-width: 1200px) {
        .table-container {
            overflow-x: auto;
        }
        
        .data-table {
            min-width: 1200px;
        }
    }
    
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
        
        .table-container,
        .form-section {
            padding: 15px;
            border-radius: 8px;
        }
        
        .data-table {
            font-size: 0.85rem;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px 8px;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .update-btn,
        .btn {
            padding: 8px 16px;
            font-size: 0.9rem;
        }
        
        .modal-content {
            width: 95%;
        }
        
        .close-modal {
            top: 10px;
            right: 20px;
            font-size: 30px;
        }
    }
    
    @media (max-width: 480px) {
        .content-header h1 {
            font-size: 1.4rem;
        }
        
        .content-header p {
            font-size: 1rem;
        }
        
        .empty-state i {
            font-size: 48px;
        }
        
        .empty-state h3 {
            font-size: 1.3rem;
        }
        
        .success-message,
        .error-message {
            padding: 12px 15px;
            font-size: 0.9rem;
        }
        
        .image-upload-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .image-upload-form input[type="file"] {
            width: 100%;
        }
    }
    
    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    .data-table tbody tr {
        animation: fadeIn 0.3s ease;
    }
    
    .data-table tbody tr:nth-child(even) {
        background-color: #fafafa;
    }
    
    .data-table tbody tr:nth-child(even):hover {
        background-color: #f0f2ff;
    }
    
    /* Button icons */
    .update-btn i,
    .btn i {
        font-size: 0.9rem;
    }
    
    /* Custom scrollbar for table */
    .table-container::-webkit-scrollbar {
        height: 8px;
    }
    
    .table-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .table-container::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    
    .table-container::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    .delete-btn {
        background: #dc3545;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .delete-btn:hover {
        background: #c82333;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    }
</style>

</body>
</html>