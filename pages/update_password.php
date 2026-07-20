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
        
        $currentPassword = trim($_POST['current_password'] ?? '');
        $newPassword     = trim($_POST['new_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        $hasError = false;

        if (empty($currentPassword) || !password_verify($currentPassword, $user['password'])) {
            $toastMessage = 'Current password is incorrect.';
            $toastType = 'error';
            $hasError = true;
        }

        if (!$hasError && strlen($newPassword) < 6) {
            $toastMessage = 'New password must be at least 6 characters long.';
            $toastType = 'error';
            $hasError = true;
        }

        if (!$hasError && $newPassword !== $confirmPassword) {
            $toastMessage = 'New password and confirm password do not match.';
            $toastType = 'error';
            $hasError = true;
        }

        if (!$hasError) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->execute([$hashedPassword, $userId]);

            $toastMessage = 'Password updated successfully!';
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
    <title>Update Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/update_password-style.css">
</head>

<body>

    <div class="profile-container">
        <div class="sidebar">
            <h2>Update Password</h2>
            <div class="avatar-box">
                <img src="<?= htmlspecialchars($avatarSrc) ?>" alt="Profile" id="user-avatar">
            </div>
            <h3 id="display-name"><?= htmlspecialchars($user['name'] ?? 'User Name') ?></h3>
            <p id="display-email"><?= htmlspecialchars($user['email'] ?? 'example@email.com') ?></p>
            <div class="user-info-list">
                <p><i class="fa-solid fa-phone"></i> <span id="display-phone"><?= htmlspecialchars($user['phone'] ?? 'Not provided') ?></span></p>
                <p><i class="fa-solid fa-location-dot"></i> <span id="display-address"><?= htmlspecialchars($fullAddress ?: 'Not provided') ?></span></p>
                <p><i class="fa-solid fa-shield-halved"></i> Membership: <strong id="display-membership"><?= $membership ?></strong></p>
            </div>
            <div class="rentals-box">
                <i class="fa-solid fa-box-archive" style="font-size: 24px; color: #10482D;"></i>
                <div>
                    <p>Total Rentals</p><strong id="rental-count-display"><?= $rentalsCount ?> Rentals</strong>
                </div>
            </div>
        </div>

        <div class="main-form">
            <h2>Please enter your new password below</h2>
            <p>Confirm your new password to continue.</p>

            <form id="password-form" method="POST">
                <input type="password" name="current_password" placeholder="Current Password" required>
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>

                <button type="submit" class="save-btn">Update Password</button>
            </form>
        </div>
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


</body>
</html>