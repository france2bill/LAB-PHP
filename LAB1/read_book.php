<?php
$conn = new mysqli("localhost", "root", "billmartial", "TestDB");
$result = $conn->query("SELECT * FROM Books");

echo "<table border='1'>";
echo "<tr><th>ID</th><th>Title</th><th>Author</th><th>Year</th><th>Genre</th><th>Price</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['book_id']}</td>
            <td>{$row['title']}</td>
            <td>{$row['author']}</td>
            <td>{$row['publication_year']}</td>
            <td>{$row['genre']}</td>
            <td>{$row['price']}</td>
          </tr>";
}
echo "</table>";

$conn->close();
?>
