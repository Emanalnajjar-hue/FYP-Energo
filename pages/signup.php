<?php
session_start();
 $msg       = '';
 $msg_type  = '';
 $old_first = '';
 $old_last  = '';
 $old_email = '';
 $old_phone = '';

if (isset($_SESSION['msg'])) {
    $msg      = $_SESSION['msg'];
    $msg_type = $_SESSION['msg_type'] ?? 'error';
    unset($_SESSION['msg'], $_SESSION['msg_type']);
}
if (isset($_SESSION['old_first'])) {
    $old_first = $_SESSION['old_first'];
    $old_last  = $_SESSION['old_last'];
    $old_email = $_SESSION['old_email'];
    $old_phone = $_SESSION['old_phone'] ?? ''; 
    unset($_SESSION['old_first'], $_SESSION['old_last'], $_SESSION['old_email'], $_SESSION['old_phone']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energo - Sign Up</title>
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
            <h2>Join Energo for smart energy solutions</h2>
        </div>
    </div>

    <div class="white-side">
        <div class="form-section">
            <div class="content-area">

                <input type="hidden" id="msg-data" value="<?= htmlspecialchars($msg) ?>" data-type="<?= $msg_type ?>">
                <div id="alert-box" style="display:none;"></div>

                <h3>Create Account</h3>
                <form action="auth.php" method="POST">
                    <input type="hidden" name="action" value="signup">
                    
                    <div class="name-row">
                        <div class="input-field">
                            <label for="first_name" style="color: #1a4d3a; font-weight: 600;">First Name</label>
                            <input type="text" id="first_name" name="first_name"
                                   autocomplete="given-name"
                                   placeholder="First Name"
                                   value="<?= htmlspecialchars($old_first) ?>" required>
                        </div>
                        <div class="input-field">
                            <label for="last_name"  style="color: #1a4d3a; font-weight: 600;">Last Name</label>
                            <input type="text" id="last_name" name="last_name"
                                   autocomplete="family-name"
                                   placeholder="Last Name"
                                   value="<?= htmlspecialchars($old_last) ?>" required>
                        </div>
                    </div>
                    
                    <div class="input-field">
                        <label for="email" style="color: #1a4d3a; font-weight: 600;">Email</label>
                        <input type="email" id="email" name="email"
                               autocomplete="email"
                               placeholder="Email Address"
                               value="<?= htmlspecialchars($old_email) ?>" required>
                    </div>
                    
                    <div class="input-field">
                        <label for="phone" style="color: #1a4d3a; font-weight: 600;">Phone Number</label>
                        <input type="text" id="phone" name="phone"
                               autocomplete="tel"
                               placeholder="059XXXXXXX"
                               maxlength="10"
                               value="<?= htmlspecialchars($old_phone) ?>" required>
                    </div>
                    
                    <div class="input-field">
    <label for="password" style="color: #1a4d3a; font-weight: 600;">Password</label>

    <div class="password-container">
        <input type="password"
               id="password"
               name="password"
               autocomplete="new-password"
               placeholder="••••••••"
               required>

        <span class="toggle-password" onclick="toggleSignupPassword()">
            <i class="fa-solid fa-eye"></i>
        </span>
    </div>
</div>
                  
                    <div class="checkbox-group">
                        <input type="checkbox" id="terms" name="terms" value="1" required>
                        <label for="terms">I Agree with <span class="orange-text">Terms and Conditions</span></label>
                    </div>
                    
                    <button type="submit" class="btn-submit">Create Account</button>
                </form>
                
                <div class="links">
                    <p>Already have an account? <a href="login.php">Login Here</a></p>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="../script/script.js?v=<?= time(); ?>"></script>
</body>
</html>