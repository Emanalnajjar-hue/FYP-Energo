<?php
session_start();
require_once '../config/db.php'; 

 $database = new Database();
 $pdo = $database->getConnection();

 $stmt = $pdo->prepare("SELECT * FROM equipment WHERE is_featured = 1 LIMIT 4");
 $stmt->execute();
 $best_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

 $searchQuery = isset($_GET['search_query']) ? $_GET['search_query'] : '';
 $sql = "SELECT * FROM equipment WHERE name LIKE :search ORDER BY equipment_id DESC";
 $stmt = $pdo->prepare($sql);
 $stmt->execute(['search' => "%$searchQuery%"]);
 $all_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energo - Products</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/products-style.css">

    

</head>

<body>

    <header class="top-bar">
        <div class="top-bar-wrapper">
            <div class="contact-info">
                <div class="contact-item">
                    <span class="icon-box"><i class="fa-regular fa-envelope"></i></span>
                    <span class="contact-text">For Support Mail us: <br> energocamp@gmail.com</span>
                </div>
                <div class="contact-item">
                    <span class="icon-box"><i class="fa-solid fa-phone"></i></span>
                    <span class="contact-text">Service Helpline Call Us: <br> 0593214960</span>
                </div>
            </div>
            
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="login-button" style="text-decoration:none; color:inherit;">Login / Sign Up</a>
            <?php endif; ?>
        </div>
    </header>

    <nav class="nav">
        <a href="home.php" class="logo">
            <img src="../images/logo.png" alt="Energo">
            <span>Energo</span>
        </a>
        <ul class="nav-links">
            <li><a href="home.php">Home</a></li>
            <li><a href="products.php" class="active">Products</a></li>
            <li><a href="services.php">Services</a></li>
            <li><a href="about.php">About Us</a></li>
        </ul>
        <div class="nav-right">
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="user-dropdown">
            <button class="dropdown-toggle" id="userBtn">
                <img src="../images/avatar.png" alt="User Avatar" class="user-avatar">
                <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <i class="fa-solid fa-chevron-down arrow-icon"></i>
            </button>
            <ul class="dropdown-menu" id="dropdownMenu">
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1): ?>
                    <li><a href="../admin/equipment.php"><i class="fa-solid fa-gauge-high"></i> Admin Dashboard</a></li>
                    <li class="divider"></li>
                <?php endif; ?>
                <li><a href="profile.php"><i class="fa-regular fa-user"></i> Profile Settings</a></li>
                <li><a href="update_password.php"><i class="fa-solid fa-lock"></i> Update Password</a></li>
                <li><a href="mybooking.php"><i class="fa-regular fa-calendar-check"></i> My Booking</a></li>
                <li><a href="testmimonial.php"><i class="fa-solid fa-comments"></i> Post a Testimonials</a></li>
                <li class="divider"></li>
                <li><a href="logout.php" class="sign-out"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out</a></li>
            </ul>
        </div>
    <?php else: ?>
        <!-- <a href="login.php" class="mobile-nav-login"><i class="fa-solid fa-user"></i></a> -->
    <?php endif; ?>

    <form action="products.php" method="GET" class="search-box">
        <input type="text" name="search_query" placeholder="Search...">
        <button type="submit" style="background:none; border:none; cursor:pointer;">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
        </button>
    </form>
</div>    </nav>

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="gradient-text">Power Solutions in One Place<br>Find Generators, Batteries & Cables Easily</h1>
                <p>Browse a wide range of reliable energy solutions designed to meet your needs anytime, anywhere.</p>
                <a href="#products-section" class="explore-btn">Explore Products</a>
            </div>
            <div class="hero-image">
                <img src="../images/hero.jpg" alt="Power Solutions">
            </div>
        </div>
    </section>

    <section class="top-selling-section" id="products-section">
        <div class="section-header">
            <h2>Our Best Products</h2>
        </div>

        <div class="products-grid">
            <?php if (!empty($best_products)): ?>
                <?php foreach ($best_products as $item): 
                    $is_maintenance = ($item['status'] == 'under_maintenance');
                ?>
                    <div class="product-card <?= $is_maintenance ? 'maintenance-featured' : '' ?>">
                        <img src="../images/<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                        <p><?= htmlspecialchars(mb_strimwidth($item['description'], 0, 60, "...")) ?></p>
                        <div class="price-row">
                            <span>$<?= htmlspecialchars($item['price_per_day']) ?></span>
                        </div>
                        <div style="margin-top:8px;">
                            <?php if ($is_maintenance): ?>
                                <span class="featured-status status-under-maintenance">Under Maintenance</span>
                            <?php elseif ($item['status'] == 'booked'): ?>
                                <span class="featured-status status-booked">Booked</span>
                            <?php else: ?>
                                <span class="featured-status status-available">Available</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center; width:100%;">No featured products found.</p>
            <?php endif; ?>
        </div>

        <div class="promo-banner">
            <div class="promo-text">
                <h3>10% Off For Your First Rental !</h3>
                <p>Power up your home with our reliable, high-performance generators and batteries.</p>
            </div>
        </div>

        <section class="energy-products-section">
            <div class="category-filter">
                <button class="filter-btn active" data-filter="all">
                    <i class="fa-solid fa-border-all"></i> All
                </button>
                <button class="filter-btn" data-filter="generators">
                    <i class="fa-solid fa-bolt"></i> Generators
                </button>
                <button class="filter-btn" data-filter="batteries">
                    <i class="fa-solid fa-battery-full"></i> Batteries
                </button>
                <button class="filter-btn" data-filter="cables">
                    <i class="fa-solid fa-plug-circle-bolt"></i> Cables
                </button>
                <button class="filter-btn" data-filter="kits">
                    <i class="fa-solid fa-screwdriver-wrench"></i> Kits
                </button>
            </div>
            <div class="products-background-wrapper"></div>

            <div class="energy-grid-container">
                <?php if (!empty($all_products)): ?>
                    <?php foreach ($all_products as $item): 
                        $is_maintenance = ($item['status'] == 'under_maintenance');
                        $is_booked = ($item['status'] == 'booked');
                    ?>
                        <div class="energy-item-box <?= $is_maintenance ? 'maintenance-card' : '' ?>" data-category="<?= htmlspecialchars($item['category']) ?>">
                            <img src="../images/<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            <div class="energy-shelf-shadow"></div>
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <p></p> 
                            <p><?= htmlspecialchars($item['price_per_day']) ?>$</p>
                            
                            <div class="energy-actions">
                                <?php if ($is_maintenance): ?>
                                    <!-- حالة الصيانة: يظهر "Under Maintenance" والزر معطّل -->
                                    <div class="energy-status under-maintenance">Under Maintenance</div>
                                    <span class="energy-view-btn disabled-btn">Unavailable</span>
                                <?php elseif ($is_booked): ?>
                                    <!-- حالة محجوز: يظهر "Booked" لكن يقدر يحجز لتواريخ لاحقة -->
                                    <div class="energy-status booked">Booked</div>
                                    <a href="booking.php?product_id=<?= $item['equipment_id'] ?>" class="energy-view-btn">View Details</a>
                                <?php else: ?>
                                    <!-- حالة متاحة: عادي يقدر يحجز -->
                                    <div class="energy-status available">Available</div>
                                    <a href="booking.php?product_id=<?= $item['equipment_id'] ?>" class="energy-view-btn">View Details</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center; width:100%;">No products available.</p>
                <?php endif; ?>
            </div>
        </section>
    </section>

    <section class="special-offers-container">
        <div class="offers-sidebar">
            <span class="discount-badge">20% OFF</span>
            <h3>Special Offers</h3>
            <div class="countdown-timer">
                <span class="timer-box">02</span> :
                <span class="timer-box">26</span> :
                <span class="timer-box">49</span>
            </div>
        </div>

        <div class="offers-grid">
            <div class="offer-card">
                <span class="sale-ribbon">SALE</span>
                <img src="../images/pro-sale1.png" alt="Battery">
                <h4>Deep Cycle Battery 200Ah</h4>
                <p class="price-info"><span class="old-price">25$</span> 15$</p>
                <a href="booking.php" class="book-now-btn">Book Now</a>
            </div>

            <div class="offer-card">
                <span class="sale-ribbon">SALE</span>
                <img src="../images/pro-sale2.png" alt="Cable">
                <h4>Heavy-Duty Power Cable</h4>
                <p class="price-info"><span class="old-price">10$</span> 5$</p>
                <a href="booking.php" class="book-now-btn">Book Now</a>
            </div>

            <div class="offer-card">
                <span class="sale-ribbon">SALE</span>
                <img src="../images/pro-sale3.png" alt="Light">
                <h4>LED Power Light</h4>
                <p class="price-info"><span class="old-price">25$</span> 15$</p>
                <a href="booking.php" class="book-now-btn">Book Now</a>
            </div>

            <div class="offer-card">
                <span class="sale-ribbon">SALE</span>
                <img src="../images/pro-sale4.png" alt="Light">
                <h4>LED Power Light</h4>
                <p class="price-info"><span class="old-price">25$</span> 15$</p>
                <a href="booking.php" class="book-now-btn">Book Now</a>
            </div>
        </div>
    </section>

    <section class="cta-banner">
        <div class="banner-content">
            <div class="logo-area">
                <img src="../images/logo.png" alt="Logo">
            </div>
            <div class="text-area">
                <h3>Ready to Power Your Future?</h3>
                <p>Book a service or consultation with our experts today and start saving.</p>
            </div>
            <div class="buttons-area">
                <a href="services.php" class="btn-solid" style="text-decoration: none !important;">Book Service Now</a>
                <a href="maintenance.php" target="_blank" class="btn-outline" style="text-decoration: none !important;">Contact Experts</a>
            </div>
        </div>
    </section>

    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-column">
                <div class="footer-logo">
                    <i class="fa-solid fa-bolt logo-lightning"></i>
                    <div class="logo-title-group">
                        <h3>POWER</h3>
                        <h4>SOLUTIONS</h4>
                    </div>
                </div>
                <p class="brand-desc">We provide reliable power solutions including generator rental, maintenance,
                    and battery services to ensure uninterrupted electricity anytime.</p>
                <div class="footer-contacts">
                    <div class="contact-row"><i class="fa-solid fa-phone"></i> +972593214960</div>
                    <div class="contact-row"><i class="fa-solid fa-envelope"></i> energocamp@gmail.com</div>
                    <div class="contact-row"><i class="fa-solid fa-location-dot"></i> Palestine, Gaza</div>
                </div>
            </div>

            <div class="footer-column">
                <h3 class="column-title">QUICK LINKS</h3>
                <ul class="footer-links-list">
                    <li><a href="home.php">&gt; Home</a></li>
                    <li><a href="products.php">&gt; Products</a></li>
                    <li><a href="services.php">&gt; Services</a></li>
                    <li><a href="#">&gt; Generators</a></li>
                    <li><a href="#">&gt; Batteries</a></li>
                    <li><a href="about.php">&gt; About Us</a></li>
                    <li><a href="#">&gt; Contact Us</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3 class="column-title">OUR SERVICES</h3>
                <ul class="footer-services-list">
                    <li><span class="service-icon-box"><i class="fa-solid fa-warehouse"></i></span> Generator Rental</li>
                    <li><span class="service-icon-box"><i class="fa-solid fa-screwdriver-wrench"></i></span> Electrical Maintenance</li>
                    <li><span class="service-icon-box"><i class="fa-solid fa-battery-three-quarters"></i></span> Battery Solutions</li>
                    <li><span class="service-icon-box"><i class="fa-solid fa-headset"></i></span> Emergency Support</li>
                </ul>
            </div>

            <div class="footer-column">
                <h3 class="column-title">FOLLOW US</h3>
                <ul class="social-links-list">
                    <li><a href="#">
                            <div class="social-icon-circle"><i class="fa-brands fa-facebook-f"></i></div> Facebook
                        </a></li>
                    <li><a href="#">
                            <div class="social-icon-circle"><i class="fa-brands fa-instagram"></i></div> Instagram
                        </a></li>
                    <li><a href="#">
                            <div class="social-icon-circle"><i class="fa-brands fa-whatsapp"></i></div> WhatsApp
                        </a></li>
                    <li><a href="#">
                            <div class="social-icon-circle"><i class="fa-brands fa-linkedin-in"></i></div> LinkedIn
                        </a></li>
                </ul>
            </div>
        </div>
        <div class="footer-copyright">
            <p>&copy; 2026 Energo. All rights reserved.</p>
        </div>
    </footer>
    
    <div class="bottom-nav">
    <a href="home.php" class="bnav-item">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
    </a>
    <a href="products.php" class="bnav-item active">
        <i class="fa-solid fa-box-open"></i>
        <span>Products</span>
    </a>
    <a href="services.php" class="bnav-item">
        <i class="fa-solid fa-wrench"></i>
        <span>Services</span>
    </a>
    <a href="about.php" class="bnav-item">
        <i class="fa-solid fa-circle-info"></i>
        <span>About</span>
    </a>
</div>
    <script src="../script/products-script.js"></script>
    
</body>
</html>