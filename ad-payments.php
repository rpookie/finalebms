<?php
session_start();
include 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle payment approval/rejection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $payment_id = intval($_POST['payment_id']);
    $action = $_POST['action'];
    $admin_notes = $conn->real_escape_string($_POST['admin_notes'] ?? '');
    
    $stmt = $conn->prepare("UPDATE payments SET status = ?, admin_notes = ? WHERE id = ?");
    $stmt->bind_param("ssi", $action, $admin_notes, $payment_id);
    
    if ($stmt->execute()) {
        $success = "Payment {$action} successfully!";
    }
}

// Get all payments
$payments = $conn->query("SELECT p.*, u.fname, u.lname, u.contact, u.email FROM payments p JOIN users u ON p.boarder_id = u.boarder_id ORDER BY p.payment_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Payments | eBMS Admin</title>
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
        margin-top: 30px;
        margin-bottom: 8px;
        font-weight: 700;
    }

    .content-header p {
        font-size: 1.1em;
        color: #6c757d;
        margin: 0;
    }

    /* Success Message */
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

    .success-message i {
        font-size: 1.2em;
    }

    /* Table Container */
    .table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    /* Data Table */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95em;
    }

    .data-table thead {
        background: #4263eb;
        color: white;
    }

    .data-table th {
        padding: 18px 15px;
        text-align: left;
        font-weight: 600;
        font-size: 0.95em;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .data-table td {
        padding: 18px 15px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
        color: #2c3e50;
    }

    .data-table tbody tr {
        transition: background-color 0.3s ease;
    }

    .data-table tbody tr:hover {
        background: #f8f9fa;
    }

    .data-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Status Badges */
    .status-pending {
        background: #fff3cd;
        color: #856404;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
        display: inline-block;
    }

    .status-approved {
        background: #d4edda;
        color: #155724;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
        display: inline-block;
    }

    .status-rejected {
        background: #f8d7da;
        color: #721c24;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
        display: inline-block;
    }

    /* Action Forms */
    .payment-action-form {
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-width: 200px;
    }

    .payment-action-form textarea {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 0.9em;
        resize: vertical;
        min-height: 70px;
        font-family: inherit;
        transition: border-color 0.3s ease;
    }

    .payment-action-form textarea:focus {
        outline: none;
        border-color: #4263eb;
        box-shadow: 0 0 0 2px rgba(66, 99, 235, 0.1);
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-approve, .btn-reject {
        border: none;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 0.85em;
        cursor: pointer;
        flex: 1;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .btn-approve {
        background: #28a745;
        color: white;
    }

    .btn-approve:hover {
        background: #218838;
        transform: translateY(-1px);
    }

    .btn-reject {
        background: #dc3545;
        color: white;
    }

    .btn-reject:hover {
        background: #c82333;
        transform: translateY(-1px);
    }

    /* View Button */
    .view-btn {
        background: #4263eb;
        color: white;
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 6px;
        font-size: 0.85em;
        font-weight: 500;
        transition: background-color 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .view-btn:hover {
        background: #3b5bdb;
        text-decoration: none;
        color: white;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
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

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.9);
        backdrop-filter: blur(5px);
    }

    .modal-content {
        position: relative;
        margin: 40px auto;
        padding: 20px;
        width: 90%;
        max-width: 800px;
        text-align: center;
        animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .close {
        position: absolute;
        top: 15px;
        right: 25px;
        color: #fff;
        font-size: 35px;
        font-weight: bold;
        cursor: pointer;
        z-index: 1001;
        transition: color 0.3s ease;
    }

    .close:hover {
        color: #ccc;
    }

    #receiptImage {
        max-width: 100%;
        max-height: 80vh;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }

    /* Boarder Info */
    .boarder-info {
        line-height: 1.4;
    }

    .boarder-info strong {
        display: block;
        color: #2c3e50;
        margin-bottom: 2px;
    }

    .boarder-info small {
        color: #6c757d;
        font-size: 0.85em;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .main-content {
            margin-left: 0;
            padding: 20px;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .data-table {
            min-width: 1000px;
        }
    }

    @media (max-width: 768px) {
        .content-header h1 {
            font-size: 1.8em;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px 8px;
            font-size: 0.9em;
        }
        
        .action-buttons {
            flex-direction: column;
            gap: 5px;
        }
        
        .btn-approve, .btn-reject {
            padding: 10px;
        }
        
        .payment-action-form {
            min-width: 150px;
        }
    }

    @media (max-width: 480px) {
        .main-content {
            padding: 15px;
        }
        
        .content-header h1 {
            font-size: 1.6em;
        }
        
        .success-message {
            padding: 12px 15px;
            font-size: 0.9em;
        }
        
        .empty-state {
            padding: 40px 15px;
        }
        
        .empty-state i {
            font-size: 3em;
        }
    }
  </style>
</head>
<body>
  <?php include 'ad-header.php'; ?>

  <div class="main-container">
    <?php include 'ad-sidebar.php'; ?>

    <main class="main-content" id="mainContent">
      <div class="content-header">
        <h1>Manage Payments</h1>
        <p>Review and verify payment submissions</p>
      </div>

      <?php if (isset($success)): ?>
        <div class="success-message">
          <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
      <?php endif; ?>

      <div class="table-container">
        <?php if ($payments->num_rows > 0): ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Boarder</th>
                <th>Amount</th>
                <th>Month Covered</th>
                <th>Payment Method</th>
                <th>Reference</th>
                <th>Date</th>
                <th>Receipt</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($payment = $payments->fetch_assoc()): ?>
                <tr>
                  <td>
                    <div class="boarder-info">
                      <strong><?php echo htmlspecialchars($payment['fname'] . ' ' . $payment['lname']); ?></strong>
                      <small><?php echo htmlspecialchars($payment['contact']); ?></small>
                    </div>
                  </td>
                  <td><strong>₱<?php echo number_format($payment['amount'], 2); ?></strong></td>
                  <td><?php echo htmlspecialchars($payment['month_covered']); ?></td>
                  <td><?php echo htmlspecialchars($payment['mode_of_payment']); ?></td>
                  <td><code><?php echo htmlspecialchars($payment['reference_number']); ?></code></td>
                  <td><?php echo date('M j, Y', strtotime($payment['payment_date'])); ?></td>
                  <td>
                    <?php if (!empty($payment['receipt_image'])): ?>
                      <?php
                      // Check if receipt_image contains full path or just filename
                      $receipt_path = $payment['receipt_image'];
                      if (strpos($receipt_path, 'uploads/') === false) {
                          $receipt_path = 'uploads/receipts/' . $payment['receipt_image'];
                      }
                      ?>
                      <a href="<?php echo $receipt_path; ?>" target="_blank" class="view-btn" onclick="return openReceiptModal('<?php echo $receipt_path; ?>')">
                        <i class="fas fa-eye"></i> View
                      </a>
                    <?php else: ?>
                      <span style="color: #6c757d; font-style: italic;">N/A</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="status-<?php echo $payment['status']; ?>">
                      <?php echo ucfirst($payment['status']); ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($payment['status'] == 'pending'): ?>
                      <form method="POST" class="payment-action-form">
                        <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                        <textarea name="admin_notes" placeholder="Notes (optional)" rows="2"></textarea>
                        <div class="action-buttons">
                          <button type="submit" name="action" value="approved" class="btn-approve">
                            <i class="fas fa-check"></i> Approve
                          </button>
                          <button type="submit" name="action" value="rejected" class="btn-reject">
                            <i class="fas fa-times"></i> Reject
                          </button>
                        </div>
                      </form>
                    <?php else: ?>
                      <span style="color: #6c757d; font-style: italic;">Processed</span>
                      <?php if (!empty($payment['admin_notes'])): ?>
                        <br>
                        <small title="<?php echo htmlspecialchars($payment['admin_notes']); ?>" style="cursor: help;">📝</small>
                      <?php endif; ?>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="empty-state">
            <i class="fa-solid fa-receipt"></i>
            <h3>No Payment Records</h3>
            <p>No payment records found in the system.</p>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <!-- Receipt Modal -->
  <div id="receiptModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <img id="receiptImage" src="" alt="Payment Receipt">
    </div>
  </div>

  <?php include 'ad-footer.php'; ?>

  <script>
    // Receipt modal functionality
    function openReceiptModal(imageSrc) {
      event.preventDefault();
      const modal = document.getElementById('receiptModal');
      const modalImg = document.getElementById('receiptImage');
      modal.style.display = 'block';
      modalImg.src = imageSrc;
      return false;
    }

    // Close modal
    document.querySelector('.close').addEventListener('click', function() {
      document.getElementById('receiptModal').style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
      const modal = document.getElementById('receiptModal');
      if (event.target === modal) {
        modal.style.display = 'none';
      }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        document.getElementById('receiptModal').style.display = 'none';
      }
    });
  </script>
</body>
</html>