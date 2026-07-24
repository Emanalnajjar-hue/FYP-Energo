document.addEventListener('DOMContentLoaded', () => {

    const stars = document.querySelectorAll('.star-rating i');
    let currentRating = 0;

    function highlightStars(count) {
        stars.forEach(s => {
            if (s.getAttribute('data-value') <= count) {
                s.classList.add('active');
                s.style.color = '#FFD700';
            } else {
                s.classList.remove('active');
                s.style.color = '#e0e0e0';
            }
        });
    }

    stars.forEach(star => {
        star.addEventListener('mouseover', function () {
            const val = this.getAttribute('data-value');
            highlightStars(val);
        });

        star.addEventListener('click', function () {
            currentRating = this.getAttribute('data-value');
            highlightStars(currentRating);
        });
    });

    document.querySelector('.star-rating').addEventListener('mouseleave', () => {
        highlightStars(currentRating);
    });


    const form = document.getElementById('testimonial-form');

    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();

            if (currentRating === 0) {
                alert("Please select a star rating.");
                return;
            }

            const formData = {
                service: document.getElementById('service-name').value,
                feedback: document.getElementById('feedback-text').value,
                rating: currentRating
            };
            console.log("Submitting:", formData);

            const toast = document.getElementById('toast');
            toast.classList.add('show');

            setTimeout(() => {
                form.reset();
                highlightStars(0);
                currentRating = 0;
                toast.classList.remove('show');
            }, 3000);
        });
    }
});