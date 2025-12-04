# Libria - Library Management System

A Symfony-based library management system with admin and user roles, authentication, and EasyAdmin dashboard.

## Repository

**GitHub**: https://github.com/SalmaIT1/Libria

## Features

- **User Authentication**: Login and registration system
- **Role-based Access Control**: Admin and User roles
- **EasyAdmin Dashboard**: Full CRUD interface for managing:
  - Books (Livres)
  - Authors (Auteurs)
  - Publishers (Editeurs)
  - Categories (Categories)
  - Users
- **Database**: MySQL database named "libria"

## Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0 or higher
- Symfony CLI (optional, for development server)

### Setup Steps

1. **Install Dependencies**
   ```bash
   composer install
   ```

2. **Configure Database**
   
   Create a `.env` file in the root directory (or copy from `.env.example` if provided) and configure your database connection:
   
   ```env
   DATABASE_URL="mysql://root:your_password@127.0.0.1:3306/libria?serverVersion=8.0&charset=utf8mb4"
   ```
   
   Replace `root` and `your_password` with your MySQL credentials.

3. **Create Database**
   
   Create the MySQL database:
   ```sql
   CREATE DATABASE libria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. **Run Migrations**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

5. **Create Admin User**
   
   You can create an admin user through the registration page, then manually update the database to add `ROLE_ADMIN`:
   ```sql
   UPDATE `user` SET roles = '["ROLE_ADMIN", "ROLE_USER"]' WHERE email = 'admin@example.com';
   ```
   
   Or use the EasyAdmin interface after logging in as a regular user and updating roles.

6. **Start Development Server**
   ```bash
   symfony server:start
   ```
   
   Or use PHP built-in server:
   ```bash
   php -S localhost:8000 -t public
   ```

## Usage

### Accessing the Application

- **Home Page**: http://localhost:8000/
- **Login**: http://localhost:8000/login
- **Register**: http://localhost:8000/register
- **Admin Dashboard**: http://localhost:8000/admin (requires ROLE_ADMIN)

### Default Roles

- **ROLE_USER**: Regular user access
- **ROLE_ADMIN**: Full access to admin dashboard and all CRUD operations

### Entity Relationships

Based on the UML diagram:

- **Livre (Book)**:
  - Belongs to one Editeur (Publisher) - Many-to-One
  - Has many Auteurs (Authors) - Many-to-Many
  - Has many Categories - Many-to-Many

- **Editeur (Publisher)**:
  - Has many Livres (Books) - One-to-Many

- **Auteur (Author)**:
  - Has many Livres (Books) - Many-to-Many

- **Categorie (Category)**:
  - Has many Livres (Books) - Many-to-Many (Aggregation)

## Project Structure

```
libria/
├── config/
│   ├── packages/
│   │   ├── doctrine.yaml      # Database configuration
│   │   ├── security.yaml      # Security and authentication
│   │   └── easyadmin.yaml     # EasyAdmin configuration
│   └── routes/
├── src/
│   ├── Controller/
│   │   ├── Admin/             # EasyAdmin CRUD controllers
│   │   ├── HomeController.php
│   │   └── SecurityController.php
│   ├── Entity/                # Doctrine entities
│   ├── Form/                  # Symfony forms
│   └── Repository/            # Doctrine repositories
├── templates/
│   ├── admin/                 # Admin templates
│   ├── security/              # Login/Register templates
│   └── home/                  # Home page templates
└── migrations/                # Database migrations
```

## Troubleshooting

### Database Connection Issues

- Ensure MySQL is running
- Verify database credentials in `.env`
- Check that the `libria` database exists
- Ensure PHP MySQL extension is enabled (`php -m | grep pdo_mysql`)

### Permission Issues

- Ensure `var/` directory is writable
- Check file permissions on `public/` directory

## License

This project is for educational purposes.

