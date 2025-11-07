function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    if (!sidebar) {
        console.error("Sidebar element not found!");
        return;
    }
    sidebar.classList.toggle("active");

    // Handle overlay for mobile
    if (sidebar.classList.contains("active") && window.innerWidth <= 768) {
        createOverlay();
    } else {
        removeOverlay();
    }
}

function createOverlay() {
    removeOverlay();
    const overlay = document.createElement('div');
    overlay.id = 'sidebar-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 60px;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 999;
        cursor: pointer;
    `;
    overlay.onclick = toggleSidebar;
    document.body.appendChild(overlay);
}

function removeOverlay() {
    const overlay = document.getElementById('sidebar-overlay');
    if (overlay) overlay.remove();
}

function setActiveMenuItem() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    const menuItems = document.querySelectorAll('.sidebar a');
    menuItems.forEach(item => {
        const href = item.getAttribute('href');
        if (href === currentPage) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
        item.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                const sidebar = document.getElementById('sidebar');
                if (sidebar && sidebar.classList.contains('active')) {
                    toggleSidebar();
                }
            }
        });
    });
}

function initializeLoginLogoutButton() {
    const btn = document.querySelector('.logout-btn');
    if (!btn) return;
    btn.onclick = function(e) {
        e.preventDefault();
        const label = btn.textContent.trim();
        if (label === 'Login') {
            window.location.href = 'login.php';
        } else if (label === 'Logout') {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }
    }
}

// Sidebar should start closed
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById("sidebar");
    if (sidebar) sidebar.classList.remove("active");
    setActiveMenuItem();
    initializeLoginLogoutButton();

    // Close sidebar when clicking outside (on mobile)
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById("sidebar");
        const menuToggle = document.querySelector('.menu-toggle');
        if (window.innerWidth <= 768 && sidebar && menuToggle) {
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                if (sidebar.classList.contains('active')) {
                    toggleSidebar();
                }
            }
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) removeOverlay();
    });
});

// --- Additional page functions ---

// View room details function
function viewRoomDetails(roomNumber) {
    window.location.href = 'room-detail.php?room=' + roomNumber;
}

// Change main image in room detail page
function changeMainImage(src) {
    const mainImage = document.getElementById('mainImage');
    if (mainImage) mainImage.src = src;
}

// Dropdown for user menu (if needed)
function toggleDropdown() {
    const userMenu = document.querySelector('.user-menu');
    if (userMenu) userMenu.classList.toggle('show');
}

function initializeDropdown() {
    const userBtn = document.querySelector('.user-btn');
    if (userBtn) {
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDropdown();
        });
    }
    document.addEventListener('click', function(e) {
        const userMenu = document.querySelector('.user-menu');
        if (userMenu && !userMenu.contains(e.target)) {
            userMenu.classList.remove('show');
        }
    });
}

function validateForm(formId) {
    let isValid = true;
    const form = document.getElementById(formId);
    if (!form) return false;
    form.querySelectorAll('.error').forEach(error => error.style.display = 'none');
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            const fieldName = field.name;
            const errorElement = document.getElementById(fieldName + 'Error');
            if (errorElement) {
                errorElement.style.display = 'block';
                errorElement.textContent = 'This field is required';
            }
            isValid = false;
        }
    });
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (field.value && !emailRegex.test(field.value)) {
            const errorElement = document.getElementById(field.name + 'Error');
            if (errorElement) {
                errorElement.style.display = 'block';
                errorElement.textContent = 'Please enter a valid email address';
            }
            isValid = false;
        }
    });
    return isValid;
}

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    const reader = new FileReader();
    reader.onloadend = function() {
        preview.src = reader.result;
    }
    if (file) reader.readAsDataURL(file);
    else preview.src = "";
}