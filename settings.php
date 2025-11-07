<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings | eBMS</title>
  <link rel="stylesheet" href="style.css">
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
        <h1>Settings</h1>
        <p>Manage your account preferences</p>
      </div>

      <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto;">
        <h3 style="color: #4263eb; margin-bottom: 20px;">Notification Settings</h3>
        
        <form>
          <div style="margin-bottom: 20px;">
            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
              <input type="checkbox" checked>
              <span>Email notifications</span>
            </label>
            <label style="display: flex; align-items: center; gap: 10px;">
              <input type="checkbox" checked>
              <span>SMS notifications</span>
            </label>
          </div>

          <h3 style="color: #4263eb; margin: 30px 0 20px 0;">Privacy Settings</h3>
          
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px;">Profile Visibility</label>
            <select style="width: 100%; padding: 10px; border: 2px solid #c9d3ff; border-radius: 6px;">
              <option>Public</option>
              <option>Boarders Only</option>
              <option>Private</option>
            </select>
          </div>

          <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="btn">Save Settings</button>
          </div>
        </form>
      </div>
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