<?php
$pageTitle = 'Cloud 9 Cafe | Menu';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers/functions.php';

$menuItems = [];
$menuLoadError = '';

try {
    $stmt = $pdo->query(
        "SELECT id, name, description, price, image_path
         FROM menu_items
         WHERE is_available = 1
         ORDER BY category ASC, name ASC"
    );
    $menuItems = $stmt->fetchAll();
} catch (Throwable $e) {
    $menuLoadError = 'We could not load the menu right now. Please try again shortly.';
}

function menuPlaceholderImage(string $name): string
{
    $short = strtoupper(substr(trim($name), 0, 18));
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 600">'
        . '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
        . '<stop offset="0%" stop-color="#6f4d3b"/>'
        . '<stop offset="100%" stop-color="#c08d5d"/>'
        . '</linearGradient></defs>'
        . '<rect width="900" height="600" fill="url(#g)"/>'
        . '<circle cx="730" cy="120" r="120" fill="rgba(255,255,255,0.16)"/>'
        . '<text x="50%" y="52%" text-anchor="middle" fill="#fff8ef"'
        . ' font-family="Arial,sans-serif" font-size="56" letter-spacing="2">'
        . htmlspecialchars($short, ENT_QUOTES, 'UTF-8')
        . '</text>'
        . '</svg>';

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}
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
                <li class="nav-item"><a class="nav-link active" aria-current="page" href="/cloud_9_cafe/guest/menu.php">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="/cloud_9_cafe/guest/index.php#about-preview">About</a></li>
                <li class="nav-item"><a class="nav-link" href="/cloud_9_cafe/guest/contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link nav-action" href="/cloud_9_cafe/guest/auth/login.php">Login</a></li>
                <li class="nav-item"><a class="btn btn-cafe-primary btn-sm" href="/cloud_9_cafe/guest/auth/register.php">Register</a></li>
            </ul>
        </div>
    </div>
</nav>

<section class="menu-page-hero">
    <div class="container">
        <div class="menu-page-hero-content text-center text-lg-start">
            <span class="section-kicker">Our Coffee Kitchen</span>
            <h1>Explore The Cloud 9 Cafe Menu</h1>
            <p>Freshly brewed drinks and handcrafted bites, prepared all day for every mood.</p>
        </div>
    </div>
</section>

<section class="menu-page-grid section-space pt-0">
    <div class="container">
        <?php if ($menuLoadError !== ''): ?>
            <div class="alert alert-warning border-0 menu-alert mb-4" role="alert">
                <?php echo e($menuLoadError); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($menuItems)): ?>
            <div class="menu-empty-state text-center">
                <h2>No menu items available yet</h2>
                <p>Please check back soon. New items are being prepared.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($menuItems as $item): ?>
                    <?php
                    $imagePath = trim((string) ($item['image_path'] ?? ''));
                    $imageSrc = $imagePath !== ''
                        ? '/cloud_9_cafe/' . ltrim($imagePath, '/')
                        : menuPlaceholderImage((string) ($item['name'] ?? 'Cloud 9 Item'));

                    $name = (string) ($item['name'] ?? 'Menu Item');
                    $description = trim((string) ($item['description'] ?? ''));
                    if ($description === '') {
                        $description = 'Freshly prepared by Cloud 9 Cafe.';
                    }
                    $price = number_format((float) ($item['price'] ?? 0), 2);
                    ?>

                    <div class="col-12 col-sm-6 col-xl-4">
                        <article class="menu-item-card h-100">
                            <div class="menu-item-image-wrap">
                                <img src="<?php echo e($imageSrc); ?>" alt="<?php echo e($name); ?>" class="menu-item-image">
                                <span class="menu-item-badge">Fresh Pick</span>
                            </div>
                            <div class="menu-item-body">
                                <h3 class="menu-item-title"><?php echo e($name); ?></h3>
                                <p class="menu-item-description"><?php echo e($description); ?></p>
                                <div class="menu-item-footer">
                                    <span class="menu-item-price">$<?php echo e($price); ?></span>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
