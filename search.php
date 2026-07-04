<?php
session_start();
require_once 'includes/db.php';

$location = $_GET['location'] ?? '';
$type = $_GET['type'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

$query = "SELECT a.*, (SELECT image_path FROM ad_images WHERE ad_id = a.ad_id LIMIT 1) as cover_image FROM advertisements a WHERE status = 'active'";
$params = [];

if (!empty($location)) {
    $query .= " AND (location LIKE ? OR address LIKE ?)";
    $params[] = "%$location%";
    $params[] = "%$location%";
}
if (!empty($type)) {
    $query .= " AND room_type = ?";
    $params[] = $type;
}
if (!empty($min_price)) {
    $query .= " AND price >= ?";
    $params[] = $min_price;
}
if (!empty($max_price)) {
    $query .= " AND price <= ?";
    $params[] = $max_price;
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll();

// Fetch saved ads for currently logged in user (if any)
$savedAdIds = [];
if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'user') {
    $stmt_saved = $pdo->prepare("SELECT ad_id FROM saved_listings WHERE user_id = ?");
    $stmt_saved->execute([$_SESSION['user_id']]);
    $savedAdIds = $stmt_saved->fetchAll(PDO::FETCH_COLUMN);
}
?>

<?php include 'includes/header.php'; ?>

<div class="container" style="margin-top: 2rem;">
    <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
        
        <!-- Sidebar Filters -->
        <div style="flex: 1; min-width: 250px; max-width: 300px;">
            <div class="card" style="position: sticky; top: 100px;">
                <h3>Filters</h3>
                <form action="search.php" method="GET">
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($location); ?>" placeholder="e.g. Colombo">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Room Type</label>
                        <select name="type" class="form-control">
                            <option value="">Any</option>
                            <option value="Single" <?php echo $type == 'Single' ? 'selected' : ''; ?>>Single</option>
                            <option value="Shared" <?php echo $type == 'Shared' ? 'selected' : ''; ?>>Shared</option>
                            <option value="Annex" <?php echo $type == 'Annex' ? 'selected' : ''; ?>>Annex</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Min Price (Rs.)</label>
                        <input type="number" name="min_price" class="form-control" value="<?php echo htmlspecialchars($min_price); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Price (Rs.)</label>
                        <input type="number" name="max_price" class="form-control" value="<?php echo htmlspecialchars($max_price); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Apply Filters</button>
                    <a href="search.php" class="btn btn-outline" style="width: 100%; margin-top: 0.5rem; display: block;">Clear</a>
                </form>
            </div>
        </div>
        
        <!-- Results -->
        <div style="flex: 3;">
            <h2 style="margin-bottom: 1.5rem;">Search Results (<?php echo count($results); ?>)</h2>
            
            <?php if(count($results) > 0): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                    <?php foreach($results as $ad): ?>
                        <div class="card" style="padding: 0; overflow: visible; margin-bottom: 0; position: relative;">
                            <?php 
                            $imgPath = $ad['cover_image'] ? htmlspecialchars($ad['cover_image']) : 'https://via.placeholder.com/400x300?text=No+Image';
                            $isFavorite = in_array($ad['ad_id'], $savedAdIds);
                            ?>
                            <button class="favorite-btn <?php echo $isFavorite ? 'active' : ''; ?>" data-id="<?php echo $ad['ad_id']; ?>" onclick="toggleFavorite(event, this)" title="Add to Favorites">
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                </svg>
                            </button>
                            <img src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($ad['title']); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: var(--border-radius) var(--border-radius) 0 0;">

                            <div style="padding: 1.5rem;">
                                <div style="font-size: 1.25rem; font-weight: 700; color: var(--primary-color); margin-bottom: 0.5rem;">Rs. <?php echo number_format($ad['price'], 2); ?> / month</div>
                                <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($ad['title']); ?></h3>
                                <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem; display: flex; gap: 0.5rem; flex-direction: column;">
                                    <span>📍 <?php echo htmlspecialchars($ad['location']); ?></span>
                                    <span>🛏️ <?php echo htmlspecialchars($ad['room_type']); ?> | 👤 <?php echo htmlspecialchars($ad['gender_preference']); ?></span>
                                </div>
                                <a href="listing.php?id=<?php echo $ad['ad_id']; ?>" class="btn btn-primary" style="display: block; width: 100%;">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card">
                    <p>No boarding places match your criteria. Try adjusting the filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
