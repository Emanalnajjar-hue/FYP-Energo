<?php
session_start();
require_once '../config/db.php'; 

 $database = new Database();
 $pdo = $database->getConnection();

 $stmt = $pdo->prepare("SELECT * FROM equipment WHERE status = 'available' AND is_featured = 1 ORDER BY equipment_id DESC LIMIT 4");
 $stmt->execute();
 $equipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energo - Power Resource Rental Platform</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&family=Segoe+UI:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/home-style.css">

</head>

<body>

<div class="top-bar">
    <div class="top-bar-wrapper">
        <div class="contact-info">
            <div class="contact-item">
                <span class="icon-box">
                    <i class="fa-regular fa-envelope"></i>
                </span>
                <span class="contact-text">For Support Mail us: <br> energocamp@gmail.com</span>
            </div>
            
            <div class="contact-item">
                <span class="icon-box">
                    <i class="fa-solid fa-phone"></i>
                </span>
                <span class="contact-text">Service Helpline Call Us: <br> 0593214960</span>
            </div>
        </div>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="login.php" class="login-button">Login / Sign Up</a>
        <?php endif; ?>
        
    </div>
</div>
<nav class="nav">
    <a href="index.php" class="logo">
        <img src="../images/logo.png" alt="Energo Logo" />
        <span>Energo</span>
    </a>

    <ul class="nav-links">
        <li><a href="home.php" class="active">Home</a></li>
        <li><a href="products.php">Products</a></li>
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
    <li><a href="testmimonial.php"><i class="fa-solid fa-comments"></i> Post a Testimonials</a>
                    </li>

    <li class="divider"></li>
    <li><a href="logout.php" class="sign-out"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out</a></li>
</ul>
            </div>
        <?php endif; ?>

        <form action="products.php" method="GET" class="search-box">
    <input type="text" name="search_query" placeholder="Search...">
    <button type="submit" style="background:none; border:none; cursor:pointer;">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
    </button>
</form>

    </div>
</nav>

    <section class="hero-section">
        <div class="hero-content">
        <p class="hero-subtitle">PRACTICAL ENERGY FOR A BRIGHT FUTURE</p>
                    <h1 class="hero-title">Powering your<br>vision to light up<br>the world.</h1>
            <button class="btn-explore-now" id="exploreBtn">Explore Now</button>
        </div>

        <div class="learn-more-badge">
            <div class="badge-top-row">
                <span class="badge-text">Learn More</span>
                <a href="#" class="badge-arrow-btn"><i class="fa-solid fa-chevron-right"></i></a>
            </div>
            <div class="badge-bottom-row">
                <div class="overlapping-images">
                    <img src="../images/hero-prod1.jpg" alt="Product" class="overlap-img">
                    <img src="../images/hero-prod2.jpg" alt="Product" class="overlap-img">
                    <img src="../images/hero-prod3.jpg" alt="Product" class="overlap-img">
                </div>
                <p class="badge-desc">Power your life<br>without interruption.</p>
            </div>
        </div>
    </section>

    <section class="intro-section">
        <div class="intro-card-shadow-wrapper">
            <div class="intro-card">
                <h2 class="intro-title">Find Reliable Power with Energo</h2>
                <p class="intro-desc">At Energo, we provide a reliable energy rental platform that allows users
    to easily browse, select, and rent generators and batteries based on their needs.</p>                <div class="floating-bulb">
                    <div class="floating-bulb-bg">
                        <img src="../images/green.png" alt="Bulb">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="top-services-section">
        <h2 class="main-sec-title">Our Top Services</h2>
        <div class="services-grid-container">

            <div class="service-flat-card">
                <div class="flat-img-holder">
                    <img src="../images/quick-repair.jpg" alt="Quick repair">
                </div>
                <h3 class="flat-card-title">Quick repair</h3>
                <p class="flat-card-desc">Fast & Reliable On-Site Assistance</p>
            </div>

            <div class="service-flat-card">
                <div class="flat-img-holder">
                    <img src="../images/verification.jpg" alt="Verification Shield">
                </div>
                <h3 class="flat-card-title">Verification Shield</h3>
                <p class="flat-card-desc">Instant Security Check Service</p>
            </div>

            <div class="service-flat-card">
                <div class="flat-img-holder">
                    <img src="../images/paypal.jpg" alt="PayPal">
                </div>
                <h3 class="flat-card-title">Payment via PayPal</h3>
                <p class="flat-card-desc">Secure & Easy Transactions</p>
            </div>

            <div class="service-flat-card">
                <div class="flat-img-holder">
                    <img src="../images/booking.jpg" alt="Booking Engine">
                </div>
                <h3 class="flat-card-title">Customized Booking Engine</h3>
                <p class="flat-card-desc">Tailored Scheduling Engine</p>
            </div>

        </div>
    </section>

    <section class="maintenance-section">
        <div class="maintenance-container">

            <div class="maintenance-content">
                <h2 class="maintenance-title">Expert Maintenance Services <br> For generators & batteries</h2>
                <div class="maintenance-text">
                    <p>strong power , trusted technicians</p>
                    <p>we power your life & fix your problems</p>
                    <p>need help? contact us anytime</p>
                </div>
                <a href="maintenance.php" class="maintenance-btn">Fix your power now</a>
            </div>

            <div class="maintenance-visual">
                <div class="dots-pattern dots-top-left"></div>
                <div class="dots-pattern dots-bottom-right"></div>
                <span class="badge-tag tag-need">Need power now?</span>
                <span class="badge-tag tag-no">No electricity?</span>
                <div class="engineer-blob-container">
                    <img src="../images/technician.png" alt="Expert Technician" class="technician-img">
                </div>
            </div>

        </div>
    </section>

    <section class="products-section">
    <h2 class="section-title">New Energy Products</h2>
    <div class="products-container">

        <?php if (!empty($equipments)): ?>
            <?php foreach ($equipments as $item): ?>
                <div class="product-card">
                    <div class="product-image-wrapper">
                        <img src="../images/<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-img">
                        <div class="specs-overlay">
                            <div class="spec-item"><i class="fa-solid fa-weight-hanging"></i> <?= htmlspecialchars($item['weight_kg']) ?></div>
                            <div class="spec-item"><i class="fa-solid fa-bolt"></i> <?= htmlspecialchars($item['voltage']) ?></div>
                        </div>
                    </div>
                    <div class="product-info">
                        <div class="product-header">
                            <h3 class="product-name"><?= htmlspecialchars($item['name']) ?></h3>
                            <span class="product-price">$<?= htmlspecialchars($item['price_per_day']) ?> <span>/Day</span></span>
                        </div>
                        <p class="product-desc">
                            <i class="fa-solid fa-wand-magic-sparkles desc-icon"></i>
                            <?= htmlspecialchars(mb_strimwidth($item['description'], 0, 80, "...")) ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; color:#888; grid-column: 1 / -1;">No equipment available at the moment.</p>
        <?php endif; ?>

    </div>

    <div class="products-slider-dots">
        <span class="dot active" data-index="0"></span>
        <span class="dot" data-index="1"></span>
        <span class="dot" data-index="2"></span>
    </div>
</section>

    <section class="reviews-section">
        <h2 class="reviews-main-title">Customer Reviews</h2>
        <p class="reviews-subtitle">We're happy to serve our customers and proud of their positive feedback.</p>

        <div class="reviews-container">
            <div class="reviews-row row-triple">
                <div class="review-card">
                    <div class="user-avatar-box"><img src="../images/M.amal.jpg" alt="M. amal"></div>
                    <h4 class="user-name">M. amal</h4>
                    <div class="divider-line"></div>
                    <p class="user-feedback">"Excellent service! The generator was delivered on time and worked perfectly during the power outage. Highly recommended."</p>
                </div>
                <div class="review-card">
                    <div class="user-avatar-box border-green"><img src="../images/Eng.ahmed.jpg" alt="Eng. ahmed"></div>
                    <h4 class="user-name">Eng. ahmed</h4>
                    <div class="divider-line"></div>
                    <p class="user-feedback">"The maintenance team is very professional and quick to respond. They solved our problem in no time."</p>
                </div>
                <div class="review-card">
                    <div class="user-avatar-box"><img src="../images/D.hazem.jpg" alt="D. hazem"></div>
                    <h4 class="user-name">D. hazem</h4>
                    <div class="divider-line"></div>
                    <p class="user-feedback">"Affordable prices and great customer support. I will definitely use this service again."</p>
                </div>
            </div>

            <div class="reviews-row row-double">
                <div class="review-card">
                    <div class="user-avatar-box border-yellow"><img src="../images/M.saja.jpg" alt="M. saja"></div>
                    <h4 class="user-name">M. saja</h4>
                    <div class="divider-line"></div>
                    <p class="user-feedback">"Our business depends on electricity, and this platform helped us a lot during emergencies. Very reliable."</p>
                </div>
                <div class="review-card">
                    <div class="user-avatar-box border-green"><img src="../images/Eng.reem.jpg" alt="Eng. reem"></div>
                    <h4 class="user-name">Eng. reem</h4>
                    <div class="divider-line"></div>
                    <p class="user-feedback">"We are proud to serve our customers with reliable power solutions and professional service. Here's what some of our clients say about us."</p>
                </div>
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
                <p class="brand-desc">We provide reliable power solutions including generator rental, maintenance, and battery services to ensure uninterrupted electricity anytime.</p>
                <div class="footer-contacts">
                    <div class="contact-row"><i class="fa-solid fa-phone"></i> +972593214960</div>
                    <div class="contact-row"><i class="fa-solid fa-envelope"></i> energocamp@gmail.com</div>
                    <div class="contact-row"><i class="fa-solid fa-location-dot"></i> Palestine, Gaza</div>
                </div>
            </div>

            <div class="footer-column">
                <h3 class="column-title">QUICK LINKS</h3>
                <ul class="footer-links-list">
                    <li><a href="index.php">&gt; Home</a></li>
                    <li><a href="services.html">&gt; Services</a></li>
                    <li><a href="">&gt; Generators</a></li>
                    <li><a href="#">&gt; Batteries</a></li>
                    <li><a href="#">&gt; Motors</a></li>
                    <li><a href="about.html">&gt; About Us</a></li>
                    <li><a href="contact.html">&gt; Contact Us</a></li>
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
    <a href="home.php" class="bnav-item active">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
    </a>
    <a href="products.php" class="bnav-item">
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

    <script src="../script/home-script.js"></script>
</body>

</html>