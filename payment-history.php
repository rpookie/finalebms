<?php
session_start();
include 'db.php';

// Check if user is logged in and is a boarder
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'boarder') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Get payment history
$payments_query = $conn->prepare("SELECT * FROM payments WHERE boarder_id = ? ORDER BY payment_date DESC");
$payments_query->bind_param("s", $user['boarder_id']);
$payments_query->execute();
$payments_result = $payments_query->get_result();

// Calculate totals
$total_paid = 0;
$total_rent = 0; // This would be calculated based on stay duration
while ($payment = $payments_result->fetch_assoc()) {
    if ($payment['status'] == 'approved') {
        $total_paid += $payment['amount'];
    }
}
// Reset pointer for later use
$payments_result->data_seek(0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment History | eBMS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
        background: #FFF8F0;
        color: #333;
        line-height: 1;
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
    }
    
    .content-header {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }
    
    .content-header h1 {
        color: #4263eb;
        font-size: 2rem;
        margin-bottom: 8px;
    }
    
    .content-header p {
        color: #666;
        font-size: 1.1rem;
    }
    
    .info-boxes {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .info-box {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .info-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .info-box strong {
        color: #555;
        font-size: 1rem;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .info-box span {
        color: #4263eb;
        font-size: 1.8rem;
        font-weight: bold;
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
    }
    
    .data-table th {
        background-color: #4263eb;
        color: white;
        padding: 15px;
        text-align: left;
        font-weight: 600;
    }
    
    .data-table td {
        padding: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .data-table tr:hover {
        background-color: #f8f9ff;
    }
    
    .status-approved {
        background-color: #d4edda;
        color: #155724;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .status-pending {
        background-color: #fff3cd;
        color: #856404;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .status-rejected {
        background-color: #f8d7da;
        color: #721c24;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .view-btn {
        background-color: #4263eb;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background 0.3s;
    }
    
    .view-btn:hover {
        background-color: #3451d8;
    }
    
    .btn {
        display: inline-block;
        background-color: #4263eb;
        color: white;
        padding: 12px 24px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        transition: background 0.3s;
        border: none;
        cursor: pointer;
    }
    
    .btn:hover {
        background-color: #3451d8;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .main-container {
            flex-direction: column;
        }
        
        .main-content {
            margin-left: 0;
            padding: 20px 15px;
        }
        
        .info-boxes {
            grid-template-columns: 1fr;
        }
        
        .data-table {
            font-size: 0.9rem;
        }
        
        .data-table th,
        .data-table td {
            padding: 10px 8px;
        }
    }
    
    @media (max-width: 480px) {
        .content-header h1 {
            font-size: 1.6rem;
        }
        
        .info-box span {
            font-size: 1.5rem;
        }
    }
  </style>
</head>
<body>
  <?php include 'bd-header.php'; ?>

  <div class="main-container">
    <?php include 'bd-sidebar.php'; ?>

    <main class="main-content" id="mainContent">
      <div class="content-header">
        <h1>Payment History</h1>
        <p>View your payment records and status</p>
      </div>

      <!-- Summary Cards -->
      <div class="info-boxes">
        <div class="info-box">
          <strong><i class="fa-solid fa-money-bill-wave"></i> Total Paid:</strong>
          <span>₱<?php echo number_format($total_paid, 2); ?></span>
        </div>
        <div class="info-box">
          <strong><i class="fa-solid fa-calendar-check"></i> Approved Payments:</strong>
          <span>
            <?php
            $approved_count = $conn->query("SELECT COUNT(*) as count FROM payments WHERE boarder_id = '{$user['boarder_id']}' AND status = 'approved'")->fetch_assoc()['count'];
            echo $approved_count;
            ?>
          </span>
        </div>
        <div class="info-box">
          <strong><i class="fa-solid fa-clock"></i> Pending Payments:</strong>
          <span>
            <?php
            $pending_count = $conn->query("SELECT COUNT(*) as count FROM payments WHERE boarder_id = '{$user['boarder_id']}' AND status = 'pending'")->fetch_assoc()['count'];
            echo $pending_count;
            ?>
          </span>
        </div>
      </div>

      <!-- Payment History Table -->
      <div class="table-container" style="margin-top: 30px;">
        <h3 style="color: #4263eb; margin-bottom: 20px;">Payment Records</h3>
        
        <?php if ($payments_result->num_rows > 0): ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Month Covered</th>
                <th>Amount</th>
                <th>Mode of Payment</th>
                <th>Reference</th>
                <th>Status</th>
                <th>Receipt</th>
                <th>Admin Notes</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($payment = $payments_result->fetch_assoc()): ?>
                <tr>
                  <td><?php echo date('M j, Y', strtotime($payment['payment_date'])); ?></td>
                  <td><?php echo $payment['month_covered']; ?></td>
                  <td>₱<?php echo number_format($payment['amount'], 2); ?></td>
                  <td><?php echo $payment['mode_of_payment']; ?></td>
                  <td><?php echo $payment['reference_number']; ?></td>
                  <td>
                    <span class="status-<?php echo $payment['status']; ?>">
                      <?php echo ucfirst($payment['status']); ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($payment['receipt_image']): ?>
                      <a href="uploads/receipts/<?php echo $payment['receipt_image']; ?>" target="_blank" class="view-btn" style="padding: 5px 10px; font-size: 0.8em;">View</a>
                    <?php else: ?>
                      N/A
                    <?php endif; ?>
                  </td>
                  <td><?php echo $payment['admin_notes'] ?: '—'; ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div style="text-align: center; padding: 40px; color: #666;">
            <i class="fa-solid fa-receipt" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
            <p>No payment records found.</p>
            <a href="payment.php" class="btn" style="margin-top: 15px;">Make Your First Payment</a>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <?php include 'bd-footer.php'; ?>
</body>
</html>