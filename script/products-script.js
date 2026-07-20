document.addEventListener("DOMContentLoaded", () => {

    // === فلتر المنتجات ===
    const filterButtons = document.querySelectorAll('.filter-btn');
    const items = document.querySelectorAll('.energy-item-box');

    const categoryMap = {
        'generators': ['generator', 'generators'],
        'batteries': ['battery', 'batteries'],
        'cables': ['cable', 'cables'],
        'kits': ['kit', 'kits']
    };

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            const filter = button.getAttribute('data-filter');
            const allowedValues = categoryMap[filter] || [filter];

            items.forEach(item => {
                const category = item.getAttribute('data-category').toLowerCase().trim();

                if (filter === 'all' || allowedValues.includes(category)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // === منيو البروفايل Dropdown ===
    const userBtn = document.getElementById('userBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');

    if (userBtn && dropdownMenu) {
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('open-menu');
        });

        document.addEventListener('click', (e) => {
            if (!userBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('open-menu');
            }
        });

        window.addEventListener('resize', () => {
            dropdownMenu.classList.remove('open-menu');
        });
    }

});