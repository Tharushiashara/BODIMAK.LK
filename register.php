<?php
session_start();
require_once 'includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role']; // 'user' or 'seller'
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Basic Validation
    if (empty($fullName) || empty($email) || empty($phone) || empty($password) || empty($confirmPassword) || empty($role)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!in_array($role, ['user', 'seller'])) {
        $error = "Invalid role selected.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Email address is already registered.";
        } else {
            // Hash password and insert
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $status = ($role == 'seller') ? 'inactive' : 'active'; // Sellers might need admin approval (optional, setting active for now to ease flow unless strictly needed. Let's make it active as per general flow, Admin can deactivate later. Actually instruction says Admin approve seller reg. Let's set inactive for seller.)

            if ($role == 'seller') {
                $status = 'inactive';
                $successMsg = "Registration successful! Your seller account is pending admin approval.";
            } else {
                $status = 'active';
                $successMsg = "Registration successful! You can now login.";
            }
            try {
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, phone, role, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$fullName, $email, $hashedPassword, $phone, $role, $status]);
                $success = $successMsg;
            } catch (PDOException $e) {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container" style="max-width: 600px; margin: 4rem auto;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 2rem;">Create an Account</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?> <br><a href="login.php">Go to Login</a></div>
        <?php else: ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">I want to register as a:</label>
                    <div style="display: flex; gap: 1rem;">
                        <label style="cursor:pointer;"><input type="radio" name="role" value="user" checked> User (Looking for Boarding)</label>
                        <label style="cursor:pointer;"><input type="radio" name="role" value="seller"> Seller (Boarding Owner)</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required placeholder="John Doe">
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required placeholder="john@example.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" required placeholder="07xxxxxxxx">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Register</button>
            </form>

        <?php endif; ?>

        <p style="text-align: center; margin-top: 1.5rem; color: var(--text-muted);">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>