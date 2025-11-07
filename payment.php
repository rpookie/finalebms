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

// Handle payment submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = floatval($_POST['amount']);
    $month_covered = $conn->real_escape_string($_POST['month_covered']);
    $mode_of_payment = $conn->real_escape_string($_POST['mode_of_payment']);
    $reference_number = $conn->real_escape_string($_POST['reference_number']);
    
    // Handle receipt upload
    $receipt_image = null;
    if (isset($_FILES['receipt_image']) && $_FILES['receipt_image']['error'] == 0) {
        $target_dir = "uploads/receipts/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["receipt_image"]["name"], PATHINFO_EXTENSION));
        $new_filename = "receipt_" . $user['boarder_id'] . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["receipt_image"]["tmp_name"], $target_file)) {
            $receipt_image = $new_filename;
        }
    }
    
    // Insert payment into database
    $stmt = $conn->prepare("INSERT INTO payments (boarder_id, amount, month_covered, mode_of_payment, reference_number, receipt_image, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("sdssss", $user['boarder_id'], $amount, $month_covered, $mode_of_payment, $reference_number, $receipt_image);
    
    if ($stmt->execute()) {
        $success = "Payment submitted successfully! Waiting for admin approval.";
    } else {
        $error = "Error submitting payment. Please try again.";
    }
}

// Get room rent amount
$room_query = $conn->prepare("SELECT monthly_rent FROM rooms WHERE room_number = ?");
$room_query->bind_param("s", $user['room_number']);
$room_query->execute();
$room_result = $room_query->get_result();
$room = $room_result->fetch_assoc();
$monthly_rent = $room ? $room['monthly_rent'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Make Payment | eBMS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* Override existing styles */
    .main-content {
        margin-left: auto ;
        padding: 20px ;
        margin-top: auto;
        min-height: calc(100vh - 70px) ;
        background: #FFF8F0 ;
        transition: margin-left 0.3s ease;
        width: auto ;
        height: auto;
    }

    body {
        font-family: Arial Black;
        background: #FFF8F0 !important;
    }

    /* Payment Page Specific Styles */
    .content-header {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 25px;
    }

    .content-header h1 {
        color: #2c3e50;
        margin: 0 0 8px 0;
        font-size: 28px;
    }

    .content-header p {
        color: #7f8c8d;
        margin: 0;
        font-size: 16px;
    }

    .form-container {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 25px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2c3e50;
        font-size: 14px;
    }

    label.required::after {
        content: " *";
        color: #e74c3c;
    }

    input, select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        font-size: 15px;
        transition: all 0.3s ease;
        background: white;
    }

    input:focus, select:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .btn {
        background: #3498db;
        color: white;
        padding: 14px 35px;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn:hover {
        background: #2980b9;
        transform: translateY(-1px);
    }

    .error {
        color: #e74c3c;
        font-size: 13px;
        margin-top: 5px;
        display: none;
    }

    .success-message {
        background: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 25px;
        border: 1px solid #c3e6cb;
    }

    .error-message {
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 25px;
        border: 1px solid #f5c6cb;
    }

    /* Payment Instructions */
    .payment-instructions {
        background: #e8f4ff;
        padding: 25px;
        border-radius: 8px;
        margin-top: 30px;
        border-left: 4px solid #3498db;
    }

    .payment-instructions h3 {
        color: #2c3e50;
        margin-bottom: 15px;
        font-size: 18px;
    }

    .payment-instructions p {
        margin: 8px 0;
        color: #34495e;
        line-height: 1.5;
    }

    /* Mobile Responsive */
    @media (max-width: 1024px) {
        .form-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0 !important;
            padding: 15px !important;
            margin-top: 60px !important;
        }
        
        .content-header {
            padding: 20px;
        }
        
        .form-container {
            padding: 20px;
        }
        
        .form-grid {
            gap: 15px;
        }
    }

    /* File input styling */
    input[type="file"] {
        padding: 8px;
    }

    /* Small text styling */
    small {
        color: #666;
        display: block;
        margin-top: 8px;
        font-size: 13px;
    }
  </style>
</head>
<body>
  <?php include 'bd-header.php'; ?>
  <?php include 'bd-sidebar.php'; ?>
  
  <div class="main-content" id="mainContent">
    <div class="content-header">
      <h1>Make Payment</h1>
      <p>Submit your room rental payment</p>
    </div>

    <div class="form-container">
      <?php if (isset($success)): ?>
        <div class="success-message">
          <?php echo $success; ?>
        </div>
      <?php endif; ?>
      
      <?php if (isset($error)): ?>
        <div class="error-message">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="payment.php" enctype="multipart/form-data" id="paymentForm">
        <div class="form-grid">
          <div class="form-group">
            <label class="required">Amount:</label>
            <input type="number" name="amount" step="0.01" min="1" value="<?php echo $monthly_rent; ?>" required>
            <div class="error" id="amountError"></div>
          </div>

          <div class="form-group">
            <label class="required">Month Covered:</label>
            <select name="month_covered" required>
              <option value="">-- Select Month --</option>
              <?php
              $months = [
                  'January', 'February', 'March', 'April', 'May', 'June',
                  'July', 'August', 'September', 'October', 'November', 'December'
              ];
              $current_year = date('Y');
              foreach ($months as $month) {
                  echo "<option value='$month $current_year'>$month $current_year</option>";
              }
              ?>
            </select>
            <div class="error" id="month_coveredError"></div>
          </div>

          <div class="form-group">
            <label class="required">Mode of Payment:</label>
            <select name="mode_of_payment" required>
              <option value="">-- Select Payment Method --</option>
              <option value="GCash">GCash</option>
              <option value="Bank Transfer">Bank Transfer</option>
              <option value="Cash">Cash</option>
              <option value="PayPal">PayPal</option>
            </select>
            <div class="error" id="mode_of_paymentError"></div>
          </div>

          <div class="form-group">
            <label class="required">Reference Number:</label>
            <input type="text" name="reference_number" required placeholder="Transaction ID/Reference">
            <div class="error" id="reference_numberError"></div>
          </div>

          <div class="form-group full-width">
            <label class="required">Upload Receipt:</label>
            <input type="file" name="receipt_image" accept="image/*" required>
            <small>Upload a clear image of your payment receipt (JPG, PNG, GIF) - Max 5MB</small>
            <div class="error" id="receipt_imageError"></div>
          </div>

          <div class="form-group full-width" style="text-align: center; margin-top: 20px;">
            <button type="submit" class="btn">
              <i class="fas fa-paper-plane"></i> Submit Payment
            </button>
          </div>
        </div>
      </form>

      <!-- Payment Information -->
      <div class="payment-instructions">
        <h3><i class="fas fa-info-circle"></i> Payment Instructions</h3>
        <p><strong>GCash:</strong> Send payment to 0917-XXX-XXXX</p>
        <p><strong>Bank Transfer:</strong> BPI Account: XXXX-XXXX-XXXX</p>
        <p><strong>Note:</strong> Always include your Boarder ID (<?php echo $user['boarder_id']; ?>) in the transaction notes.</p>
      </div>
    </div>
  </div>

  <script>
    // Form validation function
    function validateForm() {
        let isValid = true;
        const form = document.getElementById('paymentForm');
        
        // Reset errors
        document.querySelectorAll('.error').forEach(error => {
            error.style.display = 'none';
        });
        
        // Validate amount
        const amount = form.amount.value;
        if (!amount || amount <= 0) {
            document.getElementById('amountError').textContent = 'Please enter a valid amount';
            document.getElementById('amountError').style.display = 'block';
            isValid = false;
        }
        
        // Validate month covered
        const monthCovered = form.month_covered.value;
        if (!monthCovered) {
            document.getElementById('month_coveredError').textContent = 'Please select a month';
            document.getElementById('month_coveredError').style.display = 'block';
            isValid = false;
        }
        
        // Validate mode of payment
        const modeOfPayment = form.mode_of_payment.value;
        if (!modeOfPayment) {
            document.getElementById('mode_of_paymentError').textContent = 'Please select payment method';
            document.getElementById('mode_of_paymentError').style.display = 'block';
            isValid = false;
        }
        
        // Validate reference number
        const referenceNumber = form.reference_number.value;
        if (!referenceNumber.trim()) {
            document.getElementById('reference_numberError').textContent = 'Please enter reference number';
            document.getElementById('reference_numberError').style.display = 'block';
            isValid = false;
        }
        
        // Validate receipt image
        const receiptImage = form.receipt_image.files[0];
        if (!receiptImage) {
            document.getElementById('receipt_imageError').textContent = 'Please upload receipt image';
            document.getElementById('receipt_imageError').style.display = 'block';
            isValid = false;
        }
        
        return isValid;
    }

    // Form submission handler
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });

    // Set active state for sidebar
    document.addEventListener('DOMContentLoaded', function() {
        // Set payment link as active
        const currentLinks = document.querySelectorAll('.sidebar-menu a');
        currentLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === 'payment.php') {
                link.classList.add('active');
            }
        });
    });
  </script>
</body>
</html>