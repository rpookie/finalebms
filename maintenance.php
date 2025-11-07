<?php
session_start();
include 'db.php';




// Handle maintenance updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_maintenance'])) {
    $maintenance_id = intval($_POST['maintenance_id']);
    $status = $_POST['status'];
    $admin_notes = $conn->real_escape_string($_POST['admin_notes'] ?? '');
    
    $stmt = $conn->prepare("UPDATE maintenance SET status = ?, admin_notes = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $admin_notes, $maintenance_id);
    
    if ($stmt->execute()) {
        $success = "Maintenance request updated successfully!";
    } else {
        $error = "Error updating maintenance request: " . $conn->error;
    }
}

// Get all maintenance requests
$maintenance_requests = $conn->query("SELECT m.*, u.fname, u.lname, u.room_number FROM maintenance m JOIN users u ON m.boarder_id = u.boarder_id ORDER BY m.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Maintenance Requests | eBMS Admin</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <?php include 'ad-header.php'; ?>

  <div class="main-container">
    <?php include 'ad-sidebar.php'; ?>

    <main class="main-content" id="mainContent">
      <div class="content-header">
        <h1>Maintenance Requests</h1>
        <p>Manage and track maintenance requests from boarders</p>
      </div>

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

      <div class="table-container">
        <?php if ($maintenance_requests->num_rows > 0): ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Boarder</th>
                <th>Room</th>
                <th>Issue Type</th>
                <th>Description</th>
                <th>Priority</th>
                <th>Date Reported</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($request = $maintenance_requests->fetch_assoc()): ?>
                <tr>
                  <td>
                    <strong><?php echo $request['fname'] . ' ' . $request['lname']; ?></strong>
                  </td>
                  <td><?php echo $request['room_number']; ?></td>
                  <td><?php echo $request['issue_type']; ?></td>
                  <td><?php echo $request['description']; ?></td>
                  <td>
                    <span class="priority-<?php echo strtolower($request['priority']); ?>">
                      <?php echo $request['priority']; ?>
                    </span>
                  </td>
                  <td><?php echo date('M j, Y', strtotime($request['created_at'])); ?></td>
                  <td>
                    <span class="status-<?php echo str_replace(' ', '-', $request['status']); ?>">
                      <?php echo ucfirst($request['status']); ?>
                    </span>
                  </td>
                  <td>
                    <form method="POST" class="maintenance-form">
                      <input type="hidden" name="maintenance_id" value="<?php echo $request['id']; ?>">
                      <select name="status" class="status-select">
                        <option value="not started" <?php echo $request['status'] == 'not started' ? 'selected' : ''; ?>>Not Started</option>
                        <option value="ongoing" <?php echo $request['status'] == 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                        <option value="completed" <?php echo $request['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                      </select>
                      <textarea name="admin_notes" placeholder="Admin notes" class="admin-notes"><?php echo $request['admin_notes']; ?></textarea>
                      <button type="submit" name="update_maintenance" class="update-btn">Update</button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="empty-state">
            <i class="fa-solid fa-tools"></i>
            <p>No maintenance requests found.</p>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <?php include 'ad-footer.php'; ?>
</body>
</html>