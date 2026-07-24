<?php
session_start();
require_once '../config/db.php';

 $database = new Database();
 $pdo = $database->getConnection();
 $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

 $userId = $_SESSION['user_id'];
 $toastMessage = '';
 $toastType = '';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User not found");
    }

    $user['name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE user_id = ?");
    $stmt->execute([$userId]);
    $rentalsCount = (int) $stmt->fetch()['total'];

    $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
    $stmt->execute([$userId]);
    $wallet = $stmt->fetch();
    $balance = $wallet ? floatval($wallet['balance']) : 0;

    $membership = 'Silver Member';

   
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $serviceName   = trim($_POST['service_name'] ?? '');
        $feedbackText  = trim($_POST['feedback_text'] ?? '');
        $rating        = intval($_POST['rating'] ?? 0);

        $hasError = false;

        if (empty($serviceName) || empty($feedbackText)) {
            $toastMessage = 'Please fill in all fields.';
            $toastType = 'error';
            $hasError = true;
        }

        if (!$hasError && $rating < 1 || $rating > 5) {
            $toastMessage = 'Please select a star rating.';
            $toastType = 'error';
            $hasError = true;
        }

        if (!$hasError) {
            $stmt = $pdo->prepare("
                INSERT INTO testimonials (user_id, service_name, feedback_text, rating, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $serviceName, $feedbackText, $rating]);

            $toastMessage = 'Thank you! Feedback sent successfully.';
            $toastType = 'success';
        }
    }

} catch (PDOException $e) {
    $toastMessage = 'DB Error: ' . $e->getMessage();
    $toastType = 'error';
}

 $avatarSrc = !empty($user['avatar']) ? $user['avatar'] : '../images/user.jpg';
 $fullAddress = trim(($user['address'] ?? '') . ', ' . ($user['city'] ?? ''), ', ');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Testimonial</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/testmimonial-style.css">
</head>

<body>

    <div class="profile-container">
        <div class="sidebar">
            <h2>Post a Testimonial</h2>
            <p>Share your experience and feedback with us</p>

            <div class="avatar-container">
                <img src="<?= htmlspecialchars($avatarSrc) ?>" alt="Profile" class="avatar-img">
            </div>

            <h3 id="display-name"><?= htmlspecialchars($user['name'] ?? 'User Name') ?></h3>
            <p id="display-email" class="email-text"><?= htmlspecialchars($user['email'] ?? 'example@email.com') ?></p>

            <div class="status-badge"><i class="fa-solid fa-check-circle"></i> Verified User</div>

            <div class="user-info-list">
                <p><i class="fa-solid fa-phone"></i> <span id="display-phone"><?= htmlspecialchars($user['phone'] ?? 'Not provided') ?></span></p>
                <p><i class="fa-solid fa-location-dot"></i> <span id="display-address"><?= htmlspecialchars($fullAddress ?: 'Not provided') ?></span></p>
                <p><i class="fa-solid fa-shield-halved"></i> Membership Level: <strong><?= $membership ?></strong></p>
            </div>
            
            <div class="rentals-box">
                <i class="fa-solid fa-box-archive"></i>
                <div class="rentals-text">
                    <p>Total Rentals</p>
                    <strong id="rental-count-display"><?= $rentalsCount ?> Rentals</strong>
                </div>
            </div>
        </div>

        <div class="main-form">
            <h2>Please share your feedback below</h2>
            <p>Manage your feedback and reviews</p>

            <form id="testimonial-form" method="POST">
                
                <input type="hidden" name="rating" id="rating-value" value="0">

                <div class="input-field">
                    <label>Service or Product</label>
                    <input type="text" name="service_name" placeholder="What did you use?" required>
                </div>

                <div class="input-field">
                    <label>Your Feedback</label>
                    <textarea name="feedback_text" rows="5" placeholder="Write your experience..." required></textarea>
                </div>

                <div class="input-field">
                    <label>How satisfied are you?</label>
                    <div class="star-rating">
                        <i class="fa-solid fa-star" data-value="1"></i>
                        <i class="fa-solid fa-star" data-value="2"></i>
                        <i class="fa-solid fa-star" data-value="3"></i>
                        <i class="fa-solid fa-star" data-value="4"></i>
                        <i class="fa-solid fa-star" data-value="5"></i>
                    </div>
                </div>

                <button type="submit" class="save-btn">Send Feedback</button>
            </form>
        </div>
    </div>

    <?php if ($toastMessage): ?>
    
    <style>
        #inline-toast {
            position: fixed !important;
            top: 25px !important;
            bottom: auto !important;
            right: auto !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            padding: 14px 28px !important;
            border-radius: 8px !important;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2) !important;
            z-index: 999999 !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            font-size: 14px !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            color: #fff !important;
            animation: slideDown 0.4s ease forwards !important;
        }
        #inline-toast.toast-success {
            background-color: #10482D !important;
            border-left: 5px solid #2ecc71 !important;
        }
        #inline-toast.toast-error {
            background-color: #721c24 !important;
            border-left: 5px solid #f5c6cb !important;
            color: #f5c6cb !important;
        }
        @keyframes slideDown {
            from { opacity: 0; top: -50px; }
            to { opacity: 1; top: 25px; }
        }
    </style>

    <div id="inline-toast" class="<?= $toastType === 'success' ? 'toast-success' : 'toast-error' ?>">
        <?php if($toastType === 'success'): ?>
            <i class="fa-solid fa-circle-check"></i>
        <?php else: ?>
            <i class="fa-solid fa-circle-exclamation"></i>
        <?php endif; ?>
        <?= htmlspecialchars($toastMessage) ?>
    </div>
    
    <script>
        setTimeout(() => {
            let toast = document.getElementById('inline-toast');
            if(toast) {
                toast.style.transition = 'opacity 0.4s ease, top 0.4s ease';
                toast.style.opacity = '0';
                toast.style.top = '-50px';
            }
        }, 3000);
    </script>
    
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const stars = document.querySelectorAll('.star-rating i');
            let currentRating = 0;
            const ratingInput = document.getElementById('rating-value');

            function highlightStars(count) {
                stars.forEach(s => {
                    if (s.getAttribute('data-value') <= count) {
                        s.classList.add('active');
                        s.style.color = '#FFD700';
                    } else {
                        s.classList.remove('active');
                        s.style.color = '#e0e0e0';
                    }
                });
            }

            stars.forEach(star => {
                star.addEventListener('mouseover', function () {
                    highlightStars(this.getAttribute('data-value'));
                });

                star.addEventListener('click', function () {
                    currentRating = this.getAttribute('data-value');
                    ratingInput.value = currentRating;
                    highlightStars(currentRating);
                });
            });

            document.querySelector('.star-rating').addEventListener('mouseleave', () => {
                highlightStars(currentRating);
            });

            const form = document.getElementById('testimonial-form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    if (currentRating === 0) {
                        e.preventDefault();
                        alert("Please select a star rating.");
                        return;
                    }
                });
            }
        });
    </script>

</body>
</html>