<form method="POST" action="create_book.php">
    Title: <input type="text" name="title" required><br>
    Author: <input type="text" name="author" required><br>
    Year: <input type="number" name="year" required><br>
    Genre: <input type="text" name="genre" required><br>
    Price: <input type="text" name="price" required><br>
    <input type="submit" value="Add Book">
</form>

<hr>
<a href="update_book.php">update book</a><br>

<a href="delete_book.php">delete book</a><br>

<a href="read_book.php">Read Book</a>
        

        </hr>



<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "billmartial", "TestDB");
    $stmt = $conn->prepare("INSERT INTO Books (title, author, publication_year, genre, price) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisd", $_POST['title'], $_POST['author'], $_POST['year'], $_POST['genre'], $_POST['price']);
    $stmt->execute();
    echo "Book added successfully!";
    $stmt->close();
    $conn->close();
}
?>
