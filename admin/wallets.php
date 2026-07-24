<?php
require_once 'auth_check.php';
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();

 $stmtWallets = $pdo->query("SELECT * FROM wallets ORDER BY balance DESC");
 $wallets = $stmtWallets->fetchAll(PDO::FETCH_ASSOC);

 $stmtUsers = $pdo->query("SELECT user_id, first_name, last_name, email FROM users");
 $usersData = [];
 while ($u = $stmtUsers->fetch(PDO::FETCH_ASSOC)) {
     $usersData[$u['user_id']] = $u;
 }

 $final_wallets = [];
 foreach ($wallets as $w) {
     $uid = $w['user_id'];
     if (isset($usersData[$uid])) {
         $w['first_name'] = $usersData[$uid]['first_name'];
         $w['last_name'] = $usersData[$uid]['last_name'];
         $w['email'] = $usersData[$uid]['email'];
     } else {
         $w['first_name'] = 'Unknown';
         $w['last_name'] = '';
         $w['email'] = 'N/A';
     }
     $final_wallets[] = $w;
 }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energo - Wallets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; text-decoration: none; padding: 15px; display: block; }
        .sidebar a:hover { background-color: #495057; }
        .content { padding: 20px; }

        /* === نظام الـ Sidebar المتجاوب === */
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

        /* تابلت */
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -250px;
                width: 250px;
                z-index: 1000;
                transition: left 0.3s ease;
                overflow-y: auto;
            }
            .sidebar.show { left: 0; }
            .content { padding: 20px; }
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
        }

        /* موبايل */
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
            .badge {
                font-size: 0.72rem;
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
            <h4 class="text-white text-center mb-4"><i class="fa-solid fa-bolt"></i> Energo Admin</h4>
            <a href="home.php"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="equipment.php" ><i class="fa-solid fa-car-battery"></i> Equipment</a>
            <a href="bookings.php"><i class="fa-solid fa-calendar-check"></i> Bookings</a>
            <a href="verify_ticket.php"><i class="fa-solid fa-qrcode"></i> Verify Ticket</a>
            <a href="delivery_requests.php" ><i class="fa-solid fa-truck-fast"></i> Delivery Requests</a>
            <a href="maintenance.php"><i class="fa-solid fa-screwdriver-wrench"></i> Maintenance</a>
            <a href="users.php"><i class="fa-solid fa-users"></i> Users</a>
            <a href="testimonials.php"><i class="fa-solid fa-star"></i> Testimonials</a>
            <a href="wallets.php" class="bg-secondary"><i class="fa-solid fa-wallet"></i> Wallets</a>
            <a href="../pages/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>

        <div class="col-md-10 content">
            <button class="sidebar-toggle-btn" id="sidebarToggleBtn">
                <i class="fa-solid fa-bars"></i>
            </button>

            <h2 class="mb-4"><i class="fa-solid fa-wallet"></i> User Wallets</h2>

            <?php if (isset($_SESSION['msg'])): ?>
                <div class="alert alert-<?= $_SESSION['msg_type'] == 'error' ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['msg']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Wallet ID</th>
                                    <th>User Name</th>
                                    <th>Email</th>
                                    <th>Current Balance ($)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($final_wallets)): ?>
                                    <?php foreach ($final_wallets as $wallet): ?>
                                        <tr>
                                            <td><?= $wallet['wallet_id'] ?></td>
                                            <td><?= htmlspecialchars($wallet['first_name'] . ' ' . $wallet['last_name']) ?></td>
                                            <td><?= htmlspecialchars($wallet['email']) ?></td>
                                            <td><strong class="text-success">$<?= number_format($wallet['balance'], 2) ?></strong></td>
                                            <td>
                                                <span class="badge bg-secondary">View Only</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No wallets found.</td>
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