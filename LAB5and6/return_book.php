<?php
session_start();
require_once 'db.php';
require_once 'Book.php';
require_once 'user.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Not logged in";
    exit;
}

if (isset($_POST['book_id'])) {
    $book_id = $_POST['book_id'];
     $users_id = $_SESSION['user_id'];

    $users_stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $users_stmt->execute([$users_id]);
    $users_row = $users_stmt->fetch();
    $users = new user($users_row['id'], $users_row['username'], $users_row['email'], $users_row['created_at']);

    $book_stmt = $pdo->prepare("SELECT * FROM Books WHERE book_id=?");
    $book_stmt->execute([$book_id]);
    $b = $book_stmt->fetch();
    $book = new Book($b['book_id'], $b['title'], $b['author'], $b['price'], $b['genre'], $b['year']);
    if ($book->returnBook($users->id)) {
        echo "Book returned successfully.";
    } else {
        echo "Failed to return book.";
    }
} else {
    echo "Book ID not provided.";
}
?>