<?php
session_start();
require_once 'db.php';
require_once 'Book.php';
require_once 'user.php';

// Add debugging
error_log("Library Test: Session data - " . print_r($_SESSION, true));

// Check if user is logged in - check both possible session variables
if (!isset($_SESSION['user_id']) && !isset($_SESSION['logged_in'])) {
    error_log("Library Test: User not logged in, redirecting to login");
    header("Location: login.php");
    exit();
}

// Get user ID from session
$users_id = $_SESSION['user_id'];
$users_stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$users_stmt->execute([$users_id]);
$users_row = $users_stmt->fetch();
$users = new user($users_row['id'], $users_row['username'], $users_row['email'], $users_row['created_at']);

// Determine which page to show (default to available books)
$current_page = isset($_GET['page']) ? $_GET['page'] : 'available';

// Handle borrow action
if (isset($_POST['borrow_book'])) {
    $book_id = (int)$_POST['book_id'];
    
    // Get book details
    $book_stmt = $pdo->prepare("SELECT * FROM Books WHERE book_id = ?");
    $book_stmt->execute([$book_id]);
    $b = $book_stmt->fetch();
    
    if ($b) {
        // Create Book object and borrow it
        $book = new Book($b['book_id'], $b['title'], $b['author'], $b['price'], $b['genre'], $b['year']);
        if ($book->borrowBook($users->id)) {
            // Success message
            header("Location: library_test.php?page=$current_page&success=borrowed");
            exit();
        } else {
            // Error message
            header("Location: library_test.php?page=$current_page&error=failed_borrow");
            exit();
        }
    }
}

// Handle return action
if (isset($_POST['return_book'])) {
    $book_id = (int)$_POST['book_id'];
    
    // Get book details
    $book_stmt = $pdo->prepare("SELECT * FROM Books WHERE book_id = ?");
    $book_stmt->execute([$book_id]);
    $b = $book_stmt->fetch();
    
    if ($b) {
        // Create Book object and return it
        $book = new Book($b['book_id'], $b['title'], $b['author'], $b['price'], $b['genre'], $b['year']);
        if ($book->returnBook($users->id)) {
            // Success message
            header("Location: library_test.php?page=$current_page&success=returned");
            exit();
        } else {
            // Error message
            header("Location: library_test.php?page=$current_page&error=failed_return");
            exit();
        }
    }
}

// Show all available books
$books_stmt = $pdo->query("SELECT * FROM Books WHERE book_id NOT IN (SELECT book_id FROM BookLoans WHERE return_date IS NULL)");
$available_books = $books_stmt->fetchAll();

// Get user's borrowed books
$borrowed_books = $users->getBookloans();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Library System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <!-- Mobile menu toggle button -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <h2><i class="fas fa-book-reader"></i> Library</h2>
        
        <div class="user-profile">
            <div class="avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="username"><?php echo htmlspecialchars($users->username); ?></div>
            <div class="email"><?php echo htmlspecialchars($users->email); ?></div>
            <div class="joined">Member since: <?php echo date('M d, Y', strtotime($users->created_at)); ?></div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
        
        <ul>
            <li class="<?php echo $current_page == 'available' ? 'active' : ''; ?>">
                <a href="library_test.php?page=available">
                    <i class="fas fa-book"></i> Available Books
                </a>
            </li>
            <li class="<?php echo $current_page == 'borrowed' ? 'active' : ''; ?>">
                <a href="library_test.php?page=borrowed">
                    <i class="fas fa-book-reader"></i> My Borrowed Books
                </a>
            </li>
            <li class="<?php echo $current_page == 'profile' ? 'active' : ''; ?>">
                <a href="library_test.php?page=profile">
                    <i class="fas fa-user"></i> My Profile
                </a>
            </li>
        </ul>
    </div>
    
    <div class="container">
        <div class="welcome-header">
            <div class="welcome-message">
                <i class="fas fa-book-reader"></i> Welcome, <?php echo htmlspecialchars($users->username); ?>!
            </div>
        </div>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="bookSearchInput" placeholder="Search books by title, author, or genre..." onkeyup="searchBooks()">
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php if ($_GET['success'] == 'borrowed'): ?>
                    <i class="fas fa-check-circle"></i> Book has been successfully borrowed.
                <?php elseif ($_GET['success'] == 'returned'): ?>
                    <i class="fas fa-check-circle"></i> Book has been successfully returned.
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php if ($_GET['error'] == 'failed_borrow'): ?>
                    <i class="fas fa-exclamation-circle"></i> Failed to borrow the book. It may already be borrowed.
                <?php elseif ($_GET['error'] == 'failed_return'): ?>
                    <i class="fas fa-exclamation-circle"></i> Failed to return the book.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($current_page == 'available'): ?>
            <h3><i class="fas fa-book"></i> Available Books</h3>
            <table id="available-books">
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Genre</th>
                    <th>Year</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
                <?php if (empty($available_books)): ?>
                    <tr>
                        <td colspan="6" class="empty-message">No books available at the moment.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($available_books as $book): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo htmlspecialchars($book['genre']); ?></td>
                            <td><?php echo htmlspecialchars($book['year']); ?></td>
                            <td>$<?php echo htmlspecialchars($book['price']); ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                    <button type="submit" name="borrow_book" class="btn btn-sm btn-borrow">
                                        <i class="fas fa-hand-holding"></i> Borrow
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        <?php elseif ($current_page == 'borrowed'): ?>
            <h3><i class="fas fa-book-reader"></i> Your Borrowed Books</h3>
            <table id="borrowed-books">
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Genre</th>
                    <th>Action</th>
                </tr>
                <?php if (empty($borrowed_books)): ?>
                    <tr>
                        <td colspan="4" class="empty-message">You haven't borrowed any books yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($borrowed_books as $book): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo htmlspecialchars($book['genre']); ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                    <button type="submit" name="return_book" class="btn btn-sm btn-return">
                                        <i class="fas fa-undo-alt"></i> Return
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        <?php elseif ($current_page == 'profile'): ?>
            <h3><i class="fas fa-user"></i> My Profile</h3>
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Username:</div>
                        <div class="col-md-9"><?php echo htmlspecialchars($users->username); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Email:</div>
                        <div class="col-md-9"><?php echo htmlspecialchars($users->email); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Member Since:</div>
                        <div class="col-md-9"><?php echo date('F d, Y', strtotime($users->created_at)); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Books Borrowed:</div>
                        <div class="col-md-9"><?php echo count($borrowed_books); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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
                // Skip rows with colspan (empty message rows)
                if (tr[i].querySelector('td[colspan]')) {
                    continue;
                }
                
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
            
            // Check if all rows are hidden, show the empty message
            let visibleRows = Array.from(tr).slice(1).filter(row => row.style.display !== "none");
            let emptyMessage = table.querySelector('td.empty-message');
            
            if (visibleRows.length === 0 && !emptyMessage) {
                // Create a new row with empty message
                let newRow = document.createElement('tr');
                let newCell = document.createElement('td');
                newCell.colSpan = tr[0].cells.length;
                newCell.className = "empty-message";
                newCell.textContent = "No matching books found.";
                newRow.appendChild(newCell);
                table.appendChild(newRow);
            } else if (visibleRows.length > 0 && emptyMessage) {
                // Remove the empty message if we have visible rows
                emptyMessage.parentNode.remove();
            }
        }
    }
</script>
</body>
</html>
