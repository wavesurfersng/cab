# MusicPay Platform - Core PHP Music Streaming & Earnings Platform

A complete music platform built with Core PHP and MySQL where artists get paid for views.

## Features

### 💰 Earnings System
- **NGN 5,000** for every **50,000 views** (₦0.10 per view)
- Automatic view tracking with fraud prevention
- Real-time wallet balance updates
- Complete earnings log and transaction history

### 🎵 Content Upload
- Music tracks (MP3, WAV formats)
- Video uploads (MP4 format)
- Drag & drop uploader
- Configurable storage: Local, Amazon S3, or Cloudinary
- Cover images and thumbnails support

### 💳 Payment Integration
- Flutterwave payment gateway
- Registration fee payment (₦2,000)
- Secure payment verification
- Automatic account activation

### 👤 User Features
- Artist registration and profiles
- Public artist pages
- Dashboard with analytics
- Wallet management
- Track and video management

### 🎨 Social Features
- Anonymous commenting on artist profiles
- Like system for tracks and videos
- General feed for trending content
- Artist verification badges

### 🔐 Security
- Password hashing with bcrypt
- SQL injection prevention (PDO prepared statements)
- XSS protection
- Session-based authentication
- View fraud prevention (IP + session tracking)

## Installation

### 1. Database Setup
```sql
mysql -u root -p < database/schema.sql
```

### 2. Configuration
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'music_platform');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');

// Storage settings
define('STORAGE_DRIVER', 'local'); // local, s3, cloudinary

// Flutterwave keys
define('FLUTTERWAVE_PUBLIC_KEY', 'your_public_key');
define('FLUTTERWAVE_SECRET_KEY', 'your_secret_key');

// Site settings
define('SITE_URL', 'http://localhost/music-platform');
```

### 3. Directory Permissions
```bash
chmod -R 777 assets/uploads/
```

### 4. Access the Platform
Navigate to `http://localhost/music-platform`

## File Structure

```
music-platform/
├── config/
│   └── database.php          # Configuration file
├── includes/
│   ├── functions.php         # Core functions
│   ├── header.php            # Header component
│   └── footer.php            # Footer component
├── api/
│   ├── record_view.php       # View tracking API
│   └── payment_callback.php  # Payment webhook
├── assets/
│   ├── css/
│   │   └── style.css         # Stylesheet
│   ├── js/
│   │   ├── main.js           # Main JavaScript
│   │   └── player.js         # Audio/Video player
│   └── uploads/              # User uploads
├── database/
│   └── schema.sql            # Database schema
├── index.php                 # Homepage
├── register.php              # Registration page
├── login.php                 # Login page
├── dashboard.php             # User dashboard
├── upload.php                # Upload page
├── artist_profile.php        # Public artist profile
└── logout.php                # Logout handler
```

## Database Tables

- `users` - User accounts
- `artist_profiles` - Artist information
- `tracks` - Music tracks
- `videos` - Video content
- `views_log` - View tracking
- `earnings_log` - Earnings records
- `wallet_transactions` - Wallet history
- `likes` - Content likes
- `comments` - User comments
- `payments` - Payment records
- `admin_settings` - Platform settings

## Key Features Explained

### View Tracking & Earnings
Every time a user plays a track or video:
1. View is logged with IP and session ID
2. Duplicate views within 1 hour are prevented
3. ₦0.10 is added to artist's wallet
4. Transaction is recorded in wallet_transactions
5. Total views and earnings are updated

### Storage Options
The platform supports three storage drivers:
- **Local**: Files stored in `assets/uploads/`
- **Amazon S3**: Cloud storage (requires AWS SDK)
- **Cloudinary**: CDN storage (requires Cloudinary SDK)

### Payment Flow
1. User registers and fills form
2. System creates pending user account
3. Flutterwave payment is initialized
4. User completes payment on Flutterwave
5. Callback verifies payment
6. Account is activated automatically

## Customization

### Change Earnings Rate
Edit `config/database.php`:
```php
define('EARNINGS_PER_VIEWS', 5000); // Amount in NGN
define('VIEWS_THRESHOLD', 50000);    // Number of views
```

### Change Registration Fee
```php
define('REGISTRATION_FEE', 2000); // Amount in NGN
```

### Enable Different Storage
```php
define('STORAGE_DRIVER', 's3'); // or 'cloudinary'
```

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- cURL extension
- JSON extension

## Security Notes

1. Change all default keys in production
2. Use HTTPS in production
3. Set proper file permissions
4. Regular database backups
5. Keep Flutterwave keys secure

## License

MIT License - Free to use and modify

## Support

For issues and questions, contact support@musicpay.com
