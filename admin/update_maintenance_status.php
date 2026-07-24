<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    die("Access Denied");
}

require_once '../config/db.php';
 $database = new Database();
 $pdo = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? null;
    $action = $_POST['action'] ?? null; 

    if ($request_id && $action) {
        $stmt = $pdo->prepare("SELECT equipment_id FROM maintenance_requests WHERE request_id = ?");
        $stmt->execute([$request_id]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($request) {
            $equipment_id = $request['equipment_id'];
            
            try {
                $pdo->beginTransaction(); 

                if ($action === 'approve') {
                    $upd_req = $pdo->prepare("UPDATE maintenance_requests SET status = 'In Progress' WHERE request_id = ?");
                    $upd_req->execute([$request_id]);
                    
                    if (!empty($equipment_id)) {
                        $upd_eq = $pdo->prepare("UPDATE equipment SET status = 'under_maintenance' WHERE equipment_id = ?");
                        $upd_eq->execute([$equipment_id]);
                    }

                } elseif ($action === 'reject') {
                    $upd_req = $pdo->prepare("UPDATE maintenance_requests SET status = 'Rejected' WHERE request_id = ?");
                    $upd_req->execute([$request_id]);
                    

                } elseif ($action === 'complete') {
                    $upd_req = $pdo->prepare("UPDATE maintenance_requests SET status = 'Completed' WHERE request_id = ?");
                    $upd_req->execute([$request_id]);
                    
                    if (!empty($equipment_id)) {
                        $upd_eq = $pdo->prepare("UPDATE equipment SET status = 'available' WHERE equipment_id = ?");
                        $upd_eq->execute([$equipment_id]);
                    }
                }

                $pdo->commit(); 
                
                $_SESSION['msg'] = "Status updated successfully!";
                $_SESSION['msg_type'] = "success";
                header("Location: maintenance.php");
                exit();

            } catch (Exception $e) {
                $pdo->rollBack(); 
                $_SESSION['msg'] = "Error updating status.";
                $_SESSION['msg_type'] = "error";
                header("Location: maintenance.php");
                exit();
            }
        }
    }
}
header("Location: maintenance.php");
exit();