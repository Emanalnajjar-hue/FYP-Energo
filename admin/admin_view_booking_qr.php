<?php

require_once 'auth_check.php';
require_once '../config/db.php';

$database = new Database();
$pdo = $database->getConnection();

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

$stmt = $pdo->prepare("
    SELECT b.*, e.name as product_name, e.image_url as product_image, e.location as branch_name,
           u.first_name, u.last_name
    FROM bookings b
    INNER JOIN equipment e ON b.equipment_id = e.equipment_id
    INNER JOIN users u ON b.user_id = u.user_id
    WHERE b.booking_id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Booking not found.");
}

$qr_eligible = in_array($booking['status'], ['confirmed', 'active']) && !empty($booking['qr_token']);

$pickup_date = date("d M Y - h:i A", strtotime($booking['pickup_date']));
$return_date = date("d M Y - h:i A", strtotime($booking['return_date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Booking QR #<?= $booking_id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; padding: 30px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .qr-card { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); text-align: center; }
        .qr-card h4 { margin-bottom: 5px; }
        .qr-card .sub { color: #666; margin-bottom: 20px; }
        .details-table { text-align: left; margin: 20px 0; }
        .details-table td { padding: 6px 0; }
        .details-table td:first-child { color: #666; width: 40%; }
        #qrcode { margin: 20px auto; display: inline-block; }
    </style>
</head>
<body>

    <div class="qr-card">
        <h4>Booking #<?= str_pad($booking_id, 5, '0', STR_PAD_LEFT) ?></h4>
        <p class="sub">Admin QR Viewer</p>

        <table class="details-table">
            <tr><td>Customer</td><td><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></td></tr>
            <tr><td>Product</td><td><?= htmlspecialchars($booking['product_name']) ?></td></tr>
            <tr><td>Location</td><td><?= ucfirst(htmlspecialchars($booking['branch_name'])) ?> Branch</td></tr>
            <tr><td>Pickup</td><td><?= $pickup_date ?></td></tr>
            <tr><td>Return</td><td><?= $return_date ?></td></tr>
            <tr><td>Amount</td><td>$<?= number_format($booking['total_amount'], 2) ?></td></tr>
            <tr><td>Status</td><td><?= ucfirst($booking['status']) ?></td></tr>
        </table>

        <?php if ($qr_eligible): ?>
            <div id="qrcode"></div>
            <p class="text-muted" style="font-size:0.85rem;">This is the same QR shown to the customer.</p>
        <?php else: ?>
            <div class="alert alert-warning">
                No active QR for this booking (status: <?= htmlspecialchars($booking['status']) ?>).
            </div>
        <?php endif; ?>

        <a href="bookings.php" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left"></i> Back to Bookings</a>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <?php if ($qr_eligible): ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const qrToken = "<?= htmlspecialchars($booking['qr_token'], ENT_QUOTES) ?>";
            const qrData = `BK-TOKEN-${qrToken}`;

            new QRCode(document.getElementById("qrcode"), {
                text: qrData,
                width: 200,
                height: 200,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>