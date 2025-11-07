<?php
session_start();
include 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get dashboard statistics
$total_boarders = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'boarder' AND status = 'approved'")->fetch_assoc()['count'];
$pending_reservations = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'pending'")->fetch_assoc()['count'];
$pending_payments = $conn->query("SELECT COUNT(*) as count FROM payments WHERE status = 'pending'")->fetch_assoc()['count'];
$active_maintenance = $conn->query("SELECT COUNT(*) as count FROM maintenance WHERE status IN ('not started', 'ongoing')")->fetch_assoc()['count'];
$total_rooms = $conn->query("SELECT COUNT(*) as count FROM rooms")->fetch_assoc()['count'];
$occupied_rooms = $conn->query("SELECT COUNT(*) as count FROM rooms WHERE status = 'Occupied'")->fetch_assoc()['count'];
$available_rooms = $conn->query("SELECT COUNT(*) as count FROM rooms WHERE status = 'Available'")->fetch_assoc()['count'];

// Get recent activities
$recent_payments = $conn->query("SELECT p.*, u.fname, u.lname, u.room_number FROM payments p JOIN users u ON p.boarder_id = u.id ORDER BY p.payment_date DESC LIMIT 5");
$recent_reservations = $conn->query("SELECT * FROM users WHERE status = 'pending' ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - eBMS</title>
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

    /* Statistics Cards */
    .info-boxes {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .info-box {
        background: white;
        padding: 25px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        text-align: center;
        border-top: 4px solid #4263eb;
        transition: transform 0.3s ease;
    }

    .info-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    .info-box i {
        font-size: 2em;
        color: #4263eb;
        margin-bottom: 15px;
        display: block;
    }

    .info-box strong {
        display: block;
        font-size: 0.9em;
        color: #6c757d;
        margin-bottom: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-box span {
        font-size: 2.2em;
        font-weight: 700;
        color: #2c3e50;
        display: block;
    }

    .info-box .sub-text {
        font-size: 0.9em;
        color: #6c757d;
        margin-top: 5px;
    }

    /* Quick Actions */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin: 30px 0;
    }

    .btn {
        background: #4263eb;
        color: white;
        text-decoration: none;
        padding: 15px 20px;
        border-radius: 8px;
        text-align: center;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border: none;
        cursor: pointer;
        font-size: 0.95em;
    }

    .btn:hover {
        background: #3b5bdb;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(66, 99, 235, 0.3);
    }

    /* Dashboard Grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-top: 20px;
    }

    .dashboard-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: 1px solid #e9ecef;
    }

    .dashboard-card h3 {
        color: #2c3e50;
        margin-bottom: 20px;
        font-size: 1.3em;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .dashboard-card h3 a {
        font-size: 0.9em;
        color: #4263eb;
        text-decoration: none;
        font-weight: 500;
    }

    .dashboard-card h3 a:hover {
        color: #3b5bdb;
        text-decoration: underline;
    }

    /* Activity Items */
    .activity-item {
        background: white;
        padding: 20px;
        margin-bottom: 15px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .activity-item:hover {
        border-color: #4263eb;
        box-shadow: 0 2px 8px rgba(66, 99, 235, 0.1);
    }

    .activity-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .activity-header strong {
        color: #2c3e50;
        font-weight: 600;
        font-size: 1.1em;
    }

    .activity-header small {
        color: #6c757d;
        font-size: 0.85em;
    }

    .activity-details {
        color: #6c757d;
        font-size: 0.9em;
        margin-bottom: 8px;
        line-height: 1.5;
    }

    .activity-actions {
        margin-top: 12px;
        text-align: right;
    }

    .view-btn {
        background: #4263eb;
        color: white;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 0.85em;
        font-weight: 500;
        transition: background-color 0.3s ease;
        display: inline-block;
        border: none;
        cursor: pointer;
    }

    .view-btn:hover {
        background: #3b5bdb;
    }

    /* Status Badges */
    .status-pending {
        background: #fff3cd;
        color: #856404;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8em;
        font-weight: 600;
    }

    .status-approved {
        background: #d1edff;
        color: #0c5460;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8em;
        font-weight: 600;
    }

    .status-paid {
        background: #d4edda;
        color: #155724;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8em;
        font-weight: 600;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 3em;
        color: #dee2e6;
        margin-bottom: 15px;
        display: block;
    }

    .empty-state p {
        font-size: 1em;
        margin: 0;
    }

    /* Scrollable Containers */
    .scroll-container {
        max-height: 400px;
        overflow-y: auto;
        padding-right: 5px;
    }

    .scroll-container::-webkit-scrollbar {
        width: 4px;
    }

    .scroll-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }

    .scroll-container::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 2px;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .main-content {
            margin-left: 0;
            padding: 20px;
        }
    }

    @media (max-width: 768px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .content-header h1 {
            font-size: 1.8em;
        }
        
        .info-boxes {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .info-box {
            padding: 20px 15px;
        }
        
        .info-box span {
            font-size: 1.8em;
        }
        
        .quick-actions {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .main-content {
            padding: 15px;
        }
        
        .content-header h1 {
            font-size: 1.6em;
        }
        
        .info-boxes {
            grid-template-columns: 1fr 1fr;
        }
        
        .activity-header {
            flex-direction: column;
            gap: 5px;
        }
        
        .dashboard-card {
            padding: 20px 15px;
        }
    }
  </style>
</head>
<body>
  <?php include 'ad-sidebar.php' ?>
  <?php include 'ad-header.php' ?>
  
  <div class="main-container">
    <main class="main-content" id="mainContent">
      <div class="content-header">
        <h1>eBMS Admin</h1>
        <p>Welcome back, Administrator! Here's an overview of your boarding house.</p>
      </div>

      <!-- Statistics Cards -->
      <div class="info-boxes">
        <div class="info-box">
          <i class="fa-solid fa-users"></i>
          <strong>Total Boarders</strong>
          <span><?php echo $total_boarders; ?></span>
        </div>
        <div class="info-box">
          <i class="fa-solid fa-bed"></i>
          <strong>Rooms</strong>
          <span><?php echo $occupied_rooms; ?>/<?php echo $total_rooms; ?></span>
          <div class="sub-text"><?php echo $available_rooms; ?> available</div>
        </div>
        <div class="info-box">
          <i class="fa-solid fa-clock"></i>
          <strong>Pending Reservations</strong>
          <span><?php echo $pending_reservations; ?></span>
        </div>
        <div class="info-box">
          <i class="fa-solid fa-money-bill-wave"></i>
          <strong>Pending Payments</strong>
          <span><?php echo $pending_payments; ?></span>
        </div>
        <div class="info-box">
          <i class="fa-solid fa-tools"></i>
          <strong>Active Maintenance</strong>
          <span><?php echo $active_maintenance; ?></span>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <a href="ad-reservations.php" class="btn">
          <i class="fa-solid fa-user-check"></i> Manage Reservations
        </a>
        <a href="ad-payments.php" class="btn">
          <i class="fa-solid fa-credit-card"></i> Approve Payments
        </a>
        <a href="ad-boarders.php" class="btn">
          <i class="fa-solid fa-list"></i> View Boarders
        </a>
        <a href="announcements.php" class="btn">
          <i class="fa-solid fa-bullhorn"></i> Post Announcement
        </a>
      </div>

      <div class="dashboard-grid">
        <!-- Pending Reservations -->
        <div class="dashboard-card">
          <h3>
            <span>Pending Reservations</span>
            <a href="ad-reservations.php">View All →</a>
          </h3>
          
          <?php if ($recent_reservations->num_rows > 0): ?>
            <div class="scroll-container">
              <?php while ($reservation = $recent_reservations->fetch_assoc()): ?>
                <div class="activity-item">
                  <div class="activity-header">
                    <strong><?php echo htmlspecialchars($reservation['fname'] . ' ' . $reservation['lname']); ?></strong>
                    <small><?php echo date('M j, Y', strtotime($reservation['created_at'])); ?></small>
                  </div>
                  <div class="activity-details">
                    Room: <?php echo htmlspecialchars($reservation['room_number']); ?> | 
                    Contact: <?php echo htmlspecialchars($reservation['contact']); ?>
                  </div>
                  <div class="activity-actions">
                    <a href="ad-reservations.php?action=view&id=<?php echo $reservation['id']; ?>" class="view-btn">Review</a>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>
          <?php else: ?>
            <div class="empty-state">
              <i class="fa-solid fa-check"></i>
              <p>No pending reservations</p>
            </div>
          <?php endif; ?>
        </div>

        <!-- Recent Payments -->
        <div class="dashboard-card">
          <h3>
            <span>Recent Payments</span>
            <a href="ad-payments.php">View All →</a>
          </h3>
          
          <?php if ($recent_payments->num_rows > 0): ?>
            <div class="scroll-container">
              <?php while ($payment = $recent_payments->fetch_assoc()): ?>
                <div class="activity-item">
                  <div class="activity-header">
                    <strong><?php echo htmlspecialchars($payment['fname'] . ' ' . $payment['lname']); ?></strong>
                    <span class="status-<?php echo $payment['status']; ?>">
                      <?php echo ucfirst($payment['status']); ?>
                    </span>
                  </div>
                  <div class="activity-details">
                    <strong>₱<?php echo number_format($payment['amount'], 2); ?></strong> | <?php echo htmlspecialchars($payment['month_covered']); ?>
                  </div>
                  <div class="activity-details">
                    Room: <?php echo htmlspecialchars($payment['room_number']); ?> | 
                    <?php echo htmlspecialchars($payment['mode_of_payment']); ?>: <?php echo htmlspecialchars($payment['reference_number']); ?>
                  </div>
                  <?php if ($payment['status'] == 'pending'): ?>
                    <div class="activity-actions">
                      <a href="ad-payments.php?action=view&id=<?php echo $payment['id']; ?>" class="view-btn">Review</a>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endwhile; ?>
            </div>
          <?php else: ?>
            <div class="empty-state">
              <i class="fa-solid fa-receipt"></i>
              <p>No recent payments</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>

  <?php include 'ad-footer.php'; ?>
</body>
</html>