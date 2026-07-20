<?php
require_once 'auth_check.php';
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
 $csrf_token = $_SESSION['csrf_token'];

 $stmt_users = $pdo->query("SELECT user_id, first_name, last_name FROM users");
 $users_list = [];
while ($row = $stmt_users->fetch(PDO::FETCH_ASSOC)) {
    $users_list[$row['user_id']] = $row['first_name'] . ' ' . $row['last_name'];
}

 $stmt_equip = $pdo->query("SELECT equipment_id, name, image_url FROM equipment");
 $equip_list = [];
while ($row = $stmt_equip->fetch(PDO::FETCH_ASSOC)) {
    $equip_list[$row['equipment_id']] = $row;
}

 $stmt_bookings = $pdo->query("SELECT * FROM bookings ORDER BY booking_id DESC");
 $bookings = $stmt_bookings->fetchAll(PDO::FETCH_ASSOC);

function getBookingStatusBadge($status) {
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-warning text-dark">Pending</span>';
        case 'confirmed':
            return '<span class="badge bg-info text-dark">Confirmed</span>';
        case 'active':
            return '<span class="badge bg-primary">Active (Rented)</span>';
        case 'completed':
            return '<span class="badge bg-success">Completed</span>';
        case 'cancelled':
            return '<span class="badge bg-danger">Cancelled</span>';
        default:
            return "<span class='badge bg-secondary'>" . ucfirst($status) . "</span>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energo - Manage Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { height: 100vh; background-color: #343a40; padding-top: 20px; position: fixed; left: 0; width: 16.666667%; z-index: 1000; overflow-y: auto;}
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background-color: #0d6efd; }
        .content { margin-left: 16.666667%; padding: 30px; }
        .table img { object-fit: cover; border-radius: 5px; }
        .user-info { font-size: 0.9em; color: #666; }
        .date-range { font-size: 0.85em; background: #f8f9fa; padding: 4px 8px; border-radius: 4px; border: 1px solid #dee2e6; }
        .status-dropdown {
            padding: 4px 8px;
            font-size: 13px;
            width: auto;
            min-width: 150px;
        }
        .actions-cell {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            align-items: center;
        }
        .actions-cell .btn {
            padding: 5px 8px;
            font-size: 0.82rem;
        }
        .actions-cell form {
            display: inline-block;
            margin: 0;
        }
        .btn-qr {
            color: #6f42c1;
            border-color: #6f42c1;
        }
        .btn-qr:hover {
            color: #fff;
            background-color: #6f42c1;
            border-color: #6f42c1;
        }

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
                width: 240px;
                left: -240px;
                transition: left 0.3s ease;
            }
            .sidebar.show {
                left: 0;
            }
            .content {
                margin-left: 0;
                padding: 20px;
            }
            .sidebar-toggle-btn {
                display: inline-block;
            }
            .sidebar-overlay.show {
                display: block;
            }
        }

        @media (max-width: 767.98px) {
            .content {
                padding: 15px;
            }
            h2 {
                font-size: 1.4rem;
            }
            .card-body {
                padding: 12px;
            }
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start !important;
            }
            .d-flex.justify-content-between .btn {
                width: 100%;
                text-align: center;
            }
            .table {
                font-size: 0.85rem;
            }
            .table thead th {
                font-size: 0.8rem;
                padding: 8px 6px;
                white-space: nowrap;
            }
            .table tbody td {
                padding: 8px 6px;
                white-space: nowrap;
            }
            .table img {
                width: 36px;
                height: 36px;
            }
            .status-dropdown {
                min-width: 120px;
                font-size: 12px;
                padding: 3px 6px;
            }
            .actions-cell {
                gap: 3px;
            }
            .actions-cell .btn {
                padding: 4px 6px;
                font-size: 0.75rem;
            }
            .date-range {
                font-size: 0.78rem;
                padding: 3px 5px;
            }
            .user-info {
                font-size: 0.78rem;
            }
        }

        @media (max-width: 600px) {
            .content {
                padding: 10px;
            }
            h2 {
                font-size: 1.2rem;
            }
            .card-body {
                padding: 8px;
            }
            .table {
                font-size: 0.8rem;
            }
            .table thead th {
                font-size: 0.75rem;
                padding: 6px 4px;
            }
            .table tbody td {
                padding: 6px 4px;
            }
            .status-dropdown {
                min-width: 100px;
                font-size: 11px;
            }
            .actions-cell .btn {
                padding: 3px 5px;
                font-size: 0.7rem;
            }
            .alert {
                font-size: 0.85rem;
                padding: 10px;
            }
        }

        @media (max-width: 400px) {
            .content {
                padding: 6px;
            }
            h2 {
                font-size: 1.05rem;
            }
            .table {
                font-size: 0.75rem;
            }
            .table thead th {
                font-size: 0.7rem;
                padding: 5px 3px;
            }
            .table tbody td {
                padding: 5px 3px;
            }
            .table img {
                width: 28px;
                height: 28px;
            }
            .status-dropdown {
                min-width: 85px;
                font-size: 10px;
                padding: 2px 4px;
            }
            .actions-cell .btn {
                padding: 2px 4px;
                font-size: 0.65rem;
            }
            .date-range {
                font-size: 0.7rem;
                padding: 2px 4px;
            }
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
            <a href="bookings.php" class="active"><i class="fa-solid fa-calendar-check"></i> Bookings</a>
            <a href="verify_ticket.php"><i class="fa-solid fa-qrcode"></i> Verify Ticket</a>
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

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Manage Bookings</h2>
                <button class="btn btn-outline-primary"><i class="fa-solid fa-download"></i> Export Report</button>
            </div>

            <?php if (isset($_SESSION['msg'])): ?>
                <div class="alert alert-<?= $_SESSION['msg_type'] == 'error' ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['msg']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php 
                    unset($_SESSION['msg']); 
                    unset($_SESSION['msg_type']); 
                ?>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Product</th>
                                    <th>Dates</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($bookings)): ?>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td><?= $booking['booking_id'] ?></td>
                                            <td>
                                                <strong><?= isset($users_list[$booking['user_id']]) ? htmlspecialchars($users_list[$booking['user_id']]) : 'Unknown User' ?></strong>
                                                <div class="user-info">ID: <?= $booking['user_id'] ?></div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php 
                                                        $prod = isset($equip_list[$booking['equipment_id']]) ? $equip_list[$booking['equipment_id']] : ['name' => 'Deleted Item', 'image_url' => 'default.png'];
                                                    ?>
                                                    <img src="../images/<?= htmlspecialchars($prod['image_url']) ?>" width="50" height="50" alt="img" class="me-2">
                                                    <div><?= htmlspecialchars($prod['name']) ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="date-range">
                                                    <strong>From:</strong> <?= date('M d, Y', strtotime($booking['pickup_date'])) ?><br>
                                                    <strong>To:</strong> <?= date('M d, Y', strtotime($booking['return_date'])) ?>
                                                </div>
                                            </td>
                                            <td>$<?= number_format($booking['total_amount'], 2) ?></td>
                                            
                                            <td>
                                                <form action="update_booking_status.php" method="POST" style="display: flex; gap: 5px;">
                                                    <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                                    
                                                    <select name="status" class="form-select status-dropdown" onchange="this.form.submit()">
                                                        <option value="pending" <?= $booking['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                        <option value="confirmed" <?= $booking['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                        <option value="active" <?= $booking['status'] == 'active' ? 'selected' : '' ?>>Active (Rented)</option>
                                                        <option value="completed" <?= $booking['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                                        <option value="cancelled" <?= $booking['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                    </select>
                                                </form>
                                            </td>

                                            <td>
                                                <div class="actions-cell">
                                                    <a href="admin_view_booking_qr.php?booking_id=<?= $booking['booking_id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary btn-qr" title="View / Print QR Code">
                                                        <i class="fas fa-qrcode"></i>
                                                    </a>
                                                    
                                                    <a href="view_booking.php?id=<?= $booking['booking_id'] ?>" class="btn btn-sm btn-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if(!in_array($booking['status'], ['active', 'completed', 'cancelled'])): ?>
                                                        <form action="cancel_booking_admin.php" method="POST" onsubmit="return confirm('Cancel this booking?');">
                                                            <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                                            <button type="submit" class="btn btn-sm btn-warning" title="Cancel Booking">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <form action="delete_booking.php" method="POST" onsubmit="return confirm('Delete this record permanently?');">
                                                        <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Record">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">No bookings found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
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