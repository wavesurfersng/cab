/**
 * Audio/Video Player Functions
 */

let currentAudio = null;
let currentPlayer = null;

// Initialize play buttons
document.addEventListener('DOMContentLoaded', function() {
    // Track play buttons
    document.querySelectorAll('.play-btn[data-type="track"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const trackId = this.dataset.id;
            playTrack(trackId);
        });
    });

    // Video play buttons
    document.querySelectorAll('.play-btn[data-type="video"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const videoId = this.dataset.id;
            playVideo(videoId);
        });
    });

    // Play track buttons in lists
    document.querySelectorAll('.play-track-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const trackId = this.dataset.id;
            playTrack(trackId);
        });
    });
});

// Play a track
function playTrack(trackId) {
    fetch(`/music-platform/api/get_track.php?id=${trackId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const track = data.track;
                
                // Show player
                const player = document.getElementById('audio-player');
                player.classList.remove('hidden');
                
                // Update player info
                document.getElementById('player-title').textContent = track.title;
                document.getElementById('player-artist').textContent = track.artist || 'Unknown Artist';
                
                // Set audio source
                const audio = document.getElementById('main-audio');
                audio.src = track.file_path;
                audio.play();
                
                // Record view
                recordView('track', trackId);
                
                currentAudio = audio;
            } else {
                alert('Failed to load track');
            }
        })
        .catch(error => {
            console.error('Error loading track:', error);
            alert('Failed to load track');
        });
}

// Play a video
function playVideo(videoId) {
    fetch(`/music-platform/api/get_video.php?id=${videoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const video = data.video;
                
                // Create or show video player modal
                let modal = document.getElementById('video-modal');
                if (!modal) {
                    modal = document.createElement('div');
                    modal.id = 'video-modal';
                    modal.className = 'video-modal';
                    modal.innerHTML = `
                        <div class="video-modal-content">
                            <button class="close-modal" onclick="closeVideoPlayer()">&times;</button>
                            <video id="main-video" controls style="width: 100%; max-height: 80vh;">
                                <source src="" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div class="video-modal-info">
                                <h2 id="video-title"></h2>
                                <p id="video-description"></p>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);
                }
                
                modal.style.display = 'flex';
                
                const videoElement = document.getElementById('main-video');
                videoElement.src = video.file_path;
                videoElement.play();
                
                document.getElementById('video-title').textContent = video.title;
                document.getElementById('video-description').textContent = video.description || '';
                
                // Record view
                recordView('video', videoId);
                
                currentPlayer = videoElement;
            } else {
                alert('Failed to load video');
            }
        })
        .catch(error => {
            console.error('Error loading video:', error);
            alert('Failed to load video');
        });
}

// Close audio player
function closePlayer() {
    const player = document.getElementById('audio-player');
    player.classList.add('hidden');
    
    const audio = document.getElementById('main-audio');
    audio.pause();
    audio.src = '';
    
    currentAudio = null;
}

// Close video player
function closeVideoPlayer() {
    const modal = document.getElementById('video-modal');
    if (modal) {
        const video = document.getElementById('main-video');
        video.pause();
        video.src = '';
        modal.style.display = 'none';
    }
    currentPlayer = null;
}

// Handle audio ended
if (document.getElementById('main-audio')) {
    document.getElementById('main-audio').addEventListener('ended', function() {
        // Could implement auto-play next track here
    });
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Space to play/pause
    if (e.code === 'Space' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
        e.preventDefault();
        if (currentAudio && !currentAudio.paused) {
            currentAudio.pause();
        } else if (currentAudio) {
            currentAudio.play();
        }
        if (currentPlayer && !currentPlayer.paused) {
            currentPlayer.pause();
        } else if (currentPlayer) {
            currentPlayer.play();
        }
    }
    
    // Escape to close players
    if (e.code === 'Escape') {
        closePlayer();
        closeVideoPlayer();
    }
});
