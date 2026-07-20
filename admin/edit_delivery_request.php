<?php
require_once 'auth_check.php';
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();
 $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['msg'] = 'Invalid request ID.';
    $_SESSION['msg_type'] = 'error';
    header('Location: delivery_requests.php');
    exit;
}

 $id = (int)$_GET['id'];
 $stmt = $pdo->prepare("SELECT * FROM delivery_requests WHERE request_id = ?");
 $stmt->execute([$id]);
 $req = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$req) {
    $_SESSION['msg'] = 'Request not found.';
    $_SESSION['msg_type'] = 'error';
    header('Location: delivery_requests.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = trim($_POST['status'] ?? 'pending');

    try {
        $stmt = $pdo->prepare("UPDATE delivery_requests SET status = ? WHERE request_id = ?");
        $stmt->execute([$status, $id]);

        $_SESSION['msg'] = 'Request status updated successfully!';
        $_SESSION['msg_type'] = 'success';
        header('Location: delivery_requests.php');
        exit;
    } catch (Exception $e) {
        $msg = 'Database error: ' . $e->getMessage();
    }
}

function formatGov($gov) {
    $gov = strtolower($gov);
    switch ($gov) {
        case 'north_gaza': return 'North Gaza';
        case 'middle_gaza': return 'Middle Gaza';
        case 'south_gaza': return 'South Gaza';
        default: return ucfirst(str_replace('_', ' ', $gov));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energo - Edit Delivery Request</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { height: 100vh; background-color: #343a40; padding-top: 20px; position: fixed; left: 0; width: 16.666667%; z-index: 1000; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background-color: #0d6efd; }
        .content { margin-left: 16.666667%; padding: 30px; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- الـ Sidebar -->
        <div class="col-md-2 sidebar">
            <h4 class="text-white text-center mb-4"><i class="fa-solid fa-bolt text-warning"></i> Energo Admin</h4>
            <a href="home.php"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="equipment.php"><i class="fa-solid fa-car-battery"></i> Equipment</a>
            <a href="bookings.php"><i class="fa-solid fa-calendar-check"></i> Bookings</a>
            <a href="delivery_requests.php" class="active"><i class="fa-solid fa-truck-fast"></i> Delivery Requests</a>
            <a href="maintenance.php"><i class="fa-solid fa-screwdriver-wrench"></i> Maintenance</a>
            <a href="users.php"><i class="fa-solid fa-users"></i> Users</a>
            <a href="testimonials.php"><i class="fa-solid fa-star"></i> Testimonials</a>
            <a href="wallets.php"><i class="fa-solid fa-wallet"></i> Wallets</a>
            <a href="../pages/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>

        <div class="col-md-10 content">
            <h2 class="mb-4"><i class="fa-solid fa-truck-fast"></i> Edit Request #<?= $req['request_id'] ?></h2>

            <?php if (isset($msg)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row mb-4 p-3 bg-light rounded">
                        <div class="col-md-4">
                            <strong>Customer:</strong><br> <?= htmlspecialchars($req['full_name']) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Phone:</strong><br> <?= htmlspecialchars($req['primary_phone']) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Location:</strong><br> <?= htmlspecialchars($req['address']) ?> - <?= formatGov($req['governorate']) ?>
                        </div>
                    </div>

                    <form action="edit_delivery_request.php?id=<?= $req['request_id'] ?>" method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Update Request Status</label>
                                <select name="status" class="form-select form-select-lg" required>
                                    <option value="pending" <?= $req['status'] == 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                                    <option value="processing" <?= $req['status'] == 'processing' ? 'selected' : '' ?>>🔄 Processing</option>
                                    <option value="delivered" <?= $req['status'] == 'delivered' ? 'selected' : '' ?>>✅ Delivered</option>
                                    <option value="cancelled" <?= $req['status'] == 'cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
                                </select>
                            </div>
                        </div>
                        
                        <hr class="my-4">

                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-save"></i> Save Changes</button>
                        <a href="delivery_requests.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>