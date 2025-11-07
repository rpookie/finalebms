<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eBMS - Boarder</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            width: 100%;
            background: #FAFAFA;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            right: 0;
            margin-right: auto;
        }
        .menu-toggle {
            background: ;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: black;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background 0.3s;
            z-index: 1001;
        }
        .menu-toggle:hover {
            background: rgba(255,255,255,0.1);
        }
        .logo {
            font-size: 1.8em;
            font-weight: bold;
            color: black;
        }
        .user-btn {
            background: none;
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
            background: white;
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
            color: #333;
            text-decoration: none;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
        }
        .dropdown a:hover {
            background: #f8f9ff;
        }
        .dropdown a:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <h2 class="logo">eBMS</h2>
    <div class="header">
        <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Toggle menu">
            <i class="fa-solid fa-bars"></i>
        </button>

        <button class="user-btn" onclick="toggleDropdown()">
            <?php echo isset($_SESSION['fname']) ? htmlspecialchars($_SESSION['fname'] . ' ' . $_SESSION['lname']) : 'User'; ?> 
            <i class="fa-solid fa-user"></i> 
            <i class="fa-solid fa-caret-down"></i>
        </button>
        
        <div class="dropdown" id="userDropdown">
            <a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a>
            <a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('active');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            const userBtn = document.querySelector('.user-btn');
            
            if (!dropdown.contains(event.target) && !userBtn.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });

        function toggleSidebar() {
            // Replace with actual sidebar toggle functionality
            console.log('Sidebar toggle functionality would go here');
        }
    </script>
</body>
</html>
<!-- Is there something wrong with the code? -->