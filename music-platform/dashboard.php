<?php
/**
 * User Dashboard
 */
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getCurrentUser();
$db = getDBConnection();

// Get user stats
$userId = $user['id'];

// Get user tracks
$stmt = $db->prepare("SELECT * FROM tracks WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$userTracks = $stmt->fetchAll();

// Get user videos
$stmt = $db->prepare("SELECT * FROM videos WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$userVideos = $stmt->fetchAll();

// Get recent earnings
$stmt = $db->prepare("SELECT * FROM earnings_log WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$userId]);
$recentEarnings = $stmt->fetchAll();

// Get wallet transactions
$stmt = $db->prepare("SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$userId]);
$walletTransactions = $stmt->fetchAll();

$pageTitle = 'Dashboard - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="dashboard-container">
        <aside class="dashboard-sidebar">
            <div class="sidebar-profile">
                <img src="<?= !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : SITE_URL . '/assets/images/default-avatar.jpg' ?>" alt="Profile" class="profile-avatar">
                <h3><?= htmlspecialchars($user['username']) ?></h3>
                <p><?= htmlspecialchars($user['email']) ?></p>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="upload.php" class="nav-item"><i class="fas fa-upload"></i> Upload Content</a>
                <a href="my_tracks.php" class="nav-item"><i class="fas fa-music"></i> My Tracks</a>
                <a href="my_videos.php" class="nav-item"><i class="fas fa-video"></i> My Videos</a>
                <a href="wallet.php" class="nav-item"><i class="fas fa-wallet"></i> Wallet</a>
                <a href="earnings.php" class="nav-item"><i class="fas fa-chart-line"></i> Earnings</a>
                <a href="profile.php" class="nav-item"><i class="fas fa-user"></i> My Profile</a>
                <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Dashboard</h1>
                <?php if (!$user['registration_paid']): ?>
                    <div class="alert alert-warning">
                        Please complete your registration payment to start uploading content.
                        <a href="pay_registration.php" class="btn btn-sm btn-primary">Pay Now</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-eye"></i></div>
                    <div class="stat-info">
                        <h3><?= number_format($user['total_views']) ?></h3>
                        <p>Total Views</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-music"></i></div>
                    <div class="stat-info">
                        <h3><?= count($userTracks) ?></h3>
                        <p>Tracks Uploaded</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-video"></i></div>
                    <div class="stat-info">
                        <h3><?= count($userVideos) ?></h3>
                        <p>Videos Uploaded</p>
                    </div>
                </div>
                <div class="stat-card highlight">
                    <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                    <div class="stat-info">
                        <h3><?= formatCurrency($user['wallet_balance']) ?></h3>
                        <p>Wallet Balance</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-coins"></i></div>
                    <div class="stat-info">
                        <h3><?= formatCurrency($user['total_earnings']) ?></h3>
                        <p>Total Earnings</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-calculator"></i></div>
                    <div class="stat-info">
                        <h3><?= formatCurrency(RATE_PER_VIEW) ?>/view</h3>
                        <p>Earning Rate</p>
                    </div>
                </div>
            </div>

            <!-- Recent Uploads -->
            <div class="dashboard-section">
                <h2>Recent Uploads</h2>
                <div class="content-tabs">
                    <button class="tab-btn active" data-tab="tracks">Tracks</button>
                    <button class="tab-btn" data-tab="videos">Videos</button>
                </div>

                <div id="tracks-tab" class="tab-content active">
                    <?php if (empty($userTracks)): ?>
                        <p class="no-content">No tracks uploaded yet. <a href="upload.php">Upload your first track</a></p>
                    <?php else: ?>
                        <div class="tracks-list">
                            <?php foreach (array_slice($userTracks, 0, 5) as $track): ?>
                                <div class="track-item">
                                    <img src="<?= !empty($track['cover_image']) ? htmlspecialchars($track['cover_image']) : SITE_URL . '/assets/images/default-cover.jpg' ?>" alt="<?= htmlspecialchars($track['title']) ?>">
                                    <div class="track-details">
                                        <h4><?= htmlspecialchars($track['title']) ?></h4>
                                        <p><?= formatDuration($track['duration'] ?? 0) ?> • <?= $track['genre'] ?? 'Unknown' ?></p>
                                    </div>
                                    <div class="track-stats">
                                        <span><i class="fas fa-eye"></i> <?= formatViews($track['views']) ?></span>
                                        <span><i class="fas fa-heart"></i> <?= formatViews($track['likes']) ?></span>
                                    </div>
                                    <div class="track-actions">
                                        <a href="track_detail.php?id=<?= $track['id'] ?>" class="btn btn-sm"><i class="fas fa-eye"></i></a>
                                        <a href="edit_track.php?id=<?= $track['id'] ?>" class="btn btn-sm"><i class="fas fa-edit"></i></a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="videos-tab" class="tab-content">
                    <?php if (empty($userVideos)): ?>
                        <p class="no-content">No videos uploaded yet. <a href="upload.php">Upload your first video</a></p>
                    <?php else: ?>
                        <div class="videos-list">
                            <?php foreach (array_slice($userVideos, 0, 5) as $video): ?>
                                <div class="video-item">
                                    <img src="<?= !empty($video['thumbnail']) ? htmlspecialchars($video['thumbnail']) : SITE_URL . '/assets/images/default-video.jpg' ?>" alt="<?= htmlspecialchars($video['title']) ?>">
                                    <div class="video-details">
                                        <h4><?= htmlspecialchars($video['title']) ?></h4>
                                        <p><?= formatDuration($video['duration'] ?? 0) ?></p>
                                    </div>
                                    <div class="video-stats">
                                        <span><i class="fas fa-eye"></i> <?= formatViews($video['views']) ?></span>
                                        <span><i class="fas fa-heart"></i> <?= formatViews($video['likes']) ?></span>
                                    </div>
                                    <div class="video-actions">
                                        <a href="video_detail.php?id=<?= $video['id'] ?>" class="btn btn-sm"><i class="fas fa-play"></i></a>
                                        <a href="edit_video.php?id=<?= $video['id'] ?>" class="btn btn-sm"><i class="fas fa-edit"></i></a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Earnings -->
            <div class="dashboard-section">
                <h2>Recent Earnings</h2>
                <?php if (empty($recentEarnings)): ?>
                    <p class="no-content">No earnings yet. Start uploading content to earn money!</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Content</th>
                                <th>Views</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentEarnings as $earning): ?>
                                <tr>
                                    <td><?= timeAgo($earning['created_at']) ?></td>
                                    <td><span class="badge badge-<?= $earning['content_type'] ?>"><?= ucfirst($earning['content_type']) ?></span></td>
                                    <td>ID: <?= $earning['content_id'] ?></td>
                                    <td><?= $earning['views_count'] ?></td>
                                    <td class="text-success">+<?= formatCurrency($earning['amount']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>
    <script>
        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabName = this.dataset.tab;
                
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                this.classList.add('active');
                document.getElementById(tabName + '-tab').classList.add('active');
            });
        });
    </script>
</body>
</html>
