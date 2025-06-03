<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stephen Moore | Web Developer Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark: '#0f172a',
                        darker: '#020617',
                        orange: '#f97316',
                        green: '#22c55e',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
            color: #e2e8f0;
            scroll-behavior: smooth;
        }
        
        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(249, 115, 22, 0.2);
        }
        
        .gradient-text {
            background: linear-gradient(90deg, #f97316, #f59e0b);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .nav-link::after {
            content: '';
            display: block;
            width: 0;
            height: 2px;
            background: #f97316;
            transition: width 0.3s;
        }
        
        .skill-pill:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Navigation -->
    <nav class="bg-darker/80 backdrop-blur-md fixed w-full z-50 border-b border-orange/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-orange text-2xl font-bold">Undead<span class="text-white">Token</span></span>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-8">
                        <a href="#home" class="nav-link text-gray-300 hover:text-orange px-3 py-2 rounded-md text-sm font-medium">Home</a>
                        <a href="#about" class="nav-link text-gray-300 hover:text-orange px-3 py-2 rounded-md text-sm font-medium">About</a>
                        <a href="#projects" class="nav-link text-gray-300 hover:text-orange px-3 py-2 rounded-md text-sm font-medium">Projects</a>
                        <a href="#contact" class="nav-link text-gray-300 hover:text-orange px-3 py-2 rounded-md text-sm font-medium">Contact</a>
                    </div>
                </div>
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-gray-300 hover:text-orange focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-darker border-t border-orange/10">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="#home" class="text-gray-300 hover:text-orange block px-3 py-2 rounded-md text-base font-medium">Home</a>
                <a href="#about" class="text-gray-300 hover:text-orange block px-3 py-2 rounded-md text-base font-medium">About</a>
                <a href="#projects" class="text-gray-300 hover:text-orange block px-3 py-2 rounded-md text-base font-medium">Projects</a>
                <a href="#contact" class="text-gray-300 hover:text-orange block px-3 py-2 rounded-md text-base font-medium">Contact</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div class="md:w-1/2 mb-10 md:mb-0">
                <h1 class="text-4xl md:text-6xl font-bold mb-4">
                    Hi, I'm <span class="gradient-text">Stephen</span>
                </h1>
                <h2 class="text-2xl md:text-3xl font-semibold mb-6 text-gray-300">
                    Full Stack <span class="text-orange">Web Developer</span>
                </h2>
                <p class="text-gray-400 mb-8 text-lg">
                    I build exceptional digital experiences that are fast, accessible, and visually appealing.
                </p>
                <div class="flex space-x-4">
                    <a href="#projects" class="bg-orange hover:bg-orange/90 text-white px-6 py-3 rounded-lg font-medium transition duration-300">
                        View My Work
                    </a>
                    <a href="#contact" class="border border-orange text-orange hover:bg-orange/10 px-6 py-3 rounded-lg font-medium transition duration-300">
                        Contact Me
                    </a>
                </div>
            </div>
            <div class="md:w-1/2 flex justify-center">
                <div class="relative">
                    <div class="w-64 h-64 md:w-80 md:h-80 bg-orange/10 rounded-full flex items-center justify-center">
                        <div class="w-56 h-56 md:w-72 md:h-72 bg-orange/20 rounded-full flex items-center justify-center">
                            <div class="w-48 h-48 md:w-64 md:h-64 bg-orange/30 rounded-full flex items-center justify-center overflow-hidden border-2 border-orange/40">
                                <img src="static\gallery\profilepicture.jpg" 
                                     alt="Developer" 
                                     class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>
                    <div class="absolute -bottom-5 -right-5 bg-darker p-3 rounded-lg border border-orange/20 shadow-lg">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green rounded-full mr-2"></div>
                            <span class="text-sm font-medium">Available for work</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto bg-darker/50 rounded-xl my-10">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">
                <span class="border-b-2 border-orange pb-2">About Me</span>
            </h2>
            <p class="text-gray-400 max-w-3xl mx-auto">
                I'm passionate about creating beautiful, functional websites and applications.I am father of three wonderful kids and a husband to a beautiful wife. I love spending time with my family, playing games, and exploring the great outdoors.
            </p>
        </div>
        
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-10 md:mb-0 md:pr-10">
                <p class="text-gray-300 mb-6">
                    With over 3 years of experience in development, I specialize in creating responsive, user-friendly websites and web applications. My approach combines technical expertise with creative problem-solving to deliver exceptional results.
                </p>
                <p class="text-gray-300 mb-6">
                    I believe in writing clean, efficient code and staying up-to-date with the latest technologies and best practices in the industry. My goal is to create digital experiences that not only look great but also perform exceptionally well.
                </p>
                <p class="text-gray-300">
                    When I'm not coding, you can find me hiking, reading sci-fi novels, experimenting with new recipes in the kitchen, and playing games with my family.
                </p>
            </div>
            <div class="md:w-1/2">
                <h3 class="text-xl font-semibold mb-6 text-orange">My Skills</h3>
                <div class="flex flex-wrap gap-3">
                    <span class="skill-pill bg-orange/10 text-orange px-4 py-2 rounded-full text-sm font-medium transition duration-300">HTML5</span>
                    <span class="skill-pill bg-orange/10 text-orange px-4 py-2 rounded-full text-sm font-medium transition duration-300">CSS</span>
                    <span class="skill-pill bg-orange/10 text-orange px-4 py-2 rounded-full text-sm font-medium transition duration-300">JavaScript</span>
                    <span class="skill-pill bg-orange/10 text-orange px-4 py-2 rounded-full text-sm font-medium transition duration-300">Go programming language</span>
                    <span class="skill-pill bg-orange/10 text-orange px-4 py-2 rounded-full text-sm font-medium transition duration-300">python</span>
                    <span class="skill-pill bg-orange/10 text-orange px-4 py-2 rounded-full text-sm font-medium transition duration-300">Express</span>
                    <span class="skill-pill bg-orange/10 text-orange px-4 py-2 rounded-full text-sm font-medium transition duration-300">MySQL</span>
                    <span class="skill-pill bg-orange/10 text-orange px-4 py-2 rounded-full text-sm font-medium transition duration-300">Git</span>
                </div>
                
                <div class="mt-10">
                    <h3 class="text-xl font-semibold mb-6 text-orange">Experience</h3>
                    <div class="space-y-6">
                        <div class="border-l-2 border-orange pl-4">
                            <h4 class="font-medium text-lg">Web Developer</h4>
                            <p class="text-gray-400 text-sm">LI Realty. • 2020</p>
                            <p class="text-gray-300 mt-2">Leading front-end development.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section id="projects" class="py-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">
                <span class="border-b-2 border-orange pb-2">My Projects</span>
            </h2>
            <p class="text-gray-400 max-w-3xl mx-auto">
                Here are some of my recent projects. Each one was built with care and attention to detail.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Project 1 -->
            <div class="project-card bg-darker rounded-xl overflow-hidden border border-orange/10 transition duration-300">
                <div class="h-48 overflow-hidden">
                    <img src="static\gallery\SequenceAnalizer.jpg" 
                         alt="Routing and Sequence Analizer" 
                         class="w-full h-full object-cover hover:scale-105 transition duration-500">
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="text-xl font-bold">Routing and sequence analyzer</h3>
                        <span class="text-xs bg-orange/10 text-orange px-2 py-1 rounded">PHP, JavaScript</span>
                    </div>
                    <p class="text-gray-400 mb-4">
                        Web application for a client to visualize and interact with geographic datasets overlayed on a map.
                    </p>
                    <div class="flex justify-between items-center">
                        <a href="/projects/RASA.html" class="text-orange hover:text-orange/80 font-medium flex items-center">
                            View Project <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                        <div class="flex space-x-2">
                            <a href="https://github.com/UndeadTokenArt/WMscheduler" class="text-gray-400 hover:text-orange">
                                <i class="fab fa-github"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-orange">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Project 2 -->
            <div class="project-card bg-darker rounded-xl overflow-hidden border border-orange/10 transition duration-300">
                <div class="h-48 overflow-hidden">
                    <img src="static\gallery\GameMasterTools.jpg" 
                         alt="Game Master Tools" 
                         class="w-full h-full object-cover hover:scale-105 transition duration-500">
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="text-xl font-bold">Game Master Tools</h3>
                        <span class="text-xs bg-orange/10 text-orange px-2 py-1 rounded">PHP, JavaScript</span>
                    </div>
                    <p class="text-gray-400 mb-4">
                        Displays information (e.g., maps, character stats, notes) to players on a shared screen during tabletop RPG sessions.
                    </p>
                    <div class="flex justify-between items-center">
                        <a href="projects/Viewer.html" class="text-orange hover:text-orange/80 font-medium flex items-center">
                            View Project <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                        <div class="flex space-x-2">
                            <a href="https://github.com/UndeadTokenArt/InitiativeDrop" class="text-gray-400 hover:text-orange">
                                <i class="fab fa-github"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-orange">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        
        <div class="text-center mt-16">
            <a href="#" class="inline-flex items-center px-6 py-3 border border-orange text-orange hover:bg-orange/10 rounded-lg font-medium transition duration-300">
                <i class="fab fa-github mr-2"></i> View All Projects on GitHub
            </a>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto bg-darker/50 rounded-xl my-10">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">
                <span class="border-b-2 border-orange pb-2">Get In Touch</span>
            </h2>
            <p class="text-gray-400 max-w-3xl mx-auto">
                Have a project in mind or want to discuss potential opportunities? I'd love to hear from you!
            </p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-10">

            
            <div class="md:w-1/2">
                <div class="bg-dark p-8 rounded-xl h-full border border-orange/10">
                    <h3 class="text-xl font-semibold mb-6 text-orange">Contact Information</h3>
                    
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 bg-orange/10 p-3 rounded-lg text-orange">
                                <i class="fas fa-envelope text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-medium text-gray-400">Email</h4>
                                <p class="text-gray-300">MooreStephenC@gmail.com</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 bg-orange/10 p-3 rounded-lg text-orange">
                                <i class="fas fa-phone-alt text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-medium text-gray-400">Phone</h4>
                                <p class="text-gray-300">+1 (503) 360-7678</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 bg-orange/10 p-3 rounded-lg text-orange">
                                <i class="fas fa-map-marker-alt text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-medium text-gray-400">Location</h4>
                                <p class="text-gray-300">Portland, OR</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-10">
                        <h3 class="text-xl font-semibold mb-6 text-orange">Follow Me</h3>
                        <div class="flex space-x-4">
                            <a href="https://github.com/undeadtokenart/" class="w-10 h-10 bg-orange/10 hover:bg-orange/20 text-orange rounded-full flex items-center justify-center transition duration-300">
                                <i class="fab fa-github"></i>
                            </a>
                            <a href="https://www.linkedin.com/in/stephen-moore-b3865b37/" class="w-10 h-10 bg-orange/10 hover:bg-orange/20 text-orange rounded-full flex items-center justify-center transition duration-300">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-darker py-10 border-t border-orange/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-6 md:mb-0">
                    <span class="text-orange text-2xl font-bold">Undead<span class="text-white">Token</span></span>
                </div>
                <div class="flex flex-col items-center md:items-end">
                    <p class="text-gray-400 text-sm mb-2">
                        © 2023 Stephen Moore. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    // Close mobile menu if open
                    const mobileMenu = document.getElementById('mobile-menu');
                    if (!mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.add('hidden');
                    }
                    
                    // Scroll to target
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Add shadow to navbar on scroll
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('nav');
            if (window.scrollY > 10) {
                nav.classList.add('shadow-lg');
            } else {
                nav.classList.remove('shadow-lg');
            }
        });
    </script>
</body>
</html>