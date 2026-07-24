<?php
require_once 'auth_check.php';
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();

 $msg = '';
 $msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price_per_day = trim($_POST['price_per_day'] ?? '');
    $weight_kg = trim($_POST['weight_kg'] ?? '');
    $voltage = trim($_POST['voltage'] ?? '');
    $status = trim($_POST['status'] ?? 'available');
    $location = trim($_POST['location'] ?? 'north');
    
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    $image_url = 'default-equipment.jpg';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_ext, $allowed_exts)) {
            $new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
            $upload_path = '../images/' . $new_file_name;
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $image_url = $new_file_name;
            } else {
                $msg = 'Failed to upload image.';
                $msg_type = 'error';
            }
        } else {
            $msg = 'Invalid image format.';
            $msg_type = 'error';
        }
    }

    if (empty($msg)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO equipment (name, category, description, price_per_day, weight_kg, voltage, image_url, status, location, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $name, 
                $category, 
                $description, 
                $price_per_day, 
                $weight_kg, 
                $voltage, 
                $image_url, 
                $status, 
                $location, 
                $is_featured 
            ]);

            $_SESSION['msg'] = 'Equipment added successfully!';
            $_SESSION['msg_type'] = 'success';
            header('Location: equipment.php');
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
    <title>Energo - Add Equipment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { height: 100vh; background-color: #343a40; padding-top: 20px; position: fixed; left: 0; width: 16.666667%; z-index: 1000; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background-color: #0d6efd; }
        .content { margin-left: 16.666667%; padding: 30px; }

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
                overflow-y: auto;
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
                padding: 18px;
            }
            .row.mb-3 > [class^="col-"],
            .row > [class^="col-"] {
                margin-bottom: 12px;
            }
            .btn-primary, .btn-secondary {
                width: 100%;
                margin-top: 8px;
            }
        }

        @media (max-width: 600px) {
            .content {
                padding: 12px;
            }
            h2 {
                font-size: 1.25rem;
            }
            .card-body {
                padding: 14px;
            }
            .form-label {
                font-size: 0.9rem;
            }
            .form-control, .form-select {
                font-size: 0.9rem;
            }
        }

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
                padding: 10px;
            }
            .form-label {
                font-size: 0.82rem;
            }
            .form-control, .form-select {
                font-size: 0.82rem;
                padding: 6px 8px;
            }
            .btn-primary, .btn-secondary {
                font-size: 0.85rem;
                padding: 8px 10px;
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

            <h2 class="mb-4"><i class="fa-solid fa-plus-circle"></i> Add New Equipment</h2>

            <?php if ($msg): ?>
                <div class="alert alert-<?= $msg_type == 'error' ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="add_equipment.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Equipment Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Honda Generator 3000W" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select" required>
                                    <option value="" disabled selected>Select Category</option>
                                    <option value="generator">Generator</option>
                                    <option value="solar">Solar</option>
                                    <option value="kit">Kit</option>
                                    <option value="cable">Cable</option>
                                    <option value="lightimg">Lighting</option>
                                    <option value="battery">Battery</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price per Day ($)</label>
                                <input type="number" step="0.01" name="price_per_day" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Weight (kg)</label>
                                <input type="text" name="weight_kg" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Voltage</label>
                                <input type="text" name="voltage" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                             <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="available">Available</option>
                                    <option value="booked">Booked</option>
                                    <option value="maintenance">Under_Maintenance</option>
                                </select>
                            </div>
                             <div class="col-md-4">
                                <label class="form-label">Location</label>
                                <select name="location" class="form-select" required>
                                    <option value="north">North Gaza / Gaza City</option>
                                    <option value="middle">Middle Area</option>
                                    <option value="south">South Gaza</option>
                                </select>
                            </div>
                             <div class="col-md-4">
                                <label class="form-label">Featured Product?</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_featured" value="1">
                                    <label class="form-check-label">Show on Home Page</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Equipment Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>

                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Equipment</button>
                        <a href="equipment.php" class="btn btn-secondary">Cancel</a>
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