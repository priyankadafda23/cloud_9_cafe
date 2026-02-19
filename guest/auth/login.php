<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'user';
    if ($role === 'admin') {
        header('Location: /cloud_9_cafe/admin/index.php');
    } else {
        header('Location: /cloud_9_cafe/user/index.php');
    }
    exit;
}

$pageTitle = 'Cloud 9 Cafe | Login';

$errorMessage = '';
$successMessage = '';

if (!empty($_SESSION['login_error'])) {
    $errorMessage = (string) $_SESSION['login_error'];
    unset($_SESSION['login_error']);
} elseif (!empty($_GET['error'])) {
    $errorCode = (string) $_GET['error'];
    $errorMap = [
        'invalid_credentials' => 'Invalid email or password.',
        'account_inactive' => 'Your account is inactive. Please contact support.',
        'session_expired' => 'Your session expired. Please login again.',
    ];
    $errorMessage = $errorMap[$errorCode] ?? 'Unable to login right now. Please try again.';
}

if (!empty($_GET['registered']) && $_GET['registered'] === '1') {
    $successMessage = 'Registration successful. Please login to continue.';
}

$prefillEmail = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $prefillEmail = strtolower(trim((string) $_POST['email']));
} elseif (!empty($_COOKIE['remember_email'])) {
    $prefillEmail = trim((string) $_COOKIE['remember_email']);
}

$rememberChecked = false;
if (!empty($_POST['remember_me']) || !empty($_COOKIE['remember_email'])) {
    $rememberChecked = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../includes/db.php';

    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $rememberChecked = !empty($_POST['remember_me']);
    $prefillEmail = $email;

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } elseif ($password === '') {
        $errorMessage = 'Password is required.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'SELECT id, full_name, email, password_hash, role, phone, is_active
                 FROM users
                 WHERE email = :email
                 LIMIT 1'
            );
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, (string) $user['password_hash'])) {
                $errorMessage = 'Invalid email or password.';
            } elseif ((int) ($user['is_active'] ?? 0) !== 1) {
                $errorMessage = 'Your account is inactive. Please contact support.';
            } else {
                session_regenerate_id(true);

                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['full_name'] = (string) ($user['full_name'] ?? '');
                $_SESSION['user_name'] = (string) ($user['full_name'] ?? '');
                $_SESSION['username'] = (string) ($user['full_name'] ?? '');
                $_SESSION['phone'] = (string) ($user['phone'] ?? '');
                $_SESSION['role'] = (string) ($user['role'] ?? 'user');

                if ($_SESSION['role'] === 'admin') {
                    $_SESSION['admin_id'] = (int) $user['id'];
                } else {
                    unset($_SESSION['admin_id']);
                }

                $updateLoginStmt = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
                $updateLoginStmt->execute([':id' => (int) $user['id']]);

                $secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
                if ($rememberChecked) {
                    setcookie('remember_email', $email, [
                        'expires' => time() + (86400 * 30),
                        'path' => '/',
                        'secure' => $secureCookie,
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]);
                } else {
                    setcookie('remember_email', '', [
                        'expires' => time() - 3600,
                        'path' => '/',
                        'secure' => $secureCookie,
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]);
                }

                if ($_SESSION['role'] === 'admin') {
                    header('Location: /cloud_9_cafe/admin/index.php');
                } else {
                    header('Location: /cloud_9_cafe/user/index.php');
                }
                exit;
            }
        } catch (Throwable $e) {
            $errorMessage = 'Unable to login right now. Please try again.';
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
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
                <li class="nav-item"><a class="nav-link" href="/cloud_9_cafe/guest/menu.php">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="/cloud_9_cafe/guest/index.php#about-preview">About</a></li>
                <li class="nav-item"><a class="nav-link" href="/cloud_9_cafe/guest/contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link nav-action active" aria-current="page" href="/cloud_9_cafe/guest/auth/login.php">Login</a></li>
                <li class="nav-item"><a class="btn btn-cafe-primary btn-sm" href="/cloud_9_cafe/guest/auth/register.php">Register</a></li>
            </ul>
        </div>
    </div>
</nav>

<section class="login-section section-space">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="login-shell">
                    <div class="row g-0">
                        <div class="col-lg-5 login-aside">
                            <span class="login-kicker">Cloud 9 Member Area</span>
                            <h1>Welcome Back</h1>
                            <p>Sign in to manage your orders, profile, and preferences.</p>
                            <ul class="list-unstyled login-points mb-0">
                                <li><i class="bi bi-check2-circle"></i> Track order history and favorites</li>
                                <li><i class="bi bi-check2-circle"></i> Update profile and password securely</li>
                                <li><i class="bi bi-check2-circle"></i> Access personalized cafe offers</li>
                            </ul>
                        </div>

                        <div class="col-lg-7">
                            <div class="login-form-wrap">
                                <h2>Login</h2>
                                <p class="login-subtitle">Enter your account credentials.</p>

                                <?php if ($errorMessage !== ''): ?>
                                    <div class="alert alert-danger border-0 login-alert" role="alert">
                                        <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($successMessage !== ''): ?>
                                    <div class="alert alert-success border-0 login-alert" role="alert">
                                        <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                <?php endif; ?>

                                <form id="loginForm" class="row g-3" method="post" action="/cloud_9_cafe/guest/auth/login.php" novalidate>
                                    <div class="col-12">
                                        <label for="loginEmail" class="form-label">Email</label>
                                        <input
                                            type="email"
                                            class="form-control login-input"
                                            id="loginEmail"
                                            name="email"
                                            maxlength="190"
                                            autocomplete="email"
                                            value="<?php echo htmlspecialchars($prefillEmail, ENT_QUOTES, 'UTF-8'); ?>"
                                            required
                                        >
                                        <div class="invalid-feedback" id="loginEmailError"></div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label for="loginPassword" class="form-label mb-0">Password</label>
                                            <a href="/cloud_9_cafe/guest/auth/forgot-password.php" class="login-link-small">Forgot password?</a>
                                        </div>
                                        <input
                                            type="password"
                                            class="form-control login-input mt-2"
                                            id="loginPassword"
                                            name="password"
                                            minlength="8"
                                            maxlength="72"
                                            autocomplete="current-password"
                                            required
                                        >
                                        <div class="invalid-feedback" id="loginPasswordError"></div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                value="1"
                                                id="rememberMe"
                                                name="remember_me"
                                                <?php echo $rememberChecked ? 'checked' : ''; ?>
                                            >
                                            <label class="form-check-label" for="rememberMe">
                                                Remember me on this device
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-12 d-grid d-sm-block mt-1">
                                        <button type="submit" class="btn btn-cafe-primary btn-lg">Sign In</button>
                                    </div>

                                    <div class="col-12">
                                        <p class="login-footer mb-0">New to Cloud 9 Cafe? <a href="/cloud_9_cafe/guest/auth/register.php">Create an account</a></p>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
