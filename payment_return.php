<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/config.php';

/**
 * payment_return.php — Handles PayHere redirect after payment.
 */

$order_id         = $_GET['order_id']         ?? null;
$payment_id       = $_GET['payment_id']       ?? null;
$status_code      = $_GET['status_code']      ?? null;

$payment     = null;
$status      = 'unknown';

if ($order_id) {
    // 1. Load the payment record
    $stmt = $pdo->prepare("
        SELECT ap.*, a.title, a.ad_id
        FROM ad_payments ap
        JOIN advertisements a ON ap.ad_id = a.ad_id
        WHERE ap.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $payment = $stmt->fetch();

    if ($payment && $status_code !== null) {
        // 2. Update status based on PayHere status_code
        // 2 = Success, 0 = Pending, -1 = Cancelled, -2/-3 = Failed
        $code = (int)$status_code;

        if ($code === 2) {
            $status = 'success';
            $pdo->prepare("UPDATE ad_payments SET status = 'success', payhere_payment_id = ?, updated_at = NOW() WHERE order_id = ?")->execute([$payment_id, $order_id]);
            $pdo->prepare("UPDATE advertisements SET status = 'active' WHERE ad_id = ?")->execute([$payment['ad_id']]);
        } elseif ($code === 0) {
            $status = 'pending';
            $pdo->prepare("UPDATE ad_payments SET status = 'pending', payhere_payment_id = ?, updated_at = NOW() WHERE order_id = ?")->execute([$payment_id, $order_id]);
        } elseif ($code === -1) {
            $status = 'cancelled';
            $pdo->prepare("UPDATE ad_payments SET status = 'cancelled', updated_at = NOW() WHERE order_id = ?")->execute([$order_id]);
        } else {
            $status = 'failed';
            $pdo->prepare("UPDATE ad_payments SET status = 'failed', updated_at = NOW() WHERE order_id = ?")->execute([$order_id]);
        }
    } elseif ($payment && PAYHERE_ENV === 'sandbox' && $payment['status'] === 'pending') {
        // SANDBOX LOCALHOST HACK:
        // PayHere does not send status_code to return_url, only to notify_url.
        // Since notify_url is unreachable on localhost, if we get redirected back
        // here in sandbox mode and the status is still pending, assume success.
        $status = 'success';
        $pdo->prepare("UPDATE ad_payments SET status = 'success', payhere_payment_id = 'SANDBOX-LOCAL', updated_at = NOW() WHERE order_id = ?")->execute([$order_id]);
        $pdo->prepare("UPDATE advertisements SET status = 'active' WHERE ad_id = ?")->execute([$payment['ad_id']]);
    } elseif ($payment) {
        $status = $payment['status'];
    }
}

include 'includes/header.php';
?>

<div class="container" style="margin-top: 4rem; max-width: 600px; margin-left: auto; margin-right: auto; text-align: center; padding-bottom: 4rem;">
    <div class="card" style="padding: 3rem 2rem;">

        <?php if ($status === 'success'): ?>
            <!-- SUCCESS -->
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg width="40" height="40" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 style="color: #065f46; margin-bottom: 1rem;">Payment Successful!</h1>
            <p style="color: var(--text-muted); font-size: 1.05rem; line-height: 1.7;">
                Your payment has been received. Your advertisement is now <strong>live</strong> and visible to users on BODIMAK.LK!
            </p>

        <?php elseif ($status === 'pending'): ?>
            <!-- PENDING -->
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg width="40" height="40" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 style="color: #92400e; margin-bottom: 1rem;">Payment Pending</h1>
            <p style="color: var(--text-muted); font-size: 1.05rem; line-height: 1.7;">
                Your payment is being processed (e.g. bank transfer). Your ad will be activated once the payment is confirmed.
            </p>

        <?php elseif ($status === 'cancelled'): ?>
            <!-- CANCELLED -->
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #6b7280, #4b5563); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg width="40" height="40" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h1 style="color: var(--text-dark); margin-bottom: 1rem;">Payment Cancelled</h1>
            <p style="color: var(--text-muted); font-size: 1.05rem; line-height: 1.7;">
                You cancelled the payment. Your ad has not been published. You can try again from your dashboard.
            </p>

        <?php elseif ($status === 'failed'): ?>
            <!-- FAILED -->
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #ef4444, #b91c1c); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg width="40" height="40" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 style="color: #b91c1c; margin-bottom: 1rem;">Payment Failed</h1>
            <p style="color: var(--text-muted); font-size: 1.05rem; line-height: 1.7;">
                Your payment was not successful. Please try again or contact support.
            </p>

        <?php else: ?>
            <!-- UNKNOWN -->
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #8b5cf6, #6d28d9); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg width="40" height="40" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 style="color: var(--text-dark); margin-bottom: 1rem;">Payment Status Unknown</h1>
            <p style="color: var(--text-muted); font-size: 1.05rem; line-height: 1.7;">
                We could not determine your payment status. Please check your ad status in your dashboard.
            </p>
        <?php endif; ?>

        <!-- Payment Details -->
        <?php if (!empty($payment)): ?>
        <div style="background: var(--background-light); border-radius: var(--border-radius); padding: 1.25rem; margin: 1.5rem 0; text-align: left;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="color: var(--text-muted);">Ad Title:</span>
                <span style="font-weight: 600;"><?php echo htmlspecialchars($payment['title']); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="color: var(--text-muted);">Order ID:</span>
                <span style="font-size: 0.9rem;"><?php echo htmlspecialchars($payment['order_id']); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">Amount:</span>
                <span style="font-weight: bold; color: var(--success);">Rs. <?php echo number_format($payment['amount'], 2); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem; flex-wrap: wrap;">
            <a href="/BODIMAK.LK/seller/my_ads.php" class="btn btn-primary">View My Ads</a>
            <a href="/BODIMAK.LK/index.php" class="btn btn-outline">Go to Homepage</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
