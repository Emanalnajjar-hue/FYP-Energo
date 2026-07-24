<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['user_role'] != 1) {
    header('Location: ../home.php');
    exit;
}
?>