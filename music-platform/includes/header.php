<?php
/**
 * Header Component
 */
?>
<header class="header">
    <div class="header-container">
        <a href="<?= SITE_URL ?>" class="logo">🎵 MusicPay</a>
        
        <?php if (isLoggedIn()): ?>
            <nav>
                <ul class="nav-menu">
                    <li><a href="<?= SITE_URL ?>/dashboard.php">Dashboard</a></li>
                    <li><a href="<?= SITE_URL ?>/upload.php">Upload</a></li>
                    <li><a href="<?= SITE_URL ?>/wallet.php">Wallet</a></li>
                    <li><a href="<?= SITE_URL ?>/logout.php">Logout</a></li>
                </ul>
            </nav>
        <?php else: ?>
            <nav>
                <ul class="nav-menu">
                    <li><a href="<?= SITE_URL ?>">Home</a></li>
                    <li><a href="<?= SITE_URL ?>/login.php">Login</a></li>
                    <li><a href="<?= SITE_URL ?>/register.php" class="btn btn-primary">Get Started</a></li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</header>
