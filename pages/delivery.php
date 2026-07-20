<?php
session_start();
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();
 $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

 $toastMessage = '';
 $toastType = '';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

 $userId = $_SESSION['user_id'];
 $userName = '';
 $userPhone = '';
 $activeBookings = [];

 $stmt = $pdo->prepare("SELECT first_name, last_name, phone FROM users WHERE user_id = ?");
 $stmt->execute([$userId]);
 $uData = $stmt->fetch();
if ($uData) {
    $userName = trim(($uData['first_name'] ?? '') . ' ' . ($uData['last_name'] ?? ''));
    $userPhone = $uData['phone'] ?? '';
}

 $stmt = $pdo->prepare("
    SELECT b.booking_id, e.name as equipment_name 
    FROM bookings b 
    JOIN equipment e ON b.equipment_id = e.equipment_id 
    WHERE b.user_id = ? AND b.status = 'confirmed'
");
 $stmt->execute([$userId]);
 $activeBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $booking_id    = trim($_POST['booking_id'] ?? '');
        $full_name     = trim($_POST['full_name'] ?? '');
        $primary_phone = trim($_POST['primary_phone'] ?? '');
        $alt_phone     = trim($_POST['alt_phone'] ?? '');
        $address       = trim($_POST['address'] ?? '');
        $governorate   = trim($_POST['governorate'] ?? '');
        $notes         = trim($_POST['notes'] ?? '');

        if (empty($booking_id) || !is_numeric($booking_id)) {
            $toastMessage = 'Please select a valid booking.';
            $toastType = 'error';
        } elseif (empty($full_name) || empty($primary_phone) || empty($address) || empty($governorate)) {
            $toastMessage = 'Please fill in all required fields.';
            $toastType = 'error';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO delivery_requests (user_id, booking_id, full_name, primary_phone, alt_phone, address, governorate, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $booking_id, $full_name, $primary_phone, $alt_phone, $address, $governorate, $notes]);

            $toastMessage = 'Your delivery request has been sent successfully! We will get back to you as soon as possible.';
            $toastType = 'success';
            
            $_POST = [];
            $booking_id = ''; $full_name = ''; $primary_phone = ''; $alt_phone = ''; $address = ''; $governorate = ''; $notes = '';
        }
    } catch (PDOException $e) {
        $toastMessage = 'Error sending request. Please try again.';
        $toastType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fast Delivery & Smart Power</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/delivery-style.css">
    
    <style>
        .booking-box {
            background: #f8f9fa;
            border: 1px dashed #10482D;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .booking-box label {
            font-weight: bold;
            color: #10482D;
            margin-bottom: 10px;
            display: block;
        }
        .booking-box select {
            background-color: #fff;
        }
    </style>
</head>

<body>
    <header class="hero">
        <img src="../images/delivery.jpeg" alt="Delivery Background" class="hero-image">
        <div class="overlay">
            <h1>Fast Delivery & Smart Power</h1>
            <p>Skip the hassle. We handle the heavy lifting with our specialized delivery service for batteries and
                generators, ensuring efficiency every time</p>
            <div class="buttons">
                <a href="#delivery-form" class="btn-primary">Request a Delivery</a>
                <a href="home.php" class="btn-secondary">Back to home</a>
            </div>
        </div>
    </header>

    <main class="container" id="delivery-form">
        <section class="delivery-section">
            <div class="form-container">
                <h2><i class="fas fa-truck"></i> Request a Delivery</h2>
                
                <form method="POST" action="">
                    
                    <div class="booking-box">
                        <label><i class="fas fa-calendar-check"></i> Step 1: Select Your Booking</label>
                        <?php if (!empty($activeBookings)): ?>
                            <select name="booking_id" required>
                                <option value="" disabled <?= empty($booking_id) ? 'selected' : '' ?>>-- Choose an active booking --</option>
                                <?php foreach ($activeBookings as $bk): ?>
                                    <option value="<?= $bk['booking_id'] ?>" <?= (isset($booking_id) && $booking_id == $bk['booking_id']) ? 'selected' : '' ?>>
                                        Booking #<?= $bk['booking_id'] ?> - <?= htmlspecialchars($bk['equipment_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <p style="color: #666; margin: 0; font-size: 14px;">
                                <i class="fas fa-info-circle"></i> You don't have any active confirmed bookings to request delivery for.
                                <br><a href="mybooking.php" style="color: #10482D; font-weight: bold;">View my bookings</a>
                            </p>
                            <input type="hidden" name="booking_id" value=""> 
                        <?php endif; ?>
                    </div>

                    <input type="text" name="full_name" placeholder="Full Name" required value="<?= htmlspecialchars($userName) ?>">
                    <input type="tel" name="primary_phone" placeholder="Primary phone number" required value="<?= htmlspecialchars($userPhone) ?>">
                    <input type="tel" name="alt_phone" placeholder="Alternative phone number" value="<?= htmlspecialchars($alt_phone ?? '') ?>">
                    <input type="text" name="address" placeholder="Primary street address" required value="<?= htmlspecialchars($address ?? '') ?>">

                    <select name="governorate" required>
                        <option value="" disabled <?= empty($governorate) ? 'selected' : '' ?>>Select Governorate</option>
                        <option value="north_gaza" <?= (isset($governorate) && $governorate == 'north_gaza') ? 'selected' : '' ?>>North Gaza</option>
                        <option value="middle_gaza" <?= (isset($governorate) && $governorate == 'middle_gaza') ? 'selected' : '' ?>>Middle Gaza</option>
                        <option value="south_gaza" <?= (isset($governorate) && $governorate == 'south_gaza') ? 'selected' : '' ?>>South Gaza</option>
                    </select>

                    <textarea name="notes" rows="4" placeholder="Additional notes (optional)"><?= htmlspecialchars($notes ?? '') ?></textarea>

                    <button type="submit" class="submit-btn" <?= empty($activeBookings) ? 'disabled title="You must have an active booking first"' : '' ?>>Send Request</button>
                </form>
            </div>

            <aside class="contact-info">
                <h3>Contact Information</h3>
                <div class="info-item">
                    <i class="fas fa-shipping-fast"></i>
                    <div>
                        <h4>Fast Delivery</h4>
                        <p>Quick and timely delivery to your doorstep.</p>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-shield-halved"></i>
                    <div>
                        <h4>Safe Handling</h4>
                        <p>We handle your products with care and professionalism.</p>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h4>Live Tracking</h4>
                        <p>Track your order in real-time until it reaches you.</p>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-box"></i>
                    <div>
                        <h4>Secure Packaging</h4>
                        <p>Our products are packed securely to ensure safe delivery.</p>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-headset"></i>
                    <div>
                        <h4>Customer Support</h4>
                        <p>Our team is here to assist you anytime.</p>
                    </div>
                </div>
            </aside>
        </section>

        <section class="banner">
            <div class="banner-content">
                <img src="../images/deve.jpeg" alt="Delivery Location" class="banner-img">
                <div class="banner-text">
                    <h3>We Deliver to Your Location</h3>
                    <p>No matter where you are, we ensure your generators and batteries reach you safely and on time.
                        Our coverage spans across the region with real-time tracking.</p>
                </div>
            </div>
        </section>

        <section class="features">
            <div>
                <i class="fas fa-truck"></i>
                <span><strong>On-Time Delivery</strong><br><small>Always punctual</small></span>
            </div>
            <div>
                <i class="fas fa-shield-halved"></i>
                <span><strong>Safe & Secure</strong><br><small>Insured transport</small></span>
            </div>
            <div>
                <i class="fas fa-headset"></i>
                <span><strong>24/7 Support</strong><br><small>Here to help</small></span>
            </div>
        </section>
    </main>

    <?php if ($toastMessage): ?>
    <style>
        #inline-toast {
            position: fixed !important;
            top: 25px !important;
            bottom: auto !important;
            right: auto !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            padding: 18px 32px !important;
            border-radius: 8px !important;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2) !important;
            z-index: 999999 !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            font-size: 15px !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            max-width: 700px;
            text-align: center;
            animation: slideDown 0.4s ease forwards !important;
        }
        #inline-toast.toast-success {
            background-color: #ffffff !important;
            color: #10482D !important;
            border-left: 5px solid #10482D !important;
            font-weight: 600 !important;
        }
        #inline-toast.toast-success i { color: #28a745 !important; }
        #inline-toast.toast-error {
            background-color: #ffffff !important;
            color: #d9534f !important;
            border-left: 5px solid #d9534f !important;
            font-weight: 600 !important;
        }
        @keyframes slideDown {
            from { opacity: 0; top: -50px; }
            to { opacity: 1; top: 25px; }
        }
    </style>

    <div id="inline-toast" class="<?= $toastType === 'success' ? 'toast-success' : 'toast-error' ?>">
        <?php if($toastType === 'success'): ?>
            <i class="fa-solid fa-circle-check"></i>
        <?php else: ?>
            <i class="fa-solid fa-circle-exclamation"></i>
        <?php endif; ?>
        <?= htmlspecialchars($toastMessage) ?>
    </div>
    
    <script>
        setTimeout(() => {
            let toast = document.getElementById('inline-toast');
            if(toast) {
                toast.style.transition = 'opacity 0.4s ease, top 0.4s ease';
                toast.style.opacity = '0';
                toast.style.top = '-50px';
            }
        }, 5000);
    </script>
    <?php endif; ?>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const phoneInput = this.querySelector('input[name="primary_phone"]');
            const phoneDigits = phoneInput.value.replace(/\D/g, '');

            if (phoneDigits.length !== 10) {
                e.preventDefault();
                alert('Please enter a valid 10-digit phone number.');
                phoneInput.focus();
            }
        });
    </script>

</body>
</html>