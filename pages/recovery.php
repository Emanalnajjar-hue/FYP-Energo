<?php
session_start();
$msg       = '';
$msg_type  = '';
$old_email = ''; 
$old_phone = '';

if (isset($_SESSION['msg'])) {
    $msg      = $_SESSION['msg'];
    $msg_type = $_SESSION['msg_type'] ?? 'error';
    unset($_SESSION['msg'], $_SESSION['msg_type']);
}
if (isset($_SESSION['old_email'])) {
    $old_email = $_SESSION['old_email']; 
    $old_phone = $_SESSION['old_phone'] ?? '';
    unset($_SESSION['old_email'], $_SESSION['old_phone']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energo - Password Recovery</title>
    <link rel="stylesheet" href="../style/index-style.css">
    <link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>

<div class="main-card">
    <img src="../images/plant.png" alt="Plant" class="plant-overlay">

    <div class="color-side">
        <div class="side-header">
            <span class="icon">🔒</span>
            <h2>Log in to access reliable power solutions</h2>
        </div>
    </div>

    <div class="white-side">
        <div class="form-section">
            <div class="content-area">

                <?php if ($msg): ?>
                    <input type="hidden" id="msg-data"
                           value="<?= htmlspecialchars($msg) ?>"
                           data-type="<?= $msg_type ?>">
                <?php endif; ?>
                <div id="alert-box" style="display:none;"></div>

                <h3>Password Recovery 🔑</h3>
                <p class="subtitle">Please enter your info to continue password recovery</p>
                <form action="auth.php" method="POST">
                    <input type="hidden" name="action" value="recovery">
                    <div class="input-field">
                        <label style="color: #1a4d3a; font-weight: 600;">Email</label>
                        <input type="text" name="email"
                               placeholder="Enter your email"
                               value="<?= htmlspecialchars($old_email) ?>">
                    </div>
                    <div class="input-field">
                        <label style="color: #1a4d3a; font-weight: 600;">Phone Number</label>
                        <input type="text" name="phone"
                               placeholder="059XXXXXXX"
                               maxlength="10"
                               value="<?= htmlspecialchars($old_phone) ?>">
                    </div>
                    <div class="input-field">
    <label style="color: #1a4d3a; font-weight: 600;">New Password</label>

    <div class="password-container">
        <input type="password"
               id="new_password"
               name="new_password"
               placeholder="••••••••">

        <span class="toggle-password" onclick="togglePassword(this)">
            <i class="fa-solid fa-eye"></i>
        </span>
    </div>
</div>
<div class="input-field">
    <label style="color: #1a4d3a; font-weight: 600;">Confirm Password</label>

    <div class="password-container">
        <input type="password"
               id="confirm_password"
               name="confirm_password"
               placeholder="••••••••">

        <span class="toggle-password" onclick="togglePassword(this)">
            <i class="fa-solid fa-eye"></i>
        </span>
    </div>
</div>
                    <button type="submit" class="btn-submit">Reset My Password</button>
                </form>
                <div class="links">
                    <a href="login.php" class="forgot-login">Back to Login</a>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="../script/script.js?v=<?= time(); ?>"></script>
</body>
</html>