<?php
session_start();
require_once 'db.php';
require_once 'admin_auth.php'; // This will handle authentication and CSRF token

// Generate CSRF token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Fetch borrowed books from BookLoans table
$stmt = $pdo->query("
    SELECT b.book_id, b.title, b.author, b.genre, b.price, u.username, u.email, bl.loan_date 
    FROM BookLoans bl
    JOIN Books b ON bl.book_id = b.book_id
    JOIN users u ON bl.member_id = u.id
    WHERE bl.return_date IS NULL
    ORDER BY bl.loan_date DESC
");
$borrowed_books = $stmt->fetchAll();
$borrowed_count = count($borrowed_books);

// Handle return action
if (isset($_POST['return_book'])) {
    // CSRF token is already verified by admin_auth.php
    $book_id = (int)$_POST['book_id'];
    
    // Get the book loan details to find the member
    $loan_stmt = $pdo->prepare("SELECT member_id FROM BookLoans WHERE book_id = ? AND return_date IS NULL");
    $loan_stmt->execute([$book_id]);
    $loan = $loan_stmt->fetch();
    
    if ($loan) {
        $member_id = $loan['member_id'];
        
        // Get book details
        $book_stmt = $pdo->prepare("SELECT * FROM Books WHERE book_id = ?");
        $book_stmt->execute([$book_id]);
        $b = $book_stmt->fetch();
        
        if ($b) {
            // Create Book object and return it
            require_once 'Book.php';
            $book = new Book($b['book_id'], $b['title'], $b['author'], $b['price'], $b['genre'], $b['year']);
            if ($book->returnBook($member_id)) {
                header("Location: borrowed_book.php?success=returned");
                exit();
            }
        }
    }
    
    header("Location: borrowed_book.php?error=failed_return");
    exit();
}

// Bible quotes array
$bible_quotes = [
    "For I know the plans I have for you, declares the Lord. – Jeremiah 29:11",
    "I can do all things through Christ who strengthens me. – Philippians 4:13",
    "The Lord is my shepherd; I shall not want. – Psalm 23:1",
    "Trust in the Lord with all your heart. – Proverbs 3:5",
    "With God all things are possible. – Matthew 19:26"
];
$random_quote = $bible_quotes[array_rand($bible_quotes)];
$quote_colors = ["#007bff", "#28a745", "#dc3545", "#fd7e14", "#6f42c1"];
$random_color = $quote_colors[array_rand($quote_colors)];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Borrowed Books</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/responsive.css">
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
            font-size: 1.6rem;
            letter-spacing: 1px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
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
            font-size: 1.1rem;
            padding: 12px 25px;
            display: block;
            border-radius: 5px;
            margin: 0 10px;
            transition: all 0.3s;
        }
        .sidebar ul li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .sidebar ul li a:hover, .sidebar ul li.active a {
            background: rgba(255,255,255,0.15);
            transform: translateX(5px);
        }
        .sidebar ul li.active a {
            background: rgba(255,255,255,0.2);
            font-weight: 600;
        }
        .container {
            margin-left: 270px;
            max-width: 1000px;
            padding: 40px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08);
            margin-top: 40px;
            margin-bottom: 40px;
            position: relative;
        }
        .container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #007bff, #0dcaf0);
            border-radius: 10px 10px 0 0;
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        h2 i {
            margin-right: 10px;
            color: #007bff;
        }
        .stats-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            border-left: 5px solid #007bff;
        }
        .stats-card i {
            font-size: 2.5rem;
            color: #007bff;
            margin-right: 20px;
        }
        .stats-card .stats-info h3 {
            margin: 0;
            font-size: 1.8rem;
            color: #343a40;
        }
        .stats-card .stats-info p {
            margin: 5px 0 0;
            color: #6c757d;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        th {
            background: #f8f9fa;
            color: #495057;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        tr:last-child td {
            border-bottom: none;
        }
        button {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        button i {
            margin-right: 5px;
        }
        button:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .empty-message {
            text-align: center;
            padding: 30px;
            color: #6c757d;
            font-style: italic;
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
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            color: <?php echo $random_color; ?>;
            font-weight: 500;
        }
        .logout-link {
            display: inline-block;
            margin-top: 18px;
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 5px;
            background: rgba(220, 53, 69, 0.8);
            transition: all 0.3s;
        }
        .logout-link:hover {
            background: rgba(220, 53, 69, 1);
            transform: translateY(-2px);
        }
        @media (max-width: 992px) {
            .container { margin-left: 0; margin-top: 80px; }
            .sidebar { 
                width: 100%; 
                height: auto; 
                position: fixed;
                padding: 10px 0;
            }
            .sidebar h2 { margin-bottom: 10px; }
            .sidebar ul { 
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }
            .sidebar ul li { margin: 5px; }
            .sidebar ul li a { padding: 8px 15px; }
        }
        /* Additional responsive styles specific to borrowed books page */
        @media (max-width: 768px) {
            .stats-card {
                flex-direction: column;
                text-align: center;
                padding: 15px;
            }
            
            .stats-card i {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            table th, table td {
                padding: 8px;
                font-size: 0.9rem;
            }
            
            .container {
                padding: 15px;
            }
            
            button {
                padding: 6px 12px;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 576px) {
            .stats-card .stats-info h3 {
                font-size: 1.5rem;
            }
            
            .stats-card .stats-info p {
                font-size: 0.9rem;
            }
            
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            /* Make the table more compact on phones */
            table th, table td {
                padding: 6px;
                font-size: 0.85rem;
            }
            
            /* Adjust column widths for better mobile display */
            table th:nth-child(1), table td:nth-child(1) { /* Title */
                min-width: 120px;
            }
            
            table th:nth-child(2), table td:nth-child(2) { /* Author */
                min-width: 100px;
            }
            
            table th:nth-child(3), table td:nth-child(3) { /* Borrowed By */
                min-width: 120px;
            }
            
            table th:nth-child(4), table td:nth-child(4) { /* Loan Date */
                min-width: 90px;
            }
            
            /* Make the return button more touch-friendly */
            button {
                padding: 8px 12px;
                margin: 0 auto;
            }
            
            /* Bible quote adjustments */
            .bible-quote {
                position: static;
                margin: 20px 0 0 0;
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile menu toggle button -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <h2><i class="fas fa-book-reader"></i> Library Admin</h2>
        <ul>
            <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="active"><a href="borrowed_book.php"><i class="fas fa-book-open"></i> Borrowed Books</a></li>
            <li><a href="view_users.php"><i class="fas fa-users"></i> View Members</a></li>
            <li><a href="edit_book.php"><i class="fas fa-edit"></i> Edit Book</a></li>
            <li><a href="delete_book.php"><i class="fas fa-trash-alt"></i> Delete Book</a></li>
            <li><a href="add_book.php"><i class="fas fa-plus-circle"></i> Add Book</a></li>
            <li><a class="logout-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div class="container">
        <h2><i class="fas fa-book-reader"></i> Borrowed Books</h2>
        
        <div class="stats-card">
            <i class="fas fa-book-open"></i>
            <div class="stats-info">
                <h3><?php echo $borrowed_count; ?></h3>
                <p>Books Currently Borrowed</p>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Book has been successfully returned.
            </div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table id="borrowed-books" class="table table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Borrowed By</th>
                        <th>Loan Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($borrowed_books)): ?>
                        <tr>
                            <td colspan="5" class="empty-message">No books are currently borrowed.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($borrowed_books as $book): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($book['username']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($book['email']); ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($book['loan_date'])); ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <button type="submit" name="return_book" class="btn btn-danger btn-sm">
                                            <i class="fas fa-undo-alt"></i> Return
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="bible-quote">
        <i class="fas fa-quote-left me-2 opacity-50"></i> <?php echo $random_quote; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile menu toggle functionality
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
        // Close sidebar when clicking a link on mobile
        if (window.innerWidth <= 992) {
            const sidebarLinks = document.querySelectorAll('.sidebar ul li a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    document.getElementById('sidebar').classList.remove('active');
                });
            });
        }
        
        // Add touch-friendly behavior for mobile
        if ('ontouchstart' in window) {
            document.querySelectorAll('.stats-card').forEach(card => {
                card.addEventListener('touchstart', function() {
                    this.classList.add('hover');
                });
                card.addEventListener('touchend', function() {
                    this.classList.remove('hover');
                });
            });
        }
    </script>
</body>
</html>
