<?php
require_once 'auth_check.php';
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $pdo->prepare("DELETE FROM maintenance_requests WHERE request_id = ?");
    
    if ($stmt->execute([$id])) {
        $_SESSION['msg'] = "Maintenance request deleted successfully.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "Error deleting request.";
        $_SESSION['msg_type'] = "error";
    }
} else {
    $_SESSION['msg'] = "Invalid Request ID.";
    $_SESSION['msg_type'] = "error";
}

header("Location: maintenance.php");
exit();
?>