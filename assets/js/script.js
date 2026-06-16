document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');

    if (mobileBtn && navLinks) {
        mobileBtn.addEventListener('click', () => {
            navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
            navLinks.style.flexDirection = 'column';
            navLinks.style.position = 'absolute';
            navLinks.style.top = '80px';
            navLinks.style.left = '0';
            navLinks.style.width = '100%';
            navLinks.style.backgroundColor = '#fff';
            navLinks.style.padding = '1rem';
            navLinks.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
            navLinks.style.zIndex = '99';
        });
    }

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            });
        }, 5000);
    }
});

function toggleFavorite(event, button) {
    event.preventDefault();
    event.stopPropagation();
    
    const adId = button.getAttribute('data-id');
    
    fetch('/BODIMAK.LK/toggle_save.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ ad_id: adId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (data.action === 'saved') {
                button.classList.add('active');
                const textSpan = button.querySelector('.btn-text');
                if (textSpan) {
                    textSpan.textContent = 'Saved to Favorites';
                }
            } else {
                button.classList.remove('active');
                const textSpan = button.querySelector('.btn-text');
                if (textSpan) {
                    textSpan.textContent = 'Add to Favorites';
                }
                // If we are on the saved listings page, remove the card from DOM
                if (window.location.pathname.includes('saved.php')) {
                    const card = button.closest('.card') || button.closest('.listing-card');
                    if (card) {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';
                        card.style.transition = 'all 0.3s ease';
                        setTimeout(() => {
                            card.remove();
                            // Check if there are no cards left
                            const grid = document.querySelector('div[style*="grid"]');
                            if (grid && grid.querySelectorAll('.card, .listing-card').length === 0) {
                                location.reload();
                            }
                        }, 300);
                    }
                }
            }
        } else if (data.message === 'unauthorized') {
            alert('Please login to save favorites!');
            window.location.href = '/BODIMAK.LK/login.php';
        } else {
            alert(data.message || 'An error occurred.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

