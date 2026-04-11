<?php
/**
 * Database Configuration
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'music_platform');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Storage Settings (Admin can change these)
define('STORAGE_DRIVER', 'local'); // local, s3, cloudinary
define('AWS_KEY', '');
define('AWS_SECRET', '');
define('AWS_BUCKET', '');
define('AWS_REGION', 'us-east-1');
define('CLOUDINARY_CLOUD_NAME', '');
define('CLOUDINARY_API_KEY', '');
define('CLOUDINARY_API_SECRET', '');

// Flutterwave Settings
define('FLUTTERWAVE_PUBLIC_KEY', 'FLWPUBK_TEST-xxxxx');
define('FLUTTERWAVE_SECRET_KEY', 'FLWSECK_TEST-xxxxx');
define('FLUTTERWAVE_ENCRYPTION_KEY', 'FLWSECK_TEST-xxxxx');

// Earnings Configuration
define('EARNINGS_PER_VIEWS', 5000); // NGN
define('VIEWS_THRESHOLD', 50000);    // Views
define('RATE_PER_VIEW', EARNINGS_PER_VIEWS / VIEWS_THRESHOLD); // 0.10 NGN per view

// Site Settings
define('SITE_NAME', 'MusicPay Platform');
define('SITE_URL', 'http://localhost/music-platform');
define('REGISTRATION_FEE', 2000); // NGN

function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
?>
