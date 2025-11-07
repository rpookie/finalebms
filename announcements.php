<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle new announcement creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_announcement'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $type = $conn->real_escape_string($_POST['type']);
    $created_by = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("INSERT INTO announcements (title, content, type, created_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $content, $type, $created_by);
    
    if ($stmt->execute()) {
        $success = "Announcement posted successfully!";
    } else {
        $error = "Error posting announcement: " . $conn->error;
    }
}

// Get all announcements
$announcements_query = $conn->query("SELECT a.*, u.fname, u.lname FROM announcements a LEFT JOIN users u ON a.created_by = u.boarder_id ORDER BY a.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Announcements | eBMS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Titan+One&family=Tomorrow:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f8f9fa;
        color: #333;
        line-height: 1.6;
    }

    .main-container {
        display: flex;
        min-height: 100vh;
    }

    .main-content {
        flex: 1;
        padding: 30px;
        margin-left: 250px;
        transition: margin-left 0.3s ease;
        background: #f8f9fa;
    }

    .content-header {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e9ecef;
    }

    .content-header h1 {
        font-family: 'Tomorrow', sans-serif;
        font-size: 2.2em;
        color: #2c3e50;
        margin-bottom: 8px;
        font-weight: 700;
    }

    .content-header p {
        font-size: 1.1em;
        color: #6c757d;
        margin: 0;
    }

    /* Success and Error Messages */
    .success-message {
        background: #d4edda;
        color: #155724;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        border: 1px solid #c3e6cb;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .error-message {
        background: #f8d7da;
        color: #721c24;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        border: 1px solid #f5c6cb;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Payment Information Card */
    .payment-info-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        border-left: 4px solid #28a745;
    }

    .payment-info-card h3 {
        color: #28a745;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.3em;
        font-weight: 600;
    }

    .payment-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .payment-method {
        text-align: center;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        transition: transform 0.3s ease;
    }

    .payment-method:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .payment-method h4 {
        color: #2c3e50;
        margin-bottom: 10px;
        font-size: 1.1em;
        font-weight: 600;
    }

    .payment-details {
        font-weight: bold;
        color: #416cec;
        margin-bottom: 5px;
        font-size: 1.1em;
    }

    .payment-name {
        color: #666;
        font-size: 0.9em;
    }

    .qr-code {
        text-align: center;
        padding: 20px;
    }

    .qr-placeholder {
        width: 120px;
        height: 120px;
        background: #f8f9fa;
        border: 2px dashed #ddd;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #666;
        margin: 0 auto;
        transition: all 0.3s ease;
    }

    .qr-placeholder:hover {
        border-color: #28a745;
        color: #28a745;
    }

    .qr-placeholder i {
        font-size: 2em;
        margin-bottom: 10px;
    }

    /* Announcements List */
    .announcements-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-bottom: 30px;
    }

    .announcement-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border-left: 4px solid #416cec;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .announcement-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    .announcement-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
        gap: 15px;
    }

    .announcement-header h3 {
        color: #2c3e50;
        margin: 0;
        flex: 1;
        font-size: 1.3em;
        font-weight: 600;
        line-height: 1.3;
    }

    .announcement-date {
        color: #6c757d;
        font-size: 0.9em;
        white-space: nowrap;
        font-weight: 500;
    }

    .announcement-content {
        color: #333;
        line-height: 1.6;
        margin-bottom: 15px;
        font-size: 1em;
    }

    .announcement-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
        font-size: 0.9em;
    }

    .announcement-author {
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .announcement-type {
        background: #e8f4ff;
        color: #416cec;
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 0.8em;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Form Section */
    .form-section {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-top: 30px;
    }

    .form-section h3 {
        color: #2c3e50;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.3em;
        font-weight: 600;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
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
        margin-bottom: 8px;
        font-weight: 600;
        color: #495057;
    }

    .form-group label.required::after {
        content: " *";
        color: #dc3545;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.3s ease;
        background-color: #fff;
        font-family: inherit;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #4263eb;
        box-shadow: 0 0 0 3px rgba(66, 99, 235, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }

    .btn {
        background: linear-gradient(135deg, #4263eb, #3b5bdb);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 14px 25px;
        font-size: 1em;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn:hover {
        background: linear-gradient(135deg, #3b5bdb, #4263eb);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(66, 99, 235, 0.3);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .empty-state i {
        font-size: 4em;
        color: #dee2e6;
        margin-bottom: 20px;
        display: block;
    }

    .empty-state h3 {
        color: #6c757d;
        margin-bottom: 10px;
        font-size: 1.3em;
    }

    .empty-state p {
        font-size: 1em;
        margin: 0;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .main-content {
            margin-left: 0;
            padding: 20px;
        }
    }

    @media (max-width: 768px) {
        .content-header h1 {
            font-size: 1.8em;
        }
        
        .announcement-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .announcement-date {
            margin-top: 5px;
        }
        
        .announcement-footer {
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }
        
        .payment-grid {
            grid-template-columns: 1fr;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .payment-info-card,
        .announcement-card,
        .form-section {
            padding: 20px;
        }
    }

    @media (max-width: 480px) {
        .main-content {
            padding: 15px;
        }
        
        .content-header h1 {
            font-size: 1.6em;
        }
        
        .empty-state {
            padding: 40px 15px;
        }
        
        .empty-state i {
            font-size: 3em;
        }
        
        .qr-placeholder {
            width: 100px;
            height: 100px;
        }
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
        <h1>Announcements</h1>
        <p>Stay updated with the latest news and information</p>
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

      <!-- Payment Information Card -->
      <div class="payment-info-card">
        <h3><i class="fa-solid fa-qrcode"></i> Payment Information</h3>
        <div class="payment-grid">
          <div class="payment-method">
            <h4>GCash</h4>
            <p class="payment-details">0917-123-4567</p>
            <p class="payment-name">eBMS Admin</p>
          </div>
          <div class="payment-method">
            <h4>Bank Transfer</h4>
            <p class="payment-details">BPI: 1234-5678-99</p>
            <p class="payment-name">eBMS Admin</p>
          </div>
          <div class="qr-code">
            <h4>QR Code</h4>
            <div class="qr-placeholder">
              <i class="fa-solid fa-qrcode"></i>
              <span>Scan to Pay</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Announcements List -->
      <div class="announcements-list">
        <?php if ($announcements_query->num_rows > 0): ?>
          <?php while ($announcement = $announcements_query->fetch_assoc()): ?>
            <div class="announcement-card">
              <div class="announcement-header">
                <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
                <span class="announcement-date">
                  <?php echo date('M j, Y g:i A', strtotime($announcement['created_at'])); ?>
                </span>
              </div>
              
              <div class="announcement-content">
                <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
              </div>
              
              <div class="announcement-footer">
                <span class="announcement-author">
                  <i class="fa-solid fa-user"></i>
                  Posted by: <?php echo $announcement['fname'] ? htmlspecialchars($announcement['fname'] . ' ' . $announcement['lname']) : 'Administrator'; ?>
                </span>
                <span class="announcement-type">
                  <?php echo ucfirst($announcement['type']); ?>
                </span>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="empty-state">
            <i class="fa-solid fa-bullhorn"></i>
            <h3>No Announcements</h3>
            <p>No announcements have been posted yet.</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Admin Only: Add Announcement Form -->
      <?php if ($_SESSION['role'] == 'admin'): ?>
        <div class="form-section">
          <h3><i class="fas fa-plus-circle"></i> Add New Announcement</h3>
          <form method="POST">
            <div class="form-grid">
              <div class="form-group">
                <label class="required">Title:</label>
                <input type="text" name="title" required placeholder="Enter announcement title">
              </div>
              <div class="form-group">
                <label class="required">Type:</label>
                <select name="type" required>
                  <option value="general">General</option>
                  <option value="payment">Payment</option>
                  <option value="maintenance">Maintenance</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>
              <div class="form-group full-width">
                <label class="required">Content:</label>
                <textarea name="content" rows="4" required placeholder="Enter announcement content"></textarea>
              </div>
              <div class="form-group full-width">
                <button type="submit" name="add_announcement" class="btn">
                  <i class="fas fa-bullhorn"></i> Post Announcement
                </button>
              </div>
            </div>
          </form>
        </div>
      <?php endif; ?>
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