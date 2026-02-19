# Cloud 9 Cafe - User Management Access Control

## Overview

This document explains the access control mechanisms implemented in the user management system to ensure only authorized administrators can view, modify, or delete user accounts.

---

## Authentication Flow

```
┌─────────────────┐
│  User Requests  │
│  Admin Page     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐     ┌─────────────────┐
│  Check Session  │────▶│  No Session?    │
│  Exists?        │     │  Redirect to    │
└────────┬────────┘     │  Login Page     │
         │ Yes          └─────────────────┘
         ▼
┌─────────────────┐     ┌─────────────────┐
│  Check Admin    │────▶│  Not Admin?     │
│  Role?          │     │  Redirect to    │
└────────┬────────┘     │  User Dashboard │
         │ Yes          └─────────────────┘
         ▼
┌─────────────────┐
│  Grant Access   │
│  to Admin Panel │
└─────────────────┘
```

---

## Implementation

### 1. Admin Guard (`includes/auth/admin_guard.php`)

```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionUserId = $_SESSION['user_id'] ?? null;
$sessionAdminId = $_SESSION['admin_id'] ?? null;
$sessionRole = $_SESSION['role'] ?? null;

// Check for admin session
$hasAdminSession = !empty($sessionAdminId) || (!empty($sessionUserId) && $sessionRole === 'admin');

if (!$hasAdminSession) {
    header('Location: /cloud_9_cafe/admin/auth/login.php');
    exit;
}

// Prevent non-admin users from accessing admin pages
if ($sessionRole !== null && $sessionRole !== 'admin') {
    header('Location: /cloud_9_cafe/user/index.php');
    exit;
}
```

**Checks Performed:**
1. Session exists and is valid
2. User has admin role (`role = 'admin'`)
3. User is authenticated (has `user_id` or `admin_id`)

---

### 2. Self-Protection Mechanisms

#### Prevent Self-Deletion

```php
$currentUserId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;
if ($deleteId === $currentUserId) {
    $error = 'You cannot delete your own account.';
}
```

**Purpose:** Prevents administrators from accidentally deleting their own accounts, which could lock them out of the system.

#### Prevent Self-Deactivation

```php
if ($toggleId === $currentUserId) {
    $error = 'You cannot change your own account status.';
}
```

**Purpose:** Prevents administrators from deactivating their own accounts.

---

### 3. Role-Based Access Control (RBAC)

#### User Roles

| Role | Description | Access Level |
|------|-------------|--------------|
| `admin` | Administrator | Full access to admin panel |
| `user` | Regular customer | Access to user dashboard only |

#### Database Schema

```sql
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `phone` VARCHAR(20) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_is_active` (`is_active`)
);
```

---

### 4. User List Filtering

#### Show Only Regular Users

```php
$whereConditions = ["role = 'user'"]; // Only show regular users, not admins
```

**Purpose:** 
- Prevents administrators from seeing/managing other admin accounts
- Separates user management from admin management
- Reduces risk of accidentally modifying admin privileges

#### Filter Implementation

```php
// Search filter
if ($search !== '') {
    $whereConditions[] = "(full_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

// Status filter
if ($statusFilter !== '') {
    $whereConditions[] = "is_active = :status";
    $params[':status'] = $statusFilter === 'active' ? 1 : 0;
}
```

---

### 5. Soft Delete vs Hard Delete

#### Logic

```php
// Check if user has orders
$checkStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$checkStmt->execute([$deleteId]);
$hasOrders = $checkStmt->fetchColumn() > 0;

if ($hasOrders) {
    // Soft delete - deactivate account
    $deactivateStmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
    $deactivateStmt->execute([$deleteId]);
} else {
    // Hard delete - remove from database
    $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $deleteStmt->execute([$deleteId]);
}
```

**Purpose:**
- Preserves order history for users with transactions
- Maintains referential integrity
- Allows data recovery if needed

---

## Access Control Matrix

| Action | Admin | User | Guest |
|--------|-------|------|-------|
| View User List | ✅ | ❌ | ❌ |
| View User Details | ✅ | ❌ | ❌ |
| Create User | ✅ | ❌ | ❌ |
| Edit User | ✅ | ❌ | ❌ |
| Delete User | ✅ | ❌ | ❌ |
| Toggle User Status | ✅ | ❌ | ❌ |
| Delete Own Account | ❌ | ✅* | ❌ |

*Users can delete their own account through user settings, not admin panel.

---

## Session Security

### Session Variables

| Variable | Description | Set When |
|----------|-------------|----------|
| `user_id` | User's database ID | Login |
| `admin_id` | Admin's database ID | Admin Login |
| `full_name` | User's full name | Login |
| `email` | User's email | Login |
| `role` | User's role ('admin'/'user') | Login |

### Session Configuration

```php
// php.ini recommendations
session.cookie_httponly = 1
session.cookie_secure = 1      // HTTPS only
session.use_strict_mode = 1
session.cookie_samesite = "Strict"
session.gc_maxlifetime = 3600  // 1 hour
```

---

## Security Headers

Recommended headers for admin pages:

```http
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self'; script-src 'self' cdn.jsdelivr.net; style-src 'self' cdn.jsdelivr.net fonts.googleapis.com;
```

---

## Audit Trail (Recommended)

Track admin actions for security:

```sql
CREATE TABLE `admin_audit_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` BIGINT UNSIGNED NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `target_type` VARCHAR(50) NOT NULL,  -- 'user', 'menu_item', etc.
  `target_id` BIGINT UNSIGNED,
  `details` JSON,
  `ip_address` VARCHAR(45),
  `user_agent` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
);
```

### Usage Example

```php
function logAdminAction($pdo, $adminId, $action, $targetType, $targetId, $details = null) {
    $stmt = $pdo->prepare("INSERT INTO admin_audit_log 
        (admin_id, action, target_type, target_id, details, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $adminId,
        $action,
        $targetType,
        $targetId,
        $details ? json_encode($details) : null,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}

// Log user deletion
logAdminAction($pdo, $currentUserId, 'delete', 'user', $deleteId, ['reason' => 'user_request']);
```

---

## Common Security Threats & Mitigations

| Threat | Mitigation |
|--------|------------|
| Session Hijacking | HTTP-only cookies, secure flag, short session lifetime |
| Privilege Escalation | Strict role checking, parameterized queries |
| IDOR (Insecure Direct Object Reference) | Verify ownership, use UUIDs instead of sequential IDs |
| CSRF | CSRF tokens on all state-changing operations |
| Brute Force | Rate limiting on login attempts |
| SQL Injection | PDO prepared statements |
| XSS | Output encoding with `htmlspecialchars()` |

---

## Testing Access Control

### Test Cases

1. **Unauthenticated Access**
   - Clear cookies/session
   - Try accessing `/admin/users/list.php`
   - Expected: Redirect to login page

2. **Non-Admin Access**
   - Login as regular user
   - Try accessing `/admin/users/list.php`
   - Expected: Redirect to user dashboard

3. **Self-Deletion Prevention**
   - Login as admin
   - Try to delete own account
   - Expected: Error message

4. **Cross-Admin Protection**
   - Create two admin accounts
   - Login as Admin A
   - Try to delete Admin B
   - Expected: Admin B not visible in list (only users shown)

---

*Last Updated: February 2026*
*Version: 1.0*
