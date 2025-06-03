<?php
$conn = new mysqli("localhost", "root", "billmartial", "LibrarySystemDB");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Correct SQL JOIN between Books and Authors
$sql = "SELECT b.book_id, b.book_title, a.name AS author_name, b.genre, b.price 
        FROM Books b
        INNER JOIN Authors a ON b.author_id = a.author_id";

$result = $conn->query($sql);

// Check if query succeeded
if (!$result) {
    die("Query error: " . $conn->error);
}

// Display table
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Title</th><th>Author</th><th>Genre</th><th>Price</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['book_id']}</td>
            <td>{$row['book_title']}</td>
            <td>{$row['author_name']}</td>
            <td>{$row['genre']}</td>
            <td>{$row['price']}</td>
          </tr>";
}

echo "</table>";

$conn->close();
?>
