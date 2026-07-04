<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../includes/db.php';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'delete') {
        $stmt = $pdo->prepare("DELETE FROM advertisements WHERE ad_id = ?");
        $stmt->execute([$id]);
    } elseif ($action == 'approve') {
        // Admin approves → status becomes 'approved' (awaiting seller payment)
        $stmt = $pdo->prepare("UPDATE advertisements SET status = 'approved' WHERE ad_id = ?");
        $stmt->execute([$id]);
    } elseif ($action == 'reject') {
        $stmt = $pdo->prepare("UPDATE advertisements SET status = 'rejected' WHERE ad_id = ?");
        $stmt->execute([$id]);
    } elseif ($action == 'activate') {
        // Admin can manually activate (bypass payment) if needed
        $stmt = $pdo->prepare("UPDATE advertisements SET status = 'active' WHERE ad_id = ?");
        $stmt->execute([$id]);
    }
    header("Location: manage_ads.php");
    exit();
}

$status_filter = $_GET['status'] ?? '';
$query = "SELECT a.*, u.full_name as seller_name FROM advertisements a JOIN users u ON a.seller_id = u.user_id";
if ($status_filter) {
    $query .= " WHERE a.status = " . $pdo->quote($status_filter);
}
$query .= " ORDER BY a.created_at DESC";

$stmt = $pdo->query($query);
$ads = $stmt->fetchAll();
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
                <li><a href="manage_ads.php" style="display: block; padding: 0.5rem; background: var(--background-light); border-radius: 5px; font-weight: bold; color: var(--primary-color);">Manage Advertisements</a></li>
                <li><a href="commission_settings.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Commission Settings</a></li>
                <li><a href="payment_reports.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Payment Reports</a></li>
                <li><a href="../logout.php" style="display: block; padding: 0.5rem; color: var(--danger);">Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div style="flex: 3;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Manage Advertisements</h2>
            <div style="display: flex; gap: 0.5rem;">
                <a href="manage_ads.php" class="btn <?php echo empty($status_filter) ? 'btn-primary' : 'btn-outline'; ?>" style="padding: 0.5rem 1rem;">All</a>
                <a href="?status=pending" class="btn <?php echo $status_filter == 'pending' ? 'btn-warning' : 'btn-outline'; ?>" style="padding: 0.5rem 1rem;">Pending</a>
                <a href="?status=approved" class="btn <?php echo $status_filter == 'approved' ? 'btn-success' : 'btn-outline'; ?>" style="padding: 0.5rem 1rem;">Approved</a>
            </div>
        </div>
        
        <div class="card" style="padding: 0; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background-color: var(--background-light);">
                    <tr>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Ad Details</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Seller</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Date</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Status</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($ads as $ad): ?>
                        <tr>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div style="font-weight: bold;"><?php echo htmlspecialchars($ad['title']); ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($ad['location']); ?> - Rs. <?php echo number_format($ad['price'], 2); ?></div>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--border-color);"><?php echo htmlspecialchars($ad['seller_name']); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--border-color);"><?php echo date('Y-m-d', strtotime($ad['created_at'])); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <?php 
                                if ($ad['status'] === 'active') {
                                    $statusColor = 'var(--success)';
                                } elseif ($ad['status'] === 'approved') {
                                    $statusColor = '#3b82f6'; // blue = awaiting payment
                                } elseif ($ad['status'] === 'rejected') {
                                    $statusColor = 'var(--danger)';
                                } else {
                                    $statusColor = 'var(--warning)';
                                }
                                $statusLabel = $ad['status'] === 'approved' ? 'Awaiting Payment' : ucfirst($ad['status']);
                                ?>
                                <span style="background-color: <?php echo $statusColor; ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 20px; font-size: 0.85rem; white-space: nowrap;">
                                    <?php echo $statusLabel; ?>
                                </span>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: center;">
                                <div style="display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap;">
                                    <a href="../listing.php?id=<?php echo $ad['ad_id']; ?>" target="_blank" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">View</a>
                                    
                                    <?php if($ad['status'] === 'pending'): ?>
                                        <a href="?action=approve&id=<?php echo $ad['ad_id']; ?>" class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Approve</a>
                                        <a href="?action=reject&id=<?php echo $ad['ad_id']; ?>" class="btn btn-warning" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; color: var(--text-dark);">Reject</a>
                                    <?php elseif($ad['status'] === 'rejected'): ?>
                                        <a href="?action=approve&id=<?php echo $ad['ad_id']; ?>" class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Approve</a>
                                    <?php elseif($ad['status'] === 'approved'): ?>
                                        <a href="?action=reject&id=<?php echo $ad['ad_id']; ?>" class="btn btn-warning" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; color: var(--text-dark);">Reject</a>
                                        <a href="?action=activate&id=<?php echo $ad['ad_id']; ?>" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;" title="Manually activate without payment">Activate</a>
                                    <?php elseif($ad['status'] === 'active'): ?>
                                        <a href="?action=reject&id=<?php echo $ad['ad_id']; ?>" class="btn btn-warning" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; color: var(--text-dark);">Deactivate</a>
                                    <?php endif; ?>
                                    
                                    <a href="?action=delete&id=<?php echo $ad['ad_id']; ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;" onclick="return confirm('Are you sure you want to delete this ad permanently?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
