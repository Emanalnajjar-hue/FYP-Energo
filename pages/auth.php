<?php
ob_start();    
session_start();
require_once '../config/db.php'; 

 $database = new Database();
 $pdo       = $database->getConnection();

 $action = $_POST['action'] ?? '';

switch ($action) {

    case 'login':
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $_SESSION['msg']       = 'Please fill all fields';
            $_SESSION['msg_type']  = 'error';
            $_SESSION['old_email'] = $email;
            header('Location: login.php');
            exit;
        }

        $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, password, user_role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true); 

            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_role'] = $user['user_role'];
            
            header('Location: home.php'); 
            exit;
        } else if (!$user) {
            $_SESSION['msg']       = 'No account found with this email address';
            $_SESSION['msg_type']  = 'error';
            $_SESSION['old_email'] = $email;
            header('Location: login.php');
            exit; 
        } else {
            $_SESSION['msg']       = 'Incorrect password. Please try again';
            $_SESSION['msg_type']  = 'error';
            $_SESSION['old_email'] = $email;
            header('Location: login.php');
            exit; 
        }
        break;

    case 'signup':
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name  = trim($_POST['last_name']  ?? '');
        $email      = trim($_POST['email']      ?? '');
        $phone      = trim($_POST['phone']      ?? '');
        $password   = $_POST['password']        ?? '';
        $terms      = $_POST['terms']           ?? '';
        
        // الدور ثابت دائماً كـ customer
        $user_role  = 'customer'; 

        if (!$first_name || !$last_name || !$email || !$phone || !$password) {
            $_SESSION['msg']       = 'Please fill all fields';
            $_SESSION['msg_type']  = 'error';
            $_SESSION['old_first'] = $first_name;
            $_SESSION['old_last']  = $last_name;
            $_SESSION['old_email'] = $email;
            $_SESSION['old_phone'] = $phone;
            header('Location: signup.php');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['msg']       = 'Invalid email address';
            $_SESSION['msg_type']  = 'error';
            $_SESSION['old_first'] = $first_name;
            $_SESSION['old_last']  = $last_name;
            $_SESSION['old_email'] = $email;
            $_SESSION['old_phone'] = $phone;
            header('Location: signup.php');
            exit;
        }
        if (!preg_match('/^\d{10}$/', $phone)) {
            $_SESSION['msg']       = 'Phone number must be exactly 10 digits';
            $_SESSION['msg_type']  = 'error';
            $_SESSION['old_first'] = $first_name;
            $_SESSION['old_last']  = $last_name;
            $_SESSION['old_email'] = $email;
            $_SESSION['old_phone'] = $phone;
            header('Location: signup.php');
            exit;
        }
        if (strlen($password) < 6) {
            $_SESSION['msg']       = 'Password must be at least 6 characters';
            $_SESSION['msg_type']  = 'error';
            $_SESSION['old_first'] = $first_name;
            $_SESSION['old_last']  = $last_name;
            $_SESSION['old_email'] = $email;
            $_SESSION['old_phone'] = $phone;
            header('Location: signup.php');
            exit;
        }
        if ($terms !== '1') {
            $_SESSION['msg']       = 'Please accept the terms';
            $_SESSION['msg_type']  = 'error';
            $_SESSION['old_first'] = $first_name;
            $_SESSION['old_last']  = $last_name;
            $_SESSION['old_email'] = $email;
            $_SESSION['old_phone'] = $phone;
            header('Location: signup.php');
            exit;
        }

        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['msg']       = 'Email already registered';
            $_SESSION['msg_type']  = 'error';
            $_SESSION['old_first'] = $first_name;
            $_SESSION['old_last']  = $last_name;
            $_SESSION['old_email'] = $email;
            $_SESSION['old_phone'] = $phone;
            header('Location: signup.php');
            exit;
        }

        // ====== إنشاء الحساب والمحفظة باستخدام المعاملات (Transactions) ======
        try {
            $pdo->beginTransaction();

            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, password, user_role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $email, $phone, $hashed, $user_role]);
            
            $new_user_id = $pdo->lastInsertId();

            $stmt_wallet = $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)");
            $stmt_wallet->execute([$new_user_id]);

            $pdo->commit();

            $_SESSION['msg']      = 'Account created successfully! Please login.';
            $_SESSION['msg_type'] = 'success';
            header('Location: login.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['msg']      = 'Something went wrong. Please try again.';
            $_SESSION['msg_type'] = 'error';
            header('Location: signup.php');
            exit;
            
            
        
        }

    case 'recovery':
        $email            = trim($_POST['email']            ?? '');
        $phone            = trim($_POST['phone']            ?? '');
        $new_password     = $_POST['new_password']          ?? '';
        $confirm_password = $_POST['confirm_password']      ?? '';

        if (!$email || !$phone || !$new_password || !$confirm_password) {
            $_SESSION['msg']       = 'Please fill all fields';
            $_SESSION['msg_type']  = 'error';
            $_SESSION['old_email'] = $email;
            $_SESSION['old_phone'] = $phone;
            header('Location: recovery.php');
            exit;
        }
        if (!preg_match('/^\d{10}$/', $phone)) {
            $_SESSION['msg']       = 'Phone number must be exactly 10 digits';
            $_SESSION['msg_type']  = 'error';
            $_SESSION['old_email'] = $email;
            $_SESSION['old_phone'] = $phone;
            header('Location: recovery.php');
            exit;
        }
        if ($new_password !== $confirm_password) {
            $_SESSION['msg']       = 'Passwords do not match';
            $_SESSION['msg_type']  = 'error';
            $_SESSION['old_email'] = $email;
            $_SESSION['old_phone'] = $phone;
            header('Location: recovery.php');
            exit;
        }
        if (strlen($new_password) < 6) {
            $_SESSION['msg']       = 'Password must be at least 6 characters';
            $_SESSION['msg_type']  = 'error';
            $_SESSION['old_email'] = $email;
            $_SESSION['old_phone'] = $phone;
            header('Location: recovery.php');
            exit;
        }

        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND phone = ?");
        $stmt->execute([$email, $phone]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['msg']       = 'No account found with this email and phone number';
            $_SESSION['msg_type']  = 'error';
            $_SESSION['old_email'] = $email;
            $_SESSION['old_phone'] = $phone;
            header('Location: recovery.php');
            exit;
        }

        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt   = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$hashed, $user['user_id']]);

        $_SESSION['msg']      = 'Password reset successfully! Please login.';
        $_SESSION['msg_type'] = 'success';
        header('Location: login.php');
        exit;
        break;

    default:
        header('Location: login.php');
        exit;
}
?>