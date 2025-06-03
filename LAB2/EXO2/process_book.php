<?php
$conn = new mysqli("localhost", "root", "billmartial", "LibrarySystemDB");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (
    isset($_POST['book_title']) &&
    isset($_POST['author_id']) &&
    isset($_POST['genre']) &&
    isset($_POST['price'])
) {
    $book_title = trim($_POST['book_title']);
    $author_id = intval($_POST['author_id']);
    $genre = trim($_POST['genre']);
    $price = floatval($_POST['price']);

    // Prepare SQL
    $stmt = $conn->prepare("INSERT INTO Books (book_title, author_id, genre, price) VALUES (?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("sisd", $book_title, $author_id, $genre, $price);
        $stmt->execute();
        echo "Book added successfully!";
        $stmt->close();
    } else {
        die("Error in SQL preparation: " . $conn->error);
    }
} else {
    die("Form data missing. Please submit the form properly.");
}

$conn->close();
?>
