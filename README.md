# 📚 Libria - Library Management System

<div align="center">

![Symfony](https://img.shields.io/badge/Symfony-7.3-black?style=for-the-badge&logo=symfony)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql)
![License](https://img.shields.io/badge/License-Educational-blue?style=for-the-badge)

*A comprehensive Symfony-based library management system with role-based access control and modern admin interface.*

[🔗 Live Demo](#) · [📖 Documentation](#) · [🐛 Report Issues](https://github.com/SalmaIT1/Libria/issues) · [💡 Feature Requests](https://github.com/SalmaIT1/Libria/issues)

</div>

## 🌟 Features

### 🔐 **User Management**
- **Secure Authentication**: Login and registration system with password hashing
- **Role-based Access Control**: Admin and User roles with granular permissions
- **User Profile Management**: Update personal information and preferences

### 📊 **Admin Dashboard (EasyAdmin)**
Full CRUD interface for managing all library resources:
- 📖 **Books Management**: Add, edit, delete books with metadata
- ✍️ **Authors Management**: Manage author profiles and biographies
- 🏢 **Publishers Management**: Track publisher information
- 🏷️ **Categories Management**: Organize books by categories
- 👥 **User Management**: Admin control over user accounts and roles

### 🗄️ **Database Architecture**
- **MySQL 8.0+** with optimized schema
- **Doctrine ORM** for database abstraction
- **Migration system** for version control
- **Complex relationships**: Many-to-Many, One-to-Many

### 🎨 **User Experience**
- **Responsive Design**: Mobile-friendly interface
- **Modern UI**: Clean and intuitive user interface
- **Search & Filter**: Find books quickly by title, author, or category
- **PDF Generation**: Export book lists and reports (via DomPDF)

## 🚀 Quick Start

### Prerequisites

- **PHP 8.2+** with required extensions
- **Composer** for dependency management
- **MySQL 8.0+** database server
- **Symfony CLI** (optional, for development)

### ⚡ One-Click Setup (Docker)

```bash
# Clone the repository
git clone https://github.com/SalmaIT1/Libria.git
cd Libria

# Start with Docker Compose
docker-compose up -d

# Access the application
open http://localhost:8000
```

### 🛠️ Manual Installation

<details>
<summary>Click to expand manual setup instructions</summary>

1. **Clone & Install Dependencies**
   ```bash
   git clone https://github.com/SalmaIT1/Libria.git
   cd Libria
   composer install
   ```

2. **Environment Configuration**
   ```bash
   # Copy environment template
   cp .env.example .env
   
   # Edit your database credentials
   nano .env
   ```
   
   Configure your database connection:
   ```env
   DATABASE_URL="mysql://root:your_password@127.0.0.1:3306/libria?serverVersion=8.0&charset=utf8mb4"
   ```

3. **Database Setup**
   ```sql
   CREATE DATABASE libria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. **Run Migrations**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

5. **Load Fixtures (Optional)**
   ```bash
   php bin/console doctrine:fixtures:load
   ```

6. **Create Admin User**
   ```bash
   # Register a user via web interface, then promote to admin:
   php bin/console console:user:promote your@email.com ROLE_ADMIN
   ```

7. **Start Development Server**
   ```bash
   symfony server:start
   # or
   php -S localhost:8000 -t public
   ```

</details>

## 🌐 Application Access

| Page | URL | Description |
|------|-----|-------------|
| **🏠 Home** | `http://localhost:8000/` | Main landing page |
| **🔐 Login** | `http://localhost:8000/login` | User authentication |
| **📝 Register** | `http://localhost:8000/register` | New user registration |
| **⚙️ Admin Dashboard** | `http://localhost:8000/admin` | Admin control panel (ROLE_ADMIN required) |

### 👤 User Roles & Permissions

| Role | Permissions | Access Level |
|------|-------------|--------------|
| **ROLE_USER** | View books, profile management | Standard user access |
| **ROLE_ADMIN** | Full CRUD operations, user management | Administrative access |

## 🏗️ System Architecture



### 📁 Project Structure

```
libria/
├── 📂 config/                 # Configuration files
│   ├── 📂 packages/
│   │   ├── 📄 doctrine.yaml    # Database configuration
│   │   ├── 📄 security.yaml    # Security & authentication
│   │   └── 📄 easyadmin.yaml   # Admin interface config
│   └── 📂 routes/              # Route definitions
├── 📂 src/                    # Application source code
│   ├── 📂 Controller/         # HTTP controllers
│   │   ├── 📂 Admin/          # EasyAdmin CRUD controllers
│   │   ├── 📄 HomeController.php
│   │   └── 📄 SecurityController.php
│   ├── 📂 Entity/             # Doctrine entities
│   ├── 📂 Form/               # Symfony form types
│   └── 📂 Repository/         # Doctrine repositories
├── 📂 templates/              # Twig templates
│   ├── 📂 admin/              # Admin interface templates
│   ├── 📂 security/           # Login/Register templates
│   └── 📂 home/               # Homepage templates
├── 📂 migrations/             # Database migrations
├── 📂 public/                 # Web accessible files
└── 📂 translations/           # Internationalization files
```

## 🛠️ Development

### 📋 Available Commands

```bash
# Database operations
php bin/console doctrine:migrations:migrate      # Run migrations
php bin/console doctrine:fixtures:load          # Load test data
php bin/console doctrine:schema:update --force   # Update schema

# User management
php bin/console make:user                       # Create user entity
php bin/console security:encode-password        # Encode password

# Cache management
php bin/console cache:clear                    # Clear cache
php bin/console cache:warmup                   # Warm up cache

# Development server
symfony server:start                           # Start dev server
symfony server:stop                            # Stop dev server
```

### 🧪 Testing

```bash
# Run tests (when implemented)
php bin/phpunit

# Code coverage
php bin/phpunit --coverage-html coverage/
```

## 🔧 Configuration

### Environment Variables

Key environment variables in `.env`:

```env
# Database
DATABASE_URL="mysql://user:password@127.0.0.1:3306/libria"

# Mailer (for email notifications)
MAILER_DSN=smtp://localhost:1025

# Application secret
APP_SECRET=your_secret_key_here
```

### Security Configuration

- **Password Hashing**: bcrypt algorithm
- **CSRF Protection**: Enabled for all forms
- **Session Management**: Secure cookie settings
- **Role Hierarchy**: Proper role inheritance

## 🐛 Troubleshooting

<details>
<summary>Common Issues & Solutions</summary>

### 🔌 Database Connection Issues
```bash
# Check MySQL service
sudo systemctl status mysql

# Test connection
mysql -u root -p -e "SHOW DATABASES;"

# Verify PHP extensions
php -m | grep -E "(pdo|mysql)"
```

### 📁 Permission Issues
```bash
# Fix cache and log permissions
sudo chown -R www-data:www-data var/
chmod -R 755 var/

# Fix public directory permissions
chmod -R 755 public/
```

### 🚀 Performance Optimization
```bash
# Clear cache in production
php bin/console cache:clear --env=prod

# Warm up cache
php bin/console cache:warmup --env=prod

# Optimize autoloader
composer dump-autoload --optimize
```

</details>

## 📚 API Documentation

<details>
<summary>REST API Endpoints (if implemented)</summary>

### Authentication
- `POST /api/login` - User login
- `POST /api/register` - User registration
- `POST /api/logout` - User logout

### Books
- `GET /api/books` - List all books
- `GET /api/books/{id}` - Get book details
- `POST /api/books` - Create new book (Admin only)
- `PUT /api/books/{id}` - Update book (Admin only)
- `DELETE /api/books/{id}` - Delete book (Admin only)

### Users (Admin only)
- `GET /api/users` - List all users
- `GET /api/users/{id}` - Get user details
- `PUT /api/users/{id}/role` - Update user role

</details>

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. **Fork** the repository
2. **Create** a feature branch: `git checkout -b feature/amazing-feature`
3. **Commit** your changes: `git commit -m 'Add amazing feature'`
4. **Push** to the branch: `git push origin feature/amazing-feature`
5. **Open** a Pull Request

### 📝 Development Guidelines

- Follow **PSR-12** coding standards
- Write **clear commit messages**
- Add **tests** for new features
- Update **documentation** as needed

## 📄 License

This project is for **educational purposes**. Feel free to use it as a learning resource or reference for your own projects.

## 🙏 Acknowledgments

- **Symfony Framework** - Excellent PHP framework
- **EasyAdmin Bundle** - Powerful admin interface generator
- **Doctrine ORM** - Database abstraction layer
- **Twig** - Modern templating engine

## 📞 Support & Contact

- **📧 Email**: [salmaboubaker7@gmail.com]
- **🐛 Issues**: [GitHub Issues](https://github.com/SalmaIT1/Libria/issues)
- **💬 Discussions**: [GitHub Discussions](https://github.com/SalmaIT1/Libria/discussions)

---

<div align="center">

**⭐ Star this repository if it helped you!**

Made with ❤️ by [SalmaIT1](https://github.com/SalmaIT1)

</div>

