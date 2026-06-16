<?php
session_start();
require_once 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];

                if ($user['role'] == 'admin') {
                    header("Location: admin/dashboard.php");
                } elseif ($user['role'] == 'seller') {
                    header("Location: seller/dashboard.php");
                } else {
                    header("Location: user/dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid email/password or account is inactive.";
            }
        } catch (PDOException $e) {
            $error = "Login failed. Please try again later.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container" style="max-width: 500px; margin: 4rem auto;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 2rem;">Welcome Back</h2>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="Enter your email">
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Enter your password">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Login</button>
        </form>
        
        <p style="text-align: center; margin-top: 1.5rem; color: var(--text-muted);">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
