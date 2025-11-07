<?php
// Remove the session_start() since it's already called in the main file
// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - eBMS</title>

</head>
<body>
  <!-- Header -->
  <div class="header">
    <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
    <div class="logo">eBMS Admin</div>
    <div class="user-menu">
      <button class="user-btn" onclick="toggleDropdown()">
        <i class="fa-solid fa-user-shield"></i> Administrator <i class="fa-solid fa-caret-down"></i>
      </button>
      <div class="dropdown" id="userDropdown">
        <a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a>
        <a href="settings.php"><i class="fa-solid fa-cog"></i> Settings</a>
        <a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>
  </div>

  <script>
    // Toggle dropdown menu
    function toggleDropdown() {
      const dropdown = document.getElementById('userDropdown');
      dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }

    // Enhanced sidebar toggle for dashboard pages
    function toggleSidebar() {
      const sidebar = document.getElementById("sidebar");
      const mainContent = document.getElementById("mainContent");
      
      if (sidebar && mainContent) {
        if (window.innerWidth <= 768) {
          // Mobile behavior
          sidebar.classList.toggle('mobile-active');
          document.body.classList.toggle('sidebar-open');
        } else {
          // Desktop behavior - toggle hidden class
          sidebar.classList.toggle('hidden');
          if (sidebar.classList.contains('hidden')) {
            mainContent.style.marginLeft = "0";
            mainContent.classList.remove('with-sidebar');
          } else {
            mainContent.style.marginLeft = "250px";
            mainContent.classList.add('with-sidebar');
          }
        }
      }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
      const dropdown = document.getElementById('userDropdown');
      const userBtn = document.querySelector('.user-btn');
      
      if (!userBtn.contains(event.target) && dropdown && !dropdown.contains(event.target)) {
        dropdown.style.display = 'none';
      }
      
      // Close sidebar when clicking outside on mobile
      const sidebar = document.getElementById("sidebar");
      const menuToggle = document.querySelector('.menu-toggle');
      
      if (window.innerWidth <= 768 && sidebar && !sidebar.contains(event.target) && 
          event.target !== menuToggle && !menuToggle.contains(event.target)) {
        sidebar.classList.remove('mobile-active');
        document.body.classList.remove('sidebar-open');
      }
    });

    // Initialize sidebar and dropdown on page load
    document.addEventListener('DOMContentLoaded', function() {
      const sidebar = document.getElementById("sidebar");
      const mainContent = document.getElementById("mainContent");
      const dropdown = document.getElementById('userDropdown');
      
      if (sidebar && mainContent) {
        // Ensure sidebar starts closed
        sidebar.classList.add('hidden');
        mainContent.style.marginLeft = "0";
        mainContent.classList.remove('with-sidebar');
        document.body.classList.remove('sidebar-open');
      }
      
      if (dropdown) {
        dropdown.style.display = 'none';
      }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
      const sidebar = document.getElementById("sidebar");
      const mainContent = document.getElementById("mainContent");
      
      if (window.innerWidth > 768) {
        // Desktop - remove mobile active class
        sidebar.classList.remove('mobile-active');
        document.body.classList.remove('sidebar-open');
        
        // Ensure proper margin
        if (!sidebar.classList.contains('hidden')) {
          mainContent.style.marginLeft = "250px";
          mainContent.classList.add('with-sidebar');
        } else {
          mainContent.style.marginLeft = "0";
          mainContent.classList.remove('with-sidebar');
        }
      } else {
        // Mobile - ensure sidebar is hidden by default
        if (!sidebar.classList.contains('mobile-active')) {
          sidebar.classList.add('hidden');
          mainContent.style.marginLeft = "0";
          mainContent.classList.remove('with-sidebar');
        }
      }
    });
  </script>
  <style type="text/css">
    body{
      background: white;
      color: white;
    }
    .header {
      background: #FAFAFA;
      color: #FAFAFA;
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
    .logo {
      font-family: Arial Black;
      color: black;
    }
    .menu-toggle {
      background: none;
      border: none;
      color: #333333;
      font-size: 1.5em;
      cursor: pointer;
      padding: 5px 10px;
      border-radius: 5px;
      transition: background 0.3s;
      z-index: 1001;
    }

    .menu-toggle:hover {
      background: rgba(255,255,255,0.1);
    }
    .user-btn {
      background: lightgray;
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 8px 15px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .dropdown {
      position: absolute;
      top: 100%;
      right: 20px;
      background: lightgray;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      min-width: 180px;
      display: none;
      z-index: 1000;
    }
    .dropdown.active {
      display: block;
    }
    .dropdown a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 15px;
      color: black;
      text-decoration: none;
      border-bottom: 1px solid #eee;
      transition: background 0.3s;
    }
    .dropdown a:hover {
      background: blue;
    }
    .dropdown a:last-child {
      border-bottom: none;
    }
  </style>