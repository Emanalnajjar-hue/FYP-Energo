<?php
require_once 'auth_check.php';
require_once '../config/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    $database = new Database();
    $pdo = $database->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        $stmt = $pdo->prepare("DELETE FROM delivery_requests WHERE request_id = ?");
        
        if ($stmt->execute([$id]) && $stmt->rowCount() > 0) {
            $_SESSION['msg'] = 'Delivery request deleted successfully!';
            $_SESSION['msg_type'] = 'success';
        } else {
            $_SESSION['msg'] = 'Request not found or already deleted.';
            $_SESSION['msg_type'] = 'error';
        }
    } catch (Exception $e) {
        $_SESSION['msg'] = 'Error deleting request.';
        $_SESSION['msg_type'] = 'error';
    }
} else {
    $_SESSION['msg'] = 'Invalid request.';
    $_SESSION['msg_type'] = 'error';
}

header('Location: delivery_requests.php');
exit;
?>