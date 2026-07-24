<?php
require_once 'auth_check.php';
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();
 $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

 $msg = '';
 $msg_type = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['msg'] = 'Invalid user ID.';
    $_SESSION['msg_type'] = 'error';
    header('Location: users.php');
    exit;
}

 $id = (int)$_GET['id'];
 $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, email, phone, user_role, avatar, city, address, dob FROM users WHERE user_id = ?");
 $stmt->execute([$id]);
 $user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['msg'] = 'User not found.';
    $_SESSION['msg_type'] = 'error';
    header('Location: users.php');
    exit;
}

 $avatarSrc = !empty($user['avatar']) ? $user['avatar'] : '../images/user.jpg';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name']  ?? '');
    $email      = trim($_POST['email']      ?? '');
    $phone      = trim($_POST['phone']      ?? '');
    $user_role  = (int)($_POST['user_role'] ?? 0);
    
    $city    = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $dob     = trim($_POST['dob'] ?? '');

    $city    = ($city === '') ? null : $city;
    $address = ($address === '') ? null : $address;
    $dob     = ($dob === '') ? null : $dob;

    if ($id == $_SESSION['user_id'] && $user_role != 1) {
        $msg = 'You cannot remove your own Admin role!';
        $msg_type = 'error';
        $user_role = 1; 
    }

    if (empty($msg)) {
        try {
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['avatar'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (in_array($mimeType, $allowedTypes) && $file['size'] <= 5 * 1024 * 1024) {
                    $uploadDir = '../images/users/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    if (!empty($user['avatar']) && file_exists($user['avatar']) && $user['avatar'] !== '../images/user.jpg') {
                        unlink($user['avatar']);
                    }

                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $newName = 'user_' . $id . '_' . time() . '.' . $ext;
                    $destination = $uploadDir . $newName;

                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        $avatarSrc = $destination; 
                    }
                }
            }

            $stmt = $pdo->prepare("
                UPDATE users SET 
                    first_name = ?, last_name = ?, email = ?, phone = ?, user_role = ?, 
                    city = ?, address = ?, dob = ?, avatar = ? 
                WHERE user_id = ?
            ");
            $stmt->execute([$first_name, $last_name, $email, $phone, $user_role, $city, $address, $dob, $avatarSrc, $id]);

            $_SESSION['msg'] = 'User updated successfully!';
            $_SESSION['msg_type'] = 'success';
            header('Location: users.php');
            exit;
        } catch (Exception $e) {
            $msg = 'Database error: ' . $e->getMessage();
            $msg_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energo - Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; text-decoration: none; padding: 15px; display: block; }
        .sidebar a:hover { background-color: #495057; }
        .content { padding: 20px; }
        .current-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #dee2e6;
            margin-bottom: 10px;
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
        }

        @media (max-width: 767.98px) {
            .content {
                padding: 15px;
            }
            h2 {
                font-size: 1.4rem;
            }
            .card-body {
                padding: 16px;
            }
            .row.mb-3 > [class^="col-"] {
                margin-bottom: 12px;
            }
            .w-50.mx-auto {
                width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
            .btn-success, .btn-secondary {
                width: 100%;
                margin-top: 8px;
            }
            .current-avatar {
                width: 70px;
                height: 70px;
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

            <h2 class="mb-4"><i class="fa-solid fa-user-pen"></i> Edit User: <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h2>

            <?php if ($msg): ?>
                <div class="alert alert-<?= $msg_type == 'error' ? 'danger' : 'success' ?>">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-body">
                    <form action="edit_user.php?id=<?= $user['user_id'] ?>" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-4 text-center">
                            <label class="form-label fw-bold">Profile Picture</label><br>
                            <img src="<?= htmlspecialchars($avatarSrc) ?>" alt="Avatar" class="current-avatar" id="avatar-preview">
                            <br>
                            <input type="file" name="avatar" class="form-control w-50 mx-auto mt-2" accept="image/*" onchange="document.getElementById('avatar-preview').src = window.URL.createObjectURL(this.files[0])">
                            <small class="text-muted">Leave empty to keep the current image.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <small class="text-muted">(Cannot be changed)</small></label>
                                <input type="email" name="email" class="form-control bg-light" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($user['city'] ?? '') ?>" placeholder="e.g. Gaza">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($user['dob'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="e.g. Al-Rimal Street">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">User Role</label>
                            <select name="user_role" class="form-select" required>
                                <?php if ($user['user_id'] == $_SESSION['user_id']): ?>
                                    <option value="1" selected>Admin (You cannot change your own role)</option>
                                <?php else: ?>
                                    <option value="0" <?= $user['user_role'] == 0 ? 'selected' : '' ?>>Customer</option>
                                    <option value="1" <?= $user['user_role'] == 1 ? 'selected' : '' ?>>Admin</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-save"></i> Update User</button>
                        <a href="users.php" class="btn btn-secondary">Cancel</a>
                    </form>
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