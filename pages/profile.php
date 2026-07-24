<?php

session_start();
require_once '../config/db.php';

$database = new Database();
$pdo = $database->getConnection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$toastMessage = '';
$toastType = '';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User not found");
    }

    $user['name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE user_id = ?");
    $stmt->execute([$userId]);
    $rentalsCount = (int) $stmt->fetch()['total'];

    $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
    $stmt->execute([$userId]);
    $wallet = $stmt->fetch();
    $balance = $wallet ? floatval($wallet['balance']) : 0;

    $membership = 'Silver Member';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $fullName = trim($_POST['full_name'] ?? '');
        $email   = trim($_POST['email_addr'] ?? '');
        $phone   = trim($_POST['phone_num'] ?? '');
        $dob     = trim($_POST['dob'] ?? ''); 
        $address = trim($_POST['address'] ?? ''); 
        $city    = trim($_POST['city'] ?? ''); 

        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName  = $nameParts[1] ?? '';

        $dob     = ($dob === '') ? null : $dob;
        $address = ($address === '') ? null : $address;
        $city    = ($city === '') ? null : $city;

        $hasError = false;

        if (!empty($email)) {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                $toastMessage = 'This email is already used by another account.';
                $toastType = 'error';
                $hasError = true;
            }
        }

        if (!$hasError && !empty($phone)) {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE phone = ? AND user_id != ?");
            $stmt->execute([$phone, $userId]);
            if ($stmt->fetch()) {
                $toastMessage = 'This phone number is already used by another account.';
                $toastType = 'error';
                $hasError = true;
            }
        }

        if (!$hasError) {

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

                    if (!empty($user['avatar']) && file_exists($user['avatar'])) {
                        unlink($user['avatar']);
                    }

                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $newName = 'user_' . $userId . '_' . time() . '.' . $ext;
                    $destination = $uploadDir . $newName;

                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE user_id = ?");
                        $stmt->execute([$destination, $userId]);
                        $user['avatar'] = $destination;
                    }
                }
            }

            $stmt = $pdo->prepare("
                UPDATE users SET 
                    first_name = ?, 
                    last_name = ?, 
                    email = ?, 
                    phone = ?, 
                    dob = ?, 
                    address = ?, 
                    city = ?
                WHERE user_id = ?
            ");
            
            $stmt->execute([$firstName, $lastName, $email, $phone, $dob, $address, $city, $userId]);

            $user['name']    = $fullName;
            $user['email']   = $email;
            $user['phone']   = $phone;
            $user['dob']     = $dob;
            $user['address'] = $address;
            $user['city']    = $city;

            $toastMessage = 'Profile updated successfully!';
            $toastType = 'success';
        }
    }

} catch (PDOException $e) {
    $toastMessage = 'DB Error: ' . $e->getMessage();
    $toastType = 'error';
}

$avatarSrc = !empty($user['avatar']) ? $user['avatar'] : '../images/user.jpg';
$fullAddress = trim(($user['address'] ?? '') . ', ' . ($user['city'] ?? ''), ', ');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/profile-style.css">
</head>
<body>

    <div class="profile-container">
        <aside class="sidebar">
            <h2>Profile Settings</h2>
            <p>Manage your personal information and account preferences.</p>

            <div class="avatar-box">
                <img src="<?= htmlspecialchars($avatarSrc) ?>" alt="Profile" id="profile-img">
                <button type="button" class="edit-avatar" onclick="document.getElementById('file-input').click()">
                    <i class="fa-solid fa-camera"></i>
                </button>
            </div>

            <h3 id="display-name"><?= htmlspecialchars($user['name'] ?? 'User Name') ?></h3>
            <p id="display-email"><?= htmlspecialchars($user['email'] ?? 'example@email.com') ?></p>

            <div class="status-badge">
                <i class="fa-solid fa-check-circle"></i> Verified User
            </div>

            <div class="user-info-list">
                <p>
                    <i class="fa-solid fa-phone"></i>
                    <span id="display-phone"><?= htmlspecialchars($user['phone'] ?? 'Not provided') ?></span>
                </p>
                <p>
                    <i class="fa-solid fa-location-dot"></i>
                    <span id="display-address"><?= htmlspecialchars($fullAddress ?: 'Not provided') ?></span>
                </p>
                <p>
                    <i class="fa-solid fa-shield-halved"></i>
                    Membership Level: <strong><?= $membership ?></strong>
                </p>
            </div>

            <div class="rentals-box">
                <i class="fa-solid fa-box-archive"></i>
                <div>
                    <p>Total Rentals</p>
                    <strong id="rental-count-display"><?= $rentalsCount ?> Rentals</strong>
                </div>
            </div>
        </aside>

        <main class="main-form">
            <h2>Personal Information</h2>
            
            <form id="profile-form" method="POST" enctype="multipart/form-data">
                
                <input type="file" name="avatar" id="file-input" accept="image/*" onchange="previewImage(this)" style="display: none;">

                <div class="form-row">
                    <div class="input-field">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" placeholder="Enter your full name">
                    </div>
                    <div class="input-field">
                        <label>Email Address</label>
                        <input type="email" name="email_addr" value="<?= htmlspecialchars($user['email'] ?? '') ?>" placeholder="Enter your email">
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-field">
                        <label>Phone Number</label>
                        <input type="tel" name="phone_num" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+972 XXX XXX XXXX">
                    </div>
                    <div class="input-field">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" value="<?= htmlspecialchars($user['dob'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-field">
                        <label>Address</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Enter your address">
                    </div>
                    <div class="input-field">
                        <label>City</label>
                        <input type="text" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>" placeholder="Gaza">
                    </div>
                </div>

                <button type="submit" class="save-btn">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
                <div style="clear: both;"></div>
            </form>
            </main>
    </div>

    <?php if ($toastMessage): ?>
    
    <style>
        #inline-toast {
            position: fixed !important;
            top: 25px !important;
            bottom: auto !important;
            right: auto !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            padding: 14px 28px !important;
            border-radius: 8px !important;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2) !important;
            z-index: 999999 !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            font-size: 14px !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            color: #fff !important;
            animation: slideDown 0.4s ease forwards !important;
        }
        #inline-toast.toast-success {
            background-color: #10482D !important;
            border-left: 5px solid #2ecc71 !important;
        }
        #inline-toast.toast-error {
            background-color: #721c24 !important;
            border-left: 5px solid #f5c6cb !important;
            color: #f5c6cb !important;
        }
        @keyframes slideDown {
            from { opacity: 0; top: -50px; }
            to { opacity: 1; top: 25px; }
        }
    </style>

    <div id="inline-toast" class="<?= $toastType === 'success' ? 'toast-success' : 'toast-error' ?>">
        <?php if($toastType === 'success'): ?>
            <i class="fa-solid fa-circle-check"></i>
        <?php else: ?>
            <i class="fa-solid fa-circle-exclamation"></i>
        <?php endif; ?>
        <?= htmlspecialchars($toastMessage) ?>
    </div>
    
    <script>
        setTimeout(() => {
            let toast = document.getElementById('inline-toast');
            if(toast) {
                toast.style.transition = 'opacity 0.4s ease, top 0.4s ease';
                toast.style.opacity = '0';
                toast.style.top = '-50px';
            }
        }, 3000);
    </script>
    
    <?php endif; ?>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    document.getElementById('profile-img').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

</body>
</html>

</body>
</html>