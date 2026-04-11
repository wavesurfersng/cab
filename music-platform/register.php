<?php
/**
 * User Registration with Flutterwave Payment
 */
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $db = getDBConnection();
        
        // Check if username or email exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = 'Username or email already exists';
        } else {
            // Create pending user
            $hashedPassword = hashPassword($password);
            $txRef = 'TXN_' . time() . '_' . generateRandomString(10);
            
            $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, phone, is_artist) VALUES (?, ?, ?, ?, ?, 1)");
            
            if ($stmt->execute([$username, $email, $hashedPassword, $full_name, $phone])) {
                $userId = $db->lastInsertId();
                
                // Store txRef for this user
                $_SESSION['pending_user_id'] = $userId;
                $_SESSION['pending_tx_ref'] = $txRef;
                
                // Initialize Flutterwave payment
                $paymentData = initializeFlutterwavePayment(
                    REGISTRATION_FEE,
                    $email,
                    $txRef,
                    'registration'
                );
                
                if (isset($paymentData['status']) && $paymentData['status'] === 'success') {
                    // Save payment record
                    $stmt = $db->prepare("INSERT INTO payments (user_id, transaction_ref, amount, payment_type, status, payment_data) VALUES (?, ?, ?, 'registration', 'pending', ?)");
                    $paymentJson = json_encode($paymentData);
                    $stmt->execute([$userId, $txRef, REGISTRATION_FEE, $paymentJson]);
                    
                    // Redirect to Flutterwave
                    header('Location: ' . $paymentData['data']['link']);
                    exit();
                } else {
                    $error = 'Payment initialization failed. Please try again.';
                }
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <?php include 'includes/header.php'; ?>

    <div class="auth-container">
        <div class="auth-card">
            <h2>Create Your Account</h2>
            <p class="auth-subtitle">Join <?= SITE_NAME ?> and start earning from your music</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="+234..." required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" required>
                        I agree to pay the registration fee of <?= formatCurrency(REGISTRATION_FEE) ?>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i> Register & Pay <?= formatCurrency(REGISTRATION_FEE) ?>
                </button>
            </form>

            <p class="auth-footer">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
