<?php
/**
 * Main Homepage
 */
require_once 'includes/functions.php';

$db = getDBConnection();

// Get featured tracks
$stmt = $db->query("SELECT t.*, u.username, u.profile_image FROM tracks t 
                    JOIN users u ON t.user_id = u.id 
                    WHERE t.is_published = 1 
                    ORDER BY t.views DESC LIMIT 10");
$featuredTracks = $stmt->fetchAll();

// Get latest videos
$stmt = $db->query("SELECT v.*, u.username, u.profile_image FROM videos v 
                    JOIN users u ON v.user_id = u.id 
                    WHERE v.is_published = 1 
                    ORDER BY v.created_at DESC LIMIT 8");
$latestVideos = $stmt->fetchAll();

// Get top artists
$stmt = $db->query("SELECT u.*, ap.bio, ap.profile_image, COUNT(t.id) as track_count, SUM(t.views) as total_views 
                    FROM users u 
                    LEFT JOIN artist_profiles ap ON u.id = ap.user_id 
                    LEFT JOIN tracks t ON u.id = t.user_id 
                    WHERE u.is_artist = 1 AND u.registration_paid = 1 
                    GROUP BY u.id 
                    ORDER BY total_views DESC LIMIT 6");
$topArtists = $stmt->fetchAll();

// Get trending content (general feed)
$stmt = $db->query("(SELECT 'track' as type, t.id, t.title, t.description, t.cover_image, t.views, t.likes, t.created_at, u.username, u.profile_image 
                    FROM tracks t JOIN users u ON t.user_id = u.id WHERE t.is_published = 1)
                    UNION ALL
                    (SELECT 'video' as type, v.id, v.title, v.description, v.thumbnail, v.views, v.likes, v.created_at, u.username, u.profile_image 
                    FROM videos v JOIN users u ON v.user_id = u.id WHERE v.is_published = 1)
                    ORDER BY created_at DESC LIMIT 20");
$trendingContent = $stmt->fetchAll();

$pageTitle = 'Home - ' . SITE_NAME;
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Discover & Earn with Music</h1>
            <p>Upload your music and videos, get paid for every view!</p>
            <p class="earnings-info">Earn <?= formatCurrency(EARNINGS_PER_VIEWS) ?> for every <?= number_format(VIEWS_THRESHOLD) ?> views</p>
            <div class="hero-buttons">
                <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                    <a href="login.php" class="btn btn-secondary">Login</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                    <a href="upload.php" class="btn btn-secondary">Upload Content</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Featured Tracks -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Featured Tracks</h2>
            <div class="tracks-grid">
                <?php foreach ($featuredTracks as $track): ?>
                    <div class="track-card">
                        <div class="track-image">
                            <img src="<?= !empty($track['cover_image']) ? htmlspecialchars($track['cover_image']) : SITE_URL . '/assets/images/default-cover.jpg' ?>" alt="<?= htmlspecialchars($track['title']) ?>">
                            <div class="track-overlay">
                                <button class="play-btn" data-type="track" data-id="<?= $track['id'] ?>">
                                    <i class="fas fa-play"></i>
                                </button>
                            </div>
                        </div>
                        <div class="track-info">
                            <h3><?= htmlspecialchars($track['title']) ?></h3>
                            <p class="artist"><?= htmlspecialchars($track['username']) ?></p>
                            <div class="track-stats">
                                <span><i class="fas fa-eye"></i> <?= formatViews($track['views']) ?></span>
                                <span><i class="fas fa-heart"></i> <?= formatViews($track['likes']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Latest Videos -->
    <section class="section bg-light">
        <div class="container">
            <h2 class="section-title">Latest Videos</h2>
            <div class="videos-grid">
                <?php foreach ($latestVideos as $video): ?>
                    <div class="video-card">
                        <div class="video-thumbnail">
                            <img src="<?= !empty($video['thumbnail']) ? htmlspecialchars($video['thumbnail']) : SITE_URL . '/assets/images/default-video.jpg' ?>" alt="<?= htmlspecialchars($video['title']) ?>">
                            <div class="video-overlay">
                                <button class="play-btn" data-type="video" data-id="<?= $video['id'] ?>">
                                    <i class="fas fa-play"></i>
                                </button>
                            </div>
                            <?php if ($video['duration']): ?>
                                <span class="duration"><?= formatDuration($video['duration']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="video-info">
                            <h3><?= htmlspecialchars($video['title']) ?></h3>
                            <p class="artist"><?= htmlspecialchars($video['username']) ?></p>
                            <div class="video-stats">
                                <span><i class="fas fa-eye"></i> <?= formatViews($video['views']) ?></span>
                                <span><i class="fas fa-heart"></i> <?= formatViews($video['likes']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Top Artists -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Top Artists</h2>
            <div class="artists-grid">
                <?php foreach ($topArtists as $artist): ?>
                    <div class="artist-card">
                        <img src="<?= !empty($artist['profile_image']) ? htmlspecialchars($artist['profile_image']) : SITE_URL . '/assets/images/default-avatar.jpg' ?>" alt="<?= htmlspecialchars($artist['username']) ?>" class="artist-avatar">
                        <h3><?= htmlspecialchars($artist['username']) ?></h3>
                        <p class="artist-bio"><?= !empty($artist['bio']) ? htmlspecialchars(substr($artist['bio'], 0, 80)) . '...' : 'Artist' ?></p>
                        <div class="artist-stats">
                            <span><?= $artist['track_count'] ?> Tracks</span>
                            <span><?= formatViews($artist['total_views']) ?> Views</span>
                        </div>
                        <a href="artist_profile.php?id=<?= $artist['id'] ?>" class="btn btn-sm btn-primary">View Profile</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- General Feed -->
    <section class="section bg-light">
        <div class="container">
            <h2 class="section-title">Trending Now</h2>
            <div class="feed-grid">
                <?php foreach ($trendingContent as $item): ?>
                    <div class="feed-item">
                        <div class="feed-badge"><?= ucfirst($item['type']) ?></div>
                        <img src="<?= !empty($item['cover_image']) ? htmlspecialchars($item['cover_image']) : SITE_URL . '/assets/images/default-thumb.jpg' ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                        <div class="feed-content">
                            <h4><?= htmlspecialchars($item['title']) ?></h4>
                            <p class="feed-artist"><?= htmlspecialchars($item['username']) ?></p>
                            <div class="feed-stats">
                                <span><i class="fas fa-eye"></i> <?= formatViews($item['views']) ?></span>
                                <span><i class="fas fa-heart"></i> <?= formatViews($item['likes']) ?></span>
                                <span><?= timeAgo($item['created_at']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="section how-it-works">
        <div class="container">
            <h2 class="section-title">How It Works</h2>
            <div class="steps-grid">
                <div class="step">
                    <div class="step-icon"><i class="fas fa-user-plus"></i></div>
                    <h3>1. Register & Pay</h3>
                    <p>Create an account and pay the registration fee of <?= formatCurrency(REGISTRATION_FEE) ?></p>
                </div>
                <div class="step">
                    <div class="step-icon"><i class="fas fa-upload"></i></div>
                    <h3>2. Upload Content</h3>
                    <p>Upload your music tracks or MP4 videos with drag & drop</p>
                </div>
                <div class="step">
                    <div class="step-icon"><i class="fas fa-eye"></i></div>
                    <h3>3. Get Views</h3>
                    <p>Share your content and get views from listeners worldwide</p>
                </div>
                <div class="step">
                    <div class="step-icon"><i class="fas fa-wallet"></i></div>
                    <h3>4. Earn Money</h3>
                    <p>Earn <?= formatCurrency(EARNINGS_PER_VIEWS) ?> for every <?= number_format(VIEWS_THRESHOLD) ?> views directly to your wallet</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- Audio Player -->
    <div id="audio-player" class="audio-player hidden">
        <div class="player-container">
            <div class="player-info">
                <span id="player-title">Select a track</span>
                <span id="player-artist"></span>
            </div>
            <audio id="main-audio" controls>
                <source src="" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
            <button class="close-player" onclick="closePlayer()"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/player.js"></script>
</body>
</html>
