<?php
/**
 * Book form processing script
 * 
 * This file handles the processing of book-related forms (add, edit)
 * with CSRF protection and proper validation.
 */

session_start();
require_once 'db.php';
require_once 'csrf_token.php';

// Add at the beginning of the file, after session_start()
error_log('POST data: ' . print_r($_POST, true));

// Check if user is authorized
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: admin_login.php");
    exit();
}

// Initialize response variables
$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    $response['message'] = "CSRF token validation failed. Please try again.";
    echo json_encode($response);
    exit();
}

// Process add book form
if (isset($_POST['action']) && $_POST['action'] === 'add_book') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $year = isset($_POST['year']) ? trim($_POST['year']) : date('Y');
    
    // Validate required fields
    if (empty($title) || empty($author) || empty($price) || empty($genre)) {
        $response['message'] = "Please fill in all required fields.";
        echo json_encode($response);
        exit();
    }
    
    try {
        // Insert book into database
        $stmt = $pdo->prepare("INSERT INTO Books (title, author, price, genre, year, status) VALUES (?, ?, ?, ?, ?, 'available')");
        $result = $stmt->execute([$title, $author, $price, $genre, $year]);
        
        if ($result) {
            // Refresh CSRF token after successful form submission
            refresh_csrf_token();
            
            $response['success'] = true;
            $response['message'] = "Book added successfully!";
            $response['redirect'] = "add_book.php?success=1";
        } else {
            $response['message'] = "Error adding book to database.";
        }
    } catch (PDOException $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    }
}

// Process edit book form
elseif (isset($_POST['action']) && $_POST['action'] === 'edit_book') {
    error_log('Processing edit_book action');
    $book_id = (int)($_POST['book_id'] ?? 0);
    error_log('Book ID: ' . $book_id);
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $year = isset($_POST['year']) ? trim($_POST['year']) : date('Y');
    
    // Validate required fields
    if (empty($book_id) || empty($title) || empty($author) || empty($price) || empty($genre)) {
        $response['message'] = "Please fill in all required fields.";
        echo json_encode($response);
        exit();
    }
    
    try {
        // Update book in database
        $stmt = $pdo->prepare("UPDATE Books SET title = ?, author = ?, price = ?, genre = ?, year = ? WHERE book_id = ?");
        $result = $stmt->execute([$title, $author, $price, $genre, $year, $book_id]);
        
        if ($result) {
            // Refresh CSRF token after successful form submission
            refresh_csrf_token();
            
            $response['success'] = true;
            $response['message'] = "Book updated successfully!";
            $response['redirect'] = "edit_book.php?id=" . $book_id . "&success=1";
        } else {
            $response['message'] = "Error updating book.";
        }
    } catch (PDOException $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    }
}

// Process delete book form
elseif (isset($_POST['action']) && $_POST['action'] === 'delete_book') {
    $book_id = (int)($_POST['book_id'] ?? 0);
    
    if (empty($book_id)) {
        $response['message'] = "Invalid book ID.";
        echo json_encode($response);
        exit();
    }
    
    // Check if book is borrowed
    $check_stmt = $pdo->prepare("SELECT * FROM BookLoans WHERE book_id = ? AND return_date IS NULL");
    $check_stmt->execute([$book_id]);
    
    if ($check_stmt->rowCount() > 0) {
        $response['message'] = "This book cannot be deleted because it is currently borrowed.";
    } else {
        try {
            // Delete the book
            $stmt = $pdo->prepare("DELETE FROM Books WHERE book_id = ?");
            $result = $stmt->execute([$book_id]);
            
            if ($result) {
                // Refresh CSRF token after successful form submission
                refresh_csrf_token();
                
                $response['success'] = true;
                $response['message'] = "Book deleted successfully!";
                $response['redirect'] = "delete_book.php?success=1";
            } else {
                $response['message'] = "Error deleting book.";
            }
        } catch (PDOException $e) {
            $response['message'] = "Database error: " . $e->getMessage();
        }
    }
}

// No valid action specified
else {
    $response['message'] = "Invalid action.";
}

// Return JSON response
echo json_encode($response);
exit();
?>
