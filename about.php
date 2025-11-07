<?php 
session_start();
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About eBMS | eBoard Management System</title>
  <link href="https://fonts.googleapis.com/css2?family=Titan+One&family=Tomorrow:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Reset and base styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: #FFF8F0;

    }

    /* Header styles */
    .header {
      background: #FFF8F0;
      color: #FFF8F0;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
    }

    .menu-toggle {
      background: none;
      border: none;
      color: white;
      font-size: 1.5em;
      cursor: pointer;
      padding: 5px 10px;
      border-radius: 5px;
      transition: background 0.3s;
    }

    .menu-toggle:hover {
      background: rgba(255,255,255,0.1);
    }

    .logo {
      font-family: Arial Black;
      font-size: 1.8em;
      font-weight: bold;
    }

    .login-link {
      color: white;
      text-decoration: none;
      padding: 8px 15px;
      border-radius: 5px;
      background: rgba(255,255,255,0.2);
      transition: background 0.3s;
    }

    .login-link:hover {
      background: rgba(255,255,255,0.3);
    }

    /* Main content area */
    .main-content {
        padding: 80px 20px 60px 20px;
        min-height: 100vh;
        background: #FFF8F0;
        transition: margin-left 0.3s ease;
        width: 100%;
        height: 100%;
    }

    /* Content header */
    .content-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .content-header h1 {
        font-family: Arial Black;
        font-size: 2.5em;
        color: Black;
        margin-bottom: 10px;
    }

    .content-header p {
        font-size: 1.2em;
        color: Black;
    }

    /* About content */
    .about-content {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        max-width: 800px;
        margin: 0 auto;
    }

    .about-content h2 {
        color: #4263eb;
        margin-bottom: 20px;
        font-size: 1.8em;
    }

    .about-content h3 {
        color: #5e7599;
        margin: 25px 0 15px 0;
        font-size: 1.4em;
    }

    .about-content p {
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .about-content ul {
        line-height: 1.8;
        margin-bottom: 25px;
        padding-left: 20px;
    }

    .about-content li {
        margin-bottom: 8px;
    }

    .contact-info {
        background: #e8f4ff;
        padding: 20px;
        border-radius: 10px;
        margin-top: 30px;
    }

    .contact-info h4 {
        color: #4263eb;
        margin-bottom: 15px;
        font-size: 1.3em;
    }

    .contact-info p {
        margin-bottom: 10px;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .main-content {
            padding: 70px 15px 50px 15px;
        }
        
        .content-header h1 {
            font-size: 2em;
        }
        
        .content-header p {
            font-size: 1em;
        }
        
        .about-content {
            padding: 20px;
        }
    }
  </style>
</head>
<body>
  <?php include 'index-header.php'; ?>
  <?php include 'index-sidebar.php'; ?>

  <div class="main-content" id="mainContent">
    <div class="content-header">
      <h1>About eBMS</h1>
      <p>Learn more about our eBoard Management System</p>
    </div>

    <div class="about-content">
      <h2>What is eBMS?</h2>
      <p>
        eBMS (electronic Boarding Management System) is a comprehensive digital platform designed to streamline 
        the management of boarding houses and dormitories. Our system provides an efficient, secure, 
        and user-friendly solution for both boarders and administrators.
      </p>

      <h3>Features for Boarders:</h3>
      <ul>
        <li>Easy room reservation and booking</li>
        <li>Online payment processing with receipt upload</li>
        <li>Payment history tracking</li>
        <li>Maintenance request submission</li>
        <li>Real-time announcement viewing</li>
        <li>Profile management</li>
      </ul>

      <h3>Features for Administrators:</h3>
      <ul>
        <li>Reservation approval system</li>
        <li>Payment verification and management</li>
        <li>Boarder account management</li>
        <li>Maintenance request tracking</li>
        <li>Announcement broadcasting</li>
        <li>Automated SMS/Email notifications</li>
      </ul>

      <div class="contact-info">
        <h4>Contact Information</h4>
        <p><strong>Address:</strong> Gonzaga, Cagayan</p>
        <p><strong>Email:</strong> info@ebms.com</p>
        <p><strong>Phone:</strong> +63 XXX-XXX-XXXX</p>
      </div>
    </div>
  </div>

  <script>
    // Sidebar functionality
    function toggleSidebar() {
      const sidebar = document.getElementById("sidebar");
      const mainContent = document.getElementById("mainContent");
      
      if (sidebar) {
        sidebar.classList.toggle("active");
        
        // Adjust main content margin
        if (window.innerWidth > 768) {
          if (sidebar.classList.contains("active")) {
            mainContent.style.marginLeft = "250px";
          } else {
            mainContent.style.marginLeft = "0";
          }
        }
      }
    }

    // Adjust layout on window resize
    window.addEventListener('resize', function() {
      const sidebar = document.getElementById("sidebar");
      const mainContent = document.getElementById("mainContent");
      
      if (window.innerWidth <= 768) {
        mainContent.style.marginLeft = "0";
      } else if (sidebar && sidebar.classList.contains("active")) {
        mainContent.style.marginLeft = "250px";
      }
    });
  </script>
  <?php include 'index-footer.php'; ?>
</body>

</html>