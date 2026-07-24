<?php
require_once 'auth_check.php';
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();

 $booking_id = isset($_GET['id']) ? $_GET['id'] : 0;

 $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_id = ?");
 $stmt->execute([$booking_id]);
 $booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    $_SESSION['msg'] = "Booking not found.";
    $_SESSION['msg_type'] = "error";
    header("Location: bookings.php");
    exit;
}

 $stmt_user = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
 $stmt_user->execute([$booking['user_id']]);
 $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

 $stmt_equip = $pdo->prepare("SELECT * FROM equipment WHERE equipment_id = ?");
 $stmt_equip->execute([$booking['equipment_id']]);
 $equip = $stmt_equip->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Booking Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; padding: 40px; }
        .card { border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .detail-label { font-weight: bold; color: #555; }

        @media (max-width: 1024px) {
            body {
                padding: 25px;
            }
            .card {
                padding: 20px !important;
            }
        }

        @media (max-width: 767.98px) {
            body {
                padding: 15px;
            }
            .card {
                padding: 16px !important;
            }
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start !important;
            }
            .d-flex.justify-content-between .btn {
                width: 100%;
                text-align: center;
            }
            h3 {
                font-size: 1.3rem;
            }
            h5 {
                font-size: 1.05rem;
            }
            .detail-row {
                flex-direction: column;
                gap: 2px;
                padding: 8px 0;
            }
            .detail-label {
                font-size: 0.85rem;
            }
            .detail-row span:last-child,
            .detail-row div:last-child {
                font-size: 0.92rem;
                word-break: break-all;
            }
            .d-flex.align-items-center img {
                width: 60px;
                height: 60px;
            }
            .col-md-6:first-child {
                margin-bottom: 15px;
            }
        }

        @media (max-width: 575.98px) {
            body {
                padding: 10px;
            }
            .card {
                padding: 12px !important;
            }
            h3 {
                font-size: 1.15rem;
            }
            h5 {
                font-size: 0.98rem;
            }
            h6 {
                font-size: 0.92rem;
            }
            .detail-row {
                padding: 7px 0;
            }
            .detail-label {
                font-size: 0.82rem;
            }
            .detail-row span:last-child,
            .detail-row div:last-child {
                font-size: 0.88rem;
            }
            .d-flex.align-items-center img {
                width: 50px;
                height: 50px;
            }
            .fs-5 {
                font-size: 1rem !important;
            }
            .badge {
                font-size: 0.75rem;
            }
        }

        @media (max-width: 399.98px) {
            body {
                padding: 6px;
            }
            .card {
                padding: 10px !important;
            }
            h3 {
                font-size: 1.02rem;
            }
            h5 {
                font-size: 0.9rem;
            }
            h6 {
                font-size: 0.85rem;
            }
            .detail-row {
                padding: 6px 0;
            }
            .detail-label {
                font-size: 0.78rem;
            }
            .detail-row span:last-child,
            .detail-row div:last-child {
                font-size: 0.82rem;
            }
            .d-flex.align-items-center img {
                width: 44px;
                height: 44px;
            }
            .btn {
                font-size: 0.85rem;
                padding: 6px 10px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fa-solid fa-file-invoice"></i> Booking Details #<?= $booking['booking_id'] ?></h3>
            <a href="bookings.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h5 class="text-muted mb-3">User Information</h5>
                <div class="detail-row"><span class="detail-label">Name:</span> <?= $user['first_name'] . ' ' . $user['last_name'] ?></div>
                <div class="detail-row"><span class="detail-label">Email:</span> <?= $user['email'] ?></div>
                <div class="detail-row"><span class="detail-label">Phone:</span> <?= $user['phone'] ?></div>
                
                <h5 class="text-muted mb-3 mt-4">Equipment Information</h5>
                <div class="d-flex align-items-center mb-3">
                    <img src="../images/<?= $equip['image_url'] ?>" width="80" height="80" class="rounded me-3">
                    <div>
                        <h6 class="mb-0"><?= $equip['name'] ?></h6>
                        <small class="text-muted"><?= $equip['category'] ?></small>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <h5 class="text-muted mb-3">Booking Details</h5>
                <div class="detail-row"><span class="detail-label">Pickup Date:</span> <?= date('F j, Y', strtotime($booking['pickup_date'])) ?></div>
                <div class="detail-row"><span class="detail-label">Return Date:</span> <?= date('F j, Y', strtotime($booking['return_date'])) ?></div>
                <div class="detail-row"><span class="detail-label">Duration:</span> 
                    <?php 
                        $start = new DateTime($booking['pickup_date']);
                        $end = new DateTime($booking['return_date']);
                        echo $start->diff($end)->d . " Days"; 
                    ?>
                </div>
                <div class="detail-row"><span class="detail-label">Total Amount:</span> <span class="fs-5 text-success fw-bold">$<?= number_format($booking['total_amount'], 2) ?></span></div>
                <div class="detail-row"><span class="detail-label">Status:</span> 
                    <?php 
                        $badgeClass = ($booking['status'] == 'confirmed') ? 'bg-success' : 'bg-danger';
                        echo "<span class='badge $badgeClass text-uppercase'>" . $booking['status'] . "</span>";
                    ?>
                </div>
                <div class="detail-row"><span class="detail-label">Booked On:</span> <?= date('Y-m-d H:i', strtotime($booking['created_at'])) ?></div>
            </div>
        </div>
    </div>
</div>

</body>
</html>