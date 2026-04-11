<?php
/**
 * Upload Content Page (Music & Video)
 */
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getCurrentUser();

if (!$user['registration_paid']) {
    setFlashMessage('error', 'Please complete your registration payment first.');
    redirect('pay_registration.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['content_file'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $genre = sanitize($_POST['genre'] ?? '');
    $contentType = $_POST['content_type']; // track or video
    
    if (empty($title) || empty($_FILES['content_file']['name'])) {
        $error = 'Title and file are required';
    } else {
        $file = $_FILES['content_file'];
        
        // Validate file type
        $allowedAudio = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav'];
        $allowedVideo = ['video/mp4', 'video/x-m4v'];
        
        if ($contentType === 'track') {
            if (!in_array($file['type'], $allowedAudio)) {
                $error = 'Only MP3, WAV audio files are allowed for tracks';
            }
        } elseif ($contentType === 'video') {
            if (!in_array($file['type'], $allowedVideo)) {
                $error = 'Only MP4 video files are allowed';
            }
        } else {
            $error = 'Invalid content type';
        }
        
        if (empty($error)) {
            $db = getDBConnection();
            $directory = $contentType === 'track' ? 'uploads/music' : 'uploads/video';
            
            // Upload file
            $uploadResult = uploadFile($file, $directory);
            
            if ($uploadResult['success']) {
                // Handle cover image/thumbnail upload
                $coverPath = null;
                if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                    $coverUpload = uploadFile($_FILES['cover_image'], 'uploads/covers');
                    if ($coverUpload['success']) {
                        $coverPath = $coverUpload['url'];
                    }
                }
                
                if ($contentType === 'track') {
                    $stmt = $db->prepare("INSERT INTO tracks (user_id, title, description, file_path, file_type, cover_image, genre, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
                    $stmt->execute([$user['id'], $title, $description, $uploadResult['url'], $uploadResult['type'], $coverPath, $genre]);
                } else {
                    $stmt = $db->prepare("INSERT INTO videos (user_id, title, description, file_path, file_type, thumbnail, is_published) VALUES (?, ?, ?, ?, ?, ?, 1)");
                    $stmt->execute([$user['id'], $title, $description, $uploadResult['url'], $uploadResult['type'], $coverPath]);
                }
                
                $success = ucfirst($contentType) . ' uploaded successfully!';
                setFlashMessage('success', $success);
                redirect('dashboard.php');
            } else {
                $error = $uploadResult['error'];
            }
        }
    }
}

$pageTitle = 'Upload Content - ' . SITE_NAME;
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
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
                <a href="upload.php" class="nav-item active"><i class="fas fa-upload"></i> Upload Content</a>
                <a href="my_tracks.php" class="nav-item"><i class="fas fa-music"></i> My Tracks</a>
                <a href="my_videos.php" class="nav-item"><i class="fas fa-video"></i> My Videos</a>
                <a href="wallet.php" class="nav-item"><i class="fas fa-wallet"></i> Wallet</a>
                <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Upload Content</h1>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php $flash = getFlashMessage(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>

            <div class="upload-container">
                <!-- Content Type Selector -->
                <div class="content-type-selector">
                    <button class="type-btn active" data-type="track">
                        <i class="fas fa-music"></i>
                        <span>Music Track</span>
                    </button>
                    <button class="type-btn" data-type="video">
                        <i class="fas fa-video"></i>
                        <span>Video (MP4)</span>
                    </button>
                </div>

                <!-- Upload Form -->
                <form method="POST" action="" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                    <input type="hidden" name="content_type" id="contentType" value="track">

                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" required placeholder="Enter content title">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" placeholder="Describe your content"></textarea>
                    </div>

                    <div class="form-group track-field">
                        <label for="genre">Genre</label>
                        <select id="genre" name="genre">
                            <option value="">Select Genre</option>
                            <option value="Afrobeats">Afrobeats</option>
                            <option value="Hip Hop">Hip Hop</option>
                            <option value="R&B">R&B</option>
                            <option value="Gospel">Gospel</option>
                            <option value="Highlife">Highlife</option>
                            <option value="Fuji">Fuji</option>
                            <option value="Jazz">Jazz</option>
                            <option value="Pop">Pop</option>
                            <option value="Reggae">Reggae</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Drag & Drop Upload Area -->
                    <div class="form-group">
                        <label><?= ucfirst($_POST['content_type'] ?? 'Track') ?> File *</label>
                        <div class="dropzone" id="dropzone">
                            <div class="dropzone-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <h3>Drag & Drop your file here</h3>
                                <p>or click to browse</p>
                                <p class="file-types">
                                    <span class="track-types">Supported: MP3, WAV (Max 50MB)</span>
                                    <span class="video-types" style="display:none;">Supported: MP4 (Max 50MB)</span>
                                </p>
                            </div>
                            <input type="file" id="contentFile" name="content_file" accept="audio/*,video/mp4" required>
                            <div class="file-info" id="fileInfo" style="display:none;">
                                <i class="fas fa-file"></i>
                                <span id="fileName"></span>
                                <span id="fileSize"></span>
                                <button type="button" class="remove-file" onclick="removeFile()"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Cover Image Upload -->
                    <div class="form-group">
                        <label for="cover_image">Cover Image / Thumbnail</label>
                        <div class="image-upload-preview" id="imagePreview">
                            <div class="upload-placeholder">
                                <i class="fas fa-image"></i>
                                <p>Click to upload cover image</p>
                            </div>
                            <img id="previewImg" src="" alt="Preview" style="display:none;">
                            <input type="file" id="coverImage" name="cover_image" accept="image/*" onchange="previewImage(this)">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-upload"></i> Upload Content
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>
    <script>
        // Content type switching
        const typeBtns = document.querySelectorAll('.type-btn');
        const contentTypeInput = document.getElementById('contentType');
        const trackFields = document.querySelectorAll('.track-field');
        const trackTypes = document.querySelector('.track-types');
        const videoTypes = document.querySelector('.video-types');
        const contentFile = document.getElementById('contentFile');

        typeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.dataset.type;
                
                typeBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                contentTypeInput.value = type;
                
                if (type === 'track') {
                    trackFields.forEach(f => f.style.display = 'block');
                    trackTypes.style.display = 'inline';
                    videoTypes.style.display = 'none';
                    contentFile.accept = 'audio/*';
                } else {
                    trackFields.forEach(f => f.style.display = 'none');
                    trackTypes.style.display = 'none';
                    videoTypes.style.display = 'inline';
                    contentFile.accept = 'video/mp4';
                }
            });
        });

        // Drag & Drop functionality
        const dropzone = document.getElementById('dropzone');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.remove('dragover'), false);
        });

        dropzone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                const file = files[0];
                const type = contentTypeInput.value;
                
                // Validate file type
                if (type === 'track' && !file.type.startsWith('audio/')) {
                    alert('Please upload an audio file for tracks');
                    return;
                }
                
                if (type === 'video' && !file.type.startsWith('video/')) {
                    alert('Please upload a video file');
                    return;
                }
                
                contentFile.files = files;
                showFileInfo(file);
            }
        }

        dropzone.addEventListener('click', () => {
            contentFile.click();
        });

        contentFile.addEventListener('change', function() {
            if (this.files.length > 0) {
                showFileInfo(this.files[0]);
            }
        });

        function showFileInfo(file) {
            document.querySelector('.dropzone-content').style.display = 'none';
            fileInfo.style.display = 'flex';
            fileName.textContent = file.name;
            fileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        }

        function removeFile() {
            contentFile.value = '';
            document.querySelector('.dropzone-content').style.display = 'block';
            fileInfo.style.display = 'none';
        }

        // Image preview
        function previewImage(input) {
            const preview = document.getElementById('previewImg');
            const placeholder = document.querySelector('.upload-placeholder');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
