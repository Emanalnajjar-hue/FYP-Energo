const exploreBtn = document.getElementById("exploreBtn");
if (exploreBtn) {
    exploreBtn.addEventListener("click", function () {
        document.querySelector(".products-section").scrollIntoView({
            behavior: "smooth"
        });
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

document.addEventListener('DOMContentLoaded', function () {
    const msgEl = document.getElementById('msg-data');
    const box = document.getElementById('alert-box');

    if (msgEl && box) {
        const text = msgEl.value;
        const type = msgEl.dataset.type;

        if (text && text.trim() !== "") {
            box.className = 'alert-box ' + (type === 'success' ? 'alert-success' : 'alert-error');
            box.innerHTML = `
                <span class="alert-icon">${type === 'success' ? '✅' : '❌'}</span>
                <span class="alert-text">${text}</span>
            `;
            box.style.display = 'flex';
            box.style.opacity = '1';

            setTimeout(() => {
                box.style.transition = 'opacity 0.5s ease';
                box.style.opacity = '0';
                setTimeout(() => { box.style.display = 'none'; }, 500);
            }, 10000);
        }
    }
});
function togglePassword() {
    const password = document.getElementById("login-password");
    const icon = document.querySelector(".toggle-password i");

    if (password.type === "password") {
        password.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        password.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}