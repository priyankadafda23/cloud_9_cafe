<?php
require_once __DIR__ . '/../../includes/auth/auth_guard.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers/functions.php';

$pageTitle = 'Cloud 9 Cafe | Edit Profile';
$extraCssFiles = ['/cloud_9_cafe/assets/css/user.css'];

$rawUserName = $_SESSION['full_name'] ?? $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Guest';
$displayName = trim((string) $rawUserName);
if ($displayName === '') {
    $displayName = 'Guest';
}

$userId = $_SESSION['user_id'] ?? 0;

// Initialize variables
$successMessage = '';
$errorMessage = '';
$fieldErrors = [
    'full_name' => '',
    'phone' => ''
];

// Fetch current user data
try {
    $stmt = $pdo->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: /cloud_9_cafe/user/index.php');
        exit;
    }
    
    $formData = [
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'phone' => $user['phone'] ?? ''
    ];
} catch (PDOException $e) {
    $errorMessage = 'Unable to load your profile. Please try again.';
    $formData = [
        'full_name' => $displayName,
        'email' => $_SESSION['email'] ?? '',
        'phone' => $_SESSION['phone'] ?? ''
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
    $formData['phone'] = trim((string) ($_POST['phone'] ?? ''));
    
    // Validation
    if ($formData['full_name'] === '') {
        $fieldErrors['full_name'] = 'Full name is required.';
    } elseif (strlen($formData['full_name']) < 2) {
        $fieldErrors['full_name'] = 'Full name must be at least 2 characters.';
    } elseif (strlen($formData['full_name']) > 100) {
        $fieldErrors['full_name'] = 'Full name must not exceed 100 characters.';
    } elseif (!preg_match("/^[A-Za-z .'-]+$/", $formData['full_name'])) {
        $fieldErrors['full_name'] = 'Use letters, spaces, apostrophes, periods, or hyphens only.';
    }
    
    if ($formData['phone'] !== '') {
        if (!preg_match('/^[0-9+()\-\s]+$/', $formData['phone'])) {
            $fieldErrors['phone'] = 'Use digits and + ( ) - only.';
        } else {
            $digits = preg_replace('/\D/', '', $formData['phone']);
            $digitCount = strlen((string) $digits);
            if ($digitCount < 7 || $digitCount > 15) {
                $fieldErrors['phone'] = 'Phone number must contain 7 to 15 digits.';
            }
        }
    }
    
    // If no errors, update database
    if (!array_filter($fieldErrors)) {
        try {
            $updateStmt = $pdo->prepare(
                "UPDATE users 
                 SET full_name = :full_name, phone = :phone, updated_at = NOW() 
                 WHERE id = :id"
            );
            
            $updateStmt->execute([
                ':full_name' => $formData['full_name'],
                ':phone' => $formData['phone'] ?: null,
                ':id' => $userId
            ]);
            
            // Update session
            $_SESSION['full_name'] = $formData['full_name'];
            $_SESSION['user_name'] = $formData['full_name'];
            $_SESSION['username'] = $formData['full_name'];
            $_SESSION['phone'] = $formData['phone'];
            
            $displayName = $formData['full_name'];
            $successMessage = 'Your profile has been updated successfully.';
            
        } catch (PDOException $e) {
            $errorMessage = 'Unable to update your profile. Please try again.';
        }
    }
}

require_once __DIR__ . '/../../includes/layouts/user_header.php';
?>

<section class="user-dashboard-page section-space">
    <div class="container-fluid container-xxl">
        <div class="row g-4">
            <aside class="col-lg-4 col-xl-3">
                <div class="user-sidebar-card">
                    <div class="user-sidebar-head">
                        <span class="user-sidebar-kicker">Cloud 9 Cafe</span>
                        <h2><?php echo e($displayName); ?></h2>
                        <p>Manage your profile, preferences, and account settings.</p>
                    </div>

                    <nav aria-label="User dashboard navigation">
                        <ul class="nav flex-column user-sidebar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="/cloud_9_cafe/user/index.php">
                                    <i class="bi bi-grid"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/cloud_9_cafe/guest/menu.php">
                                    <i class="bi bi-cup-hot"></i>
                                    <span>View Menu</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="/cloud_9_cafe/user/account/profile.php">
                                    <i class="bi bi-person"></i>
                                    <span>Edit Profile</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/cloud_9_cafe/user/account/settings.php">
                                    <i class="bi bi-shield-lock"></i>
                                    <span>Change Password</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link nav-link-danger" href="/cloud_9_cafe/user/logout.php">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Logout</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </aside>

            <div class="col-lg-8 col-xl-9">
                <div class="dashboard-welcome-card">
                    <div>
                        <span class="dashboard-kicker">Account Settings</span>
                        <h1>Edit Profile</h1>
                        <p>Update your personal information and contact details.</p>
                    </div>
                </div>

                <?php if ($successMessage !== ''): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php echo e($successMessage); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($errorMessage !== ''): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        <?php echo e($errorMessage); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-4 mt-1">
                    <div class="col-md-8">
                        <article class="dashboard-action-card h-100">
                            <div class="dashboard-action-icon"><i class="bi bi-person-gear"></i></div>
                            <h3>Profile Information</h3>
                            <p>Update your name and phone number.</p>

                            <form method="POST" action="/cloud_9_cafe/user/account/profile.php" class="mt-4" novalidate>
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control <?php echo $fieldErrors['full_name'] !== '' ? 'is-invalid' : ''; ?>" 
                                           id="full_name" 
                                           name="full_name" 
                                           value="<?php echo e($formData['full_name']); ?>"
                                           maxlength="100"
                                           required>
                                    <div class="invalid-feedback">
                                        <?php echo e($fieldErrors['full_name']); ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           value="<?php echo e($formData['email']); ?>"
                                           disabled>
                                    <div class="form-text">Email cannot be changed. Contact support for assistance.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" 
                                           class="form-control <?php echo $fieldErrors['phone'] !== '' ? 'is-invalid' : ''; ?>" 
                                           id="phone" 
                                           name="phone" 
                                           value="<?php echo e($formData['phone']); ?>"
                                           maxlength="20"
                                           placeholder="+1 555 123 4567">
                                    <div class="invalid-feedback">
                                        <?php echo e($fieldErrors['phone']); ?>
                                    </div>
                                    <div class="form-text">Optional. Used for order notifications.</div>
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-cafe-primary">
                                        <i class="bi bi-check-lg me-2"></i>Save Changes
                                    </button>
                                    <a href="/cloud_9_cafe/user/index.php" class="btn btn-cafe-outline">Cancel</a>
                                </div>
                            </form>
                        </article>
                    </div>

                    <div class="col-md-4">
                        <article class="dashboard-action-card h-100">
                            <div class="dashboard-action-icon"><i class="bi bi-shield-lock"></i></div>
                            <h3>Security</h3>
                            <p>Keep your account secure.</p>
                            <a href="/cloud_9_cafe/user/account/settings.php" class="btn btn-cafe-outline mt-3">
                                <i class="bi bi-key me-2"></i>Change Password
                            </a>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../../includes/layouts/user_footer.php'; ?>
