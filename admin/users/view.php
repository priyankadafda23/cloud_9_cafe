<?php
require_once __DIR__ . '/../../includes/auth/admin_guard.php';
require_once __DIR__ . '/../../includes/db.php';

$pageTitle = 'Cloud 9 Cafe | User Details';
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

// Fetch user details
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header("Location: list.php?error=not_found");
        exit;
    }
    
    // Fetch user statistics
    $orderStmt = $pdo->prepare("SELECT COUNT(*) as total_orders, COALESCE(SUM(total_amount), 0) as total_spent 
                                FROM orders WHERE user_id = ?");
    $orderStmt->execute([$userId]);
    $orderStats = $orderStmt->fetch();
    
    $reservationStmt = $pdo->prepare("SELECT COUNT(*) as total_reservations FROM reservations WHERE user_id = ?");
    $reservationStmt->execute([$userId]);
    $reservationCount = $reservationStmt->fetchColumn();
    
    $messageStmt = $pdo->prepare("SELECT COUNT(*) as total_messages FROM contact_messages WHERE user_id = ?");
    $messageStmt->execute([$userId]);
    $messageCount = $messageStmt->fetchColumn();
    
} catch (PDOException $e) {
    header("Location: list.php?error=fetch_failed");
    exit;
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
                    <h1 class="content-title">User Details</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/cloud_9_cafe/admin/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="list.php">Users</a></li>
                            <li class="breadcrumb-item active" aria-current="page">View User</li>
                        </ol>
                    </nav>
                </div>
                <div class="content-actions">
                    <a href="list.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>

            <div class="row g-4">
                <!-- User Profile Card -->
                <div class="col-lg-4">
                    <div class="dashboard-card">
                        <div class="card-body text-center">
                            <div class="user-profile-avatar">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                            <h4 class="mt-3 mb-1"><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                            
                            <div class="user-status-badges">
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge bg-danger">
                                        <i class="bi bi-shield-fill me-1"></i>Administrator
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-info">
                                        <i class="bi bi-person me-1"></i>Customer
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Active
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Inactive
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="user-actions mt-4">
                                <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-primary w-100 mb-2">
                                    <i class="bi bi-pencil me-2"></i>Edit User
                                </a>
                                <a href="list.php" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Users
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Account Info Card -->
                    <div class="dashboard-card mt-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="bi bi-info-circle me-2"></i>Account Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-muted">User ID</span>
                                    <span class="fw-medium">#<?php echo $user['id']; ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Joined</span>
                                    <span class="fw-medium"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Last Updated</span>
                                    <span class="fw-medium"><?php echo date('F j, Y', strtotime($user['updated_at'])); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Last Login</span>
                                    <span class="fw-medium">
                                        <?php echo $user['last_login_at'] ? date('F j, Y g:i A', strtotime($user['last_login_at'])) : 'Never'; ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- User Statistics -->
                <div class="col-lg-8">
                    <!-- Stats Row -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="stat-card stat-card-orders">
                                <div class="stat-card-body">
                                    <div class="stat-icon">
                                        <i class="bi bi-receipt"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h3 class="stat-value"><?php echo number_format($orderStats['total_orders'] ?? 0); ?></h3>
                                        <p class="stat-label">Total Orders</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card stat-card-messages">
                                <div class="stat-card-body">
                                    <div class="stat-icon">
                                        <i class="bi bi-cash-stack"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h3 class="stat-value">$<?php echo number_format($orderStats['total_spent'] ?? 0, 2); ?></h3>
                                        <p class="stat-label">Total Spent</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card stat-card-menu">
                                <div class="stat-card-body">
                                    <div class="stat-icon">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h3 class="stat-value"><?php echo number_format($reservationCount ?? 0); ?></h3>
                                        <p class="stat-label">Reservations</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="bi bi-person-lines-fill me-2"></i>Contact Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="text-muted small">Full Name</label>
                                    <p class="fw-medium mb-0"><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Email Address</label>
                                    <p class="fw-medium mb-0">
                                        <a href="mailto:<?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Phone Number</label>
                                    <p class="fw-medium mb-0">
                                        <?php if ($user['phone']): ?>
                                            <a href="tel:<?php echo htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Not provided</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Account Status</label>
                                    <p class="mb-0">
                                        <?php if ($user['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="dashboard-card mt-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="bi bi-clock-history me-2"></i>Recent Activity
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="activity-timeline">
                                <div class="activity-item">
                                    <div class="activity-icon bg-primary">
                                        <i class="bi bi-person-plus"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p class="activity-text">Account created</p>
                                        <span class="activity-time"><?php echo date('F j, Y \a\t g:i A', strtotime($user['created_at'])); ?></span>
                                    </div>
                                </div>
                                
                                <?php if ($user['last_login_at']): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon bg-success">
                                            <i class="bi bi-box-arrow-in-right"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p class="activity-text">Last login</p>
                                            <span class="activity-time"><?php echo date('F j, Y \a\t g:i A', strtotime($user['last_login_at'])); ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($orderStats['total_orders'] > 0): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon bg-info">
                                            <i class="bi bi-receipt"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p class="activity-text">Placed <?php echo $orderStats['total_orders']; ?> order(s)</p>
                                            <span class="activity-time">Total spent: $<?php echo number_format($orderStats['total_spent'], 2); ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($reservationCount > 0): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon bg-warning">
                                            <i class="bi bi-calendar-check"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p class="activity-text">Made <?php echo $reservationCount; ?> reservation(s)</p>
                                            <span class="activity-time">Active customer</span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
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

        // Close sidebar when clicking outside on mobile
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
