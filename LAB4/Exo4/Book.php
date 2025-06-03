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

    public function borrowBook($memberId) {
        // Store loan in DB
        require 'db.php';
        $stmt = $pdo->prepare("INSERT INTO BookLoans (book_id, member_id, loan_date) VALUES (?, ?, NOW())");
        return $stmt->execute([$this->book_id, $memberId]);
    }

    public function returnBook($memberId) {
        require 'db.php';
        $stmt = $pdo->prepare("UPDATE BookLoans SET return_date = NOW() WHERE book_id = ? AND member_id = ? AND return_date IS NULL");
        return $stmt->execute([$this->book_id, $memberId]);
    }
}
?>