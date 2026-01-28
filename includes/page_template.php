<?php
// Page Template Helper
// This file is meant to be included by specific page files (e.g., hair-clinics.php)
// The variables $page_title, $page_subtitle, $page_content, $page_image should be set before including this.

$page_title = $page_title ?? 'Service Page';
$page_subtitle = $page_subtitle ?? 'Expert solutions for your business.';
$page_image = $page_image ?? 'https://adzodigital.com/wp-content/uploads/2023/07/adzo-seo.png'; // Default image

require_once __DIR__ . '/header.php';
?>

<!-- Hero Section -->
<section class="relative pt-32 pb-20 lg:pt-40 lg:pb-28 overflow-hidden bg-slate-900 text-white">
    <div class="absolute inset-0 bg-adzo/10"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-slate-900/50 to-slate-900"></div>

    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-20"
        style="background-image: radial-gradient(#fa8205 1px, transparent 1px); background-size: 32px 32px;">
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <h1 class="text-4xl lg:text-6xl font-black mb-6 leading-tight">
            <?php echo $page_title; ?>
        </h1>
        <p class="text-xl text-slate-300 max-w-2xl mx-auto leading-relaxed">
            <?php echo $page_subtitle; ?>
        </p>
        <div class="mt-10 flex flex-col sm:flex-row justify-center gap-4">
            <a href="#contact"
                class="bg-adzo hover:bg-adzo-dark text-white px-8 py-4 rounded-2xl text-lg font-bold shadow-2xl shadow-orange-900/20 transition-all hover:scale-105">
                Book Free Consultation
            </a>
            <a href="https://wa.me/918368051069" target="_blank"
                class="bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 text-white px-8 py-4 rounded-2xl text-lg font-bold transition-all hover:scale-105 flex items-center justify-center gap-2">
                <svg class="w-5 h-5 text-whatsapp" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.246 2.248 3.484 5.232 3.484 8.412 0 6.556-5.338 11.892-11.893 11.892-1.997-.001-3.951-.5-5.688-1.448l-6.309 1.656zm6.224-3.92s.589.346 3.177 1.251c1.276.444 2.622.674 3.98.674 5.456 0 9.897-4.442 9.897-9.897 0-2.643-1.029-5.128-2.902-7.001-1.874-1.874-4.359-2.903-7.001-2.903-5.456 0-9.897 4.441-9.897 9.897-.001 2.12.68 4.14 1.954 5.808l-.942 3.442 3.734-.98z" />
                </svg>
                WhatsApp Chat
            </a>
        </div>
    </div>
</section>

<!-- Content Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="reveal">
                <?php if (isset($content_html)): ?>
                    <?php echo $content_html; ?>
                <?php else: ?>
                    <h2 class="text-3xl font-black text-slate-900 mb-6">Why Choose Adzo for
                        <?php echo $page_title; ?>?
                    </h2>
                    <p class="text-lg text-slate-600 mb-6 leading-relaxed">
                        We specialize in digital growth for the
                        <?php echo $page_title; ?> industry. Our data-driven strategies ensure you get high-quality leads
                        that convert into paying clients.
                    </p>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center gap-3">
                            <span class="text-adzo text-xl">✓</span>
                            <span class="font-bold text-slate-700">Industry-Specific Strategies</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="text-adzo text-xl">✓</span>
                            <span class="font-bold text-slate-700">High-Intent Lead Generation</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="text-adzo text-xl">✓</span>
                            <span class="font-bold text-slate-700">Proven ROI Track Record</span>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="reveal" style="transition-delay: 100ms;">
                <div class="relative rounded-[2.5rem] overflow-hidden shadow-2xl border-4 border-white">
                    <img src="<?php echo $page_image; ?>" alt="<?php echo $page_title; ?>"
                        class="w-full h-auto object-cover transform hover:scale-105 transition duration-700">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Include Contact Form -->
<?php require_once __DIR__ . '/contact_form.php'; ?>

<?php require_once __DIR__ . '/footer.php'; ?>