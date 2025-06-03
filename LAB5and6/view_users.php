<?php
session_start();
require_once 'db.php';
require_once 'Book.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: admin_login.php");
    exit();
}

// Handle assign book to user
if (isset($_POST['assign_book'])) {
    $user_id = (int)$_POST['user_id'];
    $book_id = (int)$_POST['book_id'];
    
    // Get book details
    $book_stmt = $pdo->prepare("SELECT * FROM Books WHERE book_id = ?");
    $book_stmt->execute([$book_id]);
    $b = $book_stmt->fetch();
    
    if ($b) {
        // Create Book object and borrow it
        $book = new Book($b['book_id'], $b['title'], $b['author'], $b['price'], $b['genre'], $b['year']);
        if ($book->borrowBook($user_id)) {
            // Success message
            header("Location: view_users.php?success=assigned");
            exit();
        } else {
            // Error message
            header("Location: view_users.php?error=failed_assign");
            exit();
        }
    } else {
        header("Location: view_users.php?error=invalid_book");
        exit();
    }
}

// Fetch users from the database
$user_stmt = $pdo->query("SELECT id, username, email, google_id, created_at FROM users ORDER BY id ASC");
$users = $user_stmt->fetchAll();
$user_count = count($users);

// Get available books for assignment - only books with status 'available'
$available_books_stmt = $pdo->query("
    SELECT * FROM Books 
    WHERE status = 'available' 
    ORDER BY title ASC
");
$available_books = $available_books_stmt->fetchAll();

// Get borrowed books count for each user
$user_books = [];
$user_borrowed_books = [];
foreach ($users as $user) {
    $book_stmt = $pdo->prepare("
        SELECT COUNT(*) as book_count 
        FROM BookLoans 
        WHERE member_id = ? AND return_date IS NULL
    ");
    $book_stmt->execute([$user['id']]);
    $book_count = $book_stmt->fetch();
    $user_books[$user['id']] = $book_count['book_count'];
    
    // Get list of books borrowed by this user
    $borrowed_books_stmt = $pdo->prepare("
        SELECT b.book_id, b.title, b.author, bl.loan_date
        FROM BookLoans bl
        JOIN Books b ON bl.book_id = b.book_id
        WHERE bl.member_id = ? AND bl.return_date IS NULL
        ORDER BY bl.loan_date DESC
    ");
    $borrowed_books_stmt->execute([$user['id']]);
    $user_borrowed_books[$user['id']] = $borrowed_books_stmt->fetchAll();
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
$quote_colors = ["#007bff", "#28a745", "#dc3545", "#6f42c1", "#fd7e14"];
$random_color = $quote_colors[array_rand($quote_colors)];

// Calculate statistics
$google_users = count(array_filter($users, function($user) { return !empty($user['google_id']); }));
$email_users = count(array_filter($users, function($user) { return empty($user['google_id']); }));
$total_borrowed = array_sum($user_books);
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Members</title>
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
            margin-bottom: 25px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .stats-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            flex: 1;
            min-width: 200px;
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-left: 5px solid #007bff;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .stat-card.google {
            border-left-color: #ea4335;
        }
        .stat-card.email {
            border-left-color: #34495e;
        }
        .stat-card.books {
            border-left-color: #28a745;
        }
        .stat-card .icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #007bff;
        }
        .stat-card.google .icon {
            color: #ea4335;
        }
        .stat-card.email .icon {
            color: #34495e;
        }
        .stat-card.books .icon {
            color: #28a745;
        }
        .stat-card .number {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .stat-card .label {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .user-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 30px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .user-table th {
            background: #f1f5f9;
            color: #34495e;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #e1e4e8;
        }
        .user-table td {
            padding: 15px;
            border-bottom: 1px solid #e1e4e8;
            vertical-align: middle;
        }
        .user-table tr:last-child td {
            border-bottom: none;
        }
        .user-table tr:hover {
            background: #f8f9fa;
        }
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-google {
            background: #ea4335;
            color: white;
        }
        .badge-email {
            background: #34495e;
            color: white;
        }
        .badge-books {
            background: #28a745;
            color: white;
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
        .search-box {
            margin-bottom: 25px;
            position: relative;
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
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #6c757d;
            margin-right: 10px;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        
        /* Responsive styles */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .container {
                margin-left: 0;
                width: 100%;
                max-width: 100%;
                padding: 20px;
                margin-top: 60px;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .bible-quote {
                position: static;
                margin: 20px auto;
                min-width: auto;
                max-width: 100%;
            }
            
            .stats-row {
                flex-direction: column;
            }
            
            .stat-card {
                margin-bottom: 15px;
            }
        }
        
        /* Mobile menu toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 1001;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        @media (max-width: 992px) {
            .mobile-menu-toggle {
                display: block; /* Make sure it's visible on mobile */
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .stats-row {
                gap: 10px;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-card .number {
                font-size: 1.8rem;
            }
            
            .user-table th, 
            .user-table td {
                padding: 10px;
                font-size: 0.9rem;
            }
            
            .badge {
                padding: 4px 8px;
                font-size: 0.7rem;
            }
            
            .table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .user-table {
                min-width: 800px;
            }
            
            .user-table th, 
            .user-table td {
                white-space: nowrap;
            }
        }
        .assign-book-form {
            display: flex;
            flex-direction: column;
        }

        .borrowed-books-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.1);
        }

        .borrowed-books-details h5 {
            margin-bottom: 15px;
            font-size: 1rem;
            color: #2c3e50;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
        }

        .borrowed-books-details .table {
            margin-bottom: 0;
        }

        .borrowed-books-details .table th {
            background: rgba(0,0,0,0.03);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-outline-info {
            margin-left: 8px;
        }

        @media (max-width: 768px) {
            .assign-book-form {
                width: 100%;
            }
            
            .assign-book-form select,
            .assign-book-form button {
                width: 100%;
            }
        }
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            -ms-overflow-style: -ms-autohiding-scrollbar;
        }

        @media (max-width: 768px) {
            .table-responsive {
                border: 0;
            }
            
            .user-table {
                width: 100%;
                min-width: 800px; /* Force minimum width */
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
            <li><a href="borrowed_book.php"><i class="fas fa-book-open"></i> Borrowed Books</a></li>
            <li class="active"><a href="view_users.php"><i class="fas fa-users"></i> View Members</a></li>
            <li><a href="edit_book.php"><i class="fas fa-edit"></i> Edit Book</a></li>
            <li><a href="delete_book.php"><i class="fas fa-trash-alt"></i> Delete Book</a></li>
            <li><a href="add_book.php"><i class="fas fa-plus-circle"></i> Add Book</a></li>
            <li><a class="logout-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div class="container">
        <h2><i class="fas fa-users"></i> Library Members</h2>
        
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search members by name or email..." onkeyup="searchMembers()">
        </div>
        
        <!-- Add success/error messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php if ($_GET['success'] == 'assigned'): ?>
                    <i class="fas fa-check-circle"></i> Book has been successfully assigned to the user.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php if ($_GET['error'] == 'failed_assign'): ?>
                    <i class="fas fa-exclamation-circle"></i> Failed to assign the book. It may already be borrowed.
                <?php elseif ($_GET['error'] == 'invalid_book'): ?>
                    <i class="fas fa-exclamation-circle"></i> Invalid book selection.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="stats-row">
            <div class="stat-card">
                <div class="icon"><i class="fas fa-users"></i></div>
                <div class="number"><?php echo $user_count; ?></div>
                <div class="label">Total Members</div>
            </div>
            
            <div class="stat-card google">
                <div class="icon"><i class="fab fa-google"></i></div>
                <div class="number"><?php echo $google_users; ?></div>
                <div class="label">Google Users</div>
            </div>
            
            <div class="stat-card email">
                <div class="icon"><i class="fas fa-envelope"></i></div>
                <div class="number"><?php echo $email_users; ?></div>
                <div class="label">Email Users</div>
            </div>
            
            <div class="stat-card books">
                <div class="icon"><i class="fas fa-book"></i></div>
                <div class="number"><?php echo $total_borrowed; ?></div>
                <div class="label">Books Borrowed</div>
            </div>
        </div>
        
        <?php if (empty($users)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No members found in the database.
            </div>
        <?php else: ?>
            <!-- Wrap table in a div for horizontal scrolling -->
            <div class="table-responsive">
                <table class="user-table" id="userTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Login Method</th>
                            <th>Joined Date</th>
                            <th>Books Borrowed</th>
                            <th>Assign Book</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $i => $user): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                        </div>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if (!empty($user['google_id'])): ?>
                                        <span class="badge badge-google"><i class="fab fa-google"></i> Google</span>
                                    <?php else: ?>
                                        <span class="badge badge-email"><i class="fas fa-envelope"></i> Email</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($user_books[$user['id']] > 0): ?>
                                        <span class="badge badge-books"><?php echo $user_books[$user['id']]; ?> books</span>
                                        <button class="btn btn-sm btn-outline-info" type="button" 
                                                onclick="toggleBorrowedBooks(<?php echo $user['id']; ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No books</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($available_books)): ?>
                                        <form method="post" class="assign-book-form">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="book_id" class="form-select form-select-sm" required>
                                                <option value="">Select a book</option>
                                                <?php foreach ($available_books as $book): ?>
                                                    <option value="<?php echo $book['book_id']; ?>">
                                                        <?php echo htmlspecialchars($book['title']); ?> (<?php echo htmlspecialchars($book['author']); ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="assign_book" class="btn btn-sm btn-primary mt-1">
                                                <i class="fas fa-book"></i> Assign
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted"><i class="fas fa-info-circle"></i> No books available</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <!-- Borrowed books details row (hidden by default) -->
                            <?php if (!empty($user_borrowed_books[$user['id']])): ?>
                                <tr class="borrowed-books-row" id="borrowed-books-<?php echo $user['id']; ?>" style="display: none;">
                                    <td colspan="7">
                                        <div class="borrowed-books-details">
                                            <h5>Books borrowed by <?php echo htmlspecialchars($user['username']); ?></h5>
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Title</th>
                                                        <th>Author</th>
                                                        <th>Borrowed Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($user_borrowed_books[$user['id']] as $book): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                                                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                                                            <td><?php echo date('M d, Y', strtotime($book['loan_date'])); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="bible-quote">
        <i class="fas fa-quote-left" style="margin-right: 8px; opacity: 0.6;"></i>
        <?php echo $random_quote; ?>
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

        function searchMembers() {
            var input, filter, table, tr, td, i,
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("userTable");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[1];
                if (td) {
                    if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        function toggleBorrowedBooks(userId) {
            const detailRow = document.getElementById('borrowed-books-' + userId);
            if (detailRow) {
                if (detailRow.style.display === 'none' || detailRow.style.display === '') {
                    detailRow.style.display = 'table-row';
                } else {
                    detailRow.style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>
