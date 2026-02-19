# Cloud 9 Cafe - User Management Security Checklist

## Overview

This document provides a comprehensive security checklist for the user management system. Use this to verify security measures before deployment and for regular security audits.

---

## ✅ Pre-Deployment Security Checklist

### Authentication & Authorization

- [ ] Admin guard is included at the top of every admin page
- [ ] Session validation checks for both `user_id` and `admin_id`
- [ ] Role verification ensures only `role = 'admin'` can access
- [ ] Non-admin users are redirected to user dashboard
- [ ] Unauthenticated users are redirected to login page
- [ ] Session timeout is configured (recommend 1 hour)
- [ ] Session regeneration on privilege escalation

### Input Validation

- [ ] All user inputs are sanitized with `trim()`
- [ ] ID parameters are cast to integers using `intval()`
- [ ] Search inputs use parameterized queries
- [ ] Email validation on all email fields
- [ ] Phone number validation (if required)
- [ ] Name fields have maximum length limits (100 chars)

### Output Encoding

- [ ] All dynamic output uses `htmlspecialchars()`
- [ ] `ENT_QUOTES` flag is used
- [ ] UTF-8 encoding is specified
- [ ] JavaScript context variables are properly escaped
- [ ] URL parameters use `urlencode()`

### Database Security

- [ ] PDO prepared statements for all queries
- [ ] Parameter binding for all user inputs
- [ ] No string concatenation in SQL queries
- [ ] Database user has minimal required privileges
- [ ] Connection uses UTF-8 charset (utf8mb4)

### File System Security

- [ ] Upload directory is outside web root (if possible)
- [ ] Upload directory has proper permissions (755)
- [ ] File type validation for uploads
- [ ] File size limits enforced
- [ ] Unique filenames generated with `uniqid()`
- [ ] `basename()` used to prevent path traversal

---

## ✅ User Management Specific Checks

### Self-Protection

- [ ] Admin cannot delete own account
- [ ] Admin cannot deactivate own account
- [ ] Current user ID is retrieved from session, not URL
- [ ] Error message shown on self-deletion attempt

```php
$currentUserId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;
if ($deleteId === $currentUserId) {
    $error = 'You cannot delete your own account.';
}
```

### User Isolation

- [ ] User list only shows `role = 'user'` (not admins)
- [ ] Admin accounts are excluded from user management
- [ ] Separate admin management interface (if needed)

```php
$whereConditions = ["role = 'user'"]; // Only regular users
```

### Delete Protection

- [ ] Confirmation modal before deletion
- [ ] CSRF token validation (recommended)
- [ ] Soft delete for users with orders
- [ ] Hard delete only for users without related data
- [ ] Audit log entry for all deletions

```php
// Check for related data
$checkStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$checkStmt->execute([$deleteId]);
$hasOrders = $checkStmt->fetchColumn() > 0;

if ($hasOrders) {
    // Soft delete
    $deactivateStmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
    $deactivateStmt->execute([$deleteId]);
} else {
    // Hard delete
    $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $deleteStmt->execute([$deleteId]);
}
```

---

## ✅ Session Security

### Session Configuration

```ini
; php.ini settings
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
session.cookie_samesite = "Strict"
session.gc_maxlifetime = 3600
session.gc_probability = 1
session.gc_divisor = 100
```

### Session Handling

- [ ] `session_start()` called before any output
- [ ] Session validation on every request
- [ ] Session destruction on logout
- [ ] Session ID regeneration on login
- [ ] No sensitive data stored in session

---

## ✅ Password Security

### Password Requirements

- [ ] Minimum 8 characters
- [ ] At least one uppercase letter
- [ ] At least one lowercase letter
- [ ] At least one number
- [ ] At least one special character
- [ ] Password history (prevent reuse of last 5)

### Password Storage

- [ ] Passwords hashed with bcrypt
- [ ] Salt automatically generated
- [ ] Minimum cost factor of 10
- [ ] No plaintext password storage

```php
// Correct way
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Verification
if (password_verify($password, $hash)) {
    // Valid password
}
```

---

## ✅ XSS Prevention

### Context-Aware Encoding

| Context | Function | Example |
|---------|----------|---------|
| HTML Body | `htmlspecialchars()` | `<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>` |
| HTML Attribute | `htmlspecialchars()` with quotes | `value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>"` |
| JavaScript | `json_encode()` | `var name = <?php echo json_encode($name); ?>;` |
| URL | `urlencode()` | `href="?search=<?php echo urlencode($search); ?>"` |

### Content Security Policy

```http
Content-Security-Policy: 
    default-src 'self';
    script-src 'self' cdn.jsdelivr.net;
    style-src 'self' cdn.jsdelivr.net fonts.googleapis.com;
    font-src 'self' fonts.gstatic.com;
    img-src 'self' data:;
    connect-src 'self';
    frame-ancestors 'none';
    base-uri 'self';
    form-action 'self';
```

---

## ✅ CSRF Protection

### Implementation

```php
// Generate token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Add to form
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

// Validate on submission
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token validation failed');
}
```

### Token Rotation

- [ ] New token generated per session
- [ ] Token rotated on privilege change
- [ ] Token validated on all POST requests

---

## ✅ Rate Limiting

### Login Attempts

```php
// Track failed attempts
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

// Check if locked out
if ($_SESSION['login_attempts'] >= 5) {
    $lockoutTime = 900; // 15 minutes
    if (time() - $_SESSION['last_attempt_time'] < $lockoutTime) {
        die('Too many failed attempts. Please try again later.');
    } else {
        // Reset after lockout period
        $_SESSION['login_attempts'] = 0;
    }
}
```

### API Rate Limiting

- [ ] Maximum 100 requests per minute per IP
- [ ] Maximum 10 login attempts per 15 minutes
- [ ] Rate limit headers in responses

---

## ✅ Error Handling

### Error Display

- [ ] Production: `display_errors = Off`
- [ ] Development: `display_errors = On`
- [ ] Custom error pages for 404, 500, etc.
- [ ] No sensitive information in error messages

### Error Logging

```php
// Log errors securely
error_log("User deletion failed: " . $e->getMessage());

// User sees generic message
echo "An error occurred. Please try again.";
```

---

## ✅ HTTPS & Transport Security

### SSL/TLS Configuration

- [ ] HTTPS enforced on all pages
- [ ] HSTS header enabled
- [ ] Secure cookies flag set
- [ ] TLS 1.2 or higher only
- [ ] Weak ciphers disabled

### Security Headers

```http
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=()
```

---

## ✅ File Upload Security

### Validation Checklist

- [ ] File type whitelist (not blacklist)
- [ ] MIME type verification
- [ ] File extension validation
- [ ] File size limits
- [ ] Virus scanning (if available)
- [ ] Store outside web root
- [ ] Serve via PHP script (not direct URL)

```php
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

if (!in_array($_FILES['image']['type'], $allowedTypes)) {
    $errors['image'] = 'Invalid file type.';
}

if ($_FILES['image']['size'] > $maxSize) {
    $errors['image'] = 'File too large.';
}
```

---

## 🔍 Security Audit Checklist

### Daily

- [ ] Review failed login attempts
- [ ] Check for unusual activity
- [ ] Monitor error logs

### Weekly

- [ ] Review admin actions audit log
- [ ] Check for inactive admin accounts
- [ ] Verify backup integrity

### Monthly

- [ ] Security scan with tools (OWASP ZAP, etc.)
- [ ] Review user permissions
- [ ] Update dependencies
- [ ] Check SSL certificate expiration

### Quarterly

- [ ] Full penetration test
- [ ] Code security review
- [ ] Update security documentation
- [ ] Security training for staff

---

## 🚨 Incident Response

### If Security Breach Suspected

1. **Immediate Actions**
   - Disable affected admin accounts
   - Force password reset for all admins
   - Enable maintenance mode
   - Preserve logs

2. **Investigation**
   - Review access logs
   - Check audit trail
   - Identify entry point
   - Assess data exposure

3. **Recovery**
   - Patch vulnerabilities
   - Restore from clean backup if needed
   - Re-enable accounts with new credentials
   - Notify affected users

4. **Prevention**
   - Implement additional security measures
   - Update security policies
   - Conduct security training

---

## 📋 Quick Security Test

Run these tests after deployment:

```bash
# Test 1: Unauthenticated access
curl -I http://yoursite.com/admin/users/list.php
# Expected: 302 Redirect to login

# Test 2: SQL Injection attempt
curl "http://yoursite.com/admin/users/list.php?search=' OR '1'='1"
# Expected: No error, sanitized search

# Test 3: XSS attempt
curl "http://yoursite.com/admin/users/list.php?search=<script>alert(1)</script>"
# Expected: Script tags encoded in output

# Test 4: IDOR test
curl "http://yoursite.com/admin/users/view.php?id=999999"
# Expected: "Not found" or redirect
```

---

*Last Updated: February 2026*
*Version: 1.0*
