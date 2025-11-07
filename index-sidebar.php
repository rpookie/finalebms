<style>
/* Sidebar styles */
.sidebar {
    position: fixed;
    top: 0;
    left: -250px;
    width: 250px;
    height: 100%;
    background: #F5F5F5;
    color: black;
    transition: left 0.3s ease;
    z-index: 999;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
}

.sidebar.active {
    left: 0;
}

.sidebar-header {
    padding: 20px;
    background: rgba(0,0,0,0.2);
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-header h2 {
    font-family: 'Titan One', cursive;
    font-size: 1.5em;
    margin: 0;
    color: #333333;
}

.sidebar-menu {
    flex: 1;
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
}

.sidebar-menu a:hover, .sidebar-menu a.active {
    background: rgba(255,255,255,0.1);
    color: #FFB84D;
    border-left: 3px solid #416cec;
}

.sidebar-menu i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

.sidebar-footer {
    padding: 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.logout-btn {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    background: rgba(255,255,255,0.1);
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
    color: black;
    text-decoration: none;
}

.logout-btn:hover {
    background: rgba(255,255,255,0.2);
}

.logout-btn i {
    margin-right: 10px;
}

/* Overlay for mobile */
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 998;
    display: none;
}

.sidebar-overlay.active {
    display: block;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .sidebar.active {
        left: 0;
    }
    
    .sidebar-overlay.active {
        display: block;
    }
}
</style>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="sidebar index-sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>eBMS</h2>
    </div>
    <div class="sidebar-menu">
        <a href="index.php">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="rooms.php">
            <i class="fas fa-bed"></i>
            <span>Rooms</span>
        </a>
        <a href="about.php">
            <i class="fas fa-info-circle"></i>
            <span>About eBMS</span>
        </a>
    </div>
    <div class="sidebar-footer">
        <a href="login.php" class="logout-btn">
            <i class="fas fa-sign-in-alt"></i>
            <span>Login</span>
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