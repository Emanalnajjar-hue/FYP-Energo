<?php
require_once 'auth_check.php';
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
 $csrf_token = $_SESSION['csrf_token'];

 $booking     = null;   
 $lookup_msg  = "";    
 $lookup_type = "";     
 $confirm_msg = "";     
 $confirm_type = "";

function extract_qr_token($raw) {
    $raw = trim($raw);
    if (preg_match('/^BK-TOKEN-([a-f0-9]{64})$/i', $raw, $m)) {
        return strtolower($m[1]);
    }
    if (preg_match('/^[a-f0-9]{64}$/i', $raw)) {
        return strtolower($raw);
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'lookup') {

    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $lookup_msg  = "Invalid request (security token mismatch).";
        $lookup_type = "danger";
    } else {
        $raw_input = $_POST['qr_input'] ?? '';
        $token = extract_qr_token($raw_input);

        if (!$token) {
            $lookup_msg  = "Invalid QR format. Could not read a valid ticket token.";
            $lookup_type = "danger";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM bookings WHERE qr_token = ?");
            $stmt->execute([$token]);
            $found = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$found) {
                $lookup_msg  = "No booking matches this QR code. It may be forged or invalid.";
                $lookup_type = "danger";
            } elseif ($found['status'] === 'cancelled') {
                $lookup_msg  = "This booking was cancelled. Do not release the equipment.";
                $lookup_type = "danger";
            } elseif ($found['status'] === 'completed') {
                $lookup_msg  = "This booking is already completed.";
                $lookup_type = "warning";
            } elseif ($found['status'] === 'pending') {
                $lookup_msg  = "This booking is not paid/confirmed yet.";
                $lookup_type = "warning";
            } elseif ($found['status'] === 'active' && !empty($found['picked_up_at'])) {
                $lookup_msg  = "This ticket was already used for pickup on " .
                                date('Y-m-d H:i', strtotime($found['picked_up_at'])) . ". Do not release equipment again.";
                $lookup_type = "danger";
                $booking = $found; 
            } else {
                $lookup_msg  = "Valid ticket. Review details before confirming pickup.";
                $lookup_type = "success";
                $booking = $found;
            }

            if ($booking) {
                $stmt_equip = $pdo->prepare("SELECT name, image_url, location FROM equipment WHERE equipment_id = ?");
                $stmt_equip->execute([$booking['equipment_id']]);
                $equip = $stmt_equip->fetch(PDO::FETCH_ASSOC);

                $booking['product_name']  = $equip ? $equip['name'] : 'Deleted Item';
                $booking['product_image'] = $equip ? $equip['image_url'] : 'default.png';
                $booking['branch_name']   = $equip ? $equip['location'] : 'N/A';

                $stmt_user = $pdo->prepare("SELECT first_name, last_name, phone FROM users WHERE user_id = ?");
                $stmt_user->execute([$booking['user_id']]);
                $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

                $booking['first_name'] = $user ? $user['first_name'] : '';
                $booking['last_name']  = $user ? $user['last_name'] : '';
                $booking['phone']      = $user ? $user['phone'] : '';
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm') {

    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $confirm_msg  = "Invalid request (security token mismatch).";
        $confirm_type = "danger";
    } else {
        $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_id = ? FOR UPDATE");
            $stmt->execute([$booking_id]);
            $b = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$b) {
                throw new Exception("Booking not found.");
            }

            if ($b['status'] === 'cancelled') {
                throw new Exception("This booking was cancelled and cannot be picked up.");
            }
            if ($b['status'] === 'completed') {
                throw new Exception("This booking is already completed.");
            }
            if ($b['status'] === 'pending') {
                throw new Exception("This booking is not confirmed/paid yet.");
            }
            if ($b['status'] === 'active' && !empty($b['picked_up_at'])) {
                throw new Exception("This ticket was already used for pickup on " .
                    date('Y-m-d H:i', strtotime($b['picked_up_at'])) . ".");
            }

            $stmt_update = $pdo->prepare("UPDATE bookings SET status = 'active', picked_up_at = NOW() WHERE booking_id = ?");
            $stmt_update->execute([$booking_id]);

            $pdo->commit();

            $confirm_msg  = "Pickup confirmed successfully for booking #" . $booking_id . ". Equipment can be released.";
            $confirm_type = "success";

        } catch (Exception $e) {
            $pdo->rollBack();
            $confirm_msg  = "Error: " . $e->getMessage();
            $confirm_type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energo - Verify Pickup Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { height: 100vh; background-color: #343a40; padding-top: 20px; position: fixed; left: 0; width: 16.666667%; z-index: 1000; overflow-y: auto;}
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background-color: #0d6efd; }
        .content { margin-left: 16.666667%; padding: 30px; }
        .verify-card { max-width: 640px; }
        .booking-detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .booking-detail-row span:first-child { color: #666; }
        #reader { width: 100%; max-width: 400px; margin: 0 auto 15px; }

        .sidebar-toggle-btn {
            display: none;
            background: #343a40;
            color: #fff;
            border: none;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                width: 250px;
                left: -250px;
                transition: left 0.3s ease;
            }
            .sidebar.show { left: 0; }
            .content {
                margin-left: 0;
                padding: 20px;
            }
            .sidebar-toggle-btn { display: inline-block; }
            .sidebar-overlay.show { display: block; }
            .verify-card { max-width: 100%; }
            #reader { max-width: 300px; }
        }

        @media (max-width: 767.98px) {
            .content { padding: 15px; }
            h2 { font-size: 1.5rem; }
            
            .input-group {
                display: flex;
                flex-direction: column;
                gap: 12px; 
            }
            .input-group .form-control {
                width: 100%;
                border-radius: 8px !important;
                border: 1.5px solid #dee2e6 !important; 
                padding: 14px 16px !important; 
                font-size: 16px !important; 
                height: auto !important; 
                transition: border-color 0.2s, box-shadow 0.2s;
            }
            .input-group .form-control:focus {
                border-color: #80bdff !important;
                box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15) !important;
                outline: none;
            }
            .input-group .btn {
                width: 100%;
                border-radius: 8px !important;
                padding: 14px !important;
                font-size: 16px !important;
                font-weight: 500;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }

            .booking-detail-row {
                flex-direction: column;
                gap: 4px;
                padding: 12px 0;
            }
            .booking-detail-row span:first-child {
                font-size: 0.9rem;
                color: #888;
                margin-bottom: 2px;
            }
            .booking-detail-row strong {
                font-size: 1.05rem; 
                word-break: break-word;
            }

            #reader { max-width: 260px; }
        }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar" id="sidebar">
            <h4 class="text-white text-center mb-4"><i class="fa-solid fa-bolt text-warning"></i> Energo Admin</h4>
            <a href="home.php"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="equipment.php"><i class="fa-solid fa-car-battery"></i> Equipment</a>
            <a href="bookings.php"><i class="fa-solid fa-calendar-check"></i> Bookings</a>
            <a href="verify_ticket.php" class="active"><i class="fa-solid fa-qrcode"></i> Verify Ticket</a>
            <a href="delivery_requests.php"><i class="fa-solid fa-truck-fast"></i> Delivery Requests</a>
            <a href="maintenance.php"><i class="fa-solid fa-screwdriver-wrench"></i> Maintenance</a>
            <a href="users.php"><i class="fa-solid fa-users"></i> Users</a>
            <a href="testimonials.php"><i class="fa-solid fa-star"></i> Testimonials</a>
            <a href="wallets.php"><i class="fa-solid fa-wallet"></i> Wallets</a>
            <a href="../pages/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>

        <div class="col-md-10 content">
            <button class="sidebar-toggle-btn" id="sidebarToggleBtn">
                <i class="fa-solid fa-bars"></i>
            </button>

            <h2 class="mb-4"><i class="fa-solid fa-qrcode"></i> Verify Pickup Ticket</h2>

            <div class="card shadow-sm border-0 verify-card">
                <div class="card-body">

                    <?php if ($confirm_msg): ?>
                        <div class="alert alert-<?= htmlspecialchars($confirm_type) ?>"><?= htmlspecialchars($confirm_msg) ?></div>
                    <?php endif; ?>

                    <div id="reader"></div>
                    <div class="text-center mb-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleScanner">
                            <i class="fa-solid fa-camera"></i> Start Camera Scanner
                        </button>
                    </div>

                    <form method="POST" action="verify_ticket.php" class="mb-2">
                        <input type="hidden" name="action" value="lookup">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <label class="form-label">QR Code Content / Token</label>
                        <div class="input-group">
                            <input type="text" name="qr_input" id="qr_input" class="form-control" placeholder="Scan or paste QR content here" autofocus required>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Verify</button>
                        </div>
                    </form>

                    <?php if ($lookup_msg): ?>
                        <div class="alert alert-<?= htmlspecialchars($lookup_type) ?> mt-3"><?= htmlspecialchars($lookup_msg) ?></div>
                    <?php endif; ?>

                    <?php if ($booking): ?>
                        <hr>
                        <div class="d-flex align-items-center mb-3">
                            <img src="../images/<?= htmlspecialchars($booking['product_image']) ?>" width="60" height="60" class="rounded me-3" onerror="this.src='../images/default.png'">
                            <div>
                                <h6 class="mb-0"><?= htmlspecialchars($booking['product_name']) ?></h6>
                                <small class="text-muted"><?= ucfirst(htmlspecialchars($booking['branch_name'])) ?> Branch</small>
                            </div>
                        </div>

                        <div class="booking-detail-row"><span>Booking ID</span><strong>#<?= (int)$booking['booking_id'] ?></strong></div>
                        <div class="booking-detail-row"><span>Customer</span><strong><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></strong></div>
                        <div class="booking-detail-row"><span>Phone</span><strong><?= htmlspecialchars($booking['phone']) ?></strong></div>
                        <div class="booking-detail-row"><span>Pickup Date</span><strong><?= date('M d, Y', strtotime($booking['pickup_date'])) ?></strong></div>
                        <div class="booking-detail-row"><span>Return Date</span><strong><?= date('M d, Y', strtotime($booking['return_date'])) ?></strong></div>
                        <div class="booking-detail-row"><span>Amount Paid</span><strong>$<?= number_format($booking['total_amount'], 2) ?></strong></div>
                        <div class="booking-detail-row"><span>Status</span><strong><?= ucfirst(htmlspecialchars($booking['status'])) ?></strong></div>

                        <?php
                            $eligible = in_array($booking['status'], ['confirmed', 'active']) && empty($booking['picked_up_at']);
                        ?>
                        <?php if ($eligible): ?>
                            <form method="POST" action="verify_ticket.php" class="mt-3" onsubmit="return confirm('Confirm equipment pickup for this booking?');">
                                <input type="hidden" name="action" value="confirm">
                                <input type="hidden" name="booking_id" value="<?= (int)$booking['booking_id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fa-solid fa-check-circle"></i> Confirm Pickup & Release Equipment
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
    let scannerRunning = false;
    let html5QrCode;

    document.getElementById('toggleScanner').addEventListener('click', function () {
        const btn = this;
        if (!scannerRunning) {
            html5QrCode = new Html5Qrcode("reader");
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: 250 },
                (decodedText) => {
                    document.getElementById('qr_input').value = decodedText;
                    html5QrCode.stop().then(() => {
                        scannerRunning = false;
                        btn.innerHTML = '<i class="fa-solid fa-camera"></i> Start Camera Scanner';
                    });
                },
                () => { }
            ).then(() => {
                scannerRunning = true;
                btn.innerHTML = '<i class="fa-solid fa-stop"></i> Stop Camera Scanner';
            }).catch(() => {
                alert("Could not access camera. You can still paste the QR content manually.");
            });
        } else {
            html5QrCode.stop().then(() => {
                scannerRunning = false;
                btn.innerHTML = '<i class="fa-solid fa-camera"></i> Start Camera Scanner';
            });
        }
    });

    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const overlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebar.classList.add('show');
        overlay.classList.add('show');
    }
    function closeSidebar() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    }

    toggleBtn.addEventListener('click', function () {
        sidebar.classList.contains('show') ? closeSidebar() : openSidebar();
    });
    overlay.addEventListener('click', closeSidebar);
</script>
</body>
</html>