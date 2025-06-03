<?php
session_start();
require_once 'db.php';
require_once 'csrf_token.php'; // Include the CSRF protection file

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: admin_login.php");
    exit();
}

$message = "";
$message_type = "";

// Handle add book form
if (isset($_POST['add_book'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $message = "Security validation failed. Please try again.";
        $message_type = "error";
    } else {
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $price = trim($_POST['price']);
        $genre = trim($_POST['genre']);
        $year = isset($_POST['year']) ? trim($_POST['year']) : date('Y');
        
        if ($title && $author && $price && $genre) {
            try {
                // Insert book into database
                $stmt = $pdo->prepare("INSERT INTO Books (title, author, price, genre, year, status) VALUES (?, ?, ?, ?, ?, 'available')");
                $result = $stmt->execute([$title, $author, $price, $genre, $year]);
                
                if ($result) {
                    $message = "Book added successfully!";
                    $message_type = "success";
                    
                    // Refresh CSRF token after successful form submission
                    refresh_csrf_token();
                } else {
                    $message = "Error adding book to database.";
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

// Generate a new CSRF token for the form
$csrf_token = generate_csrf_token();

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
$quote_colors = [
    "#007bff", "#28a745", "#dc3545", "#fd7e14", "#6f42c1",
    "#17a2b8", "#ffc107", "#343a40", "#20c997", "#e83e8c"
];
$random_color = $quote_colors[array_rand($quote_colors)];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Book</title>
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
        <h2><i class="fas fa-book-reader"></i> Library Admin</h2>
        <ul>
            <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="borrowed_book.php"><i class="fas fa-book-open"></i> Borrowed Books</a></li>
            <li><a href="view_users.php"><i class="fas fa-users"></i> View Members</a></li>
            <li><a href="edit_book.php"><i class="fas fa-edit"></i> Edit Book</a></li>
            <li><a href="delete_book.php"><i class="fas fa-trash-alt"></i> Delete Book</a></li>
            <li class="active"><a href="add_book.php"><i class="fas fa-plus-circle"></i> Add Book</a></li>
            <li><a class="logout-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div class="container">
        <h2><i class="fas fa-plus-circle"></i> Add New Book</h2>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php if ($message_type === 'success'): ?>
                    <i class="fas fa-check-circle"></i>
                <?php else: ?>
                    <i class="fas fa-exclamation-circle"></i>
                <?php endif; ?>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="process_book.php">
            <?php csrf_token_field(); ?>
            <input type="hidden" name="action" value="add_book">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="title"><i class="fas fa-book"></i> Title:</label>
                    <input type="text" name="title" id="title" required>
                </div>
                <div class="form-group">
                    <label for="author"><i class="fas fa-user-edit"></i> Author:</label>
                    <input type="text" name="author" id="author" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price"><i class="fas fa-tag"></i> Price:</label>
                    <input type="number" step="0.01" name="price" id="price" required>
                </div>
                <div class="form-group">
                    <label for="genre"><i class="fas fa-bookmark"></i> Genre:</label>
                    <input type="text" name="genre" id="genre" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="year"><i class="fas fa-calendar-alt"></i> Year (optional):</label>
                    <input type="number" name="year" id="year" value="<?php echo date('Y'); ?>" min="1900" max="<?php echo date('Y'); ?>">
                </div>
                <div class="form-group">
                    <!-- Placeholder for future field -->
                </div>
            </div>
            
            <button type="submit" name="add_book">
                <i class="fas fa-plus-circle"></i> Add Book
            </button>
        </form>
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
    </script>
</body>
</html>
