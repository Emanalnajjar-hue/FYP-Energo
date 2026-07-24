<?php
session_start();
require_once '../config/db.php'; 

 $database = new Database();
 $pdo = $database->getConnection();

 $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

 $user_data = [
   'name' => 'User Name',
   'email' => 'user@example.com',
   'phone' => 'N/A',
   'location' => 'Palestine-Gaza', 
   'balance' => 0.00
];

 $bookings = [];

if ($user_id > 0) {
    $stmt_user = $pdo->prepare("SELECT first_name, last_name, phone, email FROM users WHERE user_id = ?");
    $stmt_user->execute([$user_id]);
    $u = $stmt_user->fetch(PDO::FETCH_ASSOC);
    
    if ($u) {
        $user_data['name'] = $u['first_name'] . ' ' . $u['last_name'];
        $user_data['phone'] = $u['phone'];
        $user_data['email'] = $u['email'];
    }

    $stmt_wallet = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
    $stmt_wallet->execute([$user_id]);
    $w = $stmt_wallet->fetch(PDO::FETCH_ASSOC);
    if ($w) {
        $user_data['balance'] = $w['balance'];
    }

    $sql = "SELECT booking_id, pickup_date, return_date, total_amount, status, equipment_id, qr_token 
            FROM bookings 
            WHERE user_id = ? 
            ORDER BY booking_id DESC";
            
    $stmt_bookings = $pdo->prepare($sql);
    $stmt_bookings->execute([$user_id]);
    $raw_bookings = $stmt_bookings->fetchAll(PDO::FETCH_ASSOC);

   
    $equip_ids = [];
    foreach ($raw_bookings as $b) {
        if (!empty($b['equipment_id'])) {
            $equip_ids[] = (int)$b['equipment_id'];
        }
    }
    $equip_ids = array_unique($equip_ids);
    
    $equipment_data = [];
    if (!empty($equip_ids)) {
        $placeholders = implode(',', array_fill(0, count($equip_ids), '?'));
        $eq_sql = "SELECT equipment_id, name, image_url, location, status FROM equipment WHERE equipment_id IN ($placeholders)";
        $eq_stmt = $pdo->prepare($eq_sql);
        $eq_stmt->execute(array_values($equip_ids));
        
        foreach ($eq_stmt->fetchAll(PDO::FETCH_ASSOC) as $eq) {
            $equipment_data[$eq['equipment_id']] = $eq;
        }
    }

    $bookings = [];
    foreach ($raw_bookings as $item) {
        $eq_id = $item['equipment_id'];
        
        if (!empty($eq_id) && isset($equipment_data[$eq_id])) {
            $item['equipment_name'] = $equipment_data[$eq_id]['name'];
            $item['equipment_image'] = $equipment_data[$eq_id]['image_url'];
            $item['equipment_location'] = $equipment_data[$eq_id]['location'];
            $item['equipment_status'] = $equipment_data[$eq_id]['status'];
        } else {
            $item['equipment_name'] = 'Deleted Item';
            $item['equipment_image'] = 'default.png';
            $item['equipment_location'] = 'N/A';
            $item['equipment_status'] = 'available';
        }
        
        $bookings[] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Booking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/mybooking-style.css?v=1.5">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>

<body>

    <div class="main-wrapper">
        <div class="sidebar">
            <div class="profile-img">
                <img src="../images/booking.jpg" alt="Profile Picture">
            </div>
            <h3><?= htmlspecialchars($user_data['name']) ?></h3>

            <div class="user-info">
                <div class="input-group">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" id="userEmail" value="<?= htmlspecialchars($user_data['email']) ?>" readonly>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-phone"></i>
                    <input type="text" id="userPhone" value="<?= htmlspecialchars($user_data['phone']) ?>" readonly>
                </div>
                <div class="input-group">
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" id="userLocation" value="<?= htmlspecialchars($user_data['location']) ?>" readonly>
                </div>
            </div>

            <div class="status"><i class="fa-solid fa-circle-check"></i> Verified User</div>
            <p><i class="fa-solid fa-shield-halved"></i> Silver Member</p>

            <div class="rentals-box">
                <i class="fa-solid fa-wallet"></i> Current Balance: <strong>$<?= number_format($user_data['balance'], 2) ?></strong><br>
                <i class="fa-solid fa-chart-line"></i> Total Rentals: <strong><?= count($bookings) ?></strong>
            </div>
        </div>

        <div class="container">
            <h1>My Booking</h1>
            <p>View and manage all your current and past power equipment rentals</p>
            
            <?php if (isset($_SESSION['alert_message'])): ?>
                <?php 
                    $msg = $_SESSION['alert_message']; 
                    $bg_color = ($msg['type'] == 'success') ? '#d4edda' : '#f8d7da';
                    $text_color = ($msg['type'] == 'success') ? '#155724' : '#721c24';
                ?>
                <div style="background-color: <?= $bg_color ?>; color: <?= $text_color ?>; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; border: 1px solid rgba(0,0,0,0.1);">
                    <?= htmlspecialchars($msg['text']) ?>
                </div>
                <?php unset($_SESSION['alert_message']); ?>
            <?php endif; ?>

            <hr>

            <?php if (count($bookings) > 0): ?>
                <?php foreach ($bookings as $booking): 
                    $row_class = '';
                    if ($booking['status'] == 'cancelled') $row_class = 'cancelled-row';
                    if ($booking['status'] == 'completed') $row_class = 'completed-row';

                    $qr_eligible = in_array($booking['status'], ['confirmed', 'active']) && !empty($booking['qr_token']);
                ?>
                    <div class="booking-row <?= $row_class ?>">
                        <img src="../images/<?= htmlspecialchars($booking['equipment_image']) ?>" class="product-img" onerror="this.src='../images/default.png'">
                        <div class="details">
                            <h3><?= htmlspecialchars($booking['equipment_name']) ?></h3>
                            <p>
                                <i class="fa-regular fa-calendar"></i> 
                                <?= date('d M Y', strtotime($booking['pickup_date'])) ?> → <?= date('d M Y', strtotime($booking['return_date'])) ?>
                            </p>
                            <p>
                                <i class="fa-solid fa-location-dot"></i> 
                                <?= htmlspecialchars($booking['equipment_location']) ?>
                            </p>
                            <p><i class="fa-solid fa-tag"></i> Price: $<?= number_format($booking['total_amount'], 2) ?></p>
                            
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 8px;">
                                <div class="status-badge status-<?= $booking['status'] ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </div>

                                <?php if (isset($booking['equipment_status']) && $booking['equipment_status'] === 'under_maintenance' && in_array($booking['status'], ['confirmed', 'active'])): ?>
                                    <span class="status-badge status-under-maintenance-eq">
                                        <i class="fas fa-wrench"></i> Under Maintenance
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($qr_eligible): ?>
                        <div class="qr-mini-container"
                             data-qr-token="<?= htmlspecialchars($booking['qr_token'], ENT_QUOTES) ?>">
                            <div class="qr-mini"></div>
                            <span>Booking QR</span>
                        </div>
                        <?php endif; ?>

                        <?php if (in_array($booking['status'], ['pending', 'confirmed', 'active'])): ?>
                            <form action="cancel_booking.php" method="POST" class="cancel-form">
                                <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                <button type="submit" class="cancel-btn" 
                                        onclick="return confirm('Are you sure you want to cancel this booking?\n\nYour paid amount will be refunded MINUS a $5.00 cancellation fee.');">
                                    Cancel
                                </button>
                            </form>
                        <?php else: ?>
                            <button class="cancel-btn disabled" disabled>
                                <?= ucfirst($booking['status']) ?>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-bookings">
                    <p>No bookings found.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script src="../script/mybooking-script.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll(".qr-mini-container").forEach(container => {
                const qrToken = container.dataset.qrToken;
                const qrData = `BK-TOKEN-${qrToken}`;
                const target = container.querySelector(".qr-mini");

                new QRCode(target, {
                    text: qrData,
                    width: 90,
                    height: 90,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            });
        });
    </script>
</body>
</html>