<?php
$conn = new mysqli("localhost", "root", "billmartial", "TestDB");

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update logic
if (isset($_POST['update'])) {
    $stmt = $conn->prepare("UPDATE Books SET title=?, author=?, publication_year=?, genre=?, price=? WHERE book_id=?");
    $stmt->bind_param("ssisdi", $_POST['title'], $_POST['author'], $_POST['year'], $_POST['genre'], $_POST['price'], $_POST['book_id']);
    $stmt->execute();
    echo "Book updated successfully!";
    $stmt->close();
    exit;
}

// Check if 'id' exists in the URL
if (!isset($_GET['id'])) {
    die("Error: No book ID provided in URL.");
}

$id = intval($_GET['id']);  // sanitize input
$result = $conn->query("SELECT * FROM Books WHERE book_id = $id");

if ($result && $row = $result->fetch_assoc()):
?>

<form method="POST">
    <input type="hidden" name="book_id" value="<?= $row['book_id'] ?>">
    Title: <input type="text" name="title" value="<?= $row['title'] ?>"><br>
    Author: <input type="text" name="author" value="<?= $row['author'] ?>"><br>
    Year: <input type="number" name="year" value="<?= $row['publication_year'] ?>"><br>
    Genre: <input type="text" name="genre" value="<?= $row['genre'] ?>"><br>
    Price: <input type="text" name="price" value="<?= $row['price'] ?>"><br>
    <input type="submit" name="update" value="Update Book">
</form>

<?php
else:
    echo "Book not found.";
endif;

$conn->close();
?>
