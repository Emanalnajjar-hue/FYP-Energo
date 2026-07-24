<?php
require_once 'auth_check.php';
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();

 $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, email, phone, user_role, created_at, avatar, city, address, dob FROM users ORDER BY user_id DESC");
 $stmt->execute();
 $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energo - Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; text-decoration: none; padding: 15px; display: block; }
        .sidebar a:hover { background-color: #495057; }
        .content { padding: 20px; }
        .user-avatar-sm {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #dee2e6;
            margin-right: 10px;
        }
        .user-info-cell {
            display: flex;
            align-items: center;
        }
        .table-responsive {
            overflow-x: auto;
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
            .btn-sm {
                padding: 4px 8px;
                font-size: 0.78rem;
            }
            .user-avatar-sm {
                width: 30px;
                height: 30px;
                margin-right: 8px;
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
            .btn-sm {
                padding: 4px 7px;
                font-size: 0.72rem;
            }
            .user-avatar-sm {
                width: 28px;
                height: 28px;
                margin-right: 6px;
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
            <a href="users.php" class="bg-secondary"><i class="fa-solid fa-users"></i> Users</a>
            <a href="testimonials.php"><i class="fa-solid fa-star"></i> Testimonials</a>
            <a href="wallets.php"><i class="fa-solid fa-wallet"></i> Wallets</a>
            <a href="../pages/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>

        <div class="col-md-10 content">
            <button class="sidebar-toggle-btn" id="sidebarToggleBtn">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fa-solid fa-users"></i> Manage Users</h2>
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

            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>User (Avatar & Name)</th>
                                    <th>Contact (Email & Phone)</th>
                                    <th>Location</th>
                                    <th>Date of Birth</th>
                                    <th>Role</th>
                                    <th>Registered At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $user): 
                                        $avatarPath = !empty($user['avatar']) ? $user['avatar'] : '../images/user.jpg';
                                        $location = trim(($user['address'] ?? '') . ', ' . ($user['city'] ?? ''), ', ');
                                    ?>
                                        <tr>
                                            <td><?= $user['user_id'] ?></td>
                                            
                                            <td>
                                                <div class="user-info-cell">
                                                    <img src="<?= htmlspecialchars($avatarPath) ?>" alt="Avatar" class="user-avatar-sm">
                                                    <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                                                </div>
                                            </td>

                                            <td>
                                                <i class="fa-solid fa-envelope text-muted"></i> <?= htmlspecialchars($user['email']) ?><br>
                                                <i class="fa-solid fa-phone text-muted"></i> <?= htmlspecialchars($user['phone'] ?? 'N/A') ?>
                                            </td>

                                            <td><?= htmlspecialchars($location ?: 'N/A') ?></td>

                                            <td>
                                                <?= !empty($user['dob']) ? date('M d, Y', strtotime($user['dob'])) : 'N/A' ?>
                                            </td>

                                            <td>
                                                <?php 
                                                    if ($user['user_role'] == 1) {
                                                        echo '<span class="badge bg-danger">Admin</span>';
                                                    } else {
                                                        echo '<span class="badge bg-primary">Customer</span>';
                                                    }
                                                ?>
                                            </td>
                                            
                                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                            
                                            <td>
                                                <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                                
                                                <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                                    <a href="delete_user.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure? Deleting a user will also delete their wallet and bookings!');"><i class="fa-solid fa-trash"></i> Delete</a>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-secondary" disabled title="You cannot delete yourself"><i class="fa-solid fa-trash"></i> Delete</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No users found.</td>
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