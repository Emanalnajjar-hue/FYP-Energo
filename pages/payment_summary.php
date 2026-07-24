<?php
session_start();
require_once '../config/db.php';

$database = new Database();
$pdo = $database->getConnection();

$product_id   = isset($_POST['product_id']) ? $_POST['product_id'] : (isset($_GET['product_id']) ? $_GET['product_id'] : 0);
$pickup_date  = isset($_GET['pickup']) ? $_GET['pickup'] : '';
$return_date  = isset($_GET['return']) ? $_GET['return'] : '';
$user_id      = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

$error_msg     = "";
$user_phone    = "+970-00-000000";
$user_balance  = 0.00;

$product_name  = "Unknown Product";
$product_image = "../images/default.png";
$branch_name   = "Main Branch";
$price_per_day = 0;
$total_amount  = 0; 

if ($product_id) {
    $stmt = $pdo->prepare("SELECT name, image_url, location, price_per_day FROM equipment WHERE equipment_id = ?");
    $stmt->execute([$product_id]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($prod) {
        $product_name  = $prod['name'];
        $product_image = "../images/" . $prod['image_url'];
        $branch_name   = ucfirst($prod['location']) . " Branch";
        $price_per_day = (float)$prod['price_per_day'];
    }
}

$days = 0;
if ($pickup_date && $return_date) {
    try {
        $start = new DateTime($pickup_date);
        $end   = new DateTime($return_date);
        $days  = (int)$start->diff($end)->d;
        if ($days <= 0) {
            $days = 1;
        }
    } catch (Exception $e) {
        $days = 0;
    }
}
$total_amount = round($price_per_day * $days, 2);

if ($user_id) {
    $stmt_wallet = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
    $stmt_wallet->execute([$user_id]);
    $wallet_data = $stmt_wallet->fetch(PDO::FETCH_ASSOC);
    if ($wallet_data) {
        $user_balance = $wallet_data['balance'];
    }

    $stmt_user = $pdo->prepare("SELECT phone FROM users WHERE user_id = ?");
    $stmt_user->execute([$user_id]);
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
    if ($user_data) {
        $user_phone = $user_data['phone'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay_now'])) {

    if ($user_id == 0) {
        $error_msg = "Please login first.";
    } elseif (!$product_id || $price_per_day <= 0) {
        $error_msg = "Invalid product.";
    } elseif ($days <= 0) {
        $error_msg = "Invalid rental dates.";
    } else {

        try {
            $pdo->beginTransaction();

            $stmt_check_balance = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ? FOR UPDATE");
            $stmt_check_balance->execute([$user_id]);
            $wallet_row = $stmt_check_balance->fetch(PDO::FETCH_ASSOC);

            if (!$wallet_row) {
                throw new Exception("Wallet not found.");
            }

            $current_db_balance = $wallet_row['balance'];

            $stmt_price = $pdo->prepare("SELECT price_per_day FROM equipment WHERE equipment_id = ? FOR UPDATE");
            $stmt_price->execute([$product_id]);
            $price_row = $stmt_price->fetch(PDO::FETCH_ASSOC);

            if (!$price_row) {
                throw new Exception("Product not found.");
            }

            $secure_total_amount = round(((float)$price_row['price_per_day']) * $days, 2);

            if ($current_db_balance >= $secure_total_amount) {

                $stmt_check_dates = $pdo->prepare("SELECT COUNT(*) FROM bookings 
                                                    WHERE equipment_id = ? 
                                                    AND status != 'cancelled' 
                                                    AND (pickup_date < ? AND return_date > ?)");

                $stmt_check_dates->execute([$product_id, $return_date, $pickup_date]);
                $conflicting_bookings = $stmt_check_dates->fetchColumn();

                if ($conflicting_bookings > 0) {
                    throw new Exception("Sorry, this equipment is already booked for the selected dates. Please choose different dates.");
                }

                $new_balance = $current_db_balance - $secure_total_amount;
                $update_wallet = $pdo->prepare("UPDATE wallets SET balance = ? WHERE user_id = ?");
                $update_wallet->execute([$new_balance, $user_id]);
                do {
                    $qr_token = bin2hex(random_bytes(32));
                    $check_token = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE qr_token = ?");
                    $check_token->execute([$qr_token]);
                } while ($check_token->fetchColumn() > 0);

                $insert_booking = $pdo->prepare("INSERT INTO bookings (user_id, equipment_id, pickup_date, return_date, total_amount, status, qr_token) VALUES (?, ?, ?, ?, ?, 'confirmed', ?)");
                $insert_booking->execute([$user_id, $product_id, $pickup_date, $return_date, $secure_total_amount, $qr_token]);

                $last_booking_id = $pdo->lastInsertId();

                $update_product = $pdo->prepare("UPDATE equipment SET status = 'booked' WHERE equipment_id = ?");
                $update_product->execute([$product_id]);

                $pdo->commit();

                $user_balance = $new_balance;

                header("Location: scan_qr.php?booking_id=" . $last_booking_id);
                exit();

            } else {
                $pdo->rollBack();
                $error_msg = "Insufficient funds! Your balance is $" . number_format($current_db_balance, 2) . " but you need $" . number_format($secure_total_amount, 2) . ".";
            }

        } catch (Exception $e) {
            $pdo->rollBack();
            $error_msg = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/payment-style.css">
</head>

<body>

    <div class="main-wrapper">
        <header class="page-header">
            <h2>Secure Checkout</h2>
            <div class="steps-indicator">
                <span class="step active"><i class="fa-solid fa-clipboard-check"></i> Details</span>
                <i class="fa-solid fa-chevron-right arrow"></i>
                <span class="step active"><i class="fa-solid fa-credit-card"></i> Payment</span>
                <i class="fa-solid fa-chevron-right arrow"></i>
                <span class="step"><i class="fa-solid fa-check-circle"></i> Confirmation</span>
            </div>
        </header>

        <form method="POST" action="">
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>">

            <div class="checkout-grid">

                <section class="summary-card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-receipt"></i> Booking Summary</h3>
                    </div>

                    <div class="product-summary">
                        <img src="<?= htmlspecialchars($product_image) ?>" alt="Product" class="prod-thumb" onerror="this.src='../images/default.png'">
                        <div>
                            <h4><?= htmlspecialchars($product_name) ?></h4>
                            <p class="location-tag"><i class="fa-solid fa-map-marker-alt"></i> <?= htmlspecialchars($branch_name) ?></p>
                        </div>
                        <div class="prod-price-total">$<?= number_format($total_amount, 2) ?></div>
                    </div>

                    <hr class="divider">

                    <div class="booking-details">
                        <div class="detail-row">
                            <span class="label">Duration</span>
                            <span class="value"><?= $days ?> Days</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Price / Day</span>
                            <span class="value">$<?= number_format($price_per_day, 2) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Pickup Date</span>
                            <span class="value"><?= htmlspecialchars($pickup_date) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Return Date</span>
                            <span class="value"><?= htmlspecialchars($return_date) ?></span>
                        </div>
                    </div>

                    <div class="total-row">
                        <span>Total to Pay</span>
                        <span class="final-price">$<?= number_format($total_amount, 2) ?></span>
                    </div>
                </section>

                <section class="payment-card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-wallet"></i> Payment Method</h3>
                    </div>

                    <div class="payment-method-box">
                        <div class="method-header">
                            <img src="../images/palpay.png" alt="PalPay" class="method-logo">
                            <div class="method-info">
                                <strong>Wallet Balance</strong>
                                <span>Secure Internal Wallet</span>
                            </div>
                            <i class="fa-solid fa-check-circle checked-icon"></i>
                        </div>

                        <div class="payment-form">
                            <label>Registered Mobile Number</label>
                            <div class="input-wrapper">
                                <img src="../images/pal-flag.jpg" alt="Flag" class="flag">
                                <input type="text" value="<?= htmlspecialchars($user_phone) ?>" class="phone-input" readonly>
                                <span class="verified-badge">Verified</span>
                            </div>

                            <div class="wallet-balance-display">
                                Your Current Balance: <span>$<?= number_format($user_balance, 2) ?></span>
                            </div>

                            <p class="small-note">Funds will be deducted immediately upon confirmation.</p>
                        </div>
                    </div>

                    <?php if ($error_msg): ?>
                        <div class="alert-error">
                            <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error_msg) ?>
                        </div>
                    <?php endif; ?>

                    <button type="submit" name="pay_now" class="pay-btn">
                        Confirm Pay
                        <i class="fa-solid fa-lock"></i>
                    </button>

                    <p class="secure-text">
                        <i class="fa-solid fa-shield-halved"></i> 256-bit SSL Encrypted Payment. Your data is safe.
                    </p>
                </section>

            </div>
        </form>
    </div>

</body>
</html>