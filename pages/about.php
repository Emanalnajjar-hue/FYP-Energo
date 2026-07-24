<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Energo - About Us</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/about-style.css" />
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

    <nav class="abt-nav">
        <a href="home.php" class="abt-logo">
            <img src="../images/logo.png" alt="Energo Logo" />
            <span>Energo</span>
        </a>
        <ul class="abt-nav-links">
            <li><a href="home.php">Home</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="services.php">Services</a></li>
            <li><a href="about.php" class="active">About Us</a></li>
        </ul>
        <div class="abt-nav-right">
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
            <?php endif; ?>
            
            <form action="products.php" method="GET" class="search-box">
                <input type="text" name="search_query" placeholder="Search products...">
                <button type="submit" style="background:none; border:none; cursor:pointer;">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                </button>
            </form>
        </div>
    </nav>

    <section class="abt-hero">
        <div class="abt-hero-content">
            <h1>About Our <span>Support &amp; Services</span></h1>
            <p>Energo provides reliable generator and battery rental solutions with fast and easy access to power
                whenever you need it. We are committed to supporting our users by offering a smooth booking experience,
                quick assistance, and dependable service.</p>
        </div>
        <div class="abt-hero-img-wrap">
            <img src="../images/abthero.png" alt="About Hero" />
        </div>
    </section>

    <section class="abt-why">
        <div class="abt-why-left">
            <h2>Why Choose Energo?</h2>
            <p class="abt-why-sub">Choosing Energo is a practical energy solution that helps users access power easily, quickly, and efficiently at an affordable cost whenever you need it.</p>

            <div class="abt-features-grid">
                <div class="abt-feature">
                    <img src="../images/power-icon.png" alt="Fast Access to Power" />
                    <span class="abt-feat-label">Fast Access to Power</span>
                    <p>Get a generator or battery instantly during power outages or whenever energy is needed.</p>
                </div>
                <div class="abt-feature">
                    <img src="../images/cost-icon.png" alt="Cost Saving" />
                    <span class="abt-feat-label">Cost Saving</span>
                    <p>Pay only for the service when needed without any purchase costs or long-term commitment.</p>
                </div>
                <div class="abt-feature">
                    <img src="../images/reliable-icon.png" alt="Reliable energy" />
                    <span class="abt-feat-label">Reliable energy</span>
                    <p>We provide stable and efficient power solutions for homes and students to ensure continuous electricity.</p>
                </div>
                <div class="abt-feature">
                    <img src="../images/flexible-icon.png" alt="Flexible & easy service" />
                    <span class="abt-feat-label">Flexible &amp; easy service</span>
                    <p>A simple and fast booking system with delivery and equipment pickup through a QR system.</p>
                </div>
            </div>
        </div>

        <div class="abt-why-right">
            <div class="abt-dots"></div>
            <img src="../images/generator.png" alt="Generator" class="abt-why-img" />
        </div>
    </section>

    <section class="abt-contact">
        <div class="abt-contact-inner">
            <div class="abt-contact-left">
                <h2>Get In Touch To Discuss<br>How We Can Help You!</h2>
                <p>We're pleased to be welcoming customers to join us.</p>
                <div class="abt-contact-actions">
                    <a href="maintenance.php" class="abt-quote-btn">Request Maintenance</a>
                    <div class="abt-phone-item">
                        <img src="../images/ico-phone.png" alt="phone" />
                        <span>Phone :+972-59-3214960</span>
                    </div>
                </div>
            </div>
            <div class="abt-contact-right">
                <img src="../images/generator-yellow.png" alt="Generator" />
            </div>
        </div>
    </section>

    <div class="abt-cta-wrap">
        <div class="abt-cta-banner">
            <div class="abt-cta-logo">
                <img src="../images/logo.png" alt="Energo" />
            </div>
            <div class="abt-cta-text">
                <h3>Ready to Power Your Future?</h3>
                <p>Book a service or consultation with our experts today and start saving.</p>
            </div>
            <div class="abt-cta-btns">
                <a href="products.php" class="abt-cta-btn-book">Book Service Now</a>
                <a href="maintenance.php" class="abt-cta-btn-con">Contact Experts</a>
            </div>
        </div>
    </div>

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
                    <li><a href="home.php">&gt; Home</a></li>
                    <li><a href="services.php">&gt; Services</a></li>
                    <li><a href="products.php">&gt; Generators</a></li>
                    <li><a href="products.php">&gt; Batteries</a></li>
                    <li><a href="products.php">&gt; Motors</a></li>
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
                    <li><a href="#"><div class="social-icon-circle"><i class="fa-brands fa-facebook-f"></i></div> Facebook</a></li>
                    <li><a href="#"><div class="social-icon-circle"><i class="fa-brands fa-instagram"></i></div> Instagram</a></li>
                    <li><a href="#"><div class="social-icon-circle"><i class="fa-brands fa-whatsapp"></i></div> WhatsApp</a></li>
                    <li><a href="#"><div class="social-icon-circle"><i class="fa-brands fa-linkedin-in"></i></div> LinkedIn</a></li>
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

    <script src="../script/about-script.js?v=<?= time(); ?>"></script>
</body>
</html>