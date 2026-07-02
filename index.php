<?php //PHP code eka armba krana tag eka
session_start(); // Session eka start karanawa. Login userge details session walin gannawa. 
require_once 'includes/db.php'; // Database connection file eka include karanawa.

// Fetch recent approved ads
$stmt = $pdo->query("SELECT a.*, (SELECT image_path FROM ad_images WHERE ad_id = a.ad_id LIMIT 1) as cover_image FROM advertisements a WHERE status = 'approved' ORDER BY created_at DESC LIMIT 3");
//Database eke approve wu new ads 3 cover image  SQL query eka run krnwa.
$recentAds = $stmt->fetchAll(); //Query eken labuna siyalu records array ekta ganwa.
$savedAdIds = [];
if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'user') //user login welad , role eka userkyla  check krnwa.
{
    $stmt_saved = $pdo->prepare("SELECT ad_id FROM saved_listings WHERE user_id = ?");
    //Login user savekrapu ads gana SQL query eka prepare krnwa
    $stmt_saved->execute([$_SESSION['user_id']]);
    //Logged user ge user_id eka query ekata yodala execute krnwa
    $savedAdIds = $stmt_saved->fetchAll(PDO::FETCH_COLUMN);
    //Save krapu  ad IDs withrak array ekata ganwa.
}
?>

<?php include 'includes/header.php'; ?>

<style>
    .hero {
        background: linear-gradient(135deg, rgba(67, 97, 238, 0.9), rgba(247, 37, 133, 0.9)), url('https://images.unsplash.com/photo-1570975640108-2292d83390ff?q=80&w=1022&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') center/cover;
        color: white;
        padding: 6rem 1rem;
        text-align: center;
        border-radius: 0 0 2rem 2rem;
        /*Hero section eke corners round krnwa*/
        margin-bottom: 3rem;
        /*Hero section ekata yatin space space ekak denwa*/
    }

    .hero h1 {
        font-size: 3.5rem;
        margin-bottom: 1rem;
        /*Heading ekata yatin space ekak denwa*/
        color: white;
    }

    .hero p {
        font-size: 1.2rem;
        max-width: 600px;
        margin: 0 auto 2rem;
        opacity: 0.9;
        /*Text tikak transparent krnwa*/
    }

    .search-bar-container {
        background: white;
        padding: 1rem;
        border-radius: var(--border-radius);
        /*Rounded corners.*/
        max-width: 800px;
        margin: 0 auto;
        /*Search bar eka center krnwa*/
        box-shadow: var(--shadow-lg);
        /*Shadow effect hadnwa*/
        display: flex;
        /*Elements row  arrange */
        gap: 1rem;
    }

    .search-bar-container form {
        display: flex;
        /*Input, Select, Button eka peliyaka penanwa*/
        width: 100%;
        /*Entire width*/
        gap: 1rem;
        /*Form elements athara gap.*/
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
        overflow: visible;
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
        border-radius: var(--border-radius) var(--border-radius) 0 0;
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
<!-- home_page-->
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
<!-- home_page why section-->

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

<!--Home page view All ads-->
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
                    $isFavorite = in_array($ad['ad_id'], $savedAdIds);
                    ?>
                    <!--favorite button-->
                    <button class="favorite-btn <?php echo $isFavorite ? 'active' : ''; ?>" data-id="<?php echo $ad['ad_id']; ?>" onclick="toggleFavorite(event, this)" title="Add to Favorites">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                        </svg>
                    </button>
                    <!--end favorite button-->
                    <!--image details-->
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