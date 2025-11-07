<header class="header">
  <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
  <div class="logo">eBMS</div>
  <a href="login.php" class="login-link">Login</a>
</header>

<style>
/* Header styles */
.header {
  background: #FAFAFA;
  color: #333333;
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

.logo {
  font-family: Arial Black;
  font-size: 1.8em;
  font-weight: bold;
}

.login-link {
  color: #333333;
  text-decoration: none;
  padding: 8px 15px;
  border-radius: 5px;
  background: rgba(255,255,255,0.2);
  transition: background 0.3s;
}

.login-link:hover {
  background: rgba(200,100,255,0.3);
}
</style>