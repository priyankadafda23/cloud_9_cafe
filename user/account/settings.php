<?php
require_once __DIR__ . '/../../includes/auth/auth_guard.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers/functions.php';

$pageTitle = 'Cloud 9 Cafe | Change Password';
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
    'current_password' => '',
    'new_password' => '',
    'confirm_password' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    
    // Validation
    if ($currentPassword === '') {
        $fieldErrors['current_password'] = 'Current password is required.';
    }
    
    if ($newPassword === '') {
        $fieldErrors['new_password'] = 'New password is required.';
    } elseif (strlen($newPassword) < 8) {
        $fieldErrors['new_password'] = 'Password must be at least 8 characters.';
    } elseif (strlen($newPassword) > 72) {
        $fieldErrors['new_password'] = 'Password must be 72 characters or fewer.';
    } elseif (!preg_match('/[a-z]/', $newPassword) || !preg_match('/[A-Z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword) || !preg_match('/[^A-Za-z0-9]/', $newPassword)) {
        $fieldErrors['new_password'] = 'Use uppercase, lowercase, number, and special character.';
    }
    
    if ($confirmPassword === '') {
        $fieldErrors['confirm_password'] = 'Please confirm your new password.';
    } elseif ($confirmPassword !== $newPassword) {
        $fieldErrors['confirm_password'] = 'Passwords do not match.';
    }
    
    // If no validation errors, verify and update
    if (!array_filter($fieldErrors)) {
        try {
            // Get current password hash
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $errorMessage = 'Unable to verify your identity. Please login again.';
            } elseif (!password_verify($currentPassword, $user['password_hash'])) {
                $fieldErrors['current_password'] = 'Current password is incorrect.';
            } else {
                // Update password
                $updateStmt = $pdo->prepare(
                    "UPDATE users 
                     SET password_hash = :password_hash, updated_at = NOW() 
                     WHERE id = :id"
                );
                
                $updateStmt->execute([
                    ':password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                    ':id' => $userId
                ]);
                
                $successMessage = 'Your password has been changed successfully.';
                
                // Clear form
                $currentPassword = '';
                $newPassword = '';
                $confirmPassword = '';
            }
        } catch (PDOException $e) {
            $errorMessage = 'Unable to change your password. Please try again.';
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
                                <a class="nav-link" href="/cloud_9_cafe/user/account/profile.php">
                                    <i class="bi bi-person"></i>
                                    <span>Edit Profile</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="/cloud_9_cafe/user/account/settings.php">
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
                        <span class="dashboard-kicker">Account Security</span>
                        <h1>Change Password</h1>
                        <p>Update your password to keep your account secure.</p>
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
                            <div class="dashboard-action-icon"><i class="bi bi-shield-lock"></i></div>
                            <h3>Change Password</h3>
                            <p>Choose a strong password to protect your account.</p>

                            <form method="POST" action="/cloud_9_cafe/user/account/settings.php" class="mt-4" novalidate>
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                                    <input type="password" 
                                           class="form-control <?php echo $fieldErrors['current_password'] !== '' ? 'is-invalid' : ''; ?>" 
                                           id="current_password" 
                                           name="current_password" 
                                           minlength="8"
                                           maxlength="72"
                                           required>
                                    <div class="invalid-feedback">
                                        <?php echo e($fieldErrors['current_password']); ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                                    <input type="password" 
                                           class="form-control <?php echo $fieldErrors['new_password'] !== '' ? 'is-invalid' : ''; ?>" 
                                           id="new_password" 
                                           name="new_password" 
                                           minlength="8"
                                           maxlength="72"
                                           required>
                                    <div class="invalid-feedback">
                                        <?php echo e($fieldErrors['new_password']); ?>
                                    </div>
                                    <div class="password-strength mt-2" aria-live="polite">
                                        <div class="password-strength-bar">
                                            <span class="password-strength-fill" id="passwordStrengthFill"></span>
                                        </div>
                                        <small class="password-strength-text" id="passwordStrengthText">Strength: Too weak</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                    <input type="password" 
                                           class="form-control <?php echo $fieldErrors['confirm_password'] !== '' ? 'is-invalid' : ''; ?>" 
                                           id="confirm_password" 
                                           name="confirm_password" 
                                           minlength="8"
                                           maxlength="72"
                                           required>
                                    <div class="invalid-feedback">
                                        <?php echo e($fieldErrors['confirm_password']); ?>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Password Requirements</h6>
                                    <ul class="mb-0 small">
                                        <li>At least 8 characters</li>
                                        <li>At least one uppercase letter (A-Z)</li>
                                        <li>At least one lowercase letter (a-z)</li>
                                        <li>At least one number (0-9)</li>
                                        <li>At least one special character (!@#$%^&*)</li>
                                    </ul>
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-cafe-primary">
                                        <i class="bi bi-check-lg me-2"></i>Change Password
                                    </button>
                                    <a href="/cloud_9_cafe/user/index.php" class="btn btn-cafe-outline">Cancel</a>
                                </div>
                            </form>
                        </article>
                    </div>

                    <div class="col-md-4">
                        <article class="dashboard-action-card h-100">
                            <div class="dashboard-action-icon"><i class="bi bi-person-gear"></i></div>
                            <h3>Profile</h3>
                            <p>Update your personal information.</p>
                            <a href="/cloud_9_cafe/user/account/profile.php" class="btn btn-cafe-outline mt-3">
                                <i class="bi bi-person me-2"></i>Edit Profile
                            </a>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Password strength meter
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('new_password');
        const strengthFill = document.getElementById('passwordStrengthFill');
        const strengthText = document.getElementById('passwordStrengthText');
        
        if (passwordInput && strengthFill && strengthText) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                if (password.length >= 8) strength++;
                if (password.length >= 12) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;
                
                const levels = ['Too weak', 'Weak', 'Fair', 'Good', 'Strong', 'Excellent'];
                const colors = ['#dc3545', '#dc3545', '#fd7e14', '#ffc107', '#28a745', '#198754'];
                const widths = ['0%', '20%', '40%', '60%', '80%', '100%'];
                
                const level = Math.min(strength, 5);
                strengthFill.style.width = widths[level];
                strengthFill.style.backgroundColor = colors[level];
                strengthFill.setAttribute('data-level', level === 0 ? 'weak' : level === 1 ? 'weak' : level === 2 ? 'fair' : level === 3 ? 'good' : level === 4 ? 'strong' : 'excellent');
                strengthText.textContent = 'Strength: ' + levels[level];
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../../includes/layouts/user_footer.php'; ?>
