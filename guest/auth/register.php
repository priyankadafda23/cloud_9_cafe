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

require_once __DIR__ . '/../../includes/helpers/functions.php';

$pageTitle = 'Cloud 9 Cafe | Register';
$generalError = '';

$formData = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
];

$fieldErrors = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'password' => '',
    'confirm_password' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
    $formData['email'] = strtolower(trim((string) ($_POST['email'] ?? '')));
    $formData['phone'] = trim((string) ($_POST['phone'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($formData['full_name'] === '') {
        $fieldErrors['full_name'] = 'Full name is required.';
    } elseif (strlen($formData['full_name']) < 2) {
        $fieldErrors['full_name'] = 'Full name must be at least 2 characters.';
    } elseif (!preg_match("/^[A-Za-z .'-]+$/", $formData['full_name'])) {
        $fieldErrors['full_name'] = 'Use letters, spaces, apostrophes, periods, or hyphens only.';
    }

    if ($formData['email'] === '') {
        $fieldErrors['email'] = 'Email is required.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['email'] = 'Enter a valid email address.';
    }

    if ($formData['phone'] === '') {
        $fieldErrors['phone'] = 'Phone is required.';
    } elseif (!preg_match('/^[0-9+()\-\s]+$/', $formData['phone'])) {
        $fieldErrors['phone'] = 'Use digits and + ( ) - only.';
    } else {
        $digits = preg_replace('/\D/', '', $formData['phone']);
        $digitCount = strlen((string) $digits);
        if ($digitCount < 7 || $digitCount > 15) {
            $fieldErrors['phone'] = 'Phone number must contain 7 to 15 digits.';
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

    if (!array_filter($fieldErrors)) {
        require_once __DIR__ . '/../../includes/db.php';

        try {
            $existingStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $existingStmt->execute([':email' => $formData['email']]);
            $existing = $existingStmt->fetch();

            if ($existing) {
                $fieldErrors['email'] = 'This email is already registered.';
            } else {
                $insertStmt = $pdo->prepare(
                    'INSERT INTO users (full_name, email, password_hash, role, phone, is_active)
                     VALUES (:full_name, :email, :password_hash, :role, :phone, 1)'
                );

                $insertStmt->execute([
                    ':full_name' => $formData['full_name'],
                    ':email' => $formData['email'],
                    ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    ':role' => 'user',
                    ':phone' => $formData['phone'],
                ]);

                header('Location: /cloud_9_cafe/guest/auth/login.php?registered=1');
                exit;
            }
        } catch (Throwable $e) {
            $generalError = 'Registration failed due to a server issue. Please try again.';
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
                <li class="nav-item"><a class="nav-link nav-action" href="/cloud_9_cafe/guest/auth/login.php">Login</a></li>
                <li class="nav-item"><a class="btn btn-cafe-primary btn-sm" href="/cloud_9_cafe/guest/auth/register.php" aria-current="page">Register</a></li>
            </ul>
        </div>
    </div>
</nav>

<section class="register-section section-space">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="register-shell">
                    <div class="row g-0">
                        <div class="col-lg-5 register-aside">
                            <span class="register-kicker">Welcome to Cloud 9</span>
                            <h1>Create Your Cafe Account</h1>
                            <p>Register to save favorites, place faster orders, and stay updated with fresh arrivals.</p>
                            <ul class="list-unstyled register-points mb-0">
                                <li><i class="bi bi-check2-circle"></i> Quick checkout for future orders</li>
                                <li><i class="bi bi-check2-circle"></i> Exclusive offers and member rewards</li>
                                <li><i class="bi bi-check2-circle"></i> Easy profile and preference management</li>
                            </ul>
                        </div>

                        <div class="col-lg-7">
                            <div class="register-form-wrap">
                                <h2>Sign Up</h2>
                                <p class="register-subtitle">Fill in your details to get started.</p>

                                <?php if ($generalError !== ''): ?>
                                    <div class="alert alert-danger border-0" role="alert"><?php echo e($generalError); ?></div>
                                <?php endif; ?>

                                <form id="registerForm" class="row g-3" method="post" action="/cloud_9_cafe/guest/auth/register.php" novalidate>
                                    <div class="col-12">
                                        <label for="regFullName" class="form-label">Full Name</label>
                                        <input type="text" class="form-control register-input<?php echo $fieldErrors['full_name'] !== '' ? ' is-invalid' : ''; ?>" id="regFullName" name="full_name" maxlength="100" autocomplete="name" value="<?php echo e($formData['full_name']); ?>" required>
                                        <div class="invalid-feedback" id="regFullNameError"><?php echo e($fieldErrors['full_name']); ?></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="regEmail" class="form-label">Email</label>
                                        <input type="email" class="form-control register-input<?php echo $fieldErrors['email'] !== '' ? ' is-invalid' : ''; ?>" id="regEmail" name="email" maxlength="190" autocomplete="email" value="<?php echo e($formData['email']); ?>" required>
                                        <div class="invalid-feedback" id="regEmailError"><?php echo e($fieldErrors['email']); ?></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="regPhone" class="form-label">Phone</label>
                                        <input type="tel" class="form-control register-input<?php echo $fieldErrors['phone'] !== '' ? ' is-invalid' : ''; ?>" id="regPhone" name="phone" maxlength="20" autocomplete="tel" placeholder="+1 555 123 4567" value="<?php echo e($formData['phone']); ?>" required>
                                        <div class="invalid-feedback" id="regPhoneError"><?php echo e($fieldErrors['phone']); ?></div>
                                    </div>

                                    <div class="col-12">
                                        <label for="regPassword" class="form-label">Password</label>
                                        <input type="password" class="form-control register-input<?php echo $fieldErrors['password'] !== '' ? ' is-invalid' : ''; ?>" id="regPassword" name="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                                        <div class="invalid-feedback" id="regPasswordError"><?php echo e($fieldErrors['password']); ?></div>

                                        <div class="password-strength mt-2" aria-live="polite">
                                            <div class="password-strength-bar">
                                                <span class="password-strength-fill" id="passwordStrengthFill"></span>
                                            </div>
                                            <small class="password-strength-text" id="passwordStrengthText">Strength: Too weak</small>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="regConfirmPassword" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control register-input<?php echo $fieldErrors['confirm_password'] !== '' ? ' is-invalid' : ''; ?>" id="regConfirmPassword" name="confirm_password" minlength="8" maxlength="72" autocomplete="new-password" required>
                                        <div class="invalid-feedback" id="regConfirmPasswordError"><?php echo e($fieldErrors['confirm_password']); ?></div>
                                    </div>

                                    <div class="col-12 d-grid d-sm-block mt-2">
                                        <button type="submit" class="btn btn-cafe-primary btn-lg">Create Account</button>
                                    </div>

                                    <div class="col-12">
                                        <p class="register-footer mb-0">Already have an account? <a href="/cloud_9_cafe/guest/auth/login.php">Login here</a></p>
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
