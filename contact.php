<?php
session_start();
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // In a real app, send an email here.
    $success = true;
}
?>
<?php include 'includes/header.php'; ?>
<?php if ($success): ?>
<?php else: ?>

<?php endif; ?>
<div style="display: flex; justify-content: center;">
    <div class="card" style="flex: 1; min-width: 600px; max-width: 400px; background-color: var(--primary-color); color: white;">
        <h2 style="color: white;">Contact Information</h2>
        <p style="margin-bottom: 2rem; opacity: 0.9;">Feel free to reach out to us directly using the information below.</p>

        <div style="margin-bottom: 1.5rem;">
            <h4 style="color: white; margin-bottom: 0.5rem;"> Address</h4>
            <p style="opacity: 0.9;">188,Veheragam,Moraketiya Road,Embilipitiya.<br></p>
        </div>
        <div style="margin-bottom: 1.5rem;">
            <h4 style="color: white; margin-bottom: 0.5rem;"> Phone</h4>
            <p style="opacity: 0.9;">+94 77 123 4567</p>
        </div>
        <div style="margin-bottom: 1.5rem;">
            <h4 style="color: white; margin-bottom: 0.5rem;">✉️ Email</h4>
            <p style="opacity: 0.9;">asharaadmin@gmail.com</p>
        </div>
    </div>

</div>
</div>

<?php include 'includes/footer.php'; ?>