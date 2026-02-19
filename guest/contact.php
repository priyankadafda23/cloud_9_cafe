<?php
$pageTitle = 'Cloud 9 Cafe | Contact';

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers/functions.php';

// Initialize variables
$successMessage = '';
$errorMessage = '';
$formData = [
    'name' => '',
    'email' => '',
    'subject' => 'Website Inquiry',
    'message' => ''
];
$fieldErrors = [
    'name' => '',
    'email' => '',
    'message' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $formData['name'] = trim((string) ($_POST['name'] ?? ''));
    $formData['email'] = strtolower(trim((string) ($_POST['email'] ?? '')));
    $formData['subject'] = trim((string) ($_POST['subject'] ?? 'Website Inquiry'));
    $formData['message'] = trim((string) ($_POST['message'] ?? ''));
    
    // Validation
    if ($formData['name'] === '') {
        $fieldErrors['name'] = 'Please enter your name.';
    } elseif (strlen($formData['name']) < 2) {
        $fieldErrors['name'] = 'Name must be at least 2 characters.';
    } elseif (strlen($formData['name']) > 100) {
        $fieldErrors['name'] = 'Name must not exceed 100 characters.';
    }
    
    if ($formData['email'] === '') {
        $fieldErrors['email'] = 'Please enter your email address.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['email'] = 'Please enter a valid email address.';
    } elseif (strlen($formData['email']) > 190) {
        $fieldErrors['email'] = 'Email must not exceed 190 characters.';
    }
    
    if ($formData['message'] === '') {
        $fieldErrors['message'] = 'Please enter your message.';
    } elseif (strlen($formData['message']) < 10) {
        $fieldErrors['message'] = 'Message must be at least 10 characters.';
    } elseif (strlen($formData['message']) > 1000) {
        $fieldErrors['message'] = 'Message must not exceed 1000 characters.';
    }
    
    // If no errors, save to database
    if (!array_filter($fieldErrors)) {
        try {
            // Check if user is logged in
            $userId = null;
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!empty($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
            }
            
            $stmt = $pdo->prepare(
                'INSERT INTO contact_messages (user_id, name, email, subject, message, status, created_at) 
                 VALUES (:user_id, :name, :email, :subject, :message, :status, NOW())'
            );
            
            $stmt->execute([
                ':user_id' => $userId,
                ':name' => $formData['name'],
                ':email' => $formData['email'],
                ':subject' => $formData['subject'] ?: 'Website Inquiry',
                ':message' => $formData['message'],
                ':status' => 'new'
            ]);
            
            $successMessage = 'Thank you for your message! We will get back to you within 24 hours.';
            
            // Clear form after successful submission
            $formData = [
                'name' => '',
                'email' => '',
                'subject' => 'Website Inquiry',
                'message' => ''
            ];
            
        } catch (Throwable $e) {
            $errorMessage = 'Sorry, we could not send your message. Please try again later.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<nav class="navbar navbar-expand-lg navbar-light sticky-top cafe-navbar" aria-label="Primary navigation">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/cloud_9_cafe/guest/index.php">
            <span class="brand-mark">C9</span>
            <span class="brand-text">Cloud 9 Cafe</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#guestNavbar" aria-controls="guestNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="guestNavbar">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="/cloud_9_cafe/guest/index.php#home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="/cloud_9_cafe/guest/menu.php">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="/cloud_9_cafe/guest/index.php#about-preview">About</a></li>
                <li class="nav-item"><a class="nav-link active" aria-current="page" href="/cloud_9_cafe/guest/contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link nav-action" href="/cloud_9_cafe/guest/auth/login.php">Login</a></li>
                <li class="nav-item"><a class="btn btn-cafe-primary btn-sm" href="/cloud_9_cafe/guest/auth/register.php">Register</a></li>
            </ul>
        </div>
    </div>
</nav>

<section class="contact-hero">
    <div class="container">
        <div class="contact-hero-content text-center text-lg-start">
            <span class="section-kicker">Get In Touch</span>
            <h1>We would love to hear from you</h1>
            <p>Questions, feedback, or catering inquiries. Send us a message and our team will get back shortly.</p>
        </div>
    </div>
</section>

<section class="contact-section section-space pt-0">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="contact-form-card">
                    <div class="contact-card-head">
                        <h2>Send a Message</h2>
                        <p>Fill out the form below and we will respond within 24 hours.</p>
                    </div>

                    <?php if ($successMessage !== ''): ?>
                        <div class="alert alert-success border-0" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <?php echo e($successMessage); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($errorMessage !== ''): ?>
                        <div class="alert alert-danger border-0" role="alert">
                            <i class="bi bi-exclamation-circle-fill me-2"></i>
                            <?php echo e($errorMessage); ?>
                        </div>
                    <?php endif; ?>

                    <form id="contactForm" class="row g-3" method="post" action="/cloud_9_cafe/guest/contact.php" novalidate>
                        <div class="col-md-6">
                            <label for="contactName" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control contact-input <?php echo $fieldErrors['name'] !== '' ? 'is-invalid' : ''; ?>" 
                                   id="contactName" 
                                   name="name" 
                                   maxlength="100" 
                                   autocomplete="name" 
                                   value="<?php echo e($formData['name']); ?>"
                                   required>
                            <div class="invalid-feedback" id="contactNameError">
                                <?php echo e($fieldErrors['name']); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="contactEmail" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" 
                                   class="form-control contact-input <?php echo $fieldErrors['email'] !== '' ? 'is-invalid' : ''; ?>" 
                                   id="contactEmail" 
                                   name="email" 
                                   maxlength="190" 
                                   autocomplete="email" 
                                   value="<?php echo e($formData['email']); ?>"
                                   required>
                            <div class="invalid-feedback" id="contactEmailError">
                                <?php echo e($fieldErrors['email']); ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="contactMessage" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control contact-input <?php echo $fieldErrors['message'] !== '' ? 'is-invalid' : ''; ?>" 
                                      id="contactMessage" 
                                      name="message" 
                                      rows="6" 
                                      maxlength="1000" 
                                      required><?php echo e($formData['message']); ?></textarea>
                            <div class="invalid-feedback" id="contactMessageError">
                                <?php echo e($fieldErrors['message']); ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="contact-hint">Minimum 10 characters.</small>
                                <small class="contact-counter" id="messageCounter"><?php echo strlen($formData['message']); ?>/1000</small>
                            </div>
                        </div>

                        <div class="col-12 d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3 mt-2">
                            <button type="submit" class="btn btn-cafe-primary btn-lg">
                                <i class="bi bi-send me-2"></i>Send Message
                            </button>
                            <small class="contact-hint mb-0">Your information is used only to reply to your message.</small>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="contact-info-card mb-4">
                    <h3>Contact Information</h3>
                    <ul class="list-unstyled mb-0">
                        <li><i class="bi bi-geo-alt"></i><span>42 Brew Lane, Downtown, New York</span></li>
                        <li><i class="bi bi-telephone"></i><span>+1 (555) 019-9090</span></li>
                        <li><i class="bi bi-envelope"></i><span>hello@cloud9cafe.com</span></li>
                        <li><i class="bi bi-clock"></i><span>Mon - Sun: 8:00 AM - 10:00 PM</span></li>
                    </ul>
                    <div class="contact-social">
                        <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                    </div>
                </div>

                <div class="map-placeholder-card">
                    <div class="map-placeholder-overlay">
                        <i class="bi bi-geo-alt-fill"></i>
                        <h3>Visit Us</h3>
                        <p>We are located in the heart of downtown. Come visit us for the best coffee experience.</p>
                        <a class="btn btn-cafe-outline btn-sm" href="https://maps.google.com/?q=42+Brew+Lane+Downtown" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Open Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Character counter for message
    document.addEventListener('DOMContentLoaded', function() {
        const messageInput = document.getElementById('contactMessage');
        const messageCounter = document.getElementById('messageCounter');
        
        if (messageInput && messageCounter) {
            messageInput.addEventListener('input', function() {
                messageCounter.textContent = this.value.length + '/1000';
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
