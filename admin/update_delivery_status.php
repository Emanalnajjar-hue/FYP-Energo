<?php
require_once 'auth_check.php';
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
    $status = trim($_POST['status'] ?? 'pending');

    if ($request_id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE delivery_requests SET status = ? WHERE request_id = ?");
            $stmt->execute([$status, $request_id]);
            
            $_SESSION['msg'] = 'Delivery status updated successfully!';
            $_SESSION['msg_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['msg'] = 'Error updating status.';
            $_SESSION['msg_type'] = 'error';
        }
    } else {
        $_SESSION['msg'] = 'Invalid Request ID.';
        $_SESSION['msg_type'] = 'error';
    }
}

header('Location: delivery_requests.php');
exit;