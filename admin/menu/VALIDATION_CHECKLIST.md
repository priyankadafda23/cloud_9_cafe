# Cloud 9 Cafe - Menu Management Validation Checklist

## Overview

This document provides a comprehensive validation checklist for the menu management system, covering both client-side and server-side validation.

---

## ✅ Server-Side Validation (PHP)

### Item Name Validation

| # | Check | Implementation | Status |
|---|-------|----------------|--------|
| 1 | Required field | `if (empty($formData['name']))` | ✅ |
| 2 | Maximum length (120 chars) | `strlen($formData['name']) > 120` | ✅ |
| 3 | HTML encoding output | `htmlspecialchars($name, ENT_QUOTES, 'UTF-8')` | ✅ |
| 4 | Trim whitespace | `trim($_POST['name'])` | ✅ |

```php
if (empty($formData['name'])) {
    $errors['name'] = 'Item name is required.';
} elseif (strlen($formData['name']) > 120) {
    $errors['name'] = 'Item name must not exceed 120 characters.';
}
```

---

### Category Validation

| # | Check | Implementation | Status |
|---|-------|----------------|--------|
| 1 | Required field | `if (empty($formData['category']))` | ✅ |
| 2 | Valid category value | Dropdown selection only | ✅ |
| 3 | Sanitize input | `trim($_POST['category'])` | ✅ |

```php
$categories = ['beverages', 'food', 'desserts', 'specials'];

if (empty($formData['category'])) {
    $errors['category'] = 'Category is required.';
}
```

---

### Price Validation

| # | Check | Implementation | Status |
|---|-------|----------------|--------|
| 1 | Required field | `if (empty($formData['price']))` | ✅ |
| 2 | Numeric value | `is_numeric($formData['price'])` | ✅ |
| 3 | Positive number | `floatval($formData['price']) >= 0` | ✅ |
| 4 | Maximum value ($9,999.99) | `floatval($formData['price']) <= 9999.99` | ✅ |
| 5 | Decimal precision | `DECIMAL(10,2)` in database | ✅ |

```php
if (empty($formData['price'])) {
    $errors['price'] = 'Price is required.';
} elseif (!is_numeric($formData['price']) || floatval($formData['price']) < 0) {
    $errors['price'] = 'Price must be a valid positive number.';
} elseif (floatval($formData['price']) > 9999.99) {
    $errors['price'] = 'Price must not exceed $9,999.99.';
}
```

---

### Description Validation

| # | Check | Implementation | Status |
|---|-------|----------------|--------|
| 1 | Optional field | No required check | ✅ |
| 2 | Maximum length (500 chars) | `maxlength="500"` + counter | ✅ |
| 3 | HTML encoding | `htmlspecialchars()` on output | ✅ |

```php
// No validation errors for empty description
// Character counter in JavaScript
```

---

### Image Upload Validation

| # | Check | Implementation | Status |
|---|-------|----------------|--------|
| 1 | File size limit (5MB) | `$_FILES['image']['size'] <= 5 * 1024 * 1024` | ✅ |
| 2 | Allowed file types | `['image/jpeg', 'image/png', 'image/gif', 'image/webp']` | ✅ |
| 3 | Upload errors handled | `$_FILES['image']['error']` check | ✅ |
| 4 | Unique filename | `uniqid() . '_' . basename($_FILES['image']['name'])` | ✅ |
| 5 | Secure file path | `basename()` to prevent traversal | ✅ |
| 6 | Directory exists | `mkdir($uploadDir, 0755, true)` | ✅ |

```php
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

if ($_FILES['image']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['image']['size'] > $maxSize) {
    $errors['image'] = 'Image size must not exceed 5MB.';
} elseif (!in_array($_FILES['image']['type'], $allowedTypes)) {
    $errors['image'] = 'Only JPG, PNG, GIF, and WebP images are allowed.';
}
```

---

### ID Parameter Validation

| # | Check | Implementation | Status |
|---|-------|----------------|--------|
| 1 | Required for edit/delete | `intval($_GET['id'] ?? 0)` | ✅ |
| 2 | Positive integer | `$itemId <= 0` check | ✅ |
| 3 | Item exists in DB | Fetch and verify | ✅ |
| 4 | SQL injection prevention | PDO prepared statements | ✅ |

```php
$itemId = intval($_GET['id'] ?? 0);
if ($itemId <= 0) {
    header("Location: list.php?error=invalid_id");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
$stmt->execute([$itemId]);
$item = $stmt->fetch();

if (!$item) {
    header("Location: list.php?error=not_found");
    exit;
}
```

---

## ✅ Client-Side Validation (JavaScript)

### Form Submission Validation

```javascript
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
```

| # | Check | Event | Status |
|---|-------|-------|--------|
| 1 | Price is numeric | Form submit | ✅ |
| 2 | Price is positive | Form submit | ✅ |
| 3 | Price max value | Form submit | ✅ |

---

### Character Counter

```javascript
const descInput = document.getElementById('description');
const descCounter = document.getElementById('descCounter');

descInput.addEventListener('input', function() {
    descCounter.textContent = this.value.length;
});
```

| # | Check | Implementation | Status |
|---|-------|----------------|--------|
| 1 | Real-time counter | Input event listener | ✅ |
| 2 | Max length enforcement | `maxlength="500"` attribute | ✅ |

---

### Image Preview Validation

| # | Check | Implementation | Status |
|---|-------|----------------|--------|
| 1 | File type filter | `accept="image/jpeg,image/png,image/gif,image/webp"` | ✅ |
| 2 | Preview before upload | FileReader API | ✅ |
| 3 | Remove image option | Clear file input | ✅ |

---

## ✅ HTML5 Validation Attributes

| Field | Attribute | Value | Purpose |
|-------|-----------|-------|---------|
| Name | `required` | - | Ensures field is not empty |
| Name | `maxlength` | 120 | Limits character count |
| Category | `required` | - | Ensures selection |
| Price | `required` | - | Ensures field is not empty |
| Price | `type` | number | Numeric keyboard on mobile |
| Price | `step` | 0.01 | Allows decimal values |
| Price | `min` | 0 | Prevents negative values |
| Price | `max` | 9999.99 | Prevents excessive values |
| Description | `maxlength` | 500 | Limits character count |
| Image | `accept` | image/* | Filters file picker |

---

## ✅ Database Constraints

| Column | Type | Constraints | Purpose |
|--------|------|-------------|---------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| `name` | VARCHAR(120) | NOT NULL | Item name (max 120 chars) |
| `description` | TEXT | NULL | Optional description |
| `category` | VARCHAR(60) | NOT NULL | Category name |
| `price` | DECIMAL(10,2) | NOT NULL | Price with 2 decimals |
| `image_path` | VARCHAR(255) | NULL | Optional image path |
| `is_available` | TINYINT(1) | NOT NULL, DEFAULT 1 | Availability flag |
| `created_by` | BIGINT UNSIGNED | NULL, FOREIGN KEY | Creator reference |

---

## 🔒 Security Validations

| # | Check | Implementation | Status |
|---|-------|----------------|--------|
| 1 | Admin authentication | `admin_guard.php` | ✅ |
| 2 | Role verification | `role = 'admin'` check | ✅ |
| 3 | SQL injection prevention | PDO prepared statements | ✅ |
| 4 | XSS prevention | `htmlspecialchars()` output | ✅ |
| 5 | CSRF token ready | Session-based | ⚠️ (Add token) |
| 6 | File upload security | Type & size validation | ✅ |
| 7 | Path traversal prevention | `basename()` usage | ✅ |

---

## 🧪 Testing Checklist

### Create Item Tests

- [ ] Submit with empty name → Shows error
- [ ] Submit with name > 120 chars → Shows error
- [ ] Submit with empty category → Shows error
- [ ] Submit with empty price → Shows error
- [ ] Submit with negative price → Shows error
- [ ] Submit with price > 9999.99 → Shows error
- [ ] Submit with non-numeric price → Shows error
- [ ] Submit with valid data → Success
- [ ] Upload image > 5MB → Shows error
- [ ] Upload non-image file → Shows error
- [ ] Upload valid image → Success with preview

### Edit Item Tests

- [ ] Edit with invalid ID → Redirect to list
- [ ] Edit non-existent item → Redirect to list
- [ ] Edit with empty name → Shows error
- [ ] Update image → Old image deleted
- [ ] Remove image checkbox → Image removed
- [ ] Valid update → Success message

### Delete Item Tests

- [ ] Click delete → Confirmation modal shows
- [ ] Cancel delete → Modal closes, item remains
- [ ] Confirm delete → Item removed, image deleted
- [ ] Delete with invalid ID → Error message

---

## 📋 Pre-Deployment Validation

Before deploying to production, verify:

- [ ] All server-side validations are active
- [ ] Error messages are user-friendly
- [ ] No sensitive data in error messages
- [ ] File upload directory is writable
- [ ] File upload directory is not publicly executable
- [ ] Database indexes are created
- [ ] CSRF tokens implemented (if required)
- [ ] Rate limiting on form submissions

---

## 🔄 Validation Flow

```
┌─────────────────┐
│  Form Submitted │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Trim Inputs    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐     ┌─────────────────┐
│  Validate Name  │────▶│  Error: Name    │
│  - Required     │     │  Required       │
│  - Max 120      │     └─────────────────┘
└────────┬────────┘
         │ Valid
         ▼
┌─────────────────┐     ┌─────────────────┐
│  Validate Cat   │────▶│  Error: Cat     │
│  - Required     │     │  Required       │
└────────┬────────┘     └─────────────────┘
         │ Valid
         ▼
┌─────────────────┐     ┌─────────────────┐
│  Validate Price │────▶│  Error: Invalid │
│  - Required     │     │  Price          │
│  - Numeric      │     └─────────────────┘
│  - 0 to 9999.99 │
└────────┬────────┘
         │ Valid
         ▼
┌─────────────────┐     ┌─────────────────┐
│  Validate Image │────▶│  Error: Image   │
│  - Size <= 5MB  │     │  Invalid        │
│  - Type check   │     └─────────────────┘
└────────┬────────┘
         │ Valid/Empty
         ▼
┌─────────────────┐
│  Process Upload │
│  (if provided)  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Insert/Update  │
│  Database       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Redirect with  │
│  Success Message│
└─────────────────┘
```

---

*Last Updated: February 2026*
*Version: 1.0*
