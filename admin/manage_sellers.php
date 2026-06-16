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
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role = 'seller'");
        $stmt->execute([$id]);
    } elseif ($action == 'approve' || $action == 'toggle') {
        $stmt = $pdo->prepare("UPDATE users SET status = IF(status='active', 'inactive', 'active') WHERE user_id = ? AND role = 'seller'");
        $stmt->execute([$id]);
    }
    header("Location: manage_sellers.php");
    exit();
}

$status_filter = $_GET['status'] ?? '';
$query = "SELECT * FROM users WHERE role = 'seller'";
if ($status_filter == 'inactive') {
    $query .= " AND status = 'inactive'";
}
$query .= " ORDER BY created_at DESC";

$stmt = $pdo->query($query);
$sellers = $stmt->fetchAll();
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
                <li><a href="manage_sellers.php" style="display: block; padding: 0.5rem; background: var(--background-light); border-radius: 5px; font-weight: bold; color: var(--primary-color);">Manage Sellers</a></li>
                <li><a href="manage_ads.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Manage Advertisements</a></li>
                <li><a href="../logout.php" style="display: block; padding: 0.5rem; color: var(--danger);">Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div style="flex: 3;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Manage Sellers</h2>
            <?php if($status_filter == 'inactive'): ?>
                <a href="manage_sellers.php" class="btn btn-outline">View All Sellers</a>
            <?php else: ?>
                <a href="?status=inactive" class="btn btn-primary">View Pending Approvals</a>
            <?php endif; ?>
        </div>
        
        <div class="card" style="padding: 0; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background-color: var(--background-light);">
                    <tr>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Name</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Contact Info</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Status</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($sellers as $s): ?>
                        <tr>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--border-color);"><?php echo htmlspecialchars($s['full_name']); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div><?php echo htmlspecialchars($s['email']); ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($s['phone']); ?></div>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <span style="padding: 0.25rem 0.5rem; border-radius: 20px; font-size: 0.85rem; color: white; background-color: <?php echo $s['status'] == 'active' ? 'var(--success)' : 'var(--warning)'; ?>;">
                                    <?php echo ucfirst($s['status']); ?>
                                </span>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: center;">
                                <?php if($s['status'] == 'inactive'): ?>
                                    <a href="?action=approve&id=<?php echo $s['user_id']; ?>" class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Approve</a>
                                <?php else: ?>
                                    <a href="?action=toggle&id=<?php echo $s['user_id']; ?>" class="btn btn-warning" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; color: var(--text-dark);">Deactivate</a>
                                <?php endif; ?>
                                <a href="?action=delete&id=<?php echo $s['user_id']; ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;" onclick="return confirm('Delete this seller and all their ads?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
