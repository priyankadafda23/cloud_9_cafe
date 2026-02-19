<?php
require_once __DIR__ . '/../../includes/auth/admin_guard.php';
require_once __DIR__ . '/../../includes/db.php';

$pageTitle = 'Cloud 9 Cafe | Add User';
$extraCssFiles = ['/cloud_9_cafe/assets/css/admin.css'];

$adminName = trim((string) ($_SESSION['full_name'] ?? $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Administrator'));
if ($adminName === '') {
    $adminName = 'Administrator';
}

// Initialize variables
$successMessage = '';
$errorMessage = '';

$formData = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'role' => 'user',
    'is_active' => 1
];

$fieldErrors = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'password' => '',
    'confirm_password' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
    $formData['email'] = strtolower(trim((string) ($_POST['email'] ?? '')));
    $formData['phone'] = trim((string) ($_POST['phone'] ?? ''));
    $formData['role'] = $_POST['role'] ?? 'user';
    $formData['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    
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
    
    if ($formData['email'] === '') {
        $fieldErrors['email'] = 'Email is required.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['email'] = 'Enter a valid email address.';
    } elseif (strlen($formData['email']) > 190) {
        $fieldErrors['email'] = 'Email must not exceed 190 characters.';
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
    
    if ($password === '') {
        $fieldErrors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $fieldErrors['password'] = 'Password must be at least 8 characters.';
    } elseif (strlen($password) > 72) {
        $fieldErrors['password'] = 'Password must be 72 characters or fewer.';
    } elseif (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
        $fieldErrors['password'] = 'Use uppercase, lowercase, number, and special character.';
    }
    
    if ($confirmPassword === '') {
        $fieldErrors['confirm_password'] = 'Confirm password is required.';
    } elseif ($confirmPassword !== $password) {
        $fieldErrors['confirm_password'] = 'Passwords do not match.';
    }
    
    // Validate role
    if (!in_array($formData['role'], ['user', 'admin'])) {
        $formData['role'] = 'user';
    }
    
    // If no errors, insert into database
    if (!array_filter($fieldErrors)) {
        try {
            // Check for duplicate email
            $existingStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $existingStmt->execute([':email' => $formData['email']]);
            $existing = $existingStmt->fetch();
            
            if ($existing) {
                $fieldErrors['email'] = 'This email is already registered.';
            } else {
                $insertStmt = $pdo->prepare(
                    'INSERT INTO users (full_name, email, password_hash, role, phone, is_active, created_at, updated_at)
                     VALUES (:full_name, :email, :password_hash, :role, :phone, :is_active, NOW(), NOW())'
                );
                
                $insertStmt->execute([
                    ':full_name' => $formData['full_name'],
                    ':email' => $formData['email'],
                    ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    ':role' => $formData['role'],
                    ':phone' => $formData['phone'] ?: null,
                    ':is_active' => $formData['is_active']
                ]);
                
                header('Location: list.php?success=created');
                exit;
            }
        } catch (Throwable $e) {
            $errorMessage = 'Failed to create user. Please try again.';
        }
    }
}

require_once __DIR__ . '/../../includes/layouts/admin_header.php';
?>

<div class="admin-dashboard-wrapper">
    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <div class="brand">
                <div class="brand-icon">
                    <i class="bi bi-cloud-fill"></i>
                </div>
                <div class="brand-text">
                    <span class="brand-title">Cloud 9 Cafe</span>
                    <span class="brand-subtitle">Admin Panel</span>
                </div>
            </div>
            <button class="sidebar-toggle d-lg-none" id="sidebarToggle" aria-label="Toggle sidebar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="sidebar-profile">
            <div class="profile-avatar">
                <i class="bi bi-person-fill"></i>
            </div>
            <div class="profile-info">
                <span class="profile-name"><?php echo htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="profile-role">Administrator</span>
            </div>
        </div>

        <nav class="sidebar-nav" aria-label="Admin navigation">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/cloud_9_cafe/admin/index.php">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/cloud_9_cafe/admin/users/list.php">
                        <i class="bi bi-people"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cloud_9_cafe/admin/menu/list.php">
                        <i class="bi bi-cup-hot"></i>
                        <span>Menu Items</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cloud_9_cafe/admin/orders/list.php">
                        <i class="bi bi-receipt"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cloud_9_cafe/admin/reservations/list.php">
                        <i class="bi bi-calendar-check"></i>
                        <span>Reservations</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cloud_9_cafe/admin/reports/sales.php">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cloud_9_cafe/admin/settings/general.php">
                        <i class="bi bi-gear"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="/cloud_9_cafe/" class="btn btn-view-site" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i>
                <span>View Site</span>
            </a>
            <a href="/cloud_9_cafe/admin/logout.php" class="btn btn-logout">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Top Header -->
        <header class="admin-topbar">
            <button class="topbar-toggle d-lg-none" id="topbarToggle" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>
            
            <div class="topbar-search d-none d-md-block">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Search..." aria-label="Search">
            </div>

            <div class="topbar-actions">
                <a href="/cloud_9_cafe/admin/reports/users.php" class="action-btn" title="Messages">
                    <i class="bi bi-envelope"></i>
                </a>
                <a href="/cloud_9_cafe/admin/settings/general.php" class="action-btn" title="Settings">
                    <i class="bi bi-gear"></i>
                </a>
                <div class="action-btn profile-dropdown dropdown">
                    <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="mini-avatar">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header"><?php echo htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8'); ?></h6></li>
                        <li><a class="dropdown-item" href="/cloud_9_cafe/admin/settings/general.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="/cloud_9_cafe/admin/settings/general.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/cloud_9_cafe/admin/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="admin-content">
            <div class="content-header">
                <div>
                    <h1 class="content-title">Add User</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/cloud_9_cafe/admin/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="list.php">Users</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Add User</li>
                        </ol>
                    </nav>
                </div>
                <div class="content-actions">
                    <a href="list.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>

            <?php if ($errorMessage !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="bi bi-person-plus me-2"></i>User Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="menu-item-form" id="userForm">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <!-- Full Name -->
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">
                                        Full Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?php echo $fieldErrors['full_name'] !== '' ? 'is-invalid' : ''; ?>" 
                                           id="full_name" 
                                           name="full_name" 
                                           value="<?php echo htmlspecialchars($formData['full_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                           maxlength="100"
                                           placeholder="John Doe"
                                           required>
                                    <?php if ($fieldErrors['full_name'] !== ''): ?>
                                        <div class="invalid-feedback"><?php echo htmlspecialchars($fieldErrors['full_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        Email Address <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" 
                                           class="form-control <?php echo $fieldErrors['email'] !== '' ? 'is-invalid' : ''; ?>" 
                                           id="email" 
                                           name="email" 
                                           value="<?php echo htmlspecialchars($formData['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                           maxlength="190"
                                           placeholder="john@example.com"
                                           required>
                                    <?php if ($fieldErrors['email'] !== ''): ?>
                                        <div class="invalid-feedback"><?php echo htmlspecialchars($fieldErrors['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Phone -->
                                <div class="mb-3">
                                    <label for="phone" class="form-label">
                                        Phone Number
                                    </label>
                                    <input type="tel" 
                                           class="form-control <?php echo $fieldErrors['phone'] !== '' ? 'is-invalid' : ''; ?>" 
                                           id="phone" 
                                           name="phone" 
                                           value="<?php echo htmlspecialchars($formData['phone'], ENT_QUOTES, 'UTF-8'); ?>"
                                           maxlength="20"
                                           placeholder="+1 555 123 4567">
                                    <?php if ($fieldErrors['phone'] !== ''): ?>
                                        <div class="invalid-feedback"><?php echo htmlspecialchars($fieldErrors['phone'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                    <div class="form-text">Optional. Used for order notifications.</div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <!-- Password -->
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" 
                                           class="form-control <?php echo $fieldErrors['password'] !== '' ? 'is-invalid' : ''; ?>" 
                                           id="password" 
                                           name="password" 
                                           minlength="8"
                                           maxlength="72"
                                           required>
                                    <?php if ($fieldErrors['password'] !== ''): ?>
                                        <div class="invalid-feedback"><?php echo htmlspecialchars($fieldErrors['password'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                    <div class="password-strength mt-2">
                                        <div class="password-strength-bar">
                                            <span class="password-strength-fill" id="passwordStrengthFill"></span>
                                        </div>
                                        <small class="password-strength-text" id="passwordStrengthText">Strength: Too weak</small>
                                    </div>
                                </div>

                                <!-- Confirm Password -->
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">
                                        Confirm Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" 
                                           class="form-control <?php echo $fieldErrors['confirm_password'] !== '' ? 'is-invalid' : ''; ?>" 
                                           id="confirm_password" 
                                           name="confirm_password" 
                                           minlength="8"
                                           maxlength="72"
                                           required>
                                    <?php if ($fieldErrors['confirm_password'] !== ''): ?>
                                        <div class="invalid-feedback"><?php echo htmlspecialchars($fieldErrors['confirm_password'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Role -->
                                <div class="mb-3">
                                    <label for="role" class="form-label">
                                        Role <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="user" <?php echo $formData['role'] === 'user' ? 'selected' : ''; ?>>User (Customer)</option>
                                        <option value="admin" <?php echo $formData['role'] === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                                    </select>
                                </div>

                                <!-- Active Status -->
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1"
                                               <?php echo $formData['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            Account is active
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Password Requirements</h6>
                            <ul class="mb-0 small">
                                <li>At least 8 characters</li>
                                <li>At least one uppercase letter (A-Z)</li>
                                <li>At least one lowercase letter (a-z)</li>
                                <li>At least one number (0-9)</li>
                                <li>At least one special character (!@#$%^&*)</li>
                            </ul>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Create User
                            </button>
                            <a href="list.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Sidebar toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('adminSidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const topbarToggle = document.getElementById('topbarToggle');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }

        if (topbarToggle) {
            topbarToggle.addEventListener('click', toggleSidebar);
        }

        document.addEventListener('click', function(e) {
            if (window.innerWidth < 992) {
                if (!sidebar.contains(e.target) && !topbarToggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });

        // Password strength meter
        const passwordInput = document.getElementById('password');
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
                strengthText.textContent = 'Strength: ' + levels[level];
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../../includes/layouts/admin_footer.php'; ?>
