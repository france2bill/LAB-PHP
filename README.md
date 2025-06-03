# PHP Laboratory Exercises - Complete Guide

## Overview
This repository contains a comprehensive collection of PHP laboratory exercises designed to teach web development concepts from basic PHP syntax to advanced web application development with security features. The labs progress from fundamental CRUD operations to object-oriented programming and secure web application development.

## Prerequisites
- XAMPP server (Apache + MySQL + PHP)
- Basic understanding of HTML and CSS
- Text editor or IDE (VS Code, PhpStorm, etc.)
- Web browser

## Project Structure
```
PHP_LAB/
├── README.md
├── .gitignore
├── LAB1/                 # Basic PHP & MySQL CRUD
├── LAB2/                 # Web Forms & Validation
├── LAB3/                 # Advanced CRUD & Relationships
├── LAB4/                 # Object-Oriented Programming
└── LAB5and6/            # Advanced Web Application with Security
```

## Laboratory Exercises

### LAB1 - Basic PHP and MySQL CRUD Operations
**Learning Focus**: PHP fundamentals and database basics

**Database**: `TestDB`
**Key Files**:
- `hello.php` - PHP syntax introduction
- `create_book.php` - Add books to database
- `read_book.php` - Display all books
- `update_book.php` - Edit book records
- `delete_book.php` - Remove books

**Concepts Covered**:
- PHP syntax and structure
- MySQL connectivity with MySQLi
- Basic CRUD operations
- HTML forms integration
- Prepared statements for security

**Database Schema**:
```sql
CREATE TABLE Books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    publication_year INT,
    genre VARCHAR(100),
    price DECIMAL(10,2)
);
```

### LAB2 - Web Forms and User Management
**Learning Focus**: Form handling, validation, and user management

**Database**: `WebAppDB` (main), `LibrarySystemDB` (EXO2)
**Key Files**:
- `user_form.php` - User registration form
- `process_form.php` - Form processing with validation
- `view_user.php` - Display users with actions
- `edit_user.php` / `update_user.php` - User editing
- `delete_user.php` - User deletion
- `EXO2/` - Book management with authors

**Concepts Covered**:
- HTML form design and styling
- Server-side form validation
- Email validation with filter_var()
- Input sanitization
- User management CRUD operations
- Foreign key relationships (EXO2)

**Database Schema**:
```sql
-- Main exercise
CREATE TABLE Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    age INT NOT NULL
);

-- EXO2: Authors and Books relationship
CREATE TABLE Authors (
    author_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE Books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    book_title VARCHAR(255) NOT NULL,
    author_id INT,
    genre VARCHAR(100),
    price DECIMAL(10,2),
    FOREIGN KEY (author_id) REFERENCES Authors(author_id)
);
```

### LAB3 - Advanced CRUD and Database Relationships
**Learning Focus**: Complex database operations and relationships

**Databases**: `EmployeeDB` (main), `StudentDB` (Exercise 2)
**Key Files**:
- `add_employee.php` - Employee registration with departments
- `view_employee.php` - Employee listing with JOIN operations
- `edit_employee.php` / `update_employee.php` - Employee management
- `EXERCISE_2/` - Student management system

**Concepts Covered**:
- Database relationships and JOINs
- Dropdown population from database
- Advanced form validation
- Error handling and user feedback
- Confirmation dialogs for deletion
- Responsive design principles

**Database Schema**:
```sql
-- Employee Management
CREATE TABLE Department (
    emp_dept_id INT AUTO_INCREMENT PRIMARY KEY,
    dept_name VARCHAR(255) NOT NULL
);

CREATE TABLE Employee (
    emp_id INT AUTO_INCREMENT PRIMARY KEY,
    emp_name VARCHAR(255) NOT NULL,
    emp_salary DECIMAL(10,2),
    emp_dept_id INT,
    FOREIGN KEY (emp_dept_id) REFERENCES Department(emp_dept_id)
);

-- Student Management (Exercise 2)
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone_number VARCHAR(9) NOT NULL
);
```

### LAB4 - Object-Oriented Programming
**Learning Focus**: OOP concepts, inheritance, interfaces, and polymorphism

**Structure**:
- `Exo1/` - Basic classes and objects
- `Exo2/` - Inheritance concepts
- `Exo3/` - Interfaces and polymorphism
- `Exo4/` - Complete library system with OOP

**Concepts Covered**:
- Class definition and instantiation
- Properties and methods
- Inheritance with extends keyword
- Interface implementation
- Polymorphism and method overriding
- Abstract concepts and practical implementation

**Key Classes and Interfaces**:
```php
// Exo1: Basic Book class
class Book {
    public string $title, $author, $genre;
    public int $publication_year;
    public float $price;
}

// Exo2: Inheritance
class Product { /* base class */ }
class Book extends Product { /* inherited class */ }

// Exo3: Interfaces
interface Discountable {
    public function getDiscount(): float;
}

// Exo4: Complete system
interface Loanable {
    public function borrowBook($memberId);
    public function returnBook($memberId);
}
```

### LAB5and6 - Advanced Web Application with Security
**Learning Focus**: Complete web application with authentication, security, and advanced features

**Database**: `LibraryDB`
**Key Features**:
- User authentication system
- Admin dashboard
- CSRF protection
- Google OAuth integration
- Session management
- Security best practices

**Key Files**:
- `login.php` / `signup.php` - User authentication
- `admin_login.php` / `admin_dashboard.php` - Admin system
- `library_test.php` - Main user interface
- `google_auth.php` - Google OAuth integration
- `security_functions.php` - Security utilities
- `csrf_token.php` - CSRF protection
- Object-oriented classes: `Book.php`, `User.php`, `Member.php`

**Concepts Covered**:
- Session management and security
- Password hashing with password_hash()
- CSRF token generation and validation
- OAuth 2.0 integration (Google)
- Admin authentication and authorization
- Advanced OOP with interfaces
- Database transactions
- Input validation and sanitization
- Responsive web design
- AJAX functionality

**Database Schema**:
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    genre VARCHAR(100),
    year INT,
    price DECIMAL(10,2),
    status ENUM('available', 'borrowed') DEFAULT 'available'
);

CREATE TABLE BookLoans (
    loan_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT,
    member_id INT,
    loan_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    return_date TIMESTAMP NULL,
    FOREIGN KEY (book_id) REFERENCES Books(book_id),
    FOREIGN KEY (member_id) REFERENCES users(id)
);
```

## Setup Instructions

### 1. Environment Setup
1. Install XAMPP and start Apache + MySQL services
2. Clone or download this repository to `htdocs/PHP_LAB/`
3. Access phpMyAdmin at `http://localhost/phpmyadmin`

### 2. Database Configuration
For each lab, create the required databases:

```sql
-- LAB1
CREATE DATABASE TestDB;

-- LAB2
CREATE DATABASE WebAppDB;
CREATE DATABASE LibrarySystemDB;

-- LAB3
CREATE DATABASE EmployeeDB;
CREATE DATABASE StudentDB;

-- LAB4 (Exo4) & LAB5and6
CREATE DATABASE LibraryDB;
```

### 3. Database Credentials
Update database connection files with your credentials:
- Username: `root`
- Password: `billmartial` (update as needed)
- Host: `localhost`

### 4. LAB5and6 Additional Setup
1. Install Composer dependencies:
   ```bash
   cd LAB5and6
   composer install
   ```

2. For Google OAuth (optional):
   - Create Google OAuth credentials
   - Update `config.php` with your credentials
   - Set up redirect URI

## Running the Labs

### Access URLs
- LAB1: `http://localhost/PHP_LAB/LAB1/`
- LAB2: `http://localhost/PHP_LAB/LAB2/`
- LAB3: `http://localhost/PHP_LAB/LAB3/`
- LAB4: `http://localhost/PHP_LAB/LAB4/`
- LAB5and6: `http://localhost/PHP_LAB/LAB5and6/`

### Recommended Learning Path
1. **LAB1**: Start with basic PHP and database operations
2. **LAB2**: Learn form handling and validation
3. **LAB3**: Master database relationships and advanced CRUD
4. **LAB4**: Understand object-oriented programming concepts
5. **LAB5and6**: Build complete secure web applications

## Key Learning Outcomes

### Technical Skills
- PHP programming fundamentals
- MySQL database design and operations
- Object-oriented programming in PHP
- Web security best practices
- Session management and authentication
- Form validation and sanitization
- AJAX and modern web development

### Security Concepts
- SQL injection prevention
- CSRF protection
- Password hashing and verification
- Session security
- Input validation and sanitization
- Authentication and authorization

### Development Practices
- Code organization and structure
- Error handling and debugging
- Database design principles
- User experience considerations
- Responsive web design
- Version control best practices

## Common Issues and Troubleshooting

### Database Connection Issues
- Ensure XAMPP MySQL service is running
- Verify database credentials in connection files
- Check if required databases exist
- Confirm table structures match schema

### Permission Issues
- Ensure proper file permissions in htdocs
- Check Apache configuration
- Verify PHP extensions are enabled

### LAB5and6 Specific Issues
- Run `composer install` for dependencies
- Check Google OAuth configuration
- Verify session configuration
- Ensure CSRF tokens are properly implemented

## Lab-Specific Quick Start Guides

### LAB1 Quick Start
1. Create `TestDB` database
2. Run the Books table schema
3. Start with `hello.php` to test PHP
4. Try `create_book.php` to add books
5. Use `read_book.php` to view all books

### LAB2 Quick Start
1. Create `WebAppDB` database
2. Run the Users table schema
3. Start with `user_form.php` to add users
4. Test validation with invalid data
5. Explore `EXO2/` for book-author relationships

### LAB3 Quick Start
1. Create `EmployeeDB` and `StudentDB` databases
2. Set up Department and Employee tables
3. Add sample departments first
4. Test employee management features
5. Try `EXERCISE_2/` for student management

### LAB4 Quick Start
1. Start with `Exo1/create_book.php` for basic OOP
2. Progress through `Exo2/test_inheritance.php`
3. Understand interfaces in `Exo3/test_polymorphism.php`
4. Complete system in `Exo4/library_test.php`

### LAB5and6 Quick Start
1. Create `LibraryDB` database
2. Run `composer install`
3. Set up all required tables
4. Start with `signup.php` to create account
5. Login and explore `library_test.php`
6. Try admin features at `admin_login.php` (admin/password)

## Contributing
This is an educational project. Students are encouraged to:
- Experiment with the code
- Add new features
- Improve security implementations
- Enhance user interfaces
- Document their learning journey

## License
This project is for educational purposes. Feel free to use and modify for learning.

---

**Happy Learning!** 🚀

For questions or issues, please refer to the individual lab files or consult your instructor.
