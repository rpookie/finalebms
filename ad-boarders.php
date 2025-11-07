<?php
session_start();
include 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle boarder deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_boarder'])) {
    $boarder_id = $_POST['boarder_id'];
    
    // Free up the room first
    $user_info = $conn->query("SELECT room_number FROM users WHERE boarder_id = '$boarder_id'")->fetch_assoc();
    if ($user_info && $user_info['room_number']) {
        $conn->query("UPDATE rooms SET status = 'Available' WHERE room_number = '{$user_info['room_number']}'");
    }
    
    // Delete the boarder and related records
    $conn->query("DELETE FROM payments WHERE boarder_id = '$boarder_id'");
    $conn->query("DELETE FROM maintenance WHERE boarder_id = '$boarder_id'");
    $conn->query("DELETE FROM users WHERE boarder_id = '$boarder_id'");
    
    $success = "Boarder deleted successfully!";
}

// Get all boarders
$boarders = $conn->query("SELECT * FROM users WHERE role = 'boarder' AND status = 'approved' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Boarders | eBMS Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Titan+One&family=Tomorrow:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial Black;
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
    }
    
    .content-header p {
        color: #666;
        font-size: 1.1rem;
        font-weight: 400;
    }
    
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
        font-weight: 500;
    }
    
    .success-message i {
        font-size: 1.2rem;
    }
    
    .table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 25px;
        overflow-x: auto;
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
        color: black;
        padding: 15px 12px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: top;
    }
    
    .data-table tr:hover {
        background-color: #f8f9ff;
        transition: background-color 0.2s ease;
    }
    
    .data-table tr:last-child td {
        border-bottom: none;
    }
    
    .delete-btn {
        background: #dc3545;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .delete-btn:hover {
        background: #c82333;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    }
    
    .delete-btn i {
        font-size: 0.8rem;
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
    
    /* Room badge styling */
    .data-table td span {
        background: #4c56a1;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-block;
    }
    
    /* Responsive Design */
    @media (max-width: 1200px) {
        .table-container {
            overflow-x: auto;
        }
        
        .data-table {
            min-width: 1000px;
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
        
        .table-container {
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
        
        .delete-btn {
            padding: 6px 12px;
            font-size: 0.8rem;
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
        
        .success-message {
            padding: 12px 15px;
            font-size: 0.9rem;
        }
    }
    
    /* Animation for table rows */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
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
  </style>
</head>
<body>
  <?php include 'ad-header.php'; ?>
  <?php include 'ad-sidebar.php'; ?>
  <div class="main-container">
    <main class="main-content" id="mainContent">
      <div class="content-header">
        <h1>Manage Boarders</h1>
        <p>View and manage all registered boarders</p>
      </div>

      <?php if (isset($success)): ?>
        <div class="success-message">
          <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
      <?php endif; ?>

      <div class="table-container">
        <?php if ($boarders->num_rows > 0): ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Boarder ID</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Room</th>
                <th>Age</th>
                <th>Guardian</th>
                <th>Date Joined</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($boarder = $boarders->fetch_assoc()): ?>
                <tr>
                  <td><strong><?php echo $boarder['boarder_id']; ?></strong></td>
                  <td><?php echo $boarder['fname'] . ' ' . $boarder['lname']; ?></td>
                  <td><?php echo $boarder['contact']; ?></td>
                  <td><?php echo $boarder['email']; ?></td>
                  <td>
                    <?php if ($boarder['room_number']): ?>
                      <span style="background: #4c56a1; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold;">
                        <?php echo $boarder['room_number']; ?>
                      </span>
                    <?php else: ?>
                      <em>Not assigned</em>
                    <?php endif; ?>
                  </td>
                  <td><?php echo $boarder['age']; ?></td>
                  <td><?php echo $boarder['guardian_fullname'] . ' (' . $boarder['guardian_relationship'] . ')'; ?></td>
                  <td><?php echo date('M j, Y', strtotime($boarder['created_at'])); ?></td>
                  <td>
                    <form method="POST" style="display: inline;">
                      <input type="hidden" name="boarder_id" value="<?php echo $boarder['boarder_id']; ?>">
                      <button type="submit" name="delete_boarder" class="delete-btn" onclick="return confirm('Are you sure you want to delete this boarder? This action cannot be undone.')">
                        <i class="fas fa-trash"></i> Delete
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="empty-state">
            <i class="fa-solid fa-users"></i>
            <h3>No Boarders Registered</h3>
            <p>No boarders have been registered yet.</p>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <?php include 'ad-footer.php'; ?>
</body>
</html>