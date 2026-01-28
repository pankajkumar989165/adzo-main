/**
 * Blog Frontend Scripts
 */

document.addEventListener('DOMContentLoaded', function () {
    // Lazy loading for images
    const images = document.querySelectorAll('img[loading="lazy"]');
    if ('loading' in HTMLImageElement.prototype) {
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    } else {
        // Fallback for older browsers
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
        document.body.appendChild(script);
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Share button popup
    const shareBtns = document.querySelectorAll('.share-btn');
    shareBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (this.getAttribute('target') === '_blank') {
                e.preventDefault();
                const url = this.href;
                window.open(url, 'share-window', 'width=600,height=400,location=no,menubar=no,status=no,toolbar=no');
            }
        });
    });

    // Add scroll progress bar for single posts
    if (document.body.classList.contains('single-post-page')) {
        const progressBar = document.createElement('div');
        progressBar.style.position = 'fixed';
        progressBar.style.top = '0';
        progressBar.style.left = '0';
        progressBar.style.height = '4px';
        progressBar.style.background = 'var(--primary)';
        progressBar.style.zIndex = '1000';
        progressBar.style.width = '0%';
        progressBar.style.transition = 'width 0.1s';
        document.body.appendChild(progressBar);

        window.addEventListener('scroll', () => {
            const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
            const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (scrollTop / scrollHeight) * 100;
            progressBar.style.width = scrolled + '%';
        });
    }
});
