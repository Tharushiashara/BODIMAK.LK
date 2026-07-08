<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../includes/db.php';

// Filters
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$where_clauses = [];
if ($status_filter) {
    $where_clauses[] = "ap.status = " . $pdo->quote($status_filter);
}
if ($date_from) {
    $where_clauses[] = "DATE(ap.created_at) >= " . $pdo->quote($date_from);
}
if ($date_to) {
    $where_clauses[] = "DATE(ap.created_at) <= " . $pdo->quote($date_to);
}

$where = !empty($where_clauses) ? "WHERE " . implode(' AND ', $where_clauses) : '';

$payments = $pdo->query("
    SELECT ap.*, a.title, a.price, a.location, u.full_name, u.email
    FROM ad_payments ap
    JOIN advertisements a ON ap.ad_id = a.ad_id
    JOIN users u ON ap.seller_id = u.user_id
    {$where}
    ORDER BY ap.created_at DESC
")->fetchAll();

$total_where_clauses = ["status = 'success'"];
if ($date_from) {
    $total_where_clauses[] = "DATE(created_at) >= " . $pdo->quote($date_from);
}
if ($date_to) {
    $total_where_clauses[] = "DATE(created_at) <= " . $pdo->quote($date_to);
}
$total_where = "WHERE " . implode(' AND ', $total_where_clauses);

$total = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM ad_payments {$total_where}")->fetchColumn();

// Export to CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=commission_report_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Payment ID', 'Seller Name', 'Seller Email', 'Ad Title', 'Rental Price', 'Commission Rate', 'Amount Paid', 'PayHere ID', 'Date', 'Status']);
    
    foreach ($payments as $p) {
        fputcsv($output, [
            $p['payment_id'],
            $p['full_name'],
            $p['email'],
            $p['title'],
            $p['price'],
            $p['commission_rate'] . '%',
            $p['amount'],
            $p['payhere_payment_id'],
            $p['created_at'],
            $p['status']
        ]);
    }
    fclose($output);
    exit();
}
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
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; justify-content: flex-end;">
                <form method="GET" style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0;">
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" class="form-control" style="padding: 0.4rem; max-width: 140px; border: 1px solid var(--border-color); border-radius: 5px;">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">to</span>
                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" class="form-control" style="padding: 0.4rem; max-width: 140px; border: 1px solid var(--border-color); border-radius: 5px;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.4rem 0.9rem;">Filter</button>
                    <?php if ($date_from || $date_to || $status_filter): ?>
                    <a href="payment_reports.php" class="btn btn-outline" style="padding: 0.4rem 0.9rem;">Clear</a>
                    <?php endif; ?>
                </form>

                <div style="border-left: 1px solid var(--border-color); height: 30px; margin: 0 0.5rem;"></div>

                <a href="?status=&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="btn <?php echo empty($status_filter) ? 'btn-primary' : 'btn-outline'; ?>" style="padding: 0.4rem 0.9rem;">All</a>
                <a href="?status=success&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="btn <?php echo $status_filter === 'success' ? 'btn-success' : 'btn-outline'; ?>" style="padding: 0.4rem 0.9rem;">Success</a>
                <a href="?status=pending&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="btn <?php echo $status_filter === 'pending' ? 'btn-warning' : 'btn-outline'; ?>" style="padding: 0.4rem 0.9rem; color: var(--text-dark);">Pending</a>
                <a href="?status=failed&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="btn <?php echo $status_filter === 'failed' ? 'btn-danger' : 'btn-outline'; ?>" style="padding: 0.4rem 0.9rem;">Failed</a>
                
                <div style="border-left: 1px solid var(--border-color); height: 30px; margin: 0 0.5rem;"></div>

                <a href="?export=csv&status=<?php echo urlencode($status_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="btn btn-success" style="padding: 0.4rem 0.9rem; display: flex; align-items: center; gap: 0.3rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Download CSV
                </a>
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
