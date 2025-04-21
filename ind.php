<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus Notes - Your Digital Note-Taking Solution</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #007bff;
            --secondary: #ff9800;
            --accent: #0288d1;
            --background: linear-gradient(135deg, #f0f7ff, #e3f2fd);
            --card-bg: #ffffff;
            --shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        body {
            font-family: 'Arial', sans-serif;
            background: var(--background);
            color: #333;
            overflow-x: hidden;
        }
        .landing-page {
            position: relative;
            z-index: 1;
        }
        .section {
            padding: 100px 0;
            position: relative;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            margin: 20px;
            overflow: hidden;
        }
        .section-content {
            position: relative;
            z-index: 2;
        }
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease-out, transform 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .bounce-in {
            animation: bounceIn 1.2s ease-out;
        }
        @keyframes bounceIn {
            0% { transform: scale(0.5); opacity: 0; }
            60% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); }
        }
        .slide-in {
            animation: slideIn 1s ease-out;
        }
        @keyframes slideIn {
            0% { transform: translateX(-100%); opacity: 0; }
            100% { transform: translateX(0); opacity: 1; }
        }
        .scroll-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary);
            color: #fff;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.4s ease, transform 0.3s ease;
            box-shadow: var(--shadow);
            z-index: 1000;
        }
        .scroll-indicator.visible {
            opacity: 1;
            transform: scale(1.1);
        }
        .scroll-indicator:hover {
            transform: scale(1.2);
            background: #0056b3;
        }
        .feature-item {
            padding: 25px;
            border-radius: 15px;
            background: var(--card-bg);
            box-shadow: var(--shadow);
            transition: transform 0.5s ease, box-shadow 0.5s ease, background 0.3s ease;
            text-align: center;
        }
        .feature-item:hover {
            transform: translateY(-10px) rotate(2deg);
            box-shadow: var(--hover-shadow);
            background: linear-gradient(135deg, #e3f2fd, #f0f7ff);
        }
        .feature-item svg {
            transition: transform 0.5s ease, fill 0.3s ease;
        }
        .feature-item:hover svg {
            transform: scale(1.2) rotate(-5deg);
            fill: var(--secondary);
        }
        .testimonial-item {
            padding: 25px;
            border-radius: 15px;
            background: var(--card-bg);
            box-shadow: var(--shadow);
            transition: transform 0.5s ease, box-shadow 0.5s ease;
            margin-bottom: 30px;
        }
        .testimonial-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }
        .cta-section {
            background: linear-gradient(135deg, var(--primary), #00c4ff);
            color: #fff;
            text-align: center;
            padding: 80px 0;
            border-radius: 15px;
            box-shadow: var(--shadow);
            animation: gradientShift 10s infinite alternate;
        }
        @keyframes gradientShift {
            0% { background: linear-gradient(135deg, var(--primary), #00c4ff); }
            100% { background: linear-gradient(135deg, #00c4ff, var(--accent)); }
        }
        .cta-section .btn {
            font-size: 1.3rem;
            padding: 15px 40px;
            background: #fff;
            color: var(--primary);
            border: none;
            border-radius: 25px;
            transition: transform 0.5s ease, background 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .cta-section .btn:hover {
            transform: scale(1.1) translateY(-3px);
            background: var(--secondary);
            color: #fff;
            box-shadow: 0 6px 15px rgba(255, 152, 0, 0.4);
        }
        h1, h2 {
            color: var(--accent);
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
        }
        .lead {
            color: #555;
            font-size: 1.2rem;
        }
        svg {
            max-width: 100%;
            height: auto;
        }
        @media (max-width: 768px) {
            .section {
                padding: 60px 0;
                margin: 10px;
            }
            .feature-item {
                margin-bottom: 20px;
            }
            .cta-section .btn {
                font-size: 1.1rem;
                padding: 12px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="landing-page">
        <!-- Scroll to Top Button -->
        <div class="scroll-indicator bounce-in" id="scrollToTop">
            <i class="fas fa-arrow-up"></i>
        </div>

        <!-- Hero Section -->
        <section class="section" id="hero">
                    <div class="section-content">
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-lg-6">
                                    <h1 class="fade-in">Welcome to Nexus Notes</h1>
                                    <p class="lead fade-in">Your all-in-one solution for digital note-taking and organization.</p>
                                    <div class="mt-4 fade-in">
                                        <a href="login.php" class="btn btn-primary me-2">Login</a>
                                        <a href="signup.php" class="btn btn-outline-primary">Sign Up</a>
                                    </div>
                                </div>
                                <div class="col-lg-6 fade-in">
                                    <img src="assets/images/notes-illustration.svg" alt="Notes Illustration" class="img-fluid">
                                    <!-- Add another SVG -->
                                    <!-- <img src="assets/images/organize-illustration.svg" alt="Organize Illustration" class="img-fluid mt-4"> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

        <!-- Features Section -->
        <section class="section" id="features">
            <div class="section-content">
                <div class="container">
                    <h2 class="text-center mb-5 fade-in">Key Features</h2>
                    <div class="row">
                        <div class="col-md-4 fade-in">
                            <div class="feature-item">
                                <svg width="50" height="50" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" aria-label="Folder Icon">
                                    <defs>
                                        <linearGradient id="folderIconGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:#4fc3f7;stop-opacity:1"/>
                                            <stop offset="100%" style="stop-color:#0288d1;stop-opacity:1"/>
                                        </linearGradient>
                                        <filter id="shadow3">
                                            <feDropShadow dx="1" dy="1" stdDeviation="1" flood-opacity="0.3"/>
                                        </filter>
                                    </defs>
                                    <path d="M5 12 H15 L20 17 H45 V38 H5 Z" fill="url(#folderIconGrad)" stroke="#01579b" stroke-width="1" filter="url(#shadow3)"/>
                                    <path d="M15 12 L20 17 H45" fill="none" stroke="#ffffff" stroke-width="1" stroke-opacity="0.5"/>
                                </svg>
                                <h4>Organize Notes</h4>
                                <p>Create folders and tags with ease.</p>
                            </div>
                        </div>
                        <div class="col-md-4 fade-in">
                            <div class="feature-item">
                                <svg width="50" height="50" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" aria-label="Search Icon">
                                    <defs>
                                        <linearGradient id="searchGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:#4fc3f7;stop-opacity:1"/>
                                            <stop offset="100%" style="stop-color:#0288d1;stop-opacity:1"/>
                                        </linearGradient>
                                        <filter id="shadow4">
                                            <feDropShadow dx="1" dy="1" stdDeviation="1" flood-opacity="0.3"/>
                                        </filter>
                                    </defs>
                                    <circle cx="22" cy="22" r="14" fill="none" stroke="url(#searchGrad)" stroke-width="3" filter="url(#shadow4)"/>
                                    <path d="M30 30 L40 40" stroke="url(#searchGrad)" stroke-width="3" stroke-linecap="round" filter="url(#shadow4)"/>
                                    <circle cx="22" cy="22" r="10" fill="none" stroke="#ffffff" stroke-width="1" stroke-opacity="0.5"/>
                                </svg>
                                <h4>Quick Search</h4>
                                <p>AI-driven search for instant results.</p>
                            </div>
                        </div>
                        <div class="col-md-4 fade-in">
                            <div class="feature-item">
                                <svg width="50" height="50" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" aria-label="Dark Mode Icon">
                                    <defs>
                                        <linearGradient id="moonGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:#b3e5fc;stop-opacity:1"/>
                                            <stop offset="100%" style="stop-color:#0288d1;stop-opacity:1"/>
                                        </linearGradient>
                                        <filter id="shadow5">
                                            <feDropShadow dx="1" dy="1" stdDeviation="1" flood-opacity="0.3"/>
                                        </filter>
                                    </defs>
                                    <circle cx="25" cy="25" r="15" fill="#263238" filter="url(#shadow5)"/>
                                    <path d="M25 10 A15 15 0 0 1 40 25 A15 15 0 0 1 25 40 C20 40 15 35 15 25 C15 15 20 10 25 10" fill="url(#moonGrad)"/>
                                    <circle cx="35" cy="15" r="1" fill="#ffffff"/>
                                    <circle cx="40" cy="20" r="1.5" fill="#ffffff"/>
                                    <circle cx="38" cy="25" r="1" fill="#ffffff"/>
                                </svg>
                                <h4>Dark Mode</h4>
                                <p>Eye-friendly dark theme toggle.</p>
                            </div>
                        </div>
                        <div class="col-md-4 fade-in">
                            <div class="feature-item">
                                <svg width="50" height="50" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" aria-label="Chatbot Icon">
                                    <defs>
                                        <linearGradient id="chatGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:#4fc3f7;stop-opacity:1"/>
                                            <stop offset="100%" style="stop-color:#0288d1;stop-opacity:1"/>
                                        </linearGradient>
                                        <filter id="shadow10">
                                            <feDropShadow dx="1" dy="1" stdDeviation="1" flood-opacity="0.3"/>
                                        </filter>
                                    </defs>
                                    <rect x="10" y="10" width="30" height="30" rx="5" fill="url(#chatGrad)" stroke="#01579b" stroke-width="1" filter="url(#shadow10)"/>
                                    <circle cx="20" cy="20" r="3" fill="#fff"/>
                                    <circle cx="30" cy="20" r="3" fill="#fff"/>
                                    <path d="M15 30 Q20 35 25 30" fill="none" stroke="#fff" stroke-width="2"/>
                                </svg>
                                <h4>AI Chatbot (Nexus)</h4>
                                <p>Get help with queries and tips.</p>
                            </div>
                        </div>
                        <div class="col-md-4 fade-in">
                            <div class="feature-item">
                                <svg width="50" height="50" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" aria-label="Quill Icon">
                                    <defs>
                                        <linearGradient id="quillGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:#4fc3f7;stop-opacity:1"/>
                                            <stop offset="100%" style="stop-color:#0288d1;stop-opacity:1"/>
                                        </linearGradient>
                                        <filter id="shadow11">
                                            <feDropShadow dx="1" dy="1" stdDeviation="1" flood-opacity="0.3"/>
                                        </filter>
                                    </defs>
                                    <path d="M10 40 L40 10 L45 15 L15 45 Z" fill="url(#quillGrad)" stroke="#01579b" stroke-width="1" filter="url(#shadow11)"/>
                                    <circle cx="15" cy="40" r="5" fill="#fff"/>
                                </svg>
                                <h4>Rich Content Editing</h4>
                                <p>Edit with Quill and upload images.</p>
                            </div>
                        </div>
                        <div class="col-md-4 fade-in">
                            <div class="feature-item">
                                <svg width="50" height="50" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" aria-label="Download Icon">
                                    <defs>
                                        <linearGradient id="downloadGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:#4fc3f7;stop-opacity:1"/>
                                            <stop offset="100%" style="stop-color:#0288d1;stop-opacity:1"/>
                                        </linearGradient>
                                        <filter id="shadow12">
                                            <feDropShadow dx="1" dy="1" stdDeviation="1" flood-opacity="0.3"/>
                                        </filter>
                                    </defs>
                                    <rect x="15" y="10" width="20" height="25" rx="3" fill="url(#downloadGrad)" stroke="#01579b" stroke-width="1" filter="url(#shadow12)"/>
                                    <path d="M20 35 L25 40 L30 35" fill="none" stroke="#fff" stroke-width="2"/>
                                </svg>
                                <h4>Note Download</h4>
                                <p>Download notes for offline use.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="section" id="benefits">
            <div class="section-content">
                <div class="container">
                    <h2 class="text-center mb-5 fade-in">Why Choose Nexus Notes?</h2>
                    <div class="row">
                        <div class="col-md-4 fade-in">
                            <div class="feature-item text-center">
                                <svg width="50" height="50" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" aria-label="Security Icon">
                                    <defs>
                                        <linearGradient id="lockGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:#4fc3f7;stop-opacity:1"/>
                                            <stop offset="100%" style="stop-color:#0288d1;stop-opacity:1"/>
                                        </linearGradient>
                                        <filter id="shadow6">
                                            <feDropShadow dx="1" dy="1" stdDeviation="1" flood-opacity="0.3"/>
                                        </filter>
                                    </defs>
                                    <rect x="15" y="20" width="20" height="20" rx="2" fill="url(#lockGrad)" stroke="#01579b" stroke-width="1" filter="url(#shadow6)"/>
                                    <path d="M25 20 V15 A5 5 0 0 0 20 10 A5 5 0 0 0 15 15 V20" fill="none" stroke="#01579b" stroke-width="2" filter="url(#shadow6)"/>
                                </svg>
                                <h4>Secure & Private</h4>
                                <p>Encrypted with top security standards.</p>
                            </div>
                        </div>
                        <div class="col-md-4 fade-in">
                            <div class="feature-item text-center">
                                <svg width="50" height="50" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" aria-label="Sync Icon">
                                    <defs>
                                        <linearGradient id="syncGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:#4fc3f7;stop-opacity:1"/>
                                            <stop offset="100%" style="stop-color:#0288d1;stop-opacity:1"/>
                                        </linearGradient>
                                        <filter id="shadow7">
                                            <feDropShadow dx="1" dy="1" stdDeviation="1" flood-opacity="0.3"/>
                                        </filter>
                                    </defs>
                                    <path d="M35 15 A15 15 0 0 0 20 30 L15 25" fill="none" stroke="url(#syncGrad)" stroke-width="3" stroke-linecap="round" filter="url(#shadow7)"/>
                                    <path d="M15 35 A15 15 0 0 0 30 20 L35 25" fill="none" stroke="url(#syncGrad)" stroke-width="3" stroke-linecap="round" filter="url(#shadow7)"/>
                                </svg>
                                <h4>Sync Across Devices</h4>
                                <p>Seamless access anywhere.</p>
                            </div>
                        </div>
                        <div class="col-md-4 fade-in">
                            <div class="feature-item text-center">
                                <svg width="50" height="50" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" aria-label="Collaboration Icon">
                                    <defs>
                                        <linearGradient id="collabGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:#4fc3f7;stop-opacity:1"/>
                                            <stop offset="100%" style="stop-color:#0288d1;stop-opacity:1"/>
                                        </linearGradient>
                                        <filter id="shadow8">
                                            <feDropShadow dx="1" dy="1" stdDeviation="1" flood-opacity="0.3"/>
                                        </filter>
                                    </defs>
                                    <circle cx="20" cy="20" r="8" fill="url(#collabGrad)" stroke="#01579b" stroke-width="1" filter="url(#shadow8)"/>
                                    <circle cx="30" cy="30" r="8" fill="url(#collabGrad)" stroke="#01579b" stroke-width="1" filter="url(#shadow8)"/>
                                    <path d="M20 28 L30 22" stroke="#01579b" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <h4>Collaboration</h4>
                                <p>Real-time team sharing.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="section" id="testimonials">
            <div class="section-content">
                <div class="container">
                    <h2 class="text-center mb-5 fade-in">What Our Users Say</h2>
                    <div class="row">
                        <div class="col-md-6 fade-in">
                            <div class="testimonial-item">
                                <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg" aria-label="Quote Icon" class="mb-3">
                                    <defs>
                                        <linearGradient id="quoteGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:#4fc3f7;stop-opacity:1"/>
                                            <stop offset="100%" style="stop-color:#0288d1;stop-opacity:1"/>
                                        </linearGradient>
                                    </defs>
                                    <path d="M10 15 L10 25 L15 30 L20 25 V15 H10 Z M20 15 L20 25 L25 30 L30 25 V15 H20 Z" fill="url(#quoteGrad)"/>
                                </svg>
                                <p>"Nexus Notes transformed my workflow with its AI search!"</p>
                                <h5 class="mt-3">Jane Doe, Project Manager</h5>
                            </div>
                        </div>
                        <div class="col-md-6 fade-in">
                            <div class="testimonial-item">
                                <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg" aria-label="Quote Icon" class="mb-3">
                                    <defs>
                                        <linearGradient id="quoteGrad2" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:#4fc3f7;stop-opacity:1"/>
                                            <stop offset="100%" style="stop-color:#0288d1;stop-opacity:1"/>
                                        </linearGradient>
                                    </defs>
                                    <path d="M10 15 L10 25 L15 30 L20 25 V15 H10 Z M20 15 L20 25 L25 30 L30 25 V15 H20 Z" fill="url(#quoteGrad2)"/>
                                </svg>
                                <p>"Dark mode and sync are perfect for my studies!"</p>
                                <h5 class="mt-3">John Smith, Student</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action Section -->
        <section class="section cta-section" id="cta">
            <div class="section-content">
                <div class="container">
                    <h2 class="fade-in mb-4">Ready to Boost Your Productivity?</h2>
                    <p class="lead fade-in mb-5">Join thousands of users with Nexus Notes today!</p>
                    <div class="fade-in">
                        <svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg" aria-label="Rocket Icon" class="mb-4">
                            <defs>
                                <linearGradient id="rocketGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#ffffff;stop-opacity:1"/>
                                    <stop offset="100%" style="stop-color:#e0f7fa;stop-opacity:1"/>
                                </linearGradient>
                                <filter id="shadow9">
                                    <feDropShadow dx="2" dy="2" stdDeviation="2" flood-opacity="0.3"/>
                                </filter>
                            </defs>
                            <path d="M30 10 L20 40 L30 50 L40 40 Z" fill="url(#rocketGrad)" stroke="#0288d1" stroke-width="2" filter="url(#shadow9)"/>
                            <path d="M25 40 L30 50 L35 40" fill="#ff6f00" stroke="#d84315" stroke-width="1"/>
                            <circle cx="30" cy="25" r="5" fill="#0288d1"/>
                        </svg>
                        <div>
                            <a href="signup.php" class="btn btn-light">Get Started Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Scroll to Top Functionality
        const scrollToTop = document.getElementById('scrollToTop');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollToTop.classList.add('visible');
            } else {
                scrollToTop.classList.remove('visible');
            }
        });

        scrollToTop.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Fade-in Animation
        const fadeElements = document.querySelectorAll('.fade-in');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.1
        });

        fadeElements.forEach(element => {
            observer.observe(element);
        });
    </script>
</body>
</html>