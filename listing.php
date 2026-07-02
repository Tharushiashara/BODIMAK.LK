<?php
session_start();
require_once 'includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: search.php");
    exit();
}

$ad_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT a.*, u.full_name, u.email, u.phone FROM advertisements a JOIN users u ON a.seller_id = u.user_id WHERE a.ad_id = ?");
$stmt->execute([$ad_id]);
$ad = $stmt->fetch();

$can_view = false;
if ($ad) {
    if ($ad['status'] === 'approved') {
        $can_view = true;
    } elseif (isset($_SESSION['user_id'])) {
        if ($_SESSION['role'] === 'admin' || $_SESSION['user_id'] == $ad['seller_id']) {
            $can_view = true;
        }
    }
}

if (!$can_view) {
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
        <h1>
            <?php echo htmlspecialchars($ad['title']); ?>
            <?php if ($ad['status'] !== 'approved'): ?>
                <span style="font-size: 0.9rem; margin-left: 1rem; vertical-align: middle; padding: 0.25rem 0.75rem; border-radius: 20px; color: white; background: <?php echo $ad['status'] == 'rejected' ? 'var(--danger)' : 'var(--warning)'; ?>; display: inline-block; font-weight: 600;">
                    <?php echo ucfirst(htmlspecialchars($ad['status'])); ?>
                </span>
            <?php endif; ?>
        </h1>
        <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary-color);">Rs. <?php echo number_format($ad['price'], 2); ?> <span style="font-size:1rem;color:var(--text-muted);font-weight:normal;">/ month</span></div>
    </div>

    <!-- Image Gallery (Modern Slider & Thumbnails) -->
    <div class="gallery-container" style="margin-bottom: 2rem;">
        <!-- Main Image Stage -->
        <div class="gallery-main" style="position: relative; height: 450px; background: #000; border-radius: var(--border-radius); overflow: hidden; box-shadow: var(--shadow-md);">
            <?php if (!empty($images)): ?>
                <?php foreach ($images as $index => $img): ?>
                    <img class="gallery-slide" src="<?php echo htmlspecialchars($img); ?>" style="width: 100%; height: 100%; object-fit: contain; position: absolute; top: 0; left: 0; opacity: <?php echo $index === 0 ? '1' : '0'; ?>; transition: opacity 0.4s ease-in-out; z-index: <?php echo $index === 0 ? '2' : '1'; ?>;">
                <?php endforeach; ?>

                <!-- Navigation Arrows -->
                <button class="gallery-nav-btn prev-btn" onclick="changeSlide(-1)" style="position: absolute; top: 50%; left: 1rem; transform: translateY(-50%); background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(5px); border: none; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: white; font-size: 1.5rem; transition: var(--transition); z-index: 10; font-weight: bold; text-shadow: 0 1px 2px rgba(0,0,0,0.5); box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                    &#10094;
                </button>
                <button class="gallery-nav-btn next-btn" onclick="changeSlide(1)" style="position: absolute; top: 50%; right: 1rem; transform: translateY(-50%); background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(5px); border: none; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: white; font-size: 1.5rem; transition: var(--transition); z-index: 10; font-weight: bold; text-shadow: 0 1px 2px rgba(0,0,0,0.5); box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                    &#10095;
                </button>

                <!-- Count Indicator -->
                <div class="gallery-indicator" style="position: absolute; bottom: 1rem; right: 1rem; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(5px); color: white; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.9rem; font-weight: 600; z-index: 10; letter-spacing: 1px;">
                    <span id="current-slide-num">1</span> / <?php echo count($images); ?>
                </div>
            <?php else: ?>
                <img src="https://via.placeholder.com/800x600?text=No+Image" style="width: 100%; height: 100%; object-fit: cover;">
            <?php endif; ?>
        </div>

        <!-- Thumbnails Strip -->
        <?php if (count($images) > 1): ?>
            <div class="gallery-thumbnails" style="display: flex; gap: 0.75rem; margin-top: 0.75rem; overflow-x: auto; padding: 0.5rem 0; scrollbar-width: thin;">
                <?php foreach ($images as $index => $img): ?>
                    <div class="thumbnail-wrapper" onclick="setSlide(<?php echo $index; ?>)" style="flex: 0 0 100px; height: 70px; border-radius: 6px; overflow: hidden; cursor: pointer; border: 3px solid <?php echo $index === 0 ? 'var(--primary-color)' : 'transparent'; ?>; opacity: <?php echo $index === 0 ? '1' : '0.6'; ?>; transition: var(--transition); box-shadow: var(--shadow-sm);">
                        <img src="<?php echo htmlspecialchars($img); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Gallery Script -->
    <script>
        let currentSlideIndex = 0;
        const slides = document.querySelectorAll('.gallery-slide');
        const thumbnails = document.querySelectorAll('.thumbnail-wrapper');
        const currentSlideNumSpan = document.getElementById('current-slide-num');

        function updateGallery() {
            slides.forEach((slide, idx) => {
                if (idx === currentSlideIndex) {
                    slide.style.opacity = '1';
                    slide.style.zIndex = '2';
                } else {
                    slide.style.opacity = '0';
                    slide.style.zIndex = '1';
                }
            });

            thumbnails.forEach((thumb, idx) => {
                if (idx === currentSlideIndex) {
                    thumb.style.borderColor = 'var(--primary-color)';
                    thumb.style.opacity = '1';
                } else {
                    thumb.style.borderColor = 'transparent';
                    thumb.style.opacity = '0.6';
                }
            });

            if (currentSlideNumSpan) {
                currentSlideNumSpan.textContent = currentSlideIndex + 1;
            }
        }

        function changeSlide(direction) {
            if (slides.length === 0) return;
            currentSlideIndex = (currentSlideIndex + direction + slides.length) % slides.length;
            updateGallery();
        }

        function setSlide(index) {
            currentSlideIndex = index;
            updateGallery();
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                changeSlide(-1);
            } else if (e.key === 'ArrowRight') {
                changeSlide(1);
            }
        });

        // Hover effects for navigation buttons
        const navBtns = document.querySelectorAll('.gallery-nav-btn');
        navBtns.forEach(btn => {
            btn.addEventListener('mouseenter', () => {
                btn.style.backgroundColor = 'var(--primary-color)';
                btn.style.transform = 'translateY(-50%) scale(1.1)';
            });
            btn.addEventListener('mouseleave', () => {
                btn.style.backgroundColor = 'rgba(255, 255, 255, 0.2)';
                btn.style.transform = 'translateY(-50%) scale(1)';
            });
        });
    </script>

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
                <!-- Favorite Button -->
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user'): ?>
                    <button class="detail-fav-btn <?php echo $is_saved ? 'active' : ''; ?>" data-id="<?php echo $ad['ad_id']; ?>" onclick="toggleFavorite(event, this)">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                        </svg>
                        <span class="btn-text"><?php echo $is_saved ? 'Saved to Favorites' : 'Add to Favorites'; ?></span>
                    </button>
                <?php elseif (!isset($_SESSION['user_id'])): ?>
                    <button class="detail-fav-btn" data-id="<?php echo $ad['ad_id']; ?>" onclick="toggleFavorite(event, this)">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                        </svg>
                        <span class="btn-text">Add to Favorites</span>
                    </button>
                <?php endif; ?>

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