<?php
$title = 'Contact Us - TANTRA GROUP OF INDUSTRIES';
include __DIR__ . '/../layouts/public_header.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message_text = trim($_POST['message'] ?? '');
    
    if ($name && $email && $subject && $message_text) {
        // Log the contact inquiry (in production, send email)
        $message = 'Thank you for your message. We will get back to you shortly.';
        $messageType = 'success';
    } else {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    }
}
?>

<section class="page-hero section">
    <div class="container">
        <div class="page-hero-content">
            <h1>Contact <span class="text-accent">Us</span></h1>
            <p>Get in touch with TANTRA GROUP OF INDUSTRIES. We'd love to hear from you.</p>
        </div>
    </div>
</section>

<section class="contact-section section">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-info">
                <h2>Get In <span class="text-accent">Touch</span></h2>
                <p>Have a question, partnership inquiry, or want to learn more about our companies? Reach out to us.</p>
                
                <div class="contact-details">
                    <div class="contact-item">
                        <span class="contact-icon">&#9993;</span>
                        <div>
                            <h4>Email</h4>
                            <p>contact@tantragroup.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">&#9742;</span>
                        <div>
                            <h4>Phone</h4>
                            <p>+91 (000) 000-0000</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">&#9873;</span>
                        <div>
                            <h4>Headquarters</h4>
                            <p>TANTRA GROUP OF INDUSTRIES<br>Corporate Headquarters, India</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="contact-form-wrapper">
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <form method="POST" action="/contact" class="contact-form">
                    <div class="form-group">
                        <label for="name">Full Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" required placeholder="Your full name">
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" required placeholder="your@email.com">
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject <span class="required">*</span></label>
                        <input type="text" id="subject" name="subject" required placeholder="What is this regarding?">
                    </div>
                    <div class="form-group">
                        <label for="message">Message <span class="required">*</span></label>
                        <textarea id="message" name="message" rows="5" required placeholder="Your message..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../layouts/public_footer.php'; ?>