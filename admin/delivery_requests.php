<?php
require_once 'auth_check.php';
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();

 $stmt = $pdo->query("SELECT * FROM delivery_requests ORDER BY request_id DESC");
 $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatGovernorate($gov) {
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
    <title>Energo - Delivery Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { height: 100vh; background-color: #343a40; padding-top: 20px; position: fixed; left: 0; width: 16.666667%; z-index: 1000; overflow-y: auto;}
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background-color: #0d6efd; }
        .content { margin-left: 16.666667%; padding: 30px; }
        .table-responsive { overflow-x: auto; }
        .notes-cell {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .status-dropdown {
            padding: 4px 8px;
            font-size: 13px;
            width: auto;
            min-width: 130px;
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
            
            .table {
                font-size: 0.88rem;
            }
            .table thead th {
                white-space: nowrap;
                padding: 10px 8px;
            }
            .table tbody td {
                white-space: nowrap;
                padding: 10px 8px;
            }
            .status-dropdown {
                min-width: 120px;
                font-size: 12px;
            }
            .notes-cell {
                max-width: 140px;
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
                padding: 10px;
            }
            
            .table {
                font-size: 0.82rem;
            }
            .table thead th {
                font-size: 0.78rem;
                padding: 8px 6px;
            }
            .table tbody td {
                padding: 8px 6px;
            }
            .status-dropdown {
                min-width: 105px;
                font-size: 11px;
                padding: 3px 5px;
            }
            .notes-cell {
                max-width: 100px;
                font-size: 0.78rem;
            }
            .btn-sm {
                padding: 4px 8px;
                font-size: 0.78rem;
            }
            .alert {
                font-size: 0.85rem;
                padding: 10px;
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
            <a href="bookings.php"><i class="fa-solid fa-calendar-check"></i> Bookings</a>
            <a href="verify_ticket.php"><i class="fa-solid fa-qrcode"></i> Verify Ticket</a>
            <a href="delivery_requests.php" class="active"><i class="fa-solid fa-truck-fast"></i> Delivery Requests</a>
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
                <h2><i class="fa-solid fa-truck-fast"></i> Delivery Requests</h2>
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
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Req. ID</th>
                                    <th>Related Booking</th>
                                    <th>Customer Info</th>
                                    <th>Location</th>
                                    <th>Notes</th>
                                    <th>Status</th>
                                    <th>Requested At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($requests)): ?>
                                    <?php foreach ($requests as $req): ?>
                                        <tr>
                                            <td><?= $req['request_id'] ?></td>
                                            
                                            <td>
                                                <?php if (!empty($req['booking_id'])): ?>
                                                    <a href="bookings.php" class="badge bg-primary text-decoration-none" title="View Booking">
                                                        <i class="fa-solid fa-link"></i> Booking #<?= $req['booking_id'] ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">N/A</span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <strong><?= htmlspecialchars($req['full_name']) ?></strong><br>
                                                <i class="fa-solid fa-phone text-muted fa-sm"></i> <?= htmlspecialchars($req['primary_phone']) ?>
                                                <?php if (!empty($req['alt_phone'])): ?>
                                                    <br><i class="fa-solid fa-phone text-secondary fa-sm"></i> <small class="text-muted">Alt: <?= htmlspecialchars($req['alt_phone']) ?></small>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($req['address']) ?><br>
                                                <span class="badge bg-secondary"><?= formatGovernorate($req['governorate']) ?></span>
                                            </td>

                                            <td>
                                                <div class="notes-cell" title="<?= htmlspecialchars($req['notes'] ?? 'No notes') ?>">
                                                    <?= htmlspecialchars($req['notes'] ?? 'No notes') ?>
                                                </div>
                                            </td>

                                            <td>
                                                <form action="update_delivery_status.php" method="POST" style="display: flex; gap: 5px;">
                                                    <input type="hidden" name="request_id" value="<?= $req['request_id'] ?>">
                                                    
                                                    <select name="status" class="form-select status-dropdown" onchange="this.form.submit()">
                                                        <option value="pending" <?= $req['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                        <option value="processing" <?= $req['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                                        <option value="completed" <?= $req['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                                        <option value="cancelled" <?= $req['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                    </select>
                                                </form>
                                            </td>
                                            
                                            <td><?= date('M d, Y h:i A', strtotime($req['created_at'])) ?></td>
                                            
                                            <td>
                                                <a href="delete_delivery_request.php?id=<?= $req['request_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this request?');">
                                                    <i class="fa-solid fa-trash"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">No delivery requests found.</td>
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