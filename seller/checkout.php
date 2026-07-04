<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}
require_once '../includes/db.php';
require_once '../includes/config.php';


if (!isset($_GET['ad_id']) || !is_numeric($_GET['ad_id'])) {
    header("Location: my_ads.php");
    exit();
}

$ad_id     = intval($_GET['ad_id']);
$seller_id = $_SESSION['user_id'];

// Verify ad belongs to this seller and is in 'approved' state
$stmt = $pdo->prepare("SELECT * FROM advertisements WHERE ad_id = ? AND seller_id = ? AND status = 'approved'");
$stmt->execute([$ad_id, $seller_id]);
$ad = $stmt->fetch();

if (!$ad) {
    header("Location: my_ads.php");
    exit();
}

// Fetch current commission rate
$commission_pct = floatval($pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'commission_percentage'")->fetchColumn() ?: 20);

// Check if there was a previous successful payment for this ad
$prev_stmt = $pdo->prepare("SELECT * FROM ad_payments WHERE ad_id = ? AND status = 'success' ORDER BY created_at DESC LIMIT 1");
$prev_stmt->execute([$ad_id]);
$prev_payment = $prev_stmt->fetch();

// Calculate amounts
$rental_price       = floatval($ad['price']);
$full_commission    = round(($rental_price * $commission_pct) / 100, 2);

if ($prev_payment) {
    // Top-up: only charge the difference
    $prev_rate          = floatval($prev_payment['commission_rate']);
    $prev_paid_amount   = floatval($prev_payment['amount']);
    $amount_due         = round(max(0, $full_commission - $prev_paid_amount), 2);
    $is_topup           = true;
} else {
    $amount_due = $full_commission;
    $is_topup   = false;
}

// If no payment is due (rate didn't change or decreased), activate the ad immediately
if ($amount_due <= 0) {
    $upd = $pdo->prepare("UPDATE advertisements SET status = 'active' WHERE ad_id = ? AND seller_id = ?");
    $upd->execute([$ad_id, $seller_id]);
    header("Location: my_ads.php?msg=activated");
    exit();
}

// Generate a unique order ID
$order_id = 'AD-' . $ad_id . '-' . time();

// Create a pending payment record
$ins = $pdo->prepare("INSERT INTO ad_payments (ad_id, seller_id, order_id, amount, commission_rate, status) VALUES (?, ?, ?, ?, ?, 'pending') ON DUPLICATE KEY UPDATE order_id = ?, amount = ?, commission_rate = ?, status = 'pending'");
$ins->execute([$ad_id, $seller_id, $order_id, $amount_due, $commission_pct, $order_id, $amount_due, $commission_pct]);

// Generate PayHere hash
$hash = generatePayhereHash(PAYHERE_MERCHANT_ID, $order_id, $amount_due, 'LKR', PAYHERE_MERCHANT_SECRET);

// Seller details
$seller_stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$seller_stmt->execute([$seller_id]);
$seller = $seller_stmt->fetch();
?>

<?php include '../includes/header.php'; ?>

<div class="container" style="margin-top: 2rem; max-width: 650px; margin-left: auto; margin-right: auto;">
    <div class="card">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <svg width="28" height="28" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <h2 style="color: var(--text-dark);"><?php echo $is_topup ? 'Top-Up Payment Required' : 'Complete Your Payment'; ?></h2>
            <p style="color: var(--text-muted); margin-top: 0.5rem;">Your ad has been approved! Pay the commission fee to publish it live.</p>
        </div>

        <!-- Ad Summary -->
        <div style="background: var(--background-light); border-radius: var(--border-radius); padding: 1.25rem; margin-bottom: 1.5rem;">
            <h4 style="margin-bottom: 0.75rem; color: var(--text-dark);">Ad Summary</h4>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="color: var(--text-muted);">Ad Title:</span>
                <span style="font-weight: 600;"><?php echo htmlspecialchars($ad['title']); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="color: var(--text-muted);">Monthly Rental:</span>
                <span>Rs. <?php echo number_format($rental_price, 2); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="color: var(--text-muted);">Commission Rate:</span>
                <span><?php echo $commission_pct; ?>% of rental</span>
            </div>
            <?php if ($is_topup && isset($prev_payment)): ?>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span style="color: var(--text-muted);">Previously Paid:</span>
                <span style="color: var(--success);">Rs. <?php echo number_format($prev_payment['amount'], 2); ?> (at <?php echo $prev_payment['commission_rate']; ?>%)</span>
            </div>
            <?php endif; ?>
            <div style="display: flex; justify-content: space-between; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border-color);">
                <span style="font-weight: bold; color: var(--text-dark);">Amount Due:</span>
                <span style="font-weight: bold; font-size: 1.2rem; color: var(--primary-color);">Rs. <?php echo number_format($amount_due, 2); ?></span>
            </div>
        </div>

        <!-- PayHere Form -->
        <form method="POST" action="<?php echo PAYHERE_CHECKOUT_URL; ?>">
            <input type="hidden" name="merchant_id"  value="<?php echo PAYHERE_MERCHANT_ID; ?>">
            <input type="hidden" name="return_url"   value="<?php echo SITE_URL; ?>/payment_return.php">
            <input type="hidden" name="cancel_url"   value="<?php echo SITE_URL; ?>/payment_cancel.php">
            <input type="hidden" name="notify_url"   value="<?php echo SITE_URL; ?>/payment_notify.php">
            <input type="hidden" name="order_id"     value="<?php echo $order_id; ?>">
            <input type="hidden" name="items"        value="Ad Publishing Commission - <?php echo htmlspecialchars($ad['title']); ?>">
            <input type="hidden" name="currency"     value="LKR">
            <input type="hidden" name="amount"       value="<?php echo number_format($amount_due, 2, '.', ''); ?>">
            <input type="hidden" name="hash"         value="<?php echo $hash; ?>">
            <!-- Customer Details -->
            <input type="hidden" name="first_name"   value="<?php echo htmlspecialchars(explode(' ', $seller['full_name'])[0]); ?>">
            <input type="hidden" name="last_name"    value="<?php echo htmlspecialchars(implode(' ', array_slice(explode(' ', $seller['full_name']), 1)) ?: 'N/A'); ?>">
            <input type="hidden" name="email"        value="<?php echo htmlspecialchars($seller['email']); ?>">
            <input type="hidden" name="phone"        value="<?php echo htmlspecialchars($seller['phone']); ?>">
            <input type="hidden" name="address"      value="<?php echo htmlspecialchars($ad['address']); ?>">
            <input type="hidden" name="city"         value="<?php echo htmlspecialchars($ad['location']); ?>">
            <input type="hidden" name="country"      value="Sri Lanka">

            <button type="submit" style="width: 100%; padding: 1rem; border: none; border-radius: var(--border-radius); background: linear-gradient(135deg, #f72585, #7209b7); color: white; font-size: 1.1rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.75rem; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(247,37,133,0.35);">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Pay Rs. <?php echo number_format($amount_due, 2); ?> via PayHere
            </button>
        </form>

        <p style="text-align: center; margin-top: 1.25rem; font-size: 0.85rem; color: var(--text-muted);">
            🔒 Secure payment powered by <strong>PayHere</strong>. Supports Visa, MasterCard & AMEX.
        </p>

        <div style="text-align: center; margin-top: 0.75rem;">
            <a href="my_ads.php" style="color: var(--text-muted); font-size: 0.9rem;">← Back to My Ads</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
