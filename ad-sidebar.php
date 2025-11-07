<style>
/* Admin Sidebar Styles */
.sidebar {
    width: 250px;
    background: white;
    color: black;
    position: fixed;
    top: 70px;
    left: 0;
    height: calc(100vh - 70px);
    transition: transform 0.3s ease, margin-left 0.3s ease;
    z-index: 999;
    overflow-y: auto;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.sidebar.hidden {
    transform: translateX(-100%);
}

.sidebar.mobile-active {
    transform: translateX(0);
}

.sidebar-header {
    padding: 3px;
    background: rgba(0,0,0,0.2);
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-header h2 {
    font-family: 'Titan One', cursive;
    font-size: 1.5em;
    margin: 0;
    color: white;
}

.sidebar-menu {
    padding: 20px 0;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    color: black;
    text-decoration: none;
    transition: all 0.3s;
    border-left: 3px solid transparent;
    font-size: 0.95em;
}

.sidebar-menu a:hover {
    background: lightblue;
    border-left: 3px solid #416cec;
}

.sidebar-menu a.active {
    background: blue;
    border-left: 3px solid #416cec;
}

.sidebar-menu i {
    margin-right: 12px;
    width: 20px;
    text-align: center;
    font-size: 1.1em;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.mobile-active {
        transform: translateX(0);
    }
}

/* Scrollbar styling */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.5);
}

/* Main content adjustment */
.main-content.with-sidebar {
    margin-left: 250px;
    transition: margin-left 0.3s ease;
}

.main-content {
    margin-left: 0;
    transition: margin-left 0.3s ease;
}

@media (max-width: 768px) {
    .main-content.with-sidebar {
        margin-left: 0;
    }
}
</style>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2></h2>
    </div>
    <div class="sidebar-menu">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>
        <a href="ad-dashboard.php" class="<?php echo $current_page == 'ad-dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="ad-reservations.php" class="<?php echo $current_page == 'ad-reservations.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-check"></i>
            <span>Reservations</span>
        </a>
        <a href="ad-boarders.php" class="<?php echo $current_page == 'ad-boarders.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Boarders</span>
        </a>
        <a href="ad-payments.php" class="<?php echo $current_page == 'ad-payments.php' ? 'active' : ''; ?>">
            <i class="fas fa-credit-card"></i>
            <span>Payments</span>
        </a>
        <a href="ad-rooms.php" class="<?php echo $current_page == 'ad-rooms.php' ? 'active' : ''; ?>">
            <i class="fas fa-bed"></i>
            <span>Rooms</span>
        </a>
        <a href="announcements.php" class="<?php echo $current_page == 'announcements.php' ? 'active' : ''; ?>">
            <i class="fas fa-bullhorn"></i>
            <span>Announcements</span>
        </a>
        <a href="index.php" target="_blank">
            <i class="fas fa-external-link-alt"></i>
            <span>View Site</span>
        </a>
        <a href="logout.php" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 20px; padding-top: 20px;">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<script>
// Enhanced sidebar functionality
function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("sidebarOverlay");
    const mainContent = document.getElementById("mainContent");
    
    if (sidebar) {
        sidebar.classList.toggle("active");
        if (overlay) overlay.classList.toggle("active");
        
        // Adjust main content if it exists
        if (mainContent && window.innerWidth > 768) {
            if (sidebar.classList.contains("active")) {
                mainContent.style.marginLeft = "250px";
            } else {
                mainContent.style.marginLeft = "0";
            }
        }
    }
}

// Close sidebar when clicking on a link (mobile)
document.addEventListener('DOMContentLoaded', function() {
    const sidebarLinks = document.querySelectorAll('.sidebar-menu a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                toggleSidebar();
            }
        });
    }); 
    
    // Set active link based on current page
    const currentPage = window.location.pathname.split('/').pop();
    sidebarLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });
});
</script>