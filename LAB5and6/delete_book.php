<?php
session_start();
require_once 'db.php';
require_once 'csrf_token.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: admin_login.php");
    exit();
}

// Handle delete action
if (isset($_POST['delete_book'])) {
    $book_id = (int)$_POST['book_id'];
    
    // Check if book is borrowed
    $check_stmt = $pdo->prepare("SELECT * FROM BookLoans WHERE book_id = ? AND return_date IS NULL");
    $check_stmt->execute([$book_id]);
    
    if ($check_stmt->rowCount() > 0) {
        $error = "This book cannot be deleted because it is currently borrowed.";
    } else {
        // Delete the book
        $stmt = $pdo->prepare("DELETE FROM books WHERE book_id = ?");
        $stmt->execute([$book_id]);
        
        header("Location: delete_book.php?success=1");
        exit();
    }
}

// Fetch all books
$stmt = $pdo->query("SELECT * FROM books ORDER BY book_id ASC");
$books = $stmt->fetchAll();

// Bible quotes array
$bible_quotes = [
    "For I know the plans I have for you, declares the Lord. – Jeremiah 29:11",
    "I can do all things through Christ who strengthens me. – Philippians 4:13",
    "The Lord is my shepherd; I shall not want. – Psalm 23:1",
    "Trust in the Lord with all your heart. – Proverbs 3:5",
    "With God all things are possible. – Matthew 19:26"
];
$random_quote = $bible_quotes[array_rand($bible_quotes)];
$quote_colors = ["#007bff", "#28a745", "#dc3545", "#6f42c1", "#fd7e14"];
$random_color = $quote_colors[array_rand($quote_colors)];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delete Book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: #fff;
            padding-top: 40px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .sidebar h2 {
            color: #fff;
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.5rem;
            letter-spacing: 1px;
            padding: 0 20px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin: 8px 0;
        }
        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 1.08rem;
            padding: 12px 30px;
            display: block;
            border-radius: 4px;
            margin: 0 10px;
            transition: all 0.3s;
        }
        .sidebar ul li a:hover, .sidebar ul li.active a {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        .sidebar ul li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .container {
            margin-left: 280px;
            max-width: 1000px;
            padding: 40px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08);
            margin-top: 40px;
            margin-bottom: 40px;
        }
        h2 {
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: #fafbfc;
        }
        th, td {
            padding: 10px 8px;
            border: 1px solid #e1e4e8;
            text-align: left;
        }
        th {
            background: #e9ecef;
            color: #34495e;
        }
        tr:nth-child(even) {
            background: #f6f8fa;
        }
        button {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 6px 14px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #a71d2a;
        }
        .bible-quote {
            font-style: italic;
            font-weight: bold;
            font-size: 1.25rem;
            color: <?php echo $random_color; ?>;
            background: #e9ecef;
            border-left: 6px solid <?php echo $random_color; ?>;
            padding: 18px 24px;
            border-radius: 10px;
            position: fixed;
            right: 30px;
            bottom: 30px;
            min-width: 280px;
            max-width: 400px;
            z-index: 999;
            box-shadow: 0 2px 12px rgba(0,0,0,0.10);
            background-clip: padding-box;
            text-shadow: 0 1px 0 #fff;
            letter-spacing: 0.5px;
        }
        .search-box {
            position: relative;
            margin-bottom: 25px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 20px;
            padding-left: 45px;
            border-radius: 30px;
            border: 1px solid #e1e4e8;
            font-size: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .search-box i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Dashboard</h2>
        <ul>
            <li><a href="admin_dashboard.php"><i class="fas fa-book"></i> Available Books</a></li>
            <li><a href="borrowed_book.php"><i class="fas fa-book-reader"></i> Borrowed Books</a></li>
            <li><a href="view_users.php"><i class="fas fa-users"></i> View Members</a></li>
            <li><a href="edit_book.php"><i class="fas fa-edit"></i> Edit Book</a></li>
            <li class="active"><a href="delete_book.php"><i class="fas fa-trash-alt"></i> Delete Book</a></li>
            <li><a href="add_book.php"><i class="fas fa-plus"></i> Add Book</a></li>
            <li><a class="logout-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div class="container">
        <h2>Delete Book</h2>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="bookSearchInput" placeholder="Search books by title, author, or genre..." onkeyup="searchBooks()">
        </div>
        <table>
            <tr>
                <th>#</th><th>Title</th><th>Author</th><th>Price</th><th>Genre</th><th>Action</th>
            </tr>
            <?php foreach ($books as $i => $book): ?>
            <tr>
                <td><?php echo $i+1; ?></td>
                <td><?php echo htmlspecialchars($book['title']); ?></td>
                <td><?php echo htmlspecialchars($book['author']); ?></td>
                <td><?php echo htmlspecialchars($book['price']); ?></td>
                <td><?php echo htmlspecialchars($book['genre']); ?></td>
                <td>
                    <form method="post" action="process_book.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                        <input type="hidden" name="action" value="delete_book">
                        <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($book['book_id']); ?>">
                        <button type="submit" name="delete_book" onclick="return confirm('Delete this book?');">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div class="bible-quote">
        <?php echo $random_quote; ?>
    </div>
    <script>
function searchBooks() {
    var input, filter, table, tr, td, i, j, txtValue, found;
    input = document.getElementById("bookSearchInput");
    filter = input.value.toUpperCase();
    table = document.querySelector("table");
    tr = table.getElementsByTagName("tr");
    
    // Loop through all table rows except the header
    for (i = 1; i < tr.length; i++) {
        found = false;
        
        // Check all cells in the row (except the last one which contains actions)
        for (j = 0; j < tr[i].cells.length - 1; j++) {
            td = tr[i].cells[j];
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        // Show or hide the row based on search match
        if (found) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
}
</script>
</body>
</html>
