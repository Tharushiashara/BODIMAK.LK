<?php
session_start();
require_once 'includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: search.php");
    exit();
}

$ad_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT a.*, u.full_name, u.email, u.phone FROM advertisements a JOIN users u ON a.seller_id = u.user_id WHERE a.ad_id = ? AND a.status = 'approved'");
$stmt->execute([$ad_id]);
$ad = $stmt->fetch();

if (!$ad) {
    echo "<div style='text-align:center; padding: 5rem;'><h2>Listing not found or pending approval.</h2><a href='search.php'>Back to Search</a></div>";
    exit();
}

$stmt_img = $pdo->prepare("SELECT image_path FROM ad_images WHERE ad_id = ?");
$stmt_img->execute([$ad_id]);
$images = $stmt_img->fetchAll(PDO::FETCH_COLUMN);

// Handle save action
$is_saved = false;
if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'user') {
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM saved_listings WHERE user_id = ? AND ad_id = ?");
    $stmt_check->execute([$_SESSION['user_id'], $ad_id]);
    if ($stmt_check->fetchColumn() > 0) $is_saved = true;

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_listing'])) {
        if (!$is_saved) {
            $stmt_save = $pdo->prepare("INSERT IGNORE INTO saved_listings (user_id, ad_id) VALUES (?, ?)");
            $stmt_save->execute([$_SESSION['user_id'], $ad_id]);
            $is_saved = true;
        } else {
            $stmt_del = $pdo->prepare("DELETE FROM saved_listings WHERE user_id = ? AND ad_id = ?");
            $stmt_del->execute([$_SESSION['user_id'], $ad_id]);
            $is_saved = false;
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container" style="margin-top: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1><?php echo htmlspecialchars($ad['title']); ?></h1>
        <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary-color);">Rs. <?php echo number_format($ad['price'], 2); ?> <span style="font-size:1rem;color:var(--text-muted);font-weight:normal;">/ month</span></div>
    </div>

    <!-- Image Gallery (Simple) -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 2rem; height: 400px;">
        <div style="height: 100%;">
            <?php $main_img = !empty($images) ? $images[0] : 'https://via.placeholder.com/800x600?text=No+Image'; ?>
            <img src="<?php echo htmlspecialchars($main_img); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--border-radius);">
        </div>
        <div style="display: flex; flex-direction: column; gap: 1rem; height: 100%;">
            <?php for ($i = 1; $i < min(3, max(2, count($images))); $i++): ?>
                <?php $sub_img = isset($images[$i]) ? $images[$i] : 'https://via.placeholder.com/400x300?text=Image'; ?>
                <img src="<?php echo htmlspecialchars($sub_img); ?>" style="width: 100%; height: calc(50% - 0.5rem); object-fit: cover; border-radius: var(--border-radius);">
            <?php endfor; ?>
        </div>
    </div>

    <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
        <!-- Main Content -->
        <div style="flex: 2; min-width: 300px;">
            <div class="card">
                <h2>Description</h2>
                <p style="white-space: pre-line; color: var(--text-dark);"><?php echo htmlspecialchars($ad['description']); ?></p>

                <h3 style="margin-top: 2rem;">Details</h3>
                <ul style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; color: var(--text-dark);">
                    <li><strong>📍 Location:</strong> <?php echo htmlspecialchars($ad['location']); ?></li>
                    <li><strong>🏠 Address:</strong> <?php echo htmlspecialchars($ad['address']); ?></li>
                    <li><strong>🛏️ Room Type:</strong> <?php echo htmlspecialchars($ad['room_type']); ?></li>
                    <li><strong>🚻 Gender Preference:</strong> <?php echo htmlspecialchars($ad['gender_preference']); ?></li>
                </ul>

                <h3 style="margin-top: 2rem;">Facilities</h3>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <?php
                    $facilities = explode(',', $ad['facilities']);
                    foreach ($facilities as $fac):
                        $fac = trim($fac);
                        if (!empty($fac)):
                    ?>
                            <span style="background: var(--background-light); padding: 0.5rem 1rem; border-radius: 20px; border: 1px solid var(--border-color);"><?php echo htmlspecialchars($fac); ?></span>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
            </div>
        </div>

        <!-- Sidebar / Contact -->
        <div style="flex: 1; min-width: 300px;">
            <div class="card" style="position: sticky; top: 100px;">
                <h3 style="margin-bottom: 1.5rem;">Contact Seller</h3>


                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                    <div style="width: 50px; height: 50px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold;">
                        <?php echo strtoupper(substr($ad['full_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <div style="font-weight: bold; font-size: 1.1rem;"><?php echo htmlspecialchars($ad['full_name']); ?></div>
                        <div style="color: var(--text-muted); font-size: 0.9rem;">Boarding Owner</div>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <p style="margin-bottom: 0.5rem;"><strong>📞 Phone:</strong> <a href="tel:<?php echo htmlspecialchars($ad['contact_no']); ?>"><?php echo htmlspecialchars($ad['contact_no']); ?></a></p>
                    <p><strong>✉️ Email:</strong> <a href="mailto:<?php echo htmlspecialchars($ad['email']); ?>"><?php echo htmlspecialchars($ad['email']); ?></a></p>
                </div>



            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>