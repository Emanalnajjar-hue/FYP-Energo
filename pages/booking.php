<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


require_once '../config/db.php';  

 $database = new Database();
 $pdo = $database->getConnection();

 $product_id = isset($_GET['product_id']) ? $_GET['product_id'] : 0;
 $product = null;

if ($product_id) {
    $stmt = $pdo->prepare("SELECT * FROM equipment WHERE equipment_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

 $is_maintenance = false;
 $is_booked = false;

if ($product) {
    $is_maintenance = ($product['status'] === 'under_maintenance');
    $is_booked = ($product['status'] === 'booked');
}

 $accessories = [];
if ($product) {
    $accessory_categories = ['cable', 'kit'];
    $placeholders = implode(',', array_fill(0, count($accessory_categories), '?'));
    
    $sql = "SELECT * FROM equipment 
            WHERE category IN ($placeholders) 
            AND equipment_id != ? 
            AND status = 'available'";
            
    $stmt_acc = $pdo->prepare($sql);
    $stmt_acc->execute([...$accessory_categories, $product_id]);
    $accessories = $stmt_acc->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Energy Booking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/booking-style.css?v=1.2">
    <style>
      
    </style>

</head>

<body>

    <header class="main-header" style="background-image: url('../images/header.png');">
        <div class="header-overlay"></div> 
        <div class="content-wrapper">
        <h1>Practical Energy <span>Booking Made Easy</span></h1>
            <p>Rent batteries, generators and cables easily and safely in Gaza.</p>
            <div class="scroll-indicator"><i class="fas fa-chevron-down"></i></div>
        </div>
    </header>

    <main class="main-container">

        <?php if (!$product): ?>
            <div class="maintenance-block">
                <div class="maintenance-icon-wrapper" style="background: linear-gradient(135deg, #e5e7eb, #d1d5db);">
                    <i class="fas fa-question" style="color: #6b7280;"></i>
                </div>
                <h2 style="color: #6b7280;">Product Not Found</h2>
                <p>The product you're looking for doesn't exist or has been removed.</p>
                <a href="products.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Products</a>
            </div>

        <?php elseif ($is_maintenance): ?>
            <div class="maintenance-block">
                <div class="maintenance-icon-wrapper">
                    <i class="fas fa-wrench"></i>
                </div>
                <h2>This Equipment is Under Maintenance</h2>
                <p>Sorry, this equipment is currently undergoing maintenance and is not available for booking. Please check back later or browse other products.</p>
                
                <div class="product-reminder">
                    <img src="../images/<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="info">
                        <h4><?= htmlspecialchars($product['name']) ?></h4>
                        <span><i class="fas fa-wrench"></i> Under Maintenance</span>
                    </div>
                </div>

                <a href="products.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Products</a>
            </div>

        <?php else: ?>
            
            <?php if ($is_booked): ?>
                <div style="max-width: 900px; margin: 20px auto 0;">
                    <div class="booked-notice">
                        <i class="fas fa-info-circle"></i>
                        <p>This equipment is currently booked, but you can still reserve it for a later date after the current booking ends. Just select your preferred dates below.</p>
                    </div>
                </div>
            <?php endif; ?>

            <section class="booking-bar">
                <h3 class="section-title"><span class="circle-num">1</span> <span class="green-text">Location & Dates</span></h3>
                
                <div class="booking-inputs">
                    <div class="input-group">
                        <label><i class="fas fa-calendar-alt"></i> Pickup Date & Time</label>
                        <div class="date-time-wrapper">
                            <input type="date" id="pickup-date">
                            <input type="time" id="pickup-time">
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label><i class="fas fa-calendar-alt"></i> Return Date & Time</label>
                        <div class="date-time-wrapper">
                            <input type="date" id="return-date">
                            <input type="time" id="return-time">
                        </div>
                    </div>

                    <div class="input-group">
                        <label><i class="fas fa-map-marker-alt"></i> Location</label>
                        <select name="location" id="location-select">
                            <option value="" disabled selected>Select Location</option>
                            <option value="north">North Gaza / Gaza City</option>
                            <option value="middle">Middle Area / Gaza City</option>
                            <option value="south">South Gaza / Gaza City</option>
                        </select>
                        <button class="update-btn" id="check-availability-btn">Check Availability <i class="fas fa-check"></i></button>
                    </div>
                </div>
                <div id="availability-message" style="margin-top: 15px; font-weight: bold; display: none;"></div>
            </section>

            <section class="main-grid-layout">
                <aside class="summary-section">
                    <h3 class="section-title"><span class="circle-num">2</span> <span class="green-text">Your Booking Summary</span></h3>

                    <div class="summary-card summary-product" 
                         data-price="<?= $product['price_per_day'] ?>" 
                         data-location="<?= $product['location'] ?>">
                        
                        <img src="../images/<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <div>
                            <h4><?= htmlspecialchars($product['name']) ?></h4>
                            <p><?= htmlspecialchars($product['description']) ?></p>
                            <p class="green-text">$<?= number_format($product['price_per_day'], 2) ?> / day</p>
                            <input type="hidden" id="backend-product-id" value="<?= $product['equipment_id'] ?>">
                        </div>
                    </div>

                    <div class="summary-card">
                        <p><strong>Add-ons & Accessories</strong></p>
                        <?php if (!empty($accessories)): ?>
                            <?php foreach ($accessories as $acc): ?>
                                <div class="addon-item">
                                    <label>
                                        <input type="checkbox" class="addon-checkbox" data-price="<?= $acc['price_per_day'] ?>">
                                        <img src="../images/<?= htmlspecialchars($acc['image_url']) ?>" class="addon-img">
                                        <span style="font-size: 0.9em;">
                                            <?= htmlspecialchars($acc['name']) ?> - 
                                            <span style="color: #10482D;">$<?= number_format($acc['price_per_day'], 2) ?>/day</span>
                                        </span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #777;">No accessories available for this item.</p>
                        <?php endif; ?>
                    </div>

                    <div class="summary-card">
                        <div class="price-row">
                            <span>Base Price (<span id="days-count">1</span> days)</span>
                            <span id="base-total">$0.00</span>
                        </div>
                        <div class="price-row">
                            <span>Accessories</span>
                            <span id="addons-total">$0.00</span>
                        </div>
                        <hr>
                        <div class="total-row">
                            <strong>Total Amount</strong>
                            <strong class="total-price" id="grand-total">$0.00</strong>
                        </div>
                        
                        <button class="book-now-btn" id="book-btn" disabled>Book Now <i class="fas fa-arrow-right"></i></button>
                    </div>

                    <div class="summary-card payment-section">
                        <p><strong>Secure Payment with PayPal</strong></p>
                        <div class="payment-info">
                            <img src="../images/palpay-logo1.png" alt="PayPal" class="payment-img">
                            <span>Safe, fast and secure payments via PayPal</span>
                        </div>
                    </div>
                </aside>
            </section>

        <?php endif; ?>

    </main>

    <script src="../script/booking-script.js"></script>
    
</body>
</html>