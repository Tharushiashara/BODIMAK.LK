<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../includes/db.php';

$msg = '';
$error = '';

// Save updated commission percentage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commission_percentage'])) {
    $new_pct = floatval($_POST['commission_percentage']);
    if ($new_pct <= 0 || $new_pct > 100) {
        $error = "Commission must be between 0.01 and 100.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('commission_percentage', ?) ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
        $stmt->execute([$new_pct, $new_pct]);
        $msg = "Commission percentage updated to {$new_pct}% successfully!";
    }
}

// Fetch current commission
$current_pct = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'commission_percentage'")->fetchColumn();
$current_pct = $current_pct !== false ? floatval($current_pct) : 20;

// Fetch total earnings
$total_earned = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM ad_payments WHERE status = 'success'")->fetchColumn();
$total_ads_paid = $pdo->query("SELECT COUNT(*) FROM ad_payments WHERE status = 'success'")->fetchColumn();

// Recent payments
$recent = $pdo->query("
    SELECT ap.*, a.title, a.price, u.full_name
    FROM ad_payments ap
    JOIN advertisements a ON ap.ad_id = a.ad_id
    JOIN users u ON ap.seller_id = u.user_id
    ORDER BY ap.created_at DESC
    LIMIT 5
")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container" style="margin-top: 2rem; display: flex; gap: 2rem; flex-wrap: wrap;">
    <!-- Sidebar -->
    <div style="flex: 1; min-width: 250px; max-width: 300px;">
        <div class="card">
            <h3 style="margin-bottom: 1rem;">Admin Panel</h3>
            <ul style="display: flex; flex-direction: column; gap: 0.5rem;">
                <li><a href="dashboard.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Dashboard</a></li>
                <li><a href="manage_users.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Manage Users</a></li>
                <li><a href="manage_sellers.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Manage Sellers</a></li>
                <li><a href="manage_ads.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Manage Advertisements</a></li>
                <li><a href="commission_settings.php" style="display: block; padding: 0.5rem; background: var(--background-light); border-radius: 5px; font-weight: bold; color: var(--primary-color);">Commission Settings</a></li>
                <li><a href="payment_reports.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Payment Reports</a></li>
                <li><a href="../logout.php" style="display: block; padding: 0.5rem; color: var(--danger);">Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div style="flex: 3;">
        <h2 style="margin-bottom: 1.5rem;">Commission Settings</h2>

        <!-- Stats -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="card" style="text-align: center; border-bottom: 4px solid var(--primary-color);">
                <h3 style="font-size: 2.5rem; color: var(--primary-color);"><?php echo $current_pct; ?>%</h3>
                <p style="color: var(--text-muted);">Current Commission Rate</p>
            </div>
            <div class="card" style="text-align: center; border-bottom: 4px solid var(--success);">
                <h3 style="font-size: 2rem; color: var(--text-dark);">Rs. <?php echo number_format($total_earned, 2); ?></h3>
                <p style="color: var(--text-muted);">Total Earnings</p>
            </div>
            <div class="card" style="text-align: center; border-bottom: 4px solid var(--secondary-color);">
                <h3 style="font-size: 2.5rem; color: var(--text-dark);"><?php echo $total_ads_paid; ?></h3>
                <p style="color: var(--text-muted);">Ads Paid & Live</p>
            </div>
        </div>

        <!-- Update Form -->
        <div class="card" style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem;">Update Commission Percentage</h3>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($msg): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group" style="max-width: 350px;">
                    <label class="form-label">Commission Percentage (%)</label>
                    <input type="number" name="commission_percentage" class="form-control"
                           min="0.01" max="100" step="0.01"
                           value="<?php echo $current_pct; ?>" required>
                    <small style="color: var(--text-muted);">
                        Sellers will be charged this % of their monthly rental price when they publish an ad.
                    </small>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Save Commission Rate</button>
            </form>
        </div>

        <!-- Recent Payments -->
        <div class="card" style="padding: 0; overflow-x: auto;">
            <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3>Recent Payments</h3>
            </div>
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background: var(--background-light);">
                    <tr>
                        <th style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">Seller</th>
                        <th style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">Ad Title</th>
                        <th style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">Rate</th>
                        <th style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">Paid</th>
                        <th style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $row): ?>
                    <tr>
                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);"><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);"><?php echo htmlspecialchars($row['title']); ?></td>
                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);"><?php echo $row['commission_rate']; ?>%</td>
                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">Rs. <?php echo number_format($row['amount'], 2); ?></td>
                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">
                            <?php
                            $sColor = $row['status'] === 'success' ? 'var(--success)' : ($row['status'] === 'failed' ? 'var(--danger)' : 'var(--warning)');
                            ?>
                            <span style="background: <?php echo $sColor; ?>; color: white; padding: 0.2rem 0.5rem; border-radius: 20px; font-size: 0.8rem; text-transform: capitalize;">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recent)): ?>
                    <tr><td colspan="5" style="padding: 2rem; text-align: center; color: var(--text-muted);">No payments yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
