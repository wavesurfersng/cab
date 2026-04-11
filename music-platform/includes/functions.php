<?php
/**
 * Core Functions and Helpers
 */

session_start();

require_once __DIR__ . '/../config/database.php';

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ip = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    return $ip;
}

/**
 * Generate unique session ID for tracking
 */
function getSessionId() {
    if (!isset($_SESSION['tracking_id'])) {
        $_SESSION['tracking_id'] = generateRandomString(40);
    }
    return $_SESSION['tracking_id'];
}

/**
 * Format currency (NGN)
 */
function formatCurrency($amount) {
    return '₦' . number_format($amount, 2);
}

/**
 * Format views count
 */
function formatViews($views) {
    if ($views >= 1000000) {
        return round($views / 1000000, 1) . 'M';
    } elseif ($views >= 1000) {
        return round($views / 1000, 1) . 'K';
    }
    return $views;
}

/**
 * Format duration (seconds to mm:ss)
 */
function formatDuration($seconds) {
    $mins = floor($seconds / 60);
    $secs = $seconds % 60;
    return sprintf('%d:%02d', $mins, $secs);
}

/**
 * Upload file to configured storage
 */
function uploadFile($file, $directory = 'uploads') {
    $storageDriver = STORAGE_DRIVER;
    $allowedTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'video/mp4', 'image/jpeg', 'image/png', 'image/gif'];
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }
    
    $maxSize = 50 * 1024 * 1024; // 50MB
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File size exceeds limit (50MB)'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    
    switch ($storageDriver) {
        case 'local':
            return uploadToLocal($file, $directory, $filename);
        
        case 's3':
            return uploadToS3($file, $directory, $filename);
        
        case 'cloudinary':
            return uploadToCloudinary($file, $directory, $filename);
        
        default:
            return uploadToLocal($file, $directory, $filename);
    }
}

/**
 * Upload to local storage
 */
function uploadToLocal($file, $directory, $filename) {
    $uploadPath = __DIR__ . '/../assets/' . $directory . '/';
    
    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }
    
    $destination = $uploadPath . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $url = SITE_URL . '/assets/' . $directory . '/' . $filename;
        return ['success' => true, 'path' => $directory . '/' . $filename, 'url' => $url, 'type' => 'local'];
    }
    
    return ['success' => false, 'error' => 'Failed to upload file'];
}

/**
 * Upload to Amazon S3
 */
function uploadToS3($file, $directory, $filename) {
    // S3 upload implementation using AWS SDK
    // This is a simplified version - in production, use AWS SDK for PHP
    $key = $directory . '/' . $filename;
    
    // Placeholder for S3 upload logic
    // In production: Use Aws\S3\S3Client
    
    return uploadToLocal($file, $directory, $filename); // Fallback to local
}

/**
 * Upload to Cloudinary
 */
function uploadToCloudinary($file, $directory, $filename) {
    // Cloudinary upload implementation
    // This is a simplified version - in production, use Cloudinary PHP SDK
    $uploadUrl = "https://api.cloudinary.com/v1_1/" . CLOUDINARY_CLOUD_NAME . "/auto/upload";
    
    $postData = [
        'file' => new CURLFile($file['tmp_name']),
        'upload_preset' => 'music_platform',
        'folder' => $directory
    ];
    
    // Placeholder for Cloudinary upload logic
    // In production: Use \Cloudinary\Uploader::upload()
    
    return uploadToLocal($file, $directory, $filename); // Fallback to local
}

/**
 * Record a view and calculate earnings
 */
function recordView($contentType, $contentId, $userId) {
    $db = getDBConnection();
    $viewerIp = getClientIP();
    $sessionId = getSessionId();
    
    // Check if this view has already been counted recently (prevent fraud)
    $stmt = $db->prepare("SELECT id FROM views_log WHERE content_type = ? AND content_id = ? AND viewer_ip = ? AND viewed_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->execute([$contentType, $contentId, $viewerIp]);
    
    if ($stmt->fetch()) {
        // View already counted in the last hour
        return false;
    }
    
    // Record the view
    $stmt = $db->prepare("INSERT INTO views_log (user_id, content_type, content_id, viewer_ip, viewer_session) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $contentType, $contentId, $viewerIp, $sessionId]);
    
    // Update content views count
    $table = $contentType . 's';
    $stmt = $db->prepare("UPDATE {$table} SET views = views + 1 WHERE id = ?");
    $stmt->execute([$contentId]);
    
    // Update user total views
    $stmt = $db->prepare("UPDATE users SET total_views = total_views + 1 WHERE id = ?");
    $stmt->execute([$userId]);
    
    // Calculate and record earnings
    $earnings = RATE_PER_VIEW;
    
    $stmt = $db->prepare("INSERT INTO earnings_log (user_id, amount, content_type, content_id, views_count, description) VALUES (?, ?, ?, ?, 1, ?)");
    $description = "Earnings from 1 view on " . $contentType;
    $stmt->execute([$userId, $earnings, $contentType, $contentId, $description]);
    
    // Update user wallet and total earnings
    $stmt = $db->prepare("UPDATE users SET wallet_balance = wallet_balance + ?, total_earnings = total_earnings + ? WHERE id = ?");
    $stmt->execute([$earnings, $earnings, $userId]);
    
    // Record wallet transaction
    $stmt = $db->prepare("SELECT wallet_balance FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    $balanceAfter = $user['wallet_balance'];
    
    $reference = 'VIEW_' . $contentType . '_' . $contentId . '_' . time();
    $stmt = $db->prepare("INSERT INTO wallet_transactions (user_id, transaction_type, amount, balance_after, description, reference) VALUES (?, 'credit', ?, ?, ?, ?)");
    $stmt->execute([$userId, $earnings, $balanceAfter, $description, $reference]);
    
    return true;
}

/**
 * Toggle like on content
 */
function toggleLike($contentType, $contentId, $ipAddress) {
    $db = getDBConnection();
    
    // Check if already liked
    $stmt = $db->prepare("SELECT id FROM likes WHERE content_type = ? AND content_id = ? AND ip_address = ?");
    $stmt->execute([$contentType, $contentId, $ipAddress]);
    
    if ($stmt->fetch()) {
        // Unlike
        $stmt = $db->prepare("DELETE FROM likes WHERE content_type = ? AND content_id = ? AND ip_address = ?");
        $stmt->execute([$contentType, $contentId, $ipAddress]);
        
        $table = $contentType . 's';
        $stmt = $db->prepare("UPDATE {$table} SET likes = GREATEST(likes - 1, 0) WHERE id = ?");
        $stmt->execute([$contentId]);
        
        return false;
    } else {
        // Like
        $stmt = $db->prepare("INSERT INTO likes (content_type, content_id, ip_address) VALUES (?, ?, ?)");
        $stmt->execute([$contentType, $contentId, $ipAddress]);
        
        $table = $contentType . 's';
        $stmt = $db->prepare("UPDATE {$table} SET likes = likes + 1 WHERE id = ?");
        $stmt->execute([$contentId]);
        
        return true;
    }
}

/**
 * Add comment to content
 */
function addComment($contentType, $contentId, $commentText, $commenterName = null) {
    $db = getDBConnection();
    $userId = getCurrentUserId();
    $ipAddress = getClientIP();
    
    $commentText = sanitize($commentText);
    
    if (empty($commentText)) {
        return ['success' => false, 'error' => 'Comment cannot be empty'];
    }
    
    $stmt = $db->prepare("INSERT INTO comments (user_id, content_type, content_id, comment_text, commenter_name, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$userId, $contentType, $contentId, $commentText, $commenterName, $ipAddress])) {
        $commentId = $db->lastInsertId();
        
        $stmt = $db->prepare("SELECT * FROM comments WHERE id = ?");
        $stmt->execute([$commentId]);
        $comment = $stmt->fetch();
        
        return ['success' => true, 'comment' => $comment];
    }
    
    return ['success' => false, 'error' => 'Failed to add comment'];
}

/**
 * Get comments for content
 */
function getComments($contentType, $contentId, $limit = 50) {
    $db = getDBConnection();
    
    $stmt = $db->prepare("SELECT c.*, u.username, u.profile_image FROM comments c LEFT JOIN users u ON c.user_id = u.id WHERE c.content_type = ? AND c.content_id = ? ORDER BY c.created_at DESC LIMIT ?");
    $stmt->execute([$contentType, $contentId, $limit]);
    
    return $stmt->fetchAll();
}

/**
 * Get admin setting
 */
function getAdminSetting($key, $default = null) {
    $db = getDBConnection();
    
    $stmt = $db->prepare("SELECT setting_value FROM admin_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    
    return $result ? $result['setting_value'] : $default;
}

/**
 * Update admin setting
 */
function updateAdminSetting($key, $value) {
    $db = getDBConnection();
    
    $stmt = $db->prepare("INSERT INTO admin_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    return $stmt->execute([$key, $value, $value]);
}

/**
 * Initialize Flutterwave payment
 */
function initializeFlutterwavePayment($amount, $email, $txRef, $paymentType = 'registration') {
    $data = [
        'amount' => $amount,
        'currency' => 'NGN',
        'redirect_url' => SITE_URL . '/api/payment_callback.php',
        'customer' => [
            'email' => $email,
        ],
        'customizations' => [
            'title' => SITE_NAME . ' - ' . ucfirst($paymentType),
            'description' => 'Payment for ' . $paymentType,
            'logo' => SITE_URL . '/assets/images/logo.png',
        ],
        'tx_ref' => $txRef,
    ];
    
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.flutterwave.com/v3/payments",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . FLUTTERWAVE_SECRET_KEY,
            "Content-Type: application/json"
        ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
        return ['success' => false, 'error' => $err];
    }
    
    return json_decode($response, true);
}

/**
 * Verify Flutterwave payment
 */
function verifyFlutterwavePayment($txRef) {
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.flutterwave.com/v3/transactions/" . $txRef . "/verify",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . FLUTTERWAVE_SECRET_KEY,
            "Content-Type: application/json"
        ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
        return ['success' => false, 'error' => $err];
    }
    
    return json_decode($response, true);
}

/**
 * Flash message helper
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Time ago helper
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $timestamp);
    }
}
?>
