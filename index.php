<!DOCTYPE html>
<html lang="so">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/logo2.png" type="image/x-icon">
  <title>Wasaarada Maaliyada SSC Khaatumo</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-blue: #0066cc;
      --light-blue: #0088ff;
      --dark-blue: #004080;
      --white: #ffffff;
      --light-gray: #f5f5f5;
      --gradient-blue: linear-gradient(135deg, var(--primary-blue) 0%, var(--light-blue) 100%);
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: var(--white);
      color: #333;
      overflow-x: hidden;
      min-height: 100vh;
      line-height: 1.6;
    }
    
    .main-container {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 60px 5%;
      min-height: calc(100vh - 80px);
      background: linear-gradient(to bottom, var(--light-gray) 50%, var(--white) 50%);
    }
    
    .text-section {
      flex: 1;
      padding: 30px;
      animation: fadeInUp 1s ease;
    }
    
    .text-section h1 {
      font-size: 3.5rem;
      margin-bottom: 25px;
      color: var(--dark-blue);
      line-height: 1.2;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .text-section h1 span {
      color: var(--primary-blue);
      position: relative;
      display: inline-block;
    }
    
    .text-section h1 span::after {
      content: '';
      position: absolute;
      bottom: 5px;
      left: 0;
      width: 100%;
      height: 8px;
      background-color: rgba(0, 136, 255, 0.2);
      z-index: -1;
      border-radius: 4px;
    }
    
    .text-section p {
      font-size: 1.25rem;
      line-height: 1.8;
      margin-bottom: 30px;
      max-width: 600px;
      color: #555;
    }
    
    .features {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-top: 30px;
    }
    
    .feature {
      background: var(--white);
      padding: 15px 20px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: all 0.3s ease;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      border: 1px solid rgba(0, 0, 0, 0.05);
      min-width: 150px;
    }
    
    .feature:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 102, 204, 0.1);
      border-color: var(--light-blue);
    }
    
    .feature i {
      color: var(--primary-blue);
      font-size: 1.2rem;
      transition: transform 0.3s ease;
    }
    
    .feature:hover i {
      transform: rotate(15deg);
    }
    
    .feature span {
      font-weight: 500;
    }
    
    .image-section {
      flex: 1;
      padding: 30px;
      display: flex;
      justify-content: center;
      align-items: center;
      animation: fadeIn 1.5s ease;
      position: relative;
    }
    
    .image-container {
      position: relative;
      width: 100%;
      max-width: 550px;
      background: var(--gradient-blue);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 20px 40px rgba(0, 102, 204, 0.15);
      overflow: hidden;
    }
    
    .image-container::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
      z-index: 1;
    }
    
    .image-section img {
      width: 100%;
      border-radius: 10px;
      transition: transform 0.5s ease;
      z-index: 2;
      position: relative;
      border: 5px solid white;
    }
    
    .image-section img:hover {
      transform: scale(1.03);
    }
    
    /* Login Button in Image Section */
    .login-btn-container {
      position: absolute;
      top: 20px;
      right: 20px;
      z-index: 10;
    }
    
    .login-btn {
      background: var(--white);
      color: var(--primary-blue);
      padding: 12px 25px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
      border: 2px solid var(--white);
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      background: var(--gradient-blue);
      color: var(--white);
    }
    
    .login-btn:hover {
       background: var(--white);
       color: var(--primary-blue);
      box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
      transform: translateY(-2px);
    }
    
    /* SSC Khaatumo Logo Animation */
    .logo-animation {
      position: absolute;
      top: 20px;
      left: 20px;
      width: 80px;
      height: 80px;
      z-index: 10;
      animation: float 4s ease-in-out infinite;
    }
    
    .logo-animation svg {
      width: 100%;
      height: 100%;
    }
    
    /* New Light Blue Footer */
    .footer {
      background: #e6f2ff; /* Light blue background */
      color: #004080; /* Dark blue text */
      padding: 30px 5%;
      text-align: center;
      position: relative;
      overflow: hidden;
      border-top: 1px solid rgba(0, 102, 204, 0.2);
    }
    
    .footer::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 3px;
      background: linear-gradient(90deg, 
        rgba(0,102,204,0) 0%, 
        rgba(0,102,204,0.5) 50%, 
        rgba(0,102,204,0) 100%);
      animation: shine 3s infinite;
    }
    
    .copyright {
      font-size: 1rem;
      position: relative;
      display: inline-block;
      padding: 15px 30px;
      background: rgba(255, 255, 255, 0.7);
      border-radius: 50px;
      backdrop-filter: blur(5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      border: 1px solid rgba(0, 102, 204, 0.2);
      animation: fadeInUp 1s ease;
    }
    
    .copyright span {
      display: inline-block;
      position: relative;
    }
    
    .copyright .year {
      color: var(--primary-blue);
      font-weight: bold;
      animation: pulse 2s infinite;
    }
    
    .copyright .heart {
      color: #ff6b6b;
      margin: 0 5px;
      animation: heartbeat 1.5s infinite;
    }
    
    .copyright .rights {
      font-weight: 300;
      opacity: 0.9;
    }
    
    /* Floating dots background */
    .footer::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: radial-gradient(circle at 20% 30%, rgba(0,102,204,0.05) 1px, transparent 1px),
                        radial-gradient(circle at 80% 70%, rgba(0,102,204,0.05) 1px, transparent 1px),
                        radial-gradient(circle at 40% 60%, rgba(0,102,204,0.05) 1px, transparent 1px),
                        radial-gradient(circle at 60% 40%, rgba(0,102,204,0.05) 1px, transparent 1px);
      background-size: 100px 100px;
      animation: floatDots 20s linear infinite;
      z-index: 0;
    }
    
    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    @keyframes fadeInUp {
      from { 
        opacity: 0;
        transform: translateY(30px);
      }
      to { 
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
      100% { transform: translateY(0px); }
    }
    
    @keyframes pulse {
      0% { transform: scale(1); opacity: 0.8; }
      50% { transform: scale(1.1); opacity: 1; }
      100% { transform: scale(1); opacity: 0.8; }
    }
    
    @keyframes heartbeat {
      0% { transform: scale(1); }
      25% { transform: scale(1.1); }
      50% { transform: scale(1); }
      75% { transform: scale(1.1); }
      100% { transform: scale(1); }
    }
    
    @keyframes shine {
      0% { left: -100%; }
      100% { left: 100%; }
    }
    
    @keyframes floatDots {
      0% { background-position: 0 0, 0 0, 0 0, 0 0; }
      100% { background-position: 100px 100px, -100px -100px, 50px -50px, -50px 50px; }
    }
    
    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-5px); }
    }
    
    /* Responsive Design */
    @media (max-width: 992px) {
      .text-section h1 {
        font-size: 2.8rem;
      }
      
      .text-section p {
        font-size: 1.1rem;
      }
    }
    
    @media (max-width: 768px) {
      .main-container {
        flex-direction: column;
        text-align: center;
        padding: 40px 20px;
        background: var(--light-gray);
      }
      
      .text-section {
        padding: 20px 0;
        display: flex;
        flex-direction: column;
        align-items: center;
      }
      
      .text-section h1 {
        font-size: 2.2rem;
      }
      
      .text-section p {
        text-align: center;
      }
      
      .features {
        justify-content: center;
      }
      
      .image-section {
        padding: 20px 0;
      }
      
      .image-container {
        padding: 20px;
      }
      
      .logo-animation {
        width: 60px;
        height: 60px;
        top: 15px;
        right: 15px;
      }
      
      .login-btn-container {
        top: 15px;
        right: 15px;
      }
      
      .login-btn {
        padding: 10px 20px;
        font-size: 0.9rem;
      }
    }
    
    @media (max-width: 576px) {
      .text-section h1 {
        font-size: 1.8rem;
      }
      
      .logo-animation {
        width: 50px;
        height: 50px;
      }
      
      .feature {
        min-width: 120px;
      }
      
      .copyright {
        font-size: 0.9rem;
        padding: 10px 20px;
      }
      
      .login-btn {
        padding: 8px 15px;
        font-size: 0.8rem;
      }
    }
    
    .theme-toggle-container {
      position: absolute;
      top: 20px;
      left: 20px;
      z-index: 20;
      display: flex;
      align-items: center;
    }
    #theme-toggle {
      background: var(--white);
      border: 2px solid var(--primary-blue);
      border-radius: 50%;
      width: 44px;
      height: 44px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(0,0,0,0.07);
      transition: background 0.3s, border 0.3s;
      font-size: 1.3rem;
      outline: none;
    }
    #theme-toggle:hover {
      background: var(--primary-blue);
      color: var(--white);
      border-color: var(--dark-blue);
    }
    #theme-icon {
      transition: color 0.3s, transform 0.3s;
    }
    body.dark-mode {
      background: #181c24;
      color: #e0e6ef;
    }
    body.dark-mode .main-container {
      background: linear-gradient(to bottom, #232a36 50%, #181c24 50%);
    }
    body.dark-mode .text-section h1 {
      color: #e0e6ef;
    }
    body.dark-mode .text-section h1 span::after {
      background-color: rgba(0, 136, 255, 0.12);
    }
    body.dark-mode .text-section p {
      color: #b0b8c9;
    }
    body.dark-mode .feature {
      background: #232a36;
      border-color: rgba(0,0,0,0.12);
      color: #e0e6ef;
    }
    body.dark-mode .feature i {
      color: #4fc3f7;
    }
    body.dark-mode .image-container {
      background: linear-gradient(135deg, #232a36 0%, #2a3a4d 100%);
      box-shadow: 0 20px 40px rgba(0, 102, 204, 0.08);
    }
    body.dark-mode .image-section img {
      border: 5px solid #232a36;
    }
    body.dark-mode .login-btn {
      background: linear-gradient(135deg, #232a36 0%, #2a3a4d 100%);
      color: #e0e6ef;
      border-color: #232a36;
    }
    body.dark-mode .login-btn:hover {
      background: #e0e6ef;
      color: #232a36;
    }
    body.dark-mode .footer {
      background: #232a36;
      color: #b0b8c9;
      border-top: 1px solid rgba(0, 102, 204, 0.08);
    }
    body.dark-mode .copyright {
      background: rgba(24, 28, 36, 0.7);
      color: #b0b8c9;
      border-color: rgba(0, 102, 204, 0.08);
    }
    body.dark-mode .copyright .year {
      color: #4fc3f7;
    }
    body.dark-mode .copyright .heart {
      color: #ff8a80;
    }
    @media (max-width: 768px) {
      .theme-toggle-container {
        top: 10px;
        left: 10px;
      }
    }
  </style>
</head>
<body>
<!-- Dark/Light Mode Toggle Button -->
<div class="theme-toggle-container">
  <button id="theme-toggle" aria-label="Toggle dark mode">
    <i id="theme-icon" class="fas fa-moon"></i>
  </button>
</div>
<div class="image-section">
      <div class="login-btn-container">
        <a href="login" class="login-btn">
          <i class="fas fa-user-shield"></i>
          <span>Login</span>
        </a>
      </div>
  <div class="main-container">
    <div class="text-section">
      <h1>Ministry of  <span>Finance </span> SSC-Khaatumo</h1>
      <p>
This system is specifically designed for the management, monitoring, and safeguarding of SSC Khaatumo's finances. It facilitates the registration, disbursement, and tracking of expenses and revenues.
      </p>
      
      <div class="features">
        <div class="feature">
          <i class="fas fa-shield-alt"></i>
          <span>Trust</span>
        </div>
        <div class="feature">
          <i class="fas fa-bolt"></i>
          <span>Efficiency</span>
        </div>
        <div class="feature">
          <i class="fas fa-cogs"></i>
          <span>Management</span>
        </div>
        <div class="feature">
          <i class="fas fa-chart-line"></i>
          <span>Reporting</span>
        </div>
      </div>
    </div>
    
    
      
      <div class="image-container">
        <!-- Animated SSC Khaatumo Logo -->
        <div class="logo-animation">
          <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="45" fill="white" stroke="#0066cc" stroke-width="2"/>
            <path d="M50 15 L75 85 L25 85 Z" fill="#0066cc" stroke="white" stroke-width="2"/>
            <circle cx="50" cy="50" r="20" fill="white" stroke="#0066cc" stroke-width="2"/>
            <text x="50" y="55" font-family="Arial" font-size="14" font-weight="bold" text-anchor="middle" fill="#0066cc">SSC</text>
          </svg>
        </div>
        <img src="img/logo2.png" alt="SSC Khaatumo Financial System">
      </div>
    </div>
  </div>

  <footer class="footer">
    <div class="copyright">
      <span class="year">Copyright </span>  © 2025 Ministry of - Finance SSC Khaatumo
      <span class="heart"><i class="fas fa-heart"></i></span> 
      <span class="rights"> | All Rights Reserved</span>
    </div>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Logo animation
      const logo = document.querySelector('.logo-animation');
      
      setInterval(() => {
        logo.style.animation = 'pulse 1.5s ease-in-out';
        setTimeout(() => {
          logo.style.animation = 'float 4s ease-in-out infinite';
        }, 1500);
      }, 8000);
      
      // Copyright text animation
      const yearSpan = document.querySelector('.copyright .year');
      setInterval(() => {
        yearSpan.style.animation = 'none';
        void yearSpan.offsetWidth; // Trigger reflow
        yearSpan.style.animation = 'pulse 2s infinite';
      }, 5000);
      
      // Feature icons animation
      const features = document.querySelectorAll('.feature');
      features.forEach(feature => {
        feature.addEventListener('mouseenter', function() {
          const icon = this.querySelector('i');
          icon.style.transform = 'rotate(15deg) scale(1.2)';
        });
        feature.addEventListener('mouseleave', function() {
          const icon = this.querySelector('i');
          icon.style.transform = 'rotate(0deg) scale(1)';
        });
      });
    });

    // Theme toggle logic
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    function setTheme(mode) {
      if (mode === 'dark') {
        document.body.classList.add('dark-mode');
        themeIcon.classList.remove('fa-moon');
        themeIcon.classList.add('fa-sun');
      } else {
        document.body.classList.remove('dark-mode');
        themeIcon.classList.remove('fa-sun');
        themeIcon.classList.add('fa-moon');
      }
    }
    function getSavedTheme() {
      return localStorage.getItem('theme-mode');
    }
    function saveTheme(mode) {
      localStorage.setItem('theme-mode', mode);
    }
    function toggleTheme() {
      const isDark = document.body.classList.contains('dark-mode');
      setTheme(isDark ? 'light' : 'dark');
      saveTheme(isDark ? 'light' : 'dark');
    }
    // On load
    (function() {
      const saved = getSavedTheme();
      if (saved) {
        setTheme(saved);
      } else if (prefersDark) {
        setTheme('dark');
      } else {
        setTheme('light');
      }
    })();
    themeToggle.addEventListener('click', toggleTheme);
  </script>

</body>
</html>