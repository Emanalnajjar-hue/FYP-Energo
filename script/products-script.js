
const PRODUCTS = [
    {
        equipment_id: 1,
        name: "Diesel Generator 5kW",
        description: "Reliable 5kW diesel generator, perfect for homes and small shops.",
        price_per_day: 20,
        image_url: "generator1.png",
        status: "available", // available | booked | under_maintenance
        category: "generators",
        is_featured: 1
    },
    {
        equipment_id: 2,
        name: "Deep Cycle Battery 200Ah",
        description: "Long-lasting 200Ah deep cycle battery for solar and backup power.",
        price_per_day: 15,
        image_url: "battery1.png",
        status: "available",
        category: "batteries",
        is_featured: 1
    },
    {
        equipment_id: 3,
        name: "Heavy-Duty Power Cable 20m",
        description: "20-meter heavy-duty power cable, weatherproof and durable.",
        price_per_day: 5,
        image_url: "cable1.png",
        status: "booked",
        category: "cables",
        is_featured: 1
    },
    {
        equipment_id: 4,
        name: "Emergency Repair Kit",
        description: "Complete kit of tools and spares for on-site electrical repairs.",
        price_per_day: 10,
        image_url: "kit1.png",
        status: "under_maintenance",
        category: "kits",
        is_featured: 1
    },
    {
        equipment_id: 5,
        name: "Diesel Generator 10kW",
        description: "10kW diesel generator suited for larger homes and small businesses.",
        price_per_day: 35,
        image_url: "generator2.png",
        status: "available",
        category: "generators",
        is_featured: 0
    },
    {
        equipment_id: 6,
        name: "Solar Battery 100Ah",
        description: "Compact 100Ah battery, ideal for solar setups and light backup loads.",
        price_per_day: 12,
        image_url: "battery2.png",
        status: "available",
        category: "batteries",
        is_featured: 0
    },
    {
        equipment_id: 7,
        name: "Power Cable 10m",
        description: "10-meter power cable for short-run connections.",
        price_per_day: 3,
        image_url: "cable2.png",
        status: "booked",
        category: "cables",
        is_featured: 0
    },
    {
        equipment_id: 8,
        name: "Maintenance Toolkit",
        description: "Essential toolkit for routine generator and battery maintenance.",
        price_per_day: 8,
        image_url: "kit2.png",
        status: "under_maintenance",
        category: "kits",
        is_featured: 0
    }
];

// بديل بسيط عن htmlspecialchars() تبع PHP
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

function truncate(str, length) {
    if (!str) return '';
    return str.length > length ? str.slice(0, length) + '...' : str;
}

// === عرض "Our Best Products" (المنتجات المميزة) ===
function renderFeatured() {
    const grid = document.getElementById('featuredGrid');
    const featured = PRODUCTS.filter(p => p.is_featured === 1).slice(0, 4);

    if (featured.length === 0) {
        grid.innerHTML = '<p style="text-align:center; width:100%;">No featured products found.</p>';
        return;
    }

    grid.innerHTML = featured.map(item => {
        const isMaintenance = item.status === 'under_maintenance';
        let statusHtml;
        if (isMaintenance) {
            statusHtml = '<span class="featured-status status-under-maintenance">Under Maintenance</span>';
        } else if (item.status === 'booked') {
            statusHtml = '<span class="featured-status status-booked">Booked</span>';
        } else {
            statusHtml = '<span class="featured-status status-available">Available</span>';
        }

        return `
            <div class="product-card ${isMaintenance ? 'maintenance-featured' : ''}">
                <img src="images/${escapeHtml(item.image_url)}" alt="${escapeHtml(item.name)}">
                <h3>${escapeHtml(item.name)}</h3>
                <p>${escapeHtml(truncate(item.description, 60))}</p>
                <div class="price-row">
                    <span>$${escapeHtml(item.price_per_day)}</span>
                </div>
                <div style="margin-top:8px;">
                    ${statusHtml}
                </div>
            </div>
        `;
    }).join('');
}

// === عرض كل المنتجات، مع دعم البحث ===
function renderAllProducts(searchQuery = '') {
    const grid = document.getElementById('allProductsGrid');
    const noResultsMsg = document.getElementById('noResultsMsg');

    const query = searchQuery.trim().toLowerCase();
    const filtered = PRODUCTS
        .filter(p => p.name.toLowerCase().includes(query))
        .sort((a, b) => b.equipment_id - a.equipment_id); // نفس ORDER BY equipment_id DESC

    if (filtered.length === 0) {
        grid.innerHTML = '';
        noResultsMsg.style.display = 'block';
        return;
    }

    noResultsMsg.style.display = 'none';

    grid.innerHTML = filtered.map(item => {
        const isMaintenance = item.status === 'under_maintenance';
        const isBooked = item.status === 'booked';

        let actionsHtml;
        if (isMaintenance) {
            actionsHtml = `
                <div class="energy-status under-maintenance">Under Maintenance</div>
                <span class="energy-view-btn disabled-btn">Unavailable</span>
            `;
        } else if (isBooked) {
            actionsHtml = `
                <div class="energy-status booked">Booked</div>
                <a href="booking.html?product_id=${item.equipment_id}" class="energy-view-btn">View Details</a>
            `;
        } else {
            actionsHtml = `
                <div class="energy-status available">Available</div>
                <a href="booking.html?product_id=${item.equipment_id}" class="energy-view-btn">View Details</a>
            `;
        }

        return `
            <div class="energy-item-box ${isMaintenance ? 'maintenance-card' : ''}" data-category="${escapeHtml(item.category)}">
                <img src="images/${escapeHtml(item.image_url)}" alt="${escapeHtml(item.name)}">
                <div class="energy-shelf-shadow"></div>
                <h3>${escapeHtml(item.name)}</h3>
                <p></p>
                <p>${escapeHtml(item.price_per_day)}$</p>
                <div class="energy-actions">
                    ${actionsHtml}
                </div>
            </div>
        `;
    }).join('');

    applyActiveCategoryFilter();
}

function applyActiveCategoryFilter() {
    const activeBtn = document.querySelector('.filter-btn.active');
    if (!activeBtn) return;

    const categoryMap = {
        'generators': ['generator', 'generators'],
        'batteries': ['battery', 'batteries'],
        'cables': ['cable', 'cables'],
        'kits': ['kit', 'kits']
    };

    const filter = activeBtn.getAttribute('data-filter');
    const allowedValues = categoryMap[filter] || [filter];
    const items = document.querySelectorAll('.energy-item-box');

    items.forEach(item => {
        const category = item.getAttribute('data-category').toLowerCase().trim();
        item.style.display = (filter === 'all' || allowedValues.includes(category)) ? 'block' : 'none';
    });
}

document.addEventListener("DOMContentLoaded", () => {

    // العرض الأولي
    renderFeatured();
    renderAllProducts();

    // === البحث ===
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');

    if (searchForm && searchInput) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            renderAllProducts(searchInput.value);
            document.getElementById('products-section').scrollIntoView({ behavior: 'smooth' });
        });
    }

    // === فلتر المنتجات (category filter) ===
    const filterButtons = document.querySelectorAll('.filter-btn');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            applyActiveCategoryFilter();
        });
    });

    // === منيو البروفايل Dropdown (بيشتغل بس لو فعّلت كود تسجيل الدخول فوق) ===
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