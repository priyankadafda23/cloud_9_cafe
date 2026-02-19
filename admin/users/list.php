<?php
require_once __DIR__ . '/../../includes/auth/admin_guard.php';
require_once __DIR__ . '/../../includes/db.php';

$pageTitle = 'Cloud 9 Cafe | Manage Users';
$extraCssFiles = ['/cloud_9_cafe/assets/css/admin.css'];

$adminName = trim((string) ($_SESSION['full_name'] ?? $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Administrator'));
if ($adminName === '') {
    $adminName = 'Administrator';
}

// Initialize variables
$users = [];
$error = '';
$success = '';

// Get filter parameters
$search = trim($_GET['search'] ?? '');
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

try {
    // Build query with filters
    $whereConditions = ["role = 'user'"]; // Only show regular users, not admins
    $params = [];
    
    if ($search !== '') {
        $whereConditions[] = "(full_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    if ($roleFilter !== '') {
        $whereConditions[] = "role = :role";
        $params[':role'] = $roleFilter;
    }
    
    if ($statusFilter !== '') {
        $whereConditions[] = "is_active = :status";
        $params[':status'] = $statusFilter === 'active' ? 1 : 0;
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    
    // Get total count for pagination
    $countSql = "SELECT COUNT(*) FROM users $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalItems = $countStmt->fetchColumn();
    $totalPages = ceil($totalItems / $perPage);
    
    // Get users
    $sql = "SELECT id, full_name, email, phone, role, is_active, last_login_at, created_at 
            FROM users 
            $whereClause 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Failed to load users. Please try again.';
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = intval($_POST['delete_id']);
    
    // Prevent self-deletion
    $currentUserId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;
    if ($deleteId === $currentUserId) {
        $error = 'You cannot delete your own account.';
    } else {
        try {
            // Check if user has orders or other related data
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
            $checkStmt->execute([$deleteId]);
            $hasOrders = $checkStmt->fetchColumn() > 0;
            
            if ($hasOrders) {
                // Soft delete - just deactivate
                $deactivateStmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
                $deactivateStmt->execute([$deleteId]);
                $success = 'User has orders. Account deactivated instead of deleted.';
            } else {
                // Hard delete
                $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $deleteStmt->execute([$deleteId]);
                $success = 'User deleted successfully.';
            }
            
            // Refresh the page
            header("Location: list.php?success=deleted");
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to delete user. Please try again.';
        }
    }
}

// Handle toggle status action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $toggleId = intval($_POST['toggle_id']);
    $newStatus = intval($_POST['new_status']);
    
    // Prevent self-deactivation
    $currentUserId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;
    if ($toggleId === $currentUserId) {
        $error = 'You cannot change your own account status.';
    } else {
        try {
            $toggleStmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            $toggleStmt->execute([$newStatus, $toggleId]);
            
            $statusText = $newStatus ? 'activated' : 'deactivated';
            header("Location: list.php?success=status_" . $statusText);
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to update user status. Please try again.';
        }
    }
}

// Check for success message from redirect
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'created':
            $success = 'User created successfully.';
            break;
        case 'updated':
            $success = 'User updated successfully.';
            break;
        case 'deleted':
            $success = 'User deleted successfully.';
            break;
        case 'status_activated':
            $success = 'User activated successfully.';
            break;
        case 'status_deactivated':
            $success = 'User deactivated successfully.';
            break;
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
                    <h1 class="content-title">Manage Users</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/cloud_9_cafe/admin/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Users</li>
                        </ol>
                    </nav>
                </div>
                <div class="content-actions">
                    <a href="create.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Add User
                    </a>
                </div>
            </div>

            <!-- Alerts -->
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="stat-card stat-card-users">
                        <div class="stat-card-body">
                            <div class="stat-icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-value"><?php echo number_format($totalItems); ?></h3>
                                <p class="stat-label">Total Users</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card stat-card-menu">
                        <div class="stat-card-body">
                            <div class="stat-icon">
                                <i class="bi bi-person-check-fill"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-value">
                                    <?php 
                                    $activeCount = array_filter($users, fn($u) => $u['is_active']);
                                    echo number_format(count($activeCount));
                                    ?>
                                </h3>
                                <p class="stat-label">Active Users</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card stat-card-messages">
                        <div class="stat-card-body">
                            <div class="stat-icon">
                                <i class="bi bi-person-x-fill"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-value">
                                    <?php 
                                    $inactiveCount = array_filter($users, fn($u) => !$u['is_active']);
                                    echo number_format(count($inactiveCount));
                                    ?>
                                </h3>
                                <p class="stat-label">Inactive Users</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="dashboard-card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" name="search" placeholder="Search by name, email, or phone..." 
                                       value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="list.php" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-x-lg me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-people me-2"></i>User List
                    </h5>
                    <span class="text-muted small">
                        Showing <?php echo count($users); ?> of <?php echo $totalItems; ?> users
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover users-table mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Contact</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Joined</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="bi bi-people display-4 text-muted"></i>
                                                <h5 class="mt-3">No users found</h5>
                                                <p class="text-muted">Get started by adding your first user.</p>
                                                <a href="create.php" class="btn btn-primary">
                                                    <i class="bi bi-plus-lg me-2"></i>Add User
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="user-info">
                                                    <div class="user-avatar">
                                                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                                    </div>
                                                    <div class="user-details">
                                                        <h6 class="user-name"><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                                        <span class="user-id">ID: #<?php echo $user['id']; ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="contact-info">
                                                    <div class="contact-item">
                                                        <i class="bi bi-envelope"></i>
                                                        <span><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                    </div>
                                                    <?php if ($user['phone']): ?>
                                                        <div class="contact-item">
                                                            <i class="bi bi-telephone"></i>
                                                            <span><?php echo htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($user['role'] === 'admin'): ?>
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-shield-fill me-1"></i>Admin
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">
                                                        <i class="bi bi-person me-1"></i>User
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($user['is_active']): ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>Active
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-x-circle me-1"></i>Inactive
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($user['last_login_at']): ?>
                                                    <span class="text-muted small">
                                                        <?php echo date('M j, Y g:i A', strtotime($user['last_login_at'])); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted small">Never</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="text-muted small">
                                                    <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="view.php?id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal"
                                                            data-user-id="<?php echo $user['id']; ?>"
                                                            data-user-name="<?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-transparent">
                        <nav aria-label="Users pagination">
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                    Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle me-2"></i>
                    If this user has orders, their account will be deactivated instead of deleted to preserve order history.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="delete_id" id="deleteUserId">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>
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

        // Delete modal functionality
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const userName = button.getAttribute('data-user-name');
                
                document.getElementById('deleteUserId').value = userId;
                document.getElementById('deleteUserName').textContent = userName;
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../../includes/layouts/admin_footer.php'; ?>
