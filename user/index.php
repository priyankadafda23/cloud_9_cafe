<?php
require_once __DIR__ . '/../includes/auth/auth_guard.php';

$pageTitle = 'Cloud 9 Cafe | User Dashboard';
$extraCssFiles = ['/cloud_9_cafe/assets/css/user.css'];

$rawUserName = $_SESSION['full_name'] ?? $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Guest';
$displayName = trim((string) $rawUserName);
if ($displayName === '') {
    $displayName = 'Guest';
}

require_once __DIR__ . '/../includes/layouts/user_header.php';
?>

<section class="user-dashboard-page section-space">
    <div class="container-fluid container-xxl">
        <div class="row g-4">
            <aside class="col-lg-4 col-xl-3">
                <div class="user-sidebar-card">
                    <div class="user-sidebar-head">
                        <span class="user-sidebar-kicker">Cloud 9 Cafe</span>
                        <h2><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p>Manage your profile, preferences, and account settings.</p>
                    </div>

                    <nav aria-label="User dashboard navigation">
                        <ul class="nav flex-column user-sidebar-nav">
                            <li class="nav-item">
                                <a class="nav-link active" href="/cloud_9_cafe/user/index.php">
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
                    <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="dashboard-kicker">Welcome Back</span>
                            <h1>Hello, <?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></h1>
                            <p>Ready for your next coffee moment? Check what is fresh on today's menu.</p>
                        </div>
                        <a href="/cloud_9_cafe/guest/menu.php" class="btn btn-cafe-primary btn-lg">
                            <i class="bi bi-cup-hot me-2"></i>View Menu
                        </a>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <article class="dashboard-action-card h-100">
                            <div class="dashboard-action-icon"><i class="bi bi-person-gear"></i></div>
                            <h3>Edit Profile</h3>
                            <p>Update your full name, phone number, and personal details.</p>
                            <a href="/cloud_9_cafe/user/account/profile.php" class="btn btn-cafe-outline">Open Profile</a>
                        </article>
                    </div>

                    <div class="col-md-6">
                        <article class="dashboard-action-card h-100">
                            <div class="dashboard-action-icon"><i class="bi bi-lock"></i></div>
                            <h3>Change Password</h3>
                            <p>Keep your account secure by updating your password regularly.</p>
                            <a href="/cloud_9_cafe/user/account/settings.php" class="btn btn-cafe-outline">Security Settings</a>
                        </article>
                    </div>

                    <div class="col-md-6">
                        <article class="dashboard-action-card h-100">
                            <div class="dashboard-action-icon"><i class="bi bi-journal-check"></i></div>
                            <h3>My Orders</h3>
                            <p>Track past orders and quickly reorder your favorites.</p>
                            <a href="/cloud_9_cafe/user/orders/history.php" class="btn btn-cafe-outline">Order History</a>
                        </article>
                    </div>

                    <div class="col-md-6">
                        <article class="dashboard-action-card h-100">
                            <div class="dashboard-action-icon"><i class="bi bi-box-arrow-right"></i></div>
                            <h3>Logout</h3>
                            <p>Sign out safely from your account when you are done.</p>
                            <a href="/cloud_9_cafe/user/logout.php" class="btn btn-cafe-outline">Logout</a>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/layouts/user_footer.php'; ?>

