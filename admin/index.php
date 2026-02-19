<?php
require_once __DIR__ . '/../includes/auth/admin_guard.php';
require_once __DIR__ . '/../includes/db.php';

$pageTitle = 'Cloud 9 Cafe | Admin Dashboard';
$extraCssFiles = ['/cloud_9_cafe/assets/css/admin.css'];

$adminName = trim((string) ($_SESSION['full_name'] ?? $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Administrator'));
if ($adminName === '') {
    $adminName = 'Administrator';
}

$adminEmail = $_SESSION['email'] ?? 'admin@cloud9cafe.com';
$adminRole = $_SESSION['role'] ?? 'admin';

// Initialize stats
$stats = [
    'users' => null,
    'menu_items' => null,
    'messages' => null,
    'orders' => null,
    'reservations' => null,
];
$dataWarnings = [];

// Fetch statistics with error handling
try {
    $stats['users'] = (int) $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role = 'user'")->fetch()['total'];
} catch (Throwable $e) {
    $dataWarnings[] = 'Unable to load total users count.';
}

try {
    $stats['menu_items'] = (int) $pdo->query('SELECT COUNT(*) AS total FROM menu_items')->fetch()['total'];
} catch (Throwable $e) {
    $dataWarnings[] = 'Unable to load total menu items count.';
}

try {
    $stats['messages'] = (int) $pdo->query("SELECT COUNT(*) AS total FROM contact_messages WHERE status = 'new'")->fetch()['total'];
} catch (Throwable $e) {
    $dataWarnings[] = 'Unable to load unread messages count.';
}

try {
    $stats['orders'] = (int) $pdo->query("SELECT COUNT(*) AS total FROM orders WHERE status IN ('pending', 'processing')")->fetch()['total'];
} catch (Throwable $e) {
    $stats['orders'] = 0; // Orders table may not exist yet
}

try {
    $stats['reservations'] = (int) $pdo->query("SELECT COUNT(*) AS total FROM reservations WHERE status IN ('pending', 'confirmed')")->fetch()['total'];
} catch (Throwable $e) {
    $stats['reservations'] = 0; // Reservations table may not exist yet
}

// Helper function to format stat values
function adminStatValue(?int $value): string {
    return $value === null ? '--' : number_format($value);
}

// Get current page for active state
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

require_once __DIR__ . '/../includes/layouts/admin_header.php';
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
                <span class="profile-role"><?php echo ucfirst(htmlspecialchars($adminRole, ENT_QUOTES, 'UTF-8')); ?></span>
            </div>
        </div>

        <nav class="sidebar-nav" aria-label="Admin navigation">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="/cloud_9_cafe/admin/index.php">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cloud_9_cafe/admin/users/list.php">
                        <i class="bi bi-people"></i>
                        <span>Users</span>
                        <?php if ($stats['users'] !== null && $stats['users'] > 0): ?>
                            <span class="badge bg-primary rounded-pill ms-auto"><?php echo $stats['users']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cloud_9_cafe/admin/menu/list.php">
                        <i class="bi bi-cup-hot"></i>
                        <span>Menu Items</span>
                        <?php if ($stats['menu_items'] !== null && $stats['menu_items'] > 0): ?>
                            <span class="badge bg-success rounded-pill ms-auto"><?php echo $stats['menu_items']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cloud_9_cafe/admin/orders/list.php">
                        <i class="bi bi-receipt"></i>
                        <span>Orders</span>
                        <?php if ($stats['orders'] > 0): ?>
                            <span class="badge bg-warning text-dark rounded-pill ms-auto"><?php echo $stats['orders']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/cloud_9_cafe/admin/reservations/list.php">
                        <i class="bi bi-calendar-check"></i>
                        <span>Reservations</span>
                        <?php if ($stats['reservations'] > 0): ?>
                            <span class="badge bg-info rounded-pill ms-auto"><?php echo $stats['reservations']; ?></span>
                        <?php endif; ?>
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
                    <?php if ($stats['messages'] !== null && $stats['messages'] > 0): ?>
                        <span class="action-badge"><?php echo $stats['messages']; ?></span>
                    <?php endif; ?>
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

        <!-- Dashboard Content -->
        <div class="admin-content">
            <div class="content-header">
                <div>
                    <h1 class="content-title">Dashboard</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </nav>
                </div>
                <div class="content-actions">
                    <span class="text-muted small"><?php echo date('l, F j, Y'); ?></span>
                </div>
            </div>

            <?php if (!empty($dataWarnings)): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars(implode(' ', $dataWarnings), ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Stats Cards Row -->
            <div class="row g-4 mb-4">
                <!-- Total Users Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card stat-card-users">
                        <div class="stat-card-body">
                            <div class="stat-icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-value"><?php echo adminStatValue($stats['users']); ?></h3>
                                <p class="stat-label">Total Users</p>
                            </div>
                        </div>
                        <div class="stat-card-footer">
                            <a href="/cloud_9_cafe/admin/users/list.php" class="stat-link">
                                <span>Manage Users</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Total Menu Items Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card stat-card-menu">
                        <div class="stat-card-body">
                            <div class="stat-icon">
                                <i class="bi bi-cup-hot-fill"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-value"><?php echo adminStatValue($stats['menu_items']); ?></h3>
                                <p class="stat-label">Menu Items</p>
                            </div>
                        </div>
                        <div class="stat-card-footer">
                            <a href="/cloud_9_cafe/admin/menu/list.php" class="stat-link">
                                <span>Manage Menu</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Total Messages Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card stat-card-messages">
                        <div class="stat-card-body">
                            <div class="stat-icon">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-value"><?php echo adminStatValue($stats['messages']); ?></h3>
                                <p class="stat-label">New Messages</p>
                            </div>
                        </div>
                        <div class="stat-card-footer">
                            <a href="/cloud_9_cafe/admin/reports/users.php" class="stat-link">
                                <span>View Messages</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Total Orders Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card stat-card-orders">
                        <div class="stat-card-body">
                            <div class="stat-icon">
                                <i class="bi bi-cart-fill"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-value"><?php echo adminStatValue($stats['orders']); ?></h3>
                                <p class="stat-label">Pending Orders</p>
                            </div>
                        </div>
                        <div class="stat-card-footer">
                            <a href="/cloud_9_cafe/admin/orders/list.php" class="stat-link">
                                <span>View Orders</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Info Row -->
            <div class="row g-4">
                <!-- Quick Actions -->
                <div class="col-lg-8">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="bi bi-lightning-charge-fill text-warning me-2"></i>
                                Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-xl-4">
                                    <a href="/cloud_9_cafe/admin/menu/create.php" class="quick-action-btn">
                                        <div class="quick-icon bg-primary-subtle text-primary">
                                            <i class="bi bi-plus-circle"></i>
                                        </div>
                                        <div class="quick-text">
                                            <span class="quick-title">Add Menu Item</span>
                                            <span class="quick-desc">Create new menu entry</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <a href="/cloud_9_cafe/admin/users/create.php" class="quick-action-btn">
                                        <div class="quick-icon bg-success-subtle text-success">
                                            <i class="bi bi-person-plus"></i>
                                        </div>
                                        <div class="quick-text">
                                            <span class="quick-title">Add User</span>
                                            <span class="quick-desc">Create new user account</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <a href="/cloud_9_cafe/admin/reports/sales.php" class="quick-action-btn">
                                        <div class="quick-icon bg-info-subtle text-info">
                                            <i class="bi bi-bar-chart"></i>
                                        </div>
                                        <div class="quick-text">
                                            <span class="quick-title">View Reports</span>
                                            <span class="quick-desc">Check sales analytics</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <a href="/cloud_9_cafe/admin/orders/list.php" class="quick-action-btn">
                                        <div class="quick-icon bg-warning-subtle text-warning">
                                            <i class="bi bi-receipt"></i>
                                        </div>
                                        <div class="quick-text">
                                            <span class="quick-title">Manage Orders</span>
                                            <span class="quick-desc">Process customer orders</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <a href="/cloud_9_cafe/admin/reservations/list.php" class="quick-action-btn">
                                        <div class="quick-icon bg-danger-subtle text-danger">
                                            <i class="bi bi-calendar-check"></i>
                                        </div>
                                        <div class="quick-text">
                                            <span class="quick-title">Reservations</span>
                                            <span class="quick-desc">Manage table bookings</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6 col-xl-4">
                                    <a href="/cloud_9_cafe/admin/settings/general.php" class="quick-action-btn">
                                        <div class="quick-icon bg-secondary-subtle text-secondary">
                                            <i class="bi bi-gear"></i>
                                        </div>
                                        <div class="quick-text">
                                            <span class="quick-title">Settings</span>
                                            <span class="quick-desc">Configure system</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="col-lg-4">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="bi bi-activity text-success me-2"></i>
                                System Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="status-list">
                                <li class="status-item">
                                    <div class="status-indicator status-online"></div>
                                    <div class="status-info">
                                        <span class="status-name">Database</span>
                                        <span class="status-value">Connected</span>
                                    </div>
                                </li>
                                <li class="status-item">
                                    <div class="status-indicator status-online"></div>
                                    <div class="status-info">
                                        <span class="status-name">Web Server</span>
                                        <span class="status-value">Running</span>
                                    </div>
                                </li>
                                <li class="status-item">
                                    <div class="status-indicator status-online"></div>
                                    <div class="status-info">
                                        <span class="status-name">PHP Version</span>
                                        <span class="status-value"><?php echo phpversion(); ?></span>
                                    </div>
                                </li>
                                <li class="status-item">
                                    <div class="status-indicator status-warning"></div>
                                    <div class="status-info">
                                        <span class="status-name">Last Backup</span>
                                        <span class="status-value">24 hours ago</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="/cloud_9_cafe/admin/settings/general.php" class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-shield-check me-1"></i>
                                Security Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Welcome Card -->
            <div class="welcome-banner mt-4">
                <div class="welcome-content">
                    <div class="welcome-icon">
                        <i class="bi bi-cup-hot"></i>
                    </div>
                    <div class="welcome-text">
                        <h4>Welcome back, <?php echo htmlspecialchars(explode(' ', $adminName)[0], ENT_QUOTES, 'UTF-8'); ?>!</h4>
                        <p>Manage your cafe operations from this central dashboard. Track users, menu items, orders, and customer messages all in one place.</p>
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

<?php require_once __DIR__ . '/../includes/layouts/admin_footer.php'; ?>
