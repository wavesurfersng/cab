<?php
/**
 * Footer Component
 */
?>
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>🎵 MusicPay Platform</h3>
                <p>Earn money from your music and videos. Get paid for every view!</p>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="<?= SITE_URL ?>">Home</a></li>
                    <li><a href="<?= SITE_URL ?>/register.php">Register</a></li>
                    <li><a href="<?= SITE_URL ?>/login.php">Login</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Earnings</h3>
                <ul>
                    <li><?= formatCurrency(EARNINGS_PER_VIEWS) ?> per <?= number_format(VIEWS_THRESHOLD) ?> views</li>
                    <li><?= formatCurrency(RATE_PER_VIEW) ?> per view</li>
                    <li>Instant wallet withdrawals</li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Contact</h3>
                <ul>
                    <li>Email: support@musicpay.com</li>
                    <li>Phone: +234 800 MUSIC PAY</li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom" style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
        </div>
    </div>
</footer>
