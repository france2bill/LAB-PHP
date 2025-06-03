<?php
// Include admin authentication check
require_once 'admin_auth.php';

// Generate CSRF token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Log access for debugging
error_log('Admin dashboard accessed by admin ID: ' . $_SESSION['admin_id']);
error_log('Session data: ' . print_r($_SESSION, true));

// Continue with the rest of the dashboard code
require_once 'Book.php';
require_once 'db.php';

// Function to check and fix data inconsistencies
function fixDataInconsistencies($pdo) {
    // Find books that are in BookLoans but have wrong status
    $inconsistent_books_query = "
        SELECT b.book_id, b.title, b.status
        FROM Books b
        JOIN BookLoans bl ON b.book_id = bl.book_id
        WHERE bl.return_date IS NULL AND (b.status IS NULL OR b.status != 'borrowed')
    ";
    $inconsistent_books = $pdo->query($inconsistent_books_query)->fetchAll();
    
    foreach ($inconsistent_books as $book) {
        $update_stmt = $pdo->prepare("UPDATE Books SET status = 'borrowed' WHERE book_id = ?");
        $update_stmt->execute([$book['book_id']]);
        error_log("Fixed inconsistency: Book ID {$book['book_id']} ({$book['title']}) status updated to 'borrowed'");
    }
    
    // Find books that are not in BookLoans but have wrong status
    $available_books_query = "
        SELECT b.book_id, b.title, b.status
        FROM Books b
        WHERE b.book_id NOT IN (
            SELECT book_id FROM BookLoans WHERE return_date IS NULL
        ) AND (b.status IS NULL OR b.status != 'available')
    ";
    $available_books = $pdo->query($available_books_query)->fetchAll();
    
    foreach ($available_books as $book) {
        $update_stmt = $pdo->prepare("UPDATE Books SET status = 'available' WHERE book_id = ?");
        $update_stmt->execute([$book['book_id']]);
        error_log("Fixed inconsistency: Book ID {$book['book_id']} ({$book['title']}) status updated to 'available'");
    }
}

// Run the fix function
fixDataInconsistencies($pdo);

// Fetch all books from the database
$stmt = $pdo->query("SELECT * FROM books ORDER BY title ASC");
$all_books = $stmt->fetchAll();

// Fetch borrowed books from BookLoans table with user information
$borrowed_stmt = $pdo->query("
    SELECT b.book_id, b.title, b.author, b.genre, u.username, u.email, bl.loan_date 
    FROM BookLoans bl
    JOIN Books b ON bl.book_id = b.book_id
    JOIN users u ON bl.member_id = u.id
    WHERE bl.return_date IS NULL
    ORDER BY bl.loan_date DESC
");
$borrowed_books_list = $borrowed_stmt->fetchAll();
$borrowed_book_ids = array_column($borrowed_books_list, 'book_id');

// Filter out borrowed books from the display list and ensure status is 'available'
$available_books = array_filter($all_books, function($book) use ($borrowed_book_ids) {
    return !in_array($book['book_id'], $borrowed_book_ids) && 
           (isset($book['status']) && $book['status'] === 'available');
});

// Update any inconsistent book statuses
foreach ($all_books as $book) {
    $is_borrowed = in_array($book['book_id'], $borrowed_book_ids);
    $current_status = isset($book['status']) ? $book['status'] : null;
    
    // If book is borrowed but status is not 'borrowed', update it
    if ($is_borrowed && $current_status !== 'borrowed') {
        $update_stmt = $pdo->prepare("UPDATE Books SET status = 'borrowed' WHERE book_id = ?");
        $update_stmt->execute([$book['book_id']]);
        error_log("Updated book ID {$book['book_id']} status from '{$current_status}' to 'borrowed'");
    }
    
    // If book is not borrowed but status is not 'available', update it
    if (!$is_borrowed && $current_status !== 'available') {
        $update_stmt = $pdo->prepare("UPDATE Books SET status = 'available' WHERE book_id = ?");
        $update_stmt->execute([$book['book_id']]);
        error_log("Updated book ID {$book['book_id']} status from '{$current_status}' to 'available'");
    }
}

// Fetch users from the database
$user_stmt = $pdo->query("SELECT id, username, email FROM users ORDER BY id ASC");
$signup_members = $user_stmt->fetchAll();
$member_count = count($signup_members);

// Get total number of books in the database
$total_books_stmt = $pdo->query("SELECT COUNT(*) as total FROM books");
$total_books_result = $total_books_stmt->fetch();
$total_books_count = $total_books_result['total'];

// Calculate statistics for the dashboard
$available_books_count = count($available_books);
$borrowed_books_count = count($borrowed_books_list);
$member_count = count($signup_members);

// Handle assign borrow
if (isset($_POST['assign_borrow'])) {
    $book_id = (int)$_POST['book_index'];
    $member_email = $_POST['member_email'];
    
    // Get the member ID from email
    $member_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $member_stmt->execute([$member_email]);
    $member = $member_stmt->fetch();
    
    if ($member) {
        $member_id = $member['id'];
        
        // Get book details
        $book_stmt = $pdo->prepare("SELECT * FROM Books WHERE book_id = ?");
        $book_stmt->execute([$book_id]);
        $b = $book_stmt->fetch();
        
        if ($b) {
            // Create Book object and borrow it
            require_once 'Book.php';
            $book = new Book($b['book_id'], $b['title'], $b['author'], $b['price'], $b['genre'], $b['year']);
            if ($book->borrowBook($member_id)) {
                // Success message or redirect
                header("Location: admin_dashboard.php?success=borrowed");
                exit();
            } else {
                // Error message
                header("Location: admin_dashboard.php?error=failed_borrow");
                exit();
            }
        }
    }
    
    // If we get here, something went wrong
    header("Location: admin_dashboard.php?error=invalid_data");
    exit();
}

// Handle return book
if (isset($_POST['return_book'])) {
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
            $book->returnBook($member_id);
            
            // Redirect with success message
            header("Location: admin_dashboard.php?success=returned");
            exit();
        }
    }
    
    // If we get here, something went wrong
    header("Location: admin_dashboard.php?error=failed_return");
    exit();
}

// Handle fix book status
if (isset($_POST['fix_status'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // Invalid CSRF token
        header("Location: admin_dashboard.php?tab=all&error=security_validation");
        exit();
    }
    
    $book_id = (int)$_POST['book_id'];
    $new_status = $_POST['new_status'];
    
    if ($new_status === 'available' || $new_status === 'borrowed') {
        // Update the book status
        $update_stmt = $pdo->prepare("UPDATE Books SET status = ? WHERE book_id = ?");
        $update_stmt->execute([$new_status, $book_id]);
        
        // If setting to available, also update any BookLoans entries
        if ($new_status === 'available') {
            $update_loans = $pdo->prepare("UPDATE BookLoans SET return_date = NOW() WHERE book_id = ? AND return_date IS NULL");
            $update_loans->execute([$book_id]);
        }
        
        header("Location: admin_dashboard.php?tab=all&success=status_fixed");
        exit();
    } else {
        header("Location: admin_dashboard.php?tab=all&error=invalid_status");
        exit();
    }
}

// Bible quotes array
$bible_quotes = [
    "For I know the plans I have for you, declares the Lord. – Jeremiah 29:11",
    "I can do all things through Christ who strengthens me. – Philippians 4:13",
    "The Lord is my shepherd; I shall not want. – Psalm 23:1",
    "Trust in the Lord with all your heart. – Proverbs 3:5",
    "With God all things are possible. – Matthew 19:26",
    "Be strong and courageous. Do not be afraid. – Joshua 1:9",
    "The joy of the Lord is your strength. – Nehemiah 8:10",
    "Let all that you do be done in love. – 1 Corinthians 16:14",
    "God is our refuge and strength. – Psalm 46:1",
    "Cast all your anxiety on Him because He cares for you. – 1 Peter 5:7"
];
$random_quote = $bible_quotes[array_rand($bible_quotes)];

// Array of possible colors for the quote
$quote_colors = [
    "#007bff", "#28a745", "#dc3545", "#fd7e14", "#6f42c1",
    "#17a2b8", "#ffc107", "#343a40", "#20c997", "#e83e8c"
];
$random_color = $quote_colors[array_rand($quote_colors)];

// Check if "View Users" was clicked
$show_users = isset($_GET['show_users']);

// Determine which tab to show
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'available';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        /* Additional responsive styles specific to admin dashboard */
        @media (max-width: 992px) {
            .nav-tabs {
                flex-wrap: wrap;
                border-bottom: none;
            }
            
            .nav-tabs .nav-item {
                width: 100%;
                margin-bottom: 5px;
            }
            
            .nav-tabs .nav-link {
                border: 1px solid #dee2e6;
                border-radius: 5px;
                margin-right: 0;
            }
            
            .nav-tabs .nav-link.active {
                background-color: #0d6efd;
                color: white;
                border-color: #0d6efd;
            }
            
            form select, form button {
                width: 100%;
                margin-top: 5px;
            }
            
            td form {
                display: flex;
                flex-direction: column;
            }
        }
        
        @media (max-width: 576px) {
            .welcome-message {
                font-size: 1rem;
                padding: 10px;
            }
            
            .alert {
                padding: 10px;
                font-size: 0.9rem;
            }
            
            table th, table td {
                padding: 8px;
                font-size: 0.9rem;
            }
            
            .stat-card i {
                font-size: 2rem;
            }
            
            .stat-card h3 {
                font-size: 1.5rem;
            }
            
            .stat-card p {
                font-size: 0.9rem;
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
            <li class="active"><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="borrowed_book.php"><i class="fas fa-book-open"></i> Borrowed Books</a></li>
            <li><a href="view_users.php"><i class="fas fa-users"></i> View Members</a></li>
            <li><a href="edit_book.php"><i class="fas fa-edit"></i> Edit Book</a></li>
            <li><a href="delete_book.php"><i class="fas fa-trash-alt"></i> Delete Book</a></li>
            <li><a href="add_book.php"><i class="fas fa-plus-circle"></i> Add Book</a></li>
            <li><a class="logout-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="container">
        <div class="welcome-message">
            <i class="fas fa-user-shield"></i> Welcome, Admin!
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php if ($_GET['success'] == 'borrowed'): ?>
                    <i class="fas fa-check-circle"></i> Book has been successfully borrowed by the user.
                <?php elseif ($_GET['success'] == 'returned'): ?>
                    <i class="fas fa-check-circle"></i> Book has been successfully returned.
                <?php elseif ($_GET['success'] == 'status_fixed'): ?>
                    <i class="fas fa-check-circle"></i> Book status has been successfully updated.
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php if ($_GET['error'] == 'failed_borrow'): ?>
                    <i class="fas fa-exclamation-circle"></i> Failed to borrow the book. It may already be borrowed.
                <?php elseif ($_GET['error'] == 'invalid_data'): ?>
                    <i class="fas fa-exclamation-circle"></i> Invalid user or book data.
                <?php elseif ($_GET['error'] == 'failed_return'): ?>
                    <i class="fas fa-exclamation-circle"></i> Failed to return the book.
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="stats-container">
            <div class="stat-card users">
                <i class="fas fa-users"></i>
                <h3><?php echo $member_count; ?></h3>
                <p>Registered Users</p>
            </div>
            <div class="stat-card borrowed">
                <i class="fas fa-book-reader"></i>
                <h3><?php echo $borrowed_books_count; ?></h3>
                <p>Books Borrowed</p>
            </div>
            <div class="stat-card available">
                <i class="fas fa-book"></i>
                <h3><?php echo $available_books_count; ?></h3>
                <p>Available Books</p>
            </div>
            <div class="stat-card total">
                <i class="fas fa-book-bookmark"></i>
                <h3><?php echo $total_books_count; ?></h3>
                <p>Total Books</p>
            </div>
        </div>
        
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="bookSearchInput" placeholder="Search books by title, author, or genre..." onkeyup="searchBooks()">
        </div>
        
        <!-- Navigation tabs -->
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_tab == 'available' ? 'active' : ''; ?>" href="admin_dashboard.php?tab=available">
                    <i class="fas fa-book"></i> Available Books
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_tab == 'borrowed' ? 'active' : ''; ?>" href="admin_dashboard.php?tab=borrowed">
                    <i class="fas fa-book-reader"></i> Borrowed Books
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_tab == 'all' ? 'active' : ''; ?>" href="admin_dashboard.php?tab=all">
                    <i class="fas fa-books"></i> All Books
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_tab == 'users' ? 'active' : ''; ?>" href="admin_dashboard.php?tab=users">
                    <i class="fas fa-users"></i> Users
                </a>
            </li>
        </ul>
        
        <!-- Tab content -->
        <div class="tab-content">
            <!-- Available Books Tab -->
            <?php if ($current_tab == 'available'): ?>
                <h3><i class="fas fa-book"></i> Available Books</h3>
                <table border="1" cellpadding="5">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Price</th>
                        <th>Genre</th>
                        <th>Action</th>
                    </tr>
                    <?php
                    $displayCount = 1;
                    foreach ($available_books as $i => $book):
                    ?>
                    <tr>
                        <td><?php echo $displayCount++; ?></td>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['price']); ?></td>
                        <td><?php echo htmlspecialchars($book['genre']); ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="book_index" value="<?php echo $book['book_id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <select name="member_email" required>
                                    <option value="">Select a user</option>
                                    <?php foreach ($signup_members as $m): ?>
                                        <option value="<?php echo htmlspecialchars($m['email']); ?>"><?php echo htmlspecialchars($m['username']); ?> (<?php echo htmlspecialchars($m['email']); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="assign_borrow"><i class="fas fa-share"></i> Borrow</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if ($displayCount === 1): ?>
                        <tr><td colspan="6" style="text-align:center;"><em>No books in the library.</em></td></tr>
                    <?php endif; ?>
                </table>
                
                <a href="add_book.php" class="add-book-btn">
                    <i class="fas fa-plus-circle"></i> Add New Book
                </a>
            
            <!-- Borrowed Books Tab -->
            <?php elseif ($current_tab == 'borrowed'): ?>
                <h3><i class="fas fa-book-reader"></i> Borrowed Books</h3>
                <table border="1" cellpadding="5">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Genre</th>
                        <th>Borrowed By</th>
                        <th>Loan Date</th>
                        <th>Action</th>
                    </tr>
                    <?php if (empty($borrowed_books_list)): ?>
                        <tr><td colspan="7" style="text-align:center;"><em>No books are currently borrowed.</em></td></tr>
                    <?php else: ?>
                        <?php foreach ($borrowed_books_list as $i => $book): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo htmlspecialchars($book['genre']); ?></td>
                                <td><?php echo htmlspecialchars($book['username']); ?> (<?php echo htmlspecialchars($book['email']); ?>)</td>
                                <td><?php echo htmlspecialchars($book['loan_date']); ?></td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <button type="submit" name="return_book"><i class="fas fa-undo-alt"></i> Return</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>
            
            <!-- Users Tab -->
            <?php elseif ($current_tab == 'users'): ?>
                <h3><i class="fas fa-users"></i> Registered Users</h3>
                <table border="1" cellpadding="5">
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Email</th>
                    </tr>
                    <?php if (empty($signup_members)): ?>
                        <tr><td colspan="3" style="text-align:center;"><em>No users found.</em></td></tr>
                    <?php else: ?>
                        <?php foreach ($signup_members as $i => $user): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>
            <!-- All Books Tab -->
            <?php elseif ($current_tab == 'all'): ?>
                <h3><i class="fas fa-books"></i> All Books</h3>
                <table border="1" cellpadding="5">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Price</th>
                        <th>Genre</th>
                        <th>Status</th>
                        <th>In BookLoans</th>
                        <th>Action</th>
                    </tr>
                    <?php
                    $displayCount = 1;
                    foreach ($all_books as $i => $book):
                        // Check if book is in BookLoans
                        $in_bookloans = in_array($book['book_id'], $borrowed_book_ids) ? 'Yes' : 'No';
                    ?>
                    <tr>
                        <td><?php echo $displayCount++; ?></td>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['price']); ?></td>
                        <td><?php echo htmlspecialchars($book['genre']); ?></td>
                        <td><?php echo htmlspecialchars($book['status'] ?? 'NULL'); ?></td>
                        <td><?php echo $in_bookloans; ?></td>
                        <td>
                            <?php if ($in_bookloans == 'Yes'): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <button type="submit" name="return_book"><i class="fas fa-undo-alt"></i> Return</button>
                                </form>
                            <?php elseif (isset($book['status']) && $book['status'] == 'available'): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="book_index" value="<?php echo $book['book_id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <select name="member_email" required>
                                        <option value="">Select a user</option>
                                        <?php foreach ($signup_members as $m): ?>
                                            <option value="<?php echo htmlspecialchars($m['email']); ?>"><?php echo htmlspecialchars($m['username']); ?> (<?php echo htmlspecialchars($m['email']); ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="assign_borrow"><i class="fas fa-share"></i> Borrow</button>
                                </form>
                            <?php else: ?>
                                <button type="button" class="btn-fix-status" data-book-id="<?php echo $book['book_id']; ?>" data-status="available">
                                    <i class="fas fa-wrench"></i> Fix Status
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if ($displayCount === 1): ?>
                        <tr><td colspan="8" style="text-align:center;"><em>No books in the library.</em></td></tr>
                    <?php endif; ?>
                </table>
            <?php endif; ?>
        </div>
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
        const sidebarLinks = document.querySelectorAll('.sidebar ul li a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 992) {
                    document.getElementById('sidebar').classList.remove('active');
                }
            });
        });
        
        // Improved search function for mobile
        function searchBooks() {
            var input, filter, tables, tr, td, i, j, txtValue, found;
            input = document.getElementById("bookSearchInput");
            filter = input.value.toUpperCase();
            tables = document.querySelectorAll("table");
            
            // Loop through all tables that contain books
            for (let table of tables) {
                tr = table.getElementsByTagName("tr");
                
                // Loop through all table rows except the header
                for (i = 1; i < tr.length; i++) {
                    found = false;
                    
                    // Check all cells in the row (except the last one which contains actions)
                    for (j = 1; j < tr[i].cells.length - 1; j++) {
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
        }
        
        // Add touch-friendly behavior for mobile
        if ('ontouchstart' in window) {
            document.querySelectorAll('.stat-card').forEach(card => {
                card.addEventListener('touchstart', function() {
                    this.classList.add('hover');
                });
                card.addEventListener('touchend', function() {
                    this.classList.remove('hover');
                });
            });
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners to all fix status buttons
            const fixButtons = document.querySelectorAll('.btn-fix-status');
            fixButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const bookId = this.getAttribute('data-book-id');
                    const newStatus = this.getAttribute('data-status');
                    
                    if (confirm('Are you sure you want to change this book\'s status to ' + newStatus + '?')) {
                        // Create and submit a form
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.style.display = 'none';
                        
                        const bookIdInput = document.createElement('input');
                        bookIdInput.type = 'hidden';
                        bookIdInput.name = 'book_id';
                        bookIdInput.value = bookId;
                        
                        const statusInput = document.createElement('input');
                        statusInput.type = 'hidden';
                        statusInput.name = 'new_status';
                        statusInput.value = newStatus;
                        
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = 'csrf_token';
                        csrfInput.value = '<?php echo $csrf_token; ?>';
                        
                        const submitInput = document.createElement('input');
                        submitInput.type = 'hidden';
                        submitInput.name = 'fix_status';
                        submitInput.value = '1';
                        
                        form.appendChild(bookIdInput);
                        form.appendChild(statusInput);
                        form.appendChild(csrfInput);
                        form.appendChild(submitInput);
                        
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
</body>
</html>
