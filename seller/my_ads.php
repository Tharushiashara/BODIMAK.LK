<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}
require_once '../includes/db.php';

// Handle deletion
if(isset($_POST['delete_id'])) {
    $del_id = $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM advertisements WHERE ad_id = ? AND seller_id = ?");
    $stmt->execute([$del_id, $_SESSION['user_id']]);
    header("Location: my_ads.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT a.*, (SELECT image_path FROM ad_images WHERE ad_id = a.ad_id LIMIT 1) as cover_image 
    FROM advertisements a 
    WHERE seller_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$ads = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container" style="margin-top: 2rem; display: flex; gap: 2rem; flex-wrap: wrap;">
    <!-- Sidebar -->
    <div style="flex: 1; min-width: 250px; max-width: 300px;">
        <div class="card">
            <h3 style="margin-bottom: 1rem;">Seller Menu</h3>
            <ul style="display: flex; flex-direction: column; gap: 0.5rem;">
                <li><a href="dashboard.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Overview</a></li>
                <li><a href="add_ad.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Post Advertisement</a></li>
                <li><a href="my_ads.php" style="display: block; padding: 0.5rem; background: var(--background-light); border-radius: 5px; font-weight: bold; color: var(--primary-color);">My Advertisements</a></li>
                <li><a href="../logout.php" style="display: block; padding: 0.5rem; color: var(--danger);">Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div style="flex: 3;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>My Advertisements</h2>
            <a href="add_ad.php" class="btn btn-primary">Post New</a>
        </div>
        
        <?php if(count($ads) > 0): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: var(--white); box-shadow: var(--shadow-sm); border-radius: var(--border-radius); overflow: hidden;">
                    <thead style="background-color: var(--background-light);">
                        <tr>
                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color);">Title</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color);">Price</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color);">Date</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-color);">Status</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 1px solid var(--border-color);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($ads as $ad): ?>
                            <tr>
                                <td style="padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                    <div style="font-weight: bold;"><?php echo htmlspecialchars($ad['title']); ?></div>
                                    <div style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($ad['location']); ?></div>
                                </td>
                                <td style="padding: 1rem; border-bottom: 1px solid var(--border-color);">Rs. <?php echo number_format($ad['price'], 2); ?></td>
                                <td style="padding: 1rem; border-bottom: 1px solid var(--border-color);"><?php echo date('Y-m-d', strtotime($ad['created_at'])); ?></td>
                                <td style="padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                    <?php
                                    if ($ad['status'] === 'active') {
                                        $statusColor = 'var(--success)';
                                        $statusLabel = '🟢 Live';
                                    } elseif ($ad['status'] === 'approved') {
                                        $statusColor = '#3b82f6';
                                        $statusLabel = '💳 Pay to Publish';
                                    } elseif ($ad['status'] === 'rejected') {
                                        $statusColor = 'var(--danger)';
                                        $statusLabel = 'Rejected';
                                    } else {
                                        $statusColor = 'var(--warning)';
                                        $statusLabel = 'Under Review';
                                    }
                                    ?>
                                    <span style="background-color: <?php echo $statusColor; ?>; color: white; padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.82rem; white-space: nowrap;">
                                        <?php echo $statusLabel; ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: center;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap;">
                                        <?php if ($ad['status'] === 'approved'): ?>
                                            <!-- Payment Required - prominent Pay Now button -->
                                            <a href="checkout.php?ad_id=<?php echo $ad['ad_id']; ?>" class="btn btn-primary" style="padding: 0.35rem 0.75rem; font-size: 0.85rem; background: linear-gradient(135deg,#f72585,#7209b7); border: none; animation: pulse 2s infinite;">
                                                💳 Pay Now
                                            </a>
                                        <?php else: ?>
                                            <a href="../listing.php?id=<?php echo $ad['ad_id']; ?>" target="_blank" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">View</a>
                                            <a href="edit_ad.php?id=<?php echo $ad['ad_id']; ?>" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; background-color: var(--secondary-color); border-color: var(--secondary-color);">Edit</a>
                                        <?php endif; ?>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this ad?');" style="display: inline;">
                                            <input type="hidden" name="delete_id" value="<?php echo $ad['ad_id']; ?>">
                                            <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card" style="text-align: center; padding: 3rem;">
                <p style="color: var(--text-muted); margin-bottom: 1rem;">You haven't posted any advertisements yet.</p>
                <a href="add_ad.php" class="btn btn-primary">Create Your First Ad</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
