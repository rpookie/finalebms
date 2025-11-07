<footer>
  <span>&copy; 2025 eBoarding Management System. All rights reserved.</span>
</footer>

<script>
  // Enhanced sidebar functionality
  function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.getElementById("mainContent");
    
    if (sidebar && mainContent) {
      if (window.innerWidth <= 768) {
        // Mobile behavior
        sidebar.classList.toggle('mobile-active');
        document.body.classList.toggle('sidebar-open');
      } else {
        // Desktop behavior
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

  // Close sidebar when clicking on links (mobile)
  document.addEventListener('DOMContentLoaded', function() {
    const sidebarLinks = document.querySelectorAll('.sidebar-menu a');
    sidebarLinks.forEach(link => {
      link.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
          toggleSidebar();
        }
      });
    });
  });

  // Handle window resize
  window.addEventListener('resize', function() {
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.getElementById("mainContent");
    
    if (window.innerWidth > 768) {
      sidebar.classList.remove('mobile-active');
      document.body.classList.remove('sidebar-open');
      
      if (!sidebar.classList.contains('hidden')) {
        mainContent.style.marginLeft = "250px";
        mainContent.classList.add('with-sidebar');
      }
    }
  });
</script>
<style type="text/css">
  footer{
        background: #FAFAFA;
        color: black;
        text-align: center;
        padding: 10px;
        box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.1);
        margin-top: auto;
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
</body>
</html>