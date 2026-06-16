<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}
require_once '../includes/db.php';

// Fetch saved ads
$stmt = $pdo->prepare("
    SELECT a.*, (SELECT image_path FROM ad_images WHERE ad_id = a.ad_id LIMIT 1) as cover_image 
    FROM advertisements a 
    JOIN saved_listings s ON a.ad_id = s.ad_id 
    WHERE s.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$savedAds = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container" style="margin-top: 2rem; display: flex; gap: 2rem; flex-wrap: wrap;">
    <!-- Sidebar -->
    <div style="flex: 1; min-width: 250px; max-width: 300px;">
        <div class="card">
            <h3 style="margin-bottom: 1rem;">Navigation</h3>
            <ul style="display: flex; flex-direction: column; gap: 0.5rem;">
                <li><a href="dashboard.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">My Profile</a></li>
                <li><a href="saved.php" style="display: block; padding: 0.5rem; background: var(--background-light); border-radius: 5px; font-weight: bold; color: var(--primary-color);">Saved Listings</a></li>
                <li><a href="../logout.php" style="display: block; padding: 0.5rem; color: var(--danger);">Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div style="flex: 3;">
        <h2 style="margin-bottom: 1.5rem;">Saved Listings</h2>

        <?php if (count($savedAds) > 0): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                <?php foreach ($savedAds as $ad): ?>
                    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 0;">
                        <?php
                        $imgPath = $ad['cover_image'] ? '../' . htmlspecialchars($ad['cover_image']) : 'https://via.placeholder.com/400x300?text=No+Image';
                        ?>
                        <img src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($ad['title']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
                        <div style="padding: 1.5rem;">
                            <div style="font-size: 1.25rem; font-weight: 700; color: var(--primary-color); margin-bottom: 0.5rem;">Rs. <?php echo number_format($ad['price'], 2); ?> / month</div>
                            <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($ad['title']); ?></h3>
                            <a href="../listing.php?id=<?php echo $ad['ad_id']; ?>" class="btn btn-primary" style="display: block; width: 100%; margin-top: 1rem;">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card">
                <p>You haven't saved any listings yet. <a href="../search.php">Browse boardings</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>