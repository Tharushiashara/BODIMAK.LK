// Custom Toast Notification System
function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);//error ekak yatin ekak enwa nan meke thamai balanna wenne
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    let iconSvg = '';
    if (type === 'success') {
        iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
    } else if (type === 'error') {
        iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
    } else if (type === 'warning') {
        iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;
    } else {
        iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
    }

    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-icon">${iconSvg}</span>
            <span class="toast-message">${message}</span>
        </div>
        <button class="toast-close" onclick="this.parentElement.classList.remove('show'); setTimeout(() => this.parentElement.remove(), 300);">&times;</button>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    setTimeout(() => {
        if (toast.parentNode) {
            toast.classList.add('hide');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }
    }, 4000);
}

// Override default window.alert
window.alert = function (message) {
    let type = 'info';
    const lowerMessage = message.toLowerCase();
    if (lowerMessage.includes('error') || lowerMessage.includes('fail') || lowerMessage.includes('occurred')) {
        type = 'error';
    } else if (lowerMessage.includes('success') || lowerMessage.includes('thank') || lowerMessage.includes('saved')) {
        type = 'success';
    } else if (lowerMessage.includes('please') || lowerMessage.includes('login') || lowerMessage.includes('warn')) {
        type = 'warning';
    }
    showToast(message, type);
};

//button click krama pasubime toggle_save.php wetha Request yawla 
// labena parathicahara anuwa bottame penuma (red color) change
document.addEventListener('DOMContentLoaded', function () {
    // Theme toggle functionality
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    }

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
            navLinks.style.backgroundColor = 'var(--white)';
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
                showToast('Please login to save favorites!', 'warning');//error message save listing dapu kenata login nathi nam
                setTimeout(() => {
                    window.location.href = '/BODIMAK.LK/login.php';
                }, 1500);
            } else {
                showToast(data.message || 'An error occurred.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
        });
}

