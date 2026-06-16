<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BODIMAK.LK - Find Your Perfect Boarding</title>
    <!-- <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"> -->
    <link rel="stylesheet" href="/BODIMAK.LK/assets/css/style.css">
</head>

<body>
    <header class="header">
        <div class="container navbar">
            <a href="/BODIMAK.LK/index.php" class="logo">BODIMAK<span>.LK</span></a>

            <button class="mobile-menu-btn" style="display:none; background:none; border:none; font-size:1.5rem; cursor:pointer;">☰</button>

            <nav class="nav-links">
                <a href="/BODIMAK.LK/index.php">Home</a>
                <a href="/BODIMAK.LK/search.php">Search</a>
                <a href="/BODIMAK.LK/about.php">About</a>
                <a href="/BODIMAK.LK/contact.php">Contact</a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <a href="/BODIMAK.LK/admin/dashboard.php">Dashboard</a>
                    <?php elseif ($_SESSION['role'] == 'seller'): ?>
                        <a href="/BODIMAK.LK/seller/dashboard.php">Dashboard</a>
                    <?php else: ?>
                        <a href="/BODIMAK.LK/user/dashboard.php">Dashboard</a>
                    <?php endif; ?>
                    <a href="/BODIMAK.LK/logout.php" class="btn btn-outline" style="padding: 0.5rem 1rem;">Logout</a>
                <?php else: ?>
                    <a href="/BODIMAK.LK/login.php">Login</a>
                    <a href="/BODIMAK.LK/register.php" class="btn btn-primary" style="padding: 0.5rem 1rem; color:white;">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main>