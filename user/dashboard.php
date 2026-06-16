<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}
require_once '../includes/db.php';

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle profile update
$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);

    if (!empty($fullName) && !empty($phone)) {
        $updateStmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE user_id = ?");
        if ($updateStmt->execute([$fullName, $phone, $_SESSION['user_id']])) {
            $msg = "Profile updated successfully.";
            $_SESSION['full_name'] = $fullName;
            $user['full_name'] = $fullName;
            $user['phone'] = $phone;
        } else {
            $msg = "Failed to update profile.";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container" style="margin-top: 2rem; display: flex; gap: 2rem; flex-wrap: wrap;">
    <!-- Sidebar -->
    <div style="flex: 1; min-width: 250px; max-width: 300px;">
        <div class="card">
            <h3 style="margin-bottom: 1rem;">Navigation</h3>
            <ul style="display: flex; flex-direction: column; gap: 0.5rem;">
                <li><a href="dashboard.php" style="display: block; padding: 0.5rem; background: var(--background-light); border-radius: 5px; font-weight: bold; color: var(--primary-color);">My Profile</a></li>
                <li><a href="saved.php" style="display: block; padding: 0.5rem; color: var(--text-dark);">Saved Listings</a></li>
                <li><a href="../logout.php" style="display: block; padding: 0.5rem; color: var(--danger);">Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div style="flex: 3;">
        <div class="card">
            <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Manage your profile information.</p>

            <?php if($msg): ?>
                <div class="alert alert-success"><?php echo $msg; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background-color: var(--background-light);">
                    <small style="color: var(--text-muted);">Email address cannot be changed.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
