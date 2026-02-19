# Cloud 9 Cafe - Menu Management CRUD Flow

## Overview

This document explains the Create, Read, Update, Delete (CRUD) workflow for the menu management system in Cloud 9 Cafe.

---

## File Structure

```
admin/menu/
├── list.php       # Read (List all items + Delete)
├── create.php     # Create (Add new item)
├── edit.php       # Update (Edit existing item)
└── CRUD_FLOW.md   # This documentation
```

---

## CREATE - Adding a New Menu Item

### Flow Diagram
```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  User Clicks │────▶│  Display    │────▶│  User Fills │
│  "Add Item"  │     │  Create Form│     │  Form Data  │
└─────────────┘     └─────────────┘     └──────┬──────┘
                                                │
                                                ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Redirect   │◀────│  Insert to  │◀────│  Validation │
│  to List    │     │  Database   │     │  & Upload   │
│  (Success)  │     │             │     │  Image      │
└─────────────┘     └─────────────┘     └─────────────┘
```

### Implementation Details

**File:** `create.php`

1. **Display Form (GET Request)**
   ```php
   // Show empty form with default values
   $formData = [
       'name' => '',
       'description' => '',
       'category' => '',
       'price' => '',
       'is_available' => 1
   ];
   ```

2. **Process Submission (POST Request)**
   ```php
   // Sanitize input
   $formData['name'] = trim($_POST['name'] ?? '');
   $formData['price'] = trim($_POST['price'] ?? '');
   // ... etc
   ```

3. **Validation**
   - Name: Required, max 120 characters
   - Category: Required
   - Price: Required, numeric, 0-9999.99
   - Image: Optional, max 5MB, JPG/PNG/GIF/WebP only

4. **Image Upload**
   ```php
   $uploadDir = __DIR__ . '/../../uploads/menu/';
   $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
   move_uploaded_file($_FILES['image']['tmp_name'], $targetPath);
   ```

5. **Database Insert**
   ```sql
   INSERT INTO menu_items (name, description, category, price, image_path, is_available, created_by, created_at, updated_at)
   VALUES (:name, :description, :category, :price, :image_path, :is_available, :created_by, NOW(), NOW())
   ```

---

## READ - Listing Menu Items

### Flow Diagram
```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  User Opens │────▶│  Fetch from │────▶│  Apply      │
│  Menu List  │     │  Database   │     │  Filters    │
└─────────────┘     └─────────────┘     └──────┬──────┘
                                                │
                                                ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Display    │◀────│  Paginate   │◀────│  Sort by    │
│  in Table   │     │  Results    │     │  Date DESC  │
└─────────────┘     └─────────────┘     └─────────────┘
```

### Implementation Details

**File:** `list.php`

1. **Fetch Categories for Filter**
   ```sql
   SELECT DISTINCT category FROM menu_items ORDER BY category ASC
   ```

2. **Build Query with Filters**
   ```php
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
   ```

3. **Pagination Query**
   ```sql
   SELECT m.*, u.full_name as created_by_name 
   FROM menu_items m 
   LEFT JOIN users u ON m.created_by = u.id 
   WHERE ... 
   ORDER BY m.created_at DESC 
   LIMIT :limit OFFSET :offset
   ```

4. **Display in Table**
   - Thumbnail image (or placeholder)
   - Item name and description
   - Category badge
   - Price
   - Availability status
   - Created date
   - Action buttons (Edit, Delete)

---

## UPDATE - Editing a Menu Item

### Flow Diagram
```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  User Clicks│────▶│  Fetch Item │────▶│  Display    │
│  "Edit"     │     │  from DB    │     │  Edit Form  │
└─────────────┘     └─────────────┘     └──────┬──────┘
                                                │
                                                ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Redirect   │◀────│  Update     │◀────│  Validation │
│  to List    │     │  Database   │     │  & Upload   │
│  (Success)  │     │             │     │  New Image  │
└─────────────┘     └─────────────┘     └─────────────┘
```

### Implementation Details

**File:** `edit.php`

1. **Fetch Existing Item**
   ```php
   $itemId = intval($_GET['id'] ?? 0);
   $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
   $stmt->execute([$itemId]);
   $item = $stmt->fetch();
   ```

2. **Populate Form**
   ```php
   $formData = [
       'name' => $item['name'],
       'description' => $item['description'] ?? '',
       'category' => $item['category'],
       'price' => $item['price'],
       'is_available' => $item['is_available']
   ];
   ```

3. **Handle Image Update**
   - Upload new image (if provided)
   - Delete old image file
   - Or remove image (if checkbox checked)

4. **Database Update**
   ```sql
   UPDATE menu_items 
   SET name = :name, 
       description = :description, 
       category = :category, 
       price = :price, 
       image_path = :image_path, 
       is_available = :is_available,
       updated_at = NOW()
   WHERE id = :id
   ```

---

## DELETE - Removing a Menu Item

### Flow Diagram
```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  User Clicks│────▶│  Show       │────▶│  User       │
│  "Delete"   │     │  Confirm    │     │  Confirms   │
└─────────────┘     │  Modal      │     └──────┬──────┘
                    └─────────────┘              │
                                                 ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Redirect   │◀────│  Delete     │◀────│  Delete     │
│  to List    │     │  Image File │     │  from DB    │
│  (Success)  │     │  (if exists)│     │             │
└─────────────┘     └─────────────┘     └─────────────┘
```

### Implementation Details

**File:** `list.php` (Delete action)

1. **Confirmation Modal**
   ```javascript
   // Bootstrap modal with item name
   $('#deleteModal').modal('show');
   ```

2. **Process Delete (POST Request)**
   ```php
   $deleteId = intval($_POST['delete_id']);
   
   // Get image path before deleting
   $imgStmt = $pdo->prepare("SELECT image_path FROM menu_items WHERE id = ?");
   $imgStmt->execute([$deleteId]);
   $item = $imgStmt->fetch();
   
   // Delete from database
   $deleteStmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
   $deleteStmt->execute([$deleteId]);
   
   // Delete image file if exists
   if ($item && $item['image_path']) {
       unlink(__DIR__ . '/../../uploads/menu/' . basename($item['image_path']));
   }
   ```

---

## Database Schema

```sql
CREATE TABLE `menu_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `category` VARCHAR(60) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `is_available` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_menu_items_category` (`category`),
  KEY `idx_menu_items_is_available` (`is_available`),
  KEY `idx_menu_items_created_by` (`created_by`),
  CONSTRAINT `fk_menu_items_created_by_users_id`
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
);
```

---

## Security Measures

| Measure | Implementation |
|---------|---------------|
| Authentication | `admin_guard.php` checks admin role |
| SQL Injection | PDO prepared statements with parameter binding |
| XSS Prevention | `htmlspecialchars()` on all output |
| CSRF Protection | Ready for token implementation |
| File Upload | Type validation, size limit, unique filename |
| Path Traversal | `basename()` used on uploaded files |

---

## Error Handling

| Error Type | User Message | Log |
|------------|--------------|-----|
| Database Connection | "Failed to load menu items" | Yes |
| Validation Error | Field-specific error message | No |
| Upload Error | "Failed to upload image" | Yes |
| Not Found | Redirect to list with error | Yes |

---

## Success Messages

| Action | Message |
|--------|---------|
| Create | "Menu item created successfully." |
| Update | "Menu item updated successfully." |
| Delete | "Menu item deleted successfully." |

---

## URL Patterns

| Action | URL | Method |
|--------|-----|--------|
| List | `/admin/menu/list.php` | GET |
| Create Form | `/admin/menu/create.php` | GET |
| Create Submit | `/admin/menu/create.php` | POST |
| Edit Form | `/admin/menu/edit.php?id={id}` | GET |
| Edit Submit | `/admin/menu/edit.php?id={id}` | POST |
| Delete | `/admin/menu/list.php` | POST |

---

## Future Enhancements

1. **AJAX Operations** - Inline editing without page reload
2. **Bulk Actions** - Delete multiple items at once
3. **Image Cropping** - Client-side image resize/crop
4. **Drag & Drop Sorting** - Reorder menu items
5. **Version History** - Track changes to menu items
6. **Multi-language Support** - Names/descriptions in multiple languages
