<?php
session_start();
require_once 'db.php';
require_once 'csrf_token.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('POST data in edit_book.php: ' . print_r($_POST, true));
}

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: admin_login.php");
    exit();
}

$message = "";
$message_type = "";

// Handle edit book form submission
if (isset($_POST['action']) && $_POST['action'] === 'edit_book') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $message = "Security validation failed. Please try again.";
        $message_type = "error";
    } else {
        $book_id = (int)$_POST['book_id'];
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $price = trim($_POST['price']);
        $genre = trim($_POST['genre']);
        $year = isset($_POST['year']) ? trim($_POST['year']) : date('Y');
        
        if ($title && $author && $price && $genre) {
            try {
                // Update book in database
                $stmt = $pdo->prepare("UPDATE Books SET title = ?, author = ?, price = ?, genre = ?, year = ? WHERE book_id = ?");
                $result = $stmt->execute([$title, $author, $price, $genre, $year, $book_id]);
                
                if ($result) {
                    $message = "Book updated successfully!";
                    $message_type = "success";
                    
                    // Refresh CSRF token after successful form submission
                    refresh_csrf_token();
                } else {
                    $message = "Error updating book.";
                    $message_type = "error";
                }
            } catch (PDOException $e) {
                $message = "Database error: " . $e->getMessage();
                $message_type = "error";
            }
        } else {
            $message = "Please fill in all required fields.";
            $message_type = "error";
        }
    }
}

// Fetch all books
$stmt = $pdo->query("SELECT * FROM Books ORDER BY book_id ASC");
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
    <title>Edit Book</title>
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
            padding: 0 20px; 
            margin-bottom: 30px; 
            font-size: 1.75rem;
            display: flex;
            align-items: center;
        }
        .sidebar h2 i {
            margin-right: 10px;
        }
        .sidebar ul { 
            list-style: none; 
            padding: 0; 
            margin: 0; 
        }
        .sidebar ul li { 
            margin-bottom: 5px; 
        }
        .sidebar ul li a { 
            display: block; 
            padding: 12px 20px; 
            color: rgba(255,255,255,0.8); 
            text-decoration: none; 
            transition: all 0.3s;
            font-size: 1rem;
        }
        .sidebar ul li a:hover, 
        .sidebar ul li.active a { 
            background: rgba(255,255,255,0.1); 
            color: #fff; 
            padding-left: 25px; 
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
            margin-bottom: 25px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 30px;
            background: #fafbfc;
        }
        th, td { 
            padding: 12px 15px; 
            border: 1px solid #e1e4e8; 
            text-align: left;
        }
        th { 
            background: #f1f5f9; 
            color: #34495e;
            font-weight: 600;
        }
        tr:hover { 
            background: #f6f8fa; 
        }
        button { 
            background: #007bff; 
            color: #fff; 
            border: none; 
            padding: 8px 15px; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 0.9rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
        }
        button i {
            margin-right: 5px;
        }
        button:hover { 
            background: #0069d9; 
        }
        .message { 
            margin-bottom: 20px; 
            padding: 15px;
            border-radius: 4px;
        }
        .message.success { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb;
        }
        .message.error { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb;
        }
        .edit-form {
            background: #f8f9fa;
            border: 1px solid #e1e4e8;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: none;
        }
        .edit-form h3 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 1px solid #e1e4e8;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #34495e;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .btn-cancel {
            background: #6c757d;
        }
        .btn-cancel:hover {
            background: #5a6268;
        }
        .bible-quote {
            font-style: italic;
            background: #fff;
            border-left: 4px solid <?php echo $random_color; ?>;
            padding: 15px 20px;
            border-radius: 8px;
            position: fixed;
            right: 30px;
            bottom: 30px;
            min-width: 280px;
            max-width: 350px;
            z-index: 999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            color: <?php echo $random_color; ?>;
            font-size: 1.1rem;
            line-height: 1.5;
        }
        .logout-link {
            color: #fff !important;
            background: rgba(220, 53, 69, 0.2);
            margin-top: 30px !important;
        }
        .logout-link:hover {
            background: rgba(220, 53, 69, 0.4) !important;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .bg-success {
            background-color: #28a745 !important;
            color: white;
        }
        .bg-warning {
            background-color: #ffc107 !important;
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2><i class="fas fa-book-reader"></i> Library Admin</h2>
        <ul>
            <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="borrowed_book.php"><i class="fas fa-book-open"></i> Borrowed Books</a></li>
            <li><a href="view_users.php"><i class="fas fa-users"></i> View Members</a></li>
            <li class="active"><a href="edit_book.php"><i class="fas fa-edit"></i> Edit Book</a></li>
            <li><a href="delete_book.php"><i class="fas fa-trash-alt"></i> Delete Book</a></li>
            <li><a href="add_book.php"><i class="fas fa-plus-circle"></i> Add Book</a></li>
            <li><a class="logout-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div class="container">
        <h2><i class="fas fa-edit"></i> Edit Books</h2>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

<div id="editForm" class="edit-form">
    <h3><i class="fas fa-edit"></i> Edit Book Details</h3>
    <form method="post" action="edit_book.php">
        <?php csrf_token_field(); ?>
        <input type="hidden" name="action" value="edit_book">
        <input type="hidden" id="book_id" name="book_id">
        
        <div class="form-group">
            <label for="title"><i class="fas fa-book"></i> Title:</label>
            <input type="text" id="title" name="title" required>
        </div>
        
        <div class="form-group">
            <label for="author"><i class="fas fa-user-edit"></i> Author:</label>
            <input type="text" id="author" name="author" required>
        </div>
        
        <div class="form-group">
            <label for="price"><i class="fas fa-tag"></i> Price:</label>
            <input type="number" step="0.01" id="price" name="price" required>
        </div>
        
        <div class="form-group">
            <label for="genre"><i class="fas fa-bookmark"></i> Genre:</label>
            <input type="text" id="genre" name="genre" required>
        </div>
        
        <div class="form-group">
            <label for="year"><i class="fas fa-calendar-alt"></i> Year:</label>
            <input type="number" id="year" name="year" min="1900" max="<?php echo date('Y'); ?>">
        </div>
        
        <div class="form-actions">
            <button type="submit">
                <i class="fas fa-save"></i> Update Book
            </button>
            <button type="button" class="btn-cancel" onclick="hideEditForm()">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </form>
</div>

<table>
    <tr>
        <th>#</th>
        <th>Title</th>
        <th>Author</th>
        <th>Price</th>
        <th>Genre</th>
        <th>Year</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php if (empty($books)): ?>
        <tr>
            <td colspan="8" style="text-align: center;">No books found in the database.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($books as $i => $book): ?>
        <tr>
            <td><?php echo $i+1; ?></td>
            <td><?php echo htmlspecialchars($book['title']); ?></td>
            <td><?php echo htmlspecialchars($book['author']); ?></td>
            <td>$<?php echo htmlspecialchars($book['price']); ?></td>
            <td><?php echo htmlspecialchars($book['genre']); ?></td>
            <td><?php echo htmlspecialchars($book['year'] ?? 'N/A'); ?></td>
            <td>
                <span class="badge <?php echo $book['status'] === 'available' ? 'bg-success' : 'bg-warning'; ?>">
                    <?php echo ucfirst(htmlspecialchars($book['status'])); ?>
                </span>
            </td>
            <td>
                <button onclick="showEditForm(<?php echo $book['book_id']; ?>, '<?php echo addslashes($book['title']); ?>', '<?php echo addslashes($book['author']); ?>', '<?php echo $book['price']; ?>', '<?php echo addslashes($book['genre']); ?>', '<?php echo $book['year'] ?? date('Y'); ?>')">
                    <i class="fas fa-edit"></i> Edit
                </button>
                
                <!-- Direct update form for testing -->
                <form method="post" action="edit_book.php" style="display: inline-block; margin-left: 5px;">
                    <?php csrf_token_field(); ?>
                    <input type="hidden" name="action" value="edit_book">
                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                    <input type="hidden" name="title" value="<?php echo htmlspecialchars($book['title']); ?>">
                    <input type="hidden" name="author" value="<?php echo htmlspecialchars($book['author']); ?>">
                    <input type="hidden" name="price" value="<?php echo htmlspecialchars($book['price']); ?>">
                    <input type="hidden" name="genre" value="<?php echo htmlspecialchars($book['genre']); ?>">
                    <input type="hidden" name="year" value="<?php echo htmlspecialchars($book['year'] ?? date('Y')); ?>">
                    <button type="submit" style="background-color: #28a745;">
                        <i class="fas fa-check"></i> Quick Update
                    </button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>
    </div>
</body>
</html>
<script>
    function showEditForm(bookId, title, author, price, genre, year) {
        // Set form values
        document.getElementById('book_id').value = bookId;
        document.getElementById('title').value = title;
        document.getElementById('author').value = author;
        document.getElementById('price').value = price;
        document.getElementById('genre').value = genre;
        document.getElementById('year').value = year;
        
        // Show the form
        document.getElementById('editForm').style.display = 'block';
        
        // Scroll to the form
        document.getElementById('editForm').scrollIntoView({ behavior: 'smooth' });
    }
    
    function hideEditForm() {
        document.getElementById('editForm').style.display = 'none';
    }
    
    // Check for success parameter in URL
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            alert('Book updated successfully!');
        }
        
        // If there's an error message, show the form again
        <?php if ($message_type === 'error' && isset($_POST['book_id'])): ?>
        showEditForm(
            <?php echo json_encode($_POST['book_id']); ?>,
            <?php echo json_encode($_POST['title']); ?>,
            <?php echo json_encode($_POST['author']); ?>,
            <?php echo json_encode($_POST['price']); ?>,
            <?php echo json_encode($_POST['genre']); ?>,
            <?php echo json_encode($_POST['year']); ?>
        );
        <?php endif; ?>
    });
</script>
