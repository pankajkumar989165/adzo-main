<?php
// Default values if not set
$page_title = $page_title ?? 'Adzo | Performance-Driven Digital Growth Agency';
$meta_description = $meta_description ?? 'Adzo helps local and service-based businesses turn clicks into customers using Lead Gen Ads, Local SEO, and ROI-focused marketing systems.';
$current_page = $current_page ?? 'home';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Primary Meta Tags -->
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="keywords" content="Digital Marketing, Lead Generation, Local SEO, Google Business Profile, Adzo Digital, Social Media Marketing">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://adzodigital.com/">
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta property="og:image" content="https://adzodigital.com/wp-content/uploads/2023/07/adzo-seo.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://adzodigital.com/">
    <meta property="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="twitter:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta property="twitter:image" content="https://adzodigital.com/wp-content/uploads/2023/07/adzo-seo.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        adzo: '#fa8205',
                        'adzo-dark': '#e07200',
                        'adzo-light': '#fff3e6',
                        'adzo-deep': '#1a1005',
                        'whatsapp': '#25D366'
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }

        .animate-float {
            animation: float 5s ease-in-out infinite;
        }

        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .text-gradient {
            background: linear-gradient(to right, #fa8205, #ffb057);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e2e8f0, transparent);
        }
    </style>
</head>

<body class="bg-[#fafafa] text-slate-900 overflow-x-hidden relative">

    <!-- Navigation -->
    <nav class="fixed w-full z-50 transition-all duration-300 border-b border-transparent bg-white/80 backdrop-blur-md" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex-shrink-0">
                    <a href="/index.php">
                        <img src="https://adzodigital.com/wp-content/uploads/2025/09/logo-adzo.png" alt="Adzo Logo" class="h-10 w-auto">
                    </a>
                </div>
                <!-- Desktop Navigation -->
                <div class="hidden md:flex space-x-6 lg:space-x-8 items-center">
                    <a href="/index.php#about" class="text-sm font-bold text-slate-600 hover:text-adzo transition">Who We Are</a>
                    <a href="/index.php#approach" class="text-sm font-bold text-slate-600 hover:text-adzo transition">Approach</a>
                    <a href="/index.php#services" class="text-sm font-bold text-slate-600 hover:text-adzo transition">Services</a>
                    <a href="/pages/industries.php" class="text-sm font-bold text-slate-600 hover:text-adzo transition <?php echo $current_page == 'industries' ? 'text-adzo' : ''; ?>">Industries</a>
                    <a href="https://adzodigital.com/blog/" target="_blank" class="text-sm font-bold text-slate-600 hover:text-adzo transition">Blog</a>

                    <div class="flex items-center gap-3 ml-2 lg:ml-4">
                        <a href="https://wa.me/918368051069" target="_blank"
                            class="bg-whatsapp hover:bg-[#20ba5a] text-white px-5 py-2.5 rounded-full font-bold text-sm transition shadow-lg shadow-green-100 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.246 2.248 3.484 5.232 3.484 8.412 0 6.556-5.338 11.892-11.893 11.892-1.997-.001-3.951-.5-5.688-1.448l-6.309 1.656zm6.224-3.92s.589.346 3.177 1.251c1.276.444 2.622.674 3.98.674 5.456 0 9.897-4.442 9.897-9.897 0-2.643-1.029-5.128-2.902-7.001-1.874-1.874-4.359-2.903-7.001-2.903-5.456 0-9.897 4.441-9.897 9.897-.001 2.12.68 4.14 1.954 5.808l-.942 3.442 3.734-.98z" />
                            </svg>
                            WhatsApp
                        </a>
                        <a href="https://wa.me/918368051069"
                            class="bg-adzo hover:bg-adzo-dark text-white px-5 py-2.5 rounded-full font-bold text-sm transition shadow-lg shadow-orange-200">
                            Growth Audit
                        </a>
                    </div>
                </div>
                <!-- Mobile Menu Button - for now just simplified -->
                 <div class="md:hidden flex items-center gap-2">
                    <a href="https://wa.me/918368051069" class="bg-whatsapp text-white px-4 py-1.5 rounded-full font-bold text-xs flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                           <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.246 2.248 3.484 5.232 3.484 8.412 0 6.556-5.338 11.892-11.893 11.892-1.997-.001-3.951-.5-5.688-1.448l-6.309 1.656zm6.224-3.92s.589.346 3.177 1.251c1.276.444 2.622.674 3.98.674 5.456 0 9.897-4.442 9.897-9.897 0-2.643-1.029-5.128-2.902-7.001-1.874-1.874-4.359-2.903-7.001-2.903-5.456 0-9.897 4.441-9.897 9.897-.001 2.12.68 4.14 1.954 5.808l-.942 3.442 3.734-.98z" />
                        </svg>
                        WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </nav>
