<?php
session_start();
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();

 $user_id    = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
 $booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if ($user_id == 0) {
    header("Location: login.php");
    exit();
}

if ($booking_id === 0) {
    die("Invalid booking ID.");
}

 $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_id = ?");
 $stmt->execute([$booking_id]);
 $booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Booking not found.");
}

if ((int)$booking['user_id'] !== (int)$user_id) {
    http_response_code(403);
    die("Unauthorized: this booking does not belong to your account.");
}

 $stmt_equip = $pdo->prepare("SELECT name, image_url, location FROM equipment WHERE equipment_id = ?");
 $stmt_equip->execute([$booking['equipment_id']]);
 $equipment = $stmt_equip->fetch(PDO::FETCH_ASSOC);

 $product_name  = $equipment ? $equipment['name'] : 'Deleted Item';
 $product_image = $equipment ? $equipment['image_url'] : 'default.png';
 $branch_name   = $equipment ? $equipment['location'] : 'N/A';

 $qr_eligible = in_array($booking['status'], ['confirmed', 'active']);

 $pickup_date = date("d M Y - h:i A", strtotime($booking['pickup_date']));
 $return_date = date("d M Y - h:i A", strtotime($booking['return_date']));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Pickup Ticket #<?= str_pad($booking_id, 5, '0', STR_PAD_LEFT) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/qr-style.css">
</head>

<body>

    <main class="ticket-page-container">

        <section class="payment-info-panel">
            <div class="badge-box">
                <i class="fas fa-check-circle" style="color: #28a745; font-size: 24px;"></i>
                <span>Payment Successful</span>
            </div>
            
            <div class="success-message">
                <h2>Thank You!</h2>
                <p>Your booking has been confirmed.</p>
                <p>Please scan the QR code to receive your equipment.</p>
            </div>

            <div class="details-list">
                <div class="detail-item">
                    <span>Booking ID:</span> 
                    <strong>#<?= str_pad($booking_id, 5, '0', STR_PAD_LEFT) ?></strong>
                </div>
                <div class="detail-item">
                    <span>Product:</span> 
                    <strong><?= htmlspecialchars($product_name) ?></strong>
                </div>
                <div class="detail-item">
                    <span>Paid Amount:</span> 
                    <strong>$<?= number_format($booking['total_amount'], 2) ?> via Wallet</strong>
                </div>
                <div class="detail-item">
                    <span>Location:</span> 
                    <strong><?= ucfirst($branch_name) ?> Branch</strong>
                </div>
                <div class="detail-item">
                    <span>Pickup Date:</span> 
                    <strong><?= $pickup_date ?></strong>
                </div>
                <div class="detail-item">
                    <span>Return Date:</span> 
                    <strong><?= $return_date ?></strong>
                </div>
            </div>
        </section>

        <section class="ticket-qr-panel">
            <h3>Digital Ticket</h3>

            <?php if ($qr_eligible && !empty($booking['qr_token'])): ?>
                <div id="qrcode" class="qr-placeholder"></div>
                <p style="color: #666; font-size: 0.9rem; margin-top: 10px;">
                    Show this code at the collection point.
                </p>
            <?php else: ?>
                <p style="color:#721c24; font-weight:bold;">
                    QR code is not available for this booking (status: <?= htmlspecialchars($booking['status']) ?>).
                </p>
            <?php endif; ?>

            <a href="#" onclick="window.print(); return false;" class="btn-download">
                Download / Print Ticket
                <i class="fas fa-print"></i>
            </a>

            <a href="home.php" class="btn-home">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </section>

    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <?php if ($qr_eligible && !empty($booking['qr_token'])): ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const qrElement = document.getElementById("qrcode");
            const qrToken = "<?= htmlspecialchars($booking['qr_token'], ENT_QUOTES) ?>";
            const qrData = "BK-TOKEN-" + qrToken;

            if (qrElement) {
                new QRCode(qrElement, {
                    text: qrData, 
                    width: 200,
                    height: 200,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>