# Cloud 9 Cafe ☕

A professional, full-featured web application for managing a cafe business. Built with PHP, MySQL, and Bootstrap 5.

![Cloud 9 Cafe](https://img.shields.io/badge/Cloud%209%20Cafe-PHP%20%2B%20MySQL-blue)
![License](https://img.shields.io/badge/License-MIT-green)
![Version](https://img.shields.io/badge/Version-1.0.0-orange)

---

## 📋 Table of Contents

- [Features](#features)
- [Application Overview](#application-overview)
- [Functions & Modules](#functions--modules)
- [Folder Structure](#folder-structure)
- [Installation & Configuration](#installation--configuration)
- [How to Use](#how-to-use)
- [User Roles](#user-roles)
- [Database Schema](#database-schema)
- [Security Features](#security-features)
- [Technologies Used](#technologies-used)
- [Screenshots](#screenshots)
- [Contributing](#contributing)
- [License](#license)
- [Support](#support)

---

## ✨ Features

### Core Features
- **User Authentication** - Secure registration and login system with role-based access
- **Admin Dashboard** - Comprehensive management panel with statistics and analytics
- **User Dashboard** - Personal account management for customers
- **Menu Management** - Full CRUD operations for menu items with image upload
- **User Management** - Admin can create, view, edit, and delete user accounts
- **Contact Form** - Customer inquiry system with database storage
- **Responsive Design** - Mobile-first, fully responsive UI using Bootstrap 5

### Guest Features
- Browse cafe menu with dynamic content from database
- View featured items and daily specials
- Contact cafe through inquiry form
- Register for an account
- Login to access personal dashboard

### User Features
- View and edit personal profile
- Change password securely
- View order history (future enhancement)
- Make reservations (future enhancement)
- Access personalized dashboard

### Admin Features
- View system statistics (users, menu items, orders, messages)
- Manage users (create, edit, delete, activate/deactivate)
- Manage menu items (create, edit, delete with image upload)
- View contact messages
- System status monitoring
- Quick action shortcuts

---

## 🏢 Application Overview

Cloud 9 Cafe is a complete web-based management system designed for cafes and restaurants. It provides a seamless experience for both customers and administrators.

### Architecture
```
┌─────────────────────────────────────────────────────────┐
│                    CLOUD 9 CAFE                          │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐     │
│  │   GUEST     │  │    USER     │  │    ADMIN    │     │
│  │   (Public)  │  │  (Logged In)│  │  (Privileged)│     │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘     │
│         │                │                │             │
│         └────────────────┴────────────────┘             │
│                          │                              │
│              ┌───────────┴───────────┐                  │
│              │   PHP + MySQL Backend  │                  │
│              │   Bootstrap 5 Frontend │                  │
│              └────────────────────────┘                  │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### User Flow
1. **Guest** visits the website, browses menu, and can contact the cafe
2. **Guest** registers for an account → becomes **User**
3. **User** logs in to access personal dashboard
4. **Admin** manages users, menu items, and monitors system

---

## ⚙️ Functions & Modules

### 1. Authentication Module

#### Registration (`guest/auth/register.php`)
- Validates user input (name, email, phone, password)
- Checks for duplicate email addresses
- Hashes password using bcrypt
- Stores user data in database
- Redirects to login page on success

#### Login (`guest/auth/login.php`)
- Validates email and password
- Verifies password hash
- Checks account active status
- Creates secure session
- Updates last login timestamp
- Redirects based on user role (user/admin)

#### Logout (`user/logout.php`, `admin/logout.php`)
- Destroys session
- Clears cookies
- Redirects to login page

### 2. User Management Module

#### User Dashboard (`user/index.php`)
- Displays welcome message
- Quick access to profile, menu, settings
- Responsive sidebar navigation

#### Profile Management (`user/account/profile.php`)
- View current profile information
- Edit full name and phone number
- Validation for all fields
- Session update after changes

#### Password Change (`user/account/settings.php`)
- Verify current password
- Set new password with strength meter
- Confirm password matching
- bcrypt hashing for security

### 3. Admin Management Module

#### Admin Dashboard (`admin/index.php`)
- Statistics cards (users, menu items, messages, orders)
- Quick action buttons
- System status monitoring
- Responsive sidebar navigation

#### User Management (`admin/users/`)
- **List** (`list.php`): View all users with search, filter, pagination
- **View** (`view.php`): Detailed user information with statistics
- **Create** (`create.php`): Add new users with role selection
- **Edit** (`edit.php`): Modify user details and status
- **Delete**: Soft delete (deactivate) or hard delete

#### Menu Management (`admin/menu/`)
- **List** (`list.php`): View all menu items with filters
- **Create** (`create.php`): Add new items with image upload
- **Edit** (`edit.php`): Modify item details and images
- **Delete**: Remove items with confirmation

### 4. Guest Module

#### Home Page (`guest/index.php`)
- Hero section with call-to-action
- Featured menu items
- About section
- Contact information

#### Menu Page (`guest/menu.php`)
- Display all available menu items
- Dynamic content from database
- Image placeholders for items without images
- Responsive grid layout

#### Contact Page (`guest/contact.php`)
- Contact form with validation
- Store messages in database
- Display contact information
- Google Maps integration placeholder

### 5. Database Module (`includes/db.php`)
- PDO connection with error handling
- Environment-based configuration
- UTF-8 charset support
- Secure credential management

### 6. Security Module

#### Auth Guard (`includes/auth/auth_guard.php`)
- Protects user pages
- Validates session
- Redirects unauthorized users

#### Admin Guard (`includes/auth/admin_guard.php`)
- Protects admin pages
- Validates admin role
- Redirects non-admin users

---

## 📁 Folder Structure

```
cloud_9_cafe/
│
├── 📄 index.php                          # Main entry point
├── 📄 README.md                          # This file
├── 📄 .env                               # Environment configuration
├── 📄 .env.example                       # Environment template
├── 📄 .gitignore                         # Git ignore rules
├── 📄 .htaccess                          # Apache configuration
│
├── 📁 admin/                             # Admin panel
│   ├── 📄 index.php                      # Admin dashboard
│   ├── 📄 logout.php                     # Admin logout
│   │
│   ├── 📁 auth/                          # Admin authentication
│   │   └── 📄 login.php                  # Admin login page
│   │
│   ├── 📁 menu/                          # Menu management
│   │   ├── 📄 list.php                   # List menu items
│   │   ├── 📄 create.php                 # Add menu item
│   │   ├── 📄 edit.php                   # Edit menu item
│   │   ├── 📄 CRUD_FLOW.md               # CRUD documentation
│   │   └── 📄 VALIDATION_CHECKLIST.md    # Validation guide
│   │
│   ├── 📁 users/                         # User management
│   │   ├── 📄 list.php                   # List users
│   │   ├── 📄 view.php                   # View user details
│   │   ├── 📄 create.php                 # Add user
│   │   ├── 📄 edit.php                   # Edit user
│   │   ├── 📄 ACCESS_CONTROL.md          # Access control docs
│   │   ├── 📄 SECURITY_CHECKLIST.md      # Security checklist
│   │   └── 📄 COMPLETION_SUMMARY.md      # Feature summary
│   │
│   ├── 📁 orders/                        # Order management
│   │   ├── 📄 list.php                   # List orders
│   │   ├── 📄 details.php                # Order details
│   │   └── 📄 update-status.php          # Update order status
│   │
│   ├── 📁 reservations/                  # Reservation management
│   │   ├── 📄 list.php                   # List reservations
│   │   └── 📄 update-status.php          # Update reservation status
│   │
│   ├── 📁 reports/                       # Reports & analytics
│   │   ├── 📄 sales.php                  # Sales reports
│   │   └── 📄 users.php                  # User reports
│   │
│   └── 📁 settings/                      # System settings
│       └── 📄 general.php                # General settings
│
├── 📁 user/                              # User dashboard
│   ├── 📄 index.php                      # User dashboard
│   ├── 📄 logout.php                     # User logout
│   │
│   ├── 📁 account/                       # Account management
│   │   ├── 📄 profile.php                # Edit profile
│   │   └── 📄 settings.php               # Change password
│   │
│   ├── 📁 orders/                        # Order history
│   │   ├── 📄 history.php                # Order history
│   │   └── 📄 details.php                # Order details
│   │
│   └── 📁 reservations/                  # User reservations
│       └── 📄 list.php                   # Reservation list
│
├── 📁 guest/                             # Public pages
│   ├── 📄 index.php                      # Home page
│   ├── 📄 menu.php                       # Menu page
│   ├── 📄 about.php                      # About page
│   ├── 📄 contact.php                    # Contact page
│   │
│   └── 📁 auth/                          # Guest authentication
│       ├── 📄 login.php                  # User login
│       ├── 📄 register.php               # User registration
│       └── 📄 forgot-password.php        # Password recovery
│
├── 📁 includes/                          # Shared components
│   ├── 📄 header.php                     # Common header
│   ├── 📄 footer.php                     # Common footer
│   ├── 📄 db.php                         # Database connection
│   │
│   ├── 📁 auth/                          # Authentication guards
│   │   ├── 📄 auth_guard.php             # User guard
│   │   └── 📄 admin_guard.php            # Admin guard
│   │
│   ├── 📁 helpers/                       # Helper functions
│   │   └── 📄 functions.php              # Utility functions
│   │
│   └── 📁 layouts/                       # Layout templates
│       ├── 📄 admin_header.php           # Admin header
│       ├── 📄 admin_footer.php           # Admin footer
│       ├── 📄 user_header.php            # User header
│       └── 📄 user_footer.php            # User footer
│
├── 📁 config/                            # Configuration files
│   └── 📄 app.php                        # Application config
│
├── 📁 database/                          # Database files
│   └── 📁 migrations/                    # SQL migrations
│       ├── 📄 001_init_schema.sql        # Initial schema
│       └── 📄 002_orders_reservations.sql # Orders & reservations
│
├── 📁 assets/                            # Static assets
│   └── 📁 css/                           # Stylesheets
│       ├── 📄 main.css                   # Main styles
│       ├── 📄 admin.css                  # Admin styles
│       └── 📄 user.css                   # User styles
│
├── 📁 uploads/                           # Uploaded files
│   └── 📁 menu/                          # Menu item images
│
└── 📁 storage/                           # Application storage
```

---

## 🛠️ Installation & Configuration

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx web server
- XAMPP/WAMP/MAMP (for local development)

### Step 1: Clone/Download the Project
```bash
# Navigate to your web server directory
cd /path/to/htdocs

# Create project folder
mkdir cloud_9_cafe
cd cloud_9_cafe

# Extract project files here
```

### Step 2: Configure Environment Variables
```bash
# Copy the example environment file
cp .env.example .env

# Edit .env file with your database credentials
```

**`.env` file:**
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=cloud_9_cafe
DB_USER=root
DB_PASS=your_password_here
```

### Step 3: Create Database
1. Open phpMyAdmin or MySQL command line
2. Create a new database named `cloud_9_cafe`
3. Import the SQL migrations:

```bash
# Using MySQL command line
mysql -u root -p cloud_9_cafe < database/migrations/001_init_schema.sql
mysql -u root -p cloud_9_cafe < database/migrations/002_orders_reservations.sql
```

Or use phpMyAdmin:
1. Select the `cloud_9_cafe` database
2. Click "Import" tab
3. Choose `001_init_schema.sql` and import
4. Repeat for `002_orders_reservations.sql`

### Step 4: Configure Web Server

#### For Apache (XAMPP):
Ensure `.htaccess` file exists in project root:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
```

#### For Virtual Host (Optional):
```apache
<VirtualHost *:80>
    ServerName cloud9cafe.local
    DocumentRoot "C:/xampp/htdocs/cloud_9_cafe"
    <Directory "C:/xampp/htdocs/cloud_9_cafe">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Step 5: Set Folder Permissions
```bash
# Make uploads folder writable
chmod 755 uploads/
chmod 755 uploads/menu/

# For Linux/Mac
chmod -R 755 storage/
```

### Step 6: Create Admin User
```sql
-- Run this SQL query to create an admin user
INSERT INTO users (full_name, email, password_hash, role, phone, is_active, created_at)
VALUES (
    'Administrator',
    'admin@cloud9cafe.com',
    '$2y$10$YourHashedPasswordHere', -- Use password_hash() in PHP
    'admin',
    '+1234567890',
    1,
    NOW()
);
```

Or use the registration page and manually update the role in database:
```sql
UPDATE users SET role = 'admin' WHERE email = 'your_email@example.com';
```

### Step 7: Access the Application
```
Guest Site: http://localhost/cloud_9_cafe/
User Login: http://localhost/cloud_9_cafe/guest/auth/login.php
Admin Login: http://localhost/cloud_9_cafe/admin/auth/login.php
```

---

## 📖 How to Use

### As a Guest
1. Visit the home page to learn about the cafe
2. Browse the menu at `Menu` page
3. Contact the cafe using the contact form
4. Register for an account to access more features

### As a User
1. **Login** with your registered email and password
2. **Dashboard** - View your account overview
3. **Edit Profile** - Update your name and phone number
4. **Change Password** - Update your password securely
5. **View Menu** - Browse and order (future enhancement)
6. **Logout** - Securely sign out

### As an Admin
1. **Login** at `/admin/auth/login.php`
2. **Dashboard** - View system statistics
3. **Manage Users**:
   - View all registered users
   - Add new users
   - Edit user details
   - Activate/deactivate accounts
   - Delete users
4. **Manage Menu**:
   - Add new menu items with images
   - Edit existing items
   - Remove items
   - Set availability status
5. **View Reports** - Check user activity and messages
6. **Settings** - Configure system options

---

## 👥 User Roles

### Guest (Not Logged In)
- View home page
- Browse menu
- Contact cafe
- Register/Login

### User (Logged In)
- Access personal dashboard
- Edit profile
- Change password
- View menu
- Place orders (future)
- Make reservations (future)

### Admin (Administrator)
- Full system access
- Manage users
- Manage menu items
- View reports
- System configuration

---

## 🗄️ Database Schema

### Users Table
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key, auto-increment |
| full_name | VARCHAR(100) | User's full name |
| email | VARCHAR(190) | Unique email address |
| password_hash | VARCHAR(255) | bcrypt hashed password |
| role | ENUM | 'user' or 'admin' |
| phone | VARCHAR(20) | Phone number |
| is_active | TINYINT | Account status (0/1) |
| last_login_at | DATETIME | Last login timestamp |
| created_at | TIMESTAMP | Account creation |
| updated_at | TIMESTAMP | Last update |

### Menu Items Table
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| name | VARCHAR(120) | Item name |
| description | TEXT | Item description |
| category | VARCHAR(60) | Item category |
| price | DECIMAL(10,2) | Item price |
| image_path | VARCHAR(255) | Image file path |
| is_available | TINYINT | Availability status |
| created_by | BIGINT | Creator user ID |
| created_at | TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | Last update |

### Contact Messages Table
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| user_id | BIGINT | Linked user (optional) |
| name | VARCHAR(100) | Sender name |
| email | VARCHAR(190) | Sender email |
| subject | VARCHAR(150) | Message subject |
| message | TEXT | Message content |
| status | ENUM | 'new', 'read', 'replied', 'archived' |
| created_at | TIMESTAMP | Message timestamp |

### Orders Table
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| user_id | BIGINT | Customer ID |
| order_number | VARCHAR(20) | Unique order number |
| status | ENUM | Order status |
| total_amount | DECIMAL(10,2) | Order total |
| payment_status | ENUM | Payment status |
| delivery_type | ENUM | 'dine_in', 'takeaway', 'delivery' |
| ordered_at | TIMESTAMP | Order time |

### Reservations Table
| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| user_id | BIGINT | Customer ID |
| reservation_code | VARCHAR(20) | Unique code |
| party_size | INT | Number of guests |
| reservation_date | DATE | Reservation date |
| reservation_time | TIME | Reservation time |
| status | ENUM | Reservation status |

---

## 🔒 Security Features

### Authentication & Authorization
- ✅ bcrypt password hashing (cost factor 10+)
- ✅ Secure session management
- ✅ Session regeneration on login
- ✅ Role-based access control (RBAC)
- ✅ Account activation/deactivation

### Data Protection
- ✅ PDO prepared statements (SQL injection prevention)
- ✅ `htmlspecialchars()` output encoding (XSS prevention)
- ✅ Input validation and sanitization
- ✅ CSRF protection ready (token implementation)
- ✅ Secure cookie settings (HttpOnly, Secure, SameSite)

### Access Control
- ✅ Auth guards for protected pages
- ✅ Self-deletion prevention
- ✅ Admin-only access to admin panel
- ✅ User isolation (admins hidden from user list)

### File Upload Security
- ✅ File type validation (whitelist)
- ✅ File size limits (5MB max)
- ✅ Unique filename generation
- ✅ Path traversal prevention (`basename()`)

---

## 💻 Technologies Used

### Backend
- **PHP 8.0+** - Server-side scripting
- **MySQL/MariaDB** - Database
- **PDO** - Database abstraction layer

### Frontend
- **Bootstrap 5.3** - CSS framework
- **Bootstrap Icons 1.11** - Icon library
- **Google Fonts** - Typography (Cormorant Garamond, Nunito Sans)

### Tools & Libraries
- **XAMPP/WAMP/MAMP** - Local development
- **phpMyAdmin** - Database management
- **Git** - Version control

---

## 📸 Screenshots

### Guest Pages
- Home Page - Hero section with featured items
- Menu Page - Grid layout of menu items
- Contact Page - Contact form with information

### User Dashboard
- Dashboard - Welcome with quick actions
- Profile Edit - Form to update personal info
- Settings - Password change with strength meter

### Admin Panel
- Dashboard - Statistics cards and quick actions
- User Management - Table with search and filters
- Menu Management - CRUD operations with image upload

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🆘 Support

For support, email hello@cloud9cafe.com or open an issue in the repository.

### Common Issues

**1. Database Connection Failed**
- Check `.env` file credentials
- Ensure MySQL is running
- Verify database exists

**2. Images Not Uploading**
- Check `uploads/menu/` folder permissions (755)
- Verify PHP file upload limits in `php.ini`

**3. Session Not Working**
- Ensure `session_start()` is called
- Check PHP session configuration
- Clear browser cookies

**4. 404 Errors**
- Check `.htaccess` file exists
- Ensure Apache mod_rewrite is enabled
- Verify base URL in configuration

---

## 🙏 Acknowledgments

- Bootstrap Team for the excellent CSS framework
- Google Fonts for beautiful typography
- All contributors who helped improve this project

---

**Made  by MoredXD for Cloud 9 Cafe Team**

*Last Updated: February 2026*
*Version: 1.0.0*
