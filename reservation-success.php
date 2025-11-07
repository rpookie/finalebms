<?php
session_start();
if (!isset($_SESSION['reservation_data'])) {
    header("Location: reserve-room.php");
    exit();
}

$data = $_SESSION['reservation_data'];
unset($_SESSION['reservation_data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reservation Submitted | eBMS</title>
</head>
<body>
  <?php include 'index-header.php'; ?>

  <div class="main-content" id="mainContent">
    <div class="success-container">
      <h1>Reservation Submitted Successfully!</h1>
      
      <div class="reservation-info">
        <p><span class="info-label">Room Reserved:</span> <?php echo $data['room']; ?></p>
        <p><span class="info-label">Name:</span> <?php echo $data['name']; ?></p>
        <p><span class="info-label">Email:</span> <?php echo $data['email']; ?></p>
        <p><span class="info-label">Contact:</span> <?php echo $data['contact']; ?></p>
        <p><span class="info-label">Age:</span> <?php echo $data['age']; ?></p>
        <p><span class="info-label">Address:</span> <?php echo $data['address']; ?></p>
        <p><span class="info-label">Guardian:</span> <?php echo $data['guardian']; ?></p>
        <p><span class="info-label">Guardian Contact:</span> <?php echo $data['guardian_contact']; ?></p>
        <?php if (!empty($data['guardian_email'])): ?>
          <p><span class="info-label">Guardian Email:</span> <?php echo $data['guardian_email']; ?></p>
        <?php endif; ?>
      </div>

      <div class="note-box">
        <p><strong>Important Notice:</strong></p>
        <p>✅ Your room reservation request has been received.</p>
        <p>⏳ Please wait for approval from the administrator.</p>
        <p>📧 You will be notified via email/text message once your reservation is approved.</p>
        <p>🔐 After approval, the administrator will create your account and send you your Boarder ID and temporary password.</p>
        <p>🛡️ For security purposes, you will be required to change your password upon first login.</p>
      </div>

      <a href="index.php" class="home-btn">Return to Homepage</a>
    </div>
  </div>

  <style>
    .success-container {
      background: #fff;
      color: #000;
      border-radius: 20px;
      padding: 40px;
      max-width: 600px;
      margin: 40px auto;
      text-align: center;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    }

    .success-container h1 {
      font-family: 'Titan One', cursive;
      color: #4263eb;
      font-size: 2.2em;
      margin-bottom: 20px;
    }

    .success-container p {
      font-size: 1.1em;
      line-height: 1.6;
      margin-bottom: 15px;
    }

    .note-box {
      background: #e8f4ff;
      border-left: 4px solid #4263eb;
      padding: 15px;
      margin: 20px 0;
      text-align: left;
    }

    .reservation-info {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 20px;
      margin: 20px 0;
      text-align: left;
    }

    .reservation-info p {
      margin: 8px 0;
      font-size: 1em;
    }

    .info-label {
      font-weight: bold;
      color: #333;
    }

    .home-btn {
      background: linear-gradient(90deg, #416cec, #345dd8);
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 12px 25px;
      font-size: 1em;
      font-family: 'Tomorrow', sans-serif;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
      margin-top: 10px;
    }

    .home-btn:hover {
      background: linear-gradient(90deg, #345dd8, #416cec);
      transform: translateY(-2px);
    }
  </style>
</body>
</html>