<?php
session_start();
require_once 'auth_check.php';
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['status'];

    $stmt = $pdo->prepare("SELECT equipment_id FROM bookings WHERE booking_id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        $equip_id = $booking['equipment_id'];
        
        try {
            $pdo->beginTransaction();

            $upd_book = $pdo->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
            $upd_book->execute([$new_status, $booking_id]);

            if (!empty($equip_id)) {
                if ($new_status == 'active') {
                    $upd_eq = $pdo->prepare("UPDATE equipment SET status = 'booked' WHERE equipment_id = ?");
                } elseif ($new_status == 'completed' || $new_status == 'cancelled') {
                    $upd_eq = $pdo->prepare("UPDATE equipment SET status = 'available' WHERE equipment_id = ?");
                } else {
                    $upd_eq = null; 
                }

                if ($upd_eq) {
                    $upd_eq->execute([$equip_id]);
                }
            }

            $pdo->commit();
            $_SESSION['msg'] = "Booking status updated successfully!";
            $_SESSION['msg_type'] = "success";

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['msg'] = "Error updating status.";
            $_SESSION['msg_type'] = "error";
        }
    }
    header("Location: bookings.php");
    exit();
}