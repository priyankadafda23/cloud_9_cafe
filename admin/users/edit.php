<?php
require_once __DIR__ . '/../../includes/auth/admin_guard.php';
require_once __DIR__ . '/../../includes/db.php';

$pageTitle = 'Cloud 9 Cafe | Edit User';
$extraCssFiles = ['/cloud_9_cafe/assets/css/admin.css'];

$adminName = trim((string) ($_SESSION['full_name'] ?? $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Administrator'));
if ($adminName === '') {
    $adminName = 'Administrator';
}

// Get user ID
$userId = intval($_GET['id'] ?? 0);
if ($userId <= 0) {
    header("Location: list.php?error=invalid_id");
    exit;
}

// Initialize variables
$successMessage = '';
$errorMessage = '';
$fieldErrors = [
    'full_name' => '',
    'email' => '',
    'phone' => ''
];

// Fetch existing user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header("Location: list.php?error=not_found");
        exit;
    }
    
    // Prevent editing own account through this page
    $currentAdminId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;
    if ($userId === $currentAdminId) {
        header("Location: list.php?error=cannot_edit_self");
        exit;
    }
    
    $formData = [
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'phone' => $user['phone'] ?? '',
        'role' => $user['role'],
        'is_active' => $user['is_active']
    ];
} catch (PDOException $e) {
    header("Location: list.php?error=fetch_failed");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
    $formData['email'] = strtolower(trim((string) ($_POST['email'] ?? '')));
    $formData['phone'] = trim((string) ($_POST['phone'] ?? ''));
    $formData['role'] = $_POST['role'] ?? 'user';
    $formData['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    
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
    
    // Validate role
    if (!in_array($formData['role'], ['user', 'admin'])) {
        $formData['role'] = 'user';
    }
    
    // If no errors, update database
    if (!array_filter($fieldErrors)) {
        try {
            // Check for duplicate email (excluding current user)
            $existingStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
            $existingStmt->execute([':email' => $formData['email'], ':id' => $userId]);
            $existing = $existingStmt->fetch();
            
            if ($existing) {
                $fieldErrors['email'] = 'This email is already registered by another user.';
            } else {
                $updateStmt = $pdo->prepare(
                    'UPDATE users 
                     SET full_name = :full_name, 
                         email = :email, 
                         role = :role, 
                         phone = :phone, 
                         is_active = :is_active,
                         updated_at = NOW()
                     WHERE id = :id'
                );
                
                $updateStmt->execute([
                    ':full_name' => $formData['full_name'],
                    ':email' => $formData['email'],
                    ':role' => $formData['role'],
                    ':phone' => $formData['phone'] ?: null,
                    ':is_active' => $formData['is_active'],
                    ':id' => $userId
                ]);
                
                header('Location: list.php?success=updated');
                exit;
            }
        } catch (Throwable $e) {
            $errorMessage = 'Failed to update user. Please try again.';
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
                    <h1 class="content-title">Edit User</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/cloud_9_cafe/admin/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="list.php">Users</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit User</li>
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

            <!-- User Info Card -->
            <div class="dashboard-card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="user-profile-avatar" style="width: 64px; height: 64px; font-size: 1.5rem;">
                            <?php echo strtoupper(substr($formData['full_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <h5 class="mb-1"><?php echo htmlspecialchars($formData['full_name'], ENT_QUOTES, 'UTF-8'); ?></h5>
                            <p class="mb-0 text-muted">User ID: #<?php echo $userId; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="bi bi-pencil-square me-2"></i>Edit User Information
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
                                           maxlength="20">
                                    <?php if ($fieldErrors['phone'] !== ''): ?>
                                        <div class="invalid-feedback"><?php echo htmlspecialchars($fieldErrors['phone'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <!-- Role -->
                                <div class="mb-3">
                                    <label for="role" class="form-label">
                                        Role <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="user" <?php echo $formData['role'] === 'user' ? 'selected' : ''; ?>>User (Customer)</option>
                                        <option value="admin" <?php echo $formData['role'] === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                                    </select>
                                    <div class="form-text">Admins have full access to the admin panel.</div>
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
                                    <div class="form-text">Inactive users cannot login.</div>
                                </div>

                                <!-- Account Info -->
                                <div class="alert alert-secondary mt-4">
                                    <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Account Information</h6>
                                    <ul class="mb-0 small list-unstyled">
                                        <li><strong>Created:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></li>
                                        <li><strong>Last Updated:</strong> <?php echo date('F j, Y', strtotime($user['updated_at'])); ?></li>
                                        <li><strong>Last Login:</strong> <?php echo $user['last_login_at'] ? date('F j, Y g:i A', strtotime($user['last_login_at'])) : 'Never'; ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Update User
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
    });
</script>

<?php require_once __DIR__ . '/../../includes/layouts/admin_footer.php'; ?>
