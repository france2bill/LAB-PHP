<?php
require_once 'Loanable.php';

class Book implements Loanable {
    public $book_id, $title, $author, $price, $genre, $year;

    public function __construct($book_id, $title, $author, $price, $genre, $year) {
        $this->book_id = $book_id;
        $this->title = $title;
        $this->author = $author;
        $this->price = $price;
        $this->genre = $genre;
        $this->year = $year;
    }

    public function borrowBook($user_id) {
        global $pdo;

        // Log the attempt
        error_log("Attempting to borrow book ID {$this->book_id} for user ID {$user_id}");

        // Check book status first
        $status_stmt = $pdo->prepare("SELECT status FROM Books WHERE book_id = ?");
        $status_stmt->execute([$this->book_id]);
        $book_status = $status_stmt->fetch();
        
        if (!$book_status) {
            error_log("Book ID {$this->book_id} not found in database");
            return false; // Book not found
        }
        
        error_log("Book ID {$this->book_id} current status: " . ($book_status['status'] ?? 'NULL'));
        
        if (isset($book_status['status']) && $book_status['status'] !== 'available') {
            error_log("Book ID {$this->book_id} is not available (status: {$book_status['status']})");
            return false; // Book is not available
        }

        // Check if the book is already borrowed
        $stmt = $pdo->prepare("SELECT * FROM BookLoans WHERE book_id = ? AND return_date IS NULL");
        $stmt->execute([$this->book_id]);
        $existingLoan = $stmt->fetch();

        if ($existingLoan) {
            error_log("Book ID {$this->book_id} is already borrowed by user ID {$existingLoan['member_id']}");
            
            // Update book status to match reality
            $update_status = $pdo->prepare("UPDATE Books SET status = 'borrowed' WHERE book_id = ?");
            $update_status->execute([$this->book_id]);
            error_log("Updated book status to 'borrowed' to match BookLoans table");
            
            return false; // Book is already borrowed
        }

        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Insert a new loan record
            $insert_stmt = $pdo->prepare("INSERT INTO BookLoans (book_id, member_id, loan_date) VALUES (?, ?, NOW())");
            $insert_stmt->execute([$this->book_id, $user_id]);

            // Update the book status to "borrowed"
            $update_stmt = $pdo->prepare("UPDATE Books SET status = 'borrowed' WHERE book_id = ?");
            $update_stmt->execute([$this->book_id]);
            
            // Commit transaction
            $pdo->commit();
            
            error_log("Book ID {$this->book_id} successfully borrowed by user ID {$user_id}");
            return true; // Book borrowed successfully
        } catch (PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            
            error_log("Error borrowing book ID {$this->book_id} by user ID {$user_id}: " . $e->getMessage());
            
            // Check if this is a foreign key constraint error
            if ($e->getCode() == '23000') {
                error_log("Foreign key constraint error. Attempting to add user to members table.");
                // Try to insert the user into the members table first
                try {
                    // Get user information
                    $user_stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
                    $user_stmt->execute([$user_id]);
                    $user = $user_stmt->fetch();
                    
                    if ($user) {
                        // Insert into members table
                        $member_stmt = $pdo->prepare("INSERT INTO members (member_id, name, email, membership_date) VALUES (?, ?, ?, ?)");
                        $member_stmt->execute([$user_id, $user['username'], $user['email'], $user['created_at']]);
                        
                        error_log("User ID {$user_id} added to members table. Retrying borrow operation.");
                        // Now try the borrow operation again
                        return $this->borrowBook($user_id);
                    } else {
                        error_log("User ID {$user_id} not found in users table.");
                    }
                } catch (PDOException $inner_e) {
                    error_log("Error adding user ID {$user_id} to members table: " . $inner_e->getMessage());
                    // If this also fails, return false
                    return false;
                }
            }
            
            return false;
        }
    }

    public function returnBook($user_id) {
        global $pdo;

        try {
            // Start transaction
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE BookLoans SET return_date = NOW() WHERE book_id = ? AND member_id = ? AND return_date IS NULL");
            $stmt->execute([$this->book_id, $user_id]);

            // Update the book status to "available"
            $update_stmt = $pdo->prepare("UPDATE Books SET status = 'available' WHERE book_id = ?");
            $update_stmt->execute([$this->book_id]);
            
            // Commit transaction
            $pdo->commit();
            
            return true;
        } catch (PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            return false;
        }
    }
}
?>
