<?php
session_start();
require_once 'db.php';
require_once 'user.php';

// Check if user is logged in (you might want to move this to a common include file)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$users_id = $_SESSION['user_id'];
$users_stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$users_stmt->execute([$users_id]);
$users_row = $users_stmt->fetch();
$users = new user($users_row['id'], $users_row['username'], $users_row['email'], $users_row['created_at']);

// Fetch the book lists
$availableBooks = $pdo->query("SELECT * FROM Books WHERE status = 'available'")->fetchAll(PDO::FETCH_ASSOC);
$borrowedBooks = $users->getBookloans();

// Return the data as JSON
echo json_encode(['availableBooks' => $availableBooks, 'borrowedBooks' => $borrowedBooks]);
?>