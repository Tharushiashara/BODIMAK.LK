<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    //(Session ekak) aluthin armba kra nomathi nm armba krai.
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BODIMAK.LK - Find Your Perfect Boarding</title>
    <link rel="stylesheet" href="/BODIMAK.LK/assets/css/style.css">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</head>

<body>
    <header class="header">
        <div class="container navbar">
            <a href="/BODIMAK.LK/index.php" class="logo">BODIMAK<span>.LK</span></a>

            <div class="navbar-actions" style="display: flex; align-items: center; gap: 1.5rem;">
                <nav class="nav-links">
                    <a href="/BODIMAK.LK/index.php">Home</a>
                    <a href="/BODIMAK.LK/search.php">Search</a>
                    <a href="/BODIMAK.LK/about.php">About</a>
                    <a href="/BODIMAK.LK/contact.php">Contact</a>
                    <!-- user (Role)  Dashboard change  -->
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

                <button id="theme-toggle" class="theme-toggle" aria-label="Toggle dark/light mode">
                    <!-- Moon icon (shown in light mode) -->
                    <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                    <!-- Sun icon (shown in dark mode) -->
                    <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21M4.978 4.978l1.591 1.591m10.862 10.862l1.591 1.591M3 12h2.25m13.5 0H21M4.978 19.022l1.591-1.591m10.862-10.862l1.591-1.591M12 7.5a4.5 4.5 0 100 9 4.5 4.5 0 000-9z" />
                    </svg>
                </button>

                <button class="mobile-menu-btn" style="display:none; background:none; border:none; font-size:1.5rem; cursor:pointer;">☰</button>
            </div>
        </div>
    </header>
    <main>