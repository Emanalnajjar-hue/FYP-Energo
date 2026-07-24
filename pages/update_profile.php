<?php
session_start();

require_once '../config/db.php'; 
 $database = new Database();
 $pdo = $database->getConnection();

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

 $user_id = $_SESSION['user_id'];

 $fullName = $_POST['full_name'] ?? '';
 $nameParts = explode(' ', trim($fullName), 2);
 $firstName = $nameParts[0];
 $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

 $email = $_POST['email'];
 $phone = $_POST['phone'] ?? '';
 $dob = $_POST['dob'] ?? null;
 $address = $_POST['address'] ?? '';
 $city = $_POST['city'] ?? '';
 $currentAvatar = $_POST['current_avatar'] ?? '';

 $avatarPath = $currentAvatar; 
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
    $targetDir = "../uploads/users/";
    
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES["profile_image"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
    
    if (in_array($fileType, $allowTypes)) {
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFilePath)) {
            $avatarPath = $fileName; 
        }
    }
}

try {
    if ($avatarPath !== $currentAvatar) {
        $sql = "UPDATE users SET first_name=?, last_name=?, email=?, phone=?, dob=?, address=?, city=?, avatar=? WHERE user_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$firstName, $lastName, $email, $phone, $dob, $address, $city, $avatarPath, $user_id]);
    } else {
        $sql = "UPDATE users SET first_name=?, last_name=?, email=?, phone=?, dob=?, address=?, city=? WHERE user_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$firstName, $lastName, $email, $phone, $dob, $address, $city, $user_id]);
    }

    header("Location: ../profile.php?status=success");

} catch (PDOException $e) {
    header("Location: ../profile.php?status=error");
}
?>