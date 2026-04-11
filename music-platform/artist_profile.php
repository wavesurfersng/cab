<?php
/**
 * Artist Public Profile
 */
require_once 'includes/functions.php';

$artistId = $_GET['id'] ?? null;

if (!$artistId) {
    redirect('index.php');
}

$db = getDBConnection();

// Get artist info
$stmt = $db->prepare("SELECT u.*, ap.bio, ap.profile_image, ap.cover_image, ap.social_links, ap.verified FROM users u LEFT JOIN artist_profiles ap ON u.id = ap.user_id WHERE u.id = ? AND u.is_artist = 1");
$stmt->execute([$artistId]);
$artist = $stmt->fetch();

if (!$artist) {
    redirect('index.php');
}

// Get artist tracks
$stmt = $db->prepare("SELECT * FROM tracks WHERE user_id = ? AND is_published = 1 ORDER BY created_at DESC");
$stmt->execute([$artistId]);
$artistTracks = $stmt->fetchAll();

// Get artist videos
$stmt = $db->prepare("SELECT * FROM videos WHERE user_id = ? AND is_published = 1 ORDER BY created_at DESC");
$stmt->execute([$artistId]);
$artistVideos = $stmt->fetchAll();

// Get total stats
$totalViews = $artist['total_views'] ?? 0;
$totalTracks = count($artistTracks);
$totalVideos = count($artistVideos);

$pageTitle = htmlspecialchars($artist['username']) . ' - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Artist Header -->
    <div class="artist-header" style="<?= !empty($artist['cover_image']) ? 'background-image: url(' . htmlspecialchars($artist['cover_image']) . ')' : '' ?>">
        <div class="artist-header-overlay"></div>
        <div class="container">
            <div class="artist-info">
                <img src="<?= !empty($artist['profile_image']) ? htmlspecialchars($artist['profile_image']) : SITE_URL . '/assets/images/default-avatar.jpg' ?>" alt="<?= htmlspecialchars($artist['username']) ?>" class="artist-profile-img">
                <div class="artist-details">
                    <h1>
                        <?= htmlspecialchars($artist['username']) ?>
                        <?php if ($artist['verified']): ?>
                            <i class="fas fa-check-circle verified-badge"></i>
                        <?php endif; ?>
                    </h1>
                    <?php if ($artist['bio']): ?>
                        <p class="artist-bio"><?= nl2br(htmlspecialchars($artist['bio'])) ?></p>
                    <?php endif; ?>
                    <div class="artist-stats-bar">
                        <span><i class="fas fa-music"></i> <?= $totalTracks ?> Tracks</span>
                        <span><i class="fas fa-video"></i> <?= $totalVideos ?> Videos</span>
                        <span><i class="fas fa-eye"></i> <?= formatViews($totalViews) ?> Total Views</span>
                    </div>
                    <?php if ($artist['social_links']): ?>
                        <div class="artist-social">
                            <?php 
                            $socials = json_decode($artist['social_links'], true);
                            if ($socials):
                                foreach ($socials as $platform => $url):
                            ?>
                                <a href="<?= htmlspecialchars($url) ?>" target="_blank"><i class="fab fa-<?= strtolower($platform) ?>"></i></a>
                            <?php 
                                endforeach;
                            endif;
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Content Tabs -->
        <div class="content-tabs artist-tabs">
            <button class="tab-btn active" data-tab="all">All</button>
            <button class="tab-btn" data-tab="tracks">Tracks</button>
            <button class="tab-btn" data-tab="videos">Videos</button>
        </div>

        <!-- All Content -->
        <div id="all-tab" class="tab-content active">
            <h2>All Content</h2>
            <div class="content-grid">
                <?php 
                $allContent = [];
                foreach ($artistTracks as $track) {
                    $allContent[] = ['type' => 'track', ...$track];
                }
                foreach ($artistVideos as $video) {
                    $allContent[] = ['type' => 'video', ...$video];
                }
                usort($allContent, function($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
                
                if (empty($allContent)):
                ?>
                    <p class="no-content">No content available yet.</p>
                <?php else: ?>
                    <?php foreach ($allContent as $item): ?>
                        <div class="content-item">
                            <div class="content-badge"><?= ucfirst($item['type']) ?></div>
                            <img src="<?= !empty($item['cover_image']) ? htmlspecialchars($item['cover_image']) : (!empty($item['thumbnail']) ? htmlspecialchars($item['thumbnail']) : SITE_URL . '/assets/images/default-thumb.jpg') ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                            <div class="content-overlay">
                                <button class="play-btn" data-type="<?= $item['type'] ?>" data-id="<?= $item['id'] ?>">
                                    <i class="fas fa-play"></i>
                                </button>
                            </div>
                            <div class="content-info">
                                <h4><?= htmlspecialchars($item['title']) ?></h4>
                                <div class="content-stats">
                                    <span><i class="fas fa-eye"></i> <?= formatViews($item['views']) ?></span>
                                    <span><i class="fas fa-heart"></i> <?= formatViews($item['likes']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tracks Tab -->
        <div id="tracks-tab" class="tab-content">
            <h2>Tracks</h2>
            <div class="tracks-list">
                <?php if (empty($artistTracks)): ?>
                    <p class="no-content">No tracks uploaded yet.</p>
                <?php else: ?>
                    <?php foreach ($artistTracks as $track): ?>
                        <div class="track-item">
                            <img src="<?= !empty($track['cover_image']) ? htmlspecialchars($track['cover_image']) : SITE_URL . '/assets/images/default-cover.jpg' ?>" alt="<?= htmlspecialchars($track['title']) ?>">
                            <div class="track-details">
                                <h4><?= htmlspecialchars($track['title']) ?></h4>
                                <?php if ($track['description']): ?>
                                    <p><?= htmlspecialchars(substr($track['description'], 0, 100)) ?>...</p>
                                <?php endif; ?>
                            </div>
                            <div class="track-stats">
                                <span><i class="fas fa-eye"></i> <?= formatViews($track['views']) ?></span>
                                <span><i class="fas fa-heart"></i> <?= formatViews($track['likes']) ?></span>
                            </div>
                            <button class="btn btn-sm play-track-btn" data-id="<?= $track['id'] ?>">
                                <i class="fas fa-play"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Videos Tab -->
        <div id="videos-tab" class="tab-content">
            <h2>Videos</h2>
            <div class="videos-grid">
                <?php if (empty($artistVideos)): ?>
                    <p class="no-content">No videos uploaded yet.</p>
                <?php else: ?>
                    <?php foreach ($artistVideos as $video): ?>
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
                                <div class="video-stats">
                                    <span><i class="fas fa-eye"></i> <?= formatViews($video['views']) ?></span>
                                    <span><i class="fas fa-heart"></i> <?= formatViews($video['likes']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="comments-section">
            <h2>Comments</h2>
            
            <!-- Comment Form -->
            <form class="comment-form" id="commentForm">
                <div class="form-group">
                    <input type="text" id="commenterName" placeholder="Your Name (optional)" class="form-control">
                </div>
                <div class="form-group">
                    <textarea id="commentText" placeholder="Write a comment..." rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Post Comment</button>
            </form>

            <!-- Comments List -->
            <div id="commentsList" class="comments-list">
                <!-- Comments will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Audio Player -->
    <div id="audio-player" class="audio-player hidden">
        <div class="player-container">
            <div class="player-info">
                <span id="player-title">Select a track</span>
                <span id="player-artist"><?= htmlspecialchars($artist['username']) ?></span>
            </div>
            <audio id="main-audio" controls>
                <source src="" type="audio/mpeg">
            </audio>
            <button class="close-player" onclick="closePlayer()"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/player.js"></script>
    <script>
        // Load comments
        function loadComments() {
            fetch('<?= SITE_URL ?>/api/get_comments.php?content_type=track&content_id=0&artist_id=<?= $artistId ?>')
                .then(response => response.json())
                .then(data => {
                    const commentsList = document.getElementById('commentsList');
                    if (data.comments && data.comments.length > 0) {
                        commentsList.innerHTML = data.comments.map(comment => `
                            <div class="comment-item">
                                <div class="comment-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="comment-content">
                                    <div class="comment-header">
                                        <strong>${comment.commenter_name || 'Anonymous'}</strong>
                                        <span class="comment-time">${comment.created_at}</span>
                                    </div>
                                    <p>${comment.comment_text}</p>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        commentsList.innerHTML = '<p class="no-comments">No comments yet. Be the first to comment!</p>';
                    }
                });
        }

        // Post comment
        document.getElementById('commentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('commenterName').value;
            const text = document.getElementById('commentText').value;
            
            fetch('<?= SITE_URL ?>/api/post_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    artist_id: <?= $artistId ?>,
                    commenter_name: name,
                    comment_text: text
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('commentText').value = '';
                    loadComments();
                } else {
                    alert(data.error || 'Failed to post comment');
                }
            });
        });

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

        // Load comments on page load
        loadComments();
    </script>
</body>
</html>
