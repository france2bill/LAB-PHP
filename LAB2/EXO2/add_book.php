<?php
$conn = new mysqli("localhost", "root", "billmartial", "LibrarySystemDB");

$authors = $conn->query("SELECT * FROM Authors");
?>

<h2>Add a New Book</h2>

<form method="POST" action="process_book.php">
    Title: <input type="text" name="book_title" required><br>
    Author:
    <select name="author_id" required>
        <?php while ($row = $authors->fetch_assoc()): ?>
            <option value="<?= $row['author_id'] ?>"><?= $row['name'] ?></option>
        <?php endwhile; ?>
    </select><br>
    Genre: <input type="text" name="genre" required><br>
    Price: <input type="text" name="price" required><br>
    <input type="submit" value="Add Book">
</form>

<!-- Only these two links as requested -->
<hr>
<a href="view_book.php">View All Books</a>
        </hr>
