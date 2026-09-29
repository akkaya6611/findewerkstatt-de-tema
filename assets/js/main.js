/**
 * FindeWerkstatt.de — Main Frontend JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // 1. Mobile Menu Toggle
    const menuToggle = document.querySelector('.fw-menu-toggle');
    const nav = document.querySelector('.fw-nav');

    if (menuToggle && nav) {
        menuToggle.addEventListener('click', function() {
            if (nav.style.display === 'flex') {
                nav.style.display = 'none';
            } else {
                nav.style.display = 'flex';
                nav.style.flexDirection = 'column';
                nav.style.position = 'absolute';
                nav.style.top = '72px';
                nav.style.left = '0';
                nav.style.right = '0';
                nav.style.background = '#ffffff';
                nav.style.padding = '20px';
                nav.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)';
                nav.style.zIndex = '99';
            }
        });
    }

    // 2. FAQ Accordion Toggle
    const faqQuestions = document.querySelectorAll('.fw-faq-question');
    faqQuestions.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const answer = this.nextElementSibling;
            const isOpen = answer.style.display === 'block';

            // Close all other answers
            document.querySelectorAll('.fw-faq-answer').forEach(function(a) {
                a.style.display = 'none';
            });
            document.querySelectorAll('.fw-faq-question span.fw-faq-icon').forEach(function(icon) {
                icon.textContent = '+';
            });

            if (!isOpen) {
                answer.style.display = 'block';
                const icon = this.querySelector('.fw-faq-icon');
                if (icon) icon.textContent = '−';
            }
        });
    });
});
