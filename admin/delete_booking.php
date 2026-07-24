<?php
session_start();
require_once '../config/db.php';

if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    $_SESSION['msg'] = "Invalid security token. Please try again.";
    $_SESSION['msg_type'] = "error";
    header("Location: bookings.php");
    exit;
}

 $booking_id = isset($_POST['booking_id']) ? (int) $_POST['booking_id'] : 0;

if ($booking_id === 0) {
    $_SESSION['msg'] = "Invalid Request.";
    $_SESSION['msg_type'] = "error";
    header("Location: bookings.php");
    exit;
}

 $database = new Database();
 $pdo = $database->getConnection();

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        throw new Exception("Booking not found.");
    }

    $stmt_del = $pdo->prepare("DELETE FROM bookings WHERE booking_id = ?");
    $stmt_del->execute([$booking_id]);

    $stmt_release = $pdo->prepare("UPDATE equipment SET status = 'available' WHERE equipment_id = ? AND status != 'available'");
    $stmt_release->execute([$booking['equipment_id']]);

    $stmt_refund = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
    $stmt_refund->execute([$booking['total_amount'], $booking['user_id']]);

    $pdo->commit();

    $_SESSION['msg'] = "Booking deleted and refunded successfully.";
    $_SESSION['msg_type'] = "success";

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['msg'] = "Error: " . $e->getMessage();
    $_SESSION['msg_type'] = "error";
}

header("Location: bookings.php");
exit;