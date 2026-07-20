<?php
// header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// header("Cache-Control: post-check=0, pre-check=0", false);
// header("Pragma: no-cache");

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();

require_once '../config/db.php';

 $db_path = __DIR__ . '/../config/db.php';
if (file_exists($db_path)) {
    include_once $db_path;
} else {
    die("<div style='color:red; font-family:sans-serif; padding:20px; background:#fcc;'>خطأ حرج: ملف الاتصال بقاعدة البيانات غير موجود!</div>");
}

try {
    $database = new Database();
    $pdo = $database->getConnection();
} catch (Exception $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

 $is_logged_in = false;
 $display_name = "";
 $display_email = "";
 $readonly_attr = "";
 $bg_style = "background-color: #ffffff;"; 
 $uid = null;

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $is_logged_in = true;
    $uid = (int)$_SESSION['user_id']; 

    $stmt = $pdo->prepare("SELECT first_name, last_name, email FROM users WHERE user_id = ?");
    $stmt->execute([$uid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $display_name = trim($user['first_name'] . ' ' . $user['last_name']);
        $display_email = $user['email'];
        
        $readonly_attr = "readonly";
        $bg_style = "background-color: #f3f4f6; cursor: not-allowed; color: #555; border: 1px solid #ddd;";
    }
}

 $equipment_list = [];
try {
    if ($is_logged_in) {
        $booking_stmt = $pdo->prepare("SELECT equipment_id FROM bookings WHERE user_id = ? AND status IN ('confirmed', 'active')");
        $booking_stmt->execute([$uid]);
        $bookings = $booking_stmt->fetchAll(PDO::FETCH_ASSOC);

        $booked_ids = [];
        foreach ($bookings as $b) {
            if (!empty($b['equipment_id'])) {
                $booked_ids[] = (int)$b['equipment_id'];
            }
        }

        if (!empty($booked_ids)) {
            $booked_ids = array_unique($booked_ids); 
            $placeholders = implode(',', array_fill(0, count($booked_ids), '?'));
            
            $eq_stmt = $pdo->prepare("
                SELECT e.equipment_id, e.name 
                FROM equipment e
                WHERE e.equipment_id IN ($placeholders) 
                AND e.equipment_id NOT IN (
                    SELECT equipment_id FROM maintenance_requests 
                    WHERE equipment_id IS NOT NULL 
                    AND status IN ('Pending', 'In Progress')
                )
            ");
            $eq_stmt->execute($booked_ids);
            $equipment_list = $eq_stmt->fetchAll(PDO::FETCH_ASSOC);
        }

    } else {
        $eq_stmt = $pdo->query("
            SELECT equipment_id, name FROM equipment 
            WHERE equipment_id NOT IN (
                SELECT equipment_id FROM maintenance_requests 
                WHERE equipment_id IS NOT NULL 
                AND status IN ('Pending', 'In Progress')
            )
        ");
        $equipment_list = $eq_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo "<div style='color:orange; padding:10px;'>خطأ أثناء جلب المعدات: " . $e->getMessage() . "</div>";
}

 $message = "";
 $msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];
    
    if (empty($_POST['issue_type'])) $errors[] = "Please select an Issue Type.";
    if (empty($_POST['description'])) $errors[] = "Please describe the issue.";
    if (empty($_POST['location'])) $errors[] = "Please select your location.";
    if (empty($_POST['equipment_id'])) $errors[] = "Please select the equipment.";

    if (!$is_logged_in) {
        if (empty($_POST['fullname_display'])) $errors[] = "Full Name is required.";
        if (empty($_POST['email_display'])) $errors[] = "Email Address is required.";
    }

    if (!empty($errors)) {
        $message = implode("<br>", $errors);
        $msg_type = "error";
    } else {
        $issue_type = $_POST['issue_type'];
        $landmark = $_POST['landmark'];
        $location = $_POST['location'];
        $description = $_POST['description'];
        $selected_equipment_id = (int)$_POST['equipment_id'];
        $user_id = $is_logged_in ? $uid : NULL;

        $sql = "INSERT INTO maintenance_requests (user_id, equipment_id, issue_type, description, created_at, status, location, landmark) 
                VALUES (?, ?, ?, ?, NOW(), 'Pending', ?, ?)";

        $stmt = $pdo->prepare($sql);
        if ($stmt) {
            $result = $stmt->execute([$user_id, $selected_equipment_id, $issue_type, $description, $location, $landmark]);
            if ($result) {
                $message = "Your request has been submitted successfully! Our team will review it and get back to you shortly.";
                $msg_type = "success";
            } else {
                $message = "Error sending request. Please try again.";
                $msg_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance & Technical Support</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/maintenance-style.css"> 
    
    <style>
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; text-align: center; font-weight: 500; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        input[readonly] { pointer-events: none; } 

        .equipment-wrapper { margin-bottom: 15px; }
        .equipment-select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; background-color: white; }
    </style>
</head>

<body>

    <div class="container">
        <header class="header">
            <div class="header-overlay"></div>
            <div class="header-text">
                <h1>Maintenance & <span>Technical Support</span></h1>
                <p>Keep your generators and batteries running efficiently with fast maintenance and live technician support.</p>

                <div class="header-buttons">
                    <button class="btn-primary"
                        onclick="document.getElementById('report-section').scrollIntoView({behavior: 'smooth'});">Report an Issue</button>
                    <a href="home.php" class="btn-secondary">← Back to home</a>
                </div>
            </div>
        </header>

        <div class="main-content">
            <section class="form-section" id="report-section">
                <h3 class="section-heading"><i class="fas fa-tools"></i> Report an Issue</h3>
                
                <?php if (!empty($message)): ?>
                    <div class="alert <?= htmlspecialchars($msg_type) ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <?php if (!$is_logged_in): ?>
                    <div class="alert" style="background-color: #e3f2fd; color: #0d47a1; border:none; margin-bottom: 20px;">
                        <i class="fas fa-info-circle"></i> <strong>Guest Access:</strong> You are submitting as a guest. 
                        <a href="login.php" style="font-weight:bold; text-decoration:underline;">Login here</a> to save your history.
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
    <div class="row">
        <input type="text" 
               name="fullname_display" 
               value="<?= htmlspecialchars($display_name) ?>" 
               placeholder="Full Name" 
               required 
               <?= $readonly_attr ?>>
        
        <input type="email" 
               name="email_display" 
               value="<?= htmlspecialchars($display_email) ?>" 
               placeholder="Email Address" 
               required 
               <?= $readonly_attr ?>>
    </div>

    <div class="equipment-wrapper">
        <label>
            <?= $is_logged_in ? 'Select Your Booked Equipment' : 'Select Equipment' ?>
        </label>
        <select name="equipment_id" class="equipment-select" required>
            <option value="" disabled selected>-- Generator/Battery --</option>
            <?php if (!empty($equipment_list)): ?>
                <?php foreach ($equipment_list as $eq): ?>
                    <option value="<?= $eq['equipment_id'] ?>">
                        <?= htmlspecialchars($eq['name']) ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="" disabled>No booked equipment found</option>
            <?php endif; ?>
        </select>
    </div>
    
    <div class="row">
        <select name="issue_type" required>
            <option value="" disabled selected>Issue Type</option>
            <option value="Generator Failure">Generator Failure</option>
            <option value="Battery Damage">Battery Damage</option>
            <option value="Cable Issue">Cable Issue</option>
            <option value="Routine Check">Routine Check</option>
            <option value="Other">Other</option>
        </select>
        
        <input type="text" 
               name="landmark" 
               placeholder="Nearest Landmark">
    </div>
    
    <select name="location" class="full-width" required>
        <option value="" disabled selected>Select your location</option>
        <option value="North Gaza">North Gaza</option>
        <option value="Central Gaza">Central Gaza</option>
        <option value="South Gaza">South Gaza</option>
    </select>
    
    <textarea name="description" rows="5"
        placeholder="Please describe the issue in detail so our team can assist you better."
        required></textarea>
        
    <button type="submit" class="btn-send">Send Request <i class="fas fa-paper-plane"></i></button>
</form>
            </section>

            <aside class="contact-info">
                <h3 class="section-heading"><i class="fas fa-address-book"></i> Contact Information</h3>

                <div class="info-card">
                    <i class="fas fa-phone-alt"></i>
                    <div>
                        <strong>Phone Number</strong>
                        <p>+972-59-321-4960</p>
                    </div>
                </div>

                <div class="info-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <strong>Service Area</strong>
                        <p>North, Central & South Gaza</p>
                    </div>
                </div>

                <div class="info-card">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <strong>Email Address</strong>
                        <p>energocamp@gmail.com</p>
                    </div>
                </div>
            </aside>
        </div>

        <section class="faq">
            <h3 class="section-heading faq-main-title">Your Trust Matters: Frequently Asked Questions</h3>

            <div class="faq-item">
                <div class="faq-question">Q1: How long does the maintenance process take?</div>
                <div class="faq-answer">Usually, routine maintenance takes between 2 to 4 hours.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Q2: Do you provide spare parts?</div>
                <div class="faq-answer">Yes, we provide original and warranted spare parts.</div>
            </div>
        </section>

        <footer class="features">
            <div class="feature-item">
                <i class="fas fa-bolt"></i>
                <div>
                    <strong>Fast Response</strong>
                    <small>We'll reach your location in no time.</small>
                </div>
            </div>
            <div class="feature-item">
                <i class="fas fa-certificate"></i>
                <div>
                    <strong>Certified Technicians</strong>
                    <small>All repairs are warranted.</small>
                </div>
            </div>
            <div class="feature-item">
                <i class="fas fa-headset"></i>
                <div>
                    <strong>24/7 Support</strong>
                    <small>We're here to help anytime.</small>
                </div>
            </div>
        </footer>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        item.addEventListener('click', () => {
            faqItems.forEach(i => { if (i !== item) i.classList.remove('active'); });
            item.classList.toggle('active');
        });
    });

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', (e) => {
            const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
            let isEmpty = false;
            
            inputs.forEach(input => {
                if (input.value.trim() === "") {
                    isEmpty = true;
                    input.style.borderColor = "red"; 
                } else {
                    input.style.borderColor = "#ddd"; 
                }
            });
            
            if (isEmpty) {
                e.preventDefault(); 
                alert("Please fill in all required fields before sending!");
            }
        });
    }
});
</script>

</body>
</html>