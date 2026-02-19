# Cloud 9 Cafe Admin Dashboard - Data Source Explanation

## Overview

The admin dashboard aggregates data from multiple database tables to provide a comprehensive overview of the cafe's operations. This document explains the data sources and how they are used in the dashboard.

---

## Data Sources

### 1. Total Users Card

**Database Table:** `users`

**Query:**
```sql
SELECT COUNT(*) AS total FROM users WHERE role = 'user'
```

**Description:**
- Counts all registered users with the role 'user'
- Excludes admin accounts from the count
- Represents the customer base of the cafe

**Fields Used:**
- `id` - Primary key
- `role` - User role ('user' or 'admin')

---

### 2. Total Menu Items Card

**Database Table:** `menu_items`

**Query:**
```sql
SELECT COUNT(*) AS total FROM menu_items
```

**Description:**
- Counts all menu items in the database
- Includes both available and unavailable items
- Represents the complete menu catalog

**Fields Used:**
- `id` - Primary key
- `name` - Item name
- `is_available` - Availability status

---

### 3. Total Messages Card

**Database Table:** `contact_messages`

**Query:**
```sql
SELECT COUNT(*) AS total FROM contact_messages WHERE status = 'new'
```

**Description:**
- Counts unread/new messages from customers
- Helps admins prioritize customer inquiries
- Status values: 'new', 'read', 'replied', 'archived'

**Fields Used:**
- `id` - Primary key
- `status` - Message status
- `created_at` - Timestamp

---

### 4. Pending Orders Card

**Database Table:** `orders` (if exists)

**Query:**
```sql
SELECT COUNT(*) AS total FROM orders WHERE status IN ('pending', 'processing')
```

**Description:**
- Counts orders that need attention
- Includes pending and processing orders
- Helps track order fulfillment workload

**Fields Used:**
- `id` - Primary key
- `status` - Order status

---

### 5. Active Reservations Card

**Database Table:** `reservations` (if exists)

**Query:**
```sql
SELECT COUNT(*) AS total FROM reservations WHERE status IN ('pending', 'confirmed')
```

**Description:**
- Counts upcoming and pending reservations
- Helps manage table availability

**Fields Used:**
- `id` - Primary key
- `status` - Reservation status

---

## Database Schema

### Users Table
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

### Menu Items Table
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

### Contact Messages Table
```sql
CREATE TABLE `contact_messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('new', 'read', 'replied', 'archived') NOT NULL DEFAULT 'new',
  `replied_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contact_messages_user_id` (`user_id`),
  KEY `idx_contact_messages_status` (`status`),
  KEY `idx_contact_messages_created_at` (`created_at`),
  CONSTRAINT `fk_contact_messages_user_id_users_id`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
);
```

---

## Error Handling

The dashboard implements robust error handling for each data query:

```php
try {
    $stats['users'] = (int) $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role = 'user'")->fetch()['total'];
} catch (Throwable $e) {
    $dataWarnings[] = 'Unable to load total users count.';
}
```

**Benefits:**
- Individual query failures don't break the entire dashboard
- Users see a warning message for unavailable data
- Other statistics continue to display normally

---

## Performance Considerations

1. **Indexed Columns:** All queried columns have database indexes for fast counting
2. **COUNT(*) Optimization:** Uses efficient counting without fetching full rows
3. **Lazy Loading:** Stats are loaded synchronously but could be converted to AJAX for larger datasets
4. **Caching Opportunity:** For high-traffic sites, consider caching stats for 1-5 minutes

---

## Session Data

The dashboard also displays admin information from the PHP session:

| Session Key | Description |
|-------------|-------------|
| `user_id` / `admin_id` | Authenticated user identifier |
| `full_name` | Admin's full name |
| `email` | Admin's email address |
| `role` | User role ('admin') |

---

## Future Enhancements

Potential data sources to add:

1. **Revenue Statistics** - Daily/weekly/monthly sales totals
2. **Popular Items** - Most ordered menu items
3. **Customer Growth** - New user registrations over time
4. **Peak Hours** - Busiest times based on orders
5. **Inventory Alerts** - Low stock warnings
