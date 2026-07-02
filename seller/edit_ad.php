<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}
require_once '../includes/db.php';

$msg = '';
$error = '';

if (!isset($_GET['id'])) {
    header("Location: my_ads.php");
    exit();
}

$ad_id = intval($_GET['id']);
$seller_id = $_SESSION['user_id'];

// Fetch the advertisement details
$stmt = $pdo->prepare("SELECT * FROM advertisements WHERE ad_id = ? AND seller_id = ?");
$stmt->execute([$ad_id, $seller_id]);
$ad = $stmt->fetch();

if (!$ad) {
    header("Location: my_ads.php");
    exit();
}

// Fetch existing images
$img_stmt = $pdo->prepare("SELECT * FROM ad_images WHERE ad_id = ?");
$img_stmt->execute([$ad_id]);
$images = $img_stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $address = trim($_POST['address']);
    $room_type = trim($_POST['room_type']);
    $price = trim($_POST['price']);
    $gender = trim($_POST['gender']);
    $contact = trim($_POST['contact']);
    
    // Process facilities array into comma separated string
    $facilities = isset($_POST['facilities']) ? implode(', ', $_POST['facilities']) : '';

    if (empty($title) || empty($description) || empty($location) || empty($price)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $pdo->beginTransaction();
            
            // Update the advertisement (status goes back to 'pending' upon editing)
            $update_stmt = $pdo->prepare("
                UPDATE advertisements 
                SET title = ?, description = ?, location = ?, address = ?, room_type = ?, price = ?, facilities = ?, gender_preference = ?, contact_no = ?, status = 'pending' 
                WHERE ad_id = ? AND seller_id = ?
            ");
            $update_stmt->execute([$title, $description, $location, $address, $room_type, $price, $facilities, $gender, $contact, $ad_id, $seller_id]);

            // Handle deleted images
            if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $del_img_id) {
                    $del_img_id = intval($del_img_id);
                    // Fetch image info to delete file
                    $info_stmt = $pdo->prepare("SELECT image_path FROM ad_images WHERE image_id = ? AND ad_id = ?");
                    $info_stmt->execute([$del_img_id, $ad_id]);
                    $img_info = $info_stmt->fetch();
                    
                    if ($img_info) {
                        $filepath = '../' . $img_info['image_path'];
                        if (file_exists($filepath)) {
                            unlink($filepath);
                        }
                        
                        $delete_img_stmt = $pdo->prepare("DELETE FROM ad_images WHERE image_id = ? AND ad_id = ?");
                        $delete_img_stmt->execute([$del_img_id, $ad_id]);
                    }
                }
            }

            // Get current count of remaining images
            $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM ad_images WHERE ad_id = ?");
            $count_stmt->execute([$ad_id]);
            $current_image_count = $count_stmt->fetchColumn();

            // Handle new image uploads
            if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0 && $_FILES['images']['name'][0] != '') {
                $upload_dir = '../uploads/ads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_count = count($_FILES['images']['name']);
                $allowed_slots = 5 - $current_image_count;
                
                if ($allowed_slots > 0) {
                    for ($i = 0; $i < min($allowed_slots, $file_count); $i++) {
                        if ($_FILES['images']['error'][$i] == 0) {
                            $file_ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                            
                            if (in_array($file_ext, $allowed)) {
                                $new_name = uniqid('ad_') . '_' . time() . '.' . $file_ext;
                                $target_file = $upload_dir . $new_name;
                                
                                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                                    $db_path = 'uploads/ads/' . $new_name;
                                    $img_stmt_insert = $pdo->prepare("INSERT INTO ad_images (ad_id, image_path) VALUES (?, ?)");
                                    $img_stmt_insert->execute([$ad_id, $db_path]);
                                }
                            }
                        }
                    }
                }
            }
            
            $pdo->commit();
            
            // Refresh data
            $stmt = $pdo->prepare("SELECT * FROM advertisements WHERE ad_id = ? AND seller_id = ?");
            $stmt->execute([$ad_id, $seller_id]);
            $ad = $stmt->fetch();
            
            $img_stmt = $pdo->prepare("SELECT * FROM ad_images WHERE ad_id = ?");
            $img_stmt->execute([$ad_id]);
            $images = $img_stmt->fetchAll();

            $msg = "Advertisement updated successfully! It has been submitted for admin approval.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to update advertisement. Please try again.";
        }
    }
}

// Convert facilities to array for matching checkboxes
$existing_facilities = array_map('trim', explode(',', $ad['facilities']));
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
                <li><a href="my_ads.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">My Advertisements</a></li>
                <li><a href="../logout.php" style="display: block; padding: 0.5rem; color: var(--danger);">Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div style="flex: 3;">
        <div class="card">
            <h2 style="margin-bottom: 1.5rem;">Edit Advertisement</h2>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if($msg): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
                <a href="my_ads.php" class="btn btn-primary mt-2">View My Ads</a>
            <?php else: ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Title / Boarding Name *</label>
                    <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($ad['title']); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description *</label>
                    <textarea name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($ad['description']); ?></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">City/Area *</label>
                        <input type="text" name="location" class="form-control" required value="<?php echo htmlspecialchars($ad['location']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Monthly Price (Rs.) *</label>
                        <input type="number" name="price" class="form-control" required value="<?php echo htmlspecialchars($ad['price']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Full Address *</label>
                    <input type="text" name="address" class="form-control" required value="<?php echo htmlspecialchars($ad['address']); ?>">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Room Type *</label>
                        <select name="room_type" class="form-control" required>
                            <option value="Single" <?php echo $ad['room_type'] == 'Single' ? 'selected' : ''; ?>>Single Room</option>
                            <option value="Shared" <?php echo $ad['room_type'] == 'Shared' ? 'selected' : ''; ?>>Shared Room</option>
                            <option value="Annex" <?php echo $ad['room_type'] == 'Annex' ? 'selected' : ''; ?>>Annex</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Gender Preference *</label>
                        <select name="gender" class="form-control" required>
                            <option value="Any" <?php echo $ad['gender_preference'] == 'Any' ? 'selected' : ''; ?>>Any</option>
                            <option value="Male" <?php echo $ad['gender_preference'] == 'Male' ? 'selected' : ''; ?>>Male Only</option>
                            <option value="Female" <?php echo $ad['gender_preference'] == 'Female' ? 'selected' : ''; ?>>Female Only</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Number *</label>
                        <input type="text" name="contact" class="form-control" required value="<?php echo htmlspecialchars($ad['contact_no']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Facilities</label>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem;">
                        <?php
                        $facilities_list = [
                            "Wi-Fi", "Attached Bathroom", "Meals Provided",
                            "Kitchen Access", "Parking", "CCTV",
                            "Furniture", "Water Bill Included", "Electricity Bill Included"
                        ];
                        foreach($facilities_list as $facility) {
                            $checked = in_array($facility, $existing_facilities) ? 'checked' : '';
                            echo "<label><input type='checkbox' name='facilities[]' value='{$facility}' {$checked}> {$facility}</label>";
                        }
                        ?>
                    </div>
                </div>

                <!-- Existing Images Management -->
                <?php if (count($images) > 0): ?>
                <div class="form-group">
                    <label class="form-label">Manage Existing Photos</label>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.5rem; background: var(--background-light); padding: 1rem; border-radius: var(--border-radius);">
                        <?php foreach ($images as $img): ?>
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; border: 1px solid var(--border-color); padding: 0.5rem; border-radius: 5px; background: white;">
                            <img src="../<?php echo htmlspecialchars($img['image_path']); ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;">
                            <label style="font-size: 0.8rem; color: var(--danger); cursor: pointer; display: flex; align-items: center; gap: 0.25rem;">
                                <input type="checkbox" name="delete_images[]" value="<?php echo $img['image_id']; ?>"> Delete
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label">Upload New Photos (Maximum 5 total)</label>
                    <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                    <small style="color: var(--text-muted);">You currently have <?php echo count($images); ?> photos. You can upload up to <?php echo 5 - count($images); ?> more.</small>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <a href="my_ads.php" class="btn btn-outline" style="flex: 1; text-align: center;">Cancel</a>
                    <button type="submit" class="btn btn-primary" style="flex: 2;">Save Changes</button>
                </div>
            </form>
            
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
