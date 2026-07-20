<?php
session_start();
 $msg      = '';
 $msg_type = '';
 $old_email = '';

if (isset($_SESSION['msg'])) {
    $msg      = $_SESSION['msg'];
    $msg_type = $_SESSION['msg_type'] ?? 'error';
    unset($_SESSION['msg'], $_SESSION['msg_type']);
}
if (isset($_SESSION['old_email'])) {
    $old_email = $_SESSION['old_email'];
    unset($_SESSION['old_email']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energo - Login</title>
    <link rel="stylesheet" href="../style/index-style.css">
    <link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>

<div class="main-card">
    <img src="/SE_ENERGO/image/plant.png" alt="Plant" class="plant-overlay">

    <div class="color-side">
        <div class="side-header">
            <span class="icon">🔒</span>
            <h2>Log in to access reliable power solutions</h2>
        </div>
    </div>

    <div class="white-side">
        <div class="form-section">
            <div class="content-area">

                <input type="hidden" id="msg-data" value="<?= htmlspecialchars($msg) ?>" data-type="<?= $msg_type ?>">
                <div id="alert-box" style="display:none;"></div>

                <h3>Hello dear, welcome! Please sign in to continue ✍️</h3>
                <form action="auth.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="input-field">
                        <label for="login-email" style="color: #1a4d3a; font-weight: 600;">Email</label>
                        <input type="text" id="login-email" name="email"
                               autocomplete="email"
                               placeholder="Enter your email"
                               value="<?= htmlspecialchars($old_email) ?>">
                    </div>
                    <div class="input-field">
    <label for="login-password" style="color: #1a4d3a; font-weight: 600;">Password</label>

    <div class="password-container">
        <input type="password"
               id="login-password"
               name="password"
               autocomplete="current-password"
               placeholder="••••••••">

        <span class="toggle-password" onclick="togglePassword()">
            <i class="fa-solid fa-eye"></i>
        </span>
    </div>
</div>
                    <button type="submit" class="btn-submit">Sign in now</button>
                </form>
                <div class="links">
                    <p>Don't have an account? <a href="signup.php">Sign up Here</a></p>
                    <a href="recovery.php" class="forgot">Forgot your password?</a>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="../script/script.js?v=<?= time(); ?>"></script>
</body>
</html>