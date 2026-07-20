<?php
session_start();
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();

 $booking_id = isset($_POST['booking_id']) ? $_POST['booking_id'] : 0;
 $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

if ($user_id == 0 || $booking_id == 0) {
    $_SESSION['alert_message'] = ["type" => "error", "text" => "Invalid Request."];
    header("Location: mybooking.php");
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt_wallet = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ? FOR UPDATE");
    $stmt_wallet->execute([$user_id]);
    $wallet = $stmt_wallet->fetch(PDO::FETCH_ASSOC);

    if (!$wallet) {
        throw new Exception("Wallet not found.");
    }

    $stmt_check = $pdo->prepare("SELECT status, equipment_id, total_amount FROM bookings WHERE booking_id = ? AND user_id = ?");
    $stmt_check->execute([$booking_id, $user_id]);
    $booking = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        throw new Exception("Booking not found.");
    }

    if ($booking['status'] === 'cancelled' || $booking['status'] === 'completed') {
        throw new Exception("This booking cannot be cancelled.");
    }

    // 3. تحديث حالة الحجز
    $stmt_update_booking = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?");
    $stmt_update_booking->execute([$booking_id]);

    $total_paid = (float)$booking['total_amount'];
    $cancellation_fee = 5.00;
    $refund_amount = $total_paid - $cancellation_fee;

    if ($refund_amount > 0) {
        $stmt_update_wallet = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
        $stmt_update_wallet->execute([$refund_amount, $user_id]);
    }

    $release_product = $pdo->prepare("UPDATE equipment SET status = 'available' WHERE equipment_id = ?");
    $release_product->execute([$booking['equipment_id']]);

    $pdo->commit();

    $msg_text = ($refund_amount > 0) 
        ? "Booking cancelled. $" . number_format($refund_amount, 2) . " refunded to wallet ($5 fee deducted)." 
        : "Booking cancelled. Refund amount was fully consumed by the cancellation fee.";

    $_SESSION['alert_message'] = ["type" => "success", "text" => $msg_text];

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['alert_message'] = ["type" => "error", "text" => "Error: " . $e->getMessage()];
}

header("Location: mybooking.php");
exit;
?>