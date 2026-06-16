<?php
session_start();
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // In a real app, send an email here.
    $success = true;
}
?>
<?php include 'includes/header.php'; ?>
<!--
 <div class="container" style="margin-top: 3rem; margin-bottom: 3rem;"> 
    <div style="display: flex; gap: 2rem; flex-wrap: wrap; justify-content: center;">
        
        <div class="card" style="flex: 1; min-width: 300px; max-width: 500px;">
            <h2>Get in Touch</h2>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Have questions or need help? Fill out the form and we'll get back to you shortly.</p>
            
            <?php if ($success): ?>
                <div class="alert alert-success">Thank you for your message. We will contact you soon.</div>
            <?php else: ?>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Your Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Your Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Send Message</button>
                </form>
            <?php endif; ?>
        </div> -->
<div style="display: flex; justify-content: center;">
    <div class="card" style="flex: 1; min-width: 600px; max-width: 400px; background-color: var(--primary-color); color: white;">
        <h2 style="color: white;">Contact Information</h2>
        <p style="margin-bottom: 2rem; opacity: 0.9;">Feel free to reach out to us directly using the information below.</p>

        <div style="margin-bottom: 1.5rem;">
            <h4 style="color: white; margin-bottom: 0.5rem;"> Address</h4>
            <p style="opacity: 0.9;">Advanced Technological Institute,<br>Dehiwala - SLIATE</p>
        </div>
        <div style="margin-bottom: 1.5rem;">
            <h4 style="color: white; margin-bottom: 0.5rem;"> Phone</h4>
            <p style="opacity: 0.9;">+94 77 123 4567</p>
        </div>
        <div style="margin-bottom: 1.5rem;">
            <h4 style="color: white; margin-bottom: 0.5rem;">✉️ Email</h4>
            <p style="opacity: 0.9;">support@bodimak.lk</p>
        </div>
    </div>

</div>
</div>

<?php include 'includes/footer.php'; ?>