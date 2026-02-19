<?php
require_once __DIR__ . '/../../includes/auth/admin_guard.php';
require_once __DIR__ . '/../../includes/db.php';

$pageTitle = 'Cloud 9 Cafe | Edit Menu Item';
$extraCssFiles = ['/cloud_9_cafe/assets/css/admin.css'];

$adminName = trim((string) ($_SESSION['full_name'] ?? $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Administrator'));
if ($adminName === '') {
    $adminName = 'Administrator';
}

// Get item ID
$itemId = intval($_GET['id'] ?? 0);
if ($itemId <= 0) {
    header("Location: list.php?error=invalid_id");
    exit;
}

// Initialize variables
$errors = [];
$formData = [];
$currentImage = null;

$categories = ['beverages', 'food', 'desserts', 'specials'];

// Fetch existing item
try {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();
    
    if (!$item) {
        header("Location: list.php?error=not_found");
        exit;
    }
    
    // Populate form data
    $formData = [
        'name' => $item['name'],
        'description' => $item['description'] ?? '',
        'category' => $item['category'],
        'price' => $item['price'],
        'is_available' => $item['is_available']
    ];
    $currentImage = $item['image_path'];
    
} catch (PDOException $e) {
    header("Location: list.php?error=fetch_failed");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $formData['name'] = trim($_POST['name'] ?? '');
    $formData['description'] = trim($_POST['description'] ?? '');
    $formData['category'] = trim($_POST['category'] ?? '');
    $formData['price'] = trim($_POST['price'] ?? '');
    $formData['is_available'] = isset($_POST['is_available']) ? 1 : 0;

    // Validation
    if (empty($formData['name'])) {
        $errors['name'] = 'Item name is required.';
    } elseif (strlen($formData['name']) > 120) {
        $errors['name'] = 'Item name must not exceed 120 characters.';
    }

    if (empty($formData['category'])) {
        $errors['category'] = 'Category is required.';
    }

    if (empty($formData['price'])) {
        $errors['price'] = 'Price is required.';
    } elseif (!is_numeric($formData['price']) || floatval($formData['price']) < 0) {
        $errors['price'] = 'Price must be a valid positive number.';
    } elseif (floatval($formData['price']) > 9999.99) {
        $errors['price'] = 'Price must not exceed $9,999.99.';
    }

    // Handle image upload
    $imagePath = $currentImage;
    $deleteCurrentImage = isset($_POST['delete_image']);
    
    if ($deleteCurrentImage && $currentImage) {
        // Mark current image for deletion
        $imagePath = null;
    }
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if ($_FILES['image']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['image']['size'] > $maxSize) {
            $errors['image'] = 'Image size must not exceed 5MB.';
        } elseif (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $errors['image'] = 'Only JPG, PNG, GIF, and WebP images are allowed.';
        } else {
            $uploadDir = __DIR__ . '/../../uploads/menu/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                // Delete old image if exists
                if ($currentImage) {
                    $oldImagePath = __DIR__ . '/../../' . $currentImage;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $imagePath = 'uploads/menu/' . $fileName;
            } else {
                $errors['image'] = 'Failed to upload image. Please try again.';
            }
        }
    }

    // If no errors, update database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE menu_items 
                                   SET name = :name, 
                                       description = :description, 
                                       category = :category, 
                                       price = :price, 
                                       image_path = :image_path, 
                                       is_available = :is_available,
                                       updated_at = NOW()
                                   WHERE id = :id");
            
            $stmt->execute([
                ':name' => $formData['name'],
                ':description' => $formData['description'],
                ':category' => $formData['category'],
                ':price' => floatval($formData['price']),
                ':image_path' => $imagePath,
                ':is_available' => $formData['is_available'],
                ':id' => $itemId
            ]);
            
            // Delete image file if marked for deletion
            if ($deleteCurrentImage && $currentImage) {
                $oldImagePath = __DIR__ . '/../../' . $currentImage;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            header("Location: list.php?success=updated");
            exit;
        } catch (PDOException $e) {
            $errors['general'] = 'Failed to update menu item. Please try again.';
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
                    <h1 class="content-title">Edit Menu Item</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/cloud_9_cafe/admin/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="list.php">Menu Items</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Item</li>
                        </ol>
                    </nav>
                </div>
                <div class="content-actions">
                    <a href="list.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>

            <!-- General Error -->
            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <?php echo htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="bi bi-pencil-square me-2"></i>Edit Item: <?php echo htmlspecialchars($formData['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="menu-item-form" id="menuItemForm">
                        <div class="row g-4">
                            <!-- Left Column - Basic Info -->
                            <div class="col-lg-8">
                                <!-- Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        Item Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                           id="name" 
                                           name="name" 
                                           value="<?php echo htmlspecialchars($formData['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                           maxlength="120"
                                           placeholder="e.g., Caramel Macchiato"
                                           required>
                                    <div class="form-text">Maximum 120 characters</div>
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="4"
                                              maxlength="500"
                                              placeholder="Describe the item, ingredients, etc."><?php echo htmlspecialchars($formData['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    <div class="form-text">
                                        <span id="descCounter"><?php echo strlen($formData['description']); ?></span>/500 characters
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Category -->
                                    <div class="col-md-6 mb-3">
                                        <label for="category" class="form-label">
                                            Category <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select <?php echo isset($errors['category']) ? 'is-invalid' : ''; ?>" 
                                                id="category" 
                                                name="category" 
                                                required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat; ?>" 
                                                        <?php echo $formData['category'] === $cat ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($cat); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (isset($errors['category'])): ?>
                                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['category'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Price -->
                                    <div class="col-md-6 mb-3">
                                        <label for="price" class="form-label">
                                            Price ($) <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" 
                                                   class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>" 
                                                   id="price" 
                                                   name="price" 
                                                   value="<?php echo htmlspecialchars($formData['price'], ENT_QUOTES, 'UTF-8'); ?>"
                                                   step="0.01"
                                                   min="0"
                                                   max="9999.99"
                                                   placeholder="0.00"
                                                   required>
                                        </div>
                                        <?php if (isset($errors['price'])): ?>
                                            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['price'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Availability -->
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="is_available" 
                                               name="is_available" 
                                               value="1"
                                               <?php echo $formData['is_available'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_available">
                                            Item is available for ordering
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Image Upload -->
                            <div class="col-lg-4">
                                <div class="image-upload-section">
                                    <label class="form-label">Item Image</label>
                                    
                                    <?php if ($currentImage): ?>
                                        <div class="current-image mb-3">
                                            <img src="/cloud_9_cafe/<?php echo htmlspecialchars($currentImage, ENT_QUOTES, 'UTF-8'); ?>" 
                                                 alt="Current image" 
                                                 class="img-fluid rounded">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="delete_image" name="delete_image" value="1">
                                                <label class="form-check-label text-danger" for="delete_image">
                                                    <i class="bi bi-trash me-1"></i>Remove current image
                                                </label>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="image-upload-area <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>" 
                                         id="imageUploadArea">
                                        <input type="file" 
                                               class="image-upload-input" 
                                               id="image" 
                                               name="image" 
                                               accept="image/jpeg,image/png,image/gif,image/webp">
                                        <div class="image-upload-placeholder" id="uploadPlaceholder">
                                            <i class="bi bi-cloud-upload"></i>
                                            <span><?php echo $currentImage ? 'Replace image' : 'Click or drag image here'; ?></span>
                                            <small>JPG, PNG, GIF, WebP (max 5MB)</small>
                                        </div>
                                        <div class="image-preview" id="imagePreview" style="display: none;">
                                            <img src="" alt="Preview" id="previewImg">
                                            <button type="button" class="image-remove" id="removeImage">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php if (isset($errors['image'])): ?>
                                        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['image'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Update Item
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

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 992) {
                if (!sidebar.contains(e.target) && !topbarToggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });

        // Description character counter
        const descInput = document.getElementById('description');
        const descCounter = document.getElementById('descCounter');
        
        if (descInput && descCounter) {
            descInput.addEventListener('input', function() {
                descCounter.textContent = this.value.length;
            });
        }

        // Image upload preview
        const imageInput = document.getElementById('image');
        const uploadArea = document.getElementById('imageUploadArea');
        const placeholder = document.getElementById('uploadPlaceholder');
        const preview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const removeBtn = document.getElementById('removeImage');

        if (uploadArea) {
            uploadArea.addEventListener('click', function(e) {
                if (e.target !== removeBtn && !removeBtn.contains(e.target)) {
                    imageInput.click();
                }
            });

            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', function() {
                this.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    imageInput.files = files;
                    handleImageSelect(files[0]);
                }
            });
        }

        if (imageInput) {
            imageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    handleImageSelect(this.files[0]);
                }
            });
        }

        function handleImageSelect(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                placeholder.style.display = 'none';
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }

        if (removeBtn) {
            removeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                imageInput.value = '';
                previewImg.src = '';
                preview.style.display = 'none';
                placeholder.style.display = 'flex';
            });
        }

        // Form validation
        const form = document.getElementById('menuItemForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const priceInput = document.getElementById('price');
                const price = parseFloat(priceInput.value);
                
                if (isNaN(price) || price < 0) {
                    e.preventDefault();
                    priceInput.classList.add('is-invalid');
                    alert('Please enter a valid price.');
                    return false;
                }
                
                if (price > 9999.99) {
                    e.preventDefault();
                    priceInput.classList.add('is-invalid');
                    alert('Price must not exceed $9,999.99.');
                    return false;
                }
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../../includes/layouts/admin_footer.php'; ?>
