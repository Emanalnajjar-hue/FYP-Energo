<?php
require_once 'auth_check.php';
require_once '../config/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    if ($id == $_SESSION['user_id']) {
        $_SESSION['msg'] = 'You cannot delete your own account!';
        $_SESSION['msg_type'] = 'error';
        header('Location: users.php');
        exit;
    }

    $database = new Database();
    $pdo = $database->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        $stmt = $pdo->prepare("SELECT avatar FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        $userToDelete = $stmt->fetch();

        if ($userToDelete) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            
            if ($stmt->execute([$id])) {
                
                $avatarPath = $userToDelete['avatar'];
                if (!empty($avatarPath) && file_exists($avatarPath) && $avatarPath !== '../images/user.jpg') {
                    unlink($avatarPath);
                }

                $_SESSION['msg'] = 'User and their associated data deleted successfully!';
                $_SESSION['msg_type'] = 'success';
            } else {
                $_SESSION['msg'] = 'Failed to delete user.';
                $_SESSION['msg_type'] = 'error';
            }
        } else {
            $_SESSION['msg'] = 'User not found.';
            $_SESSION['msg_type'] = 'error';
        }

    } catch (Exception $e) {
        $_SESSION['msg'] = 'Cannot delete user. Error: ' . $e->getMessage();
        $_SESSION['msg_type'] = 'error';
    }
} else {
    $_SESSION['msg'] = 'Invalid request.';
    $_SESSION['msg_type'] = 'error';
}

header('Location: users.php');
exit;
?>