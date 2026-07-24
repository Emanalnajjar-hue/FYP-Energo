document.addEventListener('DOMContentLoaded', function () {

    const exploreBtn = document.getElementById("exploreBtn");
    if (exploreBtn) {
        exploreBtn.addEventListener("click", function () {
            const productsSection = document.querySelector(".products-section");
            if (productsSection) {
                productsSection.scrollIntoView({ behavior: "smooth" });
            }
        });
    }

    const dots = document.querySelectorAll(".dot");
    const cards = document.querySelectorAll(".product-card");

    if (dots.length > 0 && cards.length > 0) {
        dots.forEach(dot => {
            dot.addEventListener("click", function () {
                dots.forEach(d => d.classList.remove("active"));
                this.classList.add("active");

                const index = parseInt(this.getAttribute("data-index"));

                cards.forEach((card, i) => {
                    card.style.transition = "transform 0.4s ease, opacity 0.4s ease";
                    if (i === index || i === index + 1) {
                        card.style.opacity = "1";
                        card.style.transform = "scale(1)";
                    } else {
                        card.style.opacity = "0.75";
                        card.style.transform = "scale(0.98)";
                    }
                });
            });
        });
    }

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