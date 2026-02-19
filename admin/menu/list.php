<?php
require_once __DIR__ . '/../../includes/auth/admin_guard.php';
require_once __DIR__ . '/../../includes/db.php';

$pageTitle = 'Cloud 9 Cafe | Manage Menu';
$extraCssFiles = ['/cloud_9_cafe/assets/css/admin.css'];

$adminName = trim((string) ($_SESSION['full_name'] ?? $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Administrator'));
if ($adminName === '') {
    $adminName = 'Administrator';
}

// Initialize variables
$menuItems = [];
$categories = [];
$error = '';
$success = '';

// Get filter parameters
$search = trim($_GET['search'] ?? '');
$categoryFilter = $_GET['category'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

try {
    // Get all categories for filter dropdown
    $categories = $pdo->query("SELECT DISTINCT category FROM menu_items ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
    
    // Build query with filters
    $whereConditions = [];
    $params = [];
    
    if ($search !== '') {
        $whereConditions[] = "(name LIKE :search OR description LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    if ($categoryFilter !== '') {
        $whereConditions[] = "category = :category";
        $params[':category'] = $categoryFilter;
    }
    
    if ($statusFilter !== '') {
        $whereConditions[] = "is_available = :status";
        $params[':status'] = $statusFilter === 'available' ? 1 : 0;
    }
    
    $whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count for pagination
    $countSql = "SELECT COUNT(*) FROM menu_items $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalItems = $countStmt->fetchColumn();
    $totalPages = ceil($totalItems / $perPage);
    
    // Get menu items
    $sql = "SELECT m.*, u.full_name as created_by_name 
            FROM menu_items m 
            LEFT JOIN users u ON m.created_by = u.id 
            $whereClause 
            ORDER BY m.created_at DESC 
            LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $menuItems = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Failed to load menu items. Please try again.';
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = intval($_POST['delete_id']);
    try {
        // Get image path before deleting
        $imgStmt = $pdo->prepare("SELECT image_path FROM menu_items WHERE id = ?");
        $imgStmt->execute([$deleteId]);
        $item = $imgStmt->fetch();
        
        // Delete from database
        $deleteStmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
        $deleteStmt->execute([$deleteId]);
        
        // Delete image file if exists
        if ($item && $item['image_path']) {
            $imageFile = __DIR__ . '/../../uploads/menu/' . basename($item['image_path']);
            if (file_exists($imageFile)) {
                unlink($imageFile);
            }
        }
        
        $success = 'Menu item deleted successfully.';
        // Refresh the page to update the list
        header("Location: list.php?success=deleted");
        exit;
    } catch (PDOException $e) {
        $error = 'Failed to delete menu item. Please try again.';
    }
}

// Check for success message from redirect
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'created':
            $success = 'Menu item created successfully.';
            break;
        case 'updated':
            $success = 'Menu item updated successfully.';
            break;
        case 'deleted':
            $success = 'Menu item deleted successfully.';
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
                    <a class="nav-link" href="/cloud_9_cafe/admin/users/list.php">
                        <i class="bi bi-people"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/cloud_9_cafe/admin/menu/list.php">
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
                    <h1 class="content-title">Manage Menu</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/cloud_9_cafe/admin/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Menu Items</li>
                        </ol>
                    </nav>
                </div>
                <div class="content-actions">
                    <a href="create.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Add Menu Item
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

            <!-- Filters Card -->
            <div class="dashboard-card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" name="search" placeholder="Search items..." 
                                       value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>" 
                                            <?php echo $categoryFilter === $cat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(ucfirst($cat), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="available" <?php echo $statusFilter === 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="unavailable" <?php echo $statusFilter === 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Menu Items Table -->
            <div class="dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-ul me-2"></i>Menu Items
                    </h5>
                    <span class="text-muted small">
                        Showing <?php echo count($menuItems); ?> of <?php echo $totalItems; ?> items
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover menu-table mb-0">
                            <thead>
                                <tr>
                                    <th width="80">Image</th>
                                    <th>Item Details</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($menuItems)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="bi bi-cup-hot display-4 text-muted"></i>
                                                <h5 class="mt-3">No menu items found</h5>
                                                <p class="text-muted">Get started by adding your first menu item.</p>
                                                <a href="create.php" class="btn btn-primary">
                                                    <i class="bi bi-plus-lg me-2"></i>Add Menu Item
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($menuItems as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="menu-item-thumb">
                                                    <?php if ($item['image_path']): ?>
                                                        <img src="/cloud_9_cafe/uploads/menu/<?php echo htmlspecialchars(basename($item['image_path']), ENT_QUOTES, 'UTF-8'); ?>" 
                                                             alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php else: ?>
                                                        <div class="menu-item-placeholder">
                                                            <i class="bi bi-cup-hot"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="menu-item-info">
                                                    <h6 class="menu-item-name"><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                                    <p class="menu-item-desc text-muted small mb-0">
                                                        <?php echo htmlspecialchars(substr($item['description'] ?? '', 0, 60) . (strlen($item['description'] ?? '') > 60 ? '...' : ''), ENT_QUOTES, 'UTF-8'); ?>
                                                    </p>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars(ucfirst($item['category']), ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="menu-item-price">$<?php echo number_format($item['price'], 2); ?></span>
                                            </td>
                                            <td>
                                                <?php if ($item['is_available']): ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>Available
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-x-circle me-1"></i>Unavailable
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="text-muted small">
                                                    <?php echo date('M j, Y', strtotime($item['created_at'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="edit.php?id=<?php echo $item['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Delete"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal"
                                                            data-item-id="<?php echo $item['id']; ?>"
                                                            data-item-name="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>">
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
                        <nav aria-label="Menu items pagination">
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($categoryFilter); ?>&status=<?php echo urlencode($statusFilter); ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($categoryFilter); ?>&status=<?php echo urlencode($statusFilter); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($categoryFilter); ?>&status=<?php echo urlencode($statusFilter); ?>">
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
                <p>Are you sure you want to delete <strong id="deleteItemName"></strong>?</p>
                <p class="text-muted small mb-0">This action cannot be undone. The item will be permanently removed from the menu.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="delete_id" id="deleteItemId">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>Delete Item
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
                const itemId = button.getAttribute('data-item-id');
                const itemName = button.getAttribute('data-item-name');
                
                document.getElementById('deleteItemId').value = itemId;
                document.getElementById('deleteItemName').textContent = itemName;
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../../includes/layouts/admin_footer.php'; ?>
