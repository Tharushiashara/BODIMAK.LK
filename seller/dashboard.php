<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}
require_once '../includes/db.php';

// Fetch seller ads stats
$stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved, SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending FROM advertisements WHERE seller_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();
?>

<?php include '../includes/header.php'; ?>

<div class="container" style="margin-top: 2rem; display: flex; gap: 2rem; flex-wrap: wrap;">
    <!-- Sidebar -->
    <div style="flex: 1; min-width: 250px; max-width: 300px;">
        <div class="card">
            <h3 style="margin-bottom: 1rem;">Seller Menu</h3>
            <ul style="display: flex; flex-direction: column; gap: 0.5rem;">
                <li><a href="dashboard.php" style="display: block; padding: 0.5rem; background: var(--background-light); border-radius: 5px; font-weight: bold; color: var(--primary-color);">Overview</a></li>
                <li><a href="add_ad.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Post Advertisement</a></li>
                <li><a href="my_ads.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">My Advertisements</a></li>
                <li><a href="../logout.php" style="display: block; padding: 0.5rem; color: var(--danger);">Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div style="flex: 3;">
        <h2 style="margin-bottom: 1.5rem;">Seller Overview</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="card" style="text-align: center;">
                <h3 style="font-size: 2.5rem; color: var(--primary-color);"><?php echo $stats['total'] ?? 0; ?></h3>
                <p style="color: var(--text-muted);">Total Ads</p>
            </div>
            <div class="card" style="text-align: center;">
                <h3 style="font-size: 2.5rem; color: var(--success);"><?php echo $stats['approved'] ?? 0; ?></h3>
                <p style="color: var(--text-muted);">Active Ads</p>
            </div>
            <div class="card" style="text-align: center;">
                <h3 style="font-size: 2.5rem; color: var(--warning);"><?php echo $stats['pending'] ?? 0; ?></h3>
                <p style="color: var(--text-muted);">Pending Approval</p>
            </div>
        </div>

        <div class="card">
            <h3>Quick Actions</h3>
            <div style="margin-top: 1rem; display: flex; gap: 1rem;">
                <a href="add_ad.php" class="btn btn-primary">Post New Advertisement</a>
                <a href="my_ads.php" class="btn btn-outline">Manage Ads</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
