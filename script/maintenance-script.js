document.addEventListener('DOMContentLoaded', () => {

    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
        item.addEventListener('click', () => {

            faqItems.forEach(i => {
                if (i !== item) i.classList.remove('active');
            });

            item.classList.toggle('active');
        });
    });


    const reportBtn = document.querySelector('.btn-primary');
    const formSection = document.querySelector('.form-section');

    if (reportBtn && formSection) {
        reportBtn.addEventListener('click', (e) => {
            e.preventDefault();
            formSection.scrollIntoView({ behavior: 'smooth' });
        });
    }


    const sendBtn = document.querySelector('.btn-send');
    if (sendBtn) {
        sendBtn.addEventListener('click', (e) => {
            const form = document.querySelector('form');
            const inputs = form.querySelectorAll('input, textarea, select');
            let isEmpty = false;

            inputs.forEach(input => {

                if (input.value.trim() === "") {
                    isEmpty = true;
                    input.style.borderColor = "red";
                } else {
                    input.style.borderColor = "#ddd";
                }
            });

            if (isEmpty) {
                e.preventDefault();
                alert("Please fill in all required fields before sending!");
            } else {
                alert("Your request has been sent successfully! Our team will contact you soon.");
                form.reset();
            }
        });
    }
});
document.querySelectorAll('.faq-question').forEach(button => {
    button.addEventListener('click', () => {
        const faqItem = button.parentElement;
        faqItem.classList.toggle('active');
    });
});