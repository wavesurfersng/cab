<?php
/**
 * API: Payment Callback from Flutterwave
 */
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Get transaction reference from query string or POST
$txRef = $_GET['transaction_id'] ?? $_POST['tx_ref'] ?? '';

if (empty($txRef)) {
    redirect('index.php');
    exit;
}

$db = getDBConnection();

// Verify payment with Flutterwave
$verification = verifyFlutterwavePayment($txRef);

if (isset($verification['status']) && $verification['status'] === 'success') {
    $paymentData = $verification['data'];
    
    if ($paymentData['status'] === 'completed' || $paymentData['status'] === 'successful') {
        // Get payment record
        $stmt = $db->prepare("SELECT * FROM payments WHERE transaction_ref = ?");
        $stmt->execute([$txRef]);
        $payment = $stmt->fetch();
        
        if ($payment && $payment['status'] === 'pending') {
            $userId = $payment['user_id'];
            
            // Update payment status
            $stmt = $db->prepare("UPDATE payments SET status = 'completed', payment_data = ? WHERE transaction_ref = ?");
            $paymentJson = json_encode($paymentData);
            $stmt->execute([$paymentJson, $txRef]);
            
            // If registration payment, activate user account
            if ($payment['payment_type'] === 'registration') {
                $stmt = $db->prepare("UPDATE users SET registration_paid = 1 WHERE id = ?");
                $stmt->execute([$userId]);
                
                // Create artist profile
                $stmt = $db->prepare("INSERT INTO artist_profiles (user_id) VALUES (?)");
                $stmt->execute([$userId]);
                
                setFlashMessage('success', 'Registration completed successfully! You can now upload content.');
            }
            
            redirect('dashboard.php');
            exit;
        } elseif ($payment && $payment['status'] === 'completed') {
            setFlashMessage('info', 'Payment already processed.');
            redirect('dashboard.php');
            exit;
        }
    }
}

// Payment failed or verification failed
setFlashMessage('error', 'Payment verification failed. Please contact support.');
redirect('dashboard.php');
?>
