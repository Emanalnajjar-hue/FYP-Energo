<?php
require_once 'auth_check.php';
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();

 $stmt = $pdo->prepare("SELECT * FROM equipment ORDER BY equipment_id DESC");
 $stmt->execute();
 $equipments = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getCategoryBadge($category) {
    $icon = 'fa-box';
    $color = 'bg-secondary';

    switch (strtolower($category)) {
        case 'generator':
            $icon = 'fa-bolt';
            $color = 'bg-danger'; 
            break;
        case 'solar':
            $icon = 'fa-sun';
            $color = 'bg-warning'; 
            break;
        case 'kit':
            $icon = 'fa-toolbox';
            $color = 'bg-primary'; 
            break;
        case 'cable':
            $icon = 'fa-plug';
            $color = 'bg-info'; 
            break;
        case 'lighting':
            $icon = 'fa-lightbulb';
            $color = 'bg-success'; 
            break;
        case 'battery': 
            $icon = 'fa-car-battery';
            $color = 'bg-secondary'; 
            break;
    }
    return "<span class='badge $color text-uppercase'><i class='fas $icon'></i> $category</span>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energo - Manage Equipment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { height: 100vh; background-color: #343a40; padding-top: 20px; position: fixed; left: 0; width: 16.666667%; z-index: 1000; overflow-y: auto;}
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background-color: #0d6efd; }
        .content { margin-left: 16.666667%; padding: 30px; }
        .table img { object-fit: cover; border-radius: 5px; }

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
            .d-flex.justify-content-between.align-items-center.mb-4 {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }
            .d-flex.justify-content-between.align-items-center.mb-4 .btn {
                width: 100%;
            }
            h2 {
                font-size: 1.4rem;
            }
            .table thead {
                font-size: 0.85rem;
            }
            .table td, .table th {
                font-size: 0.85rem;
                white-space: nowrap;
            }
            .table img {
                width: 45px !important;
                height: 45px !important;
            }
        }

        @media (max-width: 575.98px) {
            .card-body {
                padding: 12px;
            }
            .btn-sm {
                padding: 5px 8px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 600px) {
            .content {
                padding: 12px;
            }
            h2 {
                font-size: 1.25rem;
            }
            .btn-primary {
                font-size: 0.85rem;
                padding: 8px 12px;
            }
            .table td, .table th {
                font-size: 0.8rem;
                padding: 8px 6px;
            }
            .badge {
                font-size: 0.7rem;
                padding: 5px 7px;
            }
            .table img {
                width: 40px !important;
                height: 40px !important;
            }
            td .btn-sm {
                padding: 4px 7px;
                font-size: 0.75rem;
                margin-bottom: 3px;
            }
        }

        /* ===== Extra tuning for very small phones (~400px) ===== */
        @media (max-width: 400px) {
            .content {
                padding: 8px;
            }
            h4 {
                font-size: 1.1rem;
            }
            h2 {
                font-size: 1.1rem;
            }
            .card-body {
                padding: 8px;
            }
            .table td, .table th {
                font-size: 0.72rem;
                padding: 6px 4px;
            }
            .table img {
                width: 32px !important;
                height: 32px !important;
            }
            .badge {
                font-size: 0.62rem;
                padding: 4px 6px;
            }
            .badge i {
                margin-right: 2px !important;
            }
            td a.btn-sm {
                display: block;
                width: 100%;
                margin: 2px 0;
            }
            .btn-primary {
                font-size: 0.78rem;
                padding: 7px 10px;
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
            <a href="equipment.php" class="active"><i class="fa-solid fa-car-battery"></i> Equipment</a>
            <a href="bookings.php"><i class="fa-solid fa-calendar-check"></i> Bookings</a>
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
                <h2>Manage Equipment</h2>
                <a href="add_equipment.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add New Equipment</a>
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
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price / Day</th>
                                    <th>Status</th>
                                    <th>Featured</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($equipments)): ?>
                                    <?php foreach ($equipments as $item): ?>
                                        <tr>
                                            <td><?= $item['equipment_id'] ?></td>
                                            <td>
                                                <img src="../images/<?= htmlspecialchars($item['image_url']) ?>" width="60" height="60" alt="img">
                                            </td>
                                            <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                                            <td><?= getCategoryBadge($item['category']) ?></td>
                                            <td>$<?= htmlspecialchars($item['price_per_day']) ?></td>
                                            
                                            <td>
                                                <?php 
                                                    $status = $item['status'];
                                                    if ($status == 'available') {
                                                        echo '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Available</span>';
                                                    } elseif ($status == 'booked') {
                                                        echo '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Booked</span>';
                                                    } elseif ($status == 'under_maintenance') {
                                                        echo '<span class="badge bg-danger"><i class="fas fa-wrench me-1"></i>Under Maintenance</span>';
                                                    } else {
                                                        echo '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>';
                                                    }
                                                ?>
                                            </td>

                                            <td class="text-center">
                                                <?php if ($item['is_featured'] == 1): ?>
                                                    <i class="fa-solid fa-star text-warning fs-5"></i>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit_equipment.php?id=<?= $item['equipment_id'] ?>" class="btn btn-sm btn-warning" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                                <a href="delete_equipment.php?id=<?= $item['equipment_id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this equipment?');"><i class="fa-solid fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">No equipment found.</td>
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