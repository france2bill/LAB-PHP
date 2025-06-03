<?php
session_start();
require_once 'db.php';
require_once 'Book.php';
require_once 'Ebook.php';
require_once 'Member.php';
require_once 'Discountable.php';

// Fetch all members for the dropdown
$all_members = $pdo->query("SELECT * FROM Members")->fetchAll();

// Determine selected member (default to first member if not set)
if (isset($_POST['selected_member'])) {
    $member_id = intval($_POST['selected_member']);
    $_SESSION['selected_member'] = $member_id;
} elseif (isset($_SESSION['selected_member'])) {
    $member_id = $_SESSION['selected_member'];
} else {
    $member_id = $all_members[0]['member_id'];
    $_SESSION['selected_member'] = $member_id;
}

$member_stmt = $pdo->prepare("SELECT * FROM Members WHERE member_id=?");
$member_stmt->execute([$member_id]);
$member_row = $member_stmt->fetch();
$member = new Member($member_row['member_id'], $member_row['name'], $member_row['email'], $member_row['membership_date']);

// Borrow/Return actions
if (isset($_POST['borrow']) && isset($_POST['book_id'])) {
    $book_id = $_POST['book_id'];
    $book_stmt = $pdo->prepare("SELECT * FROM Books WHERE book_id=?");
    $book_stmt->execute([$book_id]);
    $b = $book_stmt->fetch();
    if ($b) {
        $book = new Book($b['book_id'], $b['title'], $b['author'], $b['price'], $b['genre'], $b['year']);
        $book->borrowBook($member_id);
    }
}
if (isset($_POST['return']) && isset($_POST['book_id'])) {
    $book_id = $_POST['book_id'];
    $book_stmt = $pdo->prepare("SELECT * FROM Books WHERE book_id=?");
    $book_stmt->execute([$book_id]);
    $b = $book_stmt->fetch();
    if ($b) {
        $book = new Book($b['book_id'], $b['title'], $b['author'], $b['price'], $b['genre'], $b['year']);
        $book->returnBook($member_id);
    }
}

// Get all borrowed book IDs (not yet returned)
$borrowed_books_stmt = $pdo->query("SELECT book_id FROM BookLoans WHERE return_date IS NULL");
$borrowed_book_ids = $borrowed_books_stmt->fetchAll(PDO::FETCH_COLUMN);

// Show only available books (not borrowed by anyone)
if (count($borrowed_book_ids) > 0) {
    $placeholders = implode(',', array_fill(0, count($borrowed_book_ids), '?'));
    $books_stmt = $pdo->prepare("SELECT * FROM Books WHERE book_id NOT IN ($placeholders)");
    $books_stmt->execute($borrowed_book_ids);
    $books = $books_stmt->fetchAll();
} else {
    $books = $pdo->query("SELECT * FROM Books")->fetchAll();
}

// Show user's borrowed books
$borrowed = $member->getBorrowedBooks();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Library Test</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .container {
            max-width: 850px;
            margin-top: 50px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 32px rgba(0,0,0,0.10);
            padding: 40px 36px 36px 36px;
        }
        h2 {
            color: #007bff;
            font-weight: 700;
            letter-spacing: 1px;
        }
        h3 {
            color: #444;
            margin-top: 32px;
            margin-bottom: 18px;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            background: #fafdff;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #e3eaf3;
        }
        th {
            background-color: #e9ecef;
            color: #34495e;
            font-weight: 600;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .discount {
            color: #28a745;
            font-weight: bold;
        }
        .original-price {
            text-decoration: line-through;
            color: #888;
            margin-right: 6px;
        }
        .btn-success, .btn-danger, .btn-outline-secondary {
            font-size: 0.98rem;
            padding: 5px 16px;
            border-radius: 6px;
        }
        .btn-success {
            background: linear-gradient(90deg, #43e97b 0%, #38f9d7 100%);
            border: none;
            color: #fff;
        }
        .btn-success:hover {
            background: linear-gradient(90deg, #38f9d7 0%, #43e97b 100%);
            color: #fff;
        }
        .btn-danger {
            background: linear-gradient(90deg, #ff5858 0%, #f09819 100%);
            border: none;
            color: #fff;
        }
        .btn-danger:hover {
            background: linear-gradient(90deg, #f09819 0%, #ff5858 100%);
            color: #fff;
        }
        .btn-outline-secondary {
            border: 1.5px solid #007bff;
            color: #007bff;
            background: #fff;
        }
        .btn-outline-secondary:hover {
            background: #007bff;
            color: #fff;
        }
        .mb-0 {
            margin-bottom: 0 !important;
        }
        .shadow {
            box-shadow: 0 2px 16px rgba(0,0,0,0.08) !important;
        }
        .rounded {
            border-radius: 18px !important;
        }
        @media (max-width: 900px) {
            .container { padding: 18px 4vw; }
            h2 { font-size: 1.3rem; }
            h3 { font-size: 1.1rem; }
            th, td { padding: 8px 4px; }
        }
    </style>
</head>
<body>
<div class="container shadow rounded">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            Welcome,
            <form method="post" style="display:inline;">
                <select name="selected_member" onchange="this.form.submit()" class="form-select d-inline-block" style="width:auto;display:inline-block;">
                    <?php foreach ($all_members as $m): ?>
                        <option value="<?php echo $m['member_id']; ?>" <?php if ($m['member_id'] == $member_id) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($m['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </h2>
        <a href="logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
    </div>
    <h3>Available Books</h3>
    <table>
        <tr>
            <th>Title</th><th>Author</th><th>Genre</th><th>Year</th><th>Price</th><th>Action</th>
        </tr>
        <?php if (empty($books)): ?>
            <tr><td colspan="6" class="text-center text-muted">No available books.</td></tr>
        <?php else: ?>
        <?php foreach ($books as $b): ?>
            <tr>
                <td><?php echo htmlspecialchars($b['title']); ?></td>
                <td><?php echo htmlspecialchars($b['author']); ?></td>
                <td><?php echo htmlspecialchars($b['genre']); ?></td>
                <td><?php echo htmlspecialchars($b['year']); ?></td>
                <td>
                    <?php
                    if (isset($b['discount']) && $b['discount'] > 0) {
                        class DiscountedBook extends Book implements Discountable {
                            private $discount;
                            public function __construct($book_id, $title, $author, $price, $genre, $year, $discount) {
                                parent::__construct($book_id, $title, $author, $price, $genre, $year);
                                $this->discount = $discount;
                            }
                            public function getDiscount() {
                                return $this->price * (1 - $this->discount / 100);
                            }
                        }
                        $discountedBook = new DiscountedBook($b['book_id'], $b['title'], $b['author'], $b['price'], $b['genre'], $b['year'], $b['discount']);
                        $discounted = $discountedBook->getDiscount();
                        echo '<span class="original-price">₦' . number_format($b['price'], 2) . '</span> ';
                        echo '<span class="discount">₦' . number_format($discounted, 2) . ' ('.$b['discount'].'% Off)</span>';
                    } else if (strtolower($b['genre']) === 'ebook') {
                        $ebook = new Ebook($b['book_id'], $b['title'], $b['author'], $b['price'], $b['genre'], $b['year'], '');
                        $discounted = $ebook->getDiscount();
                        echo '<span class="original-price">₦' . number_format($b['price'], 2) . '</span> ';
                        echo '<span class="discount">₦' . number_format($discounted, 2) . ' (10% Off)</span>';
                    } else {
                        echo '₦' . number_format($b['price'], 2);
                    }
                    ?>
                </td>
                <td>
                    <form method="post" style="margin:0;">
                        <input type="hidden" name="book_id" value="<?php echo $b['book_id']; ?>">
                        <button name="borrow" value="1" class="btn btn-success btn-sm">Borrow</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </table>
    <h3>Your Borrowed Books</h3>
    <table>
        <tr><th>Title</th><th>Return</th></tr>
        <?php if (empty($borrowed)): ?>
            <tr><td colspan="2" class="text-center text-muted">You have not borrowed any books.</td></tr>
        <?php else: ?>
        <?php foreach ($borrowed as $b): ?>
            <tr>
                <td><?php echo htmlspecialchars($b['title']); ?></td>
                <td>
                    <form method="post" style="margin:0;">
                        <input type="hidden" name="book_id" value="<?php echo $b['book_id']; ?>">
                        <button name="return" value="1" class="btn btn-danger btn-sm">Return</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>
</body>
</html>