<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../includes/db.php';

// Filters
$status_filter = $_GET['status'] ?? '';
$where = $status_filter ? "WHERE ap.status = " . $pdo->quote($status_filter) : '';

$payments = $pdo->query("
    SELECT ap.*, a.title, a.price, a.location, u.full_name, u.email
    FROM ad_payments ap
    JOIN advertisements a ON ap.ad_id = a.ad_id
    JOIN users u ON ap.seller_id = u.user_id
    {$where}
    ORDER BY ap.created_at DESC
")->fetchAll();

$total = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM ad_payments WHERE status = 'success'")->fetchColumn();
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
                <li><a href="commission_settings.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Commission Settings</a></li>
                <li><a href="payment_reports.php" style="display: block; padding: 0.5rem; background: var(--background-light); border-radius: 5px; font-weight: bold; color: var(--primary-color);">Payment Reports</a></li>
                <li><a href="../logout.php" style="display: block; padding: 0.5rem; color: var(--danger);">Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main -->
    <div style="flex: 3;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2>Payment Reports</h2>
                <p style="color: var(--text-muted);">Total Collected: <strong style="color: var(--success);">Rs. <?php echo number_format($total, 2); ?></strong></p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="payment_reports.php" class="btn <?php echo empty($status_filter) ? 'btn-primary' : 'btn-outline'; ?>" style="padding: 0.4rem 0.9rem;">All</a>
                <a href="?status=success" class="btn <?php echo $status_filter === 'success' ? 'btn-success' : 'btn-outline'; ?>" style="padding: 0.4rem 0.9rem;">Success</a>
                <a href="?status=pending" class="btn <?php echo $status_filter === 'pending' ? 'btn-warning' : 'btn-outline'; ?>" style="padding: 0.4rem 0.9rem; color: var(--text-dark);">Pending</a>
                <a href="?status=failed" class="btn <?php echo $status_filter === 'failed' ? 'btn-danger' : 'btn-outline'; ?>" style="padding: 0.4rem 0.9rem;">Failed</a>
            </div>
        </div>

        <div class="card" style="padding: 0; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background: var(--background-light);">
                    <tr>
                        <th style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">#</th>
                        <th style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">Seller</th>
                        <th style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">Ad Title</th>
                        <th style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">Rental Price</th>
                        <th style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">Rate</th>
                        <th style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">Amount Paid</th>
                        <th style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">PayHere ID</th>
                        <th style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">Date</th>
                        <th style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): ?>
                    <tr>
                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color); font-size: 0.85rem; color: var(--text-muted);"><?php echo $p['payment_id']; ?></td>
                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($p['full_name']); ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($p['email']); ?></div>
                        </td>
                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);"><?php echo htmlspecialchars($p['title']); ?></td>
                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">Rs. <?php echo number_format($p['price'], 2); ?></td>
                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);"><?php echo $p['commission_rate']; ?>%</td>
                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color); font-weight: bold; color: var(--success);">Rs. <?php echo number_format($p['amount'], 2); ?></td>
                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color); font-size: 0.8rem; color: var(--text-muted);">
                            <?php echo $p['payhere_payment_id'] ? htmlspecialchars($p['payhere_payment_id']) : '—'; ?>
                        </td>
                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color); font-size: 0.85rem;"><?php echo date('Y-m-d H:i', strtotime($p['created_at'])); ?></td>
                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);">
                            <?php
                            $sc = $p['status'] === 'success' ? 'var(--success)' : ($p['status'] === 'failed' ? 'var(--danger)' : 'var(--warning)');
                            ?>
                            <span style="background: <?php echo $sc; ?>; color: white; padding: 0.2rem 0.5rem; border-radius: 20px; font-size: 0.8rem; text-transform: capitalize;">
                                <?php echo ucfirst($p['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($payments)): ?>
                    <tr><td colspan="9" style="padding: 2rem; text-align: center; color: var(--text-muted);">No payment records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
