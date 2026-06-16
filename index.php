<?php
session_start(); //login details
require_once 'includes/db.php';

// Fetch recent approved ads
$stmt = $pdo->query("SELECT a.*, (SELECT image_path FROM ad_images WHERE ad_id = a.ad_id LIMIT 1) as cover_image FROM advertisements a WHERE status = 'approved' ORDER BY created_at DESC LIMIT 6");
$recentAds = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<style>
    .hero {
        background: linear-gradient(135deg, rgba(67, 97, 238, 0.9), rgba(247, 37, 133, 0.9)), url('https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&q=80&w=2000') center/cover;
        color: white;
        padding: 6rem 1rem;
        text-align: center;
        border-radius: 0 0 2rem 2rem;
        margin-bottom: 3rem;
    }

    .hero h1 {
        font-size: 3.5rem;
        margin-bottom: 1rem;
        color: white;
    }

    .hero p {
        font-size: 1.2rem;
        max-width: 600px;
        margin: 0 auto 2rem;
        opacity: 0.9;
    }

    .search-bar-container {
        background: white;
        padding: 1rem;
        border-radius: var(--border-radius);
        max-width: 800px;
        margin: 0 auto;
        box-shadow: var(--shadow-lg);
        display: flex;
        gap: 1rem;
    }

    .search-bar-container form {
        display: flex;
        width: 100%;
        gap: 1rem;
    }

    .search-bar-container input,
    .search-bar-container select {
        flex: 1;
        border: 1px solid var(--border-color);
        padding: 0.75rem;
        border-radius: 5px;
        font-family: inherit;
    }

    .features-section {
        padding: 4rem 0;
        text-align: center;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .feature-card {
        padding: 2rem;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }

    .listings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .listing-card {
        background: white;
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        display: flex;
        flex-direction: column;
    }

    .listing-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }

    .listing-img {
        height: 200px;
        width: 100%;
        object-fit: cover;
    }

    .listing-content {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .listing-price {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }

    .listing-title {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }

    .listing-meta {
        color: var(--text-muted);
        font-size: 0.9rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .listing-footer {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    @media (max-width: 768px) {
        .search-bar-container form {
            flex-direction: column;
        }

        .hero h1 {
            font-size: 2.5rem;
        }
    }
</style>

<div class="hero">
    <div class="container">
        <h1>Find Your Perfect Boarding Place</h1>
        <p>Discover safe, comfortable, and affordable boarding places near your university or workplace in Sri Lanka.</p>

        <div class="search-bar-container">
            <form action="search.php" method="GET">
                <input type="text" name="location" placeholder="Enter location (e.g., Dehiwala)">
                <select name="type">
                    <option value="">Any Room Type</option>
                    <option value="Single">Single Room</option>
                    <option value="Shared">Shared Room</option>
                    <option value="Annex">Annex</option>
                </select>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
    </div>
</div>

<div class="container features-section">
    <h2>Why Choose BODIMAK.LK?</h2>
    <div class="features-grid">
        <div class="feature-card">
            <h3>📍 Verified Locations</h3>
            <p>All boarding places are reviewed to ensure accurate locations and descriptions.</p>
        </div>
        <div class="feature-card">
            <h3>💰 Best Prices</h3>
            <p>Find options that fit your budget, from economical shared rooms to premium annexes.</p>
        </div>
        <div class="feature-card">
            <h3>🔒 Safe & Secure</h3>
            <p>Connect directly with trusted boarding owners through our secure platform.</p>
        </div>
    </div>
</div>

<div class="container" style="margin-bottom: 4rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2>Recently Added Boardings</h2>
        <a href="search.php" class="btn btn-outline">View All</a>
    </div>

    <div class="listings-grid">
        <?php if (count($recentAds) > 0): ?>
            <?php foreach ($recentAds as $ad): ?>
                <div class="listing-card">
                    <?php
                    $imgPath = $ad['cover_image'] ? htmlspecialchars($ad['cover_image']) : 'https://via.placeholder.com/400x300?text=No+Image';
                    ?>
                    <img src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($ad['title']); ?>" class="listing-img">
                    <div class="listing-content">
                        <div class="listing-price">Rs. <?php echo number_format($ad['price'], 2); ?> / month</div>
                        <h3 class="listing-title"><?php echo htmlspecialchars($ad['title']); ?></h3>
                        <div class="listing-meta">
                            <span>📍 <?php echo htmlspecialchars($ad['location']); ?></span>
                            <span>• 🛏️ <?php echo htmlspecialchars($ad['room_type']); ?></span>
                        </div>
                        <div class="listing-footer">
                            <span style="font-size: 0.85rem; color: var(--text-muted);">
                                <?php echo htmlspecialchars($ad['gender_preference']); ?> Only
                            </span>
                            <a href="listing.php?id=<?php echo $ad['ad_id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No boarding places found. Check back later!</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>