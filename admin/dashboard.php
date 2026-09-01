<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../includes/db.php';

// Fetch=krna   count gana
$stats = [];
$stats['users'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$stats['sellers'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn();
$stats['ads_total'] = $pdo->query("SELECT COUNT(*) FROM advertisements")->fetchColumn();
$stats['ads_pending'] = $pdo->query("SELECT COUNT(*) FROM advertisements WHERE status = 'pending'")->fetchColumn();
?>

<?php include '../includes/header.php'; ?>

<div class="container" style="margin-top: 2rem; display: flex; gap: 2rem; flex-wrap: wrap;">
    <!-- Sidebar -->
    <div style="flex: 1; min-width: 250px; max-width: 300px;">
        <div class="card">
            <h3 style="margin-bottom: 1rem;">Admin Panel</h3>
            <ul style="display: flex; flex-direction: column; gap: 0.5rem;">
                <li><a href="dashboard.php" style="display: block; padding: 0.5rem; background: var(--background-light); border-radius: 5px; font-weight: bold; color: var(--primary-color);">Dashboard</a></li>
                <li><a href="manage_users.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Manage Users</a></li>
                <li><a href="manage_sellers.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Manage Sellers</a></li>
                <li><a href="manage_ads.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Manage Advertisements</a></li>
                <li><a href="commission_settings.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Commission Settings</a></li>
                <li><a href="payment_reports.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Payment Reports</a></li>
                <li><a href="../logout.php" style="display: block; padding: 0.5rem; color: var(--danger);">Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div style="flex: 3;">
        <h2 style="margin-bottom: 1.5rem;">Admin Dashboard</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="card" style="text-align: center; border-bottom: 4px solid var(--primary-color);">
                <h3 style="font-size: 2.5rem; color: var(--text-dark);"><?php echo $stats['users']; ?></h3>
                <p style="color: var(--text-muted);">Total Users</p>
            </div>
            <div class="card" style="text-align: center; border-bottom: 4px solid var(--secondary-color);">
                <h3 style="font-size: 2.5rem; color: var(--text-dark);"><?php echo $stats['sellers']; ?></h3>
                <p style="color: var(--text-muted);">Total Sellers</p>
            </div>
            <div class="card" style="text-align: center; border-bottom: 4px solid var(--success);">
                <h3 style="font-size: 2.5rem; color: var(--text-dark);"><?php echo $stats['ads_total']; ?></h3>
                <p style="color: var(--text-muted);">Total Listings</p>
            </div>
            <div class="card" style="text-align: center; border-bottom: 4px solid var(--warning);">
                <h3 style="font-size: 2.5rem; color: var(--text-dark);"><?php echo $stats['ads_pending']; ?></h3>
                <p style="color: var(--text-muted);">Pending Approvals</p>
            </div>
        </div>

        <div class="card">
            <h3>Quick Actions</h3>
            <div style="margin-top: 1rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="manage_ads.php?status=pending" class="btn btn-warning" style="color:var(--text-dark);">Review Pending Ads</a>
                <a href="manage_sellers.php?status=inactive" class="btn btn-primary">Review New Sellers</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>