<?php
// Your PHP backend logic remains the same
require_once 'vendor/autoload.php';
require_once 'db.php';

session_start();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chavings Tasker - Your Trusted Marketplace for Local & Digital Gigs in Nigeria</title>
    
    <link rel="stylesheet" href="style.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/heroicons@2.1.3/dist/solid/index.js" defer></script>
    <script src="https://unpkg.com/heroicons@2.1.3/dist/outline/index.js" defer></script>
</head>
<body>

    <button class="theme-toggle" id="theme-toggle-btn" onclick="toggleTheme()" aria-label="Toggle light or dark mode">
        </button>

    <header class="hero" id="home">
        <canvas id="particle-canvas"></canvas>
        <nav class="navbar">
            <div class="logo">Chavings Tasker</div>
            <ul class="nav-links" id="nav-links">
                <li><a href="#features" onclick="smoothScroll('features')">Features</a></li>
                <li><a href="#how-it-works" onclick="smoothScroll('how-it-works')">How It Works</a></li>
                <li><a href="about.php">About</a></li> <li class="nav-cta-desktop"><a href="signup.php" class="cta-btn secondary">Hire a Lancer</a></li>
                <li class="nav-cta-desktop"><a href="signup.php" class="cta-btn">Join as Lancer</a></li>
            </ul>
             <div class="nav-cta-mobile">
                <a href="signup.php" class="cta-btn">Join</a>
            </div>
            <button class="hamburger" id="hamburger-btn" onclick="toggleMenu()" aria-label="Toggle menu">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
        </nav>

        <div class="hero-content">
            <h1>From Local Tasks to Digital Gigs, Done Right.</h1>
            <p>Welcome to Nigeria's most trusted marketplace. Connect with verified local artisans and digital freelancers to accomplish any goal, big or small.</p>
            <div class="cta-buttons">
                <a href="signup.php" class="cta-btn">
                    <i data-heroicon-solid="briefcase" class="h-5 w-5"></i>
                    Find Your Pro
                </a>
                <a href="signup.php" class="cta-btn secondary">
                    <i data-heroicon-solid="sparkles" class="h-5 w-5"></i>
                    Offer Your Skills
                </a>
            </div>
        </div>
    </header>

    <main>
        <section id="features" class="section">
            <div class="container">
                <h2>The New Standard for Trust & Convenience</h2>
                <p>We're not just another marketplace. We're a community built on security, quality, and opportunity for every Nigerian.</p>
                <div class="feature-grid">
                    <div class="feature-card magic-card">
                        <i data-heroicon-outline="shield-check" class="feature-icon"></i>
                        <h3>Identity-Verified Lancers</h3>
                        <p>Our robust verification process includes NIN, BVN, and Face ID, ensuring you hire only trusted and authentic professionals.</p>
                    </div>
                    <div class="feature-card magic-card">
                        <i data-heroicon-outline="map-pin" class="feature-icon"></i>
                        <h3>Hyperlocal & Digital Hub</h3>
                        <p>Find a skilled plumber down the street or a talented graphic designer across the country. We bridge the gap between local needs and digital skills.</p>
                    </div>
                    <div class="feature-card magic-card">
                         <i data-heroicon-outline="credit-card" class="feature-icon"></i>
                        <h3>Secure Escrow Payments</h3>
                        <p>Funds are held securely in escrow and only released when you are 100% satisfied with the work. Peace of mind, guaranteed.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="how-it-works" class="section alt-bg">
             <div class="container">
                <h2>Get Started in 3 Simple Steps</h2>
                <div class="how-it-works-grid">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h3>Post Your Task</h3>
                        <p>Describe what you need, whether it's a home repair, a new logo, or a virtual assistant. It's free and takes minutes.</p>
                    </div>
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h3>Get Matched</h3>
                        <p>Our smart algorithm connects you with qualified Lancers based on their skills, verification status, and proximity for local jobs.</p>
                    </div>
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h3>Hire & Collaborate</h3>
                        <p>Review profiles, chat with Lancers, and hire the perfect fit. Manage everything seamlessly on our platform until the job is done.</p>
                    </div>
                </div>
            </div>
        </section>


        <section id="why-us" class="section">
            <div class="container">
                <h2>Powering Nigeria's Gig Economy</h2>
                <p>The future of work is here. We are tapping into a massive market of skilled individuals and connecting them with endless opportunities.</p>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>36M+</h3>
                        <p>Informal Workers in Nigeria</p>
                    </div>
                    <div class="stat-card">
                        <h3>64%</h3>
                        <p>Smartphone Penetration</p>
                    </div>
                    <div class="stat-card">
                        <h3>$400B+</h3>
                        <p>Total Addressable Market</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="join" class="cta-section">
            <div class="container">
                <h2>Ready to Bring Your Ideas to Life?</h2>
                <p>Join thousands of Nigerians getting more done and earning more money. Your next great hire or exciting project is just a click away.</p>
                <div class="cta-buttons">
                    <a href="signup.php" class="cta-btn">
                        <i data-heroicon-solid="briefcase" class="h-5 w-5"></i>
                        Hire a Lancer Now
                    </a>
                    <a href="signup.php" class="cta-btn secondary">
                        <i data-heroicon-solid="sparkles" class="h-5 w-5"></i>
                        Start Earning Today
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="logo">Chavings Tasker</div>
            <p>&copy; <?php echo date("Y"); ?> Chavings Tasker. All rights reserved.</p>
            <p>Built by Adejare Akolawole & Adeyemi Adeniji | <a href="mailto:chavingsinc@gmail.com">chavingsinc@gmail.com</a></p>
            <div class="social-links">
                <a href="#" aria-label="Instagram"><i data-heroicon-solid="instagram-logo-replace-me"></i></a>
                <a href="#" aria-label="X (Twitter)"><i data-heroicon-solid="x-logo-replace-me"></i></a>
                <a href="#" aria-label="LinkedIn"><i data-heroicon-solid="linkedin-logo-replace-me"></i></a>
            </div>
        </div>
    </footer>

    <script>
        // --- Theme Toggle ---
        const themeToggleBtn = document.getElementById('theme-toggle-btn');
        const sunIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.95-4.243-1.591 1.591M5.25 12H3m4.243-4.95L6.343 6.343m5.657 5.657a3 3 0 1 0-5.657-2.474 3 3 0 0 0 5.657 2.474Z" /></svg>`;
        const moonIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" /></svg>`;

        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            themeToggleBtn.innerHTML = theme === 'dark' ? sunIcon : moonIcon;
            localStorage.setItem('theme', theme);
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            applyTheme(currentTheme === 'light' ? 'dark' : 'light');
        }
        
        applyTheme(localStorage.getItem('theme') || 'light');

        const navLinks = document.getElementById('nav-links');
        const hamburgerBtn = document.getElementById('hamburger-btn');
        function toggleMenu() {
            navLinks.classList.toggle('active');
            hamburgerBtn.classList.toggle('active');
        }

        function smoothScroll(targetId) {
            const target = document.getElementById(targetId);
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 120, // Increased offset for new nav
                    behavior: 'smooth'
                });
                if (navLinks.classList.contains('active')) {
                    toggleMenu();
                }
            }
        }

        const sections = document.querySelectorAll('.section, .cta-section');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.15 });

        sections.forEach(section => observer.observe(section));
        
        const canvas = document.getElementById('particle-canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        let particles = [];
        const particleCount = Math.min(Math.floor(window.innerWidth / 30), 50);

        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 2.5 + 1;
                this.speedX = Math.random() * 0.4 - 0.2;
                this.speedY = Math.random() * 0.4 - 0.2;
                this.opacity = Math.random() * 0.5 + 0.2;
            }
            update() {
                this.x += this.speedX;
                this.y += this.speedY;
                if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
                if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;
            }
            draw() {
                ctx.fillStyle = `rgba(107, 70, 193, ${this.opacity})`;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        function initParticles() {
            particles = [];
            for (let i = 0; i < particleCount; i++) {
                particles.push(new Particle());
            }
        }

        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach(p => { p.update(); p.draw(); });
            requestAnimationFrame(animateParticles);
        }

        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            initParticles();
        });

        initParticles();
        animateParticles();
    </script>
</body>
</html>