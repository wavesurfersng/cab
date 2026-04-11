/**
 * Main JavaScript Functions
 */

// Record view for content
function recordView(contentType, contentId) {
    fetch('/music-platform/api/record_view.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            content_type: contentType,
            content_id: contentId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('View recorded successfully');
        }
    })
    .catch(error => console.error('Error recording view:', error));
}

// Toggle like on content
function toggleLike(contentType, contentId, element) {
    fetch('/music-platform/api/toggle_like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            content_type: contentType,
            content_id: contentId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const icon = element.querySelector('i');
            if (data.liked) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                element.style.color = '#ef4444';
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                element.style.color = '';
            }
        }
    })
    .catch(error => console.error('Error toggling like:', error));
}

// Format currency
function formatCurrency(amount) {
    return '₦' + parseFloat(amount).toFixed(2);
}

// Format views count
function formatViews(views) {
    if (views >= 1000000) {
        return (views / 1000000).toFixed(1) + 'M';
    } else if (views >= 1000) {
        return (views / 1000).toFixed(1) + 'K';
    }
    return views.toString();
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Add any initialization code here
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Lazy load images
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    });

    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}
