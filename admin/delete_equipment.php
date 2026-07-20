<?php
require_once 'auth_check.php';
require_once '../config/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    $database = new Database();
    $pdo = $database->getConnection();

    $stmt_img = $pdo->prepare("SELECT image_url FROM equipment WHERE equipment_id = ?");
    $stmt_img->execute([$id]);
    $equipment = $stmt_img->fetch(PDO::FETCH_ASSOC);

    if ($equipment) {
        $stmt = $pdo->prepare("DELETE FROM equipment WHERE equipment_id = ?");
        
        if ($stmt->execute([$id])) {
            if ($equipment['image_url'] !== 'default-equipment.jpg') {
                $image_path = '../images/' . $equipment['image_url'];
                if (file_exists($image_path)) {
                    unlink($image_path); 
                }
            }
            
            $_SESSION['msg'] = 'Equipment deleted successfully!';
            $_SESSION['msg_type'] = 'success';
        } else {
            $_SESSION['msg'] = 'Failed to delete equipment from database.';
            $_SESSION['msg_type'] = 'error';
        }
    } else {
        $_SESSION['msg'] = 'Equipment not found.';
        $_SESSION['msg_type'] = 'error';
    }
} else {
    $_SESSION['msg'] = 'Invalid request.';
    $_SESSION['msg_type'] = 'error';
}

header('Location: equipment.php');
exit;
?>