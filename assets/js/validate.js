document.addEventListener('DOMContentLoaded', function () {
    initLoginFormValidation();
    initContactFormValidation();
    initRegisterFormValidation();
    initProfileFormValidation();
    initChangePasswordValidation();
});

function setFieldState(input, errorNode, errorMessage) {
    if (!input || !errorNode) {
        return;
    }

    if (errorMessage) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        errorNode.textContent = errorMessage;
    } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        errorNode.textContent = '';
    }
}

function initContactFormValidation() {
    var form = document.getElementById('contactForm');
    if (!form) {
        return;
    }

    var nameInput = document.getElementById('contactName');
    var emailInput = document.getElementById('contactEmail');
    var messageInput = document.getElementById('contactMessage');

    var nameError = document.getElementById('contactNameError');
    var emailError = document.getElementById('contactEmailError');
    var messageError = document.getElementById('contactMessageError');
    var messageCounter = document.getElementById('messageCounter');

    function validateName() {
        var value = (nameInput.value || '').trim();

        if (value.length === 0) {
            setFieldState(nameInput, nameError, 'Name is required.');
            return false;
        }

        if (value.length < 2) {
            setFieldState(nameInput, nameError, 'Name must be at least 2 characters.');
            return false;
        }

        if (!/^[A-Za-z .'-]+$/.test(value)) {
            setFieldState(nameInput, nameError, 'Use letters, spaces, apostrophes, periods, or hyphens only.');
            return false;
        }

        setFieldState(nameInput, nameError, '');
        return true;
    }

    function validateEmail() {
        var value = (emailInput.value || '').trim();

        if (value.length === 0) {
            setFieldState(emailInput, emailError, 'Email is required.');
            return false;
        }

        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
        if (!emailPattern.test(value)) {
            setFieldState(emailInput, emailError, 'Enter a valid email address.');
            return false;
        }

        setFieldState(emailInput, emailError, '');
        return true;
    }

    function updateCounter() {
        if (!messageCounter || !messageInput) {
            return;
        }

        var length = messageInput.value.length;
        messageCounter.textContent = String(length) + '/1000';
    }

    function validateMessage() {
        var value = (messageInput.value || '').trim();
        updateCounter();

        if (value.length === 0) {
            setFieldState(messageInput, messageError, 'Message is required.');
            return false;
        }

        if (value.length < 10) {
            setFieldState(messageInput, messageError, 'Message must be at least 10 characters.');
            return false;
        }

        if (value.length > 1000) {
            setFieldState(messageInput, messageError, 'Message must be 1000 characters or fewer.');
            return false;
        }

        setFieldState(messageInput, messageError, '');
        return true;
    }

    nameInput.addEventListener('input', validateName);
    emailInput.addEventListener('input', validateEmail);
    messageInput.addEventListener('input', validateMessage);

    nameInput.addEventListener('blur', validateName);
    emailInput.addEventListener('blur', validateEmail);
    messageInput.addEventListener('blur', validateMessage);

    updateCounter();

    form.addEventListener('submit', function (event) {
        var isNameValid = validateName();
        var isEmailValid = validateEmail();
        var isMessageValid = validateMessage();

        if (!isNameValid || !isEmailValid || !isMessageValid) {
            event.preventDefault();
            event.stopPropagation();
        }
    });
}

function initRegisterFormValidation() {
    var form = document.getElementById('registerForm');
    if (!form) {
        return;
    }

    var nameInput = document.getElementById('regFullName');
    var emailInput = document.getElementById('regEmail');
    var phoneInput = document.getElementById('regPhone');
    var passwordInput = document.getElementById('regPassword');
    var confirmInput = document.getElementById('regConfirmPassword');

    var nameError = document.getElementById('regFullNameError');
    var emailError = document.getElementById('regEmailError');
    var phoneError = document.getElementById('regPhoneError');
    var passwordError = document.getElementById('regPasswordError');
    var confirmError = document.getElementById('regConfirmPasswordError');

    var strengthFill = document.getElementById('passwordStrengthFill');
    var strengthText = document.getElementById('passwordStrengthText');

    function validateName() {
        var value = (nameInput.value || '').trim();

        if (value.length === 0) {
            setFieldState(nameInput, nameError, 'Full name is required.');
            return false;
        }

        if (value.length < 2) {
            setFieldState(nameInput, nameError, 'Full name must be at least 2 characters.');
            return false;
        }

        if (!/^[A-Za-z .'-]+$/.test(value)) {
            setFieldState(nameInput, nameError, 'Use letters, spaces, apostrophes, periods, or hyphens only.');
            return false;
        }

        setFieldState(nameInput, nameError, '');
        return true;
    }

    function validateEmail() {
        var value = (emailInput.value || '').trim();

        if (value.length === 0) {
            setFieldState(emailInput, emailError, 'Email is required.');
            return false;
        }

        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
        if (!emailPattern.test(value)) {
            setFieldState(emailInput, emailError, 'Enter a valid email address.');
            return false;
        }

        setFieldState(emailInput, emailError, '');
        return true;
    }

    function validatePhone() {
        var value = (phoneInput.value || '').trim();

        if (value.length === 0) {
            setFieldState(phoneInput, phoneError, 'Phone is required.');
            return false;
        }

        if (!/^[0-9+()\-\s]+$/.test(value)) {
            setFieldState(phoneInput, phoneError, 'Use digits and + ( ) - only.');
            return false;
        }

        var digits = value.replace(/\D/g, '');
        if (digits.length < 7 || digits.length > 15) {
            setFieldState(phoneInput, phoneError, 'Phone number must contain 7 to 15 digits.');
            return false;
        }

        setFieldState(phoneInput, phoneError, '');
        return true;
    }

    function calculatePasswordStrength(value) {
        var score = 0;
        var checks = [
            value.length >= 8,
            /[a-z]/.test(value),
            /[A-Z]/.test(value),
            /[0-9]/.test(value),
            /[^A-Za-z0-9]/.test(value)
        ];

        checks.forEach(function (check) {
            if (check) {
                score += 1;
            }
        });

        return score;
    }

    function updatePasswordStrength() {
        if (!strengthFill || !strengthText) {
            return;
        }

        var value = passwordInput.value || '';
        var score = calculatePasswordStrength(value);
        var width = (score / 5) * 100;
        strengthFill.style.width = String(width) + '%';

        if (score <= 1) {
            strengthFill.dataset.level = 'weak';
            strengthText.textContent = 'Strength: Too weak';
        } else if (score <= 2) {
            strengthFill.dataset.level = 'fair';
            strengthText.textContent = 'Strength: Fair';
        } else if (score <= 3) {
            strengthFill.dataset.level = 'good';
            strengthText.textContent = 'Strength: Good';
        } else if (score <= 4) {
            strengthFill.dataset.level = 'strong';
            strengthText.textContent = 'Strength: Strong';
        } else {
            strengthFill.dataset.level = 'excellent';
            strengthText.textContent = 'Strength: Excellent';
        }
    }

    function validatePassword() {
        var value = passwordInput.value || '';

        if (value.length === 0) {
            setFieldState(passwordInput, passwordError, 'Password is required.');
            updatePasswordStrength();
            return false;
        }

        if (value.length < 8) {
            setFieldState(passwordInput, passwordError, 'Password must be at least 8 characters.');
            updatePasswordStrength();
            return false;
        }

        if (value.length > 72) {
            setFieldState(passwordInput, passwordError, 'Password must be 72 characters or fewer.');
            updatePasswordStrength();
            return false;
        }

        if (!/[a-z]/.test(value) || !/[A-Z]/.test(value) || !/[0-9]/.test(value) || !/[^A-Za-z0-9]/.test(value)) {
            setFieldState(passwordInput, passwordError, 'Use uppercase, lowercase, number, and special character.');
            updatePasswordStrength();
            return false;
        }

        setFieldState(passwordInput, passwordError, '');
        updatePasswordStrength();
        return true;
    }

    function validateConfirmPassword() {
        var passwordValue = passwordInput.value || '';
        var confirmValue = confirmInput.value || '';

        if (confirmValue.length === 0) {
            setFieldState(confirmInput, confirmError, 'Confirm password is required.');
            return false;
        }

        if (confirmValue !== passwordValue) {
            setFieldState(confirmInput, confirmError, 'Passwords do not match.');
            return false;
        }

        setFieldState(confirmInput, confirmError, '');
        return true;
    }

    nameInput.addEventListener('input', validateName);
    emailInput.addEventListener('input', validateEmail);
    phoneInput.addEventListener('input', validatePhone);
    passwordInput.addEventListener('input', function () {
        validatePassword();
        if (confirmInput.value) {
            validateConfirmPassword();
        }
    });
    confirmInput.addEventListener('input', validateConfirmPassword);

    nameInput.addEventListener('blur', validateName);
    emailInput.addEventListener('blur', validateEmail);
    phoneInput.addEventListener('blur', validatePhone);
    passwordInput.addEventListener('blur', validatePassword);
    confirmInput.addEventListener('blur', validateConfirmPassword);

    updatePasswordStrength();

    form.addEventListener('submit', function (event) {
        var isNameValid = validateName();
        var isEmailValid = validateEmail();
        var isPhoneValid = validatePhone();
        var isPasswordValid = validatePassword();
        var isConfirmValid = validateConfirmPassword();

        if (!isNameValid || !isEmailValid || !isPhoneValid || !isPasswordValid || !isConfirmValid) {
            event.preventDefault();
            event.stopPropagation();
        }
    });
}

function initLoginFormValidation() {
    var form = document.getElementById('loginForm');
    if (!form) {
        return;
    }

    var emailInput = document.getElementById('loginEmail');
    var passwordInput = document.getElementById('loginPassword');

    var emailError = document.getElementById('loginEmailError');
    var passwordError = document.getElementById('loginPasswordError');

    function validateEmail() {
        var value = (emailInput.value || '').trim();

        if (value.length === 0) {
            setFieldState(emailInput, emailError, 'Email is required.');
            return false;
        }

        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
        if (!emailPattern.test(value)) {
            setFieldState(emailInput, emailError, 'Enter a valid email address.');
            return false;
        }

        setFieldState(emailInput, emailError, '');
        return true;
    }

    function validatePassword() {
        var value = passwordInput.value || '';

        if (value.length === 0) {
            setFieldState(passwordInput, passwordError, 'Password is required.');
            return false;
        }

        if (value.length < 8) {
            setFieldState(passwordInput, passwordError, 'Password must be at least 8 characters.');
            return false;
        }

        if (value.length > 72) {
            setFieldState(passwordInput, passwordError, 'Password must be 72 characters or fewer.');
            return false;
        }

        setFieldState(passwordInput, passwordError, '');
        return true;
    }

    emailInput.addEventListener('input', validateEmail);
    passwordInput.addEventListener('input', validatePassword);
    emailInput.addEventListener('blur', validateEmail);
    passwordInput.addEventListener('blur', validatePassword);

    form.addEventListener('submit', function (event) {
        var isEmailValid = validateEmail();
        var isPasswordValid = validatePassword();

        if (!isEmailValid || !isPasswordValid) {
            event.preventDefault();
            event.stopPropagation();
        }
    });
}

function initProfileFormValidation() {
    var form = document.getElementById('profileForm');
    if (!form) {
        return;
    }

    var nameInput = document.getElementById('profileName');
    var phoneInput = document.getElementById('profilePhone');
    var imageInput = document.getElementById('profileImage');

    var nameError = document.getElementById('profileNameError');
    var phoneError = document.getElementById('profilePhoneError');
    var imageError = document.getElementById('profileImageError');

    var previewImage = document.getElementById('profileImagePreview');
    var previewMeta = document.getElementById('profileImageMeta');
    var resetBtn = document.getElementById('profileResetBtn');

    var defaultPreviewSrc = previewImage ? previewImage.getAttribute('data-default-src') || previewImage.src : '';
    var currentObjectUrl = null;

    function validateName() {
        var value = (nameInput.value || '').trim();

        if (value.length === 0) {
            setFieldState(nameInput, nameError, 'Name is required.');
            return false;
        }

        if (value.length < 2) {
            setFieldState(nameInput, nameError, 'Name must be at least 2 characters.');
            return false;
        }

        if (!/^[A-Za-z .'-]+$/.test(value)) {
            setFieldState(nameInput, nameError, 'Use letters, spaces, apostrophes, periods, or hyphens only.');
            return false;
        }

        setFieldState(nameInput, nameError, '');
        return true;
    }

    function validatePhone() {
        var value = (phoneInput.value || '').trim();

        if (value.length === 0) {
            setFieldState(phoneInput, phoneError, 'Phone is required.');
            return false;
        }

        if (!/^[0-9+()\-\s]+$/.test(value)) {
            setFieldState(phoneInput, phoneError, 'Use digits and + ( ) - only.');
            return false;
        }

        var digits = value.replace(/\D/g, '');
        if (digits.length < 7 || digits.length > 15) {
            setFieldState(phoneInput, phoneError, 'Phone number must contain 7 to 15 digits.');
            return false;
        }

        setFieldState(phoneInput, phoneError, '');
        return true;
    }

    function setImageError(message) {
        if (!imageInput || !imageError) {
            return;
        }

        if (message) {
            imageInput.classList.add('is-invalid');
            imageInput.classList.remove('is-valid');
            imageError.textContent = message;
        } else {
            imageInput.classList.remove('is-invalid');
            imageInput.classList.add('is-valid');
            imageError.textContent = '';
        }
    }

    function cleanupObjectUrl() {
        if (currentObjectUrl) {
            URL.revokeObjectURL(currentObjectUrl);
            currentObjectUrl = null;
        }
    }

    function resetPreview() {
        cleanupObjectUrl();
        if (previewImage && defaultPreviewSrc) {
            previewImage.src = defaultPreviewSrc;
        }
        if (previewMeta) {
            previewMeta.textContent = 'No new image selected.';
        }
    }

    function validateImage() {
        if (!imageInput) {
            return true;
        }

        var files = imageInput.files;
        if (!files || files.length === 0) {
            setImageError('');
            resetPreview();
            return true;
        }

        var file = files[0];
        var allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        var maxSizeBytes = 2 * 1024 * 1024;

        if (allowedTypes.indexOf(file.type) === -1) {
            setImageError('Only PNG, JPG, or WEBP files are allowed.');
            resetPreview();
            imageInput.value = '';
            return false;
        }

        if (file.size > maxSizeBytes) {
            setImageError('Image must be 2MB or smaller.');
            resetPreview();
            imageInput.value = '';
            return false;
        }

        setImageError('');
        cleanupObjectUrl();
        currentObjectUrl = URL.createObjectURL(file);
        if (previewImage) {
            previewImage.src = currentObjectUrl;
        }
        if (previewMeta) {
            var sizeKb = Math.round(file.size / 1024);
            previewMeta.textContent = file.name + ' (' + sizeKb + ' KB)';
        }
        return true;
    }

    nameInput.addEventListener('input', validateName);
    phoneInput.addEventListener('input', validatePhone);
    imageInput.addEventListener('change', validateImage);

    nameInput.addEventListener('blur', validateName);
    phoneInput.addEventListener('blur', validatePhone);

    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            setTimeout(function () {
                setFieldState(nameInput, nameError, '');
                setFieldState(phoneInput, phoneError, '');
                setImageError('');
                resetPreview();
            }, 0);
        });
    }

    form.addEventListener('submit', function (event) {
        var isNameValid = validateName();
        var isPhoneValid = validatePhone();
        var isImageValid = validateImage();

        if (!isNameValid || !isPhoneValid || !isImageValid) {
            event.preventDefault();
            event.stopPropagation();
        }
    });
}

function initChangePasswordValidation() {
    var form = document.getElementById('changePasswordForm');
    if (!form) {
        return;
    }

    var oldPasswordInput = document.getElementById('changeOldPassword');
    var newPasswordInput = document.getElementById('changeNewPassword');
    var confirmPasswordInput = document.getElementById('changeConfirmPassword');

    var oldPasswordError = document.getElementById('changeOldPasswordError');
    var newPasswordError = document.getElementById('changeNewPasswordError');
    var confirmPasswordError = document.getElementById('changeConfirmPasswordError');

    var strengthFill = document.getElementById('changePasswordStrengthFill');
    var strengthText = document.getElementById('changePasswordStrengthText');

    function validateOldPassword() {
        var value = oldPasswordInput.value || '';

        if (value.length === 0) {
            setFieldState(oldPasswordInput, oldPasswordError, 'Old password is required.');
            return false;
        }

        if (value.length < 8) {
            setFieldState(oldPasswordInput, oldPasswordError, 'Old password must be at least 8 characters.');
            return false;
        }

        if (value.length > 72) {
            setFieldState(oldPasswordInput, oldPasswordError, 'Old password must be 72 characters or fewer.');
            return false;
        }

        setFieldState(oldPasswordInput, oldPasswordError, '');
        return true;
    }

    function calculatePasswordStrength(value) {
        var score = 0;
        var checks = [
            value.length >= 8,
            /[a-z]/.test(value),
            /[A-Z]/.test(value),
            /[0-9]/.test(value),
            /[^A-Za-z0-9]/.test(value)
        ];

        checks.forEach(function (check) {
            if (check) {
                score += 1;
            }
        });

        return score;
    }

    function updatePasswordStrength() {
        if (!strengthFill || !strengthText) {
            return;
        }

        var value = newPasswordInput.value || '';
        var score = calculatePasswordStrength(value);
        var width = (score / 5) * 100;
        strengthFill.style.width = String(width) + '%';

        if (score <= 1) {
            strengthFill.dataset.level = 'weak';
            strengthText.textContent = 'Strength: Too weak';
        } else if (score <= 2) {
            strengthFill.dataset.level = 'fair';
            strengthText.textContent = 'Strength: Fair';
        } else if (score <= 3) {
            strengthFill.dataset.level = 'good';
            strengthText.textContent = 'Strength: Good';
        } else if (score <= 4) {
            strengthFill.dataset.level = 'strong';
            strengthText.textContent = 'Strength: Strong';
        } else {
            strengthFill.dataset.level = 'excellent';
            strengthText.textContent = 'Strength: Excellent';
        }
    }

    function validateNewPassword() {
        var oldValue = oldPasswordInput.value || '';
        var newValue = newPasswordInput.value || '';

        if (newValue.length === 0) {
            setFieldState(newPasswordInput, newPasswordError, 'New password is required.');
            updatePasswordStrength();
            return false;
        }

        if (newValue.length < 8) {
            setFieldState(newPasswordInput, newPasswordError, 'New password must be at least 8 characters.');
            updatePasswordStrength();
            return false;
        }

        if (newValue.length > 72) {
            setFieldState(newPasswordInput, newPasswordError, 'New password must be 72 characters or fewer.');
            updatePasswordStrength();
            return false;
        }

        if (!/[a-z]/.test(newValue) || !/[A-Z]/.test(newValue) || !/[0-9]/.test(newValue) || !/[^A-Za-z0-9]/.test(newValue)) {
            setFieldState(newPasswordInput, newPasswordError, 'Use uppercase, lowercase, number, and special character.');
            updatePasswordStrength();
            return false;
        }

        if (oldValue !== '' && newValue === oldValue) {
            setFieldState(newPasswordInput, newPasswordError, 'New password must be different from current password.');
            updatePasswordStrength();
            return false;
        }

        setFieldState(newPasswordInput, newPasswordError, '');
        updatePasswordStrength();
        return true;
    }

    function validateConfirmPassword() {
        var newValue = newPasswordInput.value || '';
        var confirmValue = confirmPasswordInput.value || '';

        if (confirmValue.length === 0) {
            setFieldState(confirmPasswordInput, confirmPasswordError, 'Confirm password is required.');
            return false;
        }

        if (confirmValue !== newValue) {
            setFieldState(confirmPasswordInput, confirmPasswordError, 'Passwords do not match.');
            return false;
        }

        setFieldState(confirmPasswordInput, confirmPasswordError, '');
        return true;
    }

    oldPasswordInput.addEventListener('input', function () {
        validateOldPassword();
        if (newPasswordInput.value) {
            validateNewPassword();
        }
    });
    newPasswordInput.addEventListener('input', function () {
        validateNewPassword();
        if (confirmPasswordInput.value) {
            validateConfirmPassword();
        }
    });
    confirmPasswordInput.addEventListener('input', validateConfirmPassword);

    oldPasswordInput.addEventListener('blur', validateOldPassword);
    newPasswordInput.addEventListener('blur', validateNewPassword);
    confirmPasswordInput.addEventListener('blur', validateConfirmPassword);

    updatePasswordStrength();

    form.addEventListener('submit', function (event) {
        var isOldValid = validateOldPassword();
        var isNewValid = validateNewPassword();
        var isConfirmValid = validateConfirmPassword();

        if (!isOldValid || !isNewValid || !isConfirmValid) {
            event.preventDefault();
            event.stopPropagation();
        }
    });
}
