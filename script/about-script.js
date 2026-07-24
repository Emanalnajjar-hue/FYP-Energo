document.addEventListener('DOMContentLoaded', () => {

    const reveals = document.querySelectorAll('.reveal');
    const io = new IntersectionObserver((entries) => {
        let delayIndex = 0;
        entries.forEach((e) => {
            if (e.isIntersecting) {
                setTimeout(() => e.target.classList.add('active'), delayIndex * 90);
                delayIndex++;
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.1 });

    reveals.forEach(el => io.observe(el));

    const searchInput = document.querySelector('.search-box input');
    if (searchInput) {
        searchInput.addEventListener('focus', () => {
            searchInput.parentElement.style.borderColor = '#2d8c58';
        });
        searchInput.addEventListener('blur', () => {
            searchInput.parentElement.style.borderColor = '#eef2ef';
        });
    }

    setInterval(() => {
        const icons = document.querySelectorAll('.icon-circle');
        icons.forEach(icon => {
            icon.style.transform = 'scale(1.1)';
            setTimeout(() => icon.style.transform = 'scale(1)', 300);
        });
    }, 5000);



    // === dropdown البروفايل (ديسكتوب + موبايل) ===
    const userBtn = document.getElementById('userBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');

    if (userBtn && dropdownMenu) {
        userBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('open-menu');
        });

        document.addEventListener('click', function (e) {
            if (!userBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('open-menu');
            }
        });

        // إغلاق عند السكرول
        window.addEventListener('scroll', function () {
            dropdownMenu.classList.remove('open-menu');
        }, { passive: true });
    }

});