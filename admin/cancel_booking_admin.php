<?php
session_start();
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();

 $booking_id = isset($_GET['id']) ? $_GET['id'] : 0;

if ($booking_id == 0) {
    $_SESSION['msg'] = "Invalid Request.";
    $_SESSION['msg_type'] = "error";
    header("Location: bookings.php");
    exit;
}

try {
    $pdo->beginTransaction();

    
    $stmt_check = $pdo->prepare("SELECT status, equipment_id, total_amount, user_id FROM bookings WHERE booking_id = ?");
    $stmt_check->execute([$booking_id]);
    $booking = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        throw new Exception("Booking not found.");
    }

    if ($booking['status'] === 'cancelled') {
        throw new Exception("Booking is already cancelled.");
    }

    $target_user_id = $booking['user_id'];

    $stmt_wallet = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ? FOR UPDATE");
    $stmt_wallet->execute([$target_user_id]);
    $wallet = $stmt_wallet->fetch(PDO::FETCH_ASSOC);

    if (!$wallet) {
        throw new Exception("Wallet not found for this user.");
    }

    $stmt_update_booking = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?");
    $stmt_update_booking->execute([$booking_id]);
    $cancellation_fee = 5.00;
    $refund_amount = $booking['total_amount'] - $cancellation_fee;

    $stmt_update_wallet = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
    $stmt_update_wallet->execute([$refund_amount, $target_user_id]);

    $release_product = $pdo->prepare("UPDATE equipment SET status = 'available' WHERE equipment_id = ?");
    $release_product->execute([$booking['equipment_id']]);

    $pdo->commit();

    $_SESSION['msg'] = "Booking cancelled successfully. $" . number_format($refund_amount, 2) . " refunded to user.";
    $_SESSION['msg_type'] = "success";

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['msg'] = "Error: " . $e->getMessage();
    $_SESSION['msg_type'] = "error";
}

header("Location: bookings.php");
exit;
?>