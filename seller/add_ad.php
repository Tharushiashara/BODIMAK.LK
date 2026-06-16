<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}
require_once '../includes/db.php';

$msg = '';
$error = '';

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
            
            $stmt = $pdo->prepare("INSERT INTO advertisements (seller_id, title, description, location, address, room_type, price, facilities, gender_preference, contact_no, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $location, $address, $room_type, $price, $facilities, $gender, $contact]);
            
            $ad_id = $pdo->lastInsertId();
            
            // Handle image uploads
            if(isset($_FILES['images']) && count($_FILES['images']['name']) > 0 && $_FILES['images']['name'][0] != '') {
                $upload_dir = '../uploads/ads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_count = count($_FILES['images']['name']);
                for($i = 0; $i < min(5, $file_count); $i++) { // Max 5 images
                    if($_FILES['images']['error'][$i] == 0) {
                        $file_ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                        
                        if(in_array($file_ext, $allowed)) {
                            $new_name = uniqid('ad_') . '_' . time() . '.' . $file_ext;
                            $target_file = $upload_dir . $new_name;
                            
                            if(move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                                $db_path = 'uploads/ads/' . $new_name;
                                $img_stmt = $pdo->prepare("INSERT INTO ad_images (ad_id, image_path) VALUES (?, ?)");
                                $img_stmt->execute([$ad_id, $db_path]);
                            }
                        }
                    }
                }
            }
            
            $pdo->commit();
            $msg = "Advertisement submitted successfully! It is now pending admin approval.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to submit advertisement. Please try again.";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container" style="margin-top: 2rem; display: flex; gap: 2rem; flex-wrap: wrap;">
    <!-- Sidebar -->
    <div style="flex: 1; min-width: 250px; max-width: 300px;">
        <div class="card">
            <h3 style="margin-bottom: 1rem;">Seller Menu</h3>
            <ul style="display: flex; flex-direction: column; gap: 0.5rem;">
                <li><a href="dashboard.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Overview</a></li>
                <li><a href="add_ad.php" style="display: block; padding: 0.5rem; background: var(--background-light); border-radius: 5px; font-weight: bold; color: var(--primary-color);">Post Advertisement</a></li>
                <li><a href="my_ads.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">My Advertisements</a></li>
                <li><a href="../logout.php" style="display: block; padding: 0.5rem; color: var(--danger);">Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div style="flex: 3;">
        <div class="card">
            <h2 style="margin-bottom: 1.5rem;">Post New Advertisement</h2>
            
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
                    <input type="text" name="title" class="form-control" required placeholder="e.g. Spacious Annex in Dehiwala">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description *</label>
                    <textarea name="description" class="form-control" rows="5" required placeholder="Describe the place, rules, nearby locations..."></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">City/Area *</label>
                        <input type="text" name="location" class="form-control" required placeholder="e.g. Dehiwala">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Monthly Price (Rs.) *</label>
                        <input type="number" name="price" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Full Address *</label>
                    <input type="text" name="address" class="form-control" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Room Type *</label>
                        <select name="room_type" class="form-control" required>
                            <option value="Single">Single Room</option>
                            <option value="Shared">Shared Room</option>
                            <option value="Annex">Annex</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Gender Preference *</label>
                        <select name="gender" class="form-control" required>
                            <option value="Any">Any</option>
                            <option value="Male">Male Only</option>
                            <option value="Female">Female Only</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Number *</label>
                        <input type="text" name="contact" class="form-control" required placeholder="07xxxxxxxx">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Facilities</label>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem;">
                        <label><input type="checkbox" name="facilities[]" value="Wi-Fi"> Wi-Fi</label>
                        <label><input type="checkbox" name="facilities[]" value="Attached Bathroom"> Attached Bathroom</label>
                        <label><input type="checkbox" name="facilities[]" value="Meals Provided"> Meals Provided</label>
                        <label><input type="checkbox" name="facilities[]" value="Kitchen Access"> Kitchen Access</label>
                        <label><input type="checkbox" name="facilities[]" value="Parking"> Parking</label>
                        <label><input type="checkbox" name="facilities[]" value="CCTV"> CCTV</label>
                        <label><input type="checkbox" name="facilities[]" value="Furniture"> Furniture included</label>
                        <label><input type="checkbox" name="facilities[]" value="Water Bill Included"> Water Bill Included</label>
                        <label><input type="checkbox" name="facilities[]" value="Electricity Bill Included"> Electricity Bill Included</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Photos (Up to 5)</label>
                    <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                    <small style="color: var(--text-muted);">First image will be used as the cover photo.</small>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Submit Advertisement</button>
            </form>
            
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
