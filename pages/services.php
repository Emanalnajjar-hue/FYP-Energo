<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Energo - Services</title>
    <link rel="stylesheet" href="../style/services-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
            <li><a href="home.php">Home</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="services.php" class="active">Services</a></li>
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
                    </li>                    <li class="divider"></li>
                    <li><a href="logout.php" class="sign-out"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out</a>
                    </li>
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



    <section class="hero">
        <div class="hero-container">
            <div class="hero-text">
                
            <h1>Rent Power Anytime, Anywhere <br><span>Reliable Generator & Battery</span> Rental Platform</h1>

<p>Energo connects energy providers with users in Gaza, offering a fast, reliable, and convenient way to rent generators and batteries.</p>                    <button class="explore-btn" onclick="document.querySelector('.how-it-works').scrollIntoView({behavior: 'smooth'})">Explore Services</button>
                    </div>
            <div class="hero-img-wrap">
                <img src="../images/abthero.png" alt="About Hero" />
            </div>
        </div>
    </section>
    <section class="how-it-works">
        <h2>How Our Service Works ?</h2>
        <p class="subtitle">Simple steps to clean energy</p>
        <div class="steps-container">

            <div class="step-card">
                <div class="icon-wrapper"><i class="fa-solid fa-calendar-days"></i></div>
                <div class="step-badge">Step 1</div>
                <h3>Rental Booking System</h3>
                <p>Users book batteries or generators easily.</p>
            </div>

            <div class="step-card">
                <div class="icon-wrapper"><i class="fa-solid fa-qrcode"></i></div>
                <div class="step-badge">Step 2</div>
                <h3>QR Code Verification</h3>
                <p>QR codes make pickup and delivery fast.</p>
            </div>

            <div class="step-card">
                <div class="icon-wrapper"><i class="fa-solid fa-wallet"></i></div>
                <div class="step-badge">Step 3</div>
                <h3>E-Wallet Payment</h3>
                <p>Users pay using digital wallets.</p>
            </div>

            <div class="step-card">
                <div class="icon-wrapper"><i class="fa-solid fa-wrench"></i></div>
                <div class="step-badge">Step 4</div>
                <h3>Maintenance & Support</h3>
                <p>Users can request maintenance and technical support.</p>
            </div>

        </div>

        </div>
        <section class="booking-section">
            <div class="booking-visual">
                <img src="../images/booking-main.png" alt=" Booking System">
            </div>

            <div class="booking-content">
            <h2>Quick Booking System for generators & batteries</h2>
<p>Experience a faster and better way to access reliable energy. Reserve generators and batteries in
    just a few clicks and get everything delivered quickly to your location.</p>                <ul class="features-list">
                    <li>Always-on support to keep your energy systems running smoothly, anytime you need.</li>
                    <li>Complete energy solutions with generators, batteries, and essential accessories like cables,
                        lights, and connectors.</li>
                    <li>Fast, simple, and reliable energy booking with Energo. Your power is just a click away.</li>
                </ul>

                <button class="explore-btn" onclick="window.location.href='booking.php'">Get Equipment</button>
            </div>
        </section>
        <section class="payment-section">
            <div class="payment-text">
                <span class="subtitle">COMPLETE YOUR PAYMENT SECURELY VIA PALPAY WALLET</span>
                <h2>Pay Via PalPay Wallet Easily And Securely.</h2>
                <p>Complete your payment securely using the PalPay digital wallet. Enjoy fast, safe, and reliable
                    transactions designed to make your payment process simple and convenient. With instant confirmation,
                    you can be sure your payment is processed quickly and without delays.</p>

                    <div class="stats-container trust-badges">
    <div class="stat">
        <i class="fa-solid fa-shield-halved"></i>
        <div class="stat-info">
            <strong>Secure Payment</strong>
            <span>100% Protected</span>
        </div>
    </div>
    <div class="stat">
        <i class="fa-solid fa-circle-check"></i>
        <div class="stat-info">
            <strong>Verified Equipment</strong>
            <span>Tested & Safe</span>
        </div>
    </div>
    <div class="stat">
        <i class="fa-solid fa-rotate-left"></i>
        <div class="stat-info">
            <strong>Easy Return</strong>
            <span>Hassle-Free</span>
        </div>
    </div>
    </div>

                <!-- <button class="explore-btn">Pay Now</button> -->
            </div>

            <div class="payment-wallet-graphic">
                <img src="../images/palpay-logo.jpg" alt="PalPay Wallet">
            </div>
        </section>
        <section class="qr-section">
            <div class="qr-visual">
                <img src="../images/qr-scan.jpg" alt="QR Code" class="qr-image">
                <div class="contact-card">
                    <div class="card-part green-side">
                        <span class="label">CALL FOR A QUOTE:</span>
                        <strong>+972-59-3214960</strong>
                    </div>
                    <div class="card-part white-side">
                        <span class="label">EMAIL ADDRESS:</span>
                        <strong>energocamp@gmail.com</strong>
                    </div>
                </div>
            </div>

            <div class="qr-content">
                <span class="subtitle">PRACTICAL  ENERGY SOLUTIONS</span>
                <h2>Why QR Code System In Energo?</h2>

                <div class="features-list">
                    <div class="feature-item">
                        <i class="fa-solid fa-qrcode"></i>
                        <div>
                            <h3>Fast Verification</h3>
                            <p>Enable quick and seamless pick up and delivery of generators and batteries using QR code
                                scanning.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fa-solid fa-check-double"></i>
                        <div>
                            <h3>Accurate Documentation</h3>
                            <p>Every transaction is recorded instantly for clear tracking and transparency.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fa-solid fa-clock"></i>
                        <div>
                            <h3>Time-Saving Solution</h3>
                            <p>Eliminates manual procedures and speeds up the entire rental and return process
                                efficiently.</p>
                        </div>
                    </div>
                </div>

                <!-- <button class="explore-btn">Scan QR Code</button> -->
            </div>
        </section>
        <section class="support-section">
            <div class="support-header">
                <span>TECHNICAL SUPPORT SERVICES</span>
                <h2>Reliable Support For Continuous Operation</h2>
            </div>

            <div class="support-wrapper">
                <div class="support-grid">
                    <div class="side-item left">
                        <div class="icon-circle"><i class="fa-solid fa-battery-full"></i></div>
                        <div class="text">
                            <h3>Battery Maintenance Services </h3>
                            <p>Regular checks and fast support to extend battery life and performance.</p>
                        </div>
                    </div>

                    <div class="center-img">
                        <img src="../images/maintenance-support.jpg" alt="Support">
                        <button class="request-btn" onclick="window.location.href='maintenance.php'">Request Maintenance</button>
                    </div>

                    <div class="side-item right">
                        <div class="text">
                            <h3>Preventive Maintenance</h3>
                            <p>Regular inspection and productive support to prevent Equipment failuer.</p>
                        </div>
                        <div class="icon-circle"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                    </div>
                </div>

                <div class="bottom-grid">
                    <div class="item">
                        <div class="icon-circle"><i class="fa-solid fa-bolt"></i></div>
                        <h3>End-to-End Energy Maintenance Solution</h3>
                        <p>Ensuring reliable performance, fast support, and continuous energy supply.</p>
                    </div>
                    <div class="item">
                        <div class="icon-circle"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <h3>Emergency Repair Service</h3>
                        <p>Quik response maintenance for urgent generator and battery breakdown.</p>
                    </div>
                    <div class="item">
                        <div class="icon-circle"><i class="fa-solid fa-leaf"></i></div>
                        <h3>Efficient Energy Management</h3>                        <p>We utilize modern system to efficiently manage rental maintenance and energy usage.</p>
                    </div>
                    <div class="item">
                        <div class="icon-circle"><i class="fa-solid fa-gear"></i></div>
                        <h3>Generator & Battery Maintenance</h3>
                        <p>Fast and reliable maintenance services for generators and batteries.</p>
                    </div>
                </div>
            </div>
        </section>
        <section class="delivery-offer-section">
            <div class="delivery-wrapper">
                <div class="delivery-icon-box">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>

                <div class="delivery-info-box">
                    <h3>Express Delivery Available</h3>
                    <p>Get your equipment delivered directly to your doorstep. Fast and secure service across the
                        region.</p>
                </div>

                <div class="delivery-btn-box">
                    <a href="delivery.php" class="btn-delivery-action">
                        Book Delivery
                        <span class="delivery-price-tag">$20</span>
                    </a>
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
                    <button class="btn-solid" onclick="window.location.href='booking.php'">Book Service Now</button>
                    <button class="btn-outline" onclick="window.location.href='maintenance.php'">Contact Experts</button>
                </div>
            </div>
        </section>
    </section>
    </section> <!-- نهاية قسم CTA Banner -->
    
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
                    <li><a href="index.php">&gt; Home</a></li>
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
        <a href="home.php" class="bnav-item">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>
        <a href="products.php" class="bnav-item">
            <i class="fa-solid fa-box-open"></i>
            <span>Products</span>
        </a>
        <a href="services.php" class="bnav-item active">
            <i class="fa-solid fa-wrench"></i>
            <span>Services</span>
        </a>
        <a href="about.php" class="bnav-item">
            <i class="fa-solid fa-circle-info"></i>
            <span>About</span>
        </a>
    </div>

    <script src="../script/services-script.js"></script>
</body>

</html>