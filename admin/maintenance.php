<?php
require_once 'auth_check.php';
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();

 $stmt = $pdo->query("SELECT * FROM maintenance_requests ORDER BY created_at DESC");
 $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

 $final_requests = [];

foreach ($requests as $req) {
    $user_id = $req['user_id'];
    $customer_name = "Unknown User";
    
    if ($user_id) {
        $u_stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
        $u_stmt->execute([$user_id]);
        $u_data = $u_stmt->fetch(PDO::FETCH_ASSOC);
        if ($u_data) {
            $customer_name = trim($u_data['first_name'] . ' ' . $u_data['last_name']);
        }
    }

    $equipment_id = $req['equipment_id'];
    $equipment_name = "General Issue"; 
    $equip_current_status = null;

    if (!empty($equipment_id)) {
        $e_stmt = $pdo->prepare("SELECT name, status FROM equipment WHERE equipment_id = ?");
        $e_stmt->execute([$equipment_id]);
        $e_data = $e_stmt->fetch(PDO::FETCH_ASSOC);
        if ($e_data) {
            $equipment_name = $e_data['name'];
            $equip_current_status = $e_data['status'];
        }
    }

    $req['customer_name'] = $customer_name;
    $req['equipment_name'] = $equipment_name;
    $req['equip_current_status'] = $equip_current_status;
    
    $final_requests[] = $req;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energo - Manage Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { height: 100vh; background-color: #343a40; padding-top: 20px; position: fixed; left: 0; width: 16.666667%; z-index: 1000; overflow-y: auto;}
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background-color: #0d6efd; }
        .content { margin-left: 16.666667%; padding: 30px; }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }
        .status-badge::before {
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 0.7rem;
        }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-pending::before { content: '\f017'; }
        
        .badge-in-progress { background: #dbeafe; color: #1e40af; }
        .badge-in-progress::before { content: '\f013'; animation: spin 2s linear infinite; }
        
        .badge-completed { background: #dcfce7; color: #166534; }
        .badge-completed::before { content: '\f058'; }
        
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .badge-rejected::before { content: '\f00d'; }

        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .equip-status-tag {
            display: inline-block;
            font-size: 0.65rem;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-left: 6px;
        }
        .equip-status-tag.status-available { background: #dcfce7; color: #16a34a; }
        .equip-status-tag.status-booked { background: #fef9c3; color: #ca8a04; }
        .equip-status-tag.status-under_maintenance { background: #fecaca; color: #dc2626; }

        .action-btn {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-decoration: none;
            color: #fff;
        }
        .btn-approve { background: linear-gradient(135deg, #059669, #10b981); }
        .btn-approve:hover { background: linear-gradient(135deg, #047857, #059669); color: #fff; transform: translateY(-1px); box-shadow: 0 3px 8px rgba(5, 150, 105, 0.3); }
        
        .btn-reject { background: linear-gradient(135deg, #dc2626, #ef4444); }
        .btn-reject:hover { background: linear-gradient(135deg, #b91c1c, #dc2626); color: #fff; transform: translateY(-1px); box-shadow: 0 3px 8px rgba(220, 38, 38, 0.3); }
        
        .btn-complete { background: linear-gradient(135deg, #2563eb, #3b82f6); }
        .btn-complete:hover { background: linear-gradient(135deg, #1d4ed8, #2563eb); color: #fff; transform: translateY(-1px); box-shadow: 0 3px 8px rgba(37, 99, 235, 0.3); }

        .actions-cell { display: flex; flex-wrap: wrap; gap: 5px; align-items: center; }

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
            .action-btn {
                padding: 4px 8px;
                font-size: 0.72rem;
            }
            .status-badge {
                padding: 4px 10px;
                font-size: 0.72rem;
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
            .action-btn {
                padding: 4px 7px;
                font-size: 0.68rem;
                gap: 3px;
            }
            .status-badge {
                padding: 4px 8px;
                font-size: 0.68rem;
            }
            .equip-status-tag {
                display: none; /* نختفيها على الموبايل عشان توفر مساحة */
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
            <a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="equipment.php"><i class="fa-solid fa-car-battery"></i> Equipment</a>
            <a href="bookings.php"><i class="fa-solid fa-calendar-check"></i> Bookings</a>
            <a href="verify_ticket.php"><i class="fa-solid fa-qrcode"></i> Verify Ticket</a>
            <a href="delivery_requests.php"><i class="fa-solid fa-truck-fast"></i> Delivery Requests</a>
            <a href="maintenance.php" class="active"><i class="fa-solid fa-screwdriver-wrench"></i> Maintenance</a>
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
                <h2>Manage Maintenance</h2>
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
                                    <th>Customer</th>
                                    <th>Equipment</th>
                                    <th>Issue Type</th>
                                    <th>Location</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (empty($final_requests)): 
                                ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="alert alert-warning m-0" style="background-color: #fff3cd; color: #856404;">
                                                No records found in <strong>maintenance_requests</strong> table.
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                else: 
                                ?>
                                    <?php foreach ($final_requests as $req): 
                                        $status = $req['status'];
                                        if ($status == 'Pending') $badge_class = 'badge-pending';
                                        elseif ($status == 'In Progress') $badge_class = 'badge-in-progress';
                                        elseif ($status == 'Completed') $badge_class = 'badge-completed';
                                        elseif ($status == 'Rejected') $badge_class = 'badge-rejected';
                                        else $badge_class = 'badge-pending';

                                        $equip_tag = '';
                                        if (!empty($req['equip_current_status'])) {
                                            $eq_st = $req['equip_current_status'];
                                            $tag_class = 'status-' . $eq_st;
                                            $equip_tag = '<span class="equip-status-tag ' . $tag_class . '">' . str_replace('_', ' ', $eq_st) . '</span>';
                                        }
                                    ?>
                                        <tr>
                                            <td><?= $req['request_id'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($req['customer_name']) ?></strong>
                                                <div class="user-info" style="font-size: 0.85em; color: #6b7280;">ID: <?= $req['user_id'] ?></div>
                                            </td>
                                            
                                            <td>
                                                <?php if ($req['equipment_name'] == "General Issue"): ?>
                                                    <span class="badge bg-secondary"><?= $req['equipment_name'] ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-info text-dark"><?= htmlspecialchars($req['equipment_name']) ?></span>
                                                    <?= $equip_tag ?>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?= htmlspecialchars($req['issue_type']) ?>
                                                </span>
                                            </td>

                                            <td>
                                                <small class="text-muted d-block"><?= htmlspecialchars($req['location']) ?></small>
                                                <?php if (!empty($req['landmark'])): ?>
                                                    <small class="text-danger"><?= htmlspecialchars($req['landmark']) ?></small>
                                                <?php endif; ?>
                                            </td>

                                            <td class="text-muted small"><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
                                            
                                            <td>
                                                <span class="status-badge <?= $badge_class ?>"><?= htmlspecialchars($status) ?></span>
                                            </td>

                                            <td>
                                                <div class="actions-cell">
                                                    <?php if ($status == 'Pending'): ?>
                                                        <form action="update_maintenance_status.php" method="POST" style="margin:0;">
                                                            <input type="hidden" name="request_id" value="<?= $req['request_id'] ?>">
                                                            <button type="submit" name="action" value="approve" class="action-btn btn-approve" title="Approve & Start Maintenance">
                                                                <i class="fas fa-check"></i> Approve
                                                            </button>
                                                        </form>
                                                        <form action="update_maintenance_status.php" method="POST" style="margin:0;">
                                                            <input type="hidden" name="request_id" value="<?= $req['request_id'] ?>">
                                                            <button type="submit" name="action" value="reject" class="action-btn btn-reject" title="Reject Request" onclick="return confirm('Are you sure you want to reject this request?');">
                                                                <i class="fas fa-times"></i> Reject
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($status == 'In Progress'): ?>
                                                        <form action="update_maintenance_status.php" method="POST" style="margin:0;">
                                                            <input type="hidden" name="request_id" value="<?= $req['request_id'] ?>">
                                                            <button type="submit" name="action" value="complete" class="action-btn btn-complete" title="Mark as Completed">
                                                                <i class="fas fa-flag-checkered"></i> Complete
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <a href="delete_maintenance.php?id=<?= $req['request_id'] ?>" class="action-btn" style="background: #6b7280;" title="Delete Record" onclick="return confirm('Delete this record permanently?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
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