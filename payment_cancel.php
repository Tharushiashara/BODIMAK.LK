<?php
session_start();
include 'includes/header.php';
?>

<div class="container" style="margin-top: 4rem; max-width: 600px; margin-left: auto; margin-right: auto; text-align: center; padding-bottom: 4rem;">
    <div class="card" style="padding: 3rem 2rem;">
        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
            <svg width="40" height="40" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h1 style="color: #92400e; margin-bottom: 1rem;">Payment Cancelled</h1>
        <p style="color: var(--text-muted); font-size: 1.05rem; line-height: 1.7;">
            You cancelled the payment. Your advertisement has <strong>not been published</strong>. You can go back to your ads and try again when you're ready.
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem; flex-wrap: wrap;">
            <a href="/BODIMAK.LK/seller/my_ads.php" class="btn btn-primary">My Ads</a>
            <a href="/BODIMAK.LK/index.php" class="btn btn-outline">Go to Homepage</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
