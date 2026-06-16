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
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role = 'user'");
        $stmt->execute([$id]);
    } elseif ($action == 'toggle') {
        $stmt = $pdo->prepare("UPDATE users SET status = IF(status='active', 'inactive', 'active') WHERE user_id = ? AND role = 'user'");
        $stmt->execute([$id]);
    }
    header("Location: manage_users.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container" style="margin-top: 2rem; display: flex; gap: 2rem; flex-wrap: wrap;">
    <!-- Sidebar -->
    <div style="flex: 1; min-width: 250px; max-width: 300px;">
        <div class="card">
            <h3 style="margin-bottom: 1rem;">Admin Panel</h3>
            <ul style="display: flex; flex-direction: column; gap: 0.5rem;">
                <li><a href="dashboard.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Dashboard</a></li>
                <li><a href="manage_users.php" style="display: block; padding: 0.5rem; background: var(--background-light); border-radius: 5px; font-weight: bold; color: var(--primary-color);">Manage Users</a></li>
                <li><a href="manage_sellers.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Manage Sellers</a></li>
                <li><a href="manage_ads.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Manage Advertisements</a></li>
                <li><a href="../logout.php" style="display: block; padding: 0.5rem; color: var(--danger);">Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div style="flex: 3;">
        <h2 style="margin-bottom: 1.5rem;">Manage Users</h2>
        
        <div class="card" style="padding: 0; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background-color: var(--background-light);">
                    <tr>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Name</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Email</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Phone</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Status</th>
                        <th style="padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                        <tr>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--border-color);"><?php echo htmlspecialchars($u['full_name']); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--border-color);"><?php echo htmlspecialchars($u['email']); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--border-color);"><?php echo htmlspecialchars($u['phone']); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <span style="padding: 0.25rem 0.5rem; border-radius: 20px; font-size: 0.85rem; color: white; background-color: <?php echo $u['status'] == 'active' ? 'var(--success)' : 'var(--danger)'; ?>;">
                                    <?php echo ucfirst($u['status']); ?>
                                </span>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: center;">
                                <a href="?action=toggle&id=<?php echo $u['user_id']; ?>" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Toggle Status</a>
                                <a href="?action=delete&id=<?php echo $u['user_id']; ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;" onclick="return confirm('Delete this user?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
