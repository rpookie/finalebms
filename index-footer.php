<footer>
  <span>&copy; 2025 eBoarding Management System. All rights reserved.</span>
</footer>

<style>
/* Footer Styles */
footer {
  background: #FAFAFA;
  color: black;
  text-align: center;
  box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.1);
}

footer span {
  font-size: 1em;
  line-height: 3;
  display: inline-block;
}

/* Mobile responsive */
@media (max-width: 768px) {
  footer {
    padding: 10px 10px;
  }
  
  footer span {
    font-size: 0.9em;
  }
}
</style>

<script>
// Enhanced sidebar and layout functionality
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("sidebarOverlay");
  const mainContent = document.getElementById("mainContent");
  
  if (sidebar) {
    sidebar.classList.toggle("active");
    if (overlay) overlay.classList.toggle("active");
    
    // Adjust main content margin
    if (window.innerWidth > 768 && mainContent) {
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
  const overlay = document.getElementById("sidebarOverlay");
  
  if (window.innerWidth <= 768) {
    if (mainContent) mainContent.style.marginLeft = "0";
    if (sidebar) sidebar.classList.remove("active");
    if (overlay) overlay.classList.remove("active");
  } else if (sidebar && mainContent && sidebar.classList.contains("active")) {
    mainContent.style.marginLeft = "250px";
  }
});

// Close sidebar when clicking on overlay
document.addEventListener('DOMContentLoaded', function() {
  const overlay = document.getElementById("sidebarOverlay");
  if (overlay) {
    overlay.addEventListener('click', function() {
      toggleSidebar();
    });
  }
  
  // Close sidebar when clicking on a link (mobile)
  const sidebarLinks = document.querySelectorAll('.sidebar-menu a');
  sidebarLinks.forEach(link => {
    link.addEventListener('click', function() {
      if (window.innerWidth <= 768) {
        toggleSidebar();
      }
    });
  });
  
  // Initial adjustment for main content
  const sidebar = document.getElementById("sidebar");
  const mainContent = document.getElementById("mainContent");
  
  if (window.innerWidth > 768 && sidebar && sidebar.classList.contains("active") && mainContent) {
    mainContent.style.marginLeft = "250px";
  }
});
</script>