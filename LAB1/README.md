# LAB1 - Basic PHP and MySQL CRUD Operations

## Overview
This lab introduces fundamental PHP programming concepts and basic database operations using MySQL. Students will learn to perform CRUD (Create, Read, Update, Delete) operations on a simple Books database.

## Learning Objectives
- Understand basic PHP syntax and structure
- Learn to connect PHP with MySQL database
- Implement basic CRUD operations
- Work with HTML forms and PHP form processing
- Understand prepared statements for database security

## Prerequisites
- XAMPP server installed and running
- Basic understanding of HTML
- MySQL database knowledge

## Database Setup

### Database Information
- **Database Name**: `TestDB`
- **Table**: `Books`
- **Connection Details**:
  - Host: localhost
  - Username: root
  - Password: billmartial

### Database Schema
```sql
CREATE DATABASE TestDB;
USE TestDB;

CREATE TABLE Books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    publication_year INT,
    genre VARCHAR(100),
    price DECIMAL(10,2)
);

-- Sample data
INSERT INTO Books (title, author, publication_year, genre, price) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', 1925, 'Fiction', 10.99),
('To Kill a Mockingbird', 'Harper Lee', 1960, 'Fiction', 12.50),
('1984', 'George Orwell', 1949, 'Dystopian Fiction', 13.99);
```

## File Structure
```
LAB1/
├── README.md
├── hello.php          # Basic PHP introduction
├── create_book.php    # Add new books to database
├── read_book.php      # Display all books
├── update_book.php    # Edit existing books
└── delete_book.php    # Remove books from database
```

## Files Description

### 1. hello.php
**Purpose**: Introduction to PHP syntax
**Functionality**: Displays "Hello, World!" message
**Key Concepts**: 
- PHP opening and closing tags
- Echo statement

### 2. create_book.php
**Purpose**: Add new books to the database
**Functionality**: 
- HTML form for book input
- Form processing with PHP
- Database insertion using prepared statements
**Key Concepts**:
- HTML forms with POST method
- PHP form validation
- MySQLi prepared statements
- Data binding with bind_param()

### 3. read_book.php
**Purpose**: Display all books from database
**Functionality**:
- Database connection
- SQL SELECT query
- HTML table generation
**Key Concepts**:
- Database queries
- Result set processing
- HTML table creation with PHP

### 4. update_book.php
**Purpose**: Edit existing book records
**Functionality**:
- Retrieve book data by ID
- Pre-populate form with existing data
- Update database record
**Key Concepts**:
- URL parameters ($_GET)
- Form pre-population
- UPDATE SQL statements
- Data validation

### 5. delete_book.php
**Purpose**: Remove books from database
**Functionality**:
- Delete records by ID
- Confirmation before deletion
**Key Concepts**:
- DELETE SQL statements
- URL parameter handling
- Basic security considerations

## How to Run

1. **Start XAMPP**:
   - Start Apache and MySQL services

2. **Setup Database**:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create the TestDB database
   - Run the provided SQL schema

3. **Access the Lab**:
   - Place LAB1 folder in `htdocs` directory
   - Navigate to `http://localhost/PHP_LAB/LAB1/`

4. **Test Each File**:
   - `hello.php` - Basic PHP test
   - `create_book.php` - Add new books
   - `read_book.php` - View all books
   - `update_book.php?id=1` - Edit book with ID 1
   - `delete_book.php?id=1` - Delete book with ID 1

## Key Programming Concepts Demonstrated

### 1. PHP Basics
- PHP syntax and structure
- Variables and data types
- Echo and print statements

### 2. Database Connectivity
- MySQLi connection
- Connection error handling
- Database selection

### 3. CRUD Operations
- **Create**: INSERT statements with form data
- **Read**: SELECT statements and result display
- **Update**: UPDATE statements with WHERE clause
- **Delete**: DELETE statements with ID parameter

### 4. Security Practices
- Prepared statements to prevent SQL injection
- Parameter binding
- Input validation

### 5. HTML Integration
- Embedding PHP in HTML
- Form creation and processing
- Dynamic content generation

## Common Issues and Solutions

### Database Connection Errors
- Verify XAMPP MySQL is running
- Check database credentials in connection strings
- Ensure TestDB database exists

### Form Submission Issues
- Verify form method is POST
- Check form action attribute
- Ensure all required fields are included

### SQL Errors
- Check table and column names
- Verify data types match
- Use phpMyAdmin to test queries

## Next Steps
After completing LAB1, students should be comfortable with:
- Basic PHP syntax
- Database connections
- Simple CRUD operations
- Form handling

This foundation prepares students for more advanced topics in subsequent labs including form validation, object-oriented programming, and security features.
